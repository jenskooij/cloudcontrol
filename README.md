# Cloud Control Framework

Cloud Control is a PHP framework that is intended for rapid development of
websites and webapps. Included in the framework is the Cloud Control CMS, for
easy management of the content and structure of your website. Cloud Control and
Cloud Control CMS are distributed under the MIT [license](LICENSE), so feel free to
use it for whatever project you see fit.

## Installation
The framework uses PHP, npm and grunt, so make sure you have those installed prior to
using the framework. See also the installation instructions for the [frontend](frontend) and [backend](cloudcontrol).

## Using Cloud Control for your project
Cloud Control was designed to accelerate your development track. After cloning this
repo, it is advised to initialize git repositories in both the `/cloudcontrol` and
`/frontend` repositories, so you can maintain your front- and backend separatly.
This is especially useful when you're working in a project team with multiple
developers.

## Getting started
1. Make sure you have PHP installed on your local computer (use WAMP, LAMP or MAMP or
any other tool of your choice).
2. Clone this repository into the www folder
3. Navigate your command line to `/path/to/www/cloudcontrol/frontend/` and run `npm install`
4. Run `grunt`
5. You can now visit the site (which will show "Cloud Control") in the root
6. You can now visit the cms in root `/cms`
    - The default admin user credentials are: admin/admin
    - The default editor user credentials are: editor/editor

## Contributing to this project
If you feel like contributing to the Cloud Control Framework itself, feel free
to do so, but please use the following work-flow:
1. Create an issue
2. Create a branch / fork
3. Checkout this branch fork
4. Make changes
6. Create a pull request
7. Wait for someone to merge the request