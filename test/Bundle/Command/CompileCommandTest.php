<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Command;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\Command\CompileCommand
 */
class CompileCommandTest extends TestCase
{
    /**
     * Simple test to see the validate function is executed from the CacheGard class.
     */
    public function testCompileCommand(): void
    {
        $guard = $this->prophesize(CacheGuard::class);
        $guard->rebuild()->shouldBeCalled();

        $compile_command = new CompileCommand($guard->reveal());

        $compile_command->run(new StringInput(''), new NullOutput());
    }
}
