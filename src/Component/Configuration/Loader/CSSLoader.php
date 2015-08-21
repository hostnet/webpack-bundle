<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class CSSLoader implements LoaderInterface
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
            ->arrayNode('css')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->booleanNode('all_chunks')->defaultTrue()->end()
                    ->scalarNode('filename')->defaultNull()->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlock()
    {
        $config      = $this->config['loaders']['css'];
        $code_blocks = [];

        if ($config['enabled'] !== true) {
            return new CodeBlock();
        }

        if (empty($config['filename'])) {
            // If the filename is not set, apply inline style tags.
            $code_blocks[] = (new CodeBlock())->set(CodeBlock::LOADER, '{ test: /\.css$/, loader: \'style!css\' }');
        } else {
            // If a filename is set, apply the ExtractTextPlugin
            $fn = 'fn_extract_text_plugin';
            $code_blocks[] = (new CodeBlock())
                ->set(CodeBlock::HEADER, 'var '.$fn.' = require("extract-text-webpack-plugin");')
                ->set(CodeBlock::LOADER, '{ test: /\.css$/, loader: '.$fn.'.extract("css-loader") }')
                ->set(CodeBlock::PLUGIN, 'new ' . $fn . '("' . $config['filename'] . '", {'. ($config['all_chunks'] ? 'allChunks: true' : '') . '})');

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
        }

        return $code_blocks;
    }
}
