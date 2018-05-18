<?php

/*
See https://docs.moodle.org/dev/Output_renderers
*/

namespace block_cmapcore\output;

defined('MOODLE_INTERNAL') || die;

//use \plugin_renderer_base;


class renderer extends \plugin_renderer_base {

    public function cmap_block($cmap) {
        if (!$cmap->show()) {
            return '';
        }
        $templatecontext = [
            'cmap_logo_url' => $this->output->image_url('cmap-logo-small','block_cmapcore'),
            'mapbase' => $cmap->baseurl,
            'moduleshortname' => $cmap->courseshortname,
            'modulelink' => $cmap->baseurl. "/ui/modules/". $cmap->courseshortname,
            'modulename' => $cmap->coursefullname,
            'elementtype' => [
                [
                    'abbrev' => 'pres',
                    'elementlabel' => get_string('corepresentations', 'block_cmapcore'),
                    'listlabel' => get_string('followingpresentations', 'block_cmapcore'),
                    'nonetext' => get_string('nopresentations', 'block_cmapcore')
                ],
                [
                    'abbrev' => 'cond',
                    'elementlabel' => get_string('coreconditions', 'block_cmapcore'),
                    'listlabel' => get_string('followingconditions', 'block_cmapcore'),
                    'nonetext' => get_string('nopresentations', 'block_cmapcore')
                ],
                [
                    'abbrev' => 'acty',
                    'elementlabel' => get_string('coreprofacts', 'block_cmapcore'),
                    'listlabel' => get_string('followingprofacts', 'block_cmapcore'),
                    'nonetext' => get_string('noprofacts', 'block_cmapcore')
                ],
            ]
        ];
        return $this->render_from_template('block_cmapcore/core', $templatecontext);
    }
}
