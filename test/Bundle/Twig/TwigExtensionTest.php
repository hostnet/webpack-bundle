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
        $this->assertCount(1, $extension->getFunctions());
        $this->assertEquals([
            'js'  => 'foobar/app_bundle.app.js',
            'css' => 'foobar/app_bundle.app.css'
        ], $extension->webpack('@AppBundle/app.js'));
    }
}
