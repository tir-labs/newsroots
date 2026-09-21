var $ = jQuery.noConflict();
$(document).ready(function($){
	$('#search').keyup(function() {
		$("#pda_search_result").empty();
	    var search = $("#search").val();
	    var data = {
	    	'action': 'wp-link-ajax',
	    	'search': search,
	    	'page':	1,
	    	'_ajax_linking_nonce': $("#_ajax_linking_nonce").val(),
	    }
	    if (search.length > 0) {
		    var searchTimer;
		    window.clearTimeout( searchTimer );
			window.setTimeout( function() {
				$.ajax({
					type: "POST",
					url: ajax_object.ajax_url,
					data: data,
					dataType: "json",
					success: function (res) {
						$("#pda_search_result").empty();
						// console.log('26', res);
						for (var i = 0; i < res.length; i++) {
							$("#pda_search_result")
							.append('<li id="'+res[i].permalink+'">' + res[i].title +'</li>');
						}
					}
				  }
				);			
			}, 500 );
	    } else {
			$("#pda_search_result").empty();
	    }
	});

	$('#pda_search_result').click(function(evt) {
		var title = evt.target.innerText;
		var link = evt.target.id;
		// $(".title_page_404").html('<input type="hidden" name="search_result_page_404" value = "'+link+';'+title+'"/>');
		$("#search_page_404_input").val(link+';'+title);
		$(evt.target).addClass('selected').siblings().removeClass('selected');
		$("#search").val(title);
		$('#pda_search_result').empty();
	})
});