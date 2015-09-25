<?php
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackExtension
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

        // This should not fail.
        $extension->load([], $container);
    }
}
