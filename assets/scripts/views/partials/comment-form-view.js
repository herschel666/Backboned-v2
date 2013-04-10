define([
	'app',
	'backbone',
	'mustache'
], function (app, Backbone, Mustache) {

	return Backbone.View.extend({

		tmpl : $('#commentform-tmpl').html(),

		tagName : 'section',

		events : {
			'submit form' : 'validateForm'
		},

		initialize : function initialize() {

			this.render();
			app.on('UI.removeCommentForm', this.removeForm, this);

		},

		render : function render() {

			return this
				.delegateEvents()
				.$el
				.html(Mustache.render(this.tmpl, {
					commentform : this.options.content
				}))
				.show();

		},

		removeForm : function removeForm() {

			this
				.undelegateEvents()
				.$el
				.hide()
				.empty();

		},

		validateEmail : function validateEmail(addr) {

			var re = new RegExp('[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?');

			return re.test(addr);

		}, 

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

		submitForm : function submitForm(formData, formElem) {

			$.post('wp-comments-post.php', formData, $.proxy(this.handleFormResponse, this, formElem));

		},

		handleFormResponse : function handleFormResponse(formElem, resp) {

			if ( !resp.hasOwnProperty('comments') ) {
				alert('Sorry, something went wrong. Please try again or contact the administrator.');
			}

			formElem.reset();
			app.trigger('UI.newCommentAdded', resp.comments);

		}

	});

});