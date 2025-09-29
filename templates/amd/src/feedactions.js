/*
 * AMD module for handling interactive actions in the Mindscape feed.
 *
 * This module intercepts like/unlike form submissions and performs them via AJAX
 * to avoid full page reloads. When the request completes successfully it
 * updates the like count and toggles the button text and styling.
 */

define(['jquery'], function($) {
    /**
     * Initialise event listeners for the Mindscape feed.
     */
    function init() {
        // When the like/unlike form is submitted via click, intercept and send via AJAX.
        $('form.mindscape-like-form').on('submit', function(event) {
            event.preventDefault();
            var $form = $(this);
            var postid = $form.data('postid');
            // Determine current like state and flip it to decide action.
            var liked = Boolean($form.data('liked'));
            var action = liked ? 'unlike' : 'like';
            var sesskey = $form.find('input[name="sesskey"]').val();
            $.post(M.cfg.wwwroot + '/local/mindscape_feed/like.php', {
                postid: postid,
                action: action,
                sesskey: sesskey,
                ajax: 1
            }, function(response) {
                if (response && response.success) {
                    // Update the data attribute with new liked state.
                    $form.data('liked', response.liked ? 1 : 0);
                    // Update like count display.
                    $form.find('span.mindscape-like-count').text(response.count);
                    // Update the button label and style based on new state.
                    var $button = $form.find('button[name="action"]');
                    if (response.liked) {
                        $button.text(M.util.get_string('unlike', 'local_mindscape_feed'));
                        $button.removeClass('btn-outline-secondary').addClass('btn-secondary');
                    } else {
                        $button.text(M.util.get_string('like', 'local_mindscape_feed'));
                        $button.removeClass('btn-secondary').addClass('btn-outline-secondary');
                    }
                }
            }, 'json');
        });

        // Before submitting the like form, set the hidden action value correctly based on state.
        $('form.mindscape-like-form button[name="action"]').on('click', function() {
            var $button = $(this);
            var $form = $button.closest('form.mindscape-like-form');
            var liked = Boolean($form.data('liked'));
            var action = liked ? 'unlike' : 'like';
            $button.val(action);
        });
    }

    return {
        init: init
    };
});