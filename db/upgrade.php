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
 * Upgrade steps for the Mindscape Feed plugin.
 *
 * This file defines the incremental steps that need to be executed
 * when upgrading from earlier versions of the plugin. Each step
 * checks the current installed version ($oldversion) and performs
 * database changes where necessary. This ensures that installations
 * that started on an older version can be safely upgraded when new
 * database tables or fields are introduced.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute all the upgrade steps for the Mindscape Feed plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Returns true on success.
 */
function xmldb_local_mindscape_feed_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    // During earlier releases (prior to 2025092805), the likes table did not exist.
    // Ensure it is created on upgrade. We also add a unique index to prevent
    // duplicate likes by the same user on the same post.
    if ($oldversion < 2025092805) {
        // Define table local_mindscape_likes to be created.
        $table = new xmldb_table('local_mindscape_likes');

        // Define fields for the table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('postid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define keys. Primary key and foreign keys for post and user.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('postid_fk', XMLDB_KEY_FOREIGN, ['postid'], 'local_mindscape_posts', ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Define unique index to prevent duplicate likes.
        $table->add_index('unique_like', XMLDB_INDEX_UNIQUE, ['postid', 'userid']);

        // Conditionally create the table if it does not exist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2025092812) {
        // Define table local_mindscape_debates to be created.
        $table = new xmldb_table('local_mindscape_debates');

        // Define fields for the debates table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('postid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('weekstart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define keys for the debates table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Create the table if it does not already exist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2025092812, 'local', 'mindscape_feed');
    }

    return true;
}
