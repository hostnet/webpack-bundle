<?php
namespace Hostnet\Component\WebpackBundle\Configuration\Loader;

use Hostnet\Component\WebpackBundle\Configuration\CodeBlock;
use Hostnet\Component\WebpackBundle\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class UrlLoader implements LoaderInterface, ConfigExtensionInterface
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
                ->canBeDisabled()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (! $this->config['enabled']) {
            return [new CodeBlock()];
        }

        // @TODO Make extensions and mimetypes configurable.
        return [(new CodeBlock())->set(CodeBlock::LOADER, [
            '{ test: /\.png$/, loader: \'url-loader?mimetype=image/png\' }',
            '{ test: /\.jpg$/, loader: \'url-loader?mimetype=image/png\' }',
            '{ test: /\.gif$/, loader: \'url-loader?mimetype=image/png\' }'
        ])];
    }
}
