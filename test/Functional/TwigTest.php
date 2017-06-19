<?php
declare(strict_types = 1);
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Twig\Token\WebpackTokenParser
 */
class TwigTest extends KernelTestCase
{
    public function testTemplates()
    {
        static::bootKernel();

        /* @var $twig_ext TwigEngine */
        $twig = static::$kernel->getContainer()->get('templating');
        $html = $twig->render('/common_id.html.twig');

        self::assertRegExp('~src="/compiled/shared\.js\?[0-9]+"~', $html);
        self::assertRegExp('~href="/compiled/shared\.css\?[0-9]+"~', $html);

        $twig->render('/template.html.twig');
    }
}
