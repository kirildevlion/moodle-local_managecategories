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
 * Block for displaying earned local badges to users
 *
 * @package    local_managecategories
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Asaf Davidovitch
 */


require_once(__DIR__ . '/../../../config.php');


class local_categories_csv_import {

    /**
     * @var int import identifier
     */
    private $_iid;

    /**
     * @var string which script imports?
     */
    private $_type;

    /**
     * @var string|null Null if ok, error msg otherwise
     */
    private $_error;

    /**
     * @var array cached columns
     */
    private $_columns;

    /**
     * @var object file handle used during import
     */
    private $_fp;

    /**
     * Contructor
     *
     * @param int $iid import identifier
     * @param string $type which script imports?
     */
    // public function __construct($iid, $type) {
    //     $this->_iid  = $iid;
    //     $this->_type = $type;
    // }

    /**
     * Parse the given content
     *
     * @param string $content the content to parse.
     * @param string $encoding content encoding
     * @param string $delimiter_name separator (comma, semicolon, colon, cfg)
     * @param string $column_validation name of function for columns validation, must have one param $columns
     * @param string $enclosure field wrapper. One character only.
     * @return bool false if error, count of data lines if ok; use get_error() to get error string
     */
    public function load_csv_content($content, $encoding, $delimiter_name, $enclosure='"') {
        global $USER, $CFG, $DB;

        //$this->close();
        $this->_error = null;

        $content = core_text::convert($content, $encoding, 'utf-8');
        // remove Unicode BOM from first line
        $content = core_text::trim_utf8_bom($content);
        // Fix mac/dos newlines
        $content = preg_replace('!\r\n?!', "\n", $content);
        // Remove any spaces or new lines at the end of the file.
        if ($delimiter_name == 'tab') {
            // trim() by default removes tabs from the end of content which is undesirable in a tab separated file.
            $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
        } else {
            $content = trim($content);
        }

        $csv_delimiter = csv_import_reader::get_delimiter($delimiter_name);

        // Create a temporary file and store the csv file there,
        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);

        // Create an array to store the imported data for error checking.
        $columns = array();
        while ($fgetdata = fgetcsv($fp, 0, $csv_delimiter, $enclosure)) {
            // Check to see if we have an empty line.
            if (count($fgetdata) == 1) {
                if ($fgetdata[0] !== null) {
                    // The element has data. Add it to the array.
                    $columns[] = $fgetdata;
                }
            } else {
                $columns[] = $fgetdata;
            }
        }

        $table = 'course_categories';
        for($i = 1; $i < sizeof($columns); $i++) {    
            if(!$DB->record_exists($table, array('name' => $columns[$i][0]))){

                $category_parent_name = $columns[$i][2];
                $parent_record = $DB->get_record($table, array('name' => $category_parent_name));
                
                $newrecord = new stdClass();
                $newrecord->name = $columns[$i][0];
                $newrecord->description  = strip_tags($columns[$i][1]);

                if($parent_record->id == null){
                    $newrecord->parent  = '0'; 
                }
                else $newrecord->parent  = $parent_record->id; 
                $DB->insert_record($table, $newrecord);
            }
        }
    }

    // /**
    //  * Get last error
    //  *
    //  * @return string error text of null if none
    //  */
    public function get_error() {
        return $this->_error;
    }
}