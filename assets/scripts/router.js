define([
	'app',
	'backbone',
	'collections/wp-collection'
], function (app, backbone, wpCollection) {

	return Backbone.Router.extend({

		routes : {
			'*path' : 'delegateRequest'
		},

		$body : $(document.body),

		collection : new wpCollection({reset : true}),

		delegateRequest : function delegateRequest(_path) {

			window.scrollTo(0, 1);

			this
				.$body
				.removeClass('content-ready')
				.addClass('request-pending');

			this.collection.url = _path || '/';
			this.collection.fetch({
				success : $.proxy(this.applyBodyClass, this)
			});

		},

		applyBodyClass : function () {

			this
				.$body
				.removeClass('request-pending')
				.addClass('content-inserted');

			_.delay(function (that) {
				that
					.$body
					.addClass('content-ready');
			}, 100, this);

			_.delay(function (that) {
				that
					.$body
					.removeClass('content-inserted');
			}, 900, this);

		}

	});

});