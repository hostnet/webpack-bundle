<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\EventListener;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\EventListener\RequestListener
 */
class RequestListenerTest extends TestCase
{
    public function testRequestNoMasterRequest(): void
    {
        $event = $this->prophesize(GetResponseEvent::class);
        $guard = $this->prophesize(CacheGuard::class);

        $guard->rebuild()->shouldNotBeCalled();
        $event->isMasterRequest()->willReturn(false);

        (new RequestListener($guard->reveal()))->onRequest($event->reveal());
    }

    public function testRequestMasterRequest(): void
    {
        $event = $this->prophesize(GetResponseEvent::class);
        $guard = $this->prophesize(CacheGuard::class);

        $guard->rebuild()->shouldBeCalled();
        $event->isMasterRequest()->willReturn(true);

        (new RequestListener($guard->reveal()))->onRequest($event->reveal());
    }
}
