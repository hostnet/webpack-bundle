<?php
namespace Hostnet\Component\Webpack\Configuration\Config;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Config\OutputConfig
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class OutputConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        OutputConfig::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('output', $config);
        $this->assertArrayHasKey('path', $config['output']);
        $this->assertArrayHasKey('filename', $config['output']);
        $this->assertArrayHasKey('common_id', $config['output']);
        $this->assertArrayHasKey('chunk_filename', $config['output']);
        $this->assertArrayHasKey('source_map_filename', $config['output']);
        $this->assertArrayHasKey('devtool_module_filename_template', $config['output']);
        $this->assertArrayHasKey('devtool_fallback_module_filename_template', $config['output']);
        $this->assertArrayHasKey('devtool_line_to_line', $config['output']);
        $this->assertArrayHasKey('hot_update_chunk_filename', $config['output']);
        $this->assertArrayHasKey('hot_update_main_filename', $config['output']);
        $this->assertArrayHasKey('public_path', $config['output']);
        $this->assertArrayHasKey('jsonp_function', $config['output']);
        $this->assertArrayHasKey('hot_update_function', $config['output']);
        $this->assertArrayHasKey('path_info', $config['output']);
    }

    public function testGetCodeBlock()
    {
        $config = new OutputConfig([
            'output' => [
                'filename' => 'foobar.js',
                'common_id' => 'common'
            ]
        ]);

        $this->assertTrue($config->getCodeBlocks()[0]->has(CodeBlock::OUTPUT));
        $this->assertArrayHasKey('filename', $config->getCodeBlocks()[0]->get(CodeBlock::OUTPUT));
        $this->assertArrayHasKey('commonId', $config->getCodeBlocks()[0]->get(CodeBlock::OUTPUT));
    }
}
