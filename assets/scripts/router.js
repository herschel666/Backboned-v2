define([
	'app',
	'backbone',
	'collections/wp-collection',
	'views/body-view'
], function (app, backbone, wpCollection, BodyView) {

	var bodyView = new BodyView({
		el: document.body
	});

	/*
	 * Main-Router - takes the URI from the pushState-Event
	 * and passes it to the main-collection.
	 *
	 * So actually there's no noteworthy routing
	 * happening here … o.O
	**/
	return Backbone.Router.extend({

		/*
		 * One route to rule them all! \m/
		**/
		routes : {
			'*path' : 'delegateRequest'
		},

		/*
		 * Instatiating the one and only main-collection.
		**/
		collection : new wpCollection({
			reset : true
		}),

		/*
		 * Scrolling to top, emitting the event for handling
		 * the page-transition-CSS-classes and rearming the
		 * main-collection.
		**/
		delegateRequest : function delegateRequest(_path) {

			var that = this;

			window.scrollTo(0, 1);

			bodyView.trigger('UI.mainViewPending');

			this.collection.url = _path || '/';
			this.collection.fetch({
				success : $.proxy(this.applyBodyClass, this),
				error: function () {
					that.collection.assignViews.apply(that.collection, arguments);
					that.applyBodyClass.call(that);
				}
			});

		},

		/*
		 * Secend event-emitting to signalize that the
		 * loading-process is finished.
		**/
		applyBodyClass : function () {
			bodyView.trigger('UI.mainViewReady');
		}

	});

});