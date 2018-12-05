<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\BabelLoader
 */
class BabelLoaderTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder('webpack');
        $node = $tree->getRootNode()->children();

        BabelLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('babel', $config);
        self::assertArrayHasKey('enabled', $config['babel']);
    }

    public function testGetCodeBlockDefault()
    {
        $config = new BabelLoader();
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new BabelLoader(['loaders' => ['babel' => ['enabled' => false]]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new BabelLoader(['loaders' => ['babel' => ['enabled' => true]]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertTrue($block->has(CodeBlock::LOADER));
    }
}
