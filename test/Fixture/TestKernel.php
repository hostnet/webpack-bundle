<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Fixture\WebpackBundle;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Hostnet\Bundle\WebpackBundle\WebpackBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Hostnet\Fixture\WebpackBundle\Bundle\FooBundle\FooBundle(),
            new \Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle(),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
