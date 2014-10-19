define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	function makeAlert(message) {
		return $('<div class="alert alert-danger fade in" role="alert"></div>').text(message);
	}

	/**
	 * If only WordPress would listen to request preference while
	 * returning error state, we wouldn’t need to parse the HTML
	 * response body like that.
	 **/
	function getXhrBodyResponseText(xhr) {
		var rt = xhr.responseText,
				rtMessageRegEx = new RegExp('(<body.*>|<\/body>)', 'img');
		if(!!rt) {
			str = rt.split(rtMessageRegEx)[2].trim();

			return $(str);
		}
	}

	/*
	 * View for the comment-form.
	**/
	return Backbone.View.extend({

		SOMETHING_WENT_WRONG: 'Sorry, something went wrong. Please try again or contact the administrator',

		tmpl : $('#commentform-tmpl').html(),

		tagName : 'section',

		events : {
			'submit form' : 'validateForm'
		},

		initialize : function initialize(opts) {

			this.opts = opts || {};
			this.render();
			app.on('UI.removeCommentForm', this.removeForm, this);

		},

		/*
		 * Delegating the submit-event and rendering the form.
		**/
		render : function render() {

			return this
				.delegateEvents()
				.$el
				.html(Mustache.render(this.tmpl, {
					commentform : this.opts.content
				}))
				.show();

		},

		/*
		 * Undelegating the submit-event and removing the form.
		**/
		removeForm : function removeForm() {

			this
				.undelegateEvents()
				.$el
				.hide()
				.empty();

		},

		/*
		 * Method for validating an email-address.
		 * The regular expression is an ugly piece
		 * of stinking horse shit.
		 *
		 * @todo EK: try out /^.+@.+\..+$/ or something similar.
		**/
		validateEmail : function validateEmail(addr) {

			var re = new RegExp('[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?');

			return re.test(addr);

		},

		/*
		 * Validating the submitted form data and
		 * adding/removing error highlight classes.
		**/
		validateForm : function validateForm(evnt) {

			var elem = evnt.currentTarget,
				$elem = $(elem),
				authorValid = elem.author.value,
				emailValid = this.validateEmail(elem.email.value),
				commentValid = elem.comment.value,
				$authorParent = $(elem.author).parent(),
				$emailParent = $(elem.email).parent(),
				$commentParent = $(elem.comment).parent();

			evnt.preventDefault();

			$authorParent[!authorValid
				? 'addClass'
				: 'removeClass'
			]('error');

			$emailParent[!emailValid
				? 'addClass'
				: 'removeClass'
			]('error');

			$commentParent[!commentValid
				? 'addClass'
				: 'removeClass'
			]('error');

			$elem[!emailValid || !authorValid || !commentValid
				? 'addClass'
				: 'removeClass'
			]('error');

			if ( !emailValid || !authorValid || !commentValid ) {
				return false;
			}

			this.submitForm.call(this, $(evnt.currentTarget).serializeArray(), elem);

			return false;

		},

		/*
		 * Asynchronously sending the form data to the wordpress system.
		**/
		submitForm : function submitForm(formData, formElem) {
			var proxy    = $.proxy(this.handleFormResponse, this, formElem),
					endpoint = 'wp-comments-post.php'; // Make this configurable #TODO

			var promise = $.post(endpoint, formData, proxy, 'json');

			promise.error(this.handleFormErrors);
		},

		handleFormErrors : function handleFormErrors(xhr, xhrStatusText) {
			console.log('handleFormErrors', arguments);

			if ( xhr.status > 399 ) {
				$('#comment-form form .alert').remove();
				// Take xhr body response text, if WP install is already supports other
				// languages than english, the message from HTML error page will be in
				// that language already.
				makeAlert(getXhrBodyResponseText(xhr).text()).prependTo('#comment-form form');
			}
		},

		/**
		 * Error message or - if nothing went wrong - resetting the form
		 * and append the new comment.
		 **/
		handleFormResponse : function handleFormResponse(formElem, responseText, xhrStatusText, xhr) {
			var resp = $.parseJSON(responseText);

			if ( !resp.hasOwnProperty('comments') ) {
				makeAlert(this.SOMETHING_WENT_WRONG).prependTo('#comment-form form');
			}

			formElem.reset();
			app.trigger('UI.newCommentAdded', resp.comments);
		}

	});

});