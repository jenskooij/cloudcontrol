[![Dependency Status](https://david-dm.org/jenskooij/cloudcontrol.svg?path=frontend)](https://david-dm.org/jenskooij/cloudcontrol?path=frontend)
[![npm version](https://badge.fury.io/js/cloud-control-frontend.svg)](https://badge.fury.io/js/cloud-control-frontend)

# cloud-control-frontend v0.0.5
## Cloud Control Frontend
### Dependencies
See also the [install log](#installLog)
- **node**: ~4.4.0,
- **npm**: ~2.14.20
- **ruby**: ~2.3.0
- **compass**: ~1.0.3
- **sass-globbing**: ~1.1.5
- **grunt**: ~1.0.0
- **grunt-contrib-compass**: ~1.1.1
- **grunt-contrib-jshint**:  ~1.0.0
- **grunt-contrib-concat**:  ~1.0.1
- **grunt-contrib-uglify**:  ~1.0.1
- **grunt-contrib-watch**:   ~1.0.0

### Installation
In the console, type `npm install` in the `frontend` folder to retrieve the
dependencies and prepare the project. Next type `grunt`, which will then
start the compiling and watching of the resource files.

### Usage
The Cloud Control Frontend uses SASS, through Compass.

To start developing your frontend assets, you can create your SASS (.scss) files
in the ``sass/site/`` folder. These will automatically be combined and
compressed into the cloud control framework.

For javascript, you can just start creating .js files in the ``javascripts/site/``
folder. These will automatically be concatinated and uglified
(which is kinda like minified) into the cloud control framework.

**Note:** For instructions on how to use the backend, see [here](../cloudcontrol)

### Install log<a name="installLog"></a>
```
grunt-contrib-concat@1.0.1 node_modules\grunt-contrib-concat
├── source-map@0.5.6
└── chalk@1.1.3 (escape-string-regexp@1.0.5, ansi-styles@2.2.1, supports-color@2.0.0, strip-ansi@3.0.1, has-ansi@2.0.0)

grunt-contrib-compass@1.1.1 node_modules\grunt-contrib-compass
├── dargs@2.1.0
├── onetime@1.1.0
├── async@1.5.2
├── tmp@0.0.28 (os-tmpdir@1.0.1)
├── which@1.2.4 (isexe@1.1.2, is-absolute@0.1.7)
└── bin-version-check@2.1.0 (minimist@1.2.0, semver@4.3.6, semver-truncate@1.1.0, bin-version@1.0.4)

grunt-contrib-watch@1.0.0 node_modules\grunt-contrib-watch
├── async@1.5.2
├── gaze@1.0.0 (globule@0.2.0)
├── tiny-lr@0.2.1 (parseurl@1.3.1, livereload-js@2.2.2, qs@5.1.0, debug@2.2.0, body-parser@1.14.2, faye-websocket@0.10.0)
└── lodash@3.10.1

grunt-contrib-jshint@1.0.0 node_modules\grunt-contrib-jshint
├── hooker@0.2.3
├── chalk@1.1.3 (escape-string-regexp@1.0.5, ansi-styles@2.2.1, supports-color@2.0.0, strip-ansi@3.0.1, has-ansi@2.0.0)
└── jshint@2.9.2 (strip-json-comments@1.0.4, exit@0.1.2, console-browserify@1.1.0, minimatch@2.0.10, shelljs@0.3.0, cli@0.6.6, htmlparser2@3.8.3, lodash@3.7.0)

grunt@1.0.1 node_modules\grunt
├── grunt-known-options@1.1.0
├── path-is-absolute@1.0.0
├── eventemitter2@0.4.14
├── rimraf@2.2.8
├── exit@0.1.2
├── iconv-lite@0.4.13
├── nopt@3.0.6 (abbrev@1.0.7)
├── coffee-script@1.10.0
├── glob@7.0.3 (inherits@2.0.1, inflight@1.0.4, once@1.3.3)
├── minimatch@3.0.0 (brace-expansion@1.1.4)
├── findup-sync@0.3.0 (glob@5.0.15)
├── grunt-cli@1.2.0 (resolve@1.1.7)
├── js-yaml@3.5.5 (esprima@2.7.2, argparse@1.0.7)
├── dateformat@1.0.12 (get-stdin@4.0.1, meow@3.7.0)
├── grunt-legacy-util@1.0.0 (getobject@0.1.0, async@1.5.2, hooker@0.2.3, which@1.2.4, underscore.string@3.2.3, lodash@4.3.0)
└── grunt-legacy-log@1.0.0 (hooker@0.2.3, colors@1.1.2, underscore.string@3.2.3, lodash@3.10.1, grunt-legacy-log-utils@1.0.0)

grunt-contrib-uglify@1.0.1 node_modules\grunt-contrib-uglify
├── uri-path@1.0.0
├── chalk@1.1.3 (escape-string-regexp@1.0.5, supports-color@2.0.0, ansi-styles@2.2.1, has-ansi@2.0.0, strip-ansi@3.0.1)
├── uglify-js@2.6.2 (async@0.2.10, uglify-to-browserify@1.0.2, source-map@0.5.6, yargs@3.10.0)
├── maxmin@1.1.0 (figures@1.5.0, gzip-size@1.0.0, pretty-bytes@1.0.4)
└── lodash@4.11.2
```