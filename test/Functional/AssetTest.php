<?php
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

        $this->assertEquals('/bundles/henk.png', $twig_ext->webpackPublic('henk.png'));
    }

    public function testCompiledAsset()
    {
        /** @var $twig_ext TwigExtension */
        $container = static::$kernel->getContainer();
        $twig_ext  = $container->get('hostnet_webpack.bridge.twig_extension');

        $this->assertEquals([
            'js'  => false,
            'css' => false,
        ], $twig_ext->webpackAsset('henk'));

        touch($this->compiled . 'app.henk.js');
        touch($this->compiled . 'app.henk.css');

        $resources = $twig_ext->webpackAsset('@App/henk.js');
        $this->assertContains('app.henk.js?', (string) $resources['js']);
        $this->assertContains('app.henk.css?', (string) $resources['css']);
    }

    public function testAliasAssetTracking()
    {
        /** @var $tracker Tracker */
        $container = static::$kernel->getContainer();
        $tracker   = $container->get('hostnet_webpack.bridge.asset_tracker');
        $tracker->rebuild();

        $found = false;
        foreach ($tracker->getCacheEntries() as $file_name => $timestamp) {
            // as set under alias "app" in config.yml
            if (preg_match('~/test/Fixture/Resources/assets/base\.js$~', $file_name)) {
                $found = true;
            }
        }

        if (!$found) {
            $this->fail('/test/Fixture/Resources/assets/base.js was not found in the tracker cache.');
        }
    }

    protected function tearDown()
    {
        `rm -rf {$this->compiled}`;

        parent::tearDown();
    }
}
