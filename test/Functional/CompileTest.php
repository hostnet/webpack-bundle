<?php
use Hostnet\Component\Webpack\Asset\Tracker;
use Hostnet\Component\Webpack\Profiler\WebpackDataCollector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class CompileTest extends KernelTestCase
{
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

    public function testTrackedTemplates()
    {
        static::bootKernel();

        /** @var $tracker Tracker */
        $tracker = static::$kernel->getContainer()->get('hostnet_webpack.bridge.asset_tracker');
        $tracker->rebuild();

        $templates = array_map(array($this, 'relative'), $tracker->getTemplates());

        $this->assertContains('/test/Fixture/Bundle/FooBundle/Resources/views/foo.html.twig', $templates);
        $this->assertContains('/test/Fixture/Resources/views/template.html.twig', $templates);
        $this->assertContains('/test/Fixture/Resources/views/template_parse_error.html.twig', $templates);

        $aliases = $tracker->getAliases();

        $this->assertEquals('/test/Fixture/Bundle/BarBundle/Resources/assets', $this->relative($aliases['@BarBundle']));
    }

    private function relative($path)
    {
        return str_replace(str_replace('/test/Fixture', '', $this->normalize(static::$kernel->getContainer()->getParameter('kernel.root_dir'))), '', $this->normalize($path));
    }

    private function normalize($path)
    {
        return str_replace('\\', '/', $path);
    }
}
