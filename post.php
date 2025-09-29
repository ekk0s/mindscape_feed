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

// Require the user to be logged in and have appropriate capability.
$systemcontext = context_system::instance();
require_login();
require_sesskey();
require_capability('local/mindscape_feed:post', $systemcontext);

// Retrieve submitted content.
$content = optional_param('content', '', PARAM_RAW);

// Only proceed if there is content or attachments to post.
if (trim($content) !== '' || !empty($_FILES['attachments']['name'][0])) {
    global $DB, $USER;
    // Create a new post record.
    $post = (object) [
        'userid' => $USER->id,
        'content' => clean_text($content, FORMAT_HTML),
        'timecreated' => time(),
        'timemodified' => time(),
        'deleted' => 0,
    ];
    $postid = $DB->insert_record('local_mindscape_posts', $post);

    // Handle file uploads if any were provided.  We expect multiple files in the
    // attachments array.  Use Moodle's file API to move them into the file store.
    if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
        $fs = get_file_storage();
        foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
            // Skip if there is no file at this index.
            if (empty($tmp) || !is_uploaded_file($tmp)) {
                continue;
            }
            $filename = clean_filename($_FILES['attachments']['name'][$i]);
            // Skip empty file names.
            if ($filename === '') {
                continue;
            }
            // Create a file record.  Files are stored in the system context and
            // associated with the post id in the 'attachment' filearea.
            $filerecord = [
                'contextid' => $systemcontext->id,
                'component' => 'local_mindscape_feed',
                'filearea'  => 'attachment',
                'itemid'    => $postid,
                'filepath'  => '/',
                'filename'  => $filename,
            ];
            // Move the uploaded file from the temporary location to the file storage.
            $fs->create_file_from_pathname($filerecord, $tmp);
        }
    }
}

// If this was an AJAX submission, return a JSON response instead of redirecting.
$isajax = optional_param('ajax', 0, PARAM_BOOL);
if ($isajax) {
    // Indicate success so the JS can refresh or update the UI as needed.  We
    // could return additional data here (e.g. new post HTML) but reloading
    // the page is sufficient for now.
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Redirect back to the feed page after posting.
redirect(new moodle_url('/local/mindscape_feed/index.php'));