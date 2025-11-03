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

/**
 * Upgrade script for the Mindscape Feed plugin.
 *
 * This function is executed when the plugin version number is bumped in
 * version.php.  It performs incremental database schema changes to
 * ensure that new tables or fields are created when upgrading from
 * previous versions.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool success
 */
function xmldb_local_mindscape_feed_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Add the dislikes table in version 2025110300.  Prior to this
    // release, only likes were stored.  The new table mirrors the
    // structure of local_mindscape_likes but stores dislikes instead.
    if ($oldversion < 2025110300) {
        // Define table local_mindscape_dislikes to be created.
        $table = new xmldb_table('local_mindscape_dislikes');

        // Adding fields to table local_mindscape_dislikes.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('postid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Adding keys to table local_mindscape_dislikes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('post_user_unique', XMLDB_KEY_UNIQUE, ['postid', 'userid']);

        // Conditionally launch create table if it does not exist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Mindscape Feed savepoint reached.
        upgrade_plugin_savepoint(true, 2025110300, 'local', 'mindscape_feed');
    }

    return true;
}