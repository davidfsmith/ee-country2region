<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
if (file_exists(PATH_THIRD.'country_to_region/config.php') === true) require PATH_THIRD.'country_to_region/config.php';
else require dirname(dirname(__FILE__)).'/country_to_region/config.php';

/**
 * Country to Region - Control Panel Elements
 * ----------------------------------------------------------------------------------------------
 * Maps country codes to user defined regions using IP 2 Nation
 *
 * ----------------------------------------------------------------------------------------------
 * 
 * @category  Utility
 * @package   Country_To_Region
 * @author    David Smith <david.smith@wirewool.com>
 * @copyright 2012 Wirewool Ltd <http://www.wirewool.com>
 * @license   http://www.wirewool.com/license/
 * @version   1.0
 * @link      http://www.wirewool.com/
 * @see       http://www.wirewool.com/ee/country_to_region
 */
class Country_to_region_mcp {

    public function __construct()
    {
        $this->EE =& get_instance();

        // Load the libraries and helpers
        $this->EE->load->library('javascript');
        $this->EE->load->library('table');
        $this->EE->load->helper('form');

        // Load the model
        $this->EE->load->model('country_to_region_model', 'c2r');

        // Set the title
        $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('country_to_region_module_name'));

        // Set the module_link
        $this->_module_link = BASE.AMP.'D=cp'.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.C2R_MODULE_NAME;

        // Set the right navigation
        $this->EE->cp->set_right_nav(
            array(
                $this->EE->lang->line('add_region') => $this->_module_link.AMP.'method=add_region',
                // $this->EE->lang->line('config')     => $this->_module_link.AMP.'method=config'
            )
        );
    }

    /**
     * Module index
     *
     * @access public
     * @return boolean
     */
    public function index()
    {

        $vars['form_hidden'] = null;
        $vars['files'] = array();

        // Lets get any regions
        $regions = $this->EE->c2r->get_regions();

        // If there are no regions yet we should redirect to add region
        if (sizeof($regions) === 0)
        {
            $this->EE->functions->redirect($this->_module_link.AMP.'method=add_region');
        }

        // Build the first part of the links
        $mod_link = $this->_module_link.AMP.'method=mod_region';
        $del_link = $this->_module_link.AMP.'method=del_region';

        // var_dump($regions);
        $vars = array();
        foreach ($regions as $region) {
            $vars['regions'][$region->region_name] = array(
                'region_id'     => $region->region_id,
                'country_count' => $this->EE->c2r->get_region_country_count($region->region_id),
                'sub_region_id' => $region->sub_region_id,
                'region_name'   => $region->region_name,
                'mod_link'      => $mod_link.AMP.'R='.$region->region_id,
                'del_link'      => $del_link.AMP.'R='.$region->region_id,
            );
        }

        // $vars['options'] = array(
        //     'edit'      => lang('edit_selected'),
        //     'delete'    => lang('delete_selected')
        // );

        return $this->EE->load->view('list_region', $vars, true);
    }

    public function add_region()
    {
        // Set the breadcrumb and title
        $this->EE->cp->set_breadcrumb($this->_module_link, $this->EE->lang->line('country_to_region_module_name'));
        $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('add_region'));

        // Set a message if there are no regions defined. (msg_no_regions_defined)
        $vars = array();
        $vars['message'] = '';
        $vars['region_name'] = '';
        if ($this->EE->c2r->get_region_count() === 0)
        {
            $vars['message'] = $this->EE->lang->line('msg_no_regions_defined');
        }

        // list of countries
        $countries = $this->_country_names();
        $country_list = array();

        // list of countries in the DB
        $ip_countries = $this->EE->db->get('ip2nation_countries')->result();

        foreach($ip_countries as $row)
        {
            $country_list[$row->code] = false;
        }

        foreach ($countries as $code => $name)
        {
            if (isset($country_list[$code]))
            {
                $vars['countries'][$code] = array(
                    'country_code'  => $code,
                    'country_name'  => $name,
                    'status'        => ($country_list[$code] == 'y'),
                );
            }
        }
        
        // Flag path (TODO - Make a configurable setting)
        $vars['flag_image_path'] = '/images/world_flags';

        // Form action 
        $vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.C2R_MODULE_NAME.AMP.'method=update';
        $vars['form_hidden'] = null;
        
        return $this->EE->load->view('add_region', $vars, true);
    }

    /**
     * Modify Region - Used to get the data out of the database for the selected region
     * 
     * (TODO - Make it more DRY)
     */
    public function mod_region()
    {
        // Get the region ID
        $region_id = $this->EE->input->get('R', true);

        if ($region_id)
        {
            // Set the breadcrumb
            $this->EE->cp->set_breadcrumb($this->_module_link, $this->EE->lang->line('country_to_region_module_name'));

            // Get the region name
            $vars = array();
            $vars['message'] = '';
            $vars['region_name'] = $this->EE->c2r->get_region_name($region_id);

            // Get the countries for the region
            $region_countries = $this->EE->c2r->get_region_countries($region_id);

            foreach ($region_countries as $row)
            {
                $selected_countries[$row->country_code] = true;
            }

            // list of countries
            $countries = $this->_country_names();

            // list of countries in the DB (so we have IP data for them)
            $ip_countries = $this->EE->db->get('ip2nation_countries')->result();
            
            foreach ($ip_countries as $row)
            {
                $country_list[$row->code] = (isset($selected_countries[$row->code]) ? true : false);
            }

            foreach ($countries as $code => $name)
            {
                if (isset($country_list[$code]))
                {
                    $vars['countries'][$code] = array(
                        'country_code'  => $code,
                        'country_name'  => $name,
                        'status'        => ($country_list[$code] == 'y'),
                    );
                }
            }            
        
            // Flag path (TODO - Make a configurable setting)
            $vars['flag_image_path'] = '/images/world_flags';

            // Form action 
            $vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.C2R_MODULE_NAME.AMP.'method=update';
            $vars['form_hidden'] = array('R' => $region_id);
            
            // Set the title
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('mod_region').' - '.$vars['region_name']);
            return $this->EE->load->view('add_region', $vars, true);
        }
        else
        {
            // Redirect to the module home page
            $this->EE->functions->redirect($this->_module_link);
        }
    }

    /**
     * Update
     * Used to get the update the region data in the databsse
     * 
     * @access public
     */
    public function update()
    {
        $region_countries = array_intersect_key($_POST, $this->_country_names());
        $region_name = $this->EE->input->post('region_name', true);
        $region_id = $this->EE->input->post('R', true);

        if ($region_id)
        {
            // If we have a region ID we are doing an update
            // Delete all of the countries associated with the region
            if ($this->EE->c2r->del_countries_from_region($region_id))
            {
                // Update the region details
                if (!$this->EE->c2r->mod_region($region_id, $region_name))
                {
                    $this->EE->session->set_flashdata('message_failure', lang('database_error'));
                }
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', lang('database_error'));
            }
        }
        else
        {
            // Create the region ID
            $region_id = $this->EE->c2r->add_region($region_name);
        }

        // Check we have some countries and a region name
        // (TODO - deal with a submission with no data)
        if (sizeof($region_countries) > 0 and strlen($region_name) > 4)
        {

            $countries = array();
            foreach ($region_countries as $country => $status)
            {
                $countries[] = $country;
            }

            // Add the countries to the region and set an appropriate message
            if ($this->EE->c2r->add_countries_to_region($region_id, $countries))
            {
                $this->EE->session->set_flashdata('message_success', lang('region_updated'));
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', lang('database_error'));
            }
        }

        // Redirect to the module home page
        $this->EE->functions->redirect($this->_module_link);
    }

    /**
     * Del Regions
     * Used to delete a region
     * 
     * @access public
     */
    public function del_region()
    {
        // Get the region ID
        $region_id = $this->EE->input->get('R', true);

        if ($region_id)
        {
            if ($this->EE->c2r->del_region($region_id))
            {
                $this->EE->session->set_flashdata('message_success', lang('region_updated'));
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', lang('database_error'));
            }
        }

        // Redirect to the module home page
        $this->EE->functions->redirect($this->_module_link);
    }

    public function config()
    {

    }

    /**
     * Country Names
     * Used to get the country code to country name array
     * 
     * @access private
     * 
     * @return array Country / Country code array
     */
    private function _country_names()
    {
        if ( ! include(APPPATH.'config/countries.php'))
        {
            show_error(lang('countryfile_missing'));
        }

        return $countries;
    }
} 
// END CLASS

/* End of file mcp.country_to_region.php */
/* Location: ./system/expressionengine/third_party/country_to_region/mcp.country_to_region.php */