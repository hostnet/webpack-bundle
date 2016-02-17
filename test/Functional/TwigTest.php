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

        $this->assertContains('src="/compiled/shared.js"', $html);
        $this->assertContains('href="/compiled/shared.css"', $html);
    }
}
