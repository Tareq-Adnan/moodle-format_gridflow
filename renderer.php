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
 * GridFlow Course Format — Renderer.
 *
 * Handles three rendering modes (view mode only — edit mode uses core):
 *   render_gridflow()       — all-sections overview (cards or accordion)
 *   render_single_section() — single section activity list page
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use core_course\activity_purpose;
/**
 * GridFlow renderer.
 */
class format_gridflow_renderer extends core_courseformat\output\section_renderer {

    /** @var format_gridflow */
    protected $courseformat;

    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course);
    }

    // ── Required stubs ─────────────────────────────────────────────────────────
    protected function start_section_list() {
        return html_writer::start_tag('ul', ['class' => 'gridflow-sections']);
    }
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }
    protected function page_title() {
        return get_string('sectionname', 'format_gridflow');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SINGLE SECTION PAGE — /course/view.php?id=X&section=N
    // Shows one section's activities in a clean full-width list.
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Render a single section page.
     *
     * @param stdClass       $course
     * @param int            $sectionnum   The section number (from ?section=N)
     * @param array          $settings
     * @param course_modinfo $modinfo
     * @return string HTML
     */
    public function render_single_section($course, $sectionnum, $settings, $modinfo) {
        global $USER, $OUTPUT;

        $context    = context_course::instance($course->id);
        $completion = new completion_info($course);
        $sectioninfo = $modinfo->get_section_info($sectionnum);

        // Build activity list for this section.
        $activities    = $this->get_section_activities(
            $course, $sectionnum, $modinfo, $completion, $context, $USER
        );

        // Prev / next section navigation.
        $allsections   = $modinfo->get_section_info_all();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

        $prevurl = $prevtitle = $nexturl = $nexttitle = null;

        for ($n = $sectionnum - 1; $n >= 1; $n--) {
            if (isset($allsections[$n]) &&
                ($allsections[$n]->uservisible || $canviewhidden)) {
                $prevurl   = (new moodle_url('/course/view.php',
                    ['id' => $course->id, 'section' => $n]))->out(false);
                $prevtitle = $this->courseformat->get_section_name($allsections[$n]);
                break;
            }
        }
        for ($n = $sectionnum + 1; $n < count($allsections); $n++) {
            if (isset($allsections[$n]) &&
                ($allsections[$n]->uservisible || $canviewhidden)) {
                $nexturl   = (new moodle_url('/course/view.php',
                    ['id' => $course->id, 'section' => $n]))->out(false);
                $nexttitle = $this->courseformat->get_section_name($allsections[$n]);
                break;
            }
        }

        $courseurl = (new moodle_url('/course/view.php',
            ['id' => $course->id]))->out(false);

        // Section progress.
        $totalacts     = count($activities);
        $completedacts = count(array_filter($activities, fn($a) => $a['completed']));
        $progress      = ($totalacts > 0)
            ? (int)round(($completedacts / $totalacts) * 100)
            : 0;

        $data = [
            'courseid'            => $course->id,
            'courseurl'           => $courseurl,
            'coursename'          => format_string($course->fullname),
            'sectionnum'          => $sectionnum,
            'sectionid'           => $sectioninfo->id,
            'title'               => $this->courseformat->get_section_name($sectioninfo),
            'summary'             => $sectioninfo->summary ?? '',
            'hassummary'          => !empty(strip_tags($sectioninfo->summary ?? '')),
            'activities'          => $activities,
            'hasactivities'       => !empty($activities),
            'totalactivities'     => $totalacts,
            'completedactivities' => $completedacts,
            'remainingactivities' => max(0, $totalacts - $completedacts),
            'sectionprogress'     => $progress,
            'hasprogress'         => ($totalacts > 0),
            'prevurl'             => $prevurl,
            'prevtitle'           => $prevtitle,
            'hasprev'             => !empty($prevurl),
            'nexturl'             => $nexturl,
            'nexttitle'           => $nexttitle,
            'hasnext'             => !empty($nexturl),
            'hidden'              => !$sectioninfo->visible,
        ];

        $this->page->requires->js_call_amd('format_gridflow/main', 'init', [[
            'layout'   => GRIDFLOW_LAYOUT_GRID, // doesn't affect single-section page
            'expanded' => 1,
            'courseid' => $course->id,
        ]]);

        return $this->render_from_template('format_gridflow/layout_single_section', $data);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ALL SECTIONS OVERVIEW — card / accordion
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Render the full course overview (view mode).
     *
     * @param stdClass $course
     * @param int      $displaysection  Always 0 here (routing done in format.php)
     * @param array    $settings
     * @param int      $layout
     * @return string HTML
     */
public function render_gridflow($course, $displaysection, $settings, $layout) {
    global $USER, $OUTPUT;

    $output     = $this->output;
    $modinfo    = get_fast_modinfo($course);
    $context    = context_course::instance($course->id);
    $completion = new completion_info($course);

    // ── Pagination ────────────────────────────────────────────────────────
    $limit = 9;
    $page  = optional_param('page', 0, PARAM_INT);

    // ── Overall progress ──────────────────────────────────────────────────
    $progresspercent = 0;
    $showprogress    = !empty($settings['showprogressbar']);

    if ($showprogress && $completion->is_enabled()) {
        $total = $done = 0;

        // Avoid heavy load for huge courses
        if (count($modinfo->cms) <= 300) {
            foreach ($modinfo->cms as $cm) {
                if ($cm->completion != COMPLETION_TRACKING_NONE && $cm->uservisible) {
                    $total++;
                    $cdata = $completion->get_data($cm, false, $USER->id);

                    if (in_array($cdata->completionstate, [
                        COMPLETION_COMPLETE,
                        COMPLETION_COMPLETE_PASS
                    ])) {
                        $done++;
                    }
                }
            }
        }

        if ($total > 0) {
            $progresspercent = (int) round(($done / $total) * 100);
        }
    }

    // ── Teachers ──────────────────────────────────────────────────────────
    $teachers    = [];
    $showteacher = !empty($settings['showteacherinfo']);

    if ($showteacher) {
        $roles = get_roles_with_capability('moodle/course:update', CAP_ALLOW, $context);

        foreach ($roles as $role) {
            $list = get_role_users(
                $role->id,
                $context,
                false,
                'u.id,u.firstname,u.lastname,u.picture,u.imagealt,u.email',
                'u.lastname,u.firstname,u.id',
                true,
                '',
                0,
                5
            );

            foreach ($list as $t) {
                $teachers[] = [
                    'fullname' => fullname($t),
                    // 'picture'  => $OUTPUT->user_picture($t, ['size' => 35])
                ];
            }

            if (!empty($teachers)) {
                break;
            }
        }
    }

    // ── Section Processing ────────────────────────────────────────────────
    $trunclen      = (int)($settings['summarytruncatelength'] ?? 120);
    $hidegeneral   = $this->courseformat->hide_general_section_when_empty($course);
    $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

    $generalsection = null;
    $sections       = [];
    $allsections    = [];

    // Collect valid sections first
    foreach ($modinfo->get_section_info_all() as $num => $info) {

        if (!$info->uservisible && !$canviewhidden) {
            continue;
        }

        // General section
        if ($num == 0) {
            $sdata = $this->build_section_card(
                $course, $info, $num, $modinfo, $completion, $context, $trunclen, $USER
            );

            if (!$hidegeneral && $sdata !== null) {
                $generalsection = $sdata;
            }

            continue;
        }

        // Skip empty sections
        if (empty($modinfo->sections[$num])) {
            continue;
        }

        $allsections[] = [$num, $info];
    }

    // Total sections count
    $total = count($allsections);

    // Slice for current page
    $start = $page * $limit;
    $pagedsections = array_slice($allsections, $start, $limit);

    // Build section cards
    foreach ($pagedsections as [$num, $info]) {

        $sdata = $this->build_section_card(
            $course, $info, $num, $modinfo, $completion, $context, $trunclen, $USER
        );

        if ($sdata === null || $info->itemid) {
            continue;
        }

        $sections[] = $sdata;
    }

    // ── Layout Settings ───────────────────────────────────────────────────
    $gridcols  = max(1, (int)($settings['gridcolumns'] ?? 3));
    $cardstyle = (int)($settings['sectioncardstyle'] ?? 0);
    $expanded  = (int)($settings['defaultsectionexpanded'] ?? 1);

    // Auto switch layout if too many sections
    // if ($total > 50) {
    //     $layout = GRIDFLOW_LAYOUT_ACCORDION;
    // }

    // ── Paging Bar ────────────────────────────────────────────────────────
    $baseurl = new moodle_url('/course/view.php', [
        'id' => $course->id
    ]);

    $pagingbar = new paging_bar(
        $total,
        $page,
        $limit,
        $baseurl
    );

    // ── Template Data ─────────────────────────────────────────────────────
    $data = [
        'courseid'          => $course->id,
        'gridcols'          => $gridcols,
        'colclass'          => 'col-lg-' . (int)(12 / $gridcols) . ' col-md-6 col-12',
        'cardstyleclass'    => 'cardstyle-' . $cardstyle,
        'showprogress'      => $showprogress,
        'progresspercent'   => $progresspercent,
        'showteacher'       => ($showteacher && !empty($teachers)),
        'teachers'          => array_values($teachers),
        'generalsection'    => $generalsection,
        'hasgeneralsection' => !empty($generalsection),
        'sections'          => array_values($sections),
        'hassections'       => !empty($sections),
        'defaultexpanded'   => $expanded,
        'sectionnum'        => $total,
        'pagingbar'         => $output->render($pagingbar)
    ];

    // ── JS (optional, safe to keep even if unused) ─────────────────────────
    $this->page->requires->js_call_amd('format_gridflow/main', 'init', [[
        'layout'   => $layout,
        'expanded' => $expanded,
        'courseid' => $course->id,
    ]]);

    // ── Template ──────────────────────────────────────────────────────────
    $template = ($layout === GRIDFLOW_LAYOUT_ACCORDION)
        ? 'format_gridflow/layout_accordion'
        : 'format_gridflow/layout_grid';

    return $this->render_from_template($template, $data);
}

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Build card/accordion data for one section (overview page).
     */
    protected function build_section_card($course, $info, $num, $modinfo,
                                          $completion, $context, $trunclen, $USER) {
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        if (!$info->uservisible && !$canviewhidden) { return null; }

        $title        = $this->courseformat->get_section_name($info);
        $summaryraw   = $info->summary ?? '';
        $summarytext  = trim(strip_tags($summaryraw));
        $summaryshort = core_text::strlen($summarytext) > $trunclen
            ? core_text::substr($summarytext, 0, $trunclen) . '…'
            : $summarytext;

        // Build full activity list (needed for general section inline display
        // and for the card toggle list in section cards).
        $activities    = $this->get_section_activities(
            $course, $num, $modinfo, $completion, $context, $USER
        );

        // Derive counts from the activity list.
        $totalacts     = count($activities);
        $completedacts = count(array_filter($activities, fn($a) => $a['completed']));

        // Activity type summary string, e.g. "2 Assignments, 1 Quiz".
        $typecounts = [];
        foreach ($activities as $act) {
            $typecounts[$act['modname']] = ($typecounts[$act['modname']] ?? 0) + 1;
        }
        $typeparts = [];
        foreach ($typecounts as $modname => $count) {
            
            $strkey = ($count > 1) ? 'modulenameplural' : 'modulename';
            $label  = get_string($strkey, $modname, null, true) ?: $modname;

            $typeparts[] = $count . ' ' . $label;
        }

        $progress = ($totalacts > 0)
            ? (int)round(($completedacts / $totalacts) * 100)
            : 0;

        $sectionurl = (new moodle_url('/course/view.php',
            ['id' => $course->id, 'section' => $num]))->out(false);

        // array_values() is required — Mustache only iterates 0-indexed arrays.
        // If any cms were skipped the keys may be non-contiguous, breaking iteration.
        $activities = array_values($activities);

        return [
            'index'               => $num,
            'id'                  => $info->id,
            'title'               => $title,
            'summary'             => $summaryraw,
            'summarytext'         => $summarytext,
            'summaryshort'        => $summaryshort,
            'hassummary'          => ($summarytext !== ''),
            'activities'          => $activities,
            'hasactivities'       => !empty($activities),
            'totalactivities'     => $totalacts,
            'completedactivities' => $completedacts,
            'activityinfostring'  => implode(', ', $typeparts),
            'sectionprogress'     => $progress,
            'hasprogress'         => ($totalacts > 0),
            'visible'             => (bool)$info->visible,
            'hidden'              => !$info->visible,
            'sectionurl'          => $sectionurl,
            'isgeneral'           => ($num === 0),
            'isallcompleted'      => ($totalacts > 0 && $completedacts === $totalacts),
        ];
    }

    /**
     * Build full activity list for a single section (single-section page).
     */
    protected function get_section_activities($course, $sectionnum, $modinfo,
                                              $completion, $context, $USER) {
        global $OUTPUT;

        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        $activities    = [];

        if (empty($modinfo->sections[$sectionnum])) {
            return $activities;
        }

        foreach ($modinfo->sections[$sectionnum] as $cmid) {
            $cm = $modinfo->cms[$cmid];
            if (!$cm->uservisible && !$canviewhidden) { continue; }
            if ($cm->is_stealth()) { continue; }

            $iscompleted = false;
            $hascompletion = ($completion->is_enabled($cm) !== COMPLETION_TRACKING_NONE);
            if ($hascompletion) {
                $cdata       = $completion->get_data($cm, false, $USER->id);
                $iscompleted = in_array($cdata->completionstate,
                    [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS]);
            }

            $iconurl = $cm->get_icon_url();
            $modname = get_string('modulename', $cm->modname, null, true) ?: $cm->modname;
            $sectionurl = '';

            if ($cm->modname === 'subsection') {
                // Get the delegated section info for this subsection cm
                $modinfo = get_fast_modinfo($cm->course);
                $sectionid = $cm->customdata['sectionid'];
                $section = $modinfo->get_section_info_by_id($sectionid);
                $subsectionnumber = $section->sectionnum;

                $sectionurl = new moodle_url('/course/view.php', [
                    'id' => $cm->course,
                    'section' => $subsectionnumber
                ]);
            }

            $activities[] = [
                'id'            => $cm->id,
                'title'         => $cm->get_formatted_name(),
                'url'           => $cm->url ? $cm->url->out(false) : $sectionurl->out(false),
                'modname'       => $cm->modname,
                'modnamestr'    => $modname,
                'iconurl'       => $iconurl ? $iconurl->out(false) : '',
                'visible'       => (bool)$cm->visible,
                'hidden'        => !$cm->visible,
                'hascompletion' => $hascompletion,
                'completed'     => $iscompleted,
                'description'   => $cm->get_formatted_content(),
                'purpose'       => $this->get_activity_purpose($cm),
            ];
        }

        // Mustache requires a 0-indexed array to iterate {{#activities}}.
        return array_values($activities);
    }

    protected function get_activity_purpose($cm) {
        $purpose = plugin_supports('mod', $cm->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER);
        switch ($purpose) {
            case MOD_PURPOSE_ASSESSMENT:
                return 'assessment';
                break;

            case MOD_PURPOSE_COMMUNICATION:
                return'communication';
                break;

            case MOD_PURPOSE_CONTENT:
                return 'content';
                break;

            case MOD_PURPOSE_COLLABORATION:
                return 'collaboration';
                break;

            case MOD_PURPOSE_INTERACTIVECONTENT:
                return 'interactivecontent';
                break;

            default:
                return 'other';
        }
    }
}
