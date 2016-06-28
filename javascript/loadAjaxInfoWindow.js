
function loadAjaxInfoWindow(el, page) {
    var completeURL = jQuery('base').attr("href") + page; //NOTE THAT jQuery('base').attr('href') should work as the base tag should be included in your header.
    if(jQuery(el).next("div.loadAjaxInfoWindowSpan").html()) {
        jQuery(el).next("div.loadAjaxInfoWindowSpan").slideToggle();
        //jQuery("reference").children().slideToggle();
    }
    else {
        jQuery(el).next("div.loadAjaxInfoWindowSpan").html("loading data...").load(completeURL, {},
            function() {}
        );
    }
    return true;
}
