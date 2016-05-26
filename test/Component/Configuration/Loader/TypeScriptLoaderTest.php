<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Loader\TypeScriptLoader
 */
class TypeScriptLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('typescript')->children();

        TypeScriptLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('typescript', $config);
        $this->assertArrayHasKey('enabled', $config['typescript']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new TypeScriptLoader(['loaders' => ['typescript' => ['enabled' => false, 'loader' => 'ts']]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new TypeScriptLoader(['loaders' => ['typescript' => ['enabled' => true, 'loader' => 'ts']]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
