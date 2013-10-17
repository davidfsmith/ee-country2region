<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
if (file_exists(PATH_THIRD.'country_to_region/config.php') === true) require PATH_THIRD.'country_to_region/config.php';
else require dirname(dirname(__FILE__)).'/country_to_region/config.php';

/**
 * Install / Uninstall and updates the modules
 *
 * @package   Country_To_Region
 * @version   1.0
 * @author    David Smith <david.smith@wirewool.com>
 * @copyright Copyright (c) 2012 Wirewool Ltd <http://www.wirewool.com>
 * @license   http://www.wirewool.com/license/
 * @link      http://www.wirewool.com/forms/
 * @see       http://expressionengine.com/user_guide/development/module_tutorial.html#update_file
 */
class Country_to_region_upd
{

    /**
     * Module version
     *
     * @var string
     * @access public
     */
    public $version = C2R_VERSION;

    /**
     * Module Short Name
     *
     * @var string
     * @access private
     */
    private $module_name = C2R_MODULE_NAME;

    /**
     * Has Control Panel Backend?
     *
     * @var string
     * @access private
     */
    private $has_cp_backend = 'y';

    /**
     * Has Publish Fields?
     *
     * @var string
     * @access private
     */
    private $has_publish_fields = 'n';

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->EE =& get_instance();
    }

    /**
     * Installs the module
     *
     * Installs the module, adding a record to the exp_modules table,
     * creates and populates and necessary database tables,
     * adds any necessary records to the exp_actions table,
     * and if custom tabs are to be used, adds those fields to any saved publish layouts
     *
     * @access public
     * @return boolean
     */
    public function install()
    {
        // Load dbforge
        $this->EE->load->dbforge();

        $module = array(
            'module_name'           => $this->module_name,
            'module_version'        => $this->version,
            'has_cp_backend'        => $this->has_cp_backend,
            'has_publish_fields'    => $this->has_publish_fields,
        );
        
        $this->EE->db->insert('modules', $module);

        //
        //  Region Table
        //
        $fields = array(
            'region_id'     => array('type' => 'INT',       'unsigned'      => true,    'auto_increment'    => true),
            'sub_region_id' => array('type' => 'INT',       'unsigned'      => true),
            'region_name'   => array('type' => 'VARCHAR',   'constraint'    => 250,     'default'           => ''),
        );

        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->add_key('region_id', true);
        $this->EE->dbforge->create_table('c2r_region', true);

        //
        //  Region to Region Mapping Table
        //
        $fields = array(
            'region_id'     => array('type' => 'INT',       'unsigned'      => true,    'auto_increment'    => false),
            'country_code'  => array('type' => 'VARCHAR',   'constraint'    => 2,       'default'           => ''),
        );

        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->create_table('c2r_region_to_country', true);

        return true;
    }

    /**
     * Updates the module
     *
     * This function is checked on any visit to the module's control panel,
     * and compares the current version number in the file to
     * the recorded version in the database.
     * This allows you to easily make database or
     * other changes as new versions of the module come out.
     *
     * @access public
     * @param int $current Installed version of the plugin [optional]
     * @return Boolean FALSE if no update is necessary, TRUE if it is.
     **/
    public function update($current = '')
    {
        if (version_compare($current, $this->version, '='))
        {
            return false;
        }

        if (version_compare($current, $this->version, '<'))
        {
            // Do your update code here
            
            // Upgrade The Module
            $this->EE->db->set('module_version', $this->version);
            $this->EE->db->where('module_name', ucfirst($this->module_name));
            $this->EE->db->update('exp_modules');
        }

        return true;
    }

    /**
     * Uninstalls the module
     *
     * @access public
     * @return Boolean FALSE if uninstall failed, TRUE if it was successful
     **/
    public function uninstall()
    {
        // Load dbforge
        $this->EE->load->dbforge();

        // Remove the tables
        $this->EE->dbforge->drop_table('c2r_region', true);
        $this->EE->dbforge->drop_table('c2r_region_to_country', true);

        $this->EE->db->where('module_name', $this->module_name);
        $this->EE->db->delete('modules');

        return TRUE;
    }

}
// END CLASS

/* End of file upd.country_to_region.php */
/* Location: ./system/expressionengine/third_party/country_to_region/upd.country_to_region.php */