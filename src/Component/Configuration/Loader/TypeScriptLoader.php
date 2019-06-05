<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class TypeScriptLoader implements LoaderInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function applyConfiguration(NodeBuilder $node_builder): void
    {
        $node_builder
            ->arrayNode('typescript')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('loader')->defaultValue('ts')->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeBlocks()
    {
        $config = $this->config['loaders']['typescript'];

        if (! $config['enabled']) {
            return [new CodeBlock()];
        }

        return [(new CodeBlock())->set(
            CodeBlock::LOADER,
            sprintf("{ test: /\\.ts/, loader: '%s' }", $config['loader'])
        )];
    }
}
