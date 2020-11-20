<?php
/**
 * @copyright 2019-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\BarBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \Hostnet\Component\Webpack\Asset\TemplateFinder
 */
class TemplateFinderTest extends TestCase
{
    use ProphecyTrait;

    public function testFindAllTemplates(): void
    {
        $kernel = $this->prophesize(Kernel::class);
        $kernel->getBundle()->shouldNotBeCalled();
        $kernel->getBundles()->willReturn(['BaseBundle' => new BarBundle()]);

        $finder = new TemplateFinder($kernel->reveal(), __DIR__ . '/../Fixtures/Resources');

        $templates = array_map(function ($template) {
            return $template->getLogicalName();
        }, $finder->findAllTemplates());

        self::assertCount(3, $templates);
        self::assertContains('BarBundle::base.format.engine', $templates);
        self::assertContains('BarBundle::this.is.a.template.format.engine', $templates);
        self::assertContains('BarBundle:controller:base.format.engine', $templates);
    }
}
