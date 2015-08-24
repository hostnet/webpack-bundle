<?php
namespace Hostnet\Component\WebpackBundle\Profiler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Hostnet\Component\WebpackBundle\Profiler\Profiler
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    public function testProfiler()
    {
        $profiler = new Profiler();
        $profiler->set('foobar', 'hoi');
        $profiler->collect($this->getMock(Request::class), $this->getMock(Response::class));

        $this->assertEquals('hoi', $profiler->get('foobar'));
        $this->assertEquals('webpack', $profiler->getName());
    }
}
