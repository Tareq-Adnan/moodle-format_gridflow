// This file is part of Moodle - http://moodle.org/
// Moodle is free software; GNU GPL v3 or later.

/**
 * GridFlow — view-mode visual behaviour.
 *
 * Only loaded in view mode. Handles:
 *   Grid:      toggle activity list inside a card
 *   Accordion: expand/collapse sections, keyboard support,
 *              expand-all / collapse-all, localStorage persistence
 *
 * @module     format_gridflow/main
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    var LAYOUT_GRID      = 0;
    var LAYOUT_ACCORDION = 1;

    // ── Grid ─────────────────────────────────────────────────────────────────

    function initGrid() {
        var $w = $('#gridflow-wrap.gridflow-grid');
        if (!$w.length) { return; }

        $w.on('click', '.gf-btn-toggle', function(e) {
            e.stopPropagation();
            var $btn  = $(this);
            var $card = $btn.closest('.gf-card');
            var $list = $card.find('.gf-act-list');
            var $icon = $btn.find('.gf-toggle-icon');
            var $lbl  = $btn.find('.gf-toggle-label');
            var open  = $btn.attr('aria-expanded') === 'true';

            if (open) {
                $list.slideUp(200, function() { $(this).attr('hidden', true); });
                $btn.attr('aria-expanded', 'false');
                $icon.css('transform', 'rotate(0deg)');
                $lbl.text($btn.data('show') || 'Show activities');
            } else {
                $list.removeAttr('hidden').hide().slideDown(200);
                $btn.attr('aria-expanded', 'true');
                $icon.css('transform', 'rotate(-180deg)');
                $lbl.text($btn.data('hide') || 'Hide activities');
            }
        });
    }

    // ── Accordion ─────────────────────────────────────────────────────────────

    function initAccordion(defaultExpanded) {
        var $w = $('#gridflow-wrap.gridflow-accordion');
        if (!$w.length) { return; }

        // Restore per-section saved state from localStorage.
        $w.find('.gf-acc-item').each(function() {
            var sid   = $(this).data('sectionid');
            var $h    = $(this).find('.gf-acc-header');
            var $p    = $(this).find('.gf-acc-panel');
            var saved = null;
            try { saved = localStorage.getItem('gf_s_' + sid); } catch(e) {}

            var shouldOpen = (saved !== null) ? (saved === '1') : defaultExpanded;
            if (shouldOpen) {
                $h.attr('aria-expanded', 'true');
                $p.removeAttr('hidden').show();
                $h.find('.gf-chevron').css('transform', 'rotate(-180deg)');
            } else {
                $h.attr('aria-expanded', 'false');
                $p.attr('hidden', true).hide();
                $h.find('.gf-chevron').css('transform', 'rotate(0deg)');
            }
        });

        // Click / keyboard toggle.
        $w.on('click keydown', '.gf-acc-header', function(e) {
            if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) { return; }
            e.preventDefault();

            var $h   = $(this);
            var $p   = $h.next('.gf-acc-panel');
            var $ic  = $h.find('.gf-chevron');
            var open = $h.attr('aria-expanded') === 'true';

            $h.attr('aria-expanded', open ? 'false' : 'true');
            $ic.css('transform', open ? 'rotate(0deg)' : 'rotate(-180deg)');

            if (open) {
                $p.slideUp(220, function() { $(this).attr('hidden', true); });
            } else {
                $p.removeAttr('hidden').hide().slideDown(220);
            }

            try {
                var sid = $h.closest('.gf-acc-item').data('sectionid');
                localStorage.setItem('gf_s_' + sid, open ? '0' : '1');
            } catch(e) {}
        });

        // Expand all.
        $w.on('click', '[data-action="expand-all"]', function() {
            $w.find('.gf-acc-header').attr('aria-expanded', 'true');
            $w.find('.gf-chevron').css('transform', 'rotate(-180deg)');
            $w.find('.gf-acc-panel').removeAttr('hidden').slideDown(200);
        });

        // Collapse all.
        $w.on('click', '[data-action="collapse-all"]', function() {
            $w.find('.gf-acc-header').attr('aria-expanded', 'false');
            $w.find('.gf-chevron').css('transform', 'rotate(0deg)');
            $w.find('.gf-acc-panel').slideUp(200, function() {
                $(this).attr('hidden', true);
            });
        });
    }

    // ── Public API ────────────────────────────────────────────────────────────

    return {
        init: function(opts) {
            var layout   = parseInt((opts && opts.layout)   || 0, 10);
            var expanded = parseInt((opts && opts.expanded) || 1, 10) === 1;

            $(document).ready(function() {
                if (layout === LAYOUT_ACCORDION) {
                    initAccordion(expanded);
                } else {
                    initGrid();
                }
            });
        }
    };
});
