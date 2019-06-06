<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

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
            ->arrayNode('sass')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all_chunks')->defaultTrue()->end()
                    ->scalarNode('filename')->defaultNull()->end()
                    ->arrayNode('include_paths')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeBlocks()
    {
        $config = $this->config['loaders']['sass'];

        $block = new CodeBlock();

        if (! $config['enabled']) {
            return [$block];
        }

        $tab1 = str_repeat(' ', 4); // "one tab" spacing for 'pretty' output
        $tab2 = str_repeat(' ', 8); // "two tabs" spacing for 'pretty' output

        if (!empty($config['include_paths'])) {
            $block->set(CodeBlock::ROOT, 'sassLoader: {' . PHP_EOL .
                $tab1 . 'includePaths: [' . PHP_EOL .
                    $tab2 . '\'' . implode('\',' . PHP_EOL . $tab2 . '\'', $config['include_paths']) . '\'' . PHP_EOL .
                $tab1 . ']' . PHP_EOL .
            '}');
        }

        if (empty($config['filename'])) {
            // If the filename is not set, apply inline style tags.
            $block->set(CodeBlock::LOADER, '{ test: /\.scss$/, loader: \'style!css!sass\' }');
            return [$block];
        }

        // If a filename is set, apply the ExtractTextPlugin
        $fn          = 'fn_extract_text_plugin_sass';
        $code_blocks = [(new CodeBlock())
            ->set(CodeBlock::HEADER, 'var ' . $fn . ' = require("extract-text-webpack-plugin");')
            ->set(CodeBlock::LOADER, '{ test: /\.scss$/, loader: ' . $fn . '.extract("css!sass") }')
            ->set(CodeBlock::PLUGIN, 'new ' . $fn . '("' . $config['filename'] . '", {' . (
                $config['all_chunks'] ? 'allChunks: true' : ''
            ) . '})'),
        ];

        if (!empty($config['include_paths'])) {
            $code_blocks[0]->set(CodeBlock::ROOT, 'sassLoader: {' . PHP_EOL .
                $tab1 . 'includePaths: [' . PHP_EOL .
                    $tab2 . '\'' . implode('\',' . PHP_EOL . $tab2 . '\'', $config['include_paths']) . '\'' . PHP_EOL .
                $tab1 . ']' . PHP_EOL .
            '}');
        }

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
