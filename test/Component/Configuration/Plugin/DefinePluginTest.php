<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Tests\AbstractTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Plugin\DefinePlugin
 */
class DefinePluginTest extends AbstractTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testConfigTreeBuilder(): void
    {
        $tree = $this->createTreeBuilder('webpack');
        $node = $this->retrieveRootNode($tree, 'webpack')->children();

        DefinePlugin::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);
    }

    public function testGetCodeBlock(): void
    {
        $config = new DefinePlugin([
            'plugins' => [
                'constants' => [
                    'foo' => 'bar',
                ],
            ],
        ]);

        $config->add('bar', 'baz');

        self::assertEquals(
            'new webpack.DefinePlugin({"foo":"bar","bar":"baz"})',
            $config->getCodeBlocks()[0]->get(CodeBlock::PLUGIN)
        );
    }
}
