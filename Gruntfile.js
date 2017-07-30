/* jshint node:true */
/* global module */
module.exports = function( grunt ) {
	require( 'matchdep' ).filterDev( ['grunt-*', '!grunt-legacy-util'] ).forEach( grunt.loadNpmTasks );
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			all: ['Gruntfile.js', 'js/*.js']
		},
		checktextdomain: {
			options: {
				correct_domain: false,
				text_domain: ['wp-idea-stream', 'default'],
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: ['**/*.php', '!**/node_modules/**'],
				expand: true
			}
		},
		clean: {
			all: [ 'templates/*.min.css', 'js/*.min.js' ]
		},
		makepot: {
			target: {
				options: {
					domainPath: 'languages',
					exclude: ['/node_modules'],
					mainFile: 'wp-idea-stream.php',
					potFilename: 'wp-idea-stream.pot',
					processPot: function( pot ) {
						pot.headers['last-translator']      = 'imath <imath@cluster.press>';
						pot.headers['language-team']        = 'FRENCH <imath@cluster.press>';
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/imath/wp-idea-stream/issues';
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		uglify: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.js',
				src: ['js/*.js', '!*.min.js']
			},
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
				'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
				'https://github.com/imath/wp-idea-stream */\n'
			}
		},
		cssmin: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.css',
				src: ['templates/embed-style.css', '!*.min.css'],
				options: {
					banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
					'https://github.com/imath/wp-idea-stream */'
				}
			}
		},
		jsvalidate:{
			src: ['js/*.js'],
			options:{
				globals: {},
				esprimaOptions:{},
				verbose: false
			}
		},
		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
			'multisite': {
				cmd: 'phpunit',
				args: ['-c', 'tests/phpunit/multisite.xml']
			}
		},
		'git-archive': {
			archive: {
				options: {
					'format'  : 'zip',
					'output'  : '<%= pkg.name %>.zip',
					'tree-ish': 'HEAD@{0}'
				}
			}
		}
	} );

	/**
	 * Register tasks.
	 */
	grunt.registerMultiTask( 'phpunit', 'Runs PHPUnit tests, including the multisite tests.', function() {
		grunt.util.spawn( {
			args: this.data.args,
			cmd:  this.data.cmd,
			opts: { stdio: 'inherit' }
		}, this.async() );
	} );

	grunt.registerTask( 'jstest', ['jsvalidate', 'jshint'] );

	grunt.registerTask( 'shrink', ['clean', 'cssmin', 'uglify'] );

	grunt.registerTask( 'zip', ['git-archive'] );

	grunt.registerTask( 'commit',  ['checktextdomain', 'jstest', 'phpunit'] );

	grunt.registerTask( 'release', ['checktextdomain', 'makepot', 'jstest', 'clean', 'cssmin', 'uglify', 'zip'] );

	// Default task.
	grunt.registerTask( 'default', ['commit'] );
};
