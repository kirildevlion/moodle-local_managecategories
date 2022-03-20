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
 * Script for exporting the categories info from the DB into a csv file
 *
 * @package    local_managecategories
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Asaf Davidovitch
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

global $DB;

if(is_siteadmin()){
    // get all categories records
    $query = "SELECT id, name, description, parent FROM mdl_course_categories;";
    $categoriesrecords = $DB->get_records_sql($query);
    
    // insert the data we want to the table with headers -> categoryname, description, categoryparent
    for($i = 0; $i < sizeof($categoriesrecords); $i++) {
        $categoriesrecords[$i]->description = strip_tags($categoriesrecords[$i]->description);  // remove html tags
        $categoriesrecords[$i] = (array)$categoriesrecords[$i];
        $index = $categoriesrecords[$i][parent];
        $categoriesrecords[$i][parent] = $categoriesrecords[$index][name];
    }

    $finalcategoryarray = ['categoryname', 'description', 'parentname'];
    array_unshift($categoriesrecords, $finalcategoryarray); //insert the table headers to the begginnig of the array of categories
    array_walk( $categoriesrecords, function(&$cr){unset($cr['id']);});

    $filename = "moodleCategories.csv";
    $tmpfilepath = make_request_directory() . DIRECTORY_SEPARATOR . $filename;
    $fp = fopen($tmpfilepath, 'w');
    if (!$fp) {
        throw new \moodle_exception('errorCannotCreateCategoriesFile', 'create_file', '', $tmpfilepath);
    }

    // Loop through file pointer and a line
    foreach ($categoriesrecords as $fields) {
        fputcsv($fp, $fields);
    }

    send_file($tmpfilepath, $filename, null, 0, false, true, '', false);
    fclose($fp);
}
else{
    redirect($CFG->wwwroot . '/local/managecategories/index.php', 'Only admin can do this action', null, \core\output\notification::NOTIFY_ERROR);
}

redirect($CFG->wwwroot . '/local/managecategories/index.php');