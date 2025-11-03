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
 * Admin page to add a new weekly debate entry.  Only users with
 * moderation capability may access this page.  The page provides a
 * simple form for creating a new record in the local_mindscape_debates
 * table.  This does not perform advanced validation, but it requires
 * title and weekstart at minimum.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/mindscape_feed:moderate', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/adddebate.php'));
$PAGE->set_title(get_string('navadddebate', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('navadddebate', 'local_mindscape_feed'));

// Load the custom stylesheet for a consistent appearance with the feed.
$PAGE->requires->css('/local/mindscape_feed/styles.css');

$errors = [];

if (data_submitted() && confirm_sesskey()) {
    // Retrieve submitted data.
    $title = required_param('title', PARAM_TEXT);
    $description = required_param('description', PARAM_TEXT);
    $weekstart = required_param('weekstart', PARAM_INT);
    $postid = optional_param('postid', 0, PARAM_INT);
    $kialocmid = optional_param('kialocmid', 0, PARAM_INT);

    // Basic validation: title and weekstart must be provided.
    if (empty($title)) {
        $errors[] = get_string('err_title_required', 'local_mindscape_feed');
    }
    if (empty($weekstart)) {
        $errors[] = get_string('err_weekstart_required', 'local_mindscape_feed');
    }

    if (empty($errors)) {
        // Insert new record.
        $record = new stdClass();
        $record->title = $title;
        $record->description = $description;
        $record->weekstart = $weekstart;
        $record->postid = $postid;
        $record->kialo_cmid = $kialocmid;
        $record->active = 1;
        $record->timecreated = time();
        $record->timemodified = time();

        $id = $DB->insert_record('local_mindscape_debates', $record);
        if ($id) {
            redirect(new moodle_url('/local/mindscape_feed/debates.php'), get_string('debatecreated', 'local_mindscape_feed'), 2);
        } else {
            $errors[] = get_string('err_couldnotcreate', 'local_mindscape_feed');
        }
    }
}

echo $OUTPUT->header();

// Display errors if any.
if (!empty($errors)) {
    echo $OUTPUT->box_start('alert alert-danger');
    foreach ($errors as $error) {
        echo html_writer::tag('p', $error);
    }
    echo $OUTPUT->box_end();
}

// Build the form.
echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/local/mindscape_feed/adddebate.php')]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
// Title.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('debate_title', 'local_mindscape_feed'), ['for' => 'title']);
echo html_writer::empty_tag('input', ['type' => 'text', 'class' => 'form-control', 'name' => 'title', 'id' => 'title', 'value' => optional_param('title', '', PARAM_TEXT), 'required' => 'required']);
echo html_writer::end_div();
// Description.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('debate_description', 'local_mindscape_feed'), ['for' => 'description']);
echo html_writer::tag('textarea', optional_param('description', '', PARAM_TEXT), ['class' => 'form-control', 'name' => 'description', 'id' => 'description', 'rows' => 4]);
echo html_writer::end_div();
// Week start date (timestamp).  Use date selector.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('debate_weekstart', 'local_mindscape_feed'), ['for' => 'weekstart']);
// Use Moodle's date selector element helper.
echo html_writer::start_div();
// We store weekstart as a Unix timestamp; use builtin form element for simplicity.
echo html_writer::empty_tag('input', ['type' => 'datetime-local', 'class' => 'form-control', 'name' => 'weekstart', 'id' => 'weekstart', 'value' => '']);
echo html_writer::end_div();
echo html_writer::end_div();
// Optional post ID.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('debate_postid', 'local_mindscape_feed'), ['for' => 'postid']);
echo html_writer::empty_tag('input', ['type' => 'number', 'class' => 'form-control', 'name' => 'postid', 'id' => 'postid', 'value' => optional_param('postid', '', PARAM_INT)]);
echo html_writer::end_div();
// Optional Kialo course module ID.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('debate_kialocmid', 'local_mindscape_feed'), ['for' => 'kialocmid']);
echo html_writer::empty_tag('input', ['type' => 'number', 'class' => 'form-control', 'name' => 'kialocmid', 'id' => 'kialocmid', 'value' => optional_param('kialocmid', '', PARAM_INT)]);
echo html_writer::end_div();

// Submit button.
echo html_writer::tag('button', get_string('save', 'local_mindscape_feed'), ['type' => 'submit', 'class' => 'btn btn-primary']);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();