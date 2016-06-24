<?php
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Component\Webpack\Configuration\Loader\CSSLoader;
use Hostnet\Component\Webpack\Configuration\Plugin\DefinePlugin;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration
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
        $this->assertArrayHasKey('compile_timeout', $final);

        $this->assertArrayHasKey('binary', $final['node']);
        $this->assertArrayHasKey('win32', $final['node']['binary']);
        $this->assertArrayHasKey('win64', $final['node']['binary']);
        $this->assertArrayHasKey('linux_x32', $final['node']['binary']);
        $this->assertArrayHasKey('linux_x64', $final['node']['binary']);
        $this->assertArrayHasKey('darwin', $final['node']['binary']);
        $this->assertArrayHasKey('fallback', $final['node']['binary']);
    }
}
