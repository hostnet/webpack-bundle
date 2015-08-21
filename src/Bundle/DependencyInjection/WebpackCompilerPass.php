<?php
namespace Hostnet\Bundle\WebpackBridge\DependencyInjection;

use Hostnet\Bundle\WebpackBridge\EventListener\RequestListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
        $bundle_paths    = [];

        foreach ($bundles as $name => $class) {
            if (! in_array($name, $tracked_bundles)) {
                continue;
            }

            $bundle_paths[$name] = realpath(dirname((new \ReflectionClass($class))->getFileName()));
        }

        $asset_tracker->replaceArgument(4, $config['resolve']['asset_path']);
        $asset_tracker->replaceArgument(5, $bundle_paths);

        // Configure the compiler process.
        $env_vars = [
            'PATH'      => getenv('PATH'),
            'NODE_PATH' => ! empty($config['node']['node_modules_path'])
                ? $config['node']['node_modules_path']
                : getenv('NODE_PATH')
        ];

        $container
            ->getDefinition('hostnet_webpack.bridge.asset_compiler')
            ->replaceArgument(6, $config['bundles']);

        $container
            ->getDefinition('hostnet_webpack.bridge.twig_extension')
            ->replaceArgument(0, $config['output']['public_path']);

        // Enable the request listener if we're running in debug mode.
        if ($container->getParameter('kernel.debug') === true) {
            $container->setDefinition(
                'hostnet_webpack.bridge.request_listener',
                (new Definition(RequestListener::class, [
                    new Reference('hostnet_webpack.bridge.asset_tracker'),
                    new Reference('hostnet_webpack.bridge.asset_compiler')
                ]))->addTag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onRequest'])
            );
        }

        $process_definition = $container
            ->getDefinition('hostnet_webpack.bridge.compiler_process')
            ->replaceArgument(0, $config['node']['binary'])
            ->replaceArgument(1, $container->getParameter('kernel.cache_dir'));

        // Unfortunately, we need to specify some additional environment variables to pass to the compiler process. We
        // need this because there is a big chance that populating the $_ENV variable is disabled on most machines.
        // FIXME http://stackoverflow.com/questions/32125810/windows-symfony2-process-crashes-when-passing-env-variables
        // @codeCoverageIgnoreStart
        if ($is_win = (strpos(strtoupper(php_uname('s')), 'WIN') === 0)) {
            $env_vars['COMSPEC']            = getenv('COMSPEC');
            $env_vars['WINDIR']             = getenv('WINDIR');
            $env_vars['COMMONPROGRAMW6432'] = getenv('COMMONPROGRAMW6432');
            $env_vars['COMPUTERNAME']       = getenv('COMPUTERNAME');
            $env_vars['TMP']                = getenv('TMP');

            $process_definition->addMethodCall('setEnhanceWindowsCompatibility', [$is_win ? true : false]);
        } else {
            $process_definition->addMethodCall('setEnv', [$env_vars]);
        }
        // @codeCoverageIgnoreEnd
    }
}
