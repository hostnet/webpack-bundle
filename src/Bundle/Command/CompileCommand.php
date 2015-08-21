<?php
namespace Hostnet\Bundle\WebpackBridge\Command;

use Hostnet\Component\WebpackBridge\Asset\Compiler;
use Hostnet\Component\WebpackBridge\Profiler\Profiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Compiler $compiler
     * @param Profiler $profiler
     */
    public function __construct(Compiler $compiler, Profiler $profiler)
    {
        parent::__construct('webpack:compile');

        $this->compiler = $compiler;
        $this->profiler = $profiler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Move logging somewhere else.
        $output->write('Compiling...');
        $this->compiler->compile();
        $output->writeln('DONE.');

        $output->writeln($this->profiler->get('compiler.last_output'));
    }
}
