define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	/*
	 * Viw for the pagination-links.
	**/
	return Backbone.View.extend({

		tmpl : $('#page-links-tmpl').html(),

		initialize : function initialize(opts) {

			this.opts = opts || {};
			this.render();
			app.on('UI.removePageLinks', this.removePageLinks, this);

		},

		render : function render() {

			return this
				.$el
				.html(Mustache.render(this.tmpl, this.opts.content))
				.show();

		},

		removePageLinks : function removePageLinks() {

			this
				.$el
				.hide()
				.empty();

		}

	});

});