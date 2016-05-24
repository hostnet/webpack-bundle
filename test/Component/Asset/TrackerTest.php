<?php
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Component\Webpack\Profiler\Profiler;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * @covers \Hostnet\Component\Webpack\Asset\Tracker
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The root directory of the application a.k.a. %kernel.root_dir%
     *
     * @var string
     */
    private $root_dir;

    /**
     * The relative path to the asset direcotry full path = $root_dir . / .$asset_dir
     *
     * @var string
     */
    private $asset_dir;

    /**
     * The path the to directory where the output is generated
     *
     * @var string
     */
    private $output_dir;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $fixture_path     = realpath(__DIR__ . '/../../Fixture');
        $this->root_dir   = $fixture_path;
        $this->asset_dir  = 'Resources/assets';
        $this->output_dir = $fixture_path . '/web/compiled';
    }

    /**
     * The the isOutdated function in case the 'compiled' version is outdated.
     */
    public function testIsOutdated()
    {

        $path = tempnam(sys_get_temp_dir(), 'unittest_tracker_source');
        unlink($path);
        mkdir($path);

        $output_directory = tempnam(sys_get_temp_dir(), 'unittest_tracker_output');
        unlink($output_directory);
        mkdir($output_directory);

        $time = time();

        $file = tempnam($path, 'unittest_tracker_source_asset');
        touch($file, $time);
        $file2 = tempnam($output_directory, 'unittest_tracker_output_compiled');
        touch($file2, $time - 100);

        $profiler = $this->prophesize(Profiler::class);
        $profiler->set('tracker.reason', 'One of the tracked files has been modified.')->shouldBeCalled();
        $profiler->set('bundles', [])->shouldBeCalled();
        $profiler->set('templates', [])->shouldBeCalled();
        $finder = $this->prophesize(TemplateFinderInterface::class);
        $finder->findAllTemplates()->willReturn([]);
        $tracker = new Tracker(
            $profiler->reveal(),
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $output_directory
        );
        $tracker->addPath($path);

        self::assertTrue($tracker->isOutdated());

        //Cleanup
        $fs = new Filesystem();
        $fs->remove($path);
        $fs->remove($output_directory);
    }

    /**
     * The the isOutdated function in case the 'compiled' version is still fresh enough.
     */
    public function testIsNotOutdated()
    {
        $path = tempnam(sys_get_temp_dir(), 'unittest_tracker_source');
        unlink($path);
        mkdir($path);

        $output_directory = tempnam(sys_get_temp_dir(), 'unittest_tracker_output');
        unlink($output_directory);
        mkdir($output_directory);

        $time = time();

        $file = tempnam($path, 'unittest_tracker_source_asset');
        touch($file, $time - 100);
        $file2 = tempnam($output_directory, 'unittest_tracker_output_compiled');
        touch($file2, $time);

        $profiler = $this->prophesize(Profiler::class);
        $profiler->set('tracker.reason', false)->shouldBeCalled();
        $profiler->set('bundles', [])->shouldBeCalled();
        $profiler->set('templates', [])->shouldBeCalled();

        $finder = $this->prophesize(TemplateFinderInterface::class);
        $finder->findAllTemplates()->willReturn([]);
        $tracker = new Tracker(
            $profiler->reveal(),
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $output_directory
        );
        $tracker->addPath($path);

        self::assertFalse($tracker->isOutdated());

        //Cleanup
        $fs = new Filesystem();
        $fs->remove($path);
        $fs->remove($output_directory);
    }

    /**
     * Test the getTemplates function when there are no templates.
     */
    public function testGetTemplatesNoTemplates()
    {
        $profiler = new Profiler();
        $finder   = $this->prophesize(TemplateFinderInterface::class);
        $finder->findAllTemplates()->willReturn([]);

        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir
        );
        self::assertEmpty($tracker->getTemplates());
    }

    /**
     * Test the getTemplates function when the path to the template is not resolvable
     */
    public function testGetTemplatesTemplateNotFound()
    {
        $profiler = new Profiler();
        $ref      = $this->prophesize(TemplateReferenceInterface::class);
        $ref->getPath()->willReturn('/ab/ca/dabra');
        $ref->get('engine')->willReturn('twig');
        $finder = $this->prophesize(TemplateFinderInterface::class);
        $finder->findAllTemplates()->willReturn([$ref->reveal()]);


        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir
        );
        self::assertEmpty($tracker->getTemplates());
    }

    /**
     * test the getTempaltes function for 1 template, including duplicate call of function.
     */
    public function testGetTemplates()
    {
        $profiler = new Profiler();
        $ref      = $this->prophesize(TemplateReferenceInterface::class);
        $ref->getPath()->willReturn('assets/base.js');
        $ref->get('engine')->willReturn('twig');
        $finder = $this->prophesize(TemplateFinderInterface::class);
        $finder->findAllTemplates()->willReturn([$ref->reveal()]);


        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir
        );
        self::assertEquals(
            [$this->root_dir . DIRECTORY_SEPARATOR . $this->asset_dir . DIRECTORY_SEPARATOR . 'base.js'],
            $tracker->getTemplates()
        );

        //Calling twice results in same answer
        self::assertEquals(
            [$this->root_dir . DIRECTORY_SEPARATOR . $this->asset_dir . DIRECTORY_SEPARATOR . 'base.js'],
            $tracker->getTemplates()
        );
    }

    /**
     * test resloveResourcePath for a full path.
     */
    public function testResolveResourcePathNoBundle()
    {
        $profiler = new Profiler();
        $finder   = $this->prophesize(TemplateFinderInterface::class);

        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir
        );
        self::assertEquals('/i/m/a/fool', $tracker->resolveResourcePath('/i/m/a/fool'));
    }

    /**
     * test resloveResourcePath for a bundle path when bundle is not found.
     */
    public function testResolveResourcePathBundleNotFound()
    {
        $profiler = new Profiler();
        $finder   = $this->prophesize(TemplateFinderInterface::class);

        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir
        );
        self::assertFalse($tracker->resolveResourcePath('@Fool'));
    }

    /**
     * test resloveResourcePath for a bundle path when bundle is found.
     */
    public function testResolveResourcePathBundle()
    {
        $profiler = new Profiler();
        $finder   = $this->prophesize(TemplateFinderInterface::class);

        $tracker = new Tracker(
            $profiler,
            $finder->reveal(),
            $this->root_dir,
            $this->asset_dir,
            $this->output_dir,
            ['BarBundle' => $this->root_dir . DIRECTORY_SEPARATOR . 'Bundle' . DIRECTORY_SEPARATOR . 'BarBundle']
        );
        self::assertEquals(
            $this->root_dir . DIRECTORY_SEPARATOR .
            'Bundle' . DIRECTORY_SEPARATOR . 'BarBundle' . DIRECTORY_SEPARATOR . $this->asset_dir,
            $tracker->resolveResourcePath('@BarBundle')
        );
    }

    /**
     * Test addPath for invalid path.
     *
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @expectedExceptionMessage File "/i/dont/exist" could not be found.
     */
    public function testAddInvalidPath()
    {
        $profiler = new Profiler();
        $finder   = $this->getMock(TemplateFinderInterface::class);
        $tracker  = new Tracker($profiler, $finder, $this->root_dir, $this->asset_dir, $this->output_dir, []);
        $tracker->addPath("/i/dont/exist");
    }
}
