<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\Loader\LoaderInterface;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser
 */
class WebpackTokenParserTest extends TestCase
{
    public function testParser()
    {
        $loader    = $this->prophesize(LoaderInterface::class)->reveal();
        $extension = new TwigExtension(
            $loader,
            __DIR__,
            '/compiled',
            '/bundles',
            '/compiled/shared.js',
            '/compiled/shared.css'
        );

        $parser = new WebpackTokenParser($extension, $loader);

        self::assertEquals(WebpackTokenParser::TAG_NAME, $parser->getTag());
    }
}
