module.exports = function (grunt) {

	grunt.initConfig({

		pkg: grunt.file.readJSON('bower.json'),

		sass : {
			prod : {
				options : {
					precision: 6,
					style: 'compressed',
					banner: [
						'/**',
						' * Theme Name: Backboned v2',
						' * Theme URI: http://www.emanuel-kluge.de/html-css/ajax-wordpress-theme-backbone-js/',
						' * Description: Backbone.js-powered WP-Theme - <strong>Please activate &quot;Default&quot;-permalink-structure under &quot;Settings =&gt; Permalinks&quot; for proper functioning</strong>',
						' * Version: <%= pkg.version %>',
						' * Author: Emanuel Kluge',
						' * Author URI: http://www.emanuel-kluge.de/',
						' * Tags: grey, fixed width, two columns, ajax',
						' * ',
						' * Backboned v2',
						' *',
						' * The CSS, XHTML and design is released under MIT',
						' */',
						''
					].join('\n')
				},
				files : {
					'style.css' : 'assets/styles/style.scss'
				}
			}
		},

		watch : {
			files : ['assets/styles/**/*.scss'],
			tasks : ['sass:prod']
		}

	});

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');

};