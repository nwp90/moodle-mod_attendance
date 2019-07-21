<?php
/**
 * @package     filter
 * @subpackage  ereserve
 * @copyright   2018 eReserve Pty Ltd
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->libdir . '/filelib.php';


/**
 * Filter for ensuring eReserve Plus content is correct for the course being rendered
 */
class filter_ereserve extends moodle_text_filter
{

    private $_resource_link_path_regex;
    private $_resource_link_paths;
    private $_source_html;

    /**
     * Constructor
     * @param object $context
     * @param object $localconfig
     */
    public function __construct($context, array $localconfig)
    {
        parent::__construct($context, $localconfig);

        $this->_resource_link_path_regex = "/local\/ereserve_plus\/resource_link\/show\.php\?course_id=\d+/";
    }

    /**
     * @param string $html
     * @param array $options
     * @return string
     */
    public function filter($html, array $options = array())
    {
        $this->_source_html = $html;
        return ($this->_updatePaths());
    }

    /**
     * Search the page html looking for matching resource link URL paths then substituting each one with a path for the
     * course being rendered
     *
     * @return string|string[]|null the updated HTML
     */
    private function _updatePaths()
    {
        global $COURSE;
        $new_path = "local/ereserve_plus/resource_link/show.php?course_id=$COURSE->id";

        return (preg_replace($this->_resource_link_path_regex, $new_path, $this->_source_html));
    }
}
