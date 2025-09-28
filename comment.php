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

require_once(__DIR__ . '/../../config.php');

// Require login and a valid sesskey.  Check capability to comment.
$systemcontext = context_system::instance();
require_login();
require_sesskey();
require_capability('local/mindscape_feed:comment', $systemcontext);

// Validate and fetch the post id.
$postid = required_param('postid', PARAM_INT);
$content = optional_param('content', '', PARAM_RAW);

if ($postid && trim($content) !== '') {
    global $DB, $USER;
    // Ensure the parent post exists and is not deleted.
    if ($DB->record_exists('local_mindscape_posts', ['id' => $postid, 'deleted' => 0])) {
        $comment = (object) [
            'postid' => $postid,
            'userid' => $USER->id,
            'content' => clean_text($content, FORMAT_HTML),
            'timecreated' => time(),
            'deleted' => 0,
        ];
        $DB->insert_record('local_mindscape_comments', $comment);
    }
}

// Redirect back to the feed, anchored to the commented post.
redirect(new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]));