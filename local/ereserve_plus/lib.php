<?php
/**
 * @package     local
 * @subpackage  ereserve_plus
 * @copyright   2018 eReserve Pty Ltd
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

define('LOCAL_ERESERVE_PLUS_PLUGIN_NAME', 'local_ereserve_plus');
define('LOCAL_ERESERVE_PLUS_DEFAULT_SCHEME', 'https');

$lib_path = dirname(__FILE__) . '/lib';
set_include_path(get_include_path() . PATH_SEPARATOR . $lib_path);
