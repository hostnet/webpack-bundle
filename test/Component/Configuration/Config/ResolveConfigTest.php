<?php
namespace Hostnet\Component\WebpackBundle\Configuration\Config;

use Hostnet\Component\WebpackBundle\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\WebpackBundle\Configuration\Config\ResolveConfig
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class ResolveConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        ResolveConfig::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('resolve', $config);
        $this->assertArrayHasKey('root', $config['resolve']);
        $this->assertArrayHasKey('alias', $config['resolve']);
        $this->assertArrayHasKey('modules_directories', $config['resolve']);
        $this->assertArrayHasKey('fallback', $config['resolve']);
        $this->assertArrayHasKey('extensions', $config['resolve']);
    }

    public function testGetCodeBlock()
    {
        $config = new ResolveConfig([
            'node' => [
                'node_modules_path' => '/path/to/node_modules'
            ],
            'resolve' => [
                'root' => ['foobar.js'],
                'alias' => ['@Common' => 'common'],
                'modules_directories' => []
            ]
        ]);
        $config->addAlias('/foo/bar', '@FooBar');

        $this->assertTrue($config->getCodeBlocks()[0]->has(CodeBlock::RESOLVE));
        $this->assertArrayHasKey('root', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        $this->assertArrayHasKey('alias', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        $this->assertArrayHasKey('modulesDirectories', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        $this->assertArrayHasKey('@FooBar', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE)['alias']);
        $this->assertArrayHasKey('@Common', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE)['alias']);
    }
}
