// https://github.com/nDmitry/grunt-postcss
module.exports = {
	options: {
		map: true,
		processors: [
			require( "autoprefixer" )( { browsers: "last 2 versions, IE >= 9" } ),
			require( "cssnano" )(),
		],
	},
	build: {
		src: "<%= files.css %>",
	},
};
