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
 * @package     local
 * @subpackage  ereserve_plus
 * @copyright   2018 eReserve Pty Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin settings.
$string['pluginname'] = 'eReserve Plus Configuration';
$string['settings_intro'] =
    '<p>' .
    'The following settings allow you to configure your eReserve Plus components in Moodle. <br/>' .
    '<span style="font-weight: bold">NB:</span> Although you may change the settings here you may need to purge your ' .
    'Moodle caches in order for them to take effect' .
    '</p>';
$string['settings_general_heading'] = 'General Settings';
$string['setting_host_label'] = 'eReserve Plus Host Name:';
$string['setting_host_desc'] = 'This setting is the full qualified domain for your eReserve Plus instance (e.g. eu-moodle.ereserve.com.au).<br/>' .
    '<span style="font-weight: bold">NB:</span> Please do not include the http:// or https:// portion of the URL<br/>' .
    '<br/>';
$string['setting_ims_lti_heading'] = 'IMS LTI Settings';
$string['setting_ims_lti_desc'] = '<p>' .
    'The following Consumer Key and Secret are generated in eReserve Plus by creating an integration record associated with this instance of Moodle.<br/>' .
    'Creation of integration records can be done within the eReserve Plus admin interface via Configure > Integrations<br/>' .
    'For specific details please see the eReserve Plus documentation <br/>' .
    '</p>';
$string['setting_consumer_key_label'] = 'Consumer Key';
$string['setting_consumer_key_desc'] = 'The <span style="font-weight: bold">Key</span> from the associated integration record in eReserve Plus<br/>' .
    '<br/>';
$string['setting_shared_secret_label'] = 'Shared Secret';
$string['setting_shared_secret_desc'] = 'The <span style="font-weight: bold">Secret</span> from the associated integration record in eReserve Plus<br/>' .
    '<br/>';
$string['host_missing_error'] = 'Missing Host: Please provide the host for your eReserve Plus instance to complete the settings';
$string['missing_lti_settings'] = '<div style="padding:20px;width:100%;font-size:14px;color:red">' .
    'Unable to complete request. Please check the ' . $string['pluginname'] . ' settings and ensure ' .
    $string['setting_consumer_key_label'] . ' and ' . $string['setting_shared_secret_label'] .
    ' have valid values' .
    '</div>';
$string['setting_development_heading'] = 'Development Setting';
$string['setting_development_desc'] = '<p>' .
    'The following settings are for <span style="font-weight: bold">development only</span>. Please use these only when directed by eReserve staff<br/>' .
    '</p>';
$string['setting_scheme_label'] = 'Use HTTPS';
$string['setting_scheme_desc'] = '<span style="font-weight: bold">DEVELOPMENT ONLY</span></br>' .
    'This setting allows developers to choose how they communicate with their instance of eReserve Plus.  This is on by default<br/>' .
    '<br/>';
$string['setting_debug_label'] = 'Enable debugging';
$string['setting_debug_desc'] = '<span style="font-weight: bold">DEVELOPMENT ONLY</span></br>' .
    'This setting enables debugging messages for developers.  This is off by default<br/>' .
    '<br/>';

