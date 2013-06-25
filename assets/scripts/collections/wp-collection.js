define([
	'app',
	'backbone',
	'views/base-view',
	'partial-views'
], function (app, Backbone, BaseView, Partials) {

	/*
	 * The main-collection for fetching all the wordpress
	 * content and assigning it to the corresonding view.
	**/
	return Backbone.Collection.extend({

		/*
		 * Object holding infos about the frame-views.
		**/
		frameViews: {
			Header: '#main-header',
			Navigation: '#main-nav',
			Aside: '#main-aside',
			Footer: '#main-footer'
		},

		/*
		 * Object holding the template-selectors
		 * for each main-content-type (loop,
		 * single, page and error).
		**/
		mainTemplates: {
			loop: $('#loop-tmpl').html(),
			error: $('#error-tmpl').html(),
			page: $('#page-tmpl').html(),
			single: $('#single-tmpl').html()
		},

		/*
		 * Inserting the frame-views like footer, header, etc. and
		 * binding the assignViews-method to the add-event of
		 * the collection.
		**/
		initialize : function initialize() {

			this.insertFrameViews();
			this.on('add', this.assignViews, this);

		},

		/*
		 * Calling all the methods to handle this big chunk
		 * of loaded data from the wordpress-system.
		**/
		assignViews : function assignView(model, obj, resp) {

			var data = resp.xhr.status === 404
				? this._handleErrorContent(resp.xhr.responseText)
				: this.at(0);

			var type = resp.xhr.status === 404
				? 'error'
				: data.get('type');

			this
				.applyMainView(data, type)
				.removeCurrentClass(type)
				.handlePageLinks(data)
				.handleComments(data)
				.handleCommentForm(data);

		},

		/*
		 * Inserting the frame-views.
		**/
		insertFrameViews : function insertFrameViews() {

			for ( frameView in this.frameViews ) {
				new Partials[frameView]({
					el : this.frameViews[frameView]
				});
			}

		},

		/*
		 * Inserting the main-content: a or several blogposts
		 * or a static wordpress-page.
		**/
		applyMainView: function applyMainView(data, type) {

			(new BaseView({
				el : '#posts',
				tmpl: this.mainTemplates[type],
				content : /^(loop)$/.test(type)
					? {posts: data.get('content')}
					: data.get('content')
			})).render();

			return this;

		},

		/*
		 * Emitting event for handling the navigation-highlighting.
		**/
		removeCurrentClass: function removeCurrentClass(type) {

			app.trigger('UI.handleCurrentClass', type);

			return this;

		},

		/*
		 * Checking if a pagination is needed. Inserting the corresponding
		 view or emitting event for removing the current pagination-view.
		**/
		handlePageLinks: function handlePageLinks(data) {

			var pagelinks = data.get('pagelinks');

			if ( !pagelinks ) {
				app.trigger('UI.removePageLinks');
				return this;
			}

			new Partials.PageLinks({
				el : '#page-links',
				content : pagelinks
			});

			return this;

		},

		/*
		 * Checking if the loaded contains comments. Insert comment-view
		 * or remove currently existing comment-view if the data
		 * contains no comment-information.
		**/
		handleComments: function handleComments(data) {

			var comments = data.get('comments');

			if ( !comments || !comments.length ) {
				app.trigger('UI.removeComments');
				return this;
			}

			new Partials.Comments({
				el : '#comments-list',
				content : comments
			});

			return this;

		},

		/*
		 * Inserting commentform-view, if it's needed. Otherwise removing the
		 * currently existing commentform.
		**/
		handleCommentForm: function handleCommentForm(data) {

			var commentform = data.get('commentform');

			if ( !commentform ) {
				app.trigger('UI.removeCommentForm');
				return this;
			}

			new Partials.CommentForm({
				el : '#comment-form',
				content : commentform
			});

			return this;

		},

		/*
		 * Parsing the 404 error message and return it wrapped into
		 * a getter-function, so it doesn't break the flow.
		**/
		_handleErrorContent: function _handleErrorContent(resp) {
			return {
				get: function get(type) {
					return /^(content)$/.test(type) && $.parseJSON(resp).content;
				}
			};
		}

	});

});