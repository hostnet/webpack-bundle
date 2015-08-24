<?php
namespace Hostnet\Bundle\WebpackBundle\EventListener;

use Hostnet\Component\WebpackBundle\Asset\Compiler;
use Hostnet\Component\WebpackBundle\Asset\Dumper;
use Hostnet\Component\WebpackBundle\Asset\Tracker;
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
     * @var \PHPUnit_Framework_MockObject_MockObject|Compiler
     */
    private $compiler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Tracker
     */
    private $tracker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Dumper
     */
    private $dumper;

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->event    = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $this->compiler = $this->getMockBuilder(Compiler::class)->disableOriginalConstructor()->getMock();
        $this->tracker  = $this->getMockBuilder(Tracker::class)->disableOriginalConstructor()->getMock();
        $this->dumper   = $this->getMockBuilder(Dumper::class)->disableOriginalConstructor()->getMock();
    }

    public function testRequestNoMasterRequest()
    {
        $this->tracker->expects($this->never())->method('isOutdated');
        $this->compiler->expects($this->never())->method('compile');
        $this->dumper->expects($this->never())->method('dump');
        $this->event->expects($this->once())->method('isMasterRequest')->willReturn(false);

        (new RequestListener($this->tracker, $this->compiler, $this->dumper))->onRequest($this->event);
    }

    public function testRequestValidCache()
    {
        $this->tracker->expects($this->once())->method('isOutdated')->willReturn(false);
        $this->compiler->expects($this->never())->method('compile');
        $this->dumper->expects($this->once())->method('dump');
        $this->event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        (new RequestListener($this->tracker, $this->compiler, $this->dumper))->onRequest($this->event);
    }

    public function testRequestCompile()
    {
        $this->tracker->expects($this->once())->method('isOutdated')->willReturn(true);
        $this->compiler->expects($this->once())->method('compile');
        $this->dumper->expects($this->once())->method('dump');
        $this->event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        (new RequestListener($this->tracker, $this->compiler, $this->dumper))->onRequest($this->event);
    }
}
