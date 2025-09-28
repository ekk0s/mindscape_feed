<?php
require('../../config.php');
require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/mindscape_feed:moderate', $context);

$postid = required_param('postid', PARAM_INT);
global $DB;
if ($postid && $DB->record_exists('local_mindscape_posts', ['id'=>$postid])) {
    $DB->set_field('local_mindscape_posts', 'deleted', 1, ['id'=>$postid]);
    $DB->execute("UPDATE {local_mindscape_comments} SET deleted = 1 WHERE postid = ?", [$postid]);
}
redirect(new moodle_url('/local/mindscape_feed/index.php'));
