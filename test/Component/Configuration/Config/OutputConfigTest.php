<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Config;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Config\OutputConfig
 */
class OutputConfigTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        OutputConfig::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('output', $config);
        self::assertArrayHasKey('path', $config['output']);
        self::assertArrayHasKey('filename', $config['output']);
        self::assertArrayHasKey('common_id', $config['output']);
        self::assertArrayHasKey('chunk_filename', $config['output']);
        self::assertArrayHasKey('source_map_filename', $config['output']);
        self::assertArrayHasKey('devtool_module_filename_template', $config['output']);
        self::assertArrayHasKey('devtool_fallback_module_filename_template', $config['output']);
        self::assertArrayHasKey('devtool_line_to_line', $config['output']);
        self::assertArrayHasKey('hot_update_chunk_filename', $config['output']);
        self::assertArrayHasKey('hot_update_main_filename', $config['output']);
        self::assertArrayHasKey('public_path', $config['output']);
        self::assertArrayHasKey('jsonp_function', $config['output']);
        self::assertArrayHasKey('hot_update_function', $config['output']);
        self::assertArrayHasKey('path_info', $config['output']);
    }

    public function testGetCodeBlock()
    {
        $config = new OutputConfig([
            'output' => [
                'filename' => 'foobar.js',
                'common_id' => 'common'
            ]
        ]);

        self::assertTrue($config->getCodeBlocks()[0]->has(CodeBlock::OUTPUT));
        self::assertArrayHasKey('filename', $config->getCodeBlocks()[0]->get(CodeBlock::OUTPUT));
        self::assertArrayHasKey('commonId', $config->getCodeBlocks()[0]->get(CodeBlock::OUTPUT));
    }
}
