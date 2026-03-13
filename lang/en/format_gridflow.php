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
 * GridFlow Course Format - Language strings (English).
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin name.
$string['pluginname'] = 'GridFlow Course Formats';

// Section names.
$string['sectionname']     = 'Section';
$string['sectionnamecaps'] = 'SECTION';
$string['section0name']    = 'General';
$string['addsections']     = 'Add section';
$string['newsectionname']  = 'New name for section {$a}';
$string['editsectionname'] = 'Edit section name';
$string['currentsection']  = 'This section';
$string['hidefromothers']  = 'Hide section';
$string['showfromothers']  = 'Show section';

// Layout selector.
$string['gridflowlayout']          = 'Course layout';
$string['gridflowlayout_help']     = 'Choose how sections and activities are displayed in this course.';
$string['gridflow_layout_grid']      = 'Grid / Card';
$string['gridflow_layout_accordion'] = 'Accordion / List';
$string['gridflow_layout_tabs']      = 'Tabbed';
$string['gridflow_layout_timeline']  = 'Timeline / Progress';

// Admin site-level defaults.
$string['defaultlayout']                    = 'Default course layout';
$string['defaultlayout_desc']               = 'The layout used for new courses.';
$string['defaultgridcolumns']               = 'Default grid columns';
$string['defaultgridcolumns_desc']          = 'Number of columns shown in grid layout.';
$string['defaultsummarytruncatelength']     = 'Default summary truncate length';
$string['defaultsummarytruncatelength_desc']= 'Maximum characters shown in section summary cards.';
$string['defaultshowteacherinfo']           = 'Show teacher info by default';
$string['defaultshowteacherinfo_desc']      = 'Display teacher name and avatar in the course header.';
$string['defaultshowprogressbar']           = 'Show progress bar by default';
$string['defaultshowprogressbar_desc']      = 'Display overall course completion progress in the header.';

// Course-level settings.
$string['gridcolumns']              = 'Grid columns';
$string['gridcolumns_help']         = 'Number of columns in the grid layout.';
$string['columns']                  = 'columns';
$string['showprogressbar']          = 'Show progress bar';
$string['showprogressbar_help']     = 'Display a completion progress bar at the top of the course.';
$string['showteacherinfo']          = 'Show teacher information';
$string['showteacherinfo_help']     = 'Display teacher name and picture in the course header.';
$string['sectioncardstyle']         = 'Card style';
$string['sectioncardstyle_help']    = 'Visual style applied to section cards.';
$string['cardstyle_default']        = 'Default (shadow + border)';
$string['cardstyle_flat']           = 'Flat (subtle border)';
$string['cardstyle_minimal']        = 'Minimal (no border)';
$string['defaultsectionexpanded']   = 'Default section view';
$string['defaultsectionexpanded_help'] = 'Whether sections start expanded or collapsed (accordion layout).';
$string['expanded']                 = 'Expand all';
$string['collapsed']                = 'Collapse all';
$string['summarytruncatelength']    = 'Summary max length';
$string['summarytruncatelength_help'] = 'Maximum characters shown in section/activity summary previews.';
$string['hidegeneralsectionwhenempty']      = 'Hide general section when empty';
$string['hidegeneralsectionwhenempty_help'] = 'Hides section 0 (General) when it has no activities and no summary.';

// Activity strings.
$string['viewactivity']     = 'View activity';
$string['viewtopic']        = 'View section';
$string['progress']         = 'Progress';
$string['completed']        = 'Completed';
$string['notcompleted']     = 'Not completed';
$string['notattempted']     = 'Not attempted';
$string['markcomplete']     = 'Mark complete';
$string['resumetoactivity'] = 'Resume';
$string['outof']            = 'out of';
$string['activitiescompleted']    = 'activities completed';
$string['activitycompleted']      = 'activity completed';
$string['activitiesremaining']    = 'activities remaining';
$string['activityremaining']      = 'activity remaining';
$string['allactivitiescompleted'] = 'All activities completed!';
$string['activitystart']          = "Let's start";
$string['courseprogresstitle']    = 'Course progress';
$string['grade']                  = 'Grade';

// Navigation.
$string['next']     = 'Next';
$string['previous'] = 'Previous';

// UI strings used in JS.
$string['showfullsummary'] = '+ Show full summary';
$string['showless']        = 'Show less';
$string['expandall']       = 'Expand all';
$string['collapseall']     = 'Collapse all';

// Teacher.
$string['teacher']  = 'Teacher';
$string['teachers'] = 'Teachers';

// GDPR.
$string['privacy:metadata'] = 'The GridFlow Course Format plugin does not store any personal data.';
