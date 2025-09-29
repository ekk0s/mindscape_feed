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

namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for the Mindscape feed plugin.
 *
 * This renderer delegates to Mustache to output the feed page.  It exposes
 * a renderable interface for the feed_page class so that the standard
 * renderer pipeline can be used if desired.  In this plugin we primarily
 * render directly from a Mustache template.
 *
 * @package   local_mindscape_feed
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_renderer extends \plugin_renderer_base {
    /**
     * Render the feed page using a Mustache template.
     *
     * @param feed_page $page The feed page renderable.
     * @return string HTML for the feed page.
     */
    public function render_feed_page(feed_page $page): string {
        return $this->render_from_template('local_mindscape_feed/feed', $page->export_for_template($this));
    }
}