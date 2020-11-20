<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Twig;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Twig\Loader\LoaderInterface;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\TwigExtension
 */
class TwigExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testExtension(): void
    {
        $loader    = $this->prophesize(LoaderInterface::class)->reveal();
        $extension = new TwigExtension($loader, __DIR__, '/', '/bundles', '/shared.js', '/shared.css');

        self::assertEquals('webpack', $extension->getName());
        self::assertEquals(['js' => false, 'css' => false], $extension->webpackAsset('@AppBundle/app.js'));
        self::assertEquals('/shared.js?0', $extension->webpackCommonJs());
        self::assertEquals('/shared.css?0', $extension->webpackCommonCss());
    }

    /**
     * @dataProvider assetProvider
     */
    public function testAssets($expected, $asset, $web_dir, $dump_path, $public_path): void
    {
        $loader    = $this->prophesize(LoaderInterface::class)->reveal();
        $extension = new TwigExtension($loader, $web_dir, $public_path, $dump_path, '', '');
        self::assertEquals($expected, $extension->webpackPublic($asset));
    }

    public function assetProvider(): iterable
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
