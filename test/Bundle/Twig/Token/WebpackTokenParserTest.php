<?php
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackTokenParserTest extends TestCase
{
    public function testParser()
    {
        $loader    = $this->prophesize(\Twig_LoaderInterface::class)->reveal();
        $extension = new TwigExtension($loader, __DIR__, '/compiled', '/bundles', '/compiled/shared.js', '/compiled/shared.css');
        $parser    = new WebpackTokenParser($extension, $loader);

        self::assertEquals(WebpackTokenParser::TAG_NAME, $parser->getTag());
    }
}
