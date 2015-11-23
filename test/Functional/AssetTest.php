<?php
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

        $twig_ext = static::$kernel->getContainer()->get('hostnet_webpack.bridge.twig_extension');

        $this->assertEquals('/bundles/henk.png', $twig_ext->webpackPublic('henk.png'));
    }

    public function testCompiledAsset()
    {
        $container = static::$kernel->getContainer();
        $twig_ext  = $container->get('hostnet_webpack.bridge.twig_extension');

//        $this->assertEquals([
//            'js'  => false,
//            'css' => false,
//        ], $twig_ext->webpackAsset('henk'));

        touch($this->compiled . 'app.henk.js');
        touch($this->compiled . 'app.henk.css');

        $resources = $twig_ext->webpackAsset('@App/henk.js');
        $this->assertContains('app.henk.js?', (string) $resources['js']);
        $this->assertContains('app.henk.css?', (string) $resources['css']);
    }

    protected function tearDown()
    {
        parent::tearDown();

        `rm -rf {$this->compiled}`;
    }
}
