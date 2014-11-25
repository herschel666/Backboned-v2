define(['app'], function (app) {

	/*
	 * Handles the adding and removing of the
	 * CSS-classes that provide the nice page-
	 * transitions after each loading of data.
	**/
	return Backbone.View.extend({

		initialize: function initialize() {
			this.on('UI.mainViewPending', this.setPending, this);
			this.on('UI.mainViewReady', this.setReady, this);
		},

		/*
		 * Waiting for data.
		**/
		setPending: function setPending() {

			this
				.$el
				.removeClass('content-ready')
				.addClass('request-pending');

		},

		/*
		 * Handling the arrival of new data.
		**/
		setReady: function setReady() {

			this
				.$el
				.addClass('content-inserted');

			_.delay(function (that) {
				that
					.$el
					.addClass('content-ready');
			}, 100, this);

			_.delay(function (that) {
				that
					.$el
 					.removeClass('request-pending')
					.removeClass('content-inserted');
			}, 900, this);

		}

	});

});