<?php
namespace Hostnet\Component\Webpack\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Hostnet\Component\Webpack\Asset\Dumper
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class DumperTest extends \PHPUnit_Framework_TestCase
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
                'BarBundle' => $this->fixture_path . '/Bundle/BarBundle'
            ],
            'Resources/public',
            $this->fixture_path . '/dumper_output'
        );

        // Clean out the fixture dumper path before dumping resources.
        if (file_exists($this->fixture_path . '/dumper_output')) {
            (new Filesystem())->remove($this->fixture_path . '/dumper_output');
        }
    }

    public function testDumpDefaults()
    {
        $this->dumper->dump();
    }
}
