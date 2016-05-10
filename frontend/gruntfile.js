/*
	Inspired by this article: http://mattwatson.codes/compile-scss-javascript-grunt/
*/
module.exports = function (grunt) {
	"use strict";
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            options: {
                separator: '\r\n'
            },
            site: {
                src: ['javascripts/site/*.js'],
                dest: 'build.site.js'
            },
            cms: {
                src: ['javascripts/cms/*.js'],
				dest: 'build.cms.js'
            }
        },
		
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			dist: {
				files: {
					'../cloudcontrol/www/js/cms.js': ['<%= concat.cms.dest %>'],
					'../cloudcontrol/www/js/site.js': ['<%= concat.site.dest %>']
				}
			}
		},
		jshint: {
            files: [
                'gruntfile.js',
                'javascripts/cms/*.js',
                '!javascripts/cms/*.min.js',
                'javascripts/site/*.js',
                '!javascripts/site/*.min.js'
            ],
            options: {
                globals: {
                    jQuery: true,
                    console: true,
                    module: true
                }
            }
        },
		compass: {
            dist: {
                options: {
                    sassDir: 'sass',
                    cssDir: '../cloudcontrol/www/css',
                    environment: 'development',
                    outputStyle: 'compressed'
                }
            }
        },
		watch: {
            styles: {
                files: ['sass/**/*.*', 'sass/*.*'],
                tasks: ['css']
            },
            scripts: {
                files: ['<%= jshint.files %>'],
                tasks: ['js']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.registerTask('default', ['concat', 'uglify', 'jshint', 'compass', 'watch']);
	grunt.registerTask('js', ['concat', 'uglify', 'jshint']);
	grunt.registerTask('css', ['compass']);
	grunt.registerTask('build', ['concat', 'uglify', 'jshint', 'compass']);
};