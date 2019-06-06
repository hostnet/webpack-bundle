<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Tests\AbstractTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\TypeScriptLoader
 */
class TypeScriptLoaderTest extends AbstractTestCase
{
    public function testConfigTreeBuilder(): void
    {
        $tree = $this->createTreeBuilder('webpack');
        $node = $this->retrieveRootNode($tree, 'webpack')->children();

        TypeScriptLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('typescript', $config);
        self::assertArrayHasKey('enabled', $config['typescript']);
    }

    public function testGetCodeBlockDisabled(): void
    {
        $config = new TypeScriptLoader(['loaders' => ['typescript' => ['enabled' => false, 'loader' => 'ts']]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock(): void
    {
        $config = new TypeScriptLoader(['loaders' => ['typescript' => ['enabled' => true, 'loader' => 'ts']]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertTrue($block->has(CodeBlock::LOADER));
    }
}
