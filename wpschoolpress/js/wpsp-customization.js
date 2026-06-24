/* WPSchoolPress - Customization Request Form JS */
var $ = jQuery.noConflict();
$(document).ready(function () {

	$('#wpspCustomizationForm').on('submit', function (e) {
		e.preventDefault();

		var $form      = $(this);
		var $btn       = $('#wpspCustomizationSubmit');
		var $response  = $('#wpsp-customization-response');

		var name        = $.trim($('#wpsp_custom_name').val());
		var email       = $.trim($('#wpsp_custom_email').val());
		var type        = $('#wpsp_custom_type').val();
		var subjectVals = $('input[name="wpsp_custom_subject[]"]:checked').map(function () {
			return this.value;
		}).get();
		var description = $.trim($('#wpsp_custom_description').val());

		var errors = [];
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

		if ( name.length < 2 ) {
			errors.push( wpspCustomization.i18n.name_error );
		}
		if ( ! emailRegex.test( email ) ) {
			errors.push( wpspCustomization.i18n.email_error );
		}
		if ( ! type ) {
			errors.push( wpspCustomization.i18n.type_error );
		}
		if ( subjectVals.length === 0 ) {
			errors.push( wpspCustomization.i18n.subject_error );
		}
		if ( description.length < 20 ) {
			errors.push( wpspCustomization.i18n.desc_error );
		}

		if ( errors.length > 0 ) {
			$response
				.removeClass('alert-success')
				.addClass('alert alert-danger')
				.html( '<ul><li>' + errors.join('</li><li>') + '</li></ul>' )
				.show();
			return;
		}

		$btn.attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> ' + wpspCustomization.i18n.sending);
		$response.hide().removeClass('alert-success alert-danger');

		$.ajax({
			type    : 'POST',
			url     : wpspCustomization.ajax_url,
			data    : {
				action               : 'wpsp_submit_customization',
				wpsp_custom_name        : name,
				wpsp_custom_email       : email,
				wpsp_custom_type        : type,
				wpsp_custom_subject     : subjectVals,
				wpsp_custom_description : description,
				wpsp_custom_budget      : $.trim($('#wpsp_custom_budget').val()),
				wpsp_custom_website     : $.trim($('#wpsp_custom_website').val()),
				wpsp_custom_siteurl     : $('#wpsp_custom_siteurl').val(),
				wpsp_customization_nonce: $('#wpsp_customization_nonce').val(),
			},
			success : function (response) {
				$btn.removeAttr('disabled').html('<i class="fa fa-paper-plane"></i> ' + wpspCustomization.i18n.submit);
				if ( response.success ) {
					$response
						.removeClass('alert-danger')
						.addClass('alert alert-success')
						.text( response.data.message )
						.show();
					$form[0].reset();
				} else {
					$response
						.removeClass('alert-success')
						.addClass('alert alert-danger')
						.text( response.data.message || wpspCustomization.i18n.error )
						.show();
				}
			},
			error   : function () {
				$btn.removeAttr('disabled').html('<i class="fa fa-paper-plane"></i> ' + wpspCustomization.i18n.submit);
				$response
					.removeClass('alert-success')
					.addClass('alert alert-danger')
					.text( wpspCustomization.i18n.error )
					.show();
			}
		});
	});
});