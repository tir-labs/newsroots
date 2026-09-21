(function(window, $) {
  	var regex = /^((?!0)(?!.*\.$)((1?\d?\d|25[0-5]|2[0-4]\d|\*)(\.|$)){4})|(([0-9a-f]|:){1,4}(:([0-9a-f]{0,4})*){1,7})$/;
    $(document).ready(function () {
        if ($('.pda-v3-gold-tooltip')) {
            if ($('.pda-v3-gold-tooltip').tooltip) {
                $('.pda-v3-gold-tooltip').tooltip({
                    position: {
                        my: "left bottom-10",
                        at: "left top",
                    }
                });
            }
        }
		if($('.pda-tooltip')) {
		  $('.pda-tooltip').tooltip({
			position: {
			  // at: "center top"
			  my: "center bottom-10",
			  at: "center top",
			}
		  });
		}
		$("body").on("click", "#pda_gold_signup_newsletter", _pda_gold_signup_newsletter_cb);
		$('#pda_free_pl_blacklist_ips').tagsInput({
			defaultText: '',
			delimiter: ';',
			width: 'auto',
			pattern: regex,
		});
    });

    $('#pda_free_options').submit(function(evt) {
        evt.preventDefault();
        const title_page = $("#title_page_404_input").val();
        if(title_page !== "") {
            $(".selected_page").text("Selected page: ");
            $("#remove_page").show();
            $('.remove-no-access-page').show();
            $(".no-access-selected-page-title").text(title_page);
            $(".no-access-selected-page-label").text('Selected page: ');
            $(".value_page").text(title_page);
        }
        _updateSettingsGeneral({
			hide_protected_files_in_media: $("#hide_protected_files_in_media").prop('checked') ? 'on' : 'off',
			disable_right_click: $("#disable_right_click").prop('checked') ? 'on' : 'off',
            enable_image_hot_linking: $("#enable_image_hot_linking").prop('checked') ? 'on' : 'off',
            enable_directory_listing: $("#enable_directory_listing").prop('checked') ? 'on' : 'off',
            search_result_page_404: $("#search_page_404_input").val(),
			file_access_permission: $("#file_access_permission").val(),
        }, function(error) {
            if(error) {
                console.error(error);
            }
        });
    });

	$('#pda_free_ip_form').submit(function (evt) {
	  evt.preventDefault();
	  _updateSettingsGeneral({
		  pda_free_pl_blacklist_ips: $("#pda_free_pl_blacklist_ips").val(),
		},
		function (error) {
		  if (error) {
			toastr.error('Your settings have been updated failed!', 'Prevent Direct Access Lite')
		  }
		},
		'pda_lite_update_ip_restriction_settings'
	  );
	});

	function setSubmitting() {
		$('#pda_free_submit_btn').val('Saving');
		$("#pda_free_submit_btn").prop("disabled", true);
	}

	function resetSubmitBtn() {
		$('#pda_free_submit_btn').val('Save Changes');
		$("#pda_free_submit_btn").prop("disabled", true);
	}

    function _updateSettingsGeneral(settings, cb, action = 'pda_lite_update_general_settings'){
        var _data = {
            action,
            settings: settings,
            security_check: $("#nonce_pda_v3").val(),
        }
	  	setSubmitting();
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: _data,
            success: function(data) {
			  resetSubmitBtn();
			  $("#pda_free_submit_btn").prop("disabled", false);
                //Do something with the result from server
                if (data === 'invalid_nonce') {
                    alert('No! No! No! Verify Nonce Fails!');
                } else if(data) {
                    //success here
                    console.log("Success", data);
                    toastr.success('Your settings have been updated successfully!', 'Prevent Direct Access Lite')
                } else {
                    console.log("Failed", data);
                }
			  cb();
            },

            error: function(error) {
			  resetSubmitBtn();
			  cb(error);
            },
            timeout: 5000
        });
    }

    function _pda_gold_signup_newsletter_cb(evt) {
	    evt.preventDefault();
	    var email = $("#pda_gold_signup_newsletter_input").val().trim();
	    var emailPattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    $("#pda_gold_signup_newsletter").val("Saving...");
	    if (email && emailPattern.test(email)) {
		    $.ajax({
			    url: newsletter_data.newsletter_url,
			    type: 'POST',
			    data: {
				    action: 'pda_free_subscribe',
				    security_check: newsletter_data.newsletter_nonce,
				    email: email
			    },
			    success: function (data) {
				    $(".pda_sub_form").hide();
				    $(".newsletter_inform").show("slow");
				    console.log("Success", data);
				    $("#pda_gold_signup_newsletter").val("Get Lucky");
			    },
			    error: function (error) {
				    $(".pda_sub_form").hide();
				    $(".newsletter_inform").show("slow");
				    $("#pda_gold_signup_newsletter").val("Get Lucky");
			    }
		    });
	    } else {
		    $("#pda_signup_newsletter_error").show("slow");
		    $("#pda_signup_newsletter").focus();
		    $("#pda_gold_signup_newsletter").val("Get Lucky");
	    }
    }

})(window, jQuery);


