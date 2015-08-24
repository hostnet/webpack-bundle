<?php
namespace Hostnet\Bundle\WebpackBundle\Command;

use Hostnet\Component\WebpackBundle\Asset\Compiler;
use Hostnet\Component\WebpackBundle\Asset\Dumper;
use Hostnet\Component\WebpackBundle\Profiler\Profiler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @TODO Add some decent logging interface to allow optional verbose output.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class CompileCommand extends Command
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

    /**
     * @param Compiler        $compiler
     * @param Dumper          $dumper
     * @param LoggerInterface $logger
     * @param Profiler        $profiler
     */
    public function __construct(Compiler $compiler, Dumper $dumper, LoggerInterface $logger, Profiler $profiler)
    {
        parent::__construct('webpack:compile');

        $this->compiler = $compiler;
        $this->dumper   = $dumper;
        $this->logger   = $logger;
        $this->profiler = $profiler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('[WEBPACK]: Compiling assets...');
        $this->compiler->compile();

        $this->logger->info('[WEBPACK]: Dumping assets...');
        $this->dumper->dump(new Filesystem());

        $this->logger->debug($this->profiler->get('compiler.last_output'));
    }
}
