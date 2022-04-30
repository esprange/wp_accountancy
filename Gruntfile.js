// phpcs:disable
// noinspection JSUnresolvedFunction,JSUnusedGlobalSymbols

/**
 * Grunt build file voor Accountancy.
 *
 * @package wp-accountancy
 */

module.exports = function( grunt ) {
	'use strict';

	// Project configuration.
	grunt.initConfig(
		{
			wp_readme_to_markdown: {
				your_target: {
					files: {
						'README.md': 'readme.txt'
					}
				}
			},
			uglify: {
				dev: {
					options: {
						mangle: {
							reserved: ['jQuery']
						}
					},
					files: [{
						expand: true,
						src: [ '*.js', '!*.min.js' ],
						dest: 'public/js',
						cwd: 'public/js',
						rename: function( dst, src ) {
							return dst + '/' + src.replace( '.js', '.min.js' );
						}
					}]
				}
			},
			cssmin: {
				target: {
					files: [{
						expand: true,
						cwd: 'public/css',
						src: [ '*.css', '!*.min.css' ],
						dest: 'public/css',
						ext: '.min.css'
					}]
				}
			},
			zip: {
				'using-router': {
					router: function( filepath ) {
						return 'wpacc/' + filepath;
					},
					src: [
						'wp-accountancy.php',
						'README.txt',
						'LICENSE.txt',
						'public/**/*',
						'admin/**/*',
						'includes/**/*',
						'vendor/**/*'
					],
					dest: 'zip/wp-accountancy.zip'
				}
			},
			shell: {
				command: 'ftp -i -s:plugin_upload.ftp'
			}
		}
	);

	grunt.util.linefeed = '\n';
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-composer' );
	grunt.loadNpmTasks( 'grunt-zip' );
	grunt.loadNpmTasks( 'grunt-shell' );
	grunt.registerTask(
		'versie_check',
		'Versie mutatie in readme en plugin',
		function (){
			const pkg  = grunt.file.readJSON( 'package.json' );
			let readme = grunt.file.read( 'readme.txt' );
			let plugin = grunt.file.read( pkg.name + '.php' );
			grunt.file.write( 'README.txt', readme.replace( /Stable tag:.*\s/gm, 'Stable tag: ' + pkg.version + "\n" ) );
			grunt.file.write( 'wp-accountancy.php', plugin.replace( /Version:.*\s/gm, 'Version:           ' + pkg.version + "\n" ) );
		}
	);
	grunt.registerTask(
		'oplevering',
		[
			'versie_check',
			'wp_readme_to_markdown',
			'uglify',
			'cssmin',
			'composer:update:no-autoloader:no-dev:verbose',
			'composer:dump-autoload:optimize',
			'zip',
			'shell:command',
			'composer:update'
		]
	);
};
