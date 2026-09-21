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
        };
        console.log(data);
        if (search.length > 2) {
            var searchTimer;
            window.clearTimeout( searchTimer );
            window.setTimeout( function() {
                $.ajax({
                    type: "POST",
                    url: server_data.ajax_url,
                    data: data,
                    dataType: "json",
                    success: function (res) {
                        console.log(res);
                        $("#pda_search_result").empty();
                        for (var i = 0; i < res.length; i++) {
                            $("#pda_search_result")
                                .append('<li id="'+res[i].permalink+'">' + res[i].title +'</li>');
                        }
                    }
                });
            }, 500 );
        } else {
            $("#pda_search_result").empty();
        }
    });

    $('#pda_search_result').click(function(evt) {
        evt.preventDefault();
        var title = evt.target.innerText;
        var link = evt.target.id;
        // $(".title_page_404").html('<input type="hidden" name="search_result_page_404" value = "'+link+';'+title+'"/>');
        $("#search_page_404_input").val(link+';'+title);
        $("#title_page_404_input").val(title);
        $(evt.target).addClass('selected').siblings().removeClass('selected');
        $("#search").val(title);
        $('#pda_search_result').empty();

        // $(".selected_page").text("Selected page: ");
        // $(".value_page").text(title);
        // $(".no-access-selected-page-title").text(title);

    });

    $('.no-access-search-container').mouseleave(function() {
        $(".no-access-search-container").hide();
    });

    $('#search').mouseenter(function() {
        $('.no-access-search-container').show();
    });

    $('.remove-no-access-page').click(function (evt) {
        evt.preventDefault();
        $("#search_page_404_input").val('');
        $("#title_page_404_input").val('');
        $(this).hide();
        $(".no-access-selected-page-title").text('404 page');
        $(".no-access-selected-page-label").text('Default page: ');
        $(".selected_page").text('Default page: ');
        $(".value_page").text('404 page');
        $("#search").val('');

    });

});