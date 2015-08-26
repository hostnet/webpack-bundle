<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\Node\WebpackNode;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
abstract class TokenParser implements \Twig_TokenParserInterface
{
    /**
     * @var \Twig_Parser
     */
    private $parser;

    /**
     * @var TwigExtension
     */
    private $extension;

    /**
     * @param TwigExtension $extension
     */
    public function __construct(TwigExtension $extension)
    {
        $this->extension = $extension;
    }

    /** @inheritDoc */
    public function setParser(\Twig_Parser $parser)
    {
        $this->parser = $parser;
    }

    /** @inheritDoc */
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $files  = [];
        $lineno = $stream->getCurrent()->getLine();

        // {% <tag> 'file' 'file' ... %}
        while (! $stream->isEOF() && ! $stream->getCurrent()->test(\Twig_Token::BLOCK_END_TYPE)) {
            $asset = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            if (false === ($file = $this->extension->webpackAsset($asset)[$this->getAssetExtension()])) {
                continue;
            }
            $files[] = $file;
        }
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(function($token) { return $token->test(['end' . $this->getTag()]); }, true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new WebpackNode([$body], ['files' => $files], $lineno, $this->getTag());
    }

    /**
     * @param  \Twig_Token $token
     * @return bool
     */
    public function decideEndBlock(\Twig_Token $token)
    {
        return $token->test(['end' . $this->getTag()]);
    }

    /**
     * Returns the asset extension to resolve. Can be one of "js" or "css".
     *
     * @return string
     */
    abstract protected function getAssetExtension();
}
