<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class CoffeeScriptLoader implements LoaderInterface, ConfigExtensionInterface
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
            ->arrayNode('coffee')
              ->canBeDisabled()
              ->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('loader')->defaultValue('coffee')->end()
              ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        $config = $this->config['loaders']['coffee'];

        if (! $config['enabled']) {
            return [new CodeBlock()];
        }

        return [(new CodeBlock())->set(
            CodeBlock::LOADER,
            sprintf("{ test: /\\.coffee/, loader: '%s' }", $config['loader'])
        )];
    }
}
