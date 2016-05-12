<?php
namespace Hostnet\Bundle\WebpackBundle\CacheWarmer;

use Hostnet\Component\Webpack\Asset\CacheGuard;

/**
 * @covers Hostnet\Bundle\WebpackBundle\CacheWarmer\WebpackCompileCacheWarmer
 */
class WebpackCompileCacheWarmerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Simple test to see the guard is executed from the cache warmer.
     */
    public function testWebpackCompileCacheWarmer()
    {
        $guard = $this->prophesize(CacheGuard::class);
        $guard->validate()->shouldBeCalled();

        $webpack_compile_cache_warmer = new WebpackCompileCacheWarmer($guard->reveal());

        //Cache warmer is optional...
        self::assertTrue($webpack_compile_cache_warmer->isOptional());
        $webpack_compile_cache_warmer->warmUp('/tmp/random/name');
    }
}
