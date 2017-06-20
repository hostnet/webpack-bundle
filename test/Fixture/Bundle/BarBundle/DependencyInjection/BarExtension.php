<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BarExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setDefinition(
            'bar.mock_loader',
            (new Definition('Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader\MockLoader'))
                ->addTag('hostnet_webpack.config_extension')
        );
    }
}
