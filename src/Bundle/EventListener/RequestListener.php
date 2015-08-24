<?php
namespace Hostnet\Bundle\WebpackBundle\EventListener;

use Hostnet\Component\Webpack\Asset\Compiler;
use Hostnet\Component\Webpack\Asset\Dumper;
use Hostnet\Component\Webpack\Asset\Tracker;
use Hostnet\Component\Webpack\Profiler\Profiler;
use Symfony\Component\Filesystem\Filesystem;
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
     * @var Dumper
     */
    private $dumper;

    /**
     * @param Tracker  $tracker
     * @param Compiler $compiler
     * @param Dumper   $dumper
     */
    public function __construct(Tracker $tracker, Compiler $compiler, Dumper $dumper)
    {
        $this->tracker  = $tracker;
        $this->compiler = $compiler;
        $this->dumper   = $dumper;
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

        $this->dumper->dump(new Filesystem());
    }
}
