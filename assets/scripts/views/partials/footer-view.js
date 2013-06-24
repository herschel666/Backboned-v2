define([
	'backbone',
	'mustache'
], function (Backbone, Mustache) {

	/*
	 * View for the footer.
	**/
	return Backbone.View.extend({

		tmpl : $('#footer-tmpl').html(),

		tagName : 'footer',

		initialize : function initialize() {

			this.render();

		},

		render : function render() {

			return this.$el.html(Mustache.render(this.tmpl, BB.footer));

		}

	});

});