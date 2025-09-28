<?php
defined('MOODLE_INTERNAL') || die();

if ($h = new admin_category('local_mindscape_feed_cat', get_string('pluginname', 'local_mindscape_feed'))) {
    $settings = new admin_settingpage('local_mindscape_feed', get_string('settings', 'local_mindscape_feed'));
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext('local_mindscape_feed/itemsperpage',
            get_string('itemsperpage', 'local_mindscape_feed'), '', 20, PARAM_INT));
    }
    $ADMIN->add('localplugins', $settings);
}
