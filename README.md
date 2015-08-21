# hostnet/webpack-bridge
This symfony2 bundle integrates assets compiled using webpack into your Symfony application.

# Installation

Install using composer.
```bash
composer.phar require "hostnet/webpack-bridge"
```

Once installed, simply enable the bundle `Hostnet\Bundle\WebpackBridge\WebpackBridgeBundle()` in your AppKernel.

# Configuration

All configuration is optional except for the binary pointing to webpack.

#### Example configuration
```yaml
webpack:
    bundles: ['AppBundle', 'YourBundle']
    node:
       binary: '/path/to/node /path/to/webpack/bin/webpack.js'
       node_modules_path: '/path/to/node_modules'
    output:
        path: '%kernel.root_dir%/../web'
        filename: '[name].js'
        common_id: 'shared_code'
    resolve:
        asset_path: 'Resources/public'
```
