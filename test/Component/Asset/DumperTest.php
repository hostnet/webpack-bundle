<?php
namespace Hostnet\Component\WebpackBridge\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @covers \Hostnet\Component\WebpackBridge\Asset\Dumper
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

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->fixture_path = realpath(__DIR__ . '/../../Fixture');
        $this->dumper = new Dumper(
            $this->getMock(LoggerInterface::class),
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

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\IOException
     */
    public function testDumpDirCreateFail()
    {
        $fs = $this->getMock(Filesystem::class);
        $fs->expects($this->once())->method('exists')->willReturn(false);
        $fs->expects($this->once())->method('mkdir')->willThrowException(new IOException(''));

        $this->dumper->dump($fs);
    }

    public function testDump()
    {
        $fs = $this->getMock(Filesystem::class);
        $fs->expects($this->exactly(2))->method('exists')->willReturn(false);
        $fs->expects($this->once())->method('symlink')->willThrowException(new IOException(''));

        $this->dumper->dump($fs);
    }
}
