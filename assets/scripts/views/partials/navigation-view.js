define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	return Backbone.View.extend({

		tmpl : $('#navigation-tmpl').html(),

		tagName : 'nav',

		events : {
			'click a' : 'toggleCurrentClass'
		},

		initialize : function initialize() {

			app.on('UI.removeCurrentClass', this.removeCurrentClass, this);
			this.render();

		},

		render : function render() {

			return this.$el.html(Mustache.render(this.tmpl, {
				'nav-items' : BB.main_nav
			}));

		},

		toggleCurrentClass : function toggleCurrentClass(evnt) {

			$(evnt.currentTarget.parentNode)
				.addClass('current')
				.siblings()
				.removeClass('current');

		},

		removeCurrentClass : function removeCurrentClass() {

			this
				.$('li')
				.removeClass('current');

		}

	});

});