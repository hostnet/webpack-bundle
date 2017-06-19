<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Config;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\Container;

final class ResolveLoaderConfig implements ConfigInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        // Apply node_modules path to resolveLoader.root
        if (! empty($config['node']['node_modules_path'])) {
            $this->config['resolve_loader']['root'][] = $config['node']['node_modules_path'];
        }
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('resolve_loader')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('root')
                        ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        // Convert keys to camelCase.
        $config = [];
        foreach ($this->config['resolve_loader'] as $key => $value) {
            $config[lcfirst(Container::camelize($key))] = $value;
        }

        return [(new CodeBlock())->set(CodeBlock::RESOLVE_LOADER, $config)];
    }
}
