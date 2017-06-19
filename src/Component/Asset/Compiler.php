<?php
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigGenerator;
use Hostnet\Component\Webpack\Profiler\Profiler;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Compiles asssets using the webpack binary.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class Compiler
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @var ConfigGenerator
     */
    private $generator;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * @var string[]
     */
    private $bundles;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param Profiler        $profiler
     * @param Tracker         $tracker
     * @param ConfigGenerator $generator
     * @param TwigParser      $twig_parser
     * @param Process         $process
     * @param Stopwatch       $stopwatch
     * @param string          $cache_dir
     * @param array           $bundles
     */
    public function __construct(
        Profiler        $profiler,
        Tracker         $tracker,
        TwigParser      $twig_parser,
        ConfigGenerator $generator,
        Process         $process,
        /* string */    $cache_dir,
        /* array */     $bundles,
        Stopwatch       $stopwatch = null
    ) {
        $this->profiler    = $profiler;
        $this->tracker     = $tracker;
        $this->twig_parser = $twig_parser;
        $this->generator   = $generator;
        $this->process     = $process;
        $this->cache_dir   = $cache_dir;
        $this->bundles     = $bundles;
        $this->stopwatch   = $stopwatch !== null ? $stopwatch : new Stopwatch();
    }

    /**
     * @return string
     */
    public function compile()
    {
        $this->stopwatch->start('webpack.total');
        $this->stopwatch->start('webpack.prepare');

        // Recompile twig templates where its needed.
        $this->addSplitPoints();
        $this->addResolveConfig();

        // Write the webpack configuration file.
        file_put_contents(
            $this->cache_dir . DIRECTORY_SEPARATOR . 'webpack.config.js',
            $this->generator->getConfiguration()
        );
        $this->profiler->set('compiler.performance.prepare', $this->stopwatch->stop('webpack.prepare')->getDuration());
        $this->stopwatch->start('webpack.compiler');
        $this->process->run();

        $output = $this->process->getOutput() . $this->process->getErrorOutput();
        $this->profiler->set('compiler.executed', true);
        $this->profiler->set('compiler.last_output', $output);
        $this->profiler->set(
            'compiler.successful',
            strpos($output, 'Error:') === false &&
            strpos($output, 'parse failed') === false
        );

        // Finally, write some logging for later use.
        file_put_contents($this->cache_dir . DIRECTORY_SEPARATOR . 'webpack.compiler.log', $output);

        $this->profiler->set(
            'compiler.performance.compiler',
            $this->stopwatch->stop('webpack.compiler')->getDuration()
        );
        $this->profiler->set('compiler.performance.total', $this->stopwatch->stop('webpack.total')->getDuration());

        return $output;
    }

    /**
     * Adds root & alias configuration entries.
     */
    private function addResolveConfig()
    {
        $aliases = $this->tracker->getAliases();
        $this->generator->addBlock(
            (new CodeBlock())->set(CodeBlock::RESOLVE, ['alias' => $aliases, 'root' => array_values($aliases)])
        );
    }

    /**
     * Add split points to the 'entry' section of the configuration.
     */
    private function addSplitPoints()
    {
        $split_points = [];
        foreach ($this->tracker->getTemplates() as $template_file) {
            $split_points = array_merge($split_points, $this->twig_parser->findSplitPoints($template_file));
        }

        foreach ($split_points as $id => $file) {
            $this->generator->addBlock((new CodeBlock())->set(CodeBlock::ENTRY, [self::getAliasId($id) => $file]));
        }
    }



    /**
     * Returns the alias id for the given path.
     *
     * @param  string $path
     * @return string
     */
    public static function getAliasId($path)
    {
        return str_replace(
            ['/', '\\'],
            '.',
            Container::underscore(ltrim(substr($path, 0, (int) strrpos($path, '.')), '@'))
        );
    }
}
