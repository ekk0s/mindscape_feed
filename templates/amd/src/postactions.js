/*
 * AMD module to handle AJAX submission of new posts and comments in the Mindscape feed.
 *
 * This module intercepts the submission of post and comment forms and sends the
 * data asynchronously to the server. Upon successful completion, it reloads
 * the page for new posts or appends the new comment to the appropriate post
 * without a full page refresh.
 */

define(['jquery'], function($) {
    /**
     * Initialise event listeners for post and comment forms.
     */
    function init() {
        // Intercept submission of new post forms.
        $('form.mindscape-post-form').on('submit', function(event) {
            event.preventDefault();
            var $form = $(this);
            // Construct FormData to handle file uploads.
            var formData = new FormData(this);
            formData.append('ajax', 1);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        // On success simply reload the page to show the new post.
                        window.location.reload();
                    }
                }
            });
        });

        // Intercept submission of comment forms.
        $('form.mindscape-comment-form').on('submit', function(event) {
            event.preventDefault();
            var $form = $(this);
            var postId = $form.data('postid');
            var formData = new FormData(this);
            formData.append('ajax', 1);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success && response.html) {
                        // Locate the comments section for the post.
                        var $article = $('#p' + postId);
                        var $comments = $article.find('section.comments');
                        // Remove any no‑comments placeholder.
                        $comments.find('.text-muted').remove();
                        // Prepend the new comment HTML.
                        $comments.prepend(response.html);
                        // Clear the form textarea.
                        $form[0].reset();
                    } else if (response && response.success) {
                        // Fallback: reload page if no HTML provided.
                        window.location.reload();
                    }
                }
            });
        });
    }

    return {
        init: init
    };
});