require.config({

	deps : ['main'],

	baseUrl : BB.template_url + '/assets/scripts',

	paths : {
		'jquery' : '../vendor/jquery/dist/jquery',
		'underscore' : '../vendor/underscore/underscore',
		'backbone' : '../vendor/backbone/backbone',
		'mustache' : '../vendor/mustache/mustache'
	},

	urlArgs : (function () {
		return BB.dev_mode
			? 'bust=' + (+new Date) // Cache-Busting
			: 'foo=bar'; // statisch fuer funktionierenden Cache
	})(),

	shim : {
		'backbone' : {
			deps : ['jquery', 'underscore'],
			exports : 'Backbone'
		},
		'underscore' : {
			exports : '_'
		},
		'mustache' : {
			exports : 'Mustache'
		}

	}

});