<?php
namespace Hostnet\Bundle\WebpackBridge\Command;

use Hostnet\Component\WebpackBridge\Asset\Compiler;
use Hostnet\Component\WebpackBridge\Profiler\Profiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class CompileCommand extends Command
{
    private $compiler;

    /**
     * @param Compiler $compiler
     */
    public function __construct(Compiler $compiler)
    {
        parent::__construct();

        $this->compiler = $compiler;
    }

    protected function configure()
    {
        $this->setName('webpack:compile');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Compiling...');
        $this->compiler->compile();
        $output->writeln('DONE.');

        $output->writeln($this->profiler->get('compiler.last_output'));
    }
}
