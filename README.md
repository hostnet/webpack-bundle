# hostnet/webpack-bridge

This package assumes you already have some basic knowledge about webpack; what it is and what it does for you. If this is not the case, please [read this first](http://webpack.github.io/docs/motivation.html). It will only take a minute, but it's worth it.

Instead of having all bundle-specific assets in one location, this package handles assets from __two__ different locations. The reasoning behind this decision is because webpack assets are compiled on the server and shouldn't be accessible from the browser.

 - `Resources/assets` contains files that are compiled by webpack.
 - `Resources/public` contains assets that are either symlinked or copied to `/web/bundles/<lowercased_bundle_name>/`

Both of these directories are being watched when running in _debug mode_. When an asset has been added, modified or deleted, compiled sources are updated directly.

- [hostnet/webpack-bridge](#hostnetwebpack-bridge)
  - [Installation](#installation)
  - [Quick how-to](#quick-how-to)
  - [Configuration](#configuration)
    - [Node](#node)
      - [Multi-platform configuration](#multi-platform-configuration)
    - [Bundle configuration](#bundle-configuration)
    - [Shared dependencies](#shared-dependencies)
    - [Asset output directories](#asset-output-directories)
  - [Loaders](#loaders)
    - [Less](#less)
    - [URL](#url)

## Installation

Install using [composer](https://getcomposer.org/).
```json
"require" : {
    "hostnet/webpack-bridge" : "1.*"
}
```
Once installed, enable the bundle `Hostnet\Bundle\WebpackBridge\WebpackBridgeBundle` in the `AppKernel` class.

## Quick how-to

> ___Warning___: the package assumes by default that `node` and `webpack` plus any additional modules are pre-installed on your system. If this is not the case, you need to specify some configuration in your `config.yml` file after enabling the bundle.

Imagine having the following files in your application:
 - `src/AppBundle/Resources/assets/app.js`
 - `src/AppBundle/Resources/assets/image.js`
 - `src/AppBundle/Resources/public/images/logo.png`
 - `src/AppBundle/Resources/views/base.html.twig`

Lets start with the twig template.
```twig
{# src/AppBundle/Resources/views/base.html.twig #}
<script src="{{ webpack_asset("@AppBundle/app.js").js }}"></script>
```
Use the twig function `webpack_asset(url)` in your template to specify an [entry-point](http://webpack.github.io/docs/configuration.html#entry).
In short, an entry-point is an asset that will be compiled and exported to the output path. This path defaults to
`%kernel.root_dir%/../web`. The compiled file will be named `app_bundle.app.js` by default. Both of these settings are configurable.

`webpack_asset` returns an array with two keys: `js` referencing the compiled javascript file and `css` referencing the compiled css file.
Beware that the `css` element may be blank if this file doesn't exist.

```javascript
// src/AppBundle/Resources/assets/app.js
var image = require('@AppBundle/image.js');
document.write(image('/bundles/app/images/logo.png'));
```
Any webpack asset may use the `require` or `define` functions that come with webpack. Webpack allows for both CommonJS and AMD-style loading of files. As you might have noticed, the example above references a bundle by its shorthand name, `@AppBundle`. The bridge automatically aliasses tracked bundles for you, so you're free to use this method of referencing dependencies throughout the entire application.

The `logo.png` file, located in the `Resources/public` directory will be symlinked or copied to `/web/bundles/<lowercased_bundle_name>` automatically. You should place any file that does not need processing in the public directory to avoid unnecessary load times in debug mode (app_dev).

Here is a simple image module that returns an image HTML-tag as string.
```javascript
// src/AppBundle/Resources/assets/image.js
module.exports = function (src) {
    return '<img src="' + src + '">;
};
```

## Configuration

The configuration options of this package are pretty large, but all settings are optional and come with sane defaults.

The following configuration options are directly copied from webpack itself and can be configured as such. The only difference to take into consideration is that keys are written in __underscores rathar than camelCase__.

For example, in webpack configuring the `output.publicPath` setting would be written as:
```yaml
webpack:
    output:
       public_path: '/public'
```

The following "webpack" sections are configurable through `config.yml`:
 - `output`: http://webpack.github.io/docs/configuration.html#output
 - `resolve`: http://webpack.github.io/docs/configuration.html#resolve
 - `resolve_loader`: http://webpack.github.io/docs/configuration.html#resolveloader

The options `entry`, `resolve.root`, `resolve.alias` and `resolveModule.modulesDirectories` are configured automatically based on split points (entry points), tracked bundles and the specified (or detected) node_modules directory. If you specify any of these options, their values will be appended to the generated values.

### Node

In order for this package to work properly, it needs to know the location where nodejs is installed and where to find its node_modules directory. If node is installed globally on your server, this setting may be omitted.

```yaml
# config.yml
webpack:
    node:
        binary: '/path/to/node-binary'
        node_modules_path: '/path/to/node_modules'
```

#### Multi-platform configuration

Since your application might be running on both windows, linux and macs all at the same time, you might need to specify different node binaries. If this is the case, instead of passing a string referencing the node binary to the `node.binary` option, you may pass an array:

```yaml
webpack:
    node:
       binary:
          win32: 'C:\\path\\to\\node32.exe'
          win64: 'C:\\path\\to\\node64.exe'
          linux_x32: '/usr/bin/node32'
          linux_x64: '/usr/bin/node64'
          darwin: '/path/to/node'
          fallback: '/if/os/detection/fails/path/to/node'
```

Again, all settings are optional. If a key isn't specified, it defaults to "node". This will only work if node is installed globally.

### Bundle configuration

By default, all enabled bundles are tracked. However, you may explicitly specify a set of bundles to track for performance or security reasons.

```yaml
webpack:
    bundles: ['AppBundle', 'YourBundle']
```

As previously mentioned, assets are located in two directories: `assets` and `public`. These options are configurable, but it's ___strongly recommended to leave this as is___. Vendor packages might not be aware of your custom configuration which results in not being able to resolve assets.

```yaml
webpack:
    bundle:
        resources_dir: 'Resources'
        assets_dir: 'assets'
        public_dir: 'public'
```

### Shared dependencies

Shared dependencies will be written to a separate javascript or css file if the option `output.common_id` is specified.

For example, the following configuration would output a `shared.js` and `shared.css` file.
```yaml
webpack:
    output:
        common_id: 'shared'
```

### Asset output directories

- Dumped assets from the "public" directory are symlinked or copied to the `<output.path>/<output.dump_dir>` directory.
- Compiled assets from the "assets" directory are written to the `<output.path>` directory.
- The twig function `webpack_asset` returns compiled filenames prefixed with the `<output.public_path>` directory.

```yaml
webpack:
    output:
        path: '%kernel.root_dir%/../web'
        dump_dir: '%kernel.root_dir%/../web/bundles'
        public_path: '/'
```

The `public_path` value represents the asset paths from a client-side perspecive. Therefore, it must specify a path that exists inside the DOCUMENT_ROOT directory.

For example, if the `output.path` value is `%kernel.root_dir/../web/packed`, the value of `output.public_path` must be set to `/packed`.

## Loaders

Loaders allow you to `require` files other than javascript. This package comes with 3 default loaders. 

 - `CSSLoader` : include CSS files
 - `UrlLoader` : include images (converted to base64)
 - `LessLoader`: include less files.
 
 Each loder has its own configuration under the `loaders` section.
 
 ### CSS
 
 Enables loading CSS files.
 
 ```yaml
 webpack:
     loaders:
         css:
             enabled: true
             filename: '[name].css'
             all_chunks: true
 ```
 
If `filename` and `all_chunks` are omitted, any CSS is converted to a style-tag in the document rather than being exported to a separate CSS file. If the `output.common_id` setting is specified - which allows extracting shared code - the [CommonsChunkPlugin]() will be used automatically as well.

Depending on the specified configuration, one or more node-modules are required:

 - `enabled:true` : style-loader, css-loader
 - `filename` : extract-text-webpack-plugin

### Less

Enables loading less files.

This plugin shares the exact same configuration settings as the CSS loader.

```webpack
webpack:
    loaders:
       less:
           enabled: true
           filename: '[name].css'
           all_chunks: true
```

### URL

Converts images to base64 code and embeds them in javascript.

This plugin only has an `enabled` setting. It is disabled by default.
```yaml
webpack:
    loaders:
       less:
           enabled: true
```
