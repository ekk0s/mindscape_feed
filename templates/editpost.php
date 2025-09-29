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

// The ID of the post being edited.
$postid = required_param('id', PARAM_INT);

$context = context_system::instance();
require_login();
require_sesskey();

// Fetch the post record and ensure it exists.
$post = $DB->get_record('local_mindscape_posts', ['id' => $postid], '*', MUST_EXIST);

// Check that the user is allowed to edit this post.
$canmoderate = has_capability('local/mindscape_feed:moderate', $context);
if ($post->userid != $USER->id && !$canmoderate) {
    print_error('nopermission', 'error');
}

// If the form was submitted, update the post.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newcontent = required_param('content', PARAM_RAW);
    $post->content = clean_text($newcontent, FORMAT_HTML);
    $post->timemodified = time();
    $DB->update_record('local_mindscape_posts', $post);
    // Redirect back to the feed anchored to the updated post.
    redirect(new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]));
}

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/editpost.php', ['id' => $postid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('edit', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('edit', 'local_mindscape_feed'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edit', 'local_mindscape_feed'));

// Display the edit form.
echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/local/mindscape_feed/editpost.php')]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $postid]);
echo html_writer::tag('textarea', s($post->content), ['name' => 'content', 'class' => 'form-control mb-2', 'rows' => 6]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary', 'value' => get_string('save', 'local_mindscape_feed')]);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
