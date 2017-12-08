<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Plugin;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @see https://github.com/webpack/docs/wiki/list-of-plugins#uglifyjsplugin
 * @see http://lisperator.net/uglifyjs/compress
 *
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
final class UglifyJsPlugin implements PluginInterface, ConfigExtensionInterface
{
    private static $config_map = [
        ['sequences', true, 'join consecutive statemets with the "comma operator"'],
        ['properties', true, 'optimize property access: a["foo"] → a.foo'],
        ['dead_code', true, 'discard unreachable code'],
        ['drop_debugger', true, 'discard "debugger" statements'],
        ['unsafe', false, 'some unsafe optimizations'],
        ['conditionals', true, 'optimize if-s and conditional expressions'],
        ['comparisons', true, 'optimize comparisons'],
        ['evaluate', true, 'evaluate constant expressions'],
        ['booleans', true, 'optimize boolean expressions'],
        ['loops', true, 'optimize loops'],
        ['unused', true, 'drop unused variables/functions'],
        ['hoist_funs', true, 'hoist function declarations'],
        ['hoist_vars', false, 'hoist variable declarations'],
        ['if_return', true, 'optimize if-s followed by return/continue'],
        ['join_vars', true, 'join var declarations'],
        ['cascade', true, 'try to cascade `right` into `left` in sequences'],
        ['side_effects', true, 'drop side-effect-free statements'],
        ['warnings', true, 'warn about potentially dangerous optimizations/code'],
    ];

    /**
     * @var string
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = isset($config['plugins']['uglifyjs'])  ? $config['plugins']['uglifyjs'] : [];
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $uglify = $node_builder
            ->arrayNode('uglifyjs')
            ->canBeEnabled()
                ->children();

        $compress = $uglify
            ->arrayNode('compress')
                ->addDefaultsIfNotSet()
                ->children();

        foreach (self::$config_map as list ($option, $default, $info)) {
            $compress
                ->booleanNode($option)
                    ->defaultValue($default)
                    ->info($info)
                ->end();
        }

        $compress
            ->arrayNode('global_defs')
                ->info('global definition')
                ->prototype('scalar')
                ->end()
            ->end();

        $uglify
            ->arrayNode('mangle_except')
                ->defaultValue(['$super', '$', 'exports', 'require'])
                ->info('Variable names to not mangle')
                ->prototype('scalar')
            ->end();

        $uglify
            ->booleanNode('source_map')
                ->defaultTrue()
                ->info(sprintf(
                    '%s %s',
                    'The plugin uses SourceMaps to map error message locations to modules.',
                    'This slows down the compilation'
                ))
            ->end();

        $uglify
            ->scalarNode('test')
                ->defaultValue('/\.js($|\?)/i')
                ->info('RegExp to filter processed files')
            ->end();

        $uglify
            ->booleanNode('minimize')
                ->defaultTrue()
                ->info('Whether to minimize or not')
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        if (empty($this->config) || !$this->config['enabled']) {
            return [];
        }

        $compress   = json_encode($this->config['compress']);
        $source_map = json_encode($this->config['source_map']);
        $test       = $this->config['test'];
        $minimize   = json_encode($this->config['minimize']);
        $mangle     = !empty($this->config['mangle_except'])
            ? '{except:' . json_encode($this->config['mangle_except']) . '}'
            : 'false';

        $config = <<<CONFIG
{
    compress: $compress,
    mangle: $mangle,
    sourceMap: $source_map,
    test: $test,
    minimize: $minimize
}
CONFIG;

        return [(new CodeBlock())
            ->set(CodeBlock::PLUGIN, sprintf('new %s(%s)', 'webpack.optimize.UglifyJsPlugin', $config))];
    }
}
