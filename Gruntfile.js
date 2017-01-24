module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-jscs' );

	grunt.initConfig( {
		jscs: {
			all: '.'
		}
	} );

	grunt.registerTask( 'test', [ 'jscs' ] );
};
