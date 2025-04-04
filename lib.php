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
 * lib file.
 *
 * @package   local_geniai
 * @copyright 2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php'); // Ensure $CFG is defined
require_once($CFG->libdir . '/gradelib.php'); // Now $CFG->libdir works

use local_geniai\core_hook_output;

/**
 * Hook before footer rendering.
 *
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_geniai_before_footer() {
    core_hook_output::before_footer_html_generation();
}

/**
 * Creates or updates a grade item and assigns a grade to a user.
 *
 * @param int $courseid Course ID
 * @param int $userid User ID
 * @param float $gradeval Grade value (0 to 10)
 * @return void
 */
function local_geniai_grade_item_update($courseid, $userid, $gradeval) {
    $item = array(
        'itemname' => 'Active Listening',
        'itemtype' => 'manual',
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => 100,
        'grademin' => 10,
        'courseid' => $courseid,
    );

    grade_update('local_geniai', $courseid, 'user', 'local_geniai', $userid, 0, null, $item);

    $grades = array($userid => $gradeval);
    grade_update('local_geniai', $courseid, 'user', 'local_geniai', $userid, 0, $grades);
}
