<?php
/**
 * Version file for the Mindscape feed plugin.
 *
 * This file contains the version information for the plugin. Bump the
 * version whenever database changes are made or new features are added.
 *
 * @package    local_mindscape_feed
 */

defined('MOODLE_INTERNAL') || die();

// The component name (frankenstyle).
$plugin->component = 'local_mindscape_feed';
// Increment this number when you release a new version of the plugin. The
// format is YYYYMMDDXX where XX is a two‑digit sequence number within the
// day.
// Bumped after adding attachment support (file uploads).
$plugin->version   = 2025092804;
// Minimum Moodle version required (Moodle 4.4+; Moodle 5.0 accepts the same).
$plugin->requires  = 2024042200;
// Plugin maturity level.
$plugin->maturity  = MATURITY_ALPHA;
// Human readable release information.
$plugin->release   = '0.3.0';