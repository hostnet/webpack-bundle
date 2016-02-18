<?php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigEngine;

class TwigTest extends KernelTestCase
{
    public function testTemplates()
    {
        static::bootKernel();

        /* @var $twig_ext TwigEngine */
        $twig = static::$kernel->getContainer()->get('templating');
        $html = $twig->render('/common_id.html.twig');

        $this->assertRegExp('~src="/compiled/shared\.js\?[0-9]+"~', $html);
        $this->assertRegExp('~href="/compiled/shared\.css\?[0-9]+"~', $html);
    }
}
