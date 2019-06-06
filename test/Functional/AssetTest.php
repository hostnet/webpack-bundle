<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Functional;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Hostnet\Fixture\WebpackBundle\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssetTest extends KernelTestCase
{
    private $compiled;

    public static function getKernelClass()
    {
        return TestKernel::class;
    }

    protected function setUp(): void
    {
        static::bootKernel();
        $this->compiled = static::$kernel->getContainer()->getParameter('kernel.root_dir') . '/cache/compiled/';

        if (!file_exists($this->compiled)) {
            mkdir($this->compiled);
        }
    }

    public function testPublicAsset(): void
    {
        static::bootKernel();

        /** @var TwigExtension $twig_ext */
        $twig_ext = static::$kernel->getContainer()->get(TwigExtension::class);

        self::assertEquals('/bundles/henk.png', $twig_ext->webpackPublic('henk.png'));
    }

    public function testCompiledAsset(): void
    {
        /** @var TwigExtension $twig_ext */
        $container = static::$kernel->getContainer();
        $twig_ext  = $container->get(TwigExtension::class);

        self::assertEquals([
            'js'  => false,
            'css' => false,
        ], $twig_ext->webpackAsset('henk'));

        touch($this->compiled . 'app.henk.js');
        touch($this->compiled . 'app.henk.css');

        $resources = $twig_ext->webpackAsset('@App/henk.js');
        self::assertStringContainsString('app.henk.js?', (string) $resources['js']);
        self::assertStringContainsString('app.henk.css?', (string) $resources['css']);
    }

    protected function tearDown(): void
    {
        shell_exec("rm -rf {$this->compiled}");

        parent::tearDown();
    }
}
