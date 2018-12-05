<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * https://github.com/webpack/docs/wiki/list-of-plugins#provideplugin
 *
 * Define free variables. Useful for having development builds with debug logging or adding global constants.
 */
final class ProvidePlugin implements PluginInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $provides;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config   = $config;
        $this->provides = $config['plugins']['provides'];
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return ProvidePlugin
     */
    public function add($key, $value)
    {
        $this->provides[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('provides')
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeBlocks()
    {
        return [(new CodeBlock())
            ->set(CodeBlock::PLUGIN, sprintf('new %s(%s)', 'webpack.ProvidePlugin', json_encode($this->provides)))];
    }
}
