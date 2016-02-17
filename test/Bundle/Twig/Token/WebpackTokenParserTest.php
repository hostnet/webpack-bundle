<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $extension = new TwigExtension(__DIR__, '/compiled', '/bundles', '/compiled/shared.js', '/compiled/shared.css');
        $parser    = new WebpackTokenParser($extension);

        $this->assertEquals(WebpackTokenParser::TAG_NAME, $parser->getTag());
    }
}
