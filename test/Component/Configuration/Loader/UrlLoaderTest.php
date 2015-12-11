<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Loader\UrlLoader
 * @author Harold Iedema <hiedema@hostnet.nl>
 * @author Guillaume Cavana <guillaume.cavana@gmail.com>
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
        $this->assertArrayHasKey('font_extensions', $config['url']);
        $this->assertArrayHasKey('image_extensions', $config['url']);
        $this->assertArrayHasKey('enabled', $config['url']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => false]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetFontExtensionCodeBlock()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => true, 'font_extensions' => 'svg,woff', 'limit' => 100]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertCount(2, $config->getCodeBlocks());
        $this->assertTrue($block->has(CodeBlock::LOADER));
    }

    public function testGetImageExtensionCodeBlock()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => true, 'image_extensions' => 'png', 'limit' => 100]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertCount(1, $config->getCodeBlocks());
        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
