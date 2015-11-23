<?php
use Hostnet\Component\Webpack\Asset\Dumper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class DumperTest extends KernelTestCase
{
    protected function setUp()
    {
        static::bootKernel();

        $this->dir = static::$kernel->getContainer()->getParameter('kernel.cache_dir') . '/../bundles';
    }

    public function testDump()
    {
        static::bootKernel();

        /** @var $dumper Dumper */
        $dumper = static::$kernel->getContainer()->get('hostnet_webpack.bridge.asset_dumper');

        $dumper->dump();

        $this->assertFileExists($this->dir . '/foo/public.js');
    }

    protected function tearDown()
    {
        `rm -rf $this->dir`;

        parent::tearDown();
    }
}
