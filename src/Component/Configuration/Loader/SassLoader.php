<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SassLoader implements LoaderInterface, ConfigExtensionInterface
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
            ->arrayNode('sass')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all_chunks')->defaultTrue()->end()
                    ->scalarNode('filename')->defaultNull()->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        $config = $this->config['loaders']['sass'];

        if (! $config['enabled']) {
            return [new CodeBlock()];
        }

        if (empty($config['filename'])) {
            // If the filename is not set, apply inline style tags.
            return [(new CodeBlock())->set(CodeBlock::LOADER, '{ test: /\.scss$/, loader: \'style!css!sass\' }')];
        }

        // If a filename is set, apply the ExtractTextPlugin
        $fn          = 'fn_extract_text_plugin_sass';
        $code_blocks = [(new CodeBlock())
            ->set(CodeBlock::HEADER, 'var ' . $fn . ' = require("extract-text-webpack-plugin");')
            ->set(CodeBlock::LOADER, '{ test: /\.scss$/, loader: '.$fn.'.extract("css!sass") }')
            ->set(CodeBlock::PLUGIN, 'new ' . $fn . '("' . $config['filename'] . '", {'. ($config['all_chunks'] ? 'allChunks: true' : '') . '})')
        ];

        // If a common_filename is set, apply the CommonsChunkPlugin.
        if (! empty($this->config['output']['common_id'])) {
            $code_blocks[] = (new CodeBlock())
                ->set(CodeBlock::PLUGIN, sprintf(
                    'new %s({name: \'%s\', filename: \'%s\'})',
                    'webpack.optimize.CommonsChunkPlugin',
                    $this->config['output']['common_id'],
                    $this->config['output']['common_id'] . '.js'
                ));
        }

        return $code_blocks;
    }
}
