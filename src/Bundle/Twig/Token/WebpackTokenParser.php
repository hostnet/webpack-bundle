<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackInlineNode;
use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackNode;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Hostnet\Component\Webpack\Asset\TwigParser;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\LoaderInterface;
use Twig\Parser;
use Twig\Token;
use Twig\TokenParser\TokenParserInterface;
use Twig\TokenStream;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackTokenParser implements TokenParserInterface
{
    /**
     * Tag name is declared as constant for easy accessibility by the TwigParser for split-point detection.
     */
    const TAG_NAME = 'webpack';

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

    /**
     * @param ExtensionInterface $extension
     * @param LoaderInterface    $loader
     */
    public function __construct(ExtensionInterface $extension, LoaderInterface $loader)
    {
        $this->extension = $extension;
        $this->loader    = $loader;
    }

    /** {@inheritDoc} */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /** {@inheritDoc} */
    public function getTag()
    {
        return self::TAG_NAME;
    }

    /** {@inheritDoc} */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();
        $lineno = $stream->getCurrent()->getLine();

        // Export type: "js" or "css"
        $export_type = $stream->expect(Token::NAME_TYPE)->getValue();
        if (! in_array($export_type, ['js', 'css', 'inline'])) {
            // This exception will include the template filename by itself.
            throw new \Twig_Error_Syntax(sprintf(
                'Expected export type "inline", "js" or "css", got "%s" at line %d.',
                $export_type,
                $lineno
            ));
        }

        if ($export_type === "inline") {
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
    private function parseType(TokenStream $stream, $lineno, $export_type)
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
    private function parseInline(TokenStream $stream, $lineno)
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
        if (! isset($this->inline_blocks[$file])) {
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
