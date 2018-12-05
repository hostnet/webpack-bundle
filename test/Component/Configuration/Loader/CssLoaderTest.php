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
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\CssLoader
 */
class CssLoaderTest extends AbstractTestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = $this->createTreeBuilder('webpack');
        $node = $this->retrieveRootNode($tree, 'webpack')->children();

        CssLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('css', $config);
        self::assertArrayHasKey('enabled', $config['css']);
        self::assertArrayHasKey('all_chunks', $config['css']);
        self::assertArrayHasKey('filename', $config['css']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new CssLoader(['loaders' => ['css' => ['enabled' => false]]]);

        self::assertFalse($config->getCodeBlocks()[0]->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlockEnabledDefaults()
    {
        $configs = (new CssLoader(['loaders' => ['css' => ['enabled' => true]]]))->getCodeBlocks();

        self::assertTrue($configs[0]->has(CodeBlock::LOADER));
        self::assertFalse($configs[0]->has(CodeBlock::HEADER));
        self::assertFalse($configs[0]->has(CodeBlock::PLUGIN));
    }

    public function testGetCodeBlockEnabledCommonsChunk()
    {
        $configs = (new CssLoader([
            'output'  => ['common_id' => 'foobar'],
            'loaders' => ['css' => ['enabled' => true, 'filename' => 'blaat', 'all_chunks' => true]],
        ]))->getCodeBlocks();

        self::assertTrue($configs[0]->has(CodeBlock::LOADER));
        self::assertTrue($configs[0]->has(CodeBlock::HEADER));
        self::assertTrue($configs[0]->has(CodeBlock::PLUGIN));
        self::assertTrue($configs[1]->has(CodeBlock::PLUGIN));
    }
}
