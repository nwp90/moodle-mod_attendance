<?php
$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_cmapcore'),
            get_string('descconfig', 'block_cmapcore')
        ));
 
$settings->add(new admin_setting_configcheckbox(
            'simplehtml/Allow_HTML',
            get_string('labelallowhtml', 'block_cmapcore'),
            get_string('descallowhtml', 'block_cmapcore'),
            '0'
        ));