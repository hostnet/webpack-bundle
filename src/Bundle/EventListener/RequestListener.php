<?php
namespace Hostnet\Bundle\WebpackBridge\EventListener;

use Hostnet\Component\WebpackBridge\Asset\Compiler;
use Hostnet\Component\WebpackBridge\Asset\Tracker;
use Hostnet\Component\WebpackBridge\Profiler\Profiler;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class RequestListener
{
    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @param Tracker  $tracker
     * @param Compiler $compiler
     */
    public function __construct(Tracker $tracker, Compiler $compiler)
    {
        $this->tracker  = $tracker;
        $this->compiler = $compiler;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (! $event->isMasterRequest()) {
            return;
        }

        if ($this->tracker->isOutdated()) {
            $this->compiler->compile();
        }
    }
}
