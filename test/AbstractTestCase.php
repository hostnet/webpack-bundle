<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AbstractTestCase extends TestCase
{
    protected function createTreeBuilder(string $config_root): TreeBuilder
    {
        if (Kernel::VERSION_ID >= 40200) {
            return new TreeBuilder($config_root);
        }

        if (Kernel::VERSION_ID >= 30300 && Kernel::VERSION_ID < 40200) {
            return new TreeBuilder();
        }

        throw new \RuntimeException('This bundle can only be used by Symfony 3.3 and up.');
    }

    protected function retrieveRootNode(TreeBuilder $tree_builder, string $config_root): NodeDefinition
    {
        if (Kernel::VERSION_ID >= 40200) {
            return $tree_builder->getRootNode();
        }

        if (Kernel::VERSION_ID >= 30300 && Kernel::VERSION_ID < 40200) {
            return $tree_builder->root($config_root);
        }

        throw new \RuntimeException('This bundle can only be used by Symfony 3.3 and up.');
    }
}
