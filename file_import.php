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
 * Page for picking a categories csv file and uploading it
 *
 * @package    local_managecategories
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Asaf Davidovitch
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot . '/local/managecategories/classes/form/file_import.php');
require_once($CFG->dirroot . '/local/managecategories/classes/parse_to_table.php');


require_login();

global $DB;

$PAGE->set_url(new moodle_url('/local/managecategories/file_import.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('importcategories', 'local_managecategories'));
$PAGE->set_heading(get_string('importcategories', 'local_managecategories'));
$PAGE->navbar->add(get_string('categoriesfile', 'local_managecategories'), new moodle_url($CFG->wwwroot . '/local/managecategories/index.php'));
$PAGE->navbar->add(get_string('importcategories', 'local_managecategories'), new moodle_url($CFG->wwwroot . '/local/managecategories/file_import.php'));


if(is_siteadmin()){
    $mform1 = new local_file_import();
    if ($mform1->is_cancelled()) {
        redirect($CFG->wwwroot . '/local/managecategories/index.php', 'File uploaded was cancelled', null, \core\output\notification::NOTIFY_ERROR);
    }else if ($formdata = $mform1->get_data()) {
        $cir = new local_categories_csv_import($iid, 'categoriesAndCourses');
        $content = $mform1->get_file_content('categoriesfile');    

        $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);

        $csvloaderror = $cir->get_error();
        unset($content);
        if (!is_null($csvloaderror)) {
            print_error('csvloaderror', '', $returnurl, $csvloaderror);
        }
        redirect($CFG->wwwroot . '/local/managecategories/index.php', 'File uploaded successfuly');

    } else {
        echo $OUTPUT->header();

        echo $OUTPUT->heading_with_help('uploadusers', 'uploadusers', 'tool_uploaduser');

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
}
else{
    redirect($CFG->wwwroot . '/local/managecategories/index.php', 'Only admin can do this action', null, \core\output\notification::NOTIFY_ERROR);
}

echo $OUTPUT->header();

echo $OUTPUT->footer();