<?php
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Bundle\WebpackBundle\WebpackBundle;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle;
use Hostnet\Fixture\WebpackBundle\Bundle\FooBundle\FooBundle;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testPass()
    {
        $bundle      = new WebpackBundle();
        $container   = new ContainerBuilder();
        $extension   = $bundle->getContainerExtension();
        $fixture_dir = realpath(__DIR__ . '/../../Fixture');

        $container->setParameter('kernel.bundles', ['FooBundle' => FooBundle::class, 'BarBundle' => BarBundle::class]);
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));
        $container->set('templating.finder', $this->getMock(TemplateFinderInterface::class));
        $container->set('twig', $this->getMock(\Twig_Environment::class));
        $container->set('logger', $this->getMock(LoggerInterface::class));

        $bundle->build($container);

        $extension->load([
            'webpack' => [
                'node' => [
                    'node_modules_path' => $fixture_dir . '/node_modules'
                ],
                'bundles' => ['FooBundle']
            ]
        ], $container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_compiler'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_tracker'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.config_generator'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.profiler'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Webpack is not installed in path
     */
    public function testLoadNoWebpack()
    {
        $bundle      = new WebpackBundle();
        $container   = new ContainerBuilder();
        $extension   = $bundle->getContainerExtension();
        $fixture_dir = realpath(__DIR__ . '/../../Fixture');

        $container->setParameter('kernel.bundles', ['FooBundle' => FooBundle::class, 'BarBundle' => BarBundle::class]);
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));
        $container->set('templating.finder', $this->getMock(TemplateFinderInterface::class));
        $container->set('twig', $this->getMock(\Twig_Environment::class));
        $container->set('logger', $this->getMock(LoggerInterface::class));

        $bundle->build($container);

        $extension->load([
            'webpack' => [
                'node' => [
                    'node_modules_path' => $fixture_dir
                ],
                'bundles' => ['FooBundle']
            ]
        ], $container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_compiler'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_tracker'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.config_generator'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.profiler'));
    }
}
