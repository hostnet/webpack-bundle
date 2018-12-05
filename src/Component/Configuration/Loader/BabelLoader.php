<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

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

    public function __construct(array $config = [])
    {
        $this->config = $config['loaders']['babel'] ?? ['enabled' => false];
    }

    /**
     * {@inheritdoc}
     */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('babel')
                ->canBeDisabled()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        return [(new CodeBlock())->set(
            CodeBlock::LOADER,
            '{ test: /\.jsx$/, loader: \'babel-loader?cacheDirectory\' }'
        )];
    }
}
