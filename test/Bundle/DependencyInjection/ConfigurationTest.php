<?php
namespace Hostnet\Bundle\WebpackBridge\DependencyInjection;

use Hostnet\Component\WebpackBridge\Configuration\Loader\CSSLoader;
use Hostnet\Component\WebpackBridge\Configuration\Plugin\DefinePlugin;

/**
 * @covers \Hostnet\Bundle\WebpackBridge\DependencyInjection\Configuration
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $config = new Configuration([], [DefinePlugin::class, CSSLoader::class]);
        $tree   = $config->getConfigTreeBuilder();
        $final  = $tree->buildTree()->finalize([]);

        // TODO: assert more config keys.
        $this->assertArrayHasKey('node', $final);
    }
}
