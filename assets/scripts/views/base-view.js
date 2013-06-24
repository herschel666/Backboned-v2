define(['backbone', 'mustache'], function (Backbone, Mustache) {

	/*
	 * Base-view for rendering the main-content (loop,
	 * single, page and error).
	**/
	return Backbone.View.extend({

		render : function render() {

			this.$el.html(Mustache.render(this.options.tmpl, this.options.content));

			return this;

		}

	});

});