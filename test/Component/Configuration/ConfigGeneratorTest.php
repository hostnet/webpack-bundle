<?php
namespace Hostnet\Component\Webpack\Configuration;

use Hostnet\Component\Webpack\Configuration\Config\OutputConfig;
use Hostnet\Component\Webpack\Configuration\Loader\CSSLoader;
use Hostnet\Component\Webpack\Configuration\Plugin\DefinePlugin;

/**
 * @covers \Hostnet\Component\Webpack\Configuration\ConfigGenerator
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class ConfigGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $config = new ConfigGenerator();
        $config
            ->addBlock((new CodeBlock())->set(CodeBlock::HEADER, 'var a = require("b");'))
            ->addBlock((new CodeBlock())->set(CodeBlock::ENTRY, ['a' => '/path/to/a.js']))
            ->addBlock((new CodeBlock())->set(CodeBlock::ENTRY, ['b' => '/path/to/b.js']))
            ->addBlock((new CodeBlock())->set(CodeBlock::OUTPUT, ['a' => 'a']))
            ->addBlock((new CodeBlock())->set(CodeBlock::OUTPUT, ['b' => 'b', 'c' => 'c']))
            ->addBlock((new CodeBlock())->set(CodeBlock::RESOLVE, ['root' => ['a' => 'b']]))
            ->addBlock((new CodeBlock())->set(CodeBlock::RESOLVE, ['root' => ['b' => 'c']]))
            ->addBlock((new CodeBlock())->set(CodeBlock::RESOLVE, ['alias' => ['a' => 'b', 'b' => 'c']]))
            ->addBlock((new CodeBlock())->set(CodeBlock::RESOLVE, ['alias' => ['c' => 'a']]))
            ->addBlock((new CodeBlock())->set(CodeBlock::RESOLVE_LOADER, ['root' => '/path/to/node_modules']));

        // Add loaders...
        $config->addBlock((new CodeBlock())->set(CodeBlock::LOADER, '{ test: /\.css$/, loader: "style!some-loader" }'));
        $config->addBlock((new CodeBlock())->set(CodeBlock::POST_LOADER, '{ test: /\.inl$/, loader: "style" }'));
        $config->addBlock(
            (new CodeBlock())
                ->set(CodeBlock::HEADER, 'var preLoader1 = require("pre-loader-1");')
                ->set(CodeBlock::PRE_LOADER, '{ test: /\.css$/, loader: preLoader1.execute("a", "b") }')
        );
        $config->addBlock(
            (new CodeBlock())
                ->set(CodeBlock::HEADER, 'var preLoader2 = require("pre-loader-2");')
                ->set(CodeBlock::PRE_LOADER, '{ test: /\.less$/, loader: preLoader2.execute("c", "d") }')
        );

        // And some plugins
        $config->addBlock((new DefinePlugin(['plugins' => ['constants' => ['a' => 'b']]]))->add('b', 'c')->getCodeBlocks()[0]);
        $config->addBlock((new DefinePlugin(['plugins' => ['constants' => ['c' => 'd']]]))->add('d', 'e')->getCodeBlocks()[0]);

        // Add extension
        $config->addExtension(new OutputConfig(['output' => ['path' => 'path/to/output']]));
        $config->addExtension(new CSSLoader(['loaders' => ['css' => ['enabled' => true, 'filename' => 'foo', 'all_chunks' => true]]]));

        $fixture_file = __DIR__ . '/../../Fixture/Component/Configuration/ConfigGenerator.js';
        // file_put_contents($fixture_file, $config->getConfiguration());
        $fixture = file_get_contents($fixture_file);

        $this->assertEquals(str_replace("\r\n", "\n", $fixture), str_replace("\r\n", "\n", $config->getConfiguration()));
    }
}
