<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Functional;

use Hostnet\Component\Webpack\Asset\Tracker;
use Hostnet\Component\Webpack\Profiler\WebpackDataCollector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CompileTest extends KernelTestCase
{
    public function testDevCollector()
    {
        static::bootKernel(['environment' => 'dev', 'debug' => false]);
        $collector = static::$kernel->getContainer()->get(WebpackDataCollector::class);

        self::assertInstanceOf(WebpackDataCollector::class, $collector);
    }

    public function testMissingCollector()
    {
        $this->expectException(ServiceNotFoundException::class);

        static::bootKernel(['environment' => 'test', 'debug' => false]);
        static::$kernel->getContainer()->get(WebpackDataCollector::class);
    }

    public function testTrackedTemplates()
    {
        static::bootKernel();

        /** @var Tracker $tracker */
        $tracker = static::$kernel->getContainer()->get(Tracker::class);

        $templates = array_map([$this, 'relative'], $tracker->getTemplates());

        self::assertContains('/test/Fixture/Bundle/FooBundle/Resources/views/foo.html.twig', $templates);
        self::assertContains('/test/Fixture/Resources/views/template.html.twig', $templates);

        $aliases = $tracker->getAliases();

        self::assertEquals('/test/Fixture/Bundle/BarBundle/Resources/assets', $this->relative($aliases['@BarBundle']));
    }

    private function relative($path)
    {
        return str_replace(
            str_replace(
                '/test/Fixture',
                '',
                $this->normalize(
                    static::$kernel->getContainer()->getParameter('kernel.root_dir')
                )
            ),
            '',
            $this->normalize($path)
        );
    }

    private function normalize($path)
    {
        return str_replace('\\', '/', $path);
    }
}
