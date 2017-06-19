<?php
declare(strict_types = 1);
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Hostnet\Component\Webpack\Asset\Tracker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class AssetTest extends KernelTestCase
{
    private $compiled;

    protected function setUp()
    {
        static::bootKernel();
        $this->compiled = static::$kernel->getContainer()->getParameter('kernel.root_dir') . '/cache/compiled/';

        if (!file_exists($this->compiled)) {
            mkdir($this->compiled);
        }
    }

    public function testPublicAsset()
    {
        static::bootKernel();

        /** @var $twig_ext TwigExtension */
        $twig_ext = static::$kernel->getContainer()->get('hostnet_webpack.bridge.twig_extension');

        self::assertEquals('/bundles/henk.png', $twig_ext->webpackPublic('henk.png'));
    }

    public function testCompiledAsset()
    {
        /** @var $twig_ext TwigExtension */
        $container = static::$kernel->getContainer();
        $twig_ext  = $container->get('hostnet_webpack.bridge.twig_extension');

        self::assertEquals([
            'js'  => false,
            'css' => false,
        ], $twig_ext->webpackAsset('henk'));

        touch($this->compiled . 'app.henk.js');
        touch($this->compiled . 'app.henk.css');

        $resources = $twig_ext->webpackAsset('@App/henk.js');
        self::assertContains('app.henk.js?', (string) $resources['js']);
        self::assertContains('app.henk.css?', (string) $resources['css']);
    }

    protected function tearDown()
    {
        `rm -rf {$this->compiled}`;

        parent::tearDown();
    }
}
