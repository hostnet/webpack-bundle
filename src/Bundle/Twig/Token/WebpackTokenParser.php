<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackInlineNode;
use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackNode;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackTokenParser implements \Twig_TokenParserInterface
{
    /**
     * Tag name is declared as constant for easy accessibility by the TwigParser for split-point detection.
     */
    const TAG_NAME = 'webpack';

    /**
     * @var \Twig_Parser
     */
    private $parser;

    /**
     * @var TwigExtension
     */
    private $extension;

    /**
     * @var int
     */
    private $inline_blocks = [];

    /**
     * @param TwigExtension $extension
     * @param string        $cache_dird
     */
    public function __construct(TwigExtension $extension)
    {
        $this->extension = $extension;
    }

    /** {@inheritDoc} */
    public function setParser(\Twig_Parser $parser)
    {
        $this->parser = $parser;
    }

    /** {@inheritDoc} */
    public function getTag()
    {
        return self::TAG_NAME;
    }

    /** {@inheritDoc} */
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $lineno = $stream->getCurrent()->getLine();

        // Export type: "js" or "css"
        $export_type = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
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

    private function parseType(\Twig_TokenStream $stream, $lineno, $export_type)
    {
        $files = [];
        while (! $stream->isEOF() && ! $stream->getCurrent()->test(\Twig_Token::BLOCK_END_TYPE)) {
            $asset = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();

            if (false === ($file = $this->extension->webpackAsset($asset)[$export_type])) {
                continue;
            }
            $files[] = $file;
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(function ($token) {
            return $token->test(['end' . $this->getTag()]);
        }, true);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new WebpackNode([$body], ['files' => $files], $lineno, $this->getTag());
    }

    private function parseInline(\Twig_TokenStream $stream, $lineno)
    {
        if ($stream->test(\Twig_Token::NAME_TYPE)) {
            $stream->next();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $this->parser->subparse(function ($token) {
            return $token->test(['end' . $this->getTag()]);
        }, true);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $file = $this->parser->getEnvironment()->getLoader()->getCacheKey($stream->getFilename());

        if (! isset($this->inline_blocks[$file])) {
            $this->inline_blocks[$file] = 0;
        }

        $file_name = md5($file . $this->inline_blocks[$file]) . '.js';
        $assets    = $this->extension->webpackAsset('cache.' . $file_name);

        $this->inline_blocks[$file]++;

        return new WebpackInlineNode(
            ['js_file' => $assets['js'], 'css_file' => $assets['css']],
            $lineno,
            $this->getTag()
        );
    }
}
