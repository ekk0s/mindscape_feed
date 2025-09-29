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

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer definitions for the Mindscape feed plugin.
 *
 * This file defines observers that listen to events emitted by the plugin.
 * When a comment is created, the observer will notify the post author.
 *
 * @package    local_mindscape_feed
 */

$observers = [
    [
        'eventname'   => '\\local_mindscape_feed\\event\\comment_created',
        'callback'    => '\\local_mindscape_feed\\observers::notify_post_author',
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\\local_mindscape_feed\\event\\post_liked',
        'callback'    => '\\local_mindscape_feed\\observers::notify_post_author_like',
        'priority'    => 9999,
    ],
];