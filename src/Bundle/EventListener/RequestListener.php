<?php
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\EventListener;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class RequestListener
{
    /**
     * Guards the cache and is able to rebuild/update it.
     *
     * @var CacheGuard
     */
    private $guard;

    /**
     * Create the listener
     *
     * @param CacheGuard $guard Guards the cache and is able to rebuild/update it.
     */
    public function __construct(CacheGuard $guard)
    {
        $this->guard = $guard;
    }


    /**
     * On Request received check the validity of the webpack cache.
     *
     * @param GetResponseEvent $event the response to send to te browser, we don't we only ensure the cache is there.
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (! $event->isMasterRequest()) {
            return;
        }

        $this->guard->rebuild();
    }
}
