define([
	'backbone',
	'mustache'
], function (Backbone, Mustache) {

	return Backbone.View.extend({

		tmpl : $('#header-tmpl').html(),

		initialize : function initialize() {

			this.render();

		},

		render : function render() {

			return this.$el.html(Mustache.render(this.tmpl, BB.site_header));

		}

	});

});