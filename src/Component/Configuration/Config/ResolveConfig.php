<?php
namespace Hostnet\Component\Webpack\Configuration\Config;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\Container;

final class ResolveConfig implements ConfigInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        // Apply node_modules path to resolve.root
        if (! empty($config['node']['node_modules_path'])) {
            $this->config['resolve']['root'][] = $config['node']['node_modules_path'];
        }
    }

    /**
     * @param  string $alias
     * @param  string $path
     * @return ResolveConfig
     */
    public function addAlias($path, $alias = null)
    {
        $this->config['resolve']['root'][] = $path;
        if ($alias !== null) {
            $this->config['resolve']['alias'][$alias] = $path;
        }

        return $this;
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('resolve')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('root')
                        ->requiresAtLeastOneElement()
                        ->addDefaultChildrenIfNoneSet(0)
                        ->prototype('scalar')->defaultValue('%kernel.root_dir%/Resources')->end()
                    ->end()
                    ->arrayNode('alias')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('modules_directories')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('fallback')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('extensions')
                        ->defaultValue(["", ".webpack.js", ".web.js", ".js"])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        // Convert keys to camelCase.
        $config = [];
        foreach ($this->config['resolve'] as $key => $value) {
            $config[lcfirst(Container::camelize($key))] = $value;
        }

        return [(new CodeBlock())->set(CodeBlock::RESOLVE, $config)];
    }
}
