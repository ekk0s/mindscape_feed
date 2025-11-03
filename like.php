<?php
// This file processes like and unlike actions for Mindscape Feed posts.
//
// It accepts POST requests with the parameters:
// - postid: integer ID of the post being liked or unliked
// - action: 'like' to like a post, 'unlike' to remove the like
// - sesskey: standard Moodle session key for CSRF protection
//
// After processing, the script redirects back to the referring page.  It
// does not output any HTML.  AJAX usage is not implemented in this
// simple version; the page must be refreshed to reflect updated counts.

require(__DIR__ . '/../../config.php');

require_login();

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    print_error('invalidaccess');
}

// Validate session key.
require_sesskey();

$postid = required_param('postid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$context = context_system::instance();

// Users need the capability to view the feed to like/unlike posts.  Use
// the existing view capability rather than introducing a separate one.
require_capability('local/mindscape_feed:view', $context);

global $DB, $USER;

// Ensure the post exists.
if (!$DB->record_exists('local_mindscape_posts', ['id' => $postid, 'deleted' => 0])) {
    print_error('invalidpostid', 'local_mindscape_feed');
}

// Process like/unlike.
if ($action === 'like') {
    // Only insert a like if one does not already exist.
    if (!$DB->record_exists('local_mindscape_likes', ['postid' => $postid, 'userid' => $USER->id])) {
        $record = new stdClass();
        $record->postid = $postid;
        $record->userid = $USER->id;
        $record->timecreated = time();
        $DB->insert_record('local_mindscape_likes', $record);
    }
} else if ($action === 'unlike') {
    // Remove the like if it exists.
    $DB->delete_records('local_mindscape_likes', ['postid' => $postid, 'userid' => $USER->id]);
} else {
    print_error('invalidaction', 'local_mindscape_feed');
}

// Redirect back to the referring page.  Fallback to the feed if HTTP_REFERER is not set.
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);
if (!$returnurl) {
    $returnurl = (new moodle_url('/local/mindscape_feed/index.php'))->out(false);
}
redirect($returnurl);