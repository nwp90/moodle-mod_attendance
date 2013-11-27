<?php

function xmldb_local_otago_presentation_install() {
    global $DB, $CFG;
    $systemcontext = get_system_context();

    // Admin has just installed our module.
    // To be at all useful, we need webservices, and rest in particular.
    // Assert that these are enabed for the admin.
    if (empty($CFG->enablewebservices)) {
        set_config('enablewebservices', '1');
    }
    if (empty($CFG->webserviceprotocols)) {
        $webserviceprotocols = array();
    } else {
        $webserviceprotocols = explode(',', $CFG->webserviceprotocols);
    }
    if (!in_array('xmlrpc', $webserviceprotocols)) {
        $webserviceprotocols[] = 'rest';
        $protocollist = implode(',', $webserviceprotocols);
        set_config('webserviceprotocols', $protocollist);
    }
}
