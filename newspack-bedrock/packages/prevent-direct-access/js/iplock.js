var $ = jQuery.noConflict();
var regex = /^((?!0)(?!.*\.$)((1?\d?\d|25[0-5]|2[0-4]\d|\*)(\.|$)){4})|(([0-9a-f]|:){1,4}(:([0-9a-f]{0,4})*){1,7})$/;
$(document).ready(function($){
	$('#ip_lock').tagsInput({
		defaultText: '',
		delimiter: ';',
		width: 'auto',
		pattern: regex,
	});
});
