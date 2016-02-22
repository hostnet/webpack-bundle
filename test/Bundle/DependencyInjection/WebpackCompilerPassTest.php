<?php
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Bundle\WebpackBundle\WebpackBundle;
use Hostnet\Component\Webpack\Configuration\CodeBlockProviderInterface;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle;
use Hostnet\Fixture\WebpackBundle\Bundle\FooBundle\FooBundle;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;

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
        $container->setParameter('kernel.environment', 'dev');
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));
        $container->set('filesystem', new Filesystem());
        $container->set('templating.finder', $this->getMock(TemplateFinderInterface::class));
        $container->set('twig', $this->getMockBuilder(\Twig_Environment::class)->disableOriginalConstructor()->getMock());
        $container->set('logger', $this->getMock(LoggerInterface::class));


        $container->setDefinition(
            'webpack_extension',
            (new Definition(CodeBlockProviderInterface::class))
                ->addTag('hostnet_webpack.config_extension')
        );

        $bundle->build($container);

        $extension->load([
            'webpack' => [
                'node' => [
                    'node_modules_path' => $fixture_dir . '/node_modules',
                ],
                'bundles' => ['FooBundle'],
                'resolve' => ['alias' => ['foo' => __DIR__, 'bar' => __DIR__ . '/fake']],
            ]
        ], $container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_compiler'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_tracker'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.config_generator'));
        $this->assertTrue($container->hasDefinition('hostnet_webpack.bridge.profiler'));

        $method_calls = $container->getDefinition('hostnet_webpack.bridge.config_generator')->getMethodCalls();
        $this->assertArraySubset([['addExtension', [new Reference('webpack_extension')]]], $method_calls);

        $method_calls = $container->getDefinition('hostnet_webpack.bridge.asset_tracker')->getMethodCalls();
        $this->assertEquals([['addPath', [__DIR__]]], $method_calls);
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
        $container->setParameter('kernel.environment', 'dev');
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));
        $container->set('filesystem', new Filesystem());
        $container->set('templating.finder', $this->getMock(TemplateFinderInterface::class));
        $container->set('twig', $this->getMockBuilder(\Twig_Environment::class)->disableOriginalConstructor()->getMock());
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
