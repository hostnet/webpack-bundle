<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Hostnet\Component\Webpack\Asset\CacheGuard
 */
class CacheGuardTest extends TestCase
{
    /**
     * Simple test for the case the cache is outdated.
     */
    public function testCacheOutdated(): void
    {
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile()->willReturn('some debug output');

        $dumper = $this->prophesize(Dumper::class);
        $dumper->dump()->shouldBeCalled();

        //Cache is outdated.
        $tracker = $this->prophesize(Tracker::class);
        $tracker->isOutdated()->willReturn(true);

        //What do we expect for logging
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('[Webpack 1/2]: Compiling assets.')->shouldBeCalled();
        $logger->info('[Webpack 2/2]: Dumping assets.')->shouldBeCalled();
        $logger->debug('some debug output')->shouldBeCalled();

        $cache_guard = new CacheGuard($compiler->reveal(), $dumper->reveal(), $tracker->reveal(), $logger->reveal());
        $cache_guard->rebuild();
    }

    /**
     * Simple test for the case the cache is not outdated.
     */
    public function testCacheUpToDate(): void
    {
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile()->willReturn('some debug output')->shouldNotBeCalled();

        $dumper = $this->prophesize(Dumper::class);
        $dumper->dump()->shouldNotBeCalled();

        //Cache is not outdated
        $tracker = $this->prophesize(Tracker::class);
        $tracker->isOutdated()->willReturn(false);

        //What do we expect for logging
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('[Webpack]: Cache still up-to-date.')->shouldBeCalled();

        $cache_guard = new CacheGuard($compiler->reveal(), $dumper->reveal(), $tracker->reveal(), $logger->reveal());
        $cache_guard->rebuild();
    }
}
