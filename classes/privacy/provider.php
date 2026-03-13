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
 * Privacy provider — GridFlow stores no personal data.
 *
 * @package    format_gridflow
 * @copyright  2026 GridFlow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_gridflow\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\data_provider;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy provider implementation.
 */
class provider implements metadata_provider, \core_privacy\local\request\nullprovider {

    /**
     * Returns metadata — this plugin stores nothing.
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        return $collection;
    }

    /**
     * No user data stored — implement null provider reason.
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
