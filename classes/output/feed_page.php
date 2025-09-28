<?php
namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

class feed_page implements renderable, templatable {
    public function export_for_template(renderer_base $output): array {
        global $CFG, $USER;

        $itemsperpage = (int) get_config('local_mindscape_feed', 'itemsperpage') ?: 20;

        $posts = self::get_posts($itemsperpage);
        $data = [
            'canpost' => has_capability('local/mindscape_feed:post', \context_system::instance()),
            'cancomment' => has_capability('local/mindscape_feed:comment', \context_system::instance()),
            'posts' => array_map(function($p) use ($output) {
                return [
                    'id' => $p->id,
                    'userpic' => $output->user_picture(\core_user::get_user($p->userid), ['size' => 35]),
                    'fullname' => fullname(\core_user::get_user($p->userid)),
                    'content' => format_text($p->content, FORMAT_HTML, ['filter' => true]),
                    'time' => userdate($p->timecreated),
                    'isowner' => ($p->userid == $GLOBALS['USER']->id),
                    'canmoderate' => has_capability('local/mindscape_feed:moderate', \context_system::instance()),
                    'comments' => self::get_comments_for_template($p->id, $output),
                    'commentformaction' => new \moodle_url('/local/mindscape_feed/comment.php', ['postid' => $p->id]),
                ];
            }, $posts),
            'postformaction' => new \moodle_url('/local/mindscape_feed/post.php'),
        ];
        return $data;
    }

    private static function get_posts(int $limit): array {
        global $DB;
        return $DB->get_records_select('local_mindscape_posts', 'deleted = 0', [], 'timecreated DESC', '*', 0, $limit);
    }

    private static function get_comments_for_template(int $postid, \renderer_base $output): array {
        global $DB;
        $comments = $DB->get_records_select('local_mindscape_comments', 'deleted = 0 AND postid = ?', [$postid], 'timecreated ASC');
        if (!$comments) { return []; }
        $out = [];
        foreach ($comments as $c) {
            $u = \core_user::get_user($c->userid);
            $out[] = [
                'userpic' => $output->user_picture($u, ['size' => 25]),
                'fullname' => fullname($u),
                'content' => format_text($c->content, FORMAT_HTML, ['filter' => true]),
                'time' => userdate($c->timecreated),
            ];
        }
        return $out;
    }
}
