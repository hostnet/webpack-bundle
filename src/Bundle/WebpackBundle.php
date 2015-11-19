<?php
namespace Hostnet\Bundle\WebpackBundle;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackCompilerPass;
use Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackExtension;
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

        $container->addCompilerPass(new WebpackCompilerPass());
    }

    /** {@inheritdoc} */
    public function getContainerExtension()
    {
        return new WebpackExtension();
    }
}
