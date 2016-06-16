<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Loader\CoffeeScriptLoader
 */
class CoffeeScriptLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('coffee')->children();

        CoffeeScriptLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('coffee', $config);
        $this->assertArrayHasKey('enabled', $config['coffee']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new CoffeeScriptLoader(['loaders' => ['coffee' => ['enabled' => false, 'loader' => 'coffee']]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new CoffeeScriptLoader(['loaders' => ['coffee' => ['enabled' => true, 'loader' => 'coffee']]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
