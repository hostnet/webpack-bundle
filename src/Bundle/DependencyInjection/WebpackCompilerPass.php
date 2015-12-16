<?php
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackCompilerPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $asset_tracker   = $container->getDefinition('hostnet_webpack.bridge.asset_tracker');
        $bundles         = $container->getParameter('kernel.bundles');
        $config          = $container->getParameter('hostnet_webpack_config');
        $tracked_bundles = $config['bundles'];
        $asset_res_path  = 'Resources' . DIRECTORY_SEPARATOR . 'assets';
        $public_res_path = 'Resources' . DIRECTORY_SEPARATOR . 'public';
        $public_path     = rtrim($config['output']['public_path'], '\\/');
        $dump_path       = rtrim($config['output']['dump_path'], '\\/');
        $path            = rtrim($config['output']['path'], '\\/');
        $web_dir         = rtrim(substr($path, 0, strlen($path) - strlen($public_path)), '/\\');
        $bundle_paths    = [];

        foreach ($bundles as $name => $class) {
            if (! in_array($name, $tracked_bundles)) {
                continue;
            }

            $bundle_paths[$name] = realpath(dirname((new \ReflectionClass($class))->getFileName()));
        }

        $asset_tracker->replaceArgument(4, $asset_res_path);
        $asset_tracker->replaceArgument(5, $bundle_paths);

        // Configure the compiler process.
        $env_vars = [
            'PATH'      => getenv('PATH'),
            'NODE_PATH' => $config['node']['node_modules_path']
        ];

        $container
            ->getDefinition('hostnet_webpack.bridge.asset_dumper')
            ->replaceArgument(2, $bundle_paths)
            ->replaceArgument(3, $public_res_path)
            ->replaceArgument(4, $dump_path);

        $container
            ->getDefinition('hostnet_webpack.bridge.asset_compiler')
            ->replaceArgument(6, $config['bundles']);

        $container
            ->getDefinition('hostnet_webpack.bridge.twig_extension')
            ->replaceArgument(0, $web_dir)
            ->replaceArgument(1, $public_path)
            ->replaceArgument(2, str_replace($web_dir, '', $dump_path));

        // Ensure webpack is installed in the given (or detected) node_modules directory.
        if (false === ($webpack = realpath($config['node']['node_modules_path'] . '/webpack/bin/webpack.js'))) {
            throw new \RuntimeException(sprintf('Webpack is not installed in path "%s".', $config['node']['node_modules_path']));
        }

        $process_definition = $container
            ->getDefinition('hostnet_webpack.bridge.compiler_process')
            ->replaceArgument(0, $config['node']['binary'] . ' ' . $webpack)
            ->replaceArgument(1, $container->getParameter('kernel.cache_dir'));

        $builder_definition   = $container->getDefinition('hostnet_webpack.bridge.config_generator');
        $config_extension_ids = array_keys($container->findTaggedServiceIds('hostnet_webpack.config_extension'));
        foreach ($config_extension_ids as $id) {
            $builder_definition->addMethodCall('addExtension', [new Reference($id)]);
        }

        // Unfortunately, we need to specify some additional environment variables to pass to the compiler process. We
        // need this because there is a big chance that populating the $_ENV variable is disabled on most machines.
        // FIXME http://stackoverflow.com/questions/32125810/windows-symfony2-process-crashes-when-passing-env-variables
        // @codeCoverageIgnoreStart
        if (strpos(strtoupper(php_uname('s')), 'WIN') === 0) {
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
