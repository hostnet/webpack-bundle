<?php
namespace Hostnet\Bundle\WebpackBridge\Twig;

/**
 * @covers \Hostnet\Bundle\WebpackBridge\Twig\TwigExtension
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtension()
    {
        $extension = new TwigExtension('foobar');

        $this->assertEquals('webpack', $extension->getName());
        $this->assertCount(2, $extension->getFunctions());
        $this->assertEquals(['js'  => false, 'css' => false], $extension->webpackAsset('@AppBundle/app.js'));
    }
}
