<?php
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Bundle\WebpackBundle\WebpackBundle;
use Hostnet\Component\Webpack\Configuration\CodeBlockProviderInterface;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle;
use Hostnet\Fixture\WebpackBundle\Bundle\FooBundle\FooBundle;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass
 */
class WebpackCompilerPassTest extends TestCase
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
        $container->set('templating.finder', $this->getMockBuilder(TemplateFinderInterface::class)->getMock());
        $container->set('twig', $this->getMockBuilder(\Twig_Environment::class)->disableOriginalConstructor()->getMock());
        $container->set('twig.loader', $this->getMockBuilder(\Twig_Loader_Filesystem::class)->disableOriginalConstructor()->getMock());
        $container->set('logger', $this->getMockBuilder(LoggerInterface::class)->getMock());

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

        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_compiler'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_tracker'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.config_generator'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.profiler'));

        $method_calls = $container->getDefinition('hostnet_webpack.bridge.config_generator')->getMethodCalls();
        self::assertArraySubset([['addExtension', [new Reference('webpack_extension')]]], $method_calls);

        $method_calls = $container->getDefinition('hostnet_webpack.bridge.asset_tracker')->getMethodCalls();
        self::assertEquals([['addPath', [__DIR__]]], $method_calls);

        $process_definition = $container->getDefinition('hostnet_webpack.bridge.compiler_process');
        self::assertTrue($process_definition->hasMethodCall('setTimeout'));
        self::assertEquals(
            Configuration::DEFAULT_COMPILE_TIMEOUT_SECONDS,
            $process_definition->getMethodCalls()[0][1][0]
        );
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
        $container->set('templating.finder', $this->getMockBuilder(TemplateFinderInterface::class)->getMock());
        $container->set('twig', $this->getMockBuilder(\Twig_Environment::class)->disableOriginalConstructor()->getMock());
        $container->set('logger', $this->getMockBuilder(LoggerInterface::class)->getMock());

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

        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_compiler'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.asset_tracker'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.config_generator'));
        self::assertTrue($container->hasDefinition('hostnet_webpack.bridge.profiler'));
    }
}
