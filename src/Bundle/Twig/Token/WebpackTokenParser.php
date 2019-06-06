<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackInlineNode;
use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackNode;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Hostnet\Component\Webpack\Asset\TwigParser;
use Twig\Error\SyntaxError;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\LoaderInterface;
use Twig\Parser;
use Twig\Token;
use Twig\TokenParser\TokenParserInterface;
use Twig\TokenStream;

class WebpackTokenParser implements TokenParserInterface
{
    /**
     * Tag name is declared as constant for easy accessibility by the TwigParser for split-point detection.
     */
    public const TAG_NAME = 'webpack';

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var TwigExtension
     */
    private $extension;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var int[]
     */
    private $inline_blocks = [];

    public function __construct(ExtensionInterface $extension, LoaderInterface $loader)
    {
        $this->extension = $extension;
        $this->loader    = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return self::TAG_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();
        $lineno = $stream->getCurrent()->getLine();

        // Export type: "js" or "css"
        $export_type = $stream->expect(Token::NAME_TYPE)->getValue();
        if (false === \in_array($export_type, ['js', 'css', 'inline'])) {
            // This exception will include the template filename by itself.
            throw new SyntaxError(sprintf(
                'Expected export type "inline", "js" or "css", got "%s" at line %d.',
                $export_type,
                $lineno
            ));
        }

        if ($export_type === 'inline') {
            return $this->parseInline($stream, $lineno);
        }

        return $this->parseType($stream, $lineno, $export_type);
    }

    /**
     * @param TokenStream $stream
     * @param int         $lineno
     * @param string      $export_type
     * @return WebpackNode
     */
    private function parseType(TokenStream $stream, $lineno, $export_type): WebpackNode
    {
        $files = [];
        while (! $stream->isEOF() && ! $stream->getCurrent()->test(Token::BLOCK_END_TYPE)) {
            $asset = $stream->expect(Token::STRING_TYPE)->getValue();

            if (false === ($file = $this->extension->webpackAsset($asset)[$export_type])) {
                continue;
            }
            $files[] = $file;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(function ($token) {
            return $token->test(['end' . $this->getTag()]);
        }, true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new WebpackNode([$body], ['files' => $files], $lineno, $this->getTag());
    }

    /**
     * @param TokenStream $stream
     * @param int         $lineno
     * @return WebpackInlineNode
     */
    private function parseInline(TokenStream $stream, $lineno): WebpackInlineNode
    {
        if ($stream->test(Token::NAME_TYPE)) {
            $stream->next();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $this->parser->subparse(function (Token $token) {
            return $token->test(['end' . $this->getTag()]);
        }, true);

        $stream->expect(Token::BLOCK_END_TYPE);

        $file = $this->loader->getCacheKey($stream->getSourceContext()->getName());
        if (false === isset($this->inline_blocks[$file])) {
            $this->inline_blocks[$file] = 0;
        }

        $file_name = TwigParser::hashInlineFileName($file, $this->inline_blocks[$file]) . '.js';
        $assets    = $this->extension->webpackAsset('cache.' . $file_name);

        $this->inline_blocks[$file]++;

        return new WebpackInlineNode(
            ['js_file' => $assets['js'], 'css_file' => $assets['css']],
            $lineno,
            $this->getTag()
        );
    }
}
