<?php
// This file processes dislike and undislike actions for Mindscape Feed posts.
//
// It accepts POST requests with parameters:
// - postid: integer ID of the post being disliked or undisliked
// - action: 'dislike' to add a dislike, 'undislike' to remove a dislike
// - sesskey: standard Moodle session key for CSRF protection
//
// After processing, the script redirects back to the referring page.  The
// implementation mirrors like.php but uses the local_mindscape_dislikes
// table instead of likes.

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

// Require capability to view the feed.  This is sufficient for dislikes.
require_capability('local/mindscape_feed:view', $context);

global $DB, $USER;

// Ensure the post exists.
if (!$DB->record_exists('local_mindscape_posts', ['id' => $postid, 'deleted' => 0])) {
    print_error('invalidpostid', 'local_mindscape_feed');
}

if ($action === 'dislike') {
    if (!$DB->record_exists('local_mindscape_dislikes', ['postid' => $postid, 'userid' => $USER->id])) {
        $record = new stdClass();
        $record->postid = $postid;
        $record->userid = $USER->id;
        $record->timecreated = time();
        $DB->insert_record('local_mindscape_dislikes', $record);
    }
} else if ($action === 'undislike') {
    $DB->delete_records('local_mindscape_dislikes', ['postid' => $postid, 'userid' => $USER->id]);
} else {
    print_error('invalidaction', 'local_mindscape_feed');
}

// Redirect back to the referring page.  Use returnurl param if provided.
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);
if (!$returnurl) {
    $returnurl = (new moodle_url('/local/mindscape_feed/index.php'))->out(false);
}
redirect($returnurl);