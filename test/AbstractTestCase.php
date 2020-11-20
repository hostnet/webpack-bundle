<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AbstractTestCase extends TestCase
{
    protected function createTreeBuilder(string $config_root): TreeBuilder
    {
        return new TreeBuilder($config_root);
    }

    protected function retrieveRootNode(TreeBuilder $tree_builder, string $config_root): NodeDefinition
    {
        return $tree_builder->getRootNode();
    }
}
