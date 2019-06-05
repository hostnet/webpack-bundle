<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Bundle\WebpackBundle\WebpackBundle;
use Hostnet\Component\Webpack\Asset\Compiler;
use Hostnet\Component\Webpack\Asset\Tracker;
use Hostnet\Component\Webpack\Configuration\CodeBlockProviderInterface;
use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Component\Webpack\Profiler\Profiler;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle;
use Hostnet\Fixture\WebpackBundle\Bundle\FooBundle\FooBundle;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass
 */
class WebpackCompilerPassTest extends TestCase
{
    public function testPass(): void
    {
        $bundle      = new WebpackBundle();
        $container   = new ContainerBuilder();
        $extension   = $bundle->getContainerExtension();
        $fixture_dir = sprintf('%s/Fixture', \dirname(__DIR__, 2));

        $container->setParameter('kernel.bundles', ['FooBundle' => FooBundle::class, 'BarBundle' => BarBundle::class]);
        $container->setParameter('kernel.environment', 'dev');
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));
        $container->set('kernel', $this->prophesize(Kernel::class)->reveal());
        $container->set('filesystem', new Filesystem());
        $container->set('twig', $this->prophesize(Environment::class)->reveal());
        $container->set('twig.loader', $this->prophesize(FilesystemLoader::class)->reveal());
        $container->set('logger', $this->prophesize(LoggerInterface::class)->reveal());

        $code_block_provider = new Definition(CodeBlockProviderInterface::class);
        $code_block_provider->addTag('hostnet_webpack.config_extension');
        $container->setDefinition('webpack_extension', $code_block_provider);

        $bundle->build($container);

        $extension->load([
            'webpack' => [
                'node'    => ['node_modules_path' => $fixture_dir . '/node_modules'],
                'bundles' => ['FooBundle'],
                'resolve' => ['alias' => ['foo' => __DIR__, 'bar' => __DIR__ . '/fake']],
            ],
        ], $container);
        $container->compile();

        self::assertTrue($container->hasDefinition(Compiler::class));
        self::assertTrue($container->hasDefinition(Tracker::class));
        self::assertTrue($container->hasDefinition(ConfigGenerator::class));
        self::assertTrue($container->hasDefinition(Profiler::class));

        $config_generator_definition = $container->getDefinition(ConfigGenerator::class);
        self::assertTrue($config_generator_definition->hasMethodCall('addExtension'));

        $method_calls = $container->getDefinition(Tracker::class)->getMethodCalls();
        self::assertEquals([['addPath', [__DIR__]]], $method_calls);

        $process_definition = $container->getDefinition(Process::class);
        self::assertTrue($process_definition->hasMethodCall('setTimeout'));
        self::assertEquals(
            Configuration::DEFAULT_COMPILE_TIMEOUT_SECONDS,
            $process_definition->getMethodCalls()[0][1][0]
        );
    }

    public function testLoadNoWebpack(): void
    {
        $bundle      = new WebpackBundle();
        $container   = new ContainerBuilder();
        $extension   = $bundle->getContainerExtension();
        $fixture_dir = realpath(__DIR__ . '/../../Fixture');

        $container->setParameter('kernel.bundles', ['FooBundle' => FooBundle::class, 'BarBundle' => BarBundle::class]);
        $container->setParameter('kernel.environment', 'dev');
        $container->setParameter('kernel.root_dir', $fixture_dir);
        $container->setParameter('kernel.cache_dir', realpath($fixture_dir . '/cache'));

        $bundle->build($container);

        $extension->load([
         'webpack' => [
             'node'    => ['node_modules_path' => $fixture_dir],
             'bundles' => ['FooBundle'],
         ],
        ], $container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Webpack is not installed in path');

        $container->compile();
    }
}
