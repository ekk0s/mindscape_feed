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
 * Block class for displaying a compact Mindscape feed on the dashboard or frontpage.
 *
 * This block queries the latest posts from the local Mindscape feed plugin and
 * presents them as a list of short summaries linking back to the full feed.
 *
 * @package    block_mindscape_feed
 * @copyright  2025 Mindscape
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Mindscape feed block definition.
 */
class block_mindscape_feed extends block_base {

    /**
     * Initialize the block by setting a title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_mindscape_feed');
    }

    /**
     * Allow multiple instances of the block to be added to a single page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Define where the block can be added.
     *
     * This block is intended primarily for the site frontpage and user dashboard.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'site' => true,       // Front page.
            'my' => true,         // Dashboard page.
            'course-view' => false,
            'course-index' => false,
            'mod' => false,
        ];
    }

    /**
     * Indicate that the block has no global configuration settings.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Generate the content displayed by the block.
     *
     * Queries the most recent posts from the Mindscape feed and constructs
     * a simple HTML list linking to each post within the full feed page. If
     * there are no posts, display a message to that effect.
     *
     * @return stdClass
     */
    public function get_content() {
        global $DB, $OUTPUT, $CFG;

        // If content is already computed, just return it.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Determine number of items to display in the block.  Use the same
        // configuration as the local feed plugin if available, but cap to 5 by default.
        $limit = (int) get_config('local_mindscape_feed', 'itemsperpage');
        if ($limit <= 0 || $limit > 5) {
            $limit = 5;
        }

        // Fetch the most recent posts that have not been deleted.
        $posts = $DB->get_records_select('local_mindscape_posts', 'deleted = 0', [], 'timecreated DESC', '*', 0, $limit);

        // If there are no posts, display a notice.
        if (empty($posts)) {
            // Use string from the local plugin for consistency.
            $this->content->text = get_string('nopostsyet', 'local_mindscape_feed');
            $this->content->footer = '';
            return $this->content;
        }

        // Build an unordered list of recent posts.
        $items = [];
        $context = context_system::instance();
        foreach ($posts as $post) {
            // Get the author full name.
            $user = \core_user::get_user($post->userid);
            $fullname = fullname($user);
            // Format the post content and shorten it for display.
            $formatted = format_text($post->content, FORMAT_HTML, ['context' => $context, 'filter' => true]);
            // Strip HTML tags before shortening to avoid breaking markup.
            $text = trim(strip_tags($formatted));
            $summary = \core_text::substr($text, 0, 100);
            if (\core_text::strlen($text) > 100) {
                $summary .= '…';
            }
            // Count likes for the post.
            $likescount = $DB->count_records('local_mindscape_likes', ['postid' => $post->id]);
            // Build the URL pointing to the post anchor within the full feed page.
            $url = new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $post->id]);
            // Construct link text: Author: summary (likes).
            $linktext = s($fullname . ': ' . $summary);
            // Append likes count if available.
            $linktext .= ' (' . $likescount . ')';
            $items[] = html_writer::link($url, $linktext);
        }

        $this->content->text = html_writer::alist($items, [
            'class' => 'list-unstyled mindscape-feed-block-list'
        ]);
        // Provide a link to view the entire feed.
        $feedurl = new moodle_url('/local/mindscape_feed/index.php');
        $this->content->footer = html_writer::link($feedurl, get_string('seefullfeed', 'block_mindscape_feed'));
        return $this->content;
    }
}