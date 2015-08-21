<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Config;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBridge\Configuration\Config\ResolveLoaderConfig
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class ResolveLoaderConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        ResolveLoaderConfig::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);
        $this->assertArrayHasKey('resolve_loader', $config);
    }

    public function testGetCodeBlock()
    {
        $config = new ResolveLoaderConfig([
            'node' => [
                'node_modules_path' => '/foo/bar'
            ],
            'resolve_loader' => [
                'root' => ['/tmp']
            ]
        ]);

        $this->assertTrue($config->getCodeBlocks()[0]->has(CodeBlock::RESOLVE_LOADER));
        $this->assertArrayHasKey('root', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE_LOADER));
    }
}
