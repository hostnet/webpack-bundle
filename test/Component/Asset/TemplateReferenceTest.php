<?php
/**
 * @copyright 2019-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\Webpack\Asset\TemplateReference
 */
class TemplateReferenceTest extends TestCase
{
    public function testGeneric(): void
    {
        $reference = new TemplateReference('AcmeBlogBundle', 'Admin\Post', 'index', 'html', 'twig');
        self::assertSame('@AcmeBlogBundle/Resources/views/Admin/Post/index.html.twig', $reference->getPath());
        self::assertSame('AcmeBlogBundle:Admin\Post:index.html.twig', $reference->getLogicalName());

        $reference = new TemplateReference(null, 'Admin\Post', 'index', 'html', 'twig');
        self::assertSame('views/Admin/Post/index.html.twig', $reference->getPath());
        self::assertSame(':Admin\Post:index.html.twig', $reference->getLogicalName());
    }
}
