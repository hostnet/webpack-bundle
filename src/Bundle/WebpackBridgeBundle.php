<?php
namespace Hostnet\Bundle\WebpackBridge;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class WebpackBridgeBundle
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackBridgeBundle extends Bundle
{
    /** {@inheritdoc} */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\WebpackCompilerPass());
    }

    /** {@inheritdoc} */
    public function getContainerExtension()
    {
        return new DependencyInjection\WebpackExtension();
    }
}
