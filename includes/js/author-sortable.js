jQuery(document).ready(function($) {  

// Make the term list sortable
        $("#author-order-terms").sortable({
            items: '.item',
            placeholder: 'sortable-placeholder',
            tolerance: 'pointer',
            distance: 1,
            forcePlaceholderSize: true,
            helper: 'clone',
            cursor: 'move'
        });
// Save the order using ajax        
   $("#save_term_order").live("click", function() {
        var postID = $("#post_ID").val();
        jQuery.post(ajaxurl, {
        action:'save_term_order', 
        cache: false, 
        post_id: postID,  
        order: jQuery("#the-terms").sortable('toArray').toString(),
        success: ajax_response()
       });
            return false; 
    });   
 });
