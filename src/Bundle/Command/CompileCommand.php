<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Command;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @TODO Add some decent logging interface to allow optional verbose output.
 */
class CompileCommand extends Command
{
    /**
     * Guards the cache and is able to rebuild/update it.
     *
     * @var CacheGuard
     */
    private $guard;

    /**
     * Create and configure webpack:compile command.
     *
     * @param CacheGuard $guard Guards the cache and is able to rebuild/update it.
     */
    public function __construct(CacheGuard $guard)
    {
        parent::__construct('webpack:compile');
        $this->guard = $guard;
    }

    /**
     * Execute the webpack:compile command (basicly forwards the logic to CacheGuard::validate).
     *
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->guard->rebuild();
    }
}
