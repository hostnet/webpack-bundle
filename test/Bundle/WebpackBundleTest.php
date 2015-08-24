<?php
namespace Hostnet\Bundle\WebpackBundle;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\WebpackBundle
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $bundle    = new WebpackBundle();
        $container = new ContainerBuilder();
        $this->assertInstanceOf(DependencyInjection\WebpackExtension::class, $bundle->getContainerExtension());
        $bundle->build($container);

        $this->assertContainsOnlyInstancesOf(WebpackCompilerPass::class, $container->getCompilerPassConfig()->getBeforeOptimizationPasses());
    }
}
