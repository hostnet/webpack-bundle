<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\DependencyInjection;

use Hostnet\Component\Webpack\Configuration\Loader\CssLoader;
use Hostnet\Component\Webpack\Configuration\Plugin\DefinePlugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $config = new Configuration([], [DefinePlugin::class, CssLoader::class]);
        $tree   = $config->getConfigTreeBuilder();
        $final  = $tree->buildTree()->finalize([]);

        self::assertArrayHasKey('node', $final);
        self::assertArrayHasKey('compile_timeout', $final);

        self::assertArrayHasKey('binary', $final['node']);
        self::assertArrayHasKey('win32', $final['node']['binary']);
        self::assertArrayHasKey('win64', $final['node']['binary']);
        self::assertArrayHasKey('linux_x32', $final['node']['binary']);
        self::assertArrayHasKey('linux_x64', $final['node']['binary']);
        self::assertArrayHasKey('darwin', $final['node']['binary']);
        self::assertArrayHasKey('fallback', $final['node']['binary']);
    }
}
