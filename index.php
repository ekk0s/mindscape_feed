<?php
require('../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/mindscape_feed:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mindscape_feed/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_mindscape_feed'));
$PAGE->set_heading(get_string('pluginname', 'local_mindscape_feed'));

$renderable = new \local_mindscape_feed\output\feed_page();
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_mindscape_feed/feed', $renderable->export_for_template($OUTPUT));
echo $OUTPUT->footer();
