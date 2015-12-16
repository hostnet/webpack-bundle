<?php
namespace Hostnet\Fixture\WebpackBundle\Bundle\BarBundle\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\CodeBlockProviderInterface;

class MockLoader implements CodeBlockProviderInterface
{
    const BLOCK_CONTENT = 'Webpack mock loader sample block content';

    public $code_blocks_called = false;

    public function getCodeBlocks()
    {
        $this->code_blocks_called = true;
        return [(new CodeBlock())->set(CodeBlock::LOADER, self::BLOCK_CONTENT)];
    }
}
