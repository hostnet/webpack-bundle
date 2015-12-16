<?php
use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader\MockLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigGeneratorTest extends KernelTestCase
{
    public function testExternalExtensions()
    {
        static::bootKernel();

        /** @var $mockLoader MockLoader */
        $mockLoader = static::$kernel->getContainer()->get('bar.mock_loader');

        /** @var $configGenerator ConfigGenerator */
        $configGenerator = static::$kernel->getContainer()->get('hostnet_webpack.bridge.config_generator');

        $contiguration = $configGenerator->getConfiguration();

        $this->assertTrue($mockLoader->getCodeBlocksCalled);
        $this->assertContains(MockLoader::BLOCK_CONTENT, $contiguration);
    }
}
