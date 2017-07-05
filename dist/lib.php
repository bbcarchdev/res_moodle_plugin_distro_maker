<?php
/**
 * Repository plugin which uses an external application to enable users to
 * select RES media URLs as resources.
 *
 * This requires a RES Moodle plugin service to act as its back-end and to
 * present the file chooser.
 *
 * @package    repository_res
 * @copyright  2017, Elliot Smith <elliot.smith@bbc.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->dirroot . '/repository/lib.php');

class repository_res extends repository {

    /**
     * Create a default instance of the plugin when the plugin starts.
     * This will point at the default BBC-maintained RES Moodle plugin
     * service.
     */
    public static function plugin_init() {
        $options = array(
            'name' => 'RES',
            'pluginservice_url' => '' . new moodle_url('/repository/res/service/')
        );

        $id = repository::static_function('res','create', 'res', 0,
                                          context_system::instance(),
                                          $options, 0);

        return !empty($id);
    }

    /**
     * Expose the RES Moodle plugin service URL as a configuration option.
     */
    public static function get_instance_option_names() {
        $option_names = array('pluginservice_url');
        return array_merge(parent::get_instance_option_names(), $option_names);
    }

    /**
     * An instance can be configured to point at any RES Moodle plugin service
     * instance, but defaults to the one maintained by the BBC.
     */
    public static function instance_config_form($mform,
                                                $classname = 'repository_res') {
        parent::instance_config_form($mform, 'repository_res');

        // name
        $mform->setDefault('name', 'RES');

        // pluginservice_url
        $mform->addElement('text', 'pluginservice_url',
                           get_string('res:pluginservice_url', 'repository_res'),
                           array('size' => '60'));
        $mform->setType('pluginservice_url', PARAM_URL);
        $mform->setDefault('pluginservice_url', '' . new moodle_url('/repository/res/service/'));
        $mform->addRule('pluginservice_url', get_string('required'),
                        'required', null, 'client');
    }

    /**
     * The listing comes from an external file picker (provided by the RES
     * Moodle plugin service).
     */
    public function get_listing($path = null, $page = null) {
        // load external filepicker
        $callback_url = new moodle_url('/') .
                        'repository/res/callback.php?repo_id=' . $this->id;

        $pluginservice_url = $this->get_option('pluginservice_url') .
                             '?callback=' . urlencode($callback_url);

        return array(
            'nologin' => true,
            'norefresh' => true,
            'nosearch' => true,
            'object' => array(
                'type' => 'text/html',
                'src' => $pluginservice_url
            )
        );
    }

    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }
}
