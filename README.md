# Cloud Control
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/jenskooij/cloudcontrol/badges/build.png?b=master)](https://scrutinizer-ci.com/g/jenskooij/cloudcontrol/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jenskooij/cloudcontrol/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jenskooij/cloudcontrol/?branch=master)
## Cloud Control - Framework & CMS

Cloud Control is a PHP framework that is intended for rapid development of
websites and webapps. Included in the framework is the Cloud Control CMS, for
easy management of the content and structure of your website. Cloud Control and
Cloud Control CMS are distributed under the MIT [license](LICENSE), so feel free to
use it for whatever project you see fit.

## Usage
It is highly recommended to use the skeleton project to create a new project using Cloud Control.
You can do this by using composer to create the project for you. It's as simple as typing:
```
composer create-project getcloudcontrol/skeleton my-project
```

This will create a folder called "my-project" containing nothing but the bare minimum to start building with Cloud Control. To initiate Cloud Control run `composer update` in this folder. Alternatively you can combine the commands like this:
```
composer create-project getcloudcontrol/skeleton my-project && cd my-project && composer update
```

## Prerequisites
The framework uses composer and PHP, so make sure you have those installed prior to
using the framework.

## Contributing to this project
If you feel like contributing to the Cloud Control Framework itself, feel free
to do so, but please follow our [contributing guide](CONTRIBUTING.md).
