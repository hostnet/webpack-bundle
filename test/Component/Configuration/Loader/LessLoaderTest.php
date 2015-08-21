<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBridge\Configuration\Loader\LessLoader
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class LessLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        LessLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('less', $config);
        $this->assertArrayHasKey('enabled', $config['less']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new LessLoader(['loaders' => ['less' => ['enabled' => false]]]);
        $block  = $config->getCodeBlock();

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new LessLoader(['loaders' => ['less' => ['enabled' => true]]]);
        $block  = $config->getCodeBlock();

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
