// https://github.com/SaschaGalley/grunt-phpcs
module.exports = {
	options: {
		ignoreExitCode: true
	},
	plugin: {
		options: {
			bin: '<%= paths.vendor %>squizlabs/php_codesniffer/bin/phpcs',
			standard: 'phpcs.xml',
			extensions: 'php'
		},
		dir: [
			'<%= files.php %>'
		]
	}
};
