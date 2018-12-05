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
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\UrlLoader
 */
class UrlLoaderTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        UrlLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);
        self::assertArrayHasKey('url', $config);
        self::assertArrayHasKey('font_extensions', $config['url']);
        self::assertArrayHasKey('image_extensions', $config['url']);
        self::assertArrayHasKey('enabled', $config['url']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new UrlLoader(['loaders' => ['url' => ['enabled' => false]]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetFontExtensionCodeBlock()
    {
        $config = new UrlLoader([
            'loaders' => ['url' => ['enabled' => true, 'font_extensions' => 'svg,woff', 'limit' => 100]],
        ]);
        $block  = $config->getCodeBlocks()[0];

        self::assertCount(2, $config->getCodeBlocks());
        self::assertTrue($block->has(CodeBlock::LOADER));
    }

    public function testGetImageExtensionCodeBlock()
    {
        $config = new UrlLoader([
            'loaders' => ['url' => ['enabled' => true, 'image_extensions' => 'png', 'limit' => 100]],
        ]);
        $block  = $config->getCodeBlocks()[0];

        self::assertCount(1, $config->getCodeBlocks());
        self::assertTrue($block->has(CodeBlock::LOADER));
    }
}
