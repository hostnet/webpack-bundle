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
     * @var bool
     */
    private $is_debug;

    /**
     * @param Profiler $profiler
     * @param Tracker  $tracker
     * @param Compiler $compiler
     * @param bool     $is_debug
     */
    public function __construct(Tracker $tracker, Compiler $compiler, $is_debug)
    {
        $this->tracker  = $tracker;
        $this->compiler = $compiler;
        $this->is_debug = $is_debug;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (! $event->isMasterRequest() || ! $this->is_debug) {
            return;
        }

        if ($this->tracker->isOutdated()) {
            $this->compiler->compile();
        }
    }
}
