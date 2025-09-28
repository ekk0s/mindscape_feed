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

// Require the user to be logged in and have the view capability.
$context = context_system::instance();
require_login();
require_capability('local/mindscape_feed:view', $context);

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('pluginname', 'local_mindscape_feed'));

// Render the feed page using our renderable and template.
$renderable = new \local_mindscape_feed\output\feed_page();

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_mindscape_feed/feed', $renderable->export_for_template($OUTPUT));
echo $OUTPUT->footer();