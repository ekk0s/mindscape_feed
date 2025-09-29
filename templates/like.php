<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Like/unlike handler for the Mindscape feed.
 *
 * This script processes requests to like or unlike a feed post.  It requires a valid
 * sesskey and a logged-in user. If the post exists and the user has not yet liked it,
 * a like record is inserted. If the action is unlike, any existing like record for
 * that user and post is removed. After processing, the user is redirected back to
 * the feed anchored to the relevant post.
 *
 * @package   local_mindscape_feed
 * @copyright 2025 Mindscape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

// Parameters: postid and action (like or unlike).
$postid = required_param('postid', PARAM_INT);
$action = required_param('action', PARAM_ALPHANUMEXT);

$context = context_system::instance();

// Ensure the post exists and is not deleted.
$postexists = $DB->record_exists('local_mindscape_posts', ['id' => $postid, 'deleted' => 0]);
if ($postexists) {
    if ($action === 'like') {
        // Only add a like if one doesn't already exist for this user and post.
        $exists = $DB->record_exists('local_mindscape_likes', ['postid' => $postid, 'userid' => $USER->id]);
        if (!$exists) {
            $record = (object) [
                'postid'      => $postid,
                'userid'      => $USER->id,
                'timecreated' => time(),
            ];
            // Insert the record and capture the ID for event purposes.
            $likeid = $DB->insert_record('local_mindscape_likes', $record, true);
            // Trigger the like event so observers can send notifications.
            $eventparams = [
                'objectid' => $likeid,
                'context'  => $context,
                'userid'   => $USER->id,
                'other'    => [
                    'postid' => $postid,
                ],
            ];
            $event = \local_mindscape_feed\event\post_liked::create($eventparams);
            $event->trigger();
        }
    } else if ($action === 'unlike') {
        // Remove any like record for this user on this post.
        $DB->delete_records('local_mindscape_likes', ['postid' => $postid, 'userid' => $USER->id]);
    }
}

// Redirect back to the feed anchored to the post.
// If this is an AJAX request (identified by the 'ajax' parameter), return JSON instead of redirecting.
if (optional_param('ajax', 0, PARAM_BOOL)) {
    // Calculate the latest like count and like status after processing.
    $likescount = $DB->count_records('local_mindscape_likes', ['postid' => $postid]);
    $likedstate = $DB->record_exists('local_mindscape_likes', ['postid' => $postid, 'userid' => $USER->id]);
    // Set JSON header and output the response.
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count'   => (int) $likescount,
        'liked'   => (bool) $likedstate,
    ]);
    exit;
}

redirect(new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]));