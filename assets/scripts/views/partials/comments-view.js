define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache, ContentView) {

	return Backbone.View.extend({

		tmpl : $('#comments-tmpl').html(),

		initialize : function initialize() {

			this.render();
			app.on('UI.removeComments', this.removeComments, this);
			app.on('UI.newCommentAdded', this.addComment, this);

		},

		render : function render() {

			var that = this;

			this
				.$el
				.show()
				.html(Mustache.render(this.tmpl, {
					comments : this.options.content
				}));

			return this;

		},

		removeComments : function removeComments() {

			this
				.$el
				.hide();

		},

		addComment : function addComment(comments) {

			this.options.content = comments;
			this.render();

		}

	});

});