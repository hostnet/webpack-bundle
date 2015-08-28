<?php
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Bundle\WebpackBundle\Twig\Token\JavascriptTokenParser;
use Hostnet\Bundle\WebpackBundle\Twig\Token\StylesheetTokenParser;
use Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * Parses twig templates to find split points.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigParser
{
    private $tracker;
    private $twig;

    /**
     * @param Tracker $tracker
     */
    public function __construct(Tracker $tracker, \Twig_Environment $twig)
    {
        $this->tracker = $tracker;
        $this->twig    = $twig;
    }

    /**
     * Returns an array of split points from the given template file.
     *
     * @param  string $template_file
     * @return array
     */
    public function findSplitPoints($template_file)
    {
        $stream = $this->twig->tokenize(file_get_contents($template_file));
        $points = [];

        while (! $stream->isEOF() && $token = $stream->next()) {
            // {{ webpack_asset(...) }}
            if ($token->test(\Twig_Token::NAME_TYPE, TwigExtension::FUNCTION_NAME)) {
                // We found the webpack function!
                $asset          = $this->getAssetFromStream($template_file, $stream);
                $points[$asset] = $this->resolveAssetPath($asset, $template_file, $token);
            }

            // {% webpack_javascripts %} and {% webpack_stylesheets %}
            if ($token->test(\Twig_Token::BLOCK_START_TYPE) && $stream->getCurrent()->test(WebpackTokenParser::TAG_NAME)) {
                $stream->next();
                $stream->next();
                while (! $stream->isEOF() && ! $stream->getCurrent()->test(\Twig_Token::BLOCK_END_TYPE)) {
                    $asset          = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
                    $points[$asset] = $this->resolveAssetPath($asset, $template_file, $token);
                }
            }
        }

        return $points;
    }

    /**
     * @param string      $asset
     * @param string      $template_file
     * @param \Twig_Token $token
     */
    private function resolveAssetPath($asset, $template_file, $token)
    {
        if (false === ($asset_path = $this->tracker->resolveResourcePath($asset))) {
            throw new \RuntimeException(sprintf(
                'The file "%s" referenced in "%s" at line %d could not be resolved.',
                $asset,
                $template_file,
                $token->getLine()
            ));
        }

        return $asset_path;
    }

    /**
     * @throws \Twig_Error_Syntax
     * @param  $filename
     * @param  \Twig_TokenStream $stream
     */
    private function getAssetFromStream($filename, \Twig_TokenStream $stream)
    {
        $this->expect($filename, $stream->next(), \Twig_Token::PUNCTUATION_TYPE, '(');
        $token = $stream->next();
        $this->expect($filename, $token, \Twig_Token::STRING_TYPE);
        $this->expect($filename, $stream->next(), \Twig_Token::PUNCTUATION_TYPE, ')');

        return $token->getValue();
    }

    private function expect($filename, \Twig_Token $token, $type, $value = null)
    {
        if ($token->getType() !== $type) {
            throw new \RuntimeException(sprintf(
                'Parse error in %s at line %d. Expected %s%s, got %s.',
                $filename,
                $token->getLine(),
                \Twig_Token::typeToEnglish($type),
                $value !== null ? ' "' . $value . '"' : '',
                \Twig_Token::typeToEnglish($token->getType())
            ));
        }
    }
}
