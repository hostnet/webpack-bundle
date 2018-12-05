<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Hostnet\Component\Webpack\Asset\Dumper
 */
class DumperTest extends TestCase
{
    /**
     * @var Dumper
     */
    private $dumper;

    /**
     * @var string
     */
    private $fixture_path;

    protected function setUp()
    {
        $this->fixture_path = realpath(__DIR__ . '/../../Fixture');
        $this->dumper       = new Dumper(
            $this->getMockBuilder(Filesystem::class)->getMock(),
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            [
                'FooBundle' => $this->fixture_path . '/Bundle/FooBundle',
                'BarBundle' => $this->fixture_path . '/Bundle/BarBundle',
            ],
            'Resources/public',
            $this->fixture_path . '/dumper_output'
        );

        // Clean out the fixture dumper path before dumping resources.
        if (file_exists($this->fixture_path . '/dumper_output')) {
            (new Filesystem())->remove($this->fixture_path . '/dumper_output');
        }
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDumpDefaults()
    {
        $this->dumper->dump();
    }
}
