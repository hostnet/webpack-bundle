<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Config;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\Container;

class OutputConfig implements ConfigInterface
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
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('output')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('path')->defaultValue('%kernel.root_dir%/../web')->end()
                    ->scalarNode('filename')->defaultValue('[name].js')->end()
                    ->scalarNode('common_id')->defaultValue('common.js')->end()
                    ->scalarNode('chunk_filename')->defaultValue('[name].[hash].chunk.js')->end()
                    ->scalarNode('source_map_filename')->defaultValue('[file].sourcemap.js')->end()
                    ->scalarNode('devtool_module_filename_template')->defaultValue('webpack:///[resource-path]')->end()
                    ->scalarNode('devtool_fallback_module_filename_template')->defaultValue('webpack:///[resourcePath]?[hash]')->end()
                    ->booleanNode('devtool_line_to_line')->defaultFalse()->end()
                    ->scalarNode('hot_update_chunk_filename')->defaultValue('[id].[hash].hot-update.js')->end()
                    ->scalarNode('hot_update_main_filename')->defaultValue('[hash].hot-update.json')->end()
                    ->scalarNode('public_path')->defaultValue('/')->end()
                    ->scalarNode('jsonp_function')->defaultValue('webpackJsonp')->end()
                    ->scalarNode('hot_update_function')->defaultValue('webpackHotUpdate')->end()
                    ->booleanNode('path_info')->defaultFalse()->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlock()
    {
        // Convert keys to camelCase.
        $config = [];
        foreach ($this->config['output'] as $key => $value) {
            $config[lcfirst(Container::camelize($key))] = $value;
        }

        return (new CodeBlock())->set(CodeBlock::OUTPUT, $config);
    }
}
