<?php
namespace Hostnet\Bundle\WebpackBridge;

use Hostnet\Bundle\WebpackBridge\DependencyInjection\WebpackCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBridge\WebpackBridgeBundle
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackBridgeBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $bundle    = new WebpackBridgeBundle();
        $container = new ContainerBuilder();
        $this->assertInstanceOf(DependencyInjection\WebpackExtension::class, $bundle->getContainerExtension());
        $bundle->build($container);

        $this->assertContainsOnlyInstancesOf(WebpackCompilerPass::class, $container->getCompilerPassConfig()->getBeforeOptimizationPasses());
    }
}
