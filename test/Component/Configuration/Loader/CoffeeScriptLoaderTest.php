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
 * @covers \Hostnet\Component\Webpack\Configuration\Loader\CoffeeScriptLoader
 */
class CoffeeScriptLoaderTest extends AbstractTestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = $this->createTreeBuilder('webpack');
        $node = $this->retrieveRootNode($tree, 'webpack')->children();

        CoffeeScriptLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('coffee', $config);
        self::assertArrayHasKey('enabled', $config['coffee']);
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new CoffeeScriptLoader(['loaders' => ['coffee' => ['enabled' => false, 'loader' => 'coffee']]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlock()
    {
        $config = new CoffeeScriptLoader(['loaders' => ['coffee' => ['enabled' => true, 'loader' => 'coffee']]]);
        $block  = $config->getCodeBlocks()[0];

        self::assertTrue($block->has(CodeBlock::LOADER));
    }
}
