<?php
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class WebpackExtension
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackExtension extends Extension
{
    /** {@inheritdoc} */
    public function load(array $config, ContainerBuilder $container)
    {
        // Load configuration
        $loader = new YamlFileLoader($container, (new FileLocator(__DIR__ . '/../Resources/config')));
        $loader->load('webpack.yml');

        // Enable the request listener if we're running in dev.
        if ($container->getParameter('kernel.environment') === 'dev') {
            $loader->load('dev.yml');
        }

        // Retrieve all configuration entities
        $config_extension_ids = array_keys($container->findTaggedServiceIds('hostnet_webpack.config_extension'));
        $config_definitions   = [];

        foreach ($config_extension_ids as $id) {
            $config_definitions[$id] = $container->getDefinition($id);
        }

        $config = $this->processConfiguration($this->getConfiguration($config, $container), $config);
        $container->addResource(new FileResource((new \ReflectionClass(Configuration::class))->getFileName()));

        // Select the correct node binary for the platform we're currently running on.
        $config['node']['binary']            = $config['node']['binary'][$this->getPlatformKey()];
        $config['node']['node_modules_path'] = ! empty($config['node']['node_modules_path'])
            ? $config['node']['node_modules_path']
            : getenv('NODE_PATH');

        // Parse application config into the config generator
        foreach ($config_definitions as $id => $definition) {
            /* @var $definition Definition */
            $definition->addArgument($config);
        }

        // Pass the configuration to a container parameter for the CompilerPass and profiler to read.
        $container->setParameter('hostnet_webpack_config', $config);
    }

    /**
     * Returns the platform key to take the node binary configuration from.
     *
     * A little caveat here: This will not give you the actual architecture of the machine, but rather if PHP is running
     * in 32 or 64-bit mode. Unfortunately there is no way figuring this out without invoking external system processes.
     *
     * @codeCoverageIgnore The outcome and coverage of this method solely depends on which platform PHP is running on.
     * @return string
     */
    private function getPlatformKey()
    {
        $platform = php_uname('s');

        if (strtoupper(substr($platform, 0, 3)) === 'WIN') {
            return PHP_INT_SIZE === 8 ? 'win64' : 'win32';
        }
        if (strtoupper(substr($platform, 0, 5)) === 'LINUX') {
            return PHP_INT_SIZE === 8 ? 'linux_x64' : 'linux_x32';
        }
        if (strtoupper(substr($platform, 0, 6)) === 'DARWIN') {
            return 'darwin';
        }

        return 'fallback';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles              = $container->getParameter('kernel.bundles');
        $config_extension_ids = array_keys($container->findTaggedServiceIds('hostnet_webpack.config_extension'));
        $config_class_names   = [];

        foreach ($config_extension_ids as $id) {
            $config_class_names[$id] = $container->getDefinition($id)->getClass();
        }

        $configuration = new Configuration(array_keys($bundles), $config_class_names);

        $container->addResource(new FileResource((new \ReflectionClass(get_class($configuration)))->getFileName()));

        return $configuration;
    }

    /** {@inheritdoc} */
    public function getNamespace()
    {
        return 'http://hostnet.nl/schema/dic/webpack';
    }
}
