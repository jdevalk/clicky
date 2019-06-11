// https://github.com/gruntjs/grunt-contrib-watch
module.exports = {
	options: {
		livereload: true
	},
	php: {
		files: [
			'<%= files.php %>'
		],
		tasks: [
			'phplint',
			'phpcs'
		]
	},
	js: {
		files: [
			'<%= files.js %>'
		],
		tasks: [
			'build:js',
			'jshint:plugin',
			'jsvalidate:plugin',
			'jscs:plugin'
		]
	},
	css: {
		files: [
			'<%= paths.sass %>'
		],
		tasks: [
			'build:css'
		]
	}
};
