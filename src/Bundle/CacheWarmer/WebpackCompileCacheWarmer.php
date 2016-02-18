<?php
namespace Hostnet\Bundle\WebpackBundle\CacheWarmer;

use Hostnet\Component\Webpack\Asset\Compiler;
use Hostnet\Component\Webpack\Asset\Dumper;
use Hostnet\Component\Webpack\Profiler\Profiler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Compiles webpack to avoid twig compiling without the assets.
 */
class WebpackCompileCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var Dumper
     */
    private $dumper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Profiler
     */
    private $profiler;


    public function __construct(Compiler $compiler, Dumper $dumper, LoggerInterface $logger, Profiler $profiler)
    {
        $this->compiler = $compiler;
        $this->dumper   = $dumper;
        $this->logger   = $logger;
        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cache_dir)
    {
        $this->logger->info('[WEBPACK]: Compiling assets.');
        $this->compiler->compile();


        $this->logger->info('[WEBPACK]: Dumping assets.');
        $this->dumper->dump();

        $this->logger->debug($this->profiler->get('compiler.last_output'));
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
