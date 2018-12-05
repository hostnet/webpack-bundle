<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Functional;

use Hostnet\Component\Path\Path;
use Hostnet\Component\Webpack\Asset\Dumper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DumperTest extends KernelTestCase
{
    /**
     * @var string
     */
    private $dir;

    protected function setUp()
    {
        static::bootKernel();

        $this->dir = Path::BASE_DIR . '/test/Fixture/cache/bundles';
    }

    public function testDump()
    {
        static::bootKernel();

        /** @var Dumper $dumper */
        $dumper = static::$kernel->getContainer()->get(Dumper::class);
        $dumper->dump();

        self::assertFileExists($this->dir . '/foo/public.js');
    }

    protected function tearDown()
    {
        shell_exec("rm -rf $this->dir");

        parent::tearDown();
    }
}
