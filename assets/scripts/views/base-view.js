define(['backbone', 'mustache'], function (Backbone, Mustache) {

	/*
	 * Base-view for rendering the main-content (loop,
	 * single, page and error).
	**/
	return Backbone.View.extend({

		initialize: function (opts) {
			this.opts = opts || {};
		},

		render : function render() {

			this.$el.html(Mustache.render(this.opts.tmpl, this.opts.content));

			return this;

		}

	});

});