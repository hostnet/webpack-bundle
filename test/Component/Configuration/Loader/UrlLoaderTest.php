<?php
namespace Hostnet\Component\WebpackBundle\Configuration\Loader;

use Hostnet\Component\WebpackBundle\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBundle\Configuration\Loader\UrlLoader
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
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => true]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
