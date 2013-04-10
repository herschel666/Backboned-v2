require.config({

	deps : ['main'],

	baseUrl : BB.template_url + '/assets/scripts',

	paths : {
		'jquery' : 'vendor/jquery-1.9.1.min',
		'underscore' : 'vendor/underscore-1.4.4.min',
		'backbone' : 'vendor/backbone-1.0.0.min',
		'mustache' : 'vendor/mustache-0.7.2.min'
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