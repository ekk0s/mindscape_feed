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
 * Profile page for the Mindscape Feed plugin.
 *
 * Shows user information and their posts in a dedicated profile view.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

// Identify which profile to display.  If no id is provided then show the current user.
$userid = optional_param('id', 0, PARAM_INT);
if (!$userid) {
    $userid = $USER->id;
}

// Fetch the user record up front so we can build page metadata and check validity.
$user = \core_user::get_user($userid, '*', MUST_EXIST);

// Process friend system actions before rendering.  Use a dedicated parameter name
// to avoid collisions with other GET parameters.  We only run these actions
// when the session key is valid to protect against CSRF.  If the table
// `local_mindscape_friends` doesn't exist, the actions are silently ignored.
$friendaction = optional_param('friendaction', '', PARAM_ALPHA);
$requestid    = optional_param('req', 0, PARAM_INT);
if ($friendaction && confirm_sesskey()) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('local_mindscape_friends')) {
        try {
            switch ($friendaction) {
                case 'send':
                    // Send a friend request from current user to the profile owner.  Do not allow self requests.
                    if ($userid != $USER->id) {
                        // Check if any relationship exists in either direction.
                        $existing = $DB->get_record_sql(
                            'SELECT * FROM {local_mindscape_friends} WHERE (userid = ? AND friendid = ?) OR (userid = ? AND friendid = ?)',
                            [$USER->id, $userid, $userid, $USER->id]
                        );
                        if (!$existing) {
                            $record = new \stdClass();
                            $record->userid = $USER->id;
                            $record->friendid = $userid;
                            $record->status = 'pending';
                            $record->timecreated = time();
                            $DB->insert_record('local_mindscape_friends', $record);
                            \core\notification::add(get_string('friendrequestsent', 'local_mindscape_feed'), \core\notification::SUCCESS);
                        }
                    }
                    break;
                case 'accept':
                    // Accept a pending friend request.  Only the intended recipient can accept.
                    if ($requestid) {
                        $req = $DB->get_record('local_mindscape_friends', ['id' => $requestid, 'friendid' => $USER->id, 'status' => 'pending'], '*', IGNORE_MISSING);
                        if ($req) {
                            $req->status = 'accepted';
                            $DB->update_record('local_mindscape_friends', $req);
                            \core\notification::add(get_string('friendrequestaccepted', 'local_mindscape_feed'), \core\notification::SUCCESS);
                        }
                    }
                    break;
                case 'cancel':
                    // Cancel a pending friend request sent by the current user.  Remove the record.
                    if ($userid != $USER->id) {
                        $pending = $DB->get_record('local_mindscape_friends', ['userid' => $USER->id, 'friendid' => $userid, 'status' => 'pending'], '*', IGNORE_MISSING);
                        if ($pending) {
                            $DB->delete_records('local_mindscape_friends', ['id' => $pending->id]);
                            \core\notification::add(get_string('friendrequestcancelled', 'local_mindscape_feed'), \core\notification::SUCCESS);
                        }
                    }
                    break;
                case 'remove':
                    // Remove an existing friendship.  Either side can remove the relationship.
                    $rel = $DB->get_record_sql(
                        'SELECT * FROM {local_mindscape_friends} WHERE (userid = ? AND friendid = ?) OR (userid = ? AND friendid = ?)',
                        [$USER->id, $userid, $userid, $USER->id]
                    );
                    if ($rel) {
                        $DB->delete_records('local_mindscape_friends', ['id' => $rel->id]);
                        \core\notification::add(get_string('friendremoved', 'local_mindscape_feed'), \core\notification::SUCCESS);
                    }
                    break;
            }
        } catch (Throwable $e) {
            \core\notification::add(get_string('friendrequesterror', 'local_mindscape_feed'), \core\notification::ERROR);
        }
    }
    // Always redirect to remove action parameters and avoid duplicate submissions.
    redirect(new moodle_url('/local/mindscape_feed/profile.php', ['id' => $userid]));
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/profile.php', ['id' => $userid]));
$PAGE->set_title(fullname($user));
$PAGE->set_heading(fullname($user));
$PAGE->set_pagelayout('standard');

$profilepage = new local_mindscape_feed\output\profile_page($userid);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_mindscape_feed/profile', $profilepage->export_for_template($OUTPUT));
echo $OUTPUT->footer();
