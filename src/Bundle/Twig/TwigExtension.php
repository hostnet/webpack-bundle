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
     * @param string $web_dir     webpack.output.path
     * @param string $public_path webpack.output.public_path
     * @param string $dump_path   webpack.output.dump_path
     */
    public function __construct($web_dir, $public_path, $dump_path)
    {
        $this->web_dir     = $web_dir;
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
        return [new WebpackTokenParser($this)];
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('webpack_asset', [$this, 'webpackAsset']),
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
        $asset_id        = rtrim($this->public_path, '/\\') . '/' . Compiler::getAliasId($asset);
        $full_asset_path = rtrim($this->web_dir, '/\\') . '/' . ltrim($asset_id, '/\\');

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
        $public_dir = rtrim($this->public_path, '/\\') . '/' . ltrim($this->dump_path, '/\\');

        $url = preg_replace_callback('/^@(?<bundle>\w+)/', function ($match) {
            $str = $match['bundle'];
            if (substr($str, strlen($str) - 6) === 'Bundle') {
                $str = substr($str, 0, strlen($str) - 6);
            }
            return strtolower($str);
        }, $url);

        return rtrim(str_replace('\\', '/', $public_dir), '/') . '/' . $url;
    }
}
