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
	css: {
		files: [
			'<%= paths.sass %>'
		],
		tasks: [
			'build:css'
		]
	}
};
