<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Functional;

use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader\MockLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigGeneratorTest extends KernelTestCase
{
    public function testExternalExtensions(): void
    {
        static::bootKernel();

        /** @var MockLoader $mock_loader */
        $mock_loader = static::$kernel->getContainer()->get(MockLoader::class);

        /** @var ConfigGenerator $config_generator */
        $config_generator = static::$kernel->getContainer()->get(ConfigGenerator::class);

        $contiguration = $config_generator->getConfiguration();

        self::assertTrue($mock_loader->code_blocks_called);
        self::assertStringContainsString(MockLoader::BLOCK_CONTENT, $contiguration);
    }
}
