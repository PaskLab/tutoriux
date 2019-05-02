/**
 * Module Name: Login
 */

import TutoriuxTranslations from '../../global/translations';

export default (function () {

	var handleLogin = function() {

		var $form = $('.login-form');

		$form.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			rules: {
				_username: {
					required: true
				},
				_password: {
					required: true
				}
			},

			messages: {
				_username: {
					required: TutoriuxTranslations.required_username[session_locale]
				},
				_password: {
					required: TutoriuxTranslations.required_password[session_locale]
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				$('.alert-danger', $('.login-form')).show();
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				$(form).find('input.spec_date').val(null);
				form.submit(); // form validation success, call ajax form submit
			}
		});

		$('.login-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($form.validate().form()) {
					$form.find('input.spec_date').val(null);
					$form.submit(); //form validation success, call ajax form submit
				}
				return false;
			}
		});

		$('#login-username').focus();
	};

	var handleForgetPassword = function () {

		var $form = $('.forget-form');

		$form.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				'forgot_password[email]': {
					required: true,
					email: true
				}
			},

			messages: {
				'forgot_password[email]': {
					required: TutoriuxTranslations.required_email[session_locale]
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit

			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				$(form).find('input.spec_date').val(null);
				form.submit();
			}
		});

		$('.forget-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($form.validate().form()) {
					$form.find('input.spec_date').val(null);
					$form.submit();
				}
				return false;
			}
		});

		jQuery('#forget-password').click(function () {
			jQuery('.login-form').hide();
			jQuery('.forget-form').show();
		});

		jQuery('#back-btn').click(function () {
			jQuery('.login-form').show();
			jQuery('.forget-form').hide();
		});

	};

	var handleResetPassword = function () {

		var $form = $('#reset-password-form');

		$form.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				'change_password[username]': {
					required: true
				},
				'change_password[password][first]': {
					required: true,
					minlength: 8
				},
				'change_password[password][second]': {
					equalTo: "#change_password_password_first"
				}
			},

			messages: {
				'change_password[username]': {
					required: TutoriuxTranslations.required_username[session_locale]
				},
				'change_password[password][first]': {
					required: TutoriuxTranslations.required_password[session_locale],
					minlength: TutoriuxTranslations.minlength_password[session_locale]
				},
				'change_password[password][second]': {
					required: TutoriuxTranslations.required_confirm_password[session_locale],
					equalTo: TutoriuxTranslations.confirm_password[session_locale]
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				$('.alert-danger', $form).show();
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				$(form).find('input.spec_date').val(null);
				form.submit();
			}
		});

		$form.find('input').keypress(function (e) {
			if (e.which == 13) {
				if ($form.validate().form()) {
					$form.find('input.spec_date').val(null);
					$form.submit();
				}
				return false;
			}
		});
	};

	var handleRegister = function () {

		var $form = $('.register-form'),
			currentUrl = document.URL,
			urlParts   = currentUrl.split('#');

		if (urlParts.length > 1 && 'register' === urlParts[1]) {
			jQuery('.login-form').hide();
			$form.show();
		}

		$form.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				'user[username]': {
					required: true
				},
				'user[email]': {
					required: true,
					email: true
				},
				'user[password][first]': {
					required: true,
					minlength: 8
				},
				'user[password][second]': {
					equalTo: "#user_password_first"
				},
				'user[tnc]': {
					required: true
				}
			},

			messages: { // custom messages for radio buttons and checkboxes
				'user[username]': {
					required: TutoriuxTranslations.required_username[session_locale]
				},
				'user[email]': {
					required: TutoriuxTranslations.required_email[session_locale],
					email: TutoriuxTranslations.invalid_email[session_locale]
				},
				'user[password][first]': {
					required: TutoriuxTranslations.required_password[session_locale],
					minlength: TutoriuxTranslations.minlength_password[session_locale]
				},
				'user[password][second]': {
					required: TutoriuxTranslations.required_confirm_password[session_locale],
					equalTo: TutoriuxTranslations.confirm_password[session_locale]
				},
				'user[tnc]': {
					required: TutoriuxTranslations.accept_tnc[session_locale]
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit

			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				if (element.attr("name") == "user[tnc]") { // insert checkbox errors after the container if unchecked
					error.appendTo($('#register_tnc_error'));
				} else if (element.closest('.input-icon').size() === 1) {
					error.insertAfter(element.closest('.input-icon'));
				} else {
					error.insertAfter(element);
				}
			},

			submitHandler: function (form) {
				$(form).find('input.spec_date').val(null);
				form.submit();
			}
		});

		$form.find('input').keypress(function (e) {
			if (e.which == 13) {
				if ($form.validate().form()) {
					$form.find('input.spec_date').val(null);
					$form.submit();
				}
				return false;
			}
		});

		jQuery('#register-btn').click(function () {
			jQuery('.login-form').hide();
			$form.show();
		});

		jQuery('#register-back-btn').click(function () {
			jQuery('.login-form').show();
			$form.hide();
		});

		if ($form.find('.has-error').length) {
			jQuery('.login-form').hide();
			$form.show();
		}
	};

	return {
		//main function to initiate the module
		init: function () {

			handleLogin();
			handleForgetPassword();
			handleResetPassword();
			handleRegister();
		}

	};

}());