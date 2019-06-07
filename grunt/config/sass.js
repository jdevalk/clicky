// https://github.com/sindresorhus/grunt-sass
module.exports = {
	build: {
		options: {
			sourceMap: true
		},
		files: {
			"<%= paths.css %>adminbar.css": "<%= paths.sass %>adminbar.scss",
			"<%= paths.css %>clicky_admin.css": "<%= paths.sass %>clicky_admin.scss"
		},
	},
	"build-release": {
		files: {
			"<%= paths.css %>adminbar.css": "<%= paths.sass %>adminbar.scss",
			"<%= paths.css %>clicky_admin.css": "<%= paths.sass %>clicky_admin.scss"
		},
	},
};
