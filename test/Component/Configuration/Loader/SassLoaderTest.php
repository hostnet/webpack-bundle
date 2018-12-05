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
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\SassLoader
 */
class SassLoaderTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        SassLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('sass', $config);
        self::assertArrayHasKey('enabled', $config['sass']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new SassLoader(['loaders' => ['sass' => ['enabled' => false]]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new SassLoader(['loaders' => ['sass' => ['enabled' => true]]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertTrue($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlockWithIncludePaths()
    {
        $config = new SassLoader([
            'loaders' => [
                'sass' => [
                    'enabled'       => true,
                    'include_paths' => ['path1', 'path2'],
                    'filename'      => 'testfile',
                    'all_chunks'    => true,
                ],
            ],
        ]);
        $block  = $config->getCodeBlocks()[0];

        self::assertTrue($block->has(CodeBlock::ROOT));
        self::assertTrue($block->has(CodeBlock::HEADER));
    }
}
