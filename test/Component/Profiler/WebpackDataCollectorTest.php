<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

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
    public function testProfiler(): void
    {
        $profiler  = new Profiler();
        $collector = new WebpackDataCollector($profiler);
        $collector->collect(
            $this->getMockBuilder(Request::class)->getMock(),
            $this->getMockBuilder(Response::class)->getMock()
        );

        self::assertEquals('webpack', $collector->getName());
        self::assertNull($collector->get('foobar'));

        $profiler->set('foobar', 'hoi');

        self::assertEquals('hoi', $collector->get('foobar'));
    }
}
