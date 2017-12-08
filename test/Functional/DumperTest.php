<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Functional;

use Hostnet\Component\Webpack\Asset\Dumper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
        $dumper = static::$kernel->getContainer()->get(Dumper::class);

        $dumper->dump();

        self::assertFileExists($this->dir . '/foo/public.js');
    }

    protected function tearDown()
    {
        `rm -rf $this->dir`;

        parent::tearDown();
    }
}
