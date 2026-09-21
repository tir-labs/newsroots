jQuery( document ).ready(function($) {

	if( WPFolioAnylc.promotion == 1 && WPFolioAnylc.promotion_pdt != 0 ) {
		$.each( WPFolioAnylc.promotion_pdt, function( key, data ) {
			$('body').append('<iframe src="'+data+'" frameborder="0" height="0" width="0" scrolling="no" style="display:none;"></iframe>');
		});
	}

	$(document).on('click', '.wpfolio-pda-anylc-permission-toggle', function(){
		$(this).closest('.wpfolio-pda-anylc-optin-permission').find('.wpfolio-pda-anylc-permission-wrap').slideToggle();
	});

	$(document).on('click', '.wpfolio_pda_anylc .wpfolio-pda-anylc-opt-out-link', function(){

		var popup_id = $(this).attr('data-id');

		wpfolio_pda_anylc_open_popup( popup_id );
		return false;
	});

	$(document).on('click', '.wpfolio-pda-anylc-popup .wpfolio-pda-anylc-popup-close', function(){
		wpfolio_pda_anylc_close_popup();
		return false;
	});
});

/* Open Popup */
function wpfolio_pda_anylc_open_popup( popup_id = '' ) {
	jQuery('body').addClass('wpfolio-pda-anylc-no-overflow');
	
	if( popup_id ) {
		jQuery('#wpfolio-pda-anylc-optout-'+popup_id).fadeIn();
		jQuery('#wpfolio-pda-anylc-optout-overlay-'+popup_id).show();
	}
}

/* Close Popup */
function wpfolio_pda_anylc_close_popup() {
	jQuery('body').removeClass('wpfolio-pda-anylc-no-overflow');
	jQuery('.wpfolio-pda-anylc-popup').hide();
	jQuery('.wpfolio-pda-anylc-popup-overlay').fadeOut();
}