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

// Dislike actions.
$string['dislike'] = 'Dislike';
$string['undislike'] = 'Remove dislike';

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

// Button label used on the debates page when a Kialo activity is linked to a debate.
$string['participatedebate'] = 'Participate in debate';

// Navigation labels for the sidebar.  Used to build the left navigation menu.
$string['navhome'] = 'Home';
$string['navprofile'] = 'Profile';
$string['navdebates'] = 'Debates';
$string['navadddebate'] = 'Add debate';

// Additional strings for profile page.
$string['editbanner'] = 'Edit cover';
$string['editprofilepic'] = 'Edit profile picture';
$string['friends'] = 'Friends';
$string['friendspending'] = 'Friend requests and connections will appear here.';
$string['posts'] = 'Posts';

// Debate page strings.
$string['viewpost'] = 'View post';

// Strings for adding a debate.
$string['debate_title'] = 'Title';
$string['debate_description'] = 'Description';
$string['debate_weekstart'] = 'Week start (timestamp)';
$string['debate_postid'] = 'Associated post ID (optional)';
$string['debate_kialocmid'] = 'Kialo course module ID (optional)';
$string['debatecreated'] = 'Debate created successfully';
$string['err_title_required'] = 'A title is required for the debate.';
$string['err_weekstart_required'] = 'The week start date is required.';
$string['err_couldnotcreate'] = 'Could not create the debate. Please try again.';

// Error when Kialo course module id does not correspond to a Kialo activity.
$string['err_invalid_kialocmid'] = 'The specified Kialo module ID is invalid.';

// Label for the checkbox used on the add debate form to automatically create
// a Kialo activity in a hidden course instead of entering a course module ID.
$string['autocreatekialo'] = 'Automatically create a Kialo activity (recommended)';

// Error shown when the system fails to create a Kialo activity automatically.
$string['err_autocreate_failed'] = 'Automatic Kialo creation failed. Please ensure the Kialo plugin is installed or provide a module ID.';

// Error when the moderator attempts to auto-create a Kialo activity but the
// mod_kialo plugin is not installed on this Moodle site.
$string['err_kialonotinstalled'] = 'Automatic creation is unavailable because the Kialo activity module is not installed.';

// Informational message shown in the add debate form when the Kialo plugin is
// missing and automatic creation cannot be used.
$string['autocreateunavailable'] = 'Kialo plugin is not installed; automatic creation of Kialo activities is unavailable.';

// Friend system strings.
$string['friendrequests'] = 'Friend requests';
$string['addfriend'] = 'Add friend';
$string['pendingfriend'] = 'Pending';
$string['acceptfriend'] = 'Accept';
$string['alreadyfriends'] = 'You are friends';
$string['nofriends'] = 'No friends yet.';
$string['nofriendrequests'] = 'No pending friend requests.';
$string['friendrequestsent'] = 'Friend request sent.';
$string['friendrequestaccepted'] = 'Friend request accepted.';
$string['friendrequestcancelled'] = 'Friend request cancelled.';
$string['friendremoved'] = 'Friend removed.';
$string['friendrequesterror'] = 'Unable to process friend request.';

// Error messages used when handling likes/dislikes.
$string['invalidpostid'] = 'Invalid post ID.';
$string['invalidaction'] = 'Invalid action.';

// Labels for canceling and removing friendships.
$string['cancelrequest'] = 'Cancel request';
$string['removefriend'] = 'Remove friend';