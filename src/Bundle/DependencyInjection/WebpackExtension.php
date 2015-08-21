<?php
namespace Hostnet\Bundle\WebpackBridge\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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
        (new YamlFileLoader($container, (new FileLocator(__DIR__ . '/../Resources/config'))))->load('webpack.yml');

        // Retrieve all configuration entities
        $bundles              = $container->getParameter('kernel.bundles');
        $builder_definition   = $container->getDefinition('hostnet_webpack.bridge.config_generator');;
        $config_extension_ids = array_keys($container->findTaggedServiceIds('hostnet_webpack.config_extension'));
        $config_definitions   = [];
        $config_class_names   = [];

        foreach ($config_extension_ids as $id) {
            $config_definitions[$id] = $container->getDefinition($id);
            $config_class_names[$id] = $container->getDefinition($id)->getClass();
        }

        $config = $this->processConfiguration(new Configuration(array_keys($bundles), $config_class_names), $config);

        // Parse application config into the config generator
        foreach ($config_definitions as $id => $definition) {
            /* @var $definition Definition */
            $builder_definition->addMethodCall('addExtension', [new Reference($id)]);
            $definition->addArgument($config);
        }

        // Pass the configuration to a container parameter for the CompilerPass and profiler to read.
        $container->setParameter('hostnet_webpack_config', $config);
    }
}
