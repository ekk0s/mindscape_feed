<?php
// This file specifies the version of the Mindscape Feed plugin.
// When you add new features or database tables you should bump the
// version number.  Moodle will detect the change and run any
// appropriate upgrade steps (including creating tables defined in
// install.xml).

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_mindscape_feed';
// Version format YYYYMMDDRR where RR is the release number on that day.
$plugin->version   = 20251199999;
$plugin->release   = '2025-11-03 (minimal feed & dislikes)';
// Require a relatively modern Moodle (3.4) for proper API support.
$plugin->requires  = 2017111300;
$plugin->maturity  = MATURITY_ALPHA;