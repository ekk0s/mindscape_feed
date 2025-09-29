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

namespace local_mindscape_feed\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event fired when a comment is created on a post in the Mindscape feed.
 *
 * This event is triggered after a user posts a new comment on an existing
 * feed post. It records the ID of the new comment (as the objectid) and
 * includes the parent post ID in the other array. Observers can listen to
 * this event to perform actions such as notifying the post author.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Mindscape
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment_created extends \core\event\base {
    /**
     * Returns the localized name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcommentcreated', 'local_mindscape_feed');
    }

    /**
     * Returns a description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '{$this->userid}' posted a comment with id '{$this->objectid}' on post with id '" .
            $this->other['postid'] . "' in the Mindscape feed.";
    }

    /**
     * Returns the URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $this->other['postid']]);
    }

    /**
     * Init the event with the appropriate level and crud type.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
}