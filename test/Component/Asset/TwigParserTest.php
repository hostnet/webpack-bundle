<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @covers \Hostnet\Component\Webpack\Asset\TwigParser
 */
class TwigParserTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Tracker
     */
    private $tracker;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $cache_dir;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->tracker   = $this->getMockBuilder(Tracker::class)->disableOriginalConstructor()->getMock();
        $this->twig      = new Environment(new ArrayLoader([]));
        $this->cache_dir = sys_get_temp_dir();
    }

    public function testParseValid(): void
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
        $file   = __DIR__ . '/Fixtures/template.html.twig';
        $points = ($parser->findSplitPoints($file));

        self::assertCount(8, $points);
        self::assertArrayHasKey('@BarBundle/app.js', $points);
        self::assertArrayHasKey('@BarBundle/app2.js', $points);
        self::assertArrayHasKey('@BarBundle/app3.js', $points);
        self::assertArrayHasKey('@BarBundle/app4.js', $points);
        self::assertArrayHasKey('cache/' . md5($file . 0) . '.js', $points);
        self::assertArrayHasKey('cache/' . md5($file . 1) . '.js', $points);
        self::assertArrayHasKey('cache/' . md5($file . 2) . '.less', $points);
        self::assertArrayHasKey('cache/' . md5($file . 3) . '.css', $points);

        self::assertContains('foobar', $points);
    }

    public function testParseError(): void
    {
        $this->tracker->expects($this->never())->method('resolveResourcePath');
        $parser = new TwigParser($this->tracker, $this->twig, $this->cache_dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('template_parse_error.html.twig at line 3. Expected punctuation "(", got name.');

        $parser->findSplitPoints(__DIR__ . '/Fixtures/template_parse_error.html.twig');
    }

    public function testResolveError(): void
    {
        $this->tracker->expects($this->once())->method('resolveResourcePath')->willReturn(false);
        $parser = new TwigParser($this->tracker, $this->twig, $this->cache_dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('at line 3 could not be resolved.');

        $parser->findSplitPoints(__DIR__ . '/Fixtures/template.html.twig');
    }
}
