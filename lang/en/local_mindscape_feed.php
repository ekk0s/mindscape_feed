<?php
// Strings for component 'local_mindscape_feed', language 'en'.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mindscape Feed';
$string['settings'] = 'Mindscape Feed settings';
$string['itemsperpage'] = 'Items per page';

// Capability descriptions.
$string['cap:view'] = 'View the feed';
$string['cap:post'] = 'Post in the feed';
$string['cap:comment'] = 'Comment on the feed';
$string['cap:moderate'] = 'Moderate the feed';

// UI strings used in templates.
$string['writepost'] = 'Write something…';
$string['publish'] = 'Publish';
$string['comment'] = 'Comment';
$string['nocomments'] = 'No comments yet.';
$string['delete'] = 'Delete';
$string['confirmdelete'] = 'Are you sure you want to delete?';
$string['nopostsyet'] = 'No posts found.';
$string['edit'] = 'Edit';
$string['save'] = 'Save';
$string['like'] = 'Like';
$string['unlike'] = 'Unlike';

// Tooltip for attachment button.
$string['attachfile'] = 'Attach file';

// Event and notification strings.
$string['eventcommentcreated'] = 'Comment created on Mindscape feed';
$string['commentnotificationsubject'] = 'New comment on your Mindscape feed post';
$string['commentnotificationmessage'] = '{$a->commenter} commented on your post: "{$a->postcontent}".\nView: {$a->url}';
$string['commentnotificationmessagehtml'] = '<p><strong>{$a->commenter}</strong> commented on your post:</p><blockquote>{$a->postcontent}</blockquote><p>View <a href="{$a->url}">here</a>.</p>';

$string['eventpostliked'] = 'Post liked in Mindscape feed';
$string['postlikenotificationsubject'] = 'Your post was liked in the Mindscape feed';
$string['postlikenotificationmessage'] = '{$a->liker} liked your post: "{$a->postcontent}".\nView: {$a->url}';
$string['postlikenotificationmessagehtml'] = '<p><strong>{$a->liker}</strong> liked your post:</p><blockquote>{$a->postcontent}</blockquote><p>View <a href="{$a->url}">here</a>.</p>';
$string['weeklydebates'] = 'Weekly debates';
$string['viewdiscussion'] = 'View discussion';
$string['nodebates'] = 'No active debates at the moment.';

// Navigation link to the weekly debates page.
$string['viewdebates'] = 'View weekly debates';
// Management page strings.
$string['managedebates'] = 'Manage weekly debates';
$string['existingdebates'] = 'Existing debates';
$string['adddebate'] = 'Add a new debate';
$string['debatecreated'] = 'Debate created successfully';

// Debate form field strings.
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['weekstart'] = 'Week start';
$string['active'] = 'Active';
$string['kialo_cmid'] = 'Kialo activity ID';
$string['kialo_cmid_help'] = 'Optional: enter the course module ID of an existing Kialo activity to link this debate to it.';

// Button label used on the debates page when a Kialo activity is linked to a debate.
$string['participatedebate'] = 'Participate in debate';

