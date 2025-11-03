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
 * Debates listing page for the Mindscape Feed plugin.
 *
 * Presents a list of weekly debates, their descriptions and links back
 * to associated posts or embedded Kialo activities.  The data is
 * provided by the debates_page renderable class.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/debates.php'));
$PAGE->set_title(get_string('weeklydebates', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('weeklydebates', 'local_mindscape_feed'));
$PAGE->set_pagelayout('standard');

$page = new local_mindscape_feed\output\debates_page();

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_mindscape_feed/debates', $page->export_for_template($OUTPUT));
echo $OUTPUT->footer();