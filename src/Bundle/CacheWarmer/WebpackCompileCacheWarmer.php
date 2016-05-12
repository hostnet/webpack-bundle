<?php
namespace Hostnet\Bundle\WebpackBundle\CacheWarmer;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Compiles webpack to avoid twig compiling without the assets.
 */
class WebpackCompileCacheWarmer implements CacheWarmerInterface
{
    /**
     * Guards the cache and is able to rebuild/update it.
     *
     * @var CacheGuard
     */
    private $guard;

    /**
     * Create the cache warmer.
     *
     * @param CacheGuard $guard Guards the cache and is able to rebuild/update it.
     */
    public function __construct(CacheGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cache_dir)
    {
        $this->guard->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
