<?php
namespace Hostnet\Component\Webpack\Asset;

use Psr\Log\LoggerInterface;

/**
 * Guards the cache, updates or creates the cache when needed.
 */
class CacheGuard
{

    /**
     * Compiler used to compile the assets using webpack
     *
     * @var Compiler
     */
    private $compiler;

    /**
     * Dumper of the assets compiled by webpack
     *
     * @var Dumper
     */
    private $dumper;

    /**
     * Keeps track of the changes in the assets compared with last build
     *
     * @var Tracker
     */
    private $tracker;

    /**
     * Logger used to write progress reports to (info & debug)
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Create Cache guard.
     *
     * @param Compiler $compiler the compiler used to compile the assets using webpack
     * @param Dumper $dumper the dumper of the assets compiled by webpack
     * @param Tracker $tracker keeps track of the changes in the assets compared with last build
     * @param LoggerInterface $logger used to write progress reports to (info & debug)
     */
    public function __construct(Compiler $compiler, Dumper $dumper, Tracker $tracker, LoggerInterface $logger)
    {
        $this->compiler = $compiler;
        $this->dumper   = $dumper;
        $this->tracker  = $tracker;
        $this->logger   = $logger;
    }

    /**
     * Rebuild the cache, check to see if it's still valid and rebuild if it's outdated.
     */
    public function rebuild()
    {
        if ($this->tracker->isOutdated()) {
            $this->logger->info('[Webpack 1/2]: Compiling assets.');
            $output = $this->compiler->compile();
            $this->logger->debug($output);

            $this->logger->info('[Webpack 2/2]: Dumping assets.');
            $this->dumper->dump();
        } else {
            $this->logger->info('[Webpack]: Cache still up-to-date.');
        }
    }
}
