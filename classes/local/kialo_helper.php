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
 * Helper class providing utility functions to integrate the Kialo activity
 * with the Mindscape Feed plugin.  This class encapsulates logic for
 * creating a hidden container course, creating new Kialo activities on
 * demand, and enrolling users into that container course as needed.
 *
 * The Kialo plugin requires a valid course module context (course id and
 * course module id) in order to launch the LTI deep-link.  Normally a
 * teacher must create a Kialo activity manually in a course.  To avoid
 * requiring administrators or moderators to create hidden courses and
 * activities manually, this helper automates those tasks.  All debates
 * created in the Mindscape Feed can therefore have a dedicated Kialo
 * activity without exposing the underlying course to users.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mindscape_feed\local;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core\role\helper as role_helper;
use moodle_exception;

/**
 * Class kialo_helper
 *
 * Provides static methods to manage the hidden container course and Kialo
 * activities used by the Mindscape Feed debates.  Methods in this class
 * automatically create and configure a course and its enrolment settings,
 * create Kialo activities within that course, and enrol users into the
 * course when they attempt to view a debate.  All methods are static
 * because the helper maintains no state between calls.
 */
class kialo_helper {
    /** @var string Unique idnumber used to identify the hidden course. */
    public const COURSE_IDNUMBER = 'mindscape_debates_container';
    /** @var string Short name used when creating the hidden course. */
    public const COURSE_SHORTNAME = 'Mindscape Debates (container)';

    /**
     * Ensure there is a hidden course used to store Kialo activities.  If
     * the course does not exist, it will be created.  The course is
     * configured with a topics format and marked as invisible.  The
     * returned object is the course record.
     *
     * @return \stdClass The course record for the container course.
     */
    public static function ensure_container_course(): \stdClass {
        global $DB, $CFG;
        // Check for an existing course with the idnumber; idnumber is unique.
        $course = $DB->get_record('course', ['idnumber' => self::COURSE_IDNUMBER]);
        if ($course) {
            return $course;
        }
        require_once($CFG->dirroot . '/course/lib.php');
        // Prepare course data.  Use minimal settings as this course is not shown
        // to students directly.
        $record = new \stdClass();
        $record->fullname   = 'Mindscape Debates (container)';
        $record->shortname  = self::COURSE_SHORTNAME;
        $record->idnumber   = self::COURSE_IDNUMBER;
        $record->format     = 'topics';
        $record->visible    = 0; // Hide course from students.
        $record->summary    = 'Technical course for storing Kialo activities used by Mindscape Feed debates';
        // Create the course.
        $course = create_course($record);
        return $course;
    }

    /**
     * Ensure that the container course has a manual enrolment instance.  If
     * none exists, create one.  Manual enrolment is used so we can enrol
     * users programmatically when they attempt to view a debate.  Returns
     * the enrolment instance record.
     *
     * @param int $courseid The id of the course to inspect.
     * @return \stdClass The enrolment instance record.
     * @throws moodle_exception If the manual enrol plugin is unavailable.
     */
    public static function ensure_manual_enrol_instance(int $courseid): \stdClass {
        global $DB;
        global $CFG;
        require_once($CFG->dirroot . '/enrol/lib.php');
        // Ensure the manual enrol plugin exists.
        $enrol = enrol_get_plugin('manual');
        if (!$enrol) {
            throw new moodle_exception('Manual enrol plugin not available');
        }
        // Look for an existing manual enrol instance.
        $instances = enrol_get_instances($courseid, false);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                return $instance;
            }
        }
        // None found, create one.
        $course = get_course($courseid);
        $instanceid = $enrol->add_instance($course, ['status' => ENROL_INSTANCE_ENABLED]);
        // Fetch the new instance record.
        $instances = enrol_get_instances($courseid, true);
        foreach ($instances as $instance) {
            if ((int) $instance->id === (int) $instanceid) {
                return $instance;
            }
        }
        // Should never reach here, but return a dummy instance to satisfy return type.
        return (object) ['id' => $instanceid, 'enrol' => 'manual', 'courseid' => $courseid];
    }

    /**
     * Enrol the current user into the container course if not already enrolled.
     * This uses the manual enrolment plugin.  Users are enrolled with the
     * default student role and an active enrolment.  If the user is
     * already enrolled, nothing happens.  Note: this function requires
     * the current user to have an active session; ensure require_login()
     * has been called before invoking this.
     *
     * @param int $courseid The id of the container course.
     * @param int $userid The id of the user to enrol.
     * @return void
     */
    public static function enrol_user_if_needed(int $courseid, int $userid): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/enrol/lib.php');
        $context = context_course::instance($courseid);
        // If already enrolled (either active or suspended) return early.
        if (is_enrolled($context, $userid, '', true)) {
            return;
        }
        $enrol = enrol_get_plugin('manual');
        if (!$enrol) {
            return;
        }
        // Ensure manual enrol instance.
        $instance = self::ensure_manual_enrol_instance($courseid);
        // Determine default student role.
        $studentrole = role_helper::get_default_role('student');
        // Enrol the user.
        $enrol->enrol_user($instance, $userid, $studentrole->id, time(), 0, ENROL_USER_ACTIVE);
    }

    /**
     * Create a new Kialo activity within the container course.  The activity
     * will be created in the top-level section of the course and set to
     * invisible.  Returns the course module id for the new activity.  If
     * anything goes wrong during creation, an exception will be thrown.
     *
     * @param string $name The name of the Kialo activity (used as the instance name).
     * @param string $introhtml Optional HTML content for the activity intro.
     * @return int The course module id (cmid) of the new Kialo activity.
     */
    public static function create_kialo_activity(string $name, string $introhtml = ''): int {
        global $CFG;
        // Create or fetch the container course.
        $course = self::ensure_container_course();
        require_once($CFG->dirroot . '/course/modlib.php');
        // Build the module info object according to add_moduleinfo API.
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename   = 'kialo';
        $moduleinfo->course       = $course->id;
        $moduleinfo->section      = 0;           // Add to section 0 (top).
        $moduleinfo->visible      = 0;           // Invisible in course page.
        $moduleinfo->name         = $name;
        $moduleinfo->intro        = $introhtml;
        $moduleinfo->introformat  = FORMAT_HTML;
        // Use Moodle's core function to add the module to the course.
        $mi = add_moduleinfo($moduleinfo, $course);
        // add_moduleinfo returns an object containing ->coursemodule (cmid).
        return (int) $mi->coursemodule;
    }

    /**
     * Public wrapper that ensures a new Kialo activity exists for a given
     * debate.  This helper simply calls create_kialo_activity and returns
     * the resulting course module id.  It exists to abstract away future
     * complexity (e.g. linking to existing activities based on title).
     *
     * @param string $title The title of the debate/Kialo activity.
     * @param string $intro Optional introductory text for the Kialo activity.
     * @return int The course module id for the new Kialo activity.
     */
    public static function ensure_cmid_for_debate(string $title, string $intro = ''): int {
        return self::create_kialo_activity($title, $intro);
    }
}