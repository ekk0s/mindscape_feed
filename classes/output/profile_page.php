<?php
namespace local_mindscape_feed\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Renderable for the user profile page.  Displays the selected user's
 * information and a list of their posts in the Mindscape Feed.  This
 * version does not implement a full friends system but includes
 * placeholders for future development.
 */
class profile_page implements renderable, templatable {
    /** @var int User ID to display */
    protected $userid;

    /**
     * Constructor.
     *
     * @param int $userid The ID of the user whose profile should be displayed
     */
    public function __construct(int $userid) {
        $this->userid = $userid;
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB, $USER;

        // Fetch the user record.  Use the Moodle user API to ensure proper formatting.
        $user = \core_user::get_user($this->userid, '*', MUST_EXIST);

        $systemcontext = \context_system::instance();
        $fs = get_file_storage();

        // Determine if the viewer is looking at their own profile.
        $isowner = ($USER->id == $this->userid);

        // Build user information.  Include the raw user id for use in friend actions.
        $userinfo = [
            'id' => $user->id,
            'fullname' => fullname($user),
            'userpic' => $output->user_picture($user, ['size' => 75]),
            'isowner' => $isowner,
        ];

        // Prepare a placeholder for the current viewer's relationship to this profile.
        $relationship = [
            'isfriend' => false,
            'requestsent' => false,
            'incoming' => false,
            'relationshipid' => 0,
        ];

        // Prepare arrays for the user's friends and incoming requests (if viewer is owner).
        $friends = [];
        $friendrequests = [];

        // Only attempt to read friend data if the friends table exists.
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('local_mindscape_friends')) {
            // Build list of accepted friends for the profile owner.
            $records = $DB->get_records_select('local_mindscape_friends',
                '(userid = ? OR friendid = ?) AND status = ?',
                [$this->userid, $this->userid, 'accepted']);
            foreach ($records as $rel) {
                // Determine the other party in the relationship.
                $friendid = ($rel->userid == $this->userid) ? $rel->friendid : $rel->userid;
                // Skip if somehow the friend id equals the current profile (prevent self-friend duplicates).
                if ($friendid == $this->userid) {
                    continue;
                }
                $frienduser = \core_user::get_user($friendid, '*', MUST_EXIST);
                $friends[] = [
                    'fullname' => fullname($frienduser),
                    'userpic' => $output->user_picture($frienduser, ['size' => 35]),
                    'profileurl' => (new \moodle_url('/local/mindscape_feed/profile.php', ['id' => $friendid]))->out(false),
                ];
            }

            // If the viewer is the owner, gather incoming friend requests (pending where friendid = $USER->id).
            if ($isowner) {
                $requests = $DB->get_records_select('local_mindscape_friends',
                    'friendid = ? AND status = ?',
                    [$USER->id, 'pending']);
                foreach ($requests as $req) {
                    $requester = \core_user::get_user($req->userid);
                    $friendrequests[] = [
                        'requestid' => $req->id,
                        'fullname' => fullname($requester),
                        'userpic' => $output->user_picture($requester, ['size' => 35]),
                        'profileurl' => (new \moodle_url('/local/mindscape_feed/profile.php', ['id' => $req->userid]))->out(false),
                    ];
                }
            }

            // Determine the relationship between the viewer and the profile owner when they are different users.
            if (!$isowner) {
                $rel = $DB->get_record_sql(
                    'SELECT * FROM {local_mindscape_friends} WHERE (userid = ? AND friendid = ?) OR (userid = ? AND friendid = ?)',
                    [$USER->id, $this->userid, $this->userid, $USER->id]
                );
                if ($rel) {
                    if ($rel->status === 'accepted') {
                        $relationship['isfriend'] = true;
                    } elseif ($rel->status === 'pending') {
                        if ($rel->userid == $USER->id) {
                            // Current user sent the request; waiting for acceptance.
                            $relationship['requestsent'] = true;
                        } else {
                            // Current user received the request; can accept.
                            $relationship['incoming'] = true;
                            $relationship['relationshipid'] = $rel->id;
                        }
                    }
                }
            }
        }

        // Fetch the user's posts for display.  Limit to recent posts for performance.
        $posts = $DB->get_records_select('local_mindscape_posts', 'deleted = 0 AND userid = ?', [$this->userid], 'timecreated DESC', '*', 0, 20);
        $postitems = [];
        foreach ($posts as $post) {
            $files = $fs->get_area_files($systemcontext->id, 'local_mindscape_feed', 'attachment', $post->id, 'id', false);
            $attachments = [];
            foreach ($files as $file) {
                $fileurl = \moodle_url::make_pluginfile_url(
                    $systemcontext->id,
                    'local_mindscape_feed',
                    'attachment',
                    $post->id,
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $filename = $file->get_filename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $isimage = preg_match('/^image\//', $file->get_mimetype()) || in_array($extension, ['jpg','jpeg','png','gif','bmp','webp']);
                $attachments[] = [
                    'url' => $fileurl->out(false),
                    'filename' => $filename,
                    'isimage' => $isimage,
                ];
            }
            // Likes.
            $likescount = $DB->count_records('local_mindscape_likes', ['postid' => $post->id]);
            $likedbyuser = $DB->record_exists('local_mindscape_likes', ['postid' => $post->id, 'userid' => $USER->id]);
            // Dislikes (optional).  Check if the dislikes table exists.
            $dislikescount = 0;
            $dislikedbyuser = false;
            $dbman = $DB->get_manager();
            if ($dbman->table_exists('local_mindscape_dislikes')) {
                $dislikescount = $DB->count_records('local_mindscape_dislikes', ['postid' => $post->id]);
                $dislikedbyuser = $DB->record_exists('local_mindscape_dislikes', ['postid' => $post->id, 'userid' => $USER->id]);
            }

            // Comments for each post (limit to 10 for performance).
            $comments = $DB->get_records_select('local_mindscape_comments', 'deleted = 0 AND postid = ?', [$post->id], 'timecreated ASC', '*', 0, 10);
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
            $postitems[] = [
                'id' => $post->id,
                'userpic' => $output->user_picture($user, ['size' => 35]),
                'fullname' => fullname($user),
                'content' => format_text($post->content, FORMAT_HTML, ['context' => $systemcontext, 'filter' => true]),
                'time' => userdate($post->timecreated),
                'attachments' => $attachments,
                'isowner' => $isowner,
                'canmoderate' => has_capability('local/mindscape_feed:moderate', $systemcontext),
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
                // Actions for like and dislike forms.  See like.php and dislike.php.
                'likeformaction' => (new \moodle_url('/local/mindscape_feed/like.php'))->out(false),
                'dislikeformaction' => (new \moodle_url('/local/mindscape_feed/dislike.php'))->out(false),
            ];
        }

        $cancomment = has_capability('local/mindscape_feed:comment', $systemcontext);
        $canmoderate = has_capability('local/mindscape_feed:moderate', $systemcontext);

        // Build additional URLs for friend actions.  Only present when the viewer is not the owner.
        $sendfriendurl = null;
        $cancelfriendurl = null;
        $removefriendurl = null;
        if (!$isowner) {
            $baseparams = ['id' => $this->userid];
            // Provide a URL to send a friend request if no relationship exists.
            if (!$relationship['isfriend'] && !$relationship['requestsent'] && !$relationship['incoming']) {
                $sendfriendurl = (new \moodle_url('/local/mindscape_feed/profile.php', $baseparams + ['friendaction' => 'send']))->out(false);
            }
            // Provide a URL to cancel a pending request if the viewer initiated it.
            if ($relationship['requestsent']) {
                $cancelfriendurl = (new \moodle_url('/local/mindscape_feed/profile.php', $baseparams + ['friendaction' => 'cancel']))->out(false);
            }
            // Provide a URL to remove an existing friendship.
            if ($relationship['isfriend']) {
                $removefriendurl = (new \moodle_url('/local/mindscape_feed/profile.php', $baseparams + ['friendaction' => 'remove']))->out(false);
            }
        }
        // Provide accept URLs on each friend request.
        foreach ($friendrequests as &$fr) {
            $fr['accepturl'] = (new \moodle_url('/local/mindscape_feed/profile.php', ['id' => $USER->id, 'friendaction' => 'accept', 'req' => $fr['requestid']]))->out(false);
        }

        return [
            'user' => $userinfo,
            'relationship' => $relationship,
            'friends' => $friends,
            'friendrequests' => $friendrequests,
            'posts' => $postitems,
            'sesskey' => sesskey(),
            'cancomment' => $cancomment,
            'canmoderate' => $canmoderate,
            'sendfriendurl' => $sendfriendurl,
            'cancelfriendurl' => $cancelfriendurl,
            'removefriendurl' => $removefriendurl,
        ];
    }
}