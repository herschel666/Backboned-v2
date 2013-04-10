define([
	'app',
	'mustache'
], function (app, Mustache) {

	return Backbone.View.extend({

		loopTmpl : $('#error-tmpl').html(),

		initialize : function initialize() {

			this.render();

		},

		render : function render() {

			this.$el.html(Mustache.render(this.loopTmpl, {
				posts : this.options.content
			}));

			return this;

		}

	});

});