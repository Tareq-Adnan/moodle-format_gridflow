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
 * GridFlow Course Format — lib.php (main format class).
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Layout constants — also defined defensively in settings.php.
defined('GRIDFLOW_LAYOUT_GRID')      || define('GRIDFLOW_LAYOUT_GRID',      0);
defined('GRIDFLOW_LAYOUT_ACCORDION') || define('GRIDFLOW_LAYOUT_ACCORDION', 1);

/**
 * GridFlow course format.
 *
 * Extends core_courseformat\base (Moodle 4.x).
 */
class format_gridflow extends core_courseformat\base {

    /**
     * Indicate this format uses sections.
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Indicate this format supports the Moodle 4.x reactive component system.
     * This is what enables add-activity chooser, add-section, bulk actions,
     * drag-and-drop, and all other editing features in Moodle 4.x.
     * @return bool
     */
    public function supports_components() {
        return true;
    }

    /**
     * Indicate AJAX is supported.
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Get and cache format settings.
     * @return array
     */
    public function get_settings() {
        if (empty($this->settings)) {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }

    /**
     * Returns the display name of the given section.
     * @param int|stdClass|section_info $section
     * @return string
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return html_entity_decode($section->name, ENT_QUOTES, 'UTF-8');
        }
        return $this->get_default_section_name($section);
    }

    /**
     * Returns the default section name.
     * @param stdClass $section
     * @return string
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            return get_string('section0name', 'format_gridflow');
        }
        return get_string('sectionname', 'format_gridflow') . ' ' . $section->section;
    }

    /**
     * Determine whether the general section (section 0) should be hidden.
     * @param stdClass $course
     * @return bool
     */
    public function hide_general_section_when_empty($course) {
        $settings = $this->get_settings();
        if (empty($settings['hidegeneralsectionwhenempty'])) {
            return false;
        }
        $modinfo = get_fast_modinfo($course);
        $s0      = $modinfo->get_section_info(0);
        return (empty(strip_tags($s0->summary)) && empty($modinfo->sections[0]));
    }

    /**
     * Define course-level format options.
     * @param bool $foreditform
     * @return array
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;

        if ($courseformatoptions === false) {
            $courseformatoptions = [
                'hiddensections' => [
                    'default' => get_config('moodlecourse', 'hiddensections'),
                    'type'    => PARAM_INT,
                ],
                'gridflowlayout' => [
                    'default' => (int)get_config('format_gridflow', 'defaultlayout'),
                    'type'    => PARAM_INT,
                ],
                'gridcolumns' => [
                    'default' => (int)(get_config('format_gridflow', 'defaultgridcolumns') ?: 3),
                    'type'    => PARAM_INT,
                ],
                'showprogressbar' => [
                    'default' => 1,
                    'type'    => PARAM_INT,
                ],
                'showteacherinfo' => [
                    'default' => 1,
                    'type'    => PARAM_INT,
                ],
                'sectioncardstyle' => [
                    'default' => 0,
                    'type'    => PARAM_INT,
                ],
                'defaultsectionexpanded' => [
                    'default' => 1,
                    'type'    => PARAM_INT,
                ],
                'summarytruncatelength' => [
                    'default' => (int)(get_config('format_gridflow', 'defaultsummarytruncatelength') ?: 120),
                    'type'    => PARAM_INT,
                ],
                'hidegeneralsectionwhenempty' => [
                    'default' => 0,
                    'type'    => PARAM_INT,
                ],
            ];
        }

        if ($foreditform && !isset($courseformatoptions['hiddensections']['label'])) {
            $edits = [
                'hiddensections' => [
                    'label'          => new lang_string('hiddensections'),
                    'help'           => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type'   => 'select',
                    'element_attributes' => [[
                        0 => new lang_string('hiddensectionscollapsed'),
                        1 => new lang_string('hiddensectionsinvisible'),
                    ]],
                ],
                'gridflowlayout' => [
                    'label'          => new lang_string('gridflowlayout', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[
                        GRIDFLOW_LAYOUT_GRID      => new lang_string('gridflow_layout_grid', 'format_gridflow'),
                        GRIDFLOW_LAYOUT_ACCORDION => new lang_string('gridflow_layout_accordion', 'format_gridflow'),
                    ]],
                    'help'           => 'gridflowlayout',
                    'help_component' => 'format_gridflow',
                ],
                'gridcolumns' => [
                    'label'          => new lang_string('gridcolumns', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[
                        2 => '2 ' . new lang_string('columns', 'format_gridflow'),
                        3 => '3 ' . new lang_string('columns', 'format_gridflow'),
                        4 => '4 ' . new lang_string('columns', 'format_gridflow'),
                    ]],
                    'help'           => 'gridcolumns',
                    'help_component' => 'format_gridflow',
                ],
                'showprogressbar' => [
                    'label'          => new lang_string('showprogressbar', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[1 => new lang_string('yes'), 0 => new lang_string('no')]],
                    'help'           => 'showprogressbar',
                    'help_component' => 'format_gridflow',
                ],
                'showteacherinfo' => [
                    'label'          => new lang_string('showteacherinfo', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[1 => new lang_string('yes'), 0 => new lang_string('no')]],
                    'help'           => 'showteacherinfo',
                    'help_component' => 'format_gridflow',
                ],
                'sectioncardstyle' => [
                    'label'          => new lang_string('sectioncardstyle', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[
                        0 => new lang_string('cardstyle_default', 'format_gridflow'),
                        1 => new lang_string('cardstyle_flat', 'format_gridflow'),
                        2 => new lang_string('cardstyle_minimal', 'format_gridflow'),
                    ]],
                    'help'           => 'sectioncardstyle',
                    'help_component' => 'format_gridflow',
                ],
                'defaultsectionexpanded' => [
                    'label'          => new lang_string('defaultsectionexpanded', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[
                        1 => new lang_string('expanded', 'format_gridflow'),
                        0 => new lang_string('collapsed', 'format_gridflow'),
                    ]],
                    'help'           => 'defaultsectionexpanded',
                    'help_component' => 'format_gridflow',
                ],
                'summarytruncatelength' => [
                    'label'          => new lang_string('summarytruncatelength', 'format_gridflow'),
                    'element_type'   => 'text',
                    'help'           => 'summarytruncatelength',
                    'help_component' => 'format_gridflow',
                ],
                'hidegeneralsectionwhenempty' => [
                    'label'          => new lang_string('hidegeneralsectionwhenempty', 'format_gridflow'),
                    'element_type'   => 'select',
                    'element_attributes' => [[0 => new lang_string('show'), 1 => new lang_string('hide')]],
                    'help'           => 'hidegeneralsectionwhenempty',
                    'help_component' => 'format_gridflow',
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $edits);
        }

        return $courseformatoptions;
    }

    /**
     * Sections can be deleted.
     * @param stdClass $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Whether to show the news/announcements forum link.
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns default blocks for this format.
     * @return array
     */
    public function get_default_blocks() {
        return [BLOCK_POS_LEFT => [], BLOCK_POS_RIGHT => []];
    }

    /**
     * Allow stealth module visibility.
     * @param cm_info $cm
     * @param section_info $section
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        return true;
    }
}
