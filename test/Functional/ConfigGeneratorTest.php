<?php
use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader\MockLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigGeneratorTest extends KernelTestCase
{
    public function testExternalExtensions()
    {
        static::bootKernel();

        /** @var $mock_loader MockLoader */
        $mock_loader = static::$kernel->getContainer()->get('bar.mock_loader');

        /** @var $config_generator ConfigGenerator */
        $config_generator = static::$kernel->getContainer()->get('hostnet_webpack.bridge.config_generator');

        $contiguration = $config_generator->getConfiguration();

        $this->assertTrue($mock_loader->code_blocks_called);
        $this->assertContains(MockLoader::BLOCK_CONTENT, $contiguration);
    }
}
