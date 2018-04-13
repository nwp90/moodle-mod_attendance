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

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot . '/local/ereserve_plus/lib.php';

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME,
        get_string('pluginname', LOCAL_ERESERVE_PLUS_PLUGIN_NAME)
    );

    // Settings Introduction
    $setting = new admin_setting_heading(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/heading',
        '',
        get_string('settings_intro', LOCAL_ERESERVE_PLUS_PLUGIN_NAME)
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    // General Settings Header
    $setting = new admin_setting_heading(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/general_settings_heading',
        get_string('settings_general_heading', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        ''
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    // eReserve Plus Host
    $host_re = '/^(([a-z0-9-]+\.)+([a-z0-9-]{2,})|localhost)(:[0-9]{1,5})*$/';
    $setting = new admin_setting_configtext(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/host',
        get_string('setting_host_label', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_host_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        '',
        $host_re
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    // IMS LTI Settings Header
    $setting = new admin_setting_heading(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/ims_lti_settings_heading',
        get_string('setting_ims_lti_heading', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_ims_lti_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME)
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    //consumer_key
    $setting = new admin_setting_configtext(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/consumer_key',
        get_string('setting_consumer_key_label', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_consumer_key_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        '',
        PARAM_TEXT
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    //shared_secret
    $setting = new admin_setting_configtext(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/shared_secret',
        get_string('setting_shared_secret_label', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_shared_secret_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        '',
        PARAM_TEXT
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    // Development Settings Header
    $setting = new admin_setting_heading(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/ims_development_heading',
        get_string('setting_development_heading', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_development_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME)
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    //scheme
    $setting = new admin_setting_configcheckbox(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/scheme',
        get_string('setting_scheme_label', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_scheme_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        LOCAL_ERESERVE_PLUS_DEFAULT_SCHEME,
        'https',
        'http'
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    //debug
    $setting = new admin_setting_configcheckbox(
        LOCAL_ERESERVE_PLUS_PLUGIN_NAME . '/debug_mode',
        get_string('setting_debug_label', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        get_string('setting_debug_desc', LOCAL_ERESERVE_PLUS_PLUGIN_NAME),
        0
    );
    $setting->plugin = LOCAL_ERESERVE_PLUS_PLUGIN_NAME;
    $settings->add($setting);

    $ADMIN->add('localplugins', $settings);
}
