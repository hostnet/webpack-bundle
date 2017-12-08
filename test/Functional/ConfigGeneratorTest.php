<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Functional;

use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader\MockLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigGeneratorTest extends KernelTestCase
{
    public function testExternalExtensions()
    {
        static::bootKernel();

        /** @var $mock_loader MockLoader */
        $mock_loader = static::$kernel->getContainer()->get(MockLoader::class);

        /** @var $config_generator ConfigGenerator */
        $config_generator = static::$kernel->getContainer()->get(ConfigGenerator::class);

        $contiguration = $config_generator->getConfiguration();

        self::assertTrue($mock_loader->code_blocks_called);
        self::assertContains(MockLoader::BLOCK_CONTENT, $contiguration);
    }
}
