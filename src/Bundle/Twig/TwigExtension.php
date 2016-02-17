<?php
namespace Hostnet\Bundle\WebpackBundle\Twig;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration;
use Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser;
use Hostnet\Component\Webpack\Asset\Compiler;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $web_dir;

    /**
     * @var string
     */
    private $public_path;

    /**
     * @var string
     */
    private $dump_path;

    /**
     * @var string
     */
    private $common_js;

    /**
     * @var string
     */
    private $common_css;

    /**
     * @param string $web_dir
     * @param string $public_path
     * @param string $dump_path
     * @param string $common_js
     * @param string $common_css
     */
    public function __construct($web_dir, $public_path, $dump_path, $common_js, $common_css)
    {
        $this->web_dir     = $web_dir;
        $this->public_path = $public_path;
        $this->dump_path   = $dump_path;
        $this->common_js   = $common_js;
        $this->common_css  = $common_css;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return Configuration::CONFIG_ROOT;
    }

    /** {@inheritdoc} */
    public function getTokenParsers()
    {
        return [new WebpackTokenParser($this)];
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('webpack_asset', [$this, 'webpackAsset']),
            new \Twig_SimpleFunction('webpack_public', [$this, 'webpackPublic']),
            new \Twig_SimpleFunction('webpack_common_js', [$this, 'webpackCommonJs']),
            new \Twig_SimpleFunction('webpack_common_css', [$this, 'webpackCommonCss']),
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
        $asset_id        = $this->public_path . '/' . Compiler::getAliasId($asset);
        $full_asset_path = $this->web_dir . '/' . $asset_id;

        return [
            'js'  => file_exists($full_asset_path . '.js')
                ? $asset_id . '.js?' . filemtime($full_asset_path . '.js')
                : false,
            'css' => file_exists($full_asset_path . '.css')
                ? $asset_id . '.css?' . filemtime($full_asset_path . '.css')
                : false
        ];
    }

    /**
     * Returns the mapped url for the given resource.
     *
     * For example:
     *      given url: "@AppBundle/images/foo.png"
     *      real path: "AppBundle/Resources/public/images/foo.png"
     *      mapped to: "/<dump_path>/app/images/foo.png"
     *
     * The mapped url is either a symlink or copied asset that resides in the <dump_path> directory.
     *
     * @param  string $url
     * @return string
     */
    public function webpackPublic($url)
    {
        $public_dir = '/' . ltrim($this->dump_path, '/');

        $url = preg_replace_callback('/^@(?<bundle>\w+)/', function ($match) {
            $str = $match['bundle'];
            if (substr($str, strlen($str) - 6) === 'Bundle') {
                $str = substr($str, 0, strlen($str) - 6);
            }
            return strtolower($str);
        }, $url);

        return rtrim($public_dir, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Example: "<output_path>/<common_id>.js".
     *
     * @return string
     */
    public function webpackCommonJs()
    {
        return $this->common_js;
    }

    /**
     * Example: "<output_path>/<common_id>.css".
     *
     * @return string
     */
    public function webpackCommonCss()
    {
        return $this->common_css;
    }
}
