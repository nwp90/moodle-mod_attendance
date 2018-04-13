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

/*
 * TinyMCE Moodle Plugin for eReserve Plus
 *
 * @package    tinymce_ereserve
 * @copyright  2018 eReserve Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-tinymce_ereserve-button
 */

(function () {
    tinymce.PluginManager.requireLangPack('ereserve');

    tinymce.create('tinymce.plugins.eReservePlusResourceLinkingPlugin', {
        init: function (ed, pluginUrl) {
            if (ed.getParam('disabled', false)) {
                return;
            }

            var instance_url = ed.getParam('ereserve_instance_base_url', '');
            var moodle_base_url = ed.getParam('moodle_base_url', '');
            var course_id = ed.getParam('course_id', '');
            var debug = ed.getParam('debug', false);

            window.addEventListener('message', function (e) {
                if (debug) {
                    var errors = '';

                    if (e.origin !== instance_url) {
                        console.log('DEBUG atto_ereserve: ERROR bad origin got "' + e.origin + '" expected "' + instance_url + '"');
                    }
                    if (e.data.action !== 'insert') {
                        console.log('DEBUG atto_ereserve: ERROR bad data got "' + e.data.action + '" expected "insert"');
                    }
                }

                if (e.origin === instance_url) {
                    if (e.data.action === 'insert') {
                        if (debug) {
                            console.log('DEBUG atto_ereserve: Insert was requested for resource id=' + e.data.resource_link_id)
                        }
                        var resource_link_id = e.data.resource_link_id;
                        var element_id = 'eres_res_link_' + resource_link_id;

                        var launch_url =
                            moodle_base_url +
                            '/local/ereserve_plus/resource_link/show.php' +
                            '?course_id=' + course_id +
                            '&id=' + resource_link_id;

                        var iframe_sizing_js =
                            "script = document.createElement('script');" +
                            "script.src = '" + instance_url + "/app/scripts/resource_link_frame.js';" +
                            "script.onload = function () { eReserve.ResourceLinking.ResourceLinkFrame.init('" + element_id + "', '" + instance_url + "'); };" +
                            "document.head.appendChild(script);";

                        var markup =
                            '<iframe ' + '' +
                            '   id="' + element_id + '" ' +
                            '   src="' + launch_url + '" ' +
                            '   width="100%" ' +
                            '   height="100" ' +
                            '   frameborder="0" ' +
                            '   onload="' + iframe_sizing_js + '" ' +
                            '   scrolling="no" ' +
                            '   style="overflow: hidden"' +
                            '   >' +
                            '</iframe>';

                        var window_manager = tinyMCE.activeEditor.windowManager;
                        window_manager.close(window_manager._frontWindow().id);
                        ed.execCommand('mceInsertContent', false, markup);
                    }
                }
            }, false);

            ed.addCommand('mceResourceLinkCreate', function () {
                var width = 1000;
                var height = 550;
                var launch_url =
                    moodle_base_url +
                    '/local/ereserve_plus/resource_link/new.php?course_id=' +
                    ed.getParam('course_id', undefined);

                ed.windowManager.open({
                    file: launch_url,
                    width: width + parseInt(ed.getLang('media.delta_width', 0)),
                    height: height + parseInt(ed.getLang('media.delta_height', 0)),
                    inline: 1
                }, {
                    plugin_url: pluginUrl
                });
            });

            ed.addButton('ereserve', {
                title: 'ereserve.create_link_button_title',
                image: pluginUrl + '/img/icon.png',
                cmd: 'mceResourceLinkCreate'
            });
        },

        getInfo: function () {
            return {
                longname: 'eReserve Plus Resource Linking',
                author: 'eReserve Pty Ltd <info@ereserve.com.au>',
                version: "1.0"
            };
        }

    });

    tinymce.PluginManager.add('ereserve', tinymce.plugins.eReservePlusResourceLinkingPlugin);
})();
