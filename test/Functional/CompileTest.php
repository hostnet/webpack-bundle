<?php
use Hostnet\Component\Webpack\Profiler\WebpackDataCollector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class CompileTest extends KernelTestCase
{
    /** @dataProvider envProvider */
    public function testContainer($env)
    {
        static::bootKernel(['environment' => $env, 'debug' => false]);

        $twig_ext = static::$kernel->getContainer()->get('hostnet_webpack.bridge.twig_extension');

        $this->assertEquals('/bundles/henk.png', $twig_ext->webpackPublic('henk.png'));
    }

    public function envProvider()
    {
        return [['dev'], ['test']];
    }

    public function testDevCollector()
    {
        static::bootKernel(['environment' => 'dev', 'debug' => false]);
        $collector = static::$kernel->getContainer()->get('hostnet_webpack.bridge.data_collector');

        $this->assertInstanceOf(WebpackDataCollector::class, $collector);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage You have requested a non-existent service "hostnet_webpack.bridge.data_collector".
     */
    public function testMissingCollector()
    {
        static::bootKernel(['environment' => 'test', 'debug' => false]);
        static::$kernel->getContainer()->get('hostnet_webpack.bridge.data_collector');
    }
}
