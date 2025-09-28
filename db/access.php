<?php
/**
 * Capability definitions for the Mindscape feed plugin.
 *
 * These capabilities define who can view, post, comment on and moderate
 * the feed. See MDL-40443 for more information about capability arrays.
 *
 * @package    local_mindscape_feed
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/mindscape_feed:view' => [
        'riskbitmask' => 0,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'guest' => CAP_PROHIBIT,
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/mindscape_feed:post' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/mindscape_feed:comment' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/mindscape_feed:moderate' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];