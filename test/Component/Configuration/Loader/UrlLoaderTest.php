<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBridge\Configuration\Loader\UrlLoader
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class UrlLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        UrlLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('url', $config);
        $this->assertArrayHasKey('enabled', $config['url']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => false]]]);
        $block  = $config->getCodeBlock();

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => true]]]);
        $block  = $config->getCodeBlock();

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
