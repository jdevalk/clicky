/* global require, process */
module.exports = function(grunt) {
	'use strict';

	require('time-grunt')(grunt);

	// Define project configuration
	var project = {
		paths: {
			get config() {
				return this.grunt + 'config/';
			},
			css: 'css/dist/',
			sass: 'css/src/',
			grunt: 'grunt/',
			images: 'images/',
			js: 'js/',
			languages: 'languages/',
			logs: 'logs/',
			vendor: 'vendor/'
		},
		files: {
			css: [
				'css/dist/*.css',
				'!css/dist/*.min.css'
			],
			images: [
				'images/*'
			],
			js: [
				'js/*.js',
				'!js/*.min.js'
			],
			php: [
				'*.php',
				'admin/**/*.php',
				'frontend/**/*.php',
				'includes/**/*.php'
			],
			phptests: 'tests/**/*.php',
			get config() {
				return project.paths.config + '*.js';
			},
			get changelog() {
				return project.paths.theme + 'changelog.txt';
			},
			grunt: 'Gruntfile.js',
			artifact: "artifact",
			artifactComposer: "artifact-composer"
		},
		pkg: grunt.file.readJSON( 'package.json' )
	};

	// Load Grunt configurations and tasks
	require( 'load-grunt-config' )(grunt, {
		configPath: require( 'path' ).join( process.cwd(), project.paths.config ),
		data: project,
		jitGrunt: {
			staticMappings: {
				addtextdomain: 'grunt-wp-i18n',
				makepot: 'grunt-wp-i18n',
				glotpress_download: 'grunt-glotpress',
				wpcss: 'grunt-wp-css'
			}
		}
	});
};
