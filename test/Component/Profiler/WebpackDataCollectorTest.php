<?php
namespace Hostnet\Component\Webpack\Profiler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Hostnet\Component\Webpack\Profiler\Profiler
 * @covers Hostnet\Component\Webpack\Profiler\WebpackDataCollector
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testProfiler()
    {
        $profiler  = new Profiler();
        $collector = new WebpackDataCollector($profiler);
        $profiler->set('foobar', 'hoi');
        $collector->collect($this->getMock(Request::class), $this->getMock(Response::class));

        $this->assertEquals('hoi', $collector->get('foobar'));
        $this->assertEquals('webpack', $collector->getName());
    }
}
