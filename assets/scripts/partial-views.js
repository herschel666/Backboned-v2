define([
	'views/partials/header-view',
	'views/partials/navigation-view',
	'views/partials/aside-view',
	'views/partials/footer-view',
	'views/partials/page-links-view',
	'views/partials/comments-view',
	'views/partials/comment-form-view'
], function (
	HeaderView,
	NavigationView,
	AsideView,
	FooterView,
	PageLinksView,
	CommentsView,
	CommentFormView
) {

	/*
	 * Getting one handy object with ell the partials.
	**/
	return {
		Header: HeaderView,
		Navigation: NavigationView,
		Aside: AsideView,
		Footer: FooterView,
		PageLinks: PageLinksView,
		Comments: CommentsView,
		CommentForm: CommentFormView
	};

});