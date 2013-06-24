define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache, ContentView) {

	/*
	 * View for the comments list.
	**/
	return Backbone.View.extend({

		tmpl : $('#comments-tmpl').html(),

		initialize : function initialize() {

			this.render();
			app.on('UI.removeComments', this.removeComments, this);
			app.on('UI.newCommentAdded', this.addComment, this);

		},

		/*
		 * Rendering and appending the comments list.
		**/
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

		/*
		 * Removing the view.
		**/
		removeComments : function removeComments() {

			this
				.$el
				.hide();

		},

		/*
		 * Adding a single comment to the content object.
		**/
		addComment : function addComment(comments) {

			this.options.content = comments;
			this.render();

		}

	});

});