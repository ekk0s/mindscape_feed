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
        // Insert the comment and capture its ID.
        $comment->id = $DB->insert_record('local_mindscape_comments', $comment);
        // Trigger an event to notify listeners that a comment has been created.
        $event = \local_mindscape_feed\event\comment_created::create([
            'objectid' => $comment->id,
            'context'  => $systemcontext,
            'userid'   => $USER->id,
            'other'    => [
                'postid' => $postid,
                'commentcontent' => $comment->content,
            ],
        ]);
        $event->trigger();
    }
}

// If this was an AJAX submission, return a JSON snippet for the new comment instead of redirecting.
$isajax = optional_param('ajax', 0, PARAM_BOOL);
if ($isajax) {
    // Fetch the newly created comment record to build the response.  The insert
    // happens above, so retrieve the last comment by this user on this post.
    // It is safe because comments have no concurrency issues on the same post
    // by the same user within the same request.
    if ($postid && trim($content) !== '') {
        // Find the most recent comment matching the inserted data.
        $newcomment = $DB->get_record_sql(
            "SELECT * FROM {local_mindscape_comments}
             WHERE postid = :postid AND userid = :userid AND deleted = 0
             ORDER BY timecreated DESC", ['postid' => $postid, 'userid' => $USER->id], IGNORE_MISSING
        );
    }
    // Build HTML for the new comment.
    $commenthtml = '';
    if (!empty($newcomment)) {
        // Load the comment user.
        $commentuser = \core_user::get_user($newcomment->userid);
        // Render user picture (size 25px).
        $userpic = $OUTPUT->user_picture($commentuser, ['size' => 25]);
        // Format the content.
        $formatted = format_text($newcomment->content, FORMAT_HTML, ['context' => $systemcontext, 'filter' => true]);
        // Build edit URL if the user owns the comment or can moderate.
        $canmoderate = has_capability('local/mindscape_feed:moderate', $systemcontext);
        $isowner = ($newcomment->userid == $USER->id);
        $editlink = '';
        if ($isowner || $canmoderate) {
            $editurl = new \moodle_url('/local/mindscape_feed/editcomment.php', ['id' => $newcomment->id]);
            $editlabel = get_string('edit', 'local_mindscape_feed');
            $editlink = ' <a href="' . $editurl->out(false) . '" class="btn btn-sm btn-outline-secondary ms-2">' . $editlabel . '</a>';
        }
        // Build the HTML structure matching the template.
        $commenthtml .= '<div class="d-flex align-items-start gap-2 mb-2">';
        $commenthtml .= $userpic;
        $commenthtml .= '<div>';
        $commenthtml .= '<div><strong>' . fullname($commentuser) . '</strong></div>';
        $commenthtml .= '<div>' . $formatted . '</div>';
        $commenthtml .= '<small class="text-muted">' . userdate($newcomment->timecreated) . '</small>';
        $commenthtml .= $editlink;
        $commenthtml .= '</div>';
        $commenthtml .= '</div>';
    }
    // Send JSON response.
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html'    => $commenthtml
    ]);
    exit;
}

// Redirect back to the feed, anchored to the commented post.
redirect(new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]));