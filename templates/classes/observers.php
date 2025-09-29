<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_mindscape_feed;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers for the Mindscape feed plugin.
 *
 * This class contains static methods that listen to events emitted by the
 * plugin. When a comment is created, it will notify the author of the post
 * via Moodle's messaging system.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Mindscape
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {
    /**
     * Notify the author of a post when a new comment is added.
     *
     * @param \local_mindscape_feed\event\comment_created $event Event object containing data about the comment.
     * @return bool true on success
     */
    public static function notify_post_author(\local_mindscape_feed\event\comment_created $event) {
        global $DB;
        // Retrieve event data.
        $postid = $event->other['postid'];
        $commentuserid = $event->userid;

        // Get the post record to identify the author and ensure it exists.
        $post = $DB->get_record('local_mindscape_posts', ['id' => $postid], 'id, userid, content');
        if (!$post) {
            return true;
        }
        // Do not notify the user if they commented on their own post.
        if ($post->userid == $commentuserid) {
            return true;
        }
        // Load user records.
        $fromuser = \core_user::get_user($commentuserid);
        $touser   = \core_user::get_user($post->userid);
        if (!$fromuser || !$touser) {
            return true;
        }
        // Build notification strings.
        $subject = get_string('commentnotificationsubject', 'local_mindscape_feed');
        // Use HTML and plain text versions for message.
        $url = (new \moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]))->out(false);
        $messagehtml = get_string('commentnotificationmessagehtml', 'local_mindscape_feed', [
            'commenter'   => fullname($fromuser),
            'postcontent' => $post->content,
            'url'         => $url,
        ]);
        $messagetext = get_string('commentnotificationmessage', 'local_mindscape_feed', [
            'commenter'   => fullname($fromuser),
            'postcontent' => $post->content,
            'url'         => $url,
        ]);
        // Build the message object.
        $msg = new \core\message\message();
        $msg->component         = 'local_mindscape_feed';
        $msg->name              = 'post_comment';
        $msg->userfrom          = $fromuser;
        $msg->userto            = $touser;
        $msg->subject           = $subject;
        $msg->fullmessage       = $messagetext;
        $msg->fullmessageformat = FORMAT_PLAIN;
        $msg->fullmessagehtml   = $messagehtml;
        $msg->smallmessage      = $subject;
        $msg->contexturl        = $url;
        $msg->contexturlname    = get_string('pluginname', 'local_mindscape_feed');
        // Send the message via the message API.
        message_send($msg);
        return true;
    }

    /**
     * Notify the author of a post when someone likes their post.
     *
     * @param \local_mindscape_feed\event\post_liked $event The event for a like action.
     * @return bool Always returns true
     */
    public static function notify_post_author_like(\local_mindscape_feed\event\post_liked $event) {
        global $DB;
        $postid = $event->other['postid'];
        $likerid = $event->userid;

        // Retrieve the post to find the author.
        $post = $DB->get_record('local_mindscape_posts', ['id' => $postid], 'id, userid, content');
        if (!$post) {
            return true;
        }
        // Do not notify if the user liked their own post.
        if ($post->userid == $likerid) {
            return true;
        }
        // Load user accounts.
        $fromuser = \core_user::get_user($likerid);
        $touser   = \core_user::get_user($post->userid);
        if (!$fromuser || !$touser) {
            return true;
        }
        // Build notification strings.
        $subject = get_string('postlikenotificationsubject', 'local_mindscape_feed');
        $url = (new \moodle_url('/local/mindscape_feed/index.php', ['#' => 'p' . $postid]))->out(false);
        $messagehtml = get_string('postlikenotificationmessagehtml', 'local_mindscape_feed', [
            'liker'       => fullname($fromuser),
            'postcontent' => $post->content,
            'url'         => $url,
        ]);
        $messagetext = get_string('postlikenotificationmessage', 'local_mindscape_feed', [
            'liker'       => fullname($fromuser),
            'postcontent' => $post->content,
            'url'         => $url,
        ]);
        $msg = new \core\message\message();
        $msg->component         = 'local_mindscape_feed';
        $msg->name              = 'post_like';
        $msg->userfrom          = $fromuser;
        $msg->userto            = $touser;
        $msg->subject           = $subject;
        $msg->fullmessage       = $messagetext;
        $msg->fullmessageformat = FORMAT_PLAIN;
        $msg->fullmessagehtml   = $messagehtml;
        $msg->smallmessage      = $subject;
        $msg->contexturl        = $url;
        $msg->contexturlname    = get_string('pluginname', 'local_mindscape_feed');
        message_send($msg);
        return true;
    }
}