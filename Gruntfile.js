module.exports = function (grunt) {

	grunt.initConfig({

		less : {
			prod : {
				options : {
					paths : ['assets/styles'],
					yuicompress : true
				},
				files : {
					'assets/styles/style.css' : 'assets/styles/style.less'
				}
			}
		},

		watch : {
			files : ['assets/styles/**/*.less'],
			tasks : ['less:prod']
		}

	});

	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');

};