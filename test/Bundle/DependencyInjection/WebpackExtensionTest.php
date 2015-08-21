<?php
namespace Hostnet\Bundle\WebpackBridge\DependencyInjection;

use Hostnet\Component\WebpackBridge\Configuration\Loader\CSSLoader;
use Hostnet\Component\WebpackBridge\Configuration\Plugin\DefinePlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBridge\DependencyInjection\WebpackExtension
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testAlias()
    {
        $this->assertEquals(Configuration::CONFIG_ROOT, (new WebpackExtension())->getAlias());
    }

    public function testLoadNoConfig()
    {
        $container = new ContainerBuilder();
        $extension = new WebpackExtension();

        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.debug', true);

        $extension->load([], $container);

        // Do stuff...
        // print_r($container->getDefinitions());
    }
}
