define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	/*
	 * View for the main-navigation.
	 * Handles navigation-highlighting.
	**/
	return Backbone.View.extend({

		tmpl : $('#navigation-tmpl').html(),

		tagName : 'nav',

		events : {
			'click a' : 'toggleCurrentClass'
		},

		/*
		 * Rendering and subscribing to the event emitted
		 * after each page-change by the wp-collection.
		**/
		initialize : function initialize() {

			app.on('UI.handleCurrentClass', this.handleCurrentClass, this);
			this.render();

		},

		/*
		 * The actual rendering.
		**/
		render : function render() {

			return this.$el.html(Mustache.render(this.tmpl, {
				'nav-items' : BB.main_nav
			}));

		},

		/*
		 * Toggling the navigation-highlighting.
		**/
		toggleCurrentClass : function toggleCurrentClass(evnt) {

			$(evnt.currentTarget.parentNode)
				.addClass('current')
				.siblings()
				.removeClass('current');

		},

		/*
		 * Removing the navigation-highlighting, if the current page
		 * is not a static wordpress-page.
		**/
		handleCurrentClass : function handleCurrentClass(type) {

			if ( type === 'page') {
				return;
			}

			this
				.$('li')
				.removeClass('current');

		}

	});

});