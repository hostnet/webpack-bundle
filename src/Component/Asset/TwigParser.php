<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Twig\Environment;
use Twig\Source;
use Twig\Token;
use Twig\TokenStream;

/**
 * Parses twig templates to find split points.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigParser
{
    private $tracker;
    private $twig;
    private $cache_dir;

    /**
     * @param Tracker $tracker
     */
    public function __construct(Tracker $tracker, Environment $twig, $cache_dir)
    {
        $this->tracker   = $tracker;
        $this->twig      = $twig;
        $this->cache_dir = $cache_dir;
    }

    /**
     * Consistently calculate file name hashes.
     *
     * @param $template_file
     * @param $block_index
     * @return string
     */
    public static function hashInlineFileName($template_file, $block_index)
    {
        // Work around path inconsistencies on Windows/XAMPP.
        if (DIRECTORY_SEPARATOR == '\\') {
            $template_file = str_replace('\\', '/', $template_file);
        }
        $hash = md5($template_file . $block_index);
        return $hash;
    }

    /**
     * Returns an array of split points from the given template file.
     *
     * @param  string $template_file
     * @return array
     */
    public function findSplitPoints($template_file)
    {
        $inline_blocks = 0;
        $source        = new Source(file_get_contents($template_file), $template_file);
        $stream        = $this->twig->tokenize($source);
        $points        = [];

        while (! $stream->isEOF() && $token = $stream->next()) {
            // {{ webpack_asset(...) }}
            if ($token->test(Token::NAME_TYPE, 'webpack_asset')) {
                // We found the webpack function!
                $asset          = $this->getAssetFromStream($template_file, $stream);
                $points[$asset] = $this->resolveAssetPath($asset, $template_file, $token);
            }

            // {% webpack_javascripts %} and {% webpack_stylesheets %}
            if ($token->test(Token::BLOCK_START_TYPE) && $stream->getCurrent()->test(WebpackTokenParser::TAG_NAME)) {
                $stream->next();

                if ($stream->getCurrent()->getValue() === 'inline') {
                    $stream->next();

                    $token     = $stream->next();
                    $file_name = TwigParser::hashInlineFileName($template_file, $inline_blocks);

                    // Are we dealing with a custom extension? If not, fallback to javascript.
                    $extension = 'js'; // Default
                    if ($token->test(Token::NAME_TYPE)) {
                        $extension = $token->getValue();
                        $stream->next();
                    }

                    file_put_contents(
                        $this->cache_dir . '/' . $file_name . '.' . $extension,
                        $this->stripScript($stream->getCurrent()->getValue())
                    );

                    $asset       = $file_name . '.' . $extension;
                    $id          = 'cache/' . $asset;
                    $points[$id] = $this->resolveAssetPath($this->cache_dir . '/' . $asset, $template_file, $token);
                    $inline_blocks++;
                } else {
                    $stream->next();
                    while (! $stream->isEOF() && ! $stream->getCurrent()->test(Token::BLOCK_END_TYPE)) {
                        $asset          = $stream->expect(Token::STRING_TYPE)->getValue();
                        $points[$asset] = $this->resolveAssetPath($asset, $template_file, $token);
                    }
                }
            }
        }

        return $points;
    }

    /**
     * @param  string $asset
     * @param  string $template_file
     * @param  Token  $token
     * @return string
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
     * @param  $filename
     * @param  TokenStream $stream
     * @return mixed
     */
    private function getAssetFromStream($filename, TokenStream $stream)
    {
        $this->expect($filename, $stream->next(), Token::PUNCTUATION_TYPE, '(');
        $token = $stream->next();
        $this->expect($filename, $token, Token::STRING_TYPE);
        $this->expect($filename, $stream->next(), Token::PUNCTUATION_TYPE, ')');

        return $token->getValue();
    }

    private function expect($filename, Token $token, $type, $value = null)
    {
        if ($token->getType() !== $type) {
            throw new \RuntimeException(sprintf(
                'Parse error in %s at line %d. Expected %s%s, got %s.',
                $filename,
                $token->getLine(),
                Token::typeToEnglish($type),
                $value !== null ? ' "' . $value . '"' : '',
                Token::typeToEnglish($token->getType())
            ));
        }
    }

    private function stripScript($str)
    {
        $matches = [];
        if (preg_match('/^\s*<script(\s.+?)?>(.*)<\/script>\s*$/s', $str, $matches)) {
            return $matches[2];
        }

        if (preg_match('/^\s*<style(\s.+?)?>(.*)<\/style>\s*$/s', $str, $matches)) {
            return $matches[2];
        }

        return $str;
    }
}
