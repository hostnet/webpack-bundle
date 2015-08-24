<?php
namespace Hostnet\Bundle\WebpackBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class WebpackBundle
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackBundle extends Bundle
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
