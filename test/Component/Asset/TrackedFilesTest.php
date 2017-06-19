<?php
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Asset;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * In this unit-test we simulate the 'tracked' files vs. the 'compiled' files with two directories
 * 'a' for the 'asset's to track' and 'b' the compiled version of those.
 *
 * @covers \Hostnet\Component\Webpack\Asset\TrackedFiles
 */
class TrackedFilesTest extends TestCase
{

    /**
     * Full path to test directory 'a'
     *
     * @var string
     */
    private $directory_a;

    /**
     * Full path to test directory 'b'
     *
     * @var string
     */
    private $directory_b;

    /**
     * Create test directories a & b
     *
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->directory_a = tempnam(sys_get_temp_dir(), 'tracked_files_unittest_a');
        unlink($this->directory_a);
        mkdir($this->directory_a);

        $this->directory_b = tempnam(sys_get_temp_dir(), 'tracked_files_unittest_b');
        unlink($this->directory_b);
        mkdir($this->directory_b);

    }

    /**
     * Ensure the test directories a & b are removed
     *
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->directory_a);
        $fs->remove($this->directory_b);
    }

    /**
     * Test the behavior for empty / no directories
     */
    public function testTrackedFilesEmpty()
    {
        $t1 = new TrackedFiles([$this->directory_a]);
        $t2 = new TrackedFiles([$this->directory_b]);

        self::assertFalse($t1->modifiedAfter($t2));
        self::assertFalse($t2->modifiedAfter($t1));

        self::assertFalse($t1->modifiedAfter($t1));
        self::assertFalse($t2->modifiedAfter($t2));
    }

    /**
     * What happens when a file is added after 'compilation'
     */
    public function testAdd()
    {
        $time = time();

        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file1');
        touch($file, $time - 100);
        $file = tempnam($this->directory_b, 'tracked_files_unittest_b_file1');
        touch($file, $time - 50);
        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file2');
        touch($file, $time);


        $t1 = new TrackedFiles([$this->directory_a]);
        $t2 = new TrackedFiles([$this->directory_b]);

        self::assertTrue($t1->modifiedAfter($t2));
        self::assertFalse($t2->modifiedAfter($t1));
    }

    /**
     * What happens when a file is removed after 'compilation'
     */
    public function testDel()
    {

        $time = time();

        $file1 = tempnam($this->directory_a, 'tracked_files_unittest_a_file1');
        touch($file1, $time - 100);
        $file2 = tempnam($this->directory_b, 'tracked_files_unittest_b_file1');
        touch($file2, $time - 50);
        $file3 = tempnam($this->directory_a, 'tracked_files_unittest_a_file2');
        touch($file3, $time);

        unlink($file3);

        $t1 = new TrackedFiles([$this->directory_a]);
        $t2 = new TrackedFiles([$this->directory_b]);

        self::assertFalse($t1->modifiedAfter($t2));
        self::assertTrue($t2->modifiedAfter($t1));

    }

    /**
     * What happens when a file is modified before 'compilation'
     */
    public function testModifyBefore()
    {
        $time = time();

        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file1');
        touch($file, $time - 100);
        $file = tempnam($this->directory_b, 'tracked_files_unittest_b_file1');
        touch($file, $time - 50);
        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file2');
        touch($file, $time-200);


        $t1 = new TrackedFiles([$this->directory_a]);
        $t2 = new TrackedFiles([$this->directory_b]);

        self::assertFalse($t1->modifiedAfter($t2));
        self::assertTrue($t2->modifiedAfter($t1));
    }

    /**
     * What happens when a file is modified after 'compilation'
     */
    public function testModifyAfter()
    {
        $time = time();

        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file1');
        touch($file, $time - 100);
        $file = tempnam($this->directory_b, 'tracked_files_unittest_b_file1');
        touch($file, $time - 50);
        $file = tempnam($this->directory_a, 'tracked_files_unittest_a_file2');
        touch($file, $time);


        $t1 = new TrackedFiles([$this->directory_a]);
        $t2 = new TrackedFiles([$this->directory_b]);

        self::assertTrue($t1->modifiedAfter($t2));
        self::assertFalse($t2->modifiedAfter($t1));
    }
}
