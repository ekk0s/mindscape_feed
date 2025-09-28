<?php
/**
 * Admin settings for the Mindscape feed plugin.
 *
 * This file defines a single setting for the plugin: the number of items
 * displayed on the feed per page. Additional settings can be added as
 * needed.
 *
 * @package    local_mindscape_feed
 */

defined('MOODLE_INTERNAL') || die();

if ($h = new admin_category('local_mindscape_feed_cat', get_string('pluginname', 'local_mindscape_feed'))) {
    $settings = new admin_settingpage('local_mindscape_feed', get_string('settings', 'local_mindscape_feed'));
    if ($ADMIN->fulltree) {
        // Number of items to display per page on the feed.
        $settings->add(new admin_setting_configtext(
            'local_mindscape_feed/itemsperpage',
            get_string('itemsperpage', 'local_mindscape_feed'),
            '',
            20,
            PARAM_INT
        ));
    }
    $ADMIN->add('localplugins', $settings);
}