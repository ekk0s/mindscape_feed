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
 * Event fired when a user likes a post in the Mindscape feed.
 *
 * The objectid of the event corresponds to the id of the like record inserted
 * into the table `local_mindscape_likes`. The post id is passed via the
 * other array. Observers may use this information to notify the post author.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Mindscape
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_liked extends \core\event\base {
    /**
     * Returns the localised name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventpostliked', 'local_mindscape_feed');
    }

    /**
     * Returns a description of the event.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '{$this->userid}' liked the post with id '" .
            $this->other['postid'] . "' in the Mindscape feed.";
    }

    /**
     * Returns a URL associated with the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $this->other['postid']]);
    }

    /**
     * Init the event's data (crud and edulevel).
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
}