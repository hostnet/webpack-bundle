<?php
namespace Hostnet\Component\WebpackBridge\Configuration;

/**
 * Class CodeBlockTest
 *
 * @covers \Hostnet\Component\WebpackBridge\Configuration\CodeBlock
 * @author Harold Iedema <harold@iedema.me>
 */
class CodeBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testCodeBlock()
    {
        $block = (new CodeBlock())->set(CodeBlock::HEADER, 'foo');
        $this->assertTrue($block->has(CodeBlock::HEADER));
        $this->assertEquals('foo', $block->get(CodeBlock::HEADER));
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
