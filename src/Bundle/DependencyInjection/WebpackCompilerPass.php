<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Hostnet\Component\Webpack\Asset\Compiler;
use Hostnet\Component\Webpack\Asset\Dumper;
use Hostnet\Component\Webpack\Asset\Tracker;
use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Process\Process;

class WebpackCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $asset_tracker   = $container->getDefinition(Tracker::class);
        $bundles         = $container->getParameter('kernel.bundles');
        $config          = $container->getParameter('hostnet_webpack_config');
        $tracked_bundles = $config['bundles'];
        $asset_res_path  = 'Resources' . DIRECTORY_SEPARATOR . 'assets';
        $public_res_path = 'Resources' . DIRECTORY_SEPARATOR . 'public';
        $public_path     = rtrim($config['output']['public_path'], '\\/');
        $dump_path       = rtrim($config['output']['dump_path'], '\\/');
        $path            = rtrim($config['output']['path'], '\\/');
        $web_dir         = rtrim(substr($path, 0, \strlen($path) - \strlen($public_path)), '/\\');
        $bundle_paths    = [];

        // add all configured bundles to the tracker
        foreach ($bundles as $name => $class) {
            if (false === \in_array($name, $tracked_bundles, false)) {
                continue;
            }

            $bundle_paths[$name] = realpath(\dirname((new \ReflectionClass($class))->getFileName()));
        }

        $asset_tracker->replaceArgument(3, $asset_res_path);
        $asset_tracker->replaceArgument(4, $path);
        $asset_tracker->replaceArgument(5, $bundle_paths);

        // add all aliases to the tracker
        if (isset($config['resolve']['alias']) && \is_array($config['resolve']['alias'])) {
            foreach ($config['resolve']['alias'] as $alias_path) {
                if (!file_exists($alias_path)) {
                    continue;
                }
                $asset_tracker->addMethodCall('addPath', [$alias_path]);
            }
        }

        // Configure the compiler process.
        $env_vars = [
            'PATH'      => getenv('PATH'),
            'NODE_PATH' => $config['node']['node_modules_path'],
        ];

        $container
            ->getDefinition(Dumper::class)
            ->replaceArgument(2, $bundle_paths)
            ->replaceArgument(3, $public_res_path)
            ->replaceArgument(4, $dump_path);

        $container
            ->getDefinition(Compiler::class)
            ->replaceArgument(6, $config['bundles']);

        $container
            ->getDefinition(TwigExtension::class)
            ->replaceArgument(1, $web_dir)
            ->replaceArgument(2, $public_path)
            ->replaceArgument(3, str_replace($web_dir, '', $dump_path))
            ->replaceArgument(4, sprintf('%s/%s.js', $public_path, $config['output']['common_id']))
            ->replaceArgument(5, sprintf('%s/%s.css', $public_path, $config['output']['common_id']));

        // Ensure webpack is installed in the given (or detected) node_modules directory.
        if (false === ($webpack = realpath($config['node']['node_modules_path'] . '/webpack/bin/webpack.js'))) {
            throw new \RuntimeException(
                sprintf(
                    'Webpack is not installed in path "%s".',
                    $config['node']['node_modules_path']
                )
            );
        }

        $process_definition = $container
            ->getDefinition(Process::class)
            ->replaceArgument(0, [$config['node']['binary'] . ' ' . $webpack])
            ->replaceArgument(1, $container->getParameter('kernel.cache_dir'))
            ->addMethodCall('setTimeout', [$config['compile_timeout']]);

        $builder_definition   = $container->getDefinition(ConfigGenerator::class);
        $config_extension_ids = array_keys($container->findTaggedServiceIds('hostnet_webpack.config_extension'));
        foreach ($config_extension_ids as $id) {
            $builder_definition->addMethodCall('addExtension', [new Reference($id)]);
        }

        // Unfortunately, we need to specify some additional environment variables to pass to the compiler process. We
        // need this because there is a big chance that populating the $_ENV variable is disabled on most machines.
        // FIXME http://stackoverflow.com/questions/32125810/windows-symfony2-process-crashes-when-passing-env-variables
        // @codeCoverageIgnoreStart
        if (stripos(PHP_OS, 'WIN') === 0) {
            $env_vars['COMSPEC']            = getenv('COMSPEC');
            $env_vars['WINDIR']             = getenv('WINDIR');
            $env_vars['COMMONPROGRAMW6432'] = getenv('COMMONPROGRAMW6432');
            $env_vars['COMPUTERNAME']       = getenv('COMPUTERNAME');
            $env_vars['TMP']                = getenv('TMP');

            $process_definition->addMethodCall('setEnhanceWindowsCompatibility', [true]);
            // $process_definition->addMethodCall('setEnv', [$env_vars]);
        } else {
            $process_definition->addMethodCall('setEnv', [$env_vars]);
        }
        // @codeCoverageIgnoreEnd
    }
}
