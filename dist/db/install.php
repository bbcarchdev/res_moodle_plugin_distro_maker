<?php
/**
 * Setup for repository_res plugin.
 *
 * @package   repository_res
 * @copyright 2017, Elliot Smith <elliot.smith@bbc.co.uk>
 * @license   Apache v2 - http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Install repository_res data into database.
 *
 * @return bool A status indicating success or failure
 */
function xmldb_repository_res_install() {
    global $CFG;

    require_once($CFG->dirroot.'/repository/lib.php');

    $resplugin = new repository_type('res', array(), true);

    return $resplugin->create(true);
}
