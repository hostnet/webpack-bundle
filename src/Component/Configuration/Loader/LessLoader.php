<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class LessLoader implements LoaderInterface
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
        $this->config = $config['loaders']['less'];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('less')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        // @TODO Make extensions and mimetypes configurable.
        return [(new CodeBlock())->set(CodeBlock::LOADER, ['{ test: /\.less$/, loader: \'style!css!less\' }'])];
    }
}
