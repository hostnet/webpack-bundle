<?php
namespace Hostnet\Bundle\WebpackBundle\Twig;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\TwigExtension
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtension()
    {
        $extension = new TwigExtension(__DIR__, '/', '/bundles');

        $this->assertEquals('webpack', $extension->getName());
        $this->assertCount(2, $extension->getFunctions());
        $this->assertCount(1, $extension->getTokenParsers());
        $this->assertEquals(['js'  => false, 'css' => false], $extension->webpackAsset('@AppBundle/app.js'));
    }

    /**
     * @dataProvider assetProvider
     */
    public function testAssets($expected, $asset, $web_dir, $dump_path, $public_path)
    {
        $extension = new TwigExtension($web_dir, $public_path, $dump_path);
        $this->assertEquals($expected, $extension->webpackPublic($asset));
    }

    public function assetProvider()
    {
        return [
            ['/bundles/img.png', 'img.png', __DIR__ . '/web/', '/bundles/', '/'],
            ['/img.png', 'img.png', __DIR__ . 'web/', '/', '/'],
            ['/bundles/app/img.png', '@App/img.png', __DIR__ . '/../web/', '/bundles/', '/'],
            ['/some/dir/app/img.png', '@App/img.png', __DIR__ . './web/', '/some/dir/', '/'],
            ['/bundles/some/img.png', '@SomeBundle/img.png', __DIR__ . '/../web/', '/bundles/', '/'],
            ['/bundles/some/test/img.png', '@SomeBundle/test/img.png', './web/', '/bundles/', '/'],
            ['/something/else/some/test/img.png', '@SomeBundle/test/img.png', '/web/', '/something/else/', '/'],
        ];
    }
}
