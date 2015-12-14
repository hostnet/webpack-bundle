<?php
namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Plugin\ProvidePlugin
 * @author Harold Iedema <hiedema@hostnet.nl>
 * @author Guillaume Cavana <guillaume.cavana@gmail.com>
 */
class ProvidePluginTest extends \PHPUnit_Framework_TestCase
{
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
                    '$' => 'jquery'
                ]
            ]
        ]);

        $config->add('jQuery', 'jquery');

        $this->assertEquals(
            'new webpack.ProvidePlugin({"$":"jquery","jQuery":"jquery"})',
            $config->getCodeBlocks()[0]->get(CodeBlock::PLUGIN)
        );
    }
}
