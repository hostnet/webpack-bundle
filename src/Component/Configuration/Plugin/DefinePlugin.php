<?php
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * https://github.com/webpack/docs/wiki/list-of-plugins#defineplugin
 *
 * Define free variables. Useful for having development builds with debug logging or adding global constants.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
final class DefinePlugin implements PluginInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $constants = [];

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config    = $config;
        $this->constants = $config['plugins']['constants'];
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return DefinePlugin
     */
    public function add($key, $value)
    {
        $this->constants[$key] = $value;

        return $this;
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('constants')
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        return [(new CodeBlock())
            ->set(CodeBlock::PLUGIN, sprintf('new %s(%s)', 'webpack.DefinePlugin', json_encode($this->constants)))];
    }
}
