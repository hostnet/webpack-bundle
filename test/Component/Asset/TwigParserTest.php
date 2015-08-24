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

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->tracker = $this->getMockBuilder(Tracker::class)->disableOriginalConstructor()->getMock();
        $this->twig    = new \Twig_Environment();
        $this->path    = realpath(__DIR__ . '/../../Fixture');
    }

    public function testParseValid()
    {
        $this->tracker->expects($this->exactly(2))->method('resolveResourcePath')->willReturn('foobar');

        $parser = new TwigParser($this->tracker, $this->twig);
        $points = ($parser->findSplitPoints($this->path . '/Resources/template.html.twig'));

        $this->assertArrayHasKey('@BarBundle/app.js', $points);
        $this->assertContains('foobar', $points);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage template_parse_error.html.twig at line 3. Expected punctuation "(", got name.
     */
    public function testParseError()
    {
        $this->tracker->expects($this->never())->method('resolveResourcePath');

        $parser = new TwigParser($this->tracker, $this->twig);
        $parser->findSplitPoints($this->path . '/Resources/template_parse_error.html.twig');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage at line 3 could not be resolved.
     */
    public function testResolveError()
    {
        $this->tracker->expects($this->once())->method('resolveResourcePath')->willReturn(false);

        $parser = new TwigParser($this->tracker, $this->twig);
        $parser->findSplitPoints($this->path . '/Resources/template.html.twig');
    }


}
