<?php
/**
 * Serve plugin files for local_mindscape_feed.
 *
 * This file defines a file serving callback for the feed plugin.  It allows
 * attachments uploaded to feed posts to be delivered to users via the
 * pluginfile.php endpoint.  Only files stored in the system context and
 * within the 'attachment' filearea are served.  All other file areas or
 * context levels are rejected.
 *
 * @package   local_mindscape_feed
 */

defined('MOODLE_INTERNAL') || die();

/**
 * File serving callback for the mindscape feed plugin.
 *
 * The Moodle core will call this function when a URL such as
 * `/pluginfile.php/1/local_mindscape_feed/attachment/5/filename.ext` is
 * requested.  This function validates the request and uses the file API
 * to locate and serve the file.  See {@link send_stored_file()} for more
 * details on the optional parameters.
 *
 * @param stdClass $course        The course object (unused, always system).
 * @param stdClass $cm            The course module object (unused).
 * @param context  $context       The context of the file (must be system).
 * @param string   $filearea      The name of the filearea (should be 'attachment').
 * @param array    $args          Arguments passed to identify the file: itemid, filepath, filename.
 * @param bool     $forcedownload If true then file download is forced, otherwise may be displayed in browser.
 * @param array    $options       Additional options affecting file serving.
 * @return void|false             Sends the file or returns false if the request is invalid.
 */
function local_mindscape_feed_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    // Only allow system context. No course or module contexts are used.
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    // We only handle the 'attachment' file area.
    if ($filearea !== 'attachment') {
        return false;
    }

    // The first element is the itemid (post id).  Without it we cannot locate the file.
    $itemid = array_shift($args);
    if (empty($itemid)) {
        return false;
    }

    // The remaining args build the filepath and filename.  The last element is the filename.
    $filename = array_pop($args);
    // If any intermediate path segments exist, join them to form the filepath.  Otherwise use root '/'.
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_mindscape_feed', 'attachment', $itemid, $filepath, $filename);
    // Ensure the file exists and is not a directory.
    if (!$file || $file->is_directory()) {
        return false;
    }

    // Everything is ok, send the stored file to the browser.  Note that
    // send_stored_file() will handle permission checks for us.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}