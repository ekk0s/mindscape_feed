<?php
namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Renderable class for the main feed page.  This version adds user navigation
 * data and additional links (home, profile, debates) to support a side
 * navigation bar similar to Facebook.  It also optionally includes a link
 * for administrators to add new debates.
 */
class feed_page implements renderable, templatable {
    /** @var \context_system */
    protected $context;

    public function __construct() {
        $this->context = \context_system::instance();
    }

    public function export_for_template(renderer_base $output): array {
        global $DB, $USER;

        $systemcontext = $this->context;
        $canpost      = has_capability('local/mindscape_feed:post', $systemcontext);
        $cancomment   = has_capability('local/mindscape_feed:comment', $systemcontext);
        $canmoderate  = has_capability('local/mindscape_feed:moderate', $systemcontext);

        $perpage = (int) get_config('local_mindscape_feed', 'itemsperpage');
        if ($perpage <= 0) {
            $perpage = 20;
        }

        $posts = $DB->get_records_select('local_mindscape_posts', 'deleted = 0', [], 'timecreated DESC', '*', 0, $perpage);

        $items = [];
        $fs = get_file_storage();
        foreach ($posts as $post) {
            $postuser = \core_user::get_user($post->userid);

            // Attachments.
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
            // Determine if the attachment is an image.  Some Moodle file types may not
            // return a standard image MIME type, so fall back to the file extension.
            $filename = $file->get_filename();
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isimage = preg_match('/^image\//', $file->get_mimetype()) || in_array($extension, ['jpg','jpeg','png','gif','bmp','webp']);
            $attachments[] = [
                'url' => $fileurl->out(false),
                'filename' => $filename,
                'isimage' => $isimage,
            ];
            }

            // Comments.
            $comments = $DB->get_records_select('local_mindscape_comments', 'deleted = 0 AND postid = ?', [$post->id], 'timecreated ASC');
            $commentitems = [];
            foreach ($comments as $comment) {
                $commentuser = \core_user::get_user($comment->userid);
                $iscommentowner = ($comment->userid == $USER->id);

                $commentitems[] = [
                    'userpic' => $output->user_picture($commentuser, ['size' => 25]),
                    'fullname' => fullname($commentuser),
                    'content' => format_text($comment->content, FORMAT_HTML, ['context' => $systemcontext, 'filter' => true]),
                    'time' => userdate($comment->timecreated),
                    'iscommentowner' => $iscommentowner,
                    'commentediturl' => (new \moodle_url(
                        '/local/mindscape_feed/editcomment.php',
                        ['id' => $comment->id, 'sesskey' => sesskey()]
                    ))->out(false),
                ];
            }

            // Likes.  Count the number of likes and whether the current user has liked this post.
            $likescount = $DB->count_records('local_mindscape_likes', ['postid' => $post->id]);
            $likedbyuser = $DB->record_exists('local_mindscape_likes', ['postid' => $post->id, 'userid' => $USER->id]);
            // Dislikes.  Use separate table for dislikes.
            // If the table does not exist (e.g., plugin not upgraded yet), counts will be zero.
            $dislikescount = 0;
            $dislikedbyuser = false;
            $dbman = $DB->get_manager();
            if ($dbman->table_exists('local_mindscape_dislikes')) {
                $dislikescount = $DB->count_records('local_mindscape_dislikes', ['postid' => $post->id]);
                $dislikedbyuser = $DB->record_exists('local_mindscape_dislikes', ['postid' => $post->id, 'userid' => $USER->id]);
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
                'likes' => [
                    'count' => $likescount,
                    'liked' => $likedbyuser,
                ],
                'dislikes' => [
                    'count' => $dislikescount,
                    'disliked' => $dislikedbyuser,
                ],
                'comments' => $commentitems,
                'editurl' => (new \moodle_url(
                    '/local/mindscape_feed/editpost.php',
                    ['id' => $post->id, 'sesskey' => sesskey()]
                ))->out(false),
                'commentformaction' => (new \moodle_url(
                    '/local/mindscape_feed/comment.php',
                    ['postid' => $post->id]
                ))->out(false),
                'deleteformaction' => (new \moodle_url(
                    '/local/mindscape_feed/delete.php',
                    ['sesskey' => sesskey()]
                ))->out(false),
                // Form actions for likes and dislikes.  Include the base URL only; returnurl is added by the script.
                'likeformaction' => (new \moodle_url('/local/mindscape_feed/like.php'))->out(false),
                'dislikeformaction' => (new \moodle_url('/local/mindscape_feed/dislike.php'))->out(false),
            ];
        }

        // User navigation information: picture, name and profile link.
        $userpic   = $output->user_picture($USER, ['size' => 50]);
        $fullname  = fullname($USER);
        $profileurl = (new \moodle_url('/local/mindscape_feed/profile.php', ['id' => $USER->id]))->out(false);
        // Build navigation links. Always include home, profile and debates. If user can moderate,
        // include an admin link to add new debates.
        $navlinks = [
            ['label' => get_string('navhome', 'local_mindscape_feed'), 'url' => (new \moodle_url('/local/mindscape_feed/index.php'))->out(false)],
            ['label' => get_string('navprofile', 'local_mindscape_feed'), 'url' => $profileurl],
            ['label' => get_string('navdebates', 'local_mindscape_feed'), 'url' => (new \moodle_url('/local/mindscape_feed/debates.php'))->out(false)],
        ];
        if ($canmoderate) {
            $navlinks[] = ['label' => get_string('navadddebate', 'local_mindscape_feed'), 'url' => (new \moodle_url('/local/mindscape_feed/adddebate.php'))->out(false)];
        }

        return [
            'canpost' => $canpost,
            'cancomment' => $cancomment,
            'canmoderate' => $canmoderate,
            'posts' => $items,
            'postformaction' => (new \moodle_url('/local/mindscape_feed/post.php'))->out(false),
            'sesskey' => sesskey(),
            'debatesurl' => (new \moodle_url('/local/mindscape_feed/debates.php'))->out(false),
            // New: user navigation block.  Mustache template will use these keys to render the sidebar.
            'usernav' => [
                'userpic' => $userpic,
                'fullname' => $fullname,
                'profileurl' => $profileurl,
                'links' => $navlinks,
            ],
        ];
    }
}