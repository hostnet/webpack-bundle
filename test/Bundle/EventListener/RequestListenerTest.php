<?php
namespace Hostnet\Bundle\WebpackBundle\EventListener;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\EventListener\RequestListener
 * @author Harold Iedema <harold@iedema.me>
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GetResponseEvent
     */
    private $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CacheGuard
     */
    private $guard;

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $this->guard = $this->getMockBuilder(CacheGuard::class)->disableOriginalConstructor()->getMock();

    }

    public function testRequestNoMasterRequest()
    {
        $this->guard->expects($this->never())->method('rebuild');
        $this->event->expects($this->once())->method('isMasterRequest')->willReturn(false);

        (new RequestListener($this->guard))->onRequest($this->event);
    }

    public function testRequestMasterRequest()
    {
        $this->guard->expects($this->once())->method('rebuild');
        $this->event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        (new RequestListener($this->guard))->onRequest($this->event);
    }
}
