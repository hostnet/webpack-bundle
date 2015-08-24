<?php
namespace Hostnet\Component\Webpack\Configuration;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
interface ConfigExtensionInterface
{
    /**
     * Applies plugin-specific configuration to the TreeBuilder used to parse configuration from the application. This
     * method is declared static because we can't instantiate anything from the container at this point.
     *
     * See http://symfony.com/doc/current/components/config/definition.html for more information regarding creating
     * configuration.
     *
     * @param  NodeBuilder $node_builder
     * @return string
     */
    public static function applyConfiguration(NodeBuilder $node_builder);

    /**
     * Returns the CodeBlock for this plugin.
     *
     * @return CodeBlock[]
     */
    public function getCodeBlocks();
}
