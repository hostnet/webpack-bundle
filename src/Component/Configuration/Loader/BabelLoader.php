<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class BabelLoader implements LoaderInterface, ConfigExtensionInterface
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
        $this->config = isset($config['loaders']['babel'])
            ? $config['loaders']['babel']
            : ['enabled' => false];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('babel')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('presets')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        $presets_query = '';
        if (! empty($this->config['presets'])) {
            foreach ($this->config['presets'] as $preset) {
                $presets_query .= sprintf(',presets[]=%s', $preset);
            }
        }

        return [(new CodeBlock())->set(
            CodeBlock::LOADER, sprintf(
                '{ test: /\.jsx$/, loader: \'babel-loader?cacheDirectory%s\' }',
                $presets_query
            )
        )];
    }
}
