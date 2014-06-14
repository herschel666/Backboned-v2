define([
	'backbone',
	'mustache'
], function (Backbone, Mustache) {

	/*
	 * View for the main-aside.
	**/
	return Backbone.View.extend({

		tmpl : $('#aside-tmpl').html(),

		tagName : 'aside',

		initialize : function initialize() {
			this.render();
		},

		render : function render() {
			return this.$el.html(Mustache.render(this.tmpl, BB.aside));
		}

	});

});