<?php
namespace Hostnet\Component\WebpackBundle\Asset;

use Hostnet\Component\WebpackBundle\Configuration\ConfigGenerator;
use Hostnet\Component\WebpackBundle\Profiler\Profiler;
use Symfony\Component\Process\Process;

/**
 * @covers \Hostnet\Component\WebpackBundle\Asset\Compiler
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
    private $profiler;
    private $tracker;
    private $twig_parser;
    private $generator;
    private $process;
    private $cache_path;

    public function setUp()
    {
        $this->profiler    = $this->getMockBuilder(Profiler::class)->disableOriginalConstructor()->getMock();
        $this->tracker     = $this->getMockBuilder(Tracker::class)->disableOriginalConstructor()->getMock();
        $this->twig_parser = $this->getMockBuilder(TwigParser::class)->disableOriginalConstructor()->getMock();
        $this->generator   = $this->getMockBuilder(ConfigGenerator::class)->disableOriginalConstructor()->getMock();
        $this->process     = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $this->cache_path  = realpath(__DIR__ . '/../../Fixture/cache');
    }

    public function testCompile()
    {
        $this->tracker->expects($this->once())->method('getTemplates')->willReturn(['foobar']);
        $this->tracker->expects($this->once())->method('getAliases')->willReturn(['@AppBundle' => 'foobar']);
        $this->twig_parser->expects($this->once())->method('findSplitPoints')->willReturn(['@AppBundle/app.js' => 'a']);
        $this->profiler->expects($this->once())->method('get')->willReturn(true);

        (new Compiler(
            $this->profiler,
            $this->tracker,
            $this->twig_parser,
            $this->generator,
            $this->process,
            $this->cache_path,
            []
        ))->compile();
    }
}
