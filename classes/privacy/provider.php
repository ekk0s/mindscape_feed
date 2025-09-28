<?php
namespace local_mindscape_feed\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements
    \core_privacy\local\metadata\null_provider,
    \core_privacy\local\request\user_preference_provider {

    public static function get_reason(): string {
        return 'privacy:metadata';
    }

    public static function export_user_preferences(int $userid) {}
}
