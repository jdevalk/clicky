// https://github.com/sindresorhus/grunt-sass
/* global developmentBuild */
module.exports = {
	build: {
		options: {
			sourceMap: developmentBuild
		},
		files: {
			"<%= paths.css %>adminbar.css": "<%= paths.sass %>adminbar.scss",
			"<%= paths.css %>clicky_admin.css": "<%= paths.sass %>clicky_admin.scss"
		},
	}
};
