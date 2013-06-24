require([
	'router'
], function (Router, Views) {

	/*
	 * Regular expression for identifying internal URIs.
	**/
	var rBase = new RegExp('^' + BB.base_url);

	/*
	 * Actual app instance.
	**/
	var app = new Router();

	/*
	 * Starting the Backbone-history with pushState-handling.
	**/
	Backbone.history.start({
		pushState : true,
		hashChange : false
	});

	/*
	 * Binding all anchor-click-events.
	 *
	 * If it's an external URL or the browser doensn't support the
	 * HTML5-pushState-API, the function is aborted.
	 * Otherwise the router-instance is triggered with the URI.
	**/
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