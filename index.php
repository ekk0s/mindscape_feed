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
 * Entry point for the Mindscape Feed plugin.
 *
 * This page displays the main feed using the feed_page renderable and
 * the Mustache template defined in templates/feed.mustache.  It
 * enforces login, sets the appropriate page context and loads a
 * custom stylesheet to achieve a minimalistic design.  The feed
 * renders the latest posts, attachments, comments and navigation
 * sidebar.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

// Require the user to be logged in to view the feed.
require_login();

// Obtain the system context for capability checks and page setup.
$context = context_system::instance();
$PAGE->set_context($context);
// Set the URL for this page.  Users access the feed via /local/mindscape_feed/index.php.
$PAGE->set_url(new moodle_url('/local/mindscape_feed/index.php'));
// Use the plugin name as the page title and heading.  The strings
// live in the language file for this plugin.
$PAGE->set_title(get_string('pluginname', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('pluginname', 'local_mindscape_feed'));

// Use the standard layout; this includes the site header and footer.
$PAGE->set_pagelayout('standard');

// Load the custom stylesheet defined by this plugin.  This should
// occur after the context and layout have been set but before
// generating the header.
$PAGE->requires->css('/local/mindscape_feed/styles.css');

// Instantiate the feed page renderable.  The renderable collects
// posts, comments and navigation data and prepares it for the
// Mustache template.
$feedpage = new local_mindscape_feed\output\feed_page();

// Output the page.  Render the header, feed template and footer.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_mindscape_feed/feed', $feedpage->export_for_template($OUTPUT));
echo $OUTPUT->footer();