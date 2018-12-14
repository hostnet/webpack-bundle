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
 * @covers \Hostnet\Component\Webpack\Configuration\Plugin\ProvidePlugin
 */
class ProvidePluginTest extends AbstractTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testConfigTreeBuilder()
    {
        $tree = $this->createTreeBuilder('webpack');
        $node = $this->retrieveRootNode($tree, 'webpack')->children();

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
