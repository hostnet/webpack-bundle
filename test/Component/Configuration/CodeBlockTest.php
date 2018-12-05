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
    public function testCodeBlock()
    {
        $block = (new CodeBlock())->set(CodeBlock::HEADER, 'foo');
        self::assertTrue($block->has(CodeBlock::HEADER));
        self::assertEquals('foo', $block->get(CodeBlock::HEADER));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidChunk()
    {
        (new CodeBlock())->set('foobar', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalid()
    {
        (new CodeBlock())->get(CodeBlock::HEADER);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The chunk "header" is already in use.
     */
    public function testDuplicateChunk()
    {
        $block = new CodeBlock();
        $block->set(CodeBlock::HEADER, 'foo');
        $block->set(CodeBlock::HEADER, 'bar');
    }
}
