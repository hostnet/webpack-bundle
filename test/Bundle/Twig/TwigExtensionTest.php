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
        $extension = new TwigExtension(__DIR__, '/', '/bundles', '/shared.js', '/shared.css');

        $this->assertEquals('webpack', $extension->getName());
        $this->assertEquals(['js'  => false, 'css' => false], $extension->webpackAsset('@AppBundle/app.js'));
        $this->assertEquals('/shared.js?0', $extension->webpackCommonJs());
        $this->assertEquals('/shared.css?0', $extension->webpackCommonCss());
    }

    /**
     * @dataProvider assetProvider
     */
    public function testAssets($expected, $asset, $web_dir, $dump_path, $public_path)
    {
        $extension = new TwigExtension($web_dir, $public_path, $dump_path, '', '');
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
