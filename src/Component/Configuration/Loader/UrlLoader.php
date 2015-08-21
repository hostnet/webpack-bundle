<?php
namespace Hostnet\Component\WebpackBridge\Configuration\Loader;

use Hostnet\Component\WebpackBridge\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class UrlLoader implements LoaderInterface
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
        $this->config = $config['loaders']['url'];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('url')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlock()
    {
        if (! $this->config['enabled']) {
            return new CodeBlock();
        }

        // @TODO Make extensions and mimetypes configurable.
        return (new CodeBlock())->set(CodeBlock::LOADER, [
            '{ test: /\.png$/, loader: \'url-loader?mimetype=image/png\' }',
            '{ test: /\.jpg$/, loader: \'url-loader?mimetype=image/png\' }',
            '{ test: /\.gif$/, loader: \'url-loader?mimetype=image/png\' }'
        ]);
    }
}
