define(['backbone'], function (Backbone) {

	return Backbone.View.extend({

		hideContent : function hideContent() {

			this
				.$el
				.removeClass('content-ready')
				.addClass('request-pending');

		},

		showContent : function showContent() {

			var that = this;

			this
				.$el
				.removeClass('request-pending')
				.addClass('content-inserted');

			setTimeout(function () {

				that
					.$el
					.addClass('content-ready');

			}, 100);

			setTimeout(function () {

				that
					.$el
					.removeClass('content-inserted');

			}, 900);

		}

	});

});