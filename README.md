**Abandoned**

This package is abandoned.
Use alternative tools like:

- [hostnet/asset-bundle]('https://github.com/hostnet/asset-bundle')
- [Webpack Encore]('https://symfony.com/doc/4.4/frontend.html')


# hostnet/webpack-bundle

- [Introduction](#introduction)
- [Installation](#installation)
- [Upgrading to 2.0](#upgrading-to-2.0)
- [Quick how-to](#quick-how-to)
- [Twig tag](#twig-tag)
- [Configuration](#configuration)
  - [Node](#node)
    - [Multi-platform configuration](#multi-platform-configuration)
  - [Bundle configuration](#bundle-configuration)
    - [Adding app resources to the tracked assets](#adding-app-resources-to-the-tracked-assets)
  - [Shared dependencies](#shared-dependencies)
  - [Asset output directories](#asset-output-directories)
  - [Ideal configuration](#ideal-configuration)
  - [Compile Timeout](#compile-timeout)
- [Loaders](#loaders)
  - [CSS](#css)
  - [Less](#less)
  - [Sass](#sass)
  - [URL](#url)
  - [TypeScript](#typescript)
  - [CoffeeScript](#coffeescript)
- [Plugins](#plugins)
  - [DefinePlugin](#defineplugin)
  - [ProvidePlugin](#provideplugin)
  - [UglifyJS](#uglifyjs)

## Introduction

This package assumes you already have some basic knowledge about webpack; what it is and what it does for you. If this
is not the case, please [read this first](http://webpack.github.io/docs/motivation.html). It will only take a minute,
but it's worth it.

Instead of having all bundle-specific assets in one location, this package handles assets from __two__ different
locations. The reasoning behind this decision is because webpack assets are compiled on the server and shouldn't be
accessible from the browser.

 - `Resources/assets` contains files that are compiled by webpack.
 - `Resources/public` contains assets that are either symlinked or copied to `/<dump_path>/<lowercased_bundle_name>/`

Both of these directories are being watched when running in _debug mode_. When an asset has been added, modified or
deleted, compiled sources are updated directly.

The package comes with two _twig functions_:

 - `webpack_asset(url)` : Resolves compiled asset files. E.g.: `webpack_asset('@AppBundle/app.js')` resolves to `Resources/assets/app.js` in AppBundle.
 - `webpack_public(url)`: Resolves dumped public assets from the `Resources/public` directory. Bundle referencing works the same way as in `webpack_asset`.

Please note that `webpack_asset` returns an array with two keys: `js` and `css`. More info on this in the
[Quick how-to](#quick-how-to) example.

## Installation

Install using [composer](https://getcomposer.org/).
```json
"require" : {
    "hostnet/webpack-bundle" : "1.*"
}
```
Once installed, enable the bundle `Hostnet\Bundle\WebpackBundle\WebpackBundle` in the `AppKernel` class.

> ___Warning___: In order to have the webpack twig tag detect the compiled files, webpack has to be compiled already. Therefore
>  it's mandatory to run the compile command `webpack:compile` __before__ the cache warmpup.


## Upgrading to 2.0

Due to some breaking changes in Twig, webpack-bundle 2.0 was released to be made compatible with the new twig version.

To ensure a smooth upgrade, please ensure that you have the following in your project:

- PHP 7.1 or higher
- Symfony 3.3.0 or higher
- Twig 2.4.0 or higher

There are no configuration changes made in this version. If your project uses the package versions as mentioned above, 
you should not run into any issues when upgrading webpack-bundle to 2.0.
 
The following additional changes were made in this version:

- Use PHP 7 strict type declaration
- Use namespaced Twig classes, which are introduced in Twig 2.4 for future compatibility (e.g. `\Twig\Environemnt` instead of `\Twig_Environment`)
- Enforce code style using `phpcs` and enforcing the extra ruleset specified by Hostnet.
- The class `CSSLoader` was renamed to `CssLoader` because our code style rules enforce this.
- Generated file names for inline javascript/css source are now coming from `TokenStream->getSourceContext()->getName()` instead of `TokenStream->getFilename()`. The latter function was removed from Twig.
- Created FCQN services and added aliases to ensure BC.
- Made the bundle compatible with Symfony (3.)4 

## Quick how-to

> ___Warning___: the package assumes by default that `node` and `webpack` plus any additional modules are pre-installed
> on your system. If this is not the case, you need to specify some configuration in your `config.yml` file after
> enabling the bundle. See [Node Configuration](#node) for more details.

Imagine having the following files in your application:
 - `src/AppBundle/Resources/assets/app.js`
 - `src/AppBundle/Resources/assets/image.js`
 - `src/AppBundle/Resources/public/images/logo.png`
 - `src/AppBundle/Resources/views/base.html.twig`

Lets start with the twig template.
```twig
{# src/AppBundle/Resources/views/base.html.twig #}

The quick variant:
<script src="{{ webpack_asset('@AppBundle/app.js').js }}"></script>

The more maintainable variant: Store the result to a variable for easy access.
{% set asset = webpack_asset('@AppBundle/app.js') %}

<script src="{{ asset.js }}"></script>
{% if asset.css is not empty %}
    <link rel="stylesheet" href="{{ asset.css }}">
{% endif %}
```

Use the twig function `webpack_asset(url)` in your template to specify an
[entry-point](http://webpack.github.io/docs/configuration.html#entry). In short, an entry-point is an asset that will be
compiled and exported to the output path. This path defaults to `%kernel.root_dir%/../web`. The compiled file will be
named `app_bundle.app.js` by default. Both of these settings are configurable.

`webpack_asset` returns an array with two keys: `js` referencing the compiled javascript file and `css` referencing the
compiled css file. Beware that the `css` element may be blank if this file doesn't exist. The latter would occur if the
referenced javascript file - or its dependencies - doesn't include any CSS-type files.

```javascript
// src/AppBundle/Resources/assets/app.js
var image = require('@AppBundle/image.js');
document.write(image('/bundles/app/images/logo.png'));
```
Any webpack asset may use the `require` or `define` functions that come with webpack. Webpack allows for both CommonJS
and AMD-style loading of files. As you might have noticed, the example above references a bundle by its shorthand name,
`@AppBundle`. The bundle automatically aliases tracked bundles for you, so you're free to use this method of
referencing dependencies throughout the entire application.

The `logo.png` file, located in the `Resources/public` directory will be symlinked or copied to
`/<dump_path>/<lowercased_bundle_name>` automatically. You should place any file that does not need processing in the
public directory to avoid unnecessary load times in debug mode (app_dev).

Here is a simple image module that returns an image HTML-tag as string.
```javascript
// src/AppBundle/Resources/assets/image.js
module.exports = function (src) {
    return '<img src="' + src + '">';
};
```

## Twig tag

Aside from the `webpack_asset` twig function, you can also use the `webpack` tag to specify one or more entry points in
a much more elegant fashion. The syntax of this tag works like this:

```twig
{% webpack <type: css|js> <list-of-javascript-files> %}
    {{ asset }}
{% endwebpack %}
```
Or if you want to have inline code without including a file:
```twig
{% webpack <type: inline> [file-type] %}
    <javascript, css, less, scss, ... code>
{% endwebpack %}
```

If you want to include javascript files, simply do this:
```twig
{% webpack js
   '@AppBundle/file1.js'
   '@AppBundle/file2.js'
%}
    <script src="{{ asset }}"></script>
{% endwebpack %}
```
Or the inline variant:
```twig
{% webpack inline %}
<script type="text/javascript">
    console.log("Hello world!");
</script>
{% endwebpack %}
```

The same method can be applied for CSS files.
```twig
{% webpack css '@AppBundle/file1.js' %}
    <link rel="stylesheet" href="{{ asset }}">
{% endwebpack %}
```

Note that in the CSS example, we're still referencing javascript files. _This is not a mistake._ Webpack extracts
referenced CSS files from javascript files and places them in separate css files - if the bundle
was configured to do so. If you want to include an already existing CSS file, just use the regular method of doing so.
For more information about CSS file exportation, please refer to the [CSS loader configuration](#css).

> __Warning__: Due to the nature of split point detection, expressions are not parsed! Only strings types are accepted.
> The reason behind this - as previously mentioned - performance. All twig templates are tokenized on request in debug-
> mode. Just tokenizing them is a lot faster than actually parsing every single one of them.

### More about the inline variant

As mentioned in the example above, you may optionally specify a file type along with the `inline` type. This can be any
type of file extension, just make sure you have the appropriate loaders enabled.

For example, `js` will always work by default. However, `css` will only work if the `css-loader` is enabled. If you have
the `less-loader` or `sass-loader` enabled, you can do something like this:

Less version

```twig
<section>
    {% webpack inline less %}
    <style>
    @color: #f00;
    @size: 42px;

    body {
        color: @color;
        section : {
            size: @size;
        }
    }
    </style>
    {% endwebpack %}
</section>
```

Sass Version

```twig
<section>
    {% webpack inline sass %}
    <style>
    $color: #f00;
    $size: 42px;

    body {
        color: $color;
        section : {
            size: $size;
        }
    }
    </style>
    {% endwebpack %}
</section>
```

The compiler will automatically strip away the `<style>`- and/or `<script>`-tags and save the contents of the block to
a file. This file will then be used for inclusion in your template by utilizing either a `link`-tag or a `script` tag
that refer to the compiled file.


## Configuration

The configuration options of this package are pretty large, but all settings are optional and come with sane defaults.

The following configuration options are directly copied from webpack itself and can be configured as such. The only
difference to take into consideration is that keys are written in __underscores rather than camelCase__.

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

The options `entry`, `resolve.root`, `resolve.alias` and `resolveModule.modulesDirectories` are configured automatically
based on split points (entry points), tracked bundles and the specified (or detected) node_modules directory. If you
specify any of these options, their values will be appended to the generated values.

### Node

In order for this package to work properly, it needs to know the location where nodejs is installed and where to find
its node_modules directory. If node is installed globally on your server, this setting may be omitted.

```yaml
# config.yml
webpack:
    node:
        binary: '/path/to/node-binary'
        node_modules_path: '/path/to/node_modules'
```

#### Multi-platform configuration

Since your application might be running on both windows, linux and macs all at the same time, you might need to specify
different node binaries. If this is the case, instead of passing a string referencing the node binary to the
`node.binary` option, you may pass an array:

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

Again, all settings are optional. If a key isn't specified, it defaults to "node". This will only work if node is
installed globally.

### Bundle configuration

By default, all enabled bundles are tracked. However, you may explicitly specify a set of bundles to track for
performance or security reasons.

```yaml
webpack:
    bundles: ['AppBundle', 'YourBundle']
```

#### Adding app resources to the tracked assets

If you decide to add your assets in app/Resources/assets, all you have to do is add an alias and it can be loaded via
the webpack loading mechanism.

```yml
webpack:
    resolve:
        alias:
            app: %kernel.root_dir%/Resources/assets
```
```javascript
# can be loaded via
require('app/base.js');
```

### Shared dependencies

Shared dependencies will be written to a separate javascript or css file if the option `output.common_id` is specified.

For example, the following configuration would output a `shared.js` and `shared.css` file.
```yaml
webpack:
    output:
        common_id: 'shared'
```

In your templates you will be able to retrieve the path to your common javascript file via `webpack_common_js()` and 
`webpack_common_css()`.

```twig
<script src="{{ webpack_common_js() }}"></script>
<link rel="stylesheet" href="{{ webpack_common_css() }}">

{# will give the following output based on output.common_id #}
<script src="/compiled/shared.js"></script>
<link rel="stylesheet" href="/compiled/shared.css">
```

### Asset output directories

- Dumped assets from the "public" directory are symlinked or copied to the `<output.dump_dir>` directory.
- Compiled assets from the "assets" directory are written to the `<output.path>` directory.
- The twig function `webpack_asset` returns compiled file names prefixed with the `<output.public_path>` directory.

```yaml
webpack:
    output:
        path: '%kernel.root_dir%/../web/compiled/'
        dump_path: '%kernel.root_dir%/../web/bundles/'
        public_path: '/compiled/'
```

The `path` value represents the asset paths from a client-side perspective. Therefore, it must specify the path
of your app(_dev).php as exposed from the web e.g. somedomain.com/my-web/app.php would make it
`%kernel.root_dir%/../web/my-app/compiled/` with the above example.

If the `output.path` value is `%kernel.root_dir/../web/packed/`, the value of `output.public_path` must be
set to `/packed/`.

### Ideal configuration

The following configuration requires the following modules to be present in your `node_modules` directory.

 - extract-text-webpack-plugin
 - style-loader
 - css-loader
 - less-loader
 - sass-loader
 - url-loader
 - babel-loader
 - ts-loader

Because we're creating shared chunks of javascript files, you'll need to include '`/compiled/shared.js`' manually in
your base template. The same might also be the case for your CSS files, depending on what you include and where you do
it.

config.yml
```yaml
webpack:
    node:
        binary: '/path/to/node'
        node_modules_path: '%kernel.root_dir%/../node_modules'
    output:
        path: '%kernel.root_dir%/../web/compiled/'
        dump_path: '%kernel.root_dir%/../web/bundles/'
        public_path: '/compiled/'
        common_id: 'shared'
    loaders:
        css:
            all_chunks: true
            filename: '[name].css'
        less:
            all_chunks: true
            filename: '[name].css'
        sass:
            all_chunks: true
            filename: '[name].css'
        url: ~
        babel: ~
        typescript: ~
```

base.html.twig
```html
<head>
    <script src="/compiled/shared.js"></script>
</head>
```

Somewhere in your twig templates
```twig
{% webpack js "@YourBundle/SomeModule.js" %}
    <script src="{{ asset }}"></script>
{% endwebpack %}

{% webpack css "@YourBundle/SomeModule.js" %}
    <link rel="stylesheet" href="{{ asset }}">
{% endwebpack %}
```

### Compile Timeout

By default, webpack is only given 60 seconds to execute.  In cases where webpack needs more time, you can update the
`compile_timeout` option.

```yaml
webpack:
    compile_timeout: 60
```

## Loaders

Loaders allow you to `require` files other than javascript. This package comes with 7 default loaders.

 - `CssLoader`       : include CSS files
 - `UrlLoader`       : include images (converted to base64)
 - `LessLoader`      : include less files.
 - `SassLoader`      : include sass files.
 - `BabelLoader`     : include ES6 files.
 - `TypeScriptLoader`: include TypeScript files.
 - `CoffeeScriptLoader`: include CoffeeScript files.

Each loader has its own configuration under the `loaders` section.

### CSS

Enables loading CSS files.

> You need the `css-loader` and `style-loader` node module for this to work.

```yaml
webpack:
    loaders:
        css:
            filename: '[name].css'
            all_chunks: true
```

If `filename` and `all_chunks` are omitted, any CSS is converted to a style-tag in the document rather than being
exported to a separate CSS file. If the `output.common_id` setting is specified - which allows extracting shared code -
the [CommonsChunkPlugin](http://webpack.github.io/docs/list-of-plugins.html#commonschunkplugin) will be used
automatically as well.

Depending on the specified configuration, one or more node-modules are required:

 - `enabled:true` : style-loader, css-loader
 - `filename` : extract-text-webpack-plugin

### Less

Enables loading less files.

This plugin shares the exact same configuration settings as the CSS loader.

> You need the `less-loader`, `css-loader` and `style-loader` node modules for this to work.

```yaml
webpack:
    loaders:
       less:
           filename: '[name].css'
           all_chunks: true
```

### Sass

Enables loading sass files.

This plugin shares the exact same configuration settings as the CSS loader.

> You need the `sass-loader`, `css-loader` and `style-loader` node modules for this to work.

```yaml
webpack:
    loaders:
       sass:
           filename: '[name].css'
           all_chunks: true
           include_paths:
               - [include dirs for node-sass]
```

### URL

Converts images to base64 code and embeds them in javascript.

This plugin only has an `enabled` setting. It is disabled by default.
```yaml
webpack:
    loaders:
       url: ~
```

### Babel

The Babel Loader transpiles ECMAScript 6 code to ECMAScript 5 code, allowing it to run in older browsers. The loader
compiles `.jsx` files instead of `.js` files, because not all files need to be compiled. Once ES6 hits mainstream, all
you would need to do is gradually rename your jsx files to js files and everything _should_ still work.

> You need the `babel-loader` node module for this to work.

```yaml
webpack:
    loaders:
        babel: ~
```

### TypeScript

The TypeScript Loader transpiles TypeScript 2 code to JavaScript code, allowing it to run in all browsers. The loader
compiles `.ts` files.

> You need the `ts-loader` node module for this to work with the default configuration.

```yaml
webpack:
    loaders:
        typescript: ~
```

You can also configure your own loader:

```yaml
webpack:
    loaders:
        typescript:
            loader: some-other-typescript-loader
```

### CoffeeScript

The CoffeeScript loader transpiles [CoffeeScript](http://coffeescript.org/) to portable (.js),
allowing coffeescript to run in every browser. The loaders loads `.coffee`-files

> You need the [coffee-loader](https://github.com/webpack/coffee-loader)

```yaml
webpack:
    loaders:
        coffee: ~
```

A `loader` field can specify any other CoffeeScript loader, see [TypeScript](#typescript).

## Plugins

The webpack-bundle package comes with an easy way to develop and use plugins. A plugin simply writes pieces of code
to the generated webpack configuration file, and by doing so it _should_ enable more features.

### DefinePlugin

The define plugin allows you to declare global variables throughout the application.
See the [defineplugin documentation](https://github.com/webpack/docs/wiki/list-of-plugins#defineplugin) for more
information.

In the example below, imagine a parameter named "environment" having the value "dev".
```yaml
webpack:
    plugins:
        constants:
            ENVIRONMENT: %environment%
```

Later, somewhere in an asset...
```javascript
// start
if (ENVIRONMENT === 'dev') {
    console.log('Hello World!');
}
// end
```
These variable declarations are parsed by webpack. Once the code is compiled and minified, these variables are left out
completely in the final code.

This means that the code on your development machine would result in something like this:
```javascript
// start
console.log('Hello World');
// end
```

And in production
```javascript
// start
// end
```
Note that the comments are only here for illustrating this example. Compiled and minified code won't contain these.

### ProvidePlugin

Automatically load module and assign it to a global variable like `$` for jquery.
See the [provideplugin documentation](https://github.com/webpack/docs/wiki/list-of-plugins#provideplugin) for more
information.

In the example below, imagine you want to access jquery through "$" or "jQuery". You just need to add this configuration and the plugin will do the rest for you.

```yaml
plugins:
    provides:
        '$': 'jquery'
        'jQuery': 'jquery'
```

Now you can add you javascript in an inline script and webpack will automatically require "jquery" for you.


```javascript
{% webpack inline %}
<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
{% endwebpack %}
```

__Important:__

Don't forget to install jquery via npm
`npm install jquery`.
As all `node_modules` are resolved in `webpack.config.js` it will find it automatically.

### UglifyJS

Run UglifyJS on JS output, creating smaller and more optimized JS files.
See the [uglifyjs documentation](https://github.com/mishoo/UglifyJS2) for more
information.

```yaml
plugins:
    uglifyjs:
        mangle_except: ['$super', '$', 'exports', 'require']  # Variable names to not mangle
        source_map: true             # Generate a source map for tracking errors in compressed files.
        test: '/\.js($|\?)/i'        # RegExp to filter processed files
        minimize: true               # Whether to minimize or not
        
        # Options to set which optimizations UglifyJS will perform.
        compress:
            sequences: true              # Join consecutive statements with the "comma operator"
            properties: true             # Optimize property access: a["foo"] → a.foo
            dead_code: true              # Discard unreachable code
            drop_debugger: true          # Discard "debugger" statements
            unsafe: false                # Perform unsafe optimizations
            conditionals: true           # Optimize ifs and conditional expressions
            comparisons: true            # Optimize comparisons
            evaluate: true               # Evaluate constant expressions
            booleans: true               # Optimize boolean expressions
            loops: true                  # Optimize loops
            unused: true                 # Drop unused variables/functions
            hoist_funs: true             # Hoist function declarations
            hoist_vars: false            # Hoist variable declarations
            if_return: true              # Optimize if-s followed by return/continue
            join_vars: true              # Join var declarations
            cascade: true                # Try to cascade `right` into `left` in sequences
            side_effects: true           # Drop side-effect-free statements
            warnings: false              # Warn about potentially dangerous optimizations/code
```

Now when your JS files are built, they will be compressed and optimized using UglifyJS.
