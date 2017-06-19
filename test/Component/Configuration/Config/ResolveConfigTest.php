<?php
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Config;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Config\ResolveConfig
 */
class ResolveConfigTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        ResolveConfig::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('resolve', $config);
        self::assertArrayHasKey('root', $config['resolve']);
        self::assertArrayHasKey('alias', $config['resolve']);
        self::assertArrayHasKey('modules_directories', $config['resolve']);
        self::assertArrayHasKey('fallback', $config['resolve']);
        self::assertArrayHasKey('extensions', $config['resolve']);
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

        self::assertTrue($config->getCodeBlocks()[0]->has(CodeBlock::RESOLVE));
        self::assertArrayHasKey('root', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        self::assertArrayHasKey('alias', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        self::assertArrayHasKey('modulesDirectories', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE));
        self::assertArrayHasKey('@FooBar', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE)['alias']);
        self::assertArrayHasKey('@Common', $config->getCodeBlocks()[0]->get(CodeBlock::RESOLVE)['alias']);
    }
}
