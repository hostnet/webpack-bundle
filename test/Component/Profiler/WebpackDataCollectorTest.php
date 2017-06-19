<?php
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Profiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \Hostnet\Component\Webpack\Profiler\Profiler
 * @covers \Hostnet\Component\Webpack\Profiler\WebpackDataCollector
 */
class WebpackDataCollectorTest extends TestCase
{
    public function testProfiler()
    {
        $profiler  = new Profiler();
        $collector = new WebpackDataCollector($profiler);
        $profiler->set('foobar', 'hoi');
        $collector->collect(
            $this->getMockBuilder(Request::class)->getMock(),
            $this->getMockBuilder(Response::class)->getMock()
        );

        self::assertEquals('hoi', $collector->get('foobar'));
        self::assertEquals('webpack', $collector->getName());
    }
}
