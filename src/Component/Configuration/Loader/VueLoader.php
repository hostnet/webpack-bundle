<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class VueLoader implements LoaderInterface, ConfigExtensionInterface
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
        $this->config = isset($config['loaders']['vue'])
            ? $config['loaders']['vue']
            : ['enabled' => false];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('vue')
                ->canBeDisabled()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        return [(new CodeBlock())->set(
            CodeBlock::LOADER, '{ test: /\.vue$/, loader: \'vue\' }'
        )];
    }
}
