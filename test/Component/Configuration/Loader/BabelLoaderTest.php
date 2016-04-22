<?php
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @covers Hostnet\Component\Webpack\Configuration\Loader\BabelLoader
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class BabelLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('webpack')->children();

        BabelLoader::applyConfiguration($node);
        $node->end();

        $config = $tree->buildTree()->finalize([]);

        $this->assertArrayHasKey('babel', $config);
        $this->assertArrayHasKey('enabled', $config['babel']);
    }

    public function testGetCodeBlockDefault()
    {
        $config = new BabelLoader();
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetCodeBlockDisabled()
    {
        $config = new BabelLoader(['loaders' => ['babel' => ['enabled' => false]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertFalse($block->has(CodeBlock::LOADER));
    }

    public function testGetPresetsCodeBlock()
    {
        $data = [
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'presets' => NULL]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'presets' => []]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'presets' => ['es2015', 'react']]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory,presets[]=es2015,presets[]=react' }"
            ]
        ];

        foreach ($data as $d) {
            $config = new BabelLoader($d['source']);
            $block  = $config->getCodeBlocks()[0];
            $this->assertTrue($block->has(CodeBlock::LOADER));

            $loader = $block->get(CodeBlock::LOADER);
            $this->assertEquals($loader, $d['output']);
        }
    }

    public function testGetExcludeCodeBlock()
    {
        $data = [
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'exclude' => NULL]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'exclude' => []]]],
                "output" => "{ test: /\.jsx$/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'exclude' => ['node_modules']]]],
                "output" => "{ test: /\.jsx$/, exclude: /(node_modules)/, loader: 'babel-loader?cacheDirectory' }"
            ],
            [
                "source" => ['loaders' => ['babel' => ['enabled' => true, 'exclude' => ['node_modules', 'some_dir']]]],
                "output" => "{ test: /\.jsx$/, exclude: /(node_modules|some_dir)/, loader: 'babel-loader?cacheDirectory' }"
            ]
        ];

        foreach ($data as $d) {
            $config = new BabelLoader($d['source']);
            $block  = $config->getCodeBlocks()[0];
            $this->assertTrue($block->has(CodeBlock::LOADER));

            $loader = $block->get(CodeBlock::LOADER);
            $this->assertEquals($loader, $d['output']);
        }
    }

    public function testGetCodeBlock()
    {
        $config = new BabelLoader(['loaders' => ['babel' => ['enabled' => true]]]);
        $block  = $config->getCodeBlocks()[0];

        $this->assertTrue($block->has(CodeBlock::LOADER));
    }
}
