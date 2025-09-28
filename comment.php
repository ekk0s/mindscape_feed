<?php
require('../../config.php');
require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/mindscape_feed:comment', $context);

$postid  = required_param('postid', PARAM_INT);
$content = optional_param('content', '', PARAM_RAW);

if ($postid && trim($content) !== '') {
    global $DB, $USER;
    if ($DB->record_exists('local_mindscape_posts', ['id'=>$postid, 'deleted'=>0])) {
        $rec = (object)[
            'postid' => $postid,
            'userid' => $USER->id,
            'content' => clean_text($content, FORMAT_HTML),
            'timecreated' => time(),
            'deleted' => 0
        ];
        $DB->insert_record('local_mindscape_comments', $rec);
    }
}
redirect(new moodle_url('/local/mindscape_feed/index.php', ['#' => 'p'.$postid]));
