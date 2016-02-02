<?php
namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * https://github.com/webpack/docs/wiki/list-of-plugins#uglifyjsplugin
 *
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
final class UglifyJsPlugin implements PluginInterface, ConfigExtensionInterface
{
    /**
     * @var string
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = isset($config['plugins']['uglifyjs']) ? $config['plugins']['uglifyjs'] : null;
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder->scalarNode('uglifyjs')->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (null === $this->config) {
            return [];
        }

        return [(new CodeBlock())
            ->set(CodeBlock::PLUGIN, sprintf('new %s(%s)', 'webpack.optimize.UglifyJsPlugin', $this->config))];
    }
}
