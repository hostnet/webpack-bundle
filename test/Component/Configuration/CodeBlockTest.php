<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * Class CodeBlockTest
 *
 * @covers \Hostnet\Component\Webpack\Configuration\CodeBlock
 */
class CodeBlockTest extends TestCase
{
    public function testCodeBlock(): void
    {
        $block = (new CodeBlock())->set(CodeBlock::HEADER, 'foo');
        self::assertTrue($block->has(CodeBlock::HEADER));
        self::assertEquals('foo', $block->get(CodeBlock::HEADER));
    }

    public function testInvalidChunk(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new CodeBlock())->set('foobar', true);
    }

    public function testGetInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new CodeBlock())->get(CodeBlock::HEADER);
    }

    public function testDuplicateChunk(): void
    {
        $block = new CodeBlock();
        $block->set(CodeBlock::HEADER, 'foo');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The chunk "header" is already in use.');

        $block->set(CodeBlock::HEADER, 'bar');
    }
}
