<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\Plugin\ProvidePlugin
 */
class ProvidePluginTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        ProvidePlugin::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);
    }

    public function testGetCodeBlock()
    {
        $config = new ProvidePlugin([
            'plugins' => [
                'provides' => [
                    '$' => 'jquery',
                ],
            ],
        ]);

        $config->add('jQuery', 'jquery');

        self::assertEquals(
            'new webpack.ProvidePlugin({"$":"jquery","jQuery":"jquery"})',
            $config->getCodeBlocks()[0]->get(CodeBlock::PLUGIN)
        );
    }
}
