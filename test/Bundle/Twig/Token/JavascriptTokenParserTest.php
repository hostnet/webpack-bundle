<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\JavascriptTokenParser
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class JavascriptTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $extension = $this->getMock(TwigExtension::class);
        $parser    = new JavascriptTokenParser($extension);

        $this->assertEquals(JavascriptTokenParser::TAG, $parser->getTag());
        $this->assertEquals('js', $parser->getAssetExtension());
    }
}
