<?php
namespace Hostnet\Bundle\WebpackBridge\Twig;

use Hostnet\Bundle\WebpackBridge\DependencyInjection\Configuration;
use Hostnet\Component\WebpackBridge\Asset\Compiler;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigExtension extends \Twig_Extension
{
    const FUNCTION_NAME = 'webpack';

    /**
     * @var string
     */
    private $public_path;

    /**
     * @param string $public_path
     */
    public function __construct($public_path = '')
    {
        $this->public_path = $public_path;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return Configuration::CONFIG_ROOT;
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [new \Twig_SimpleFunction(self::FUNCTION_NAME, [$this, 'webpack'])];
    }

    public function webpack($asset)
    {
        $asset_id = rtrim($this->public_path, '/') . '/' . Compiler::getAliasId($asset);

        return [
            'js'  => $asset_id . '.js',
            'css' => $asset_id . '.css'
        ];
    }
}
