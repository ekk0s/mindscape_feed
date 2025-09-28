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

use renderable;
use templatable;
use renderer_base;

/**
 * Renderable representing the Mindscape feed page.
 *
 * This class gathers the necessary data to display the feed, including
 * recent posts, their attachments and comments.  It checks the current
 * user's capabilities to determine whether the posting and commenting
 * interfaces should be shown.
 *
 * @package   local_mindscape_feed
 */
class feed_page implements renderable, templatable {
    /** @var \context_system System context used for file handling and capability checks */
    protected $context;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->context = \context_system::instance();
    }

    /**
     * Export data for the Mustache template.
     *
     * The returned array contains information about whether the user may
     * create posts or comments, the collection of posts (including user
     * pictures, attachments and comments), and form actions for posting
     * and commenting.
     *
     * @param renderer_base $output The renderer requesting the data.
     * @return array Data ready for use in a Mustache template.
     */
    public function export_for_template(renderer_base $output): array {
        global $DB, $USER;

        // Determine capabilities for the current user.
        $systemcontext = $this->context;
        $canpost = has_capability('local/mindscape_feed:post', $systemcontext);
        $cancomment = has_capability('local/mindscape_feed:comment', $systemcontext);
        $canmoderate = has_capability('local/mindscape_feed:moderate', $systemcontext);

        // Determine how many posts to show per page.  Default to 20 if not configured.
        $perpage = (int) get_config('local_mindscape_feed', 'itemsperpage');
        if ($perpage <= 0) {
            $perpage = 20;
        }

        // Fetch the most recent posts (not deleted) ordered by newest first.
        $posts = $DB->get_records_select('local_mindscape_posts', 'deleted = 0', [], 'timecreated DESC', '*', 0, $perpage);

        $items = [];
        $fs = get_file_storage();
        foreach ($posts as $post) {
            // Load user details for each post.
            $postuser = \core_user::get_user($post->userid);
            // Prepare attachments.
            $files = $fs->get_area_files($this->context->id, 'local_mindscape_feed', 'attachment', $post->id, 'id', false);
            $attachments = [];
            foreach ($files as $file) {
                $fileurl = \moodle_url::make_pluginfile_url(
                    $this->context->id,
                    'local_mindscape_feed',
                    'attachment',
                    $post->id,
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $attachments[] = [
                    'url' => $fileurl->out(false),
                    'filename' => $file->get_filename(),
                ];
            }
            // Prepare comments.
            $comments = $DB->get_records_select('local_mindscape_comments', 'deleted = 0 AND postid = ?', [$post->id], 'timecreated ASC');
            $commentitems = [];
            foreach ($comments as $comment) {
                $commentuser = \core_user::get_user($comment->userid);
                $commentitems[] = [
                    'userpic' => $output->user_picture($commentuser, ['size' => 25]),
                    'fullname' => fullname($commentuser),
                    'content' => format_text($comment->content, FORMAT_HTML, ['context' => $systemcontext, 'filter' => true]),
                    'time' => userdate($comment->timecreated),
                ];
            }
            $items[] = [
                'id' => $post->id,
                'userpic' => $output->user_picture($postuser, ['size' => 35]),
                'fullname' => fullname($postuser),
                'content' => format_text($post->content, FORMAT_HTML, ['context' => $systemcontext, 'filter' => true]),
                'time' => userdate($post->timecreated),
                'attachments' => $attachments,
                'isowner' => $post->userid == $USER->id,
                'canmoderate' => $canmoderate,
                'comments' => $commentitems,
                'commentformaction' => (new \moodle_url('/local/mindscape_feed/comment.php', ['postid' => $post->id]))->out(false),
                'deleteformaction' => (new \moodle_url('/local/mindscape_feed/delete.php'))->out(false),
            ];
        }

        return [
            'canpost' => $canpost,
            'cancomment' => $cancomment,
            'posts' => $items,
            'postformaction' => (new \moodle_url('/local/mindscape_feed/post.php'))->out(false),
            'sesskey' => sesskey(),
        ];
    }
}