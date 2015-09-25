<?php
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Component\Webpack\Profiler\Profiler;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\Templating\TemplateReference;

/**
 * @covers \Hostnet\Component\Webpack\Asset\Tracker
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testTracker()
    {
        $profiler     = new Profiler();
        $finder       = $this->getMock(TemplateFinderInterface::class);
        $fixture_path = realpath(__DIR__ . '/../../Fixture');
        $temp_file    = $fixture_path . '/Bundle/tempfile.txt' ;
        $temp_file2   = $fixture_path . '/Bundle/tempfile2.txt' ;
        $temp_file3   = $fixture_path . '/Bundle/tempfile3.txt' ;
        $tracker      = new Tracker($profiler, $finder, $fixture_path . '/cache', $fixture_path, 'Resources', [
            'FooBundle' => $fixture_path . '/Bundle/FooBundle',
            'BarBundle' => $fixture_path . '/Bundle/BarBundle'
        ]);

        touch($temp_file);
        $tracker->addPath($temp_file);
        $tracker->addPath($fixture_path . '/Bundle');

        $finder->expects($this->once())->method('findAllTemplates')->willReturn([
            new TemplateReference('dont_parse_me', 'php'),
            new TemplateReference('@FooBundle/foo.html.twig', 'twig'),
            new TemplateReference('template.html.twig', 'twig'),
            new TemplateReference('i_dont_exist', 'twig')
        ]);

        $this->assertEquals('/i/cant/be/resolved', $tracker->resolveResourcePath('/i/cant/be/resolved'));

        // Start by removing the cache file, if it exists.
        if (file_exists($fixture_path . '/cache/webpack.asset_tracker.cache')) {
            unlink($fixture_path . '/cache/webpack.asset_tracker.cache');
        }

        $this->assertTrue($tracker->isOutdated());

        // Start by rebuilding the cache
        $tracker->rebuild();

        // Since cache is fresh, isOutdated should return false.
        $this->assertFalse($tracker->isOutdated());

        // Modify something.
        touch($temp_file, time() + 1 + mt_rand(1, 100));
        $this->assertTrue($tracker->isOutdated());

        // Create something
        $tracker->rebuild();
        touch($temp_file2);
        $this->assertTrue($tracker->isOutdated());
        unlink($temp_file);
        unlink($temp_file2);
        touch($temp_file3);
        $this->assertTrue($tracker->isOutdated());
        $tracker->rebuild();
        touch($temp_file, time() + 10);
        $this->assertTrue($tracker->isOutdated());
        unlink($temp_file3);
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @expectedExceptionMessage File "/i/dont/exist" could not be found.
     */
    public function testAddInvalidPath()
    {
        $profiler     = new Profiler();
        $finder       = $this->getMock(TemplateFinderInterface::class);
        $fixture_path = realpath(__DIR__ . '/../../Fixture');
        $tracker      = new Tracker($profiler, $finder, $fixture_path . '/cache', $fixture_path, []);

        $tracker->addPath("/i/dont/exist");
    }
}
