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
 * Management page for weekly debates in the Mindscape Feed plugin.
 *
 * This admin page lists existing debate records and provides a form for
 * creating new debates. Only users with the managedebates capability can
 * access this page. After a debate is created it will appear on the
 * debates page for users to join or view.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/mindscape_feed/classes/form/debate_form.php');

// Require the user to be logged in and have the managedebates capability.
$context = context_system::instance();
require_login();
require_capability('local/mindscape_feed:managedebates', $context);

// Set up the page.
$pageurl = new moodle_url('/local/mindscape_feed/manage_debates.php');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('managedebates', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('managedebates', 'local_mindscape_feed'));
$PAGE->set_pagelayout('admin');

// Instantiate the form for creating a new debate.
$mform = new \local_mindscape_feed\form\debate_form($pageurl);

// Process form submission.
if ($mform->is_cancelled()) {
    // If the form was cancelled, simply reload the page.
    redirect($pageurl);
} else if ($data = $mform->get_data()) {
    // Prepare the record to insert into the database. Use the editor
    // subfield 'text' for the description; if the editor is not used,
    // description may be empty.
    $record = new stdClass();
    $record->title = $data->title;
    $record->description = $data->description['text'] ?? '';
    $record->postid = null;
    $record->weekstart = $data->weekstart;
    $record->active = $data->active;
    $record->timecreated = time();
    $record->kialo_cmid = empty($data->kialo_cmid) ? null : $data->kialo_cmid;

    $DB->insert_record('local_mindscape_debates', $record);

    // Redirect back to the management page with a notification.
    redirect($pageurl, get_string('debatecreated', 'local_mindscape_feed'), 2);
}

// Begin page output.
echo $OUTPUT->header();

// Display a list of existing debates.
echo $OUTPUT->heading(get_string('existingdebates', 'local_mindscape_feed'));

$table = new html_table();
$table->head = [
    get_string('title', 'local_mindscape_feed'),
    get_string('weekstart', 'local_mindscape_feed'),
    get_string('active', 'local_mindscape_feed'),
    get_string('kialo_cmid', 'local_mindscape_feed'),
];

$records = $DB->get_records('local_mindscape_debates', null, 'weekstart DESC');
foreach ($records as $rec) {
    $title = format_string($rec->title, true, ['context' => $context]);
    $weekstart = userdate($rec->weekstart);
    $active = $rec->active ? get_string('yes') : get_string('no');
    $kialoid = $rec->kialo_cmid ? $rec->kialo_cmid : '';
    $table->data[] = [$title, $weekstart, $active, $kialoid];
}

echo html_writer::table($table);

// Display the form for adding a new debate.
echo $OUTPUT->heading(get_string('adddebate', 'local_mindscape_feed'));
$mform->display();

echo $OUTPUT->footer();
