<?php
namespace Hostnet\Bundle\WebpackBundle\Command;

use Hostnet\Component\Webpack\Asset\CacheGuard;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers Hostnet\Bundle\WebpackBundle\Command\CompileCommand
 */
class CompileCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Simple test to see the validate function is executed from the CacheGard class.
     */
    public function testCompileCommand()
    {
        $guard = $this->prophesize(CacheGuard::class);
        $guard->validate()->shouldBeCalled();

        $compile_command = new CompileCommand($guard->reveal());

        $compile_command->run(new StringInput(''), new NullOutput());
    }
}
