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
 * Renderable for the weekly debates listing page.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

use context_system;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Renderable class that provides data for the weekly debates page template.
 */
class debates_page implements renderable, templatable {
    /**
     * Export the data required by the Mustache template.
     *
     * @param renderer_base $output The renderer requesting the data.
     * @return array Data to be used by the template.
     */
    public function export_for_template(renderer_base $output): array {
        global $DB;

        $context = context_system::instance();

        $records = $DB->get_records('local_mindscape_debates', ['active' => 1], 'weekstart DESC');

        $debates = [];
        foreach ($records as $record) {
            $title = format_string($record->title, true, ['context' => $context]);
            $description = format_text($record->description, FORMAT_HTML, ['context' => $context]);
            $weekstart = userdate($record->weekstart);

            $posturl = null;
            if ($record->postid !== null) {
                $posturl = (new moodle_url('/local/mindscape_feed/index.php', [], 'p' . $record->postid))->out(false);
            }

            $debates[] = [
                'title' => $title,
                'description' => $description,
                'weekstart' => $weekstart,
                'posturl' => $posturl,
            ];
        }

        return ['debates' => $debates];
    }
}
