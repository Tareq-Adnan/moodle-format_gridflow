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
 * GridFlow Course Format - Admin settings.
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Constants may not be loaded yet when settings.php is included independently.
defined('GRIDFLOW_LAYOUT_GRID')      || define('GRIDFLOW_LAYOUT_GRID',      0);
defined('GRIDFLOW_LAYOUT_ACCORDION') || define('GRIDFLOW_LAYOUT_ACCORDION', 1);
defined('GRIDFLOW_LAYOUT_TABS')      || define('GRIDFLOW_LAYOUT_TABS',      2);
defined('GRIDFLOW_LAYOUT_TIMELINE')  || define('GRIDFLOW_LAYOUT_TIMELINE',  3);

if ($ADMIN->fulltree) {

    // Default layout.
    $settings->add(new admin_setting_configselect(
        'format_gridflow/defaultlayout',
        get_string('defaultlayout', 'format_gridflow'),
        get_string('defaultlayout_desc', 'format_gridflow'),
        GRIDFLOW_LAYOUT_GRID,
        [
            GRIDFLOW_LAYOUT_GRID      => get_string('gridflow_layout_grid', 'format_gridflow'),
            GRIDFLOW_LAYOUT_ACCORDION => get_string('gridflow_layout_accordion', 'format_gridflow'),
            GRIDFLOW_LAYOUT_TABS      => get_string('gridflow_layout_tabs', 'format_gridflow'),
            GRIDFLOW_LAYOUT_TIMELINE  => get_string('gridflow_layout_timeline', 'format_gridflow'),
        ]
    ));

    // Default grid columns.
    $settings->add(new admin_setting_configselect(
        'format_gridflow/defaultgridcolumns',
        get_string('defaultgridcolumns', 'format_gridflow'),
        get_string('defaultgridcolumns_desc', 'format_gridflow'),
        3,
        [
            2 => '2',
            3 => '3',
            4 => '4',
        ]
    ));

    // Default summary truncate length.
    $settings->add(new admin_setting_configtext(
        'format_gridflow/defaultsummarytruncatelength',
        get_string('defaultsummarytruncatelength', 'format_gridflow'),
        get_string('defaultsummarytruncatelength_desc', 'format_gridflow'),
        120,
        PARAM_INT
    ));

    // Hide general section when empty — site default.
    $settings->add(new admin_setting_configselect(
        'format_gridflow/hidegeneralsectionwhenempty',
        get_string('hidegeneralsectionwhenempty', 'format_gridflow'),
        get_string('hidegeneralsectionwhenempty_help', 'format_gridflow'),
        0,
        [
            0 => get_string('show'),
            1 => get_string('hide'),
        ]
    ));

    // Show teacher info default.
    $settings->add(new admin_setting_configselect(
        'format_gridflow/defaultshowteacherinfo',
        get_string('defaultshowteacherinfo', 'format_gridflow'),
        get_string('defaultshowteacherinfo_desc', 'format_gridflow'),
        1,
        [
            1 => get_string('yes'),
            0 => get_string('no'),
        ]
    ));

    // Show progress bar default.
    $settings->add(new admin_setting_configselect(
        'format_gridflow/defaultshowprogressbar',
        get_string('defaultshowprogressbar', 'format_gridflow'),
        get_string('defaultshowprogressbar_desc', 'format_gridflow'),
        1,
        [
            1 => get_string('yes'),
            0 => get_string('no'),
        ]
    ));
}
