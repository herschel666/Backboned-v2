require([
	'router'
], function (Router, Views) {

	var rBase = new RegExp('^' + BB.base_url);

	var app = new Router();

	Backbone.history.start({
		pushState : true,
		hashChange : false
	});

	$(document.body).on('click', 'a', function (evnt) {

		if ( !('pushState' in window.history) || /^\/(wp-login|wp-admin)/.test(this.pathname) ) {
			return true;
		}

		evnt.preventDefault();

		rBase.test(this.href) && app.navigate(this.pathname, {
			trigger : true
		});

	});

});