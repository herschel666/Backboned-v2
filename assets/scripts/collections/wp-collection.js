define([
	'app',
	'backbone',
	'views'
], function (app, Backbone, Views) {

	return Backbone.Collection.extend({

		initialize : function initialize() {

			this.insertFrameViews();
			this.on('add', this.assignViews, this);

		},

		assignViews : function assignView() {

			var data = this.at(0),
				type = data.get('type'),
				pagelinks = data.get('pagelinks'),
				comments = data.get('comments'),
				commentform = data.get('commentform');

			new Views[type]({
				el : '#posts',
				content : data.get('content')
			});

			if ( type !== 'page') {
				app.trigger('UI.removeCurrentClass');
			}

			if ( pagelinks ) {
				new Views.partials.PageLinks({
					el : '#page-links',
					content : pagelinks
				});
			} else {
				app.trigger('UI.removePageLinks');
			}

			if ( comments && comments.length ) {
				new Views.partials.Comments({
					el : '#comments-list',
					content : comments
				});
			} else {
				app.trigger('UI.removeComments');
			}

			if ( commentform ) {
				new Views.partials.CommentForm({
					el : '#comment-form',
					content : commentform
				});
			} else {
				app.trigger('UI.removeCommentForm');
			}

		},

		insertFrameViews : function insertFrameViews() {

			new Views.partials.Header({
				el : '#main-header'
			});
			new Views.partials.Navigation({
				el : '#main-nav'
			});
			new Views.partials.Aside({
				el : '#main-aside'
			});
			new Views.partials.Footer({
				el : '#main-footer'
			});

		}

	});

});