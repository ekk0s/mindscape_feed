<?php
require('../../config.php');
require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/mindscape_feed:post', $context);

$content = optional_param('content', '', PARAM_RAW); // vamos sanitizar
if (trim($content) !== '') {
    global $DB, $USER;
    $rec = (object)[
        'userid' => $USER->id,
        'content' => clean_text($content, FORMAT_HTML),
        'timecreated' => time(),
        'timemodified' => time(),
        'deleted' => 0
    ];
    $DB->insert_record('local_mindscape_posts', $rec);
}
redirect(new moodle_url('/local/mindscape_feed/index.php'));
