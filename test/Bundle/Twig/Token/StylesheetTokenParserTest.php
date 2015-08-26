<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\StylesheetTokenParser
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class StylesheetTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $extension = $this->getMock(TwigExtension::class);
        $parser    = new StylesheetTokenParser($extension);

        $this->assertEquals(StylesheetTokenParser::TAG, $parser->getTag());
        $this->assertEquals('css', $parser->getAssetExtension());
    }
}
