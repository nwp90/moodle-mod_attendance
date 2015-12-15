/*define(['jquery'], function($) {
    
    this.displayMyInstance = function() {
        return window.console.log("amd module mod_bootstrapelements/core loaded");
    };

    return this;

}); */


// Standard license block omitted.
/*
 * @package    block_overview
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module block_overview/helloworld
  */
define(['jquery', 'jqueryui', 'mod_bootstrapelements/iconpicker'], function($) {
 
    var bElem = {};

    bElem.init = function () {
        $.noConflict(true);
    };
 
    bElem.editElem = function () {

        this.init();

        $(function(){
            // setTimeout(function() {
                $("#id_bootstrapicon").iconpicker({
                    placement: "right",
                    selectedCustomClass: "label label-success"
                });

                // $('.loading-icon').hide();
            // }, 3000);
        });

        // return window.console.log($);
        return;
    };

    return bElem;

});