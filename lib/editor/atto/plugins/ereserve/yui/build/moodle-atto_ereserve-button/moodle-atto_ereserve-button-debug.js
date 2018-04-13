YUI.add('moodle-atto_ereserve-button', function (Y, NAME) {

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
 * @package    atto_ereserve
 * @copyright  2018 eReserve Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_ereserve-button
 */

/**
 * Atto text editor ereserve plugin.
 *
 * @namespace M.atto_ereserve
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_ereserve';

var CSS = {
    INPUTSUBMIT: 'atto_media_urlentrysubmit',
    INPUTCANCEL: 'atto_media_urlentrycancel'
};

var TEMPLATE = '' +
    '<form class="atto_form" style="padding:0px">' +
    '   <div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
    '       <iframe id=\"ereserve-resoruce-linking-iframe\" style=\"border:0px;background:#ffffff;width:{{width}};height:{{height}};\" src=\"{{iframesrc}}\"></iframe>' +
    '       <button id="ereserve-resource-link-insert" class="{{CSS.INPUTSUBMIT}}" style="display:none">{{get_string "insert" component}}</button>' +
    '   </div>' +
    '</form>';

var resource_link_id = null;

Y.namespace('M.atto_ereserve').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    /**
     * Initialize the button
     *
     * @method Initializer
     */
    initializer: function () {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')) {
            return;
        }

        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_ereserve',
            buttonName: 'insert_ereserve_resource_link',
            callback: this._displayDialogue,
            callbackArgs: 'ereserve',
            title: 'buttontitle'
        });

        var allowed_origin = this.get('ereserve_instance_base_url');
        var plugin = this;
        window.addEventListener('message', function (e) {
            if (plugin.get('debug')) {
                var errors = '';

                if (e.origin !== allowed_origin) {
                    console.log('DEBUG atto_ereserve: ERROR bad origin got "' + e.origin + '" expected "' + allowed_origin + '"');
                }
                if (e.data.action !== 'insert') {
                    console.log('DEBUG atto_ereserve: ERROR bad data got "' + e.data.action + '" expected "insert"');
                }
            }

            if (e.origin === allowed_origin) {
                if (e.data.action === 'insert') {
                    if (plugin.get('debug')) {
                        console.log('DEBUG atto_ereserve: Insert was requested for resource id=' + e.data.resource_link_id)
                    }
                    resource_link_id = e.data.resource_link_id;
                    var insert_button = document.getElementById('ereserve-resource-link-insert');
                    insert_button.click();
                }
            }
        }, false);
    },

    /**
     * Display the ereserve Dialogue
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function (e, clickedicon) {
        e.preventDefault();
        var width = 1000;
        var height = 550;

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            height: height + 'px',
            draggable: false,
            overflowX: 'auto',
            constraintoviewport: false
        });
        //dialog doesn't detect changes in width without this
        //if you reuse the dialog, this seems necessary
        if (dialogue.width !== width + 'px') {
            dialogue.set('width', width + 'px');
        }

        //append buttons to iframe
        var buttonform = this._getFormContent(clickedicon);

        var bodycontent = Y.Node.create('<div></div>');
        bodycontent.append(buttonform);

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },


    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getFormContent: function () {
        var launch_url =
            this.get('moodle_base_url') +
            '/local/ereserve_plus/resource_link/new.php?course_id=' +
            this.get('course_id');
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                component: COMPONENTNAME,
                iframesrc: launch_url,
                width: '100%',
                height: '457px;',
                style: "border:10px solid black;"
            }));

        this._form = content;
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._doInsert, this);
        return content;
    },

    /**
     * Inserts the users input onto the page
     * @method _getDialogueContent
     * @private
     */
    _doInsert: function (e) {
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        var element_id = 'eres_res_link_' + resource_link_id;
        var base_url = this.get('ereserve_instance_base_url');
        var launch_url =
            this.get('moodle_base_url') +
            '/local/ereserve_plus/resource_link/show.php' +
            '?course_id=' + this.get('course_id') +
            '&id=' + resource_link_id;

        var iframe_sizing_js =
            "script = document.createElement('script');" +
            "script.src = '" + base_url + "/app/scripts/resource_link_frame.js';" +
            "script.onload = function () { eReserve.ResourceLinking.ResourceLinkFrame.init('" + element_id + "', '" + base_url + "'); };" +
            "document.head.appendChild(script);";

        var markup =
            '<iframe ' + '' +
            '   id="' + element_id + '" ' +
            '   class="ereserve_resource_link_frame" ' +
            '   src="' + launch_url + '" ' +
            '   width="100%" ' +
            '   height="100" ' +
            '   frameborder="0" ' +
            '   onload="' + iframe_sizing_js + '" ' +
            '   scrolling="no" ' +
            '   style="overflow: hidden"' +
            '   >' +
            '</iframe>';

        this.editor.focus();
        this.get('host').insertContentAtFocusPoint(markup);
        this.markUpdated();

    }
}, {
    ATTRS: {
        disabled: {value: false},
        debug: {value: false},
        ereserve_instance_base_url: {value: ''},
        moodle_base_url: {value: ''},
        course_id: {value: null},
        defaultflavor: {value: ''}
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
