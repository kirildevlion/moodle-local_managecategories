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
 * Front page of the plugin
 *
 * @package    local_managecategories
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Asaf Davidovitch
 */

require_once(__DIR__ . '/../../config.php');

require_login();

global $DB;

$PAGE->set_url(new moodle_url('/local/managecategories/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('categoriesfile', 'local_managecategories'));
$PAGE->set_heading(get_string('categoriesfile', 'local_managecategories'));
$PAGE->navbar->add(get_string('categoriesfile', 'local_managecategories'), new moodle_url($CFG->wwwroot . '/local/managecategories/index.php'));

$time = (object)[
    'time' => time(),
    'heading' => get_string('importExportHeading', 'local_managecategories'),
    'importfile' => get_string('importfile', 'local_managecategories'),
    'exportfile' => get_string('exportfile', 'local_managecategories')
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_managecategories/index', $time);
echo $OUTPUT->footer();