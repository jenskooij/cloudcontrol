# cloud-control-frontend
## Cloud Control Frontend
### Dependencies
- **node**: ~4.4.0,
- **npm**: ~2.14.20
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