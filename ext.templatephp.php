<?php
if (!defined('APP_VER')) {
    exit('No direct script access allowed');
}
// define the old-style EE object
if (!function_exists('ee')) {
    function ee()
    {
        static $EE;
        if (! $EE) {
          $EE = get_instance();
        }
        return $EE;
    }
}


class Templatephp_ext {

  public $settings = array();
  public $name = 'TemplatePHP';
  public $version = '0.1';
  public $description = 'Move template PHP configuration to config file.';
  public $settings_exist = 'n';
  public $docs_url = '';

  private $_site_pages;

  /**
   * Constructor
   *
   * @param mixed Settings array or empty string if none exist.
   */
  public function __construct($settings = array()) {
    $this->settings = $settings;
  }

  /**
   * Activate Extension
   *
   * This function enters the extension into the exp_extensions table
   *
   * @see http://codeigniter.com/user_guide/database/index.html for more information on the db class.
   *
   * @return void
   */
  public function activate_extension() {
    $hooks = array(
      'template_fetch_template' => 'template_fetch_template'
    );
    foreach($hooks as $hook => $method) {
      $data = array(
        'class' => __CLASS__,
        'method' => $method,
        'hook' => $hook,
        'priority' => 10,
        'version' => $this->version,
        'enabled' => 'y',
        'settings' => ''
      );
      ee()->db->insert('exp_extensions', $data);
    }
    return true;
  }

  /**
   * Update Extension
   *
   * This function performs any necessary db updates when the extension page is visited.
   *
   * @return mixed void on update / false if none
   */
  public function update_extension($current = '') {
    if($current == '' || $current == $this->version)
      return FALSE;

    ee()->db->where('class', _CLASS__);
    ee()->db->update(
      'extensions',
      array('version' => $this->version)
    );
  }

  /**
   * Disable Extension
   *
   * This method removes information from the exp_extensions table
   *
   * @return void
   */
  public function disable_extension() {
    ee()->db->where('class', __CLASS__);
    ee()->db->delete('extensions');
  }

  /* Hook the template route */
  public function template_fetch_template($template) {
    $group_name = $this->_get_group_name($template['group_id']);
    if ($group_name === false) return false;
    $parse = $this->_match_uri($group_name, $template['template_name']);
    if (!empty($parse)) {
      $this->_update_template_settings($template['template_id'], $parse);
    }
  }

  // update the template settings
  private function _update_template_settings($template_id, $parse) {
    ee()->db->where('template_id', $template_id);
    ee()->db->update('templates',
      array('allow_php' => $parse['allow_php'],
            'php_parse_location' => $parse['php_parse_location']));
  }

  private function _get_group_name($group_id) {
    ee()->db->select('group_name');
    ee()->db->from('template_groups');
    ee()->db->where('group_id', $group_id);
    $q = ee()->db->get();

    if (!$q->num_rows()) return false;
    return ($q->row('group_name'));
  }

  // see if there is config set for the template under this uri
  private function _match_uri($group_name, $template_name) {
    $template_php = ee()->config->item('template_php');
    if (empty($template_php)) return null;

    $template_slug = $group_name . "/" . $template_name;

    $parse = array();
    if (in_array($template_slug, $template_php['input'])) {
      $parse['allow_php'] = 'y';
      $parse['php_parse_location'] = 'i';
    }
    else if (in_array($template_slug, $template_php['output'])) {
      $parse['allow_php'] = 'y';
      $parse['php_parse_location'] = 'o';
    }
    else if (in_array($template_slug, $template_php['disable'])) {
      $parse['allow_php'] = 'n';
      $parse['php_parse_location'] = 'o';
    }
    else {
      return null;
    }

    return $parse;
  }
}
?>