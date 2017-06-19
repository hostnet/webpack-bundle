<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\WebpackBundle
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackBundleTest extends TestCase
{
    public function testBuild()
    {
        $bundle    = new WebpackBundle();
        $container = new ContainerBuilder();
        self::assertInstanceOf(DependencyInjection\WebpackExtension::class, $bundle->getContainerExtension());
        $bundle->build($container);

        // Since sf 3.3, there are symfony passes in the list, so we can't assert for only instances of
        // WebpackCompilerPass anymore.
        self::assertNotEmpty($container->getCompilerPassConfig()->getBeforeOptimizationPasses());

        $found = false;
        foreach ($container->getCompilerPassConfig()->getBeforeOptimizationPasses() as $pass) {
            if ($pass instanceof WebpackCompilerPass) {
                $found = true;
            }
        }

        self::assertTrue($found);
    }
}
