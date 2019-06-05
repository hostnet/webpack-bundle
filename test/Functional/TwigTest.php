<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Functional;

use Hostnet\Fixture\WebpackBundle\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser
 */
class TwigTest extends KernelTestCase
{
    public function testTemplates(): void
    {
        static::bootKernel();

        /** @var TwigEngine $twig_ext */
        $twig = static::$kernel->getContainer()->get('twig');
        $html = $twig->render('/common_id.html.twig');

        self::assertRegExp('~src="/compiled/shared\.js\?[0-9]+"~', $html);
        self::assertRegExp('~href="/compiled/shared\.css\?[0-9]+"~', $html);

        $twig->render('/template.html.twig');
    }
}
