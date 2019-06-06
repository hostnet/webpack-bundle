<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Component\Webpack\Configuration\Config\ConfigInterface;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Hostnet\Component\Webpack\Configuration\Loader\LoaderInterface;
use Hostnet\Component\Webpack\Configuration\Plugin\PluginInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

class Configuration implements ConfigurationInterface
{
    public const CONFIG_ROOT                     = 'webpack';
    public const DEFAULT_COMPILE_TIMEOUT_SECONDS = 60;

    private $bundles;
    private $plugins;

    public function __construct(array $bundles = [], array $plugins = [])
    {
        $this->bundles = $bundles;
        $this->plugins = $plugins;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree_builder = $this->createTreeBuilder();
        $root_node    = $this->retrieveRootNode($tree_builder);
        $children     = $root_node->children();

        $root_node->fixXmlConfig('bundle');

        $this->addNodeJSConfiguration($children);
        $this->addParentConfiguration($children);
        $this->addBundleConfiguration($children);
        $this->addLoaderConfiguration($children);
        $this->addPluginConfiguration($children);

        $children
            ->integerNode('compile_timeout')
            ->defaultValue(self::DEFAULT_COMPILE_TIMEOUT_SECONDS)
            ->end();

        $children->end();

        return $tree_builder;
    }

    /**
     * Adds node-js specific configuration to the tree builder.
     *
     * @param NodeBuilder $node
     */
    private function addNodeJSConfiguration(NodeBuilder $node): void
    {
        $node
            ->arrayNode('node')
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('binary')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) {
                            return [
                                'win32'     => $value,
                                'win64'     => $value,
                                'linux_x32' => $value,
                                'linux_x64' => $value,
                                'darwin'    => $value,
                                'fallback'  => $value,
                            ];
                        })
                    ->end()
                    ->children()
                        ->scalarNode('win32')->defaultValue('node')->end()
                        ->scalarNode('win64')->defaultValue('node')->end()
                        ->scalarNode('linux_x32')->defaultValue('node')->end()
                        ->scalarNode('linux_x64')->defaultValue('node')->end()
                        ->scalarNode('darwin')->defaultValue('node')->end()
                        ->scalarNode('fallback')->defaultValue('node')->end()
                    ->end()
                ->end()
                ->scalarNode('npm_packages_path')->defaultNull()->end()
                ->scalarNode('node_modules_path')->defaultNull()->end()
            ->end();
    }

    /**
     * Adds generic configuration to the tree builder in the parent (root) node.
     *
     * @param NodeBuilder $node
     */
    private function addParentConfiguration(NodeBuilder $node): void
    {
        $this->applyConfigurationFromClass(ConfigInterface::class, $node);
    }

    /**
     * Adds bundle configuration to the tree builder.
     *
     * @param NodeBuilder $node
     */
    private function addBundleConfiguration(NodeBuilder $node): void
    {
        $node
            ->arrayNode('bundles')
                ->defaultValue($this->bundles)
                ->prototype('scalar')
                    ->validate()
                    ->ifNotInArray($this->bundles)
                    ->thenInvalid('%s is not a valid bundle.')
                ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $node
     */
    private function addPluginConfiguration(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('plugins')
            ->addDefaultsIfNotSet()
            ->children();

        $this->applyConfigurationFromClass(PluginInterface::class, $children);
        $children->end();
    }

    /**
     * Adds loader configuration to the tree builder.
     *
     * @param NodeBuilder $node
     */
    private function addLoaderConfiguration(NodeBuilder $node): void
    {
        $children = $node
            ->arrayNode('loaders')
            ->addDefaultsIfNotSet()
            ->children();

        $this->applyConfigurationFromClass(LoaderInterface::class, $children);
        $children->end();
    }

    /**
     * @param string      $interface
     * @param NodeBuilder $node_builder
     */
    private function applyConfigurationFromClass($interface, NodeBuilder $node_builder): void
    {
        foreach ($this->plugins as $name => $class_name) {
            // Only accept plugins of type PluginInterface.
            if (false === \in_array($interface, class_implements($class_name), false)) {
                continue;
            }

            /** @var ConfigExtensionInterface $class_name */
            $class_name::applyConfiguration($node_builder);
        }
    }

    private function createTreeBuilder(): TreeBuilder
    {
        if (Kernel::VERSION_ID >= 40200) {
            return new TreeBuilder(self::CONFIG_ROOT);
        }

        if (Kernel::VERSION_ID >= 30300 && Kernel::VERSION_ID < 40200) {
            return new TreeBuilder();
        }

        throw new \RuntimeException('This bundle can only be used by Symfony 3.3 and up.');
    }

    private function retrieveRootNode(TreeBuilder $tree_builder): NodeDefinition
    {
        if (Kernel::VERSION_ID >= 40200) {
            return $tree_builder->getRootNode();
        }

        if (Kernel::VERSION_ID >= 30300 && Kernel::VERSION_ID < 40200) {
            return $tree_builder->root(self::CONFIG_ROOT);
        }

        throw new \RuntimeException('This bundle can only be used by Symfony 3.3 and up.');
    }
}
