<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBridge\Configuration\Loader\CSSLoader
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class CSSLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        CSSLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('css', $config);
        $this->assertArrayHasKey('enabled', $config['css']);
        $this->assertArrayHasKey('all_chunks', $config['css']);
        $this->assertArrayHasKey('filename', $config['css']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new CSSLoader(['loaders' => ['css' => ['enabled' => false]]]);

        $this->assertFalse($config->getCodeBlocks()[0]->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlockEnabledDefaults()
    {
        $configs = (new CSSLoader(['loaders' => ['css' => ['enabled' => true]]]))->getCodeBlocks();

        $this->assertTrue($configs[0]->has(CodeBlock::LOADER));
        $this->assertFalse($configs[0]->has(CodeBlock::HEADER));
        $this->assertFalse($configs[0]->has(CodeBlock::PLUGIN));
    }

    public function testGetCodeBlockEnabledCommonsChunk()
    {
        $configs = (new CSSLoader([
            'output'  => ['common_id' => 'foobar'],
            'loaders' => ['css' => ['enabled' => true, 'filename' => 'blaat', 'all_chunks' => true]]
        ]))->getCodeBlocks();

        $this->assertTrue($configs[0]->has(CodeBlock::LOADER));
        $this->assertTrue($configs[0]->has(CodeBlock::HEADER));
        $this->assertTrue($configs[0]->has(CodeBlock::PLUGIN));
        $this->assertTrue($configs[1]->has(CodeBlock::PLUGIN));
    }
}
