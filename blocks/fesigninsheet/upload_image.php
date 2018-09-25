<?php
// This file is part of Moodle - http://moodle.org/
//
// Signinsheet is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Signinsheet is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
 
/**
 *
 * @package    block_fesigninsheet
 * @copyright  2013 Kyle Goslin, Daniel McSweeney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
global $CFG, $DB;
$formPath = "$CFG->libdir/formslib.php";
require_once($formPath);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('settings', 'block_fesigninsheet'), new moodle_url('../../admin/settings.php?section=blocksettingsigninsheet'));
$PAGE->navbar->add(get_string('uploadimage', 'block_fesigninsheet'));


$PAGE->set_url('/blocks/cmanager/course_new.php');
$PAGE->set_context(get_system_context());
$PAGE->set_heading(get_string('pluginname', 'block_fesigninsheet'));


 class signinsheet_uploader_form extends moodleform {
 
 
    function definition() {
    	$mform = $this->_form;
		$mform->addElement('filepicker', 'userfile', get_string('file'), null,
                   array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
		
	
     $this->add_action_buttons();
  //$this->set_data($currententry);
	}
	
 }
$mform = new signinsheet_uploader_form();

if ($mform->is_cancelled()) {
   redirect(new moodle_url('/admin/settings.php?section=blocksettingfesigninsheet'));
	
} else if ($fromform = $mform->get_data()) {
	
$success = $mform->save_file('userfile', '/fesigninsheet', true);
$storedfile = $mform->save_stored_file('userfile', 1, 'fesigninsheet', 'content', 0, '/', null, true);
// ---------------------------------------------------------------------------


// ----------------------------------------------------------------------------

} else {



 
}










echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();
?>
