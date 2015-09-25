<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class UrlLoader implements LoaderInterface, ConfigExtensionInterface
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
        $this->config = $config['loaders']['url'];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('url')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('limit')->defaultValue(1000)->end()
                    ->scalarNode('extensions')->defaultValue('png,gif,jpg,jpeg,svg,woff,woff2,eot,ttf')->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        $extensions = str_replace([' ', ','], ['', '|'], $this->config['extensions']);
        $limit      = $this->config['limit'];

        return [(new CodeBlock())->set(CodeBlock::LOADER, [sprintf(
            '{ test: /\.(%s)$/, loader: \'url-loader?limit=%d&name=[name]-[hash].[ext]\' }',
            $extensions,
            $limit
        )])];
    }
}
