define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	return Backbone.View.extend({

		tmpl : $('#page-links-tmpl').html(),

		initialize : function initialize() {

			this.render();
			app.on('UI.removePageLinks', this.removePageLinks, this);

		},

		render : function render() {

			return this
				.$el
				.html(Mustache.render(this.tmpl, this.options.content))
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