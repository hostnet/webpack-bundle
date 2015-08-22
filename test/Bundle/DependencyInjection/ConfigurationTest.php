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

        $this->assertArrayHasKey('node', $final);

        $this->assertArrayHasKey('binary', $final['node']);
        $this->assertArrayHasKey('win32', $final['node']['binary']);
        $this->assertArrayHasKey('win64', $final['node']['binary']);
        $this->assertArrayHasKey('linux_x32', $final['node']['binary']);
        $this->assertArrayHasKey('linux_x64', $final['node']['binary']);
        $this->assertArrayHasKey('darwin', $final['node']['binary']);
        $this->assertArrayHasKey('fallback', $final['node']['binary']);
    }
}
