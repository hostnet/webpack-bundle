<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
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
                    ->scalarNode('font_extensions')->defaultValue('svg,woff,woff2,eot,ttf')->end()
                    ->scalarNode('image_extensions')->defaultValue('png,gif,jpg,jpeg')->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        $limit            = $this->config['limit'];
        $image_code_block = [];
        $font_code_block  = [];

        if (isset($this->config['font_extensions'])) {
            $font_extensions = explode(',', $this->config['font_extensions']);

            foreach ($font_extensions as $font) {
                $font_code_block[] = (new CodeBlock())->set(CodeBlock::LOADER, [sprintf(
                    '{ test: /\.%s(\?v=\d+\.\d+\.\d+)?$/, loader: \'url-loader?limit=%d&name=[name]-[hash].[ext]\' }',
                    $font,
                    $limit
                )]);
            }
        }

        if (isset($this->config['image_extensions'])) {
            $image_extensions = str_replace([' ', ','], ['', '|'], $this->config['image_extensions']);

            $image_code_block = [(new CodeBlock())->set(CodeBlock::LOADER, [sprintf(
                '{ test: /\.(%s)$/, loader: \'url-loader?limit=%d&name=[name]-[hash].[ext]\' }',
                $image_extensions,
                $limit
            )])];
        }

        return array_merge(
            $image_code_block,
            $font_code_block
        );
    }
}
