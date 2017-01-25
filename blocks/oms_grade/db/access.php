<?php
$capabilities = array(
 
    'block/oms_grade:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
        
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),
    
    'block/oms_grade:addinstance' => array(
        'riskbitmask' => 0,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);
