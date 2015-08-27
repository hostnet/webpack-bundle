<?php
namespace Hostnet\Bundle\WebpackBundle\Twig;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration;
use Hostnet\Component\Webpack\Asset\Compiler;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigExtension extends \Twig_Extension
{
    const FUNCTION_NAME = 'webpack_asset';

    /**
     * @var string
     */
    private $public_path;

    /**
     * @var string
     */
    private $dump_path;

    /**
     * @param string $public_path : webpack.output.public_path
     * @param string $bundle_path : webpack.output.dump_path
     */
    public function __construct($public_path = '', $dump_path = '')
    {
        $this->public_path = $public_path;
        $this->dump_path   = $dump_path;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return Configuration::CONFIG_ROOT;
    }

    /** {@inheritdoc} */
    public function getTokenParsers()
    {
        return [new Token\WebpackTokenParser($this)];
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(self::FUNCTION_NAME, [$this, 'webpackAsset']),
            new \Twig_SimpleFunction('webpack_public', [$this, 'webpackPublic']),
        ];
    }

    /**
     * Returns an array containing a 'js' and 'css' key that refer to the path of the compiled asset from a browser
     * perspective.
     *
     * @param  string $asset
     * @return array
     */
    public function webpackAsset($asset)
    {
        $asset_id      = rtrim($this->public_path, '/') . '/' . Compiler::getAliasId($asset);
        $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';

        return [
            'js'  => file_exists($document_root . '/' . $asset_id . '.js') ? $asset_id . '.js' : false,
            'css' => file_exists($document_root . '/' . $asset_id . '.css') ? $asset_id . '.css' : false
        ];
    }

    /**
     * Returns the mapped url for the given resource.
     *
     * For example:
     *      given url: "@AppBundle/images/foo.png"
     *      real path: "AppBundle/Resources/public/images/foo.png"
     *      mapped to: "/bundles/app/images/foo.png"
     *
     * The mapped url is either a symlink or copied asset that resides in the web/bundles directory.
     *
     * @param  string $url
     * @return string
     */
    public function webpackPublic($url)
    {
        $document_root = realpath(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        $public_dir    = substr(realpath($this->dump_path), strlen($document_root));

        $url = preg_replace_callback('/^@(\w+)/', function($match) {
            $str = $match[1];
            if (substr($str, strlen($str) - 6) === 'Bundle') {
                $str = substr($str, 0, strlen($str) - 6);
            }
            return strtolower($str);
        }, $url);

        return str_replace('\\', '/', $public_dir) . '/' . $url;
    }
}
