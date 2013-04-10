define([
	'views/page-view',
	'views/single-view',
	'views/loop-view',
	'views/error-view',
	'views/partials/header-view',
	'views/partials/navigation-view',
	'views/partials/aside-view',
	'views/partials/footer-view',
	'views/partials/page-links-view',
	'views/partials/comments-view',
	'views/partials/comment-form-view'
], function (
	PageView,
	SingleView,
	LoopView,
	ErrorView,
	HeaderView,
	NavigationView,
	AsideView,
	FooterView,
	PageLinksView,
	CommentsView,
	CommentFormView
) {

	return {
		page : PageView,
		single : SingleView,
		loop : LoopView,
		error : ErrorView,
		partials : {
			Header : HeaderView,
			Navigation : NavigationView,
			Aside : AsideView,
			Footer : FooterView,
			PageLinks : PageLinksView,
			Comments : CommentsView,
			CommentForm : CommentFormView
		}
	};

});