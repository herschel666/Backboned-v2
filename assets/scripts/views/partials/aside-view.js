define([
	'backbone',
	'mustache'
], function (Backbone, Mustache) {

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