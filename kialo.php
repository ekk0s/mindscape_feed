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
 * Wrapper page for embedding a Kialo activity inside the Mindscape Feed plugin.
 *
 * This page accepts a course module id ('id') for a mod_kialo activity and renders
 * the Kialo content within an iframe.  Users must have permission to view the
 * Kialo activity.  The page reuses the existing Kialo activity but keeps the user
 * within the local_mindscape_feed plugin.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$cmid = required_param('id', PARAM_INT);

// Fetch the course module, course and instance records for this Kialo activity.
// Attempt to fetch the course module, course and Kialo instance records.  If any of these
// cannot be found, redirect back to the debates page with a user-friendly error.  We
// suppress exceptions to avoid exposing technical messages to users.
try {
    $cm = get_coursemodule_from_id('kialo', $cmid, 0, false, MUST_EXIST);
} catch (Throwable $e) {
    // If the module doesn't exist or Kialo is not installed, redirect with an error.
    redirect(new moodle_url('/local/mindscape_feed/debates.php'), get_string('err_invalid_kialocmid', 'local_mindscape_feed'), 3);
    // Terminate to satisfy static analysis, though redirect() exits.
    die;
}
$course = $DB->get_record('course', ['id' => $cm->course], '*', IGNORE_MISSING);
if (!$course) {
    redirect(new moodle_url('/local/mindscape_feed/debates.php'), get_string('err_invalid_kialocmid', 'local_mindscape_feed'), 3);
    die;
}
$kialo = $DB->get_record('kialo', ['id' => $cm->instance], '*', IGNORE_MISSING);
if (!$kialo) {
    redirect(new moodle_url('/local/mindscape_feed/debates.php'), get_string('err_invalid_kialocmid', 'local_mindscape_feed'), 3);
    die;
}

// Before enforcing login, ensure the current user is enrolled in the container course
// when the Kialo activity was automatically created.  This allows users who are
// not otherwise enrolled in the course to access the activity.  If enrolment
// fails or the helper is unavailable, require_login will still handle access
// control.
require_once($CFG->dirroot . '/local/mindscape_feed/classes/local/kialo_helper.php');
// $USER is a global representing the current user; enrol them if needed.
\local_mindscape_feed\local\kialo_helper::enrol_user_if_needed($course->id, $USER->id);

// Enforce login and capability checks.
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/kialo:view', $context);

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/kialo.php', ['id' => $cmid]));
// Use the Kialo activity name as the page title.
$PAGE->set_title(format_string($kialo->name, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => context_course::instance($course->id)]));
$PAGE->set_pagelayout('incourse');

// Include custom styles so the Kialo wrapper inherits the Mindscape Feed
// appearance.  This call must precede the header output.
$PAGE->requires->css('/local/mindscape_feed/styles.css');

echo $OUTPUT->header();

// Generate the URL to the original Kialo view page.  We pass the id parameter but do not
// force a redirect so the Kialo plugin determines whether to embed or redirect.
$kialourl = new moodle_url('/mod/kialo/view.php', ['id' => $cmid]);

// Render the iframe.  We give it an id to allow CSS/JS to resize the frame as needed.
echo html_writer::start_div('local-mindscape-kialo container my-4');
echo html_writer::tag('h2', format_string($kialo->name));
echo html_writer::tag('iframe', '', [
    'id' => 'mindscape-kialo-frame',
    'src' => $kialourl->out(false),
    'width' => '100%',
    'height' => '800',
    'allowfullscreen' => 'allowfullscreen',
    'style' => 'border:0;',
]);
echo html_writer::end_div();

echo $OUTPUT->footer();
