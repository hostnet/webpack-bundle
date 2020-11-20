<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\WebpackExtension
 */
class WebpackExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testAlias(): void
    {
        self::assertEquals(Configuration::CONFIG_ROOT, (new WebpackExtension())->getAlias());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLoadNoConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new WebpackExtension();

        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.environment', 'dev');

        // This should not fail.
        $extension->load([], $container);
    }
}
