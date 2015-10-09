<?php
namespace Hostnet\Component\Webpack\Asset;

/**
 * @covers \Hostnet\Component\Webpack\Asset\TwigParser
 * @author Harold Iedema <harold@iedema.me>
 */
class TwigParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Tracker
     */
    private $tracker;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $cache_dir;

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->tracker   = $this->getMockBuilder(Tracker::class)->disableOriginalConstructor()->getMock();
        $this->twig      = new \Twig_Environment();
        $this->path      = realpath(__DIR__ . '/../../Fixture');
        $this->cache_dir = '/tmp';
    }

    public function testParseValid()
    {
        // Call count expectations:
        //  1: webpack_asset.js
        //  2: webpack_asset.css
        //  3: {% webpack js %}
        //  4: {% webpack js %}
        //  5: {% webpack css %}
        //  6: {% webpack inline %}
        //  7: {% webpack inline %}
        //  8: {% webpack inline less %}
        //  9: {% webpack inline css %}
        $this->tracker->expects($this->exactly(9))->method('resolveResourcePath')->willReturn('foobar');

        $parser = new TwigParser($this->tracker, $this->twig, $this->cache_dir);
        $file   = $this->path . '/Resources/template.html.twig';
        $points = ($parser->findSplitPoints($file));

        $this->assertCount(8, $points);
        $this->assertArrayHasKey('@BarBundle/app.js', $points);
        $this->assertArrayHasKey('@BarBundle/app2.js', $points);
        $this->assertArrayHasKey('@BarBundle/app3.js', $points);
        $this->assertArrayHasKey('@BarBundle/app4.js', $points);
        $this->assertArrayHasKey('cache/' . md5($file . 0) . '.js', $points);
        $this->assertArrayHasKey('cache/' . md5($file . 1) . '.js', $points);
        $this->assertArrayHasKey('cache/' . md5($file . 2) . '.less', $points);
        $this->assertArrayHasKey('cache/' . md5($file . 3) . '.css', $points);

        $this->assertContains('foobar', $points);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage template_parse_error.html.twig at line 3. Expected punctuation "(", got name.
     */
    public function testParseError()
    {
        $this->tracker->expects($this->never())->method('resolveResourcePath');

        $parser = new TwigParser($this->tracker, $this->twig, $this->cache_dir);
        $parser->findSplitPoints($this->path . '/Resources/template_parse_error.html.twig');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage at line 3 could not be resolved.
     */
    public function testResolveError()
    {
        $this->tracker->expects($this->once())->method('resolveResourcePath')->willReturn(false);

        $parser = new TwigParser($this->tracker, $this->twig, $this->cache_dir);
        $parser->findSplitPoints($this->path . '/Resources/template.html.twig');
    }
}
