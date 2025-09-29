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
 * Message providers for the Mindscape feed plugin.
 *
 * Defines message providers to notify users when comments are posted on their
 * feed posts. This allows users to configure how they receive these
 * notifications via their messaging preferences.
 *
 * @package    local_mindscape_feed
 */

$messageproviders = [
    // Notification sent to a post author when someone comments on their post.
    'post_comment' => [
        // We require the recipient to have at least view capability so they can access
        // the feed to see the comment. If you want notifications to always be sent,
        // regardless of capabilities, set 'capability' => null.
        'capability' => 'local/mindscape_feed:view',
    ],

    // Notification sent to a post author when someone likes their post.
    'post_like' => [
        // Require the user to have view capability to receive like notifications. Set
        // capability to null if notifications should always be delivered regardless
        // of capability.
        'capability' => 'local/mindscape_feed:view',
    ],
];