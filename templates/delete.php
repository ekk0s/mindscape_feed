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

// Require login, sesskey and moderation capability.
$systemcontext = context_system::instance();
require_login();
require_sesskey();
require_capability('local/mindscape_feed:moderate', $systemcontext);

// Validate the post id to be deleted.
$postid = required_param('postid', PARAM_INT);

if ($postid) {
    global $DB;
    // Soft-delete the post and all of its comments.
    if ($DB->record_exists('local_mindscape_posts', ['id' => $postid])) {
        $DB->set_field('local_mindscape_posts', 'deleted', 1, ['id' => $postid]);
        $DB->set_field('local_mindscape_comments', 'deleted', 1, ['postid' => $postid]);
    }
}

redirect(new moodle_url('/local/mindscape_feed/index.php'));