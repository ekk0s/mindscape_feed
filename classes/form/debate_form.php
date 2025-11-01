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
 * Form definition for creating a new weekly debate.
 *
 * This form is used on the manage_debates.php page to allow an administrator
 * or manager to specify the details of a new debate: title, description,
 * the starting date of the week, whether the debate is active, and an
 * optional Kialo course module identifier. The form uses the moodleform
 * class for automatic rendering and validation.
 *
 * @package    local_mindscape_feed
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mindscape_feed\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Class debate_form
 * @package local_mindscape_feed
 */
class debate_form extends \moodleform {

    /**
     * Defines the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // Debate title.
        $mform->addElement('text', 'title', get_string('title', 'local_mindscape_feed'), ['size' => 64]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required');

        // Description using the editor component.
        $mform->addElement('editor', 'description', get_string('description', 'local_mindscape_feed'));
        $mform->setType('description', PARAM_RAW);

        // Week starting date.
        $mform->addElement('date_selector', 'weekstart', get_string('weekstart', 'local_mindscape_feed'));
        $mform->setType('weekstart', PARAM_INT);
        $mform->addRule('weekstart', get_string('required'), 'required');

        // Active checkbox.
        $mform->addElement('advcheckbox', 'active', get_string('active', 'local_mindscape_feed'));
        $mform->setType('active', PARAM_BOOL);
        $mform->setDefault('active', 1);

        // Optional Kialo course module ID. When provided, the debate will link to
        // the corresponding Kialo activity. Use text input to allow manual entry.
        $mform->addElement('text', 'kialo_cmid', get_string('kialo_cmid', 'local_mindscape_feed'), ['size' => 10]);
        $mform->setType('kialo_cmid', PARAM_INT);
        $mform->addHelpButton('kialo_cmid', 'kialo_cmid', 'local_mindscape_feed');

        // Action buttons (Save changes and Cancel).
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}