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
 * GridFlow Course Format — format.php
 *
 * Three rendering modes:
 *   1. EDITING ON          → Moodle core component renderer (full edit UI)
 *   2. EDITING OFF + ?section=N → Single-section page (activities list for that section)
 *   3. EDITING OFF         → Custom card/accordion overview
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

$format   = course_get_format($course);
$course   = $format->get_course();
$modinfo  = get_fast_modinfo($course);
$renderer = $PAGE->get_renderer('format_gridflow');
$settings = $format->get_settings();
$layout   = (int)($settings['gridflowlayout'] ?? GRIDFLOW_LAYOUT_GRID);

// Get requested section number (0 = show all).
$displaysection = optional_param('section', 0, PARAM_INT);

// Make sure section 0 exists.
course_create_sections_if_missing($course, 0);

// ── EDIT MODE ─────────────────────────────────────────────────────────────────
if ($PAGE->user_is_editing()) {
    // Moodle 4.x core output: gives us add-activity, add-section,
    // bulk actions, drag-drop, inline rename — everything for free.
    $outputclass = $format->get_output_classname('content');
    $widget      = new $outputclass($format);
    echo $renderer->render($widget);
    return;
}

// ── VIEW MODE — load JS strings ───────────────────────────────────────────────
$PAGE->requires->strings_for_js(
    ['viewactivity', 'viewtopic', 'showless', 'expandall', 'collapseall',
     'courseprogresstitle', 'activitiescompleted', 'previous', 'next'],
    'format_gridflow'
);

// ── VIEW MODE — SINGLE SECTION PAGE (?section=N) ──────────────────────────────
if ($displaysection > 0) {
    // Validate the section exists and user can see it.
    $sectioninfo = $modinfo->get_section_info($displaysection);

    if (!$sectioninfo) {
        // Section doesn't exist — redirect back to course.
        redirect(course_get_url($course));
    }

    $context = context_course::instance($course->id);
    if (!$sectioninfo->uservisible &&
        !has_capability('moodle/course:viewhiddensections', $context)) {
        // Not visible to this user.
        redirect(course_get_url($course), get_string('notavailable'));
    }

    echo $renderer->render_single_section($course, $displaysection, $settings, $modinfo);
    return;
}

// ── VIEW MODE — ALL SECTIONS OVERVIEW (card / accordion) ──────────────────────
echo $renderer->render_gridflow($course, 0, $settings, $layout);
