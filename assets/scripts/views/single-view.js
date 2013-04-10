define([
	'app',
	'mustache'
], function (app, Mustache) {

	return Backbone.View.extend({

		tmpl : $('#single-tmpl').html(),

		initialize : function initialize() {

			this.render();

		},

		render : function render() {

			this.$el.html(Mustache.render(this.tmpl, this.options.content));

			return this;

		}

	});

});