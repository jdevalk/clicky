// See https://github.com/gruntjs/grunt-contrib-copy
module.exports = {
	artifact: {
		files: [
			{
				expand: true,
				cwd: ".",
				src: [
					"admin/**",
					"css/dist/*.css",
					"frontend/**",
					"images/**",
					"includes/**",
					"js/*.min.js",
					"languages/**",
					"vendor/**",
					"index.php",
					"readme.txt",
					"clicky.php",
					"!vendor/bin/**",
					"!vendor/composer/installed.json",
					"!vendor/composer/installers/**",
					"!vendor/yoast/i18n-module/LICENSE",
					"!**/composer.json",
					"!**/README.md",
				],
				dest: "<%= files.artifact %>",
			},
		],
	},
	"composer-artifact": {
		files: [ {
			expand: true,
			cwd: "<%= files.artifact %>",
			src: [
				"**/*",
				"!vendor_prefixed/**",
			],
			dest: "<%= files.artifactComposer %>",
		} ],
	},
	"composer-files": {
		files: [ {
			expand: true,
			cwd: ".",
			src: [
				"composer.lock",
				"composer.json",
			],
			dest: "<%= files.artifactComposer %>",
		} ],
		"composer.lock": [ "<%= files.artifact %>/composer.lock" ],
		"composer.json": [ "<%= files.artifact %>/composer.json" ],
	},
	"css-files": {
		files: [
			{
				expand: true,
				cwd: "css/src",
				// TO DO: remove the exclude when ready
				src: ["**/**.css"],
				flatten: false,
				dest: "css/dist/",
			},
		],
	}
};
