define([
	'app',
	'mustache'
], function (app, Mustache) {

	return Backbone.View.extend({

		postTmpl : $('#page-tmpl').html(),

		initialize : function initialize() {

			this.render();

		},

		render : function render() {

			this.$el.html(Mustache.render(this.postTmpl, this.options.content));

			return this;

		}

	});

});