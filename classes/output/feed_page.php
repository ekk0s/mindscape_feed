<?php
namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

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
                $attachments[] = [
                    'url' => $fileurl->out(false),
                    'filename' => $file->get_filename(),
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
                    // ✅ Adiciona sesskey na URL de editar comentário (GET).
                    'commentediturl' => (new \moodle_url(
                        '/local/mindscape_feed/editcomment.php',
                        ['id' => $comment->id, 'sesskey' => sesskey()] // <—
                    ))->out(false),
                ];
            }

            // Likes.
            $likescount = $DB->count_records('local_mindscape_likes', ['postid' => $post->id]);
            $likedbyuser = $DB->record_exists('local_mindscape_likes', ['postid' => $post->id, 'userid' => $USER->id]);

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
                'comments' => $commentitems,

                // ✅ Adiciona sesskey na URL de editar post (GET).
                'editurl' => (new \moodle_url(
                    '/local/mindscape_feed/editpost.php',
                    ['id' => $post->id, 'sesskey' => sesskey()] // <—
                ))->out(false),

                // Forms (POST). O hidden input com {{sesskey}} já cobre, mas pode deixar a query também.
                'commentformaction' => (new \moodle_url(
                    '/local/mindscape_feed/comment.php',
                    ['postid' => $post->id] // POST leva <input name="sesskey" ...>
                ))->out(false),

                // Se seu delete usa POST com hidden sesskey, pode deixar sem na query.
                // Se usa GET, inclua a sesskey aqui também:
                'deleteformaction' => (new \moodle_url(
                    '/local/mindscape_feed/delete.php',
                    ['sesskey' => sesskey()] // opcional, conforme sua implementação
                ))->out(false),
            ];
        }

        return [
            'canpost' => $canpost,
            'cancomment' => $cancomment,
            'canmoderate' => $canmoderate,
            'posts' => $items,
            'postformaction' => (new \moodle_url('/local/mindscape_feed/post.php'))->out(false),
            'sesskey' => sesskey(), // usado como <input type="hidden" name="sesskey" ...> nos forms
            // URL to the weekly debates page. This allows the template to render
            // a button linking directly to the debates listing without requiring users
            // to manually type the URL. It is always included regardless of
            // capabilities, so any logged-in user can navigate there.
            'debatesurl' => (new \moodle_url('/local/mindscape_feed/debates.php'))->out(false),
        ];
    }
}
