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
 * GridFlow — content output class.
 *
 * Overrides core_courseformat\output\local\content to inject GridFlow-specific
 * data (layout, grid columns, card style, progress) into the template context
 * while keeping ALL core editing features (add activity/resource, add section,
 * bulk actions, drag-drop, reactive state) intact.
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_gridflow\output\courseformat;

defined('MOODLE_INTERNAL') || die();

use core_courseformat\output\local\content as content_base;
use renderer_base;

/**
 * Content output class for GridFlow format.
 */
class content extends content_base {

    /**
     * Export data for the main course content template.
     *
     * We call parent::export_for_template() to get all the standard Moodle
     * course data (sections, activities, editing controls, etc.) and then
     * add our own GridFlow-specific variables on top.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): \stdClass {
        // Get all standard course data from core — this includes everything
        // needed for editing: add activity, add section, bulk actions, etc.
        $data = parent::export_for_template($output);

        // Inject GridFlow-specific settings.
        $format   = $this->format;
        $settings = $format->get_settings();

        $layout    = (int)($settings['gridflowlayout']         ?? GRIDFLOW_LAYOUT_GRID);
        $gridcols  = max(1, (int)($settings['gridcolumns']     ?? 3));
        $cardstyle = (int)($settings['sectioncardstyle']       ?? 0);
        $expanded  = (int)($settings['defaultsectionexpanded'] ?? 1);

        $data->gridflow_layout        = $layout;
        $data->gridflow_isgrid        = ($layout === GRIDFLOW_LAYOUT_GRID);
        $data->gridflow_isaccordion   = ($layout === GRIDFLOW_LAYOUT_ACCORDION);
        $data->gridflow_gridcols      = $gridcols;
        $data->gridflow_colclass      = 'col-md-' . (int)(12 / $gridcols);
        $data->gridflow_cardstyle     = $cardstyle;
        $data->gridflow_cardstyleclass = 'cardstyle-' . $cardstyle;
        $data->gridflow_expanded      = $expanded;
        $data->gridflow_layoutclass   = ($layout === GRIDFLOW_LAYOUT_ACCORDION)
            ? 'gridflow-accordion'
            : 'gridflow-grid';

        return $data;
    }
}
