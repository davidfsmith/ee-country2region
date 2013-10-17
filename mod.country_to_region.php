<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
                        'pi_name'           => 'Country To Region',
                        'pi_version'        => '1.0',
                        'pi_author'         => 'David Smith',
                        'pi_author_url'     => 'http://www.wirewool.com/',
                        'pi_description'    => 'Maps country codes to regions',
                        'pi_usage'          => Country_to_region::usage()
                    );

/**
 * Country to Region - Front End File
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
class Country_to_region {


    public $return_data = '';

    /**
     * Constructor
     * 
     * @access  public
     */
    public function __construct()
    {
        $this->EE =& get_instance();

        // Load the language file
        $this->EE->lang->loadfile('country_to_region');
        
        // Load in the IP 2 Nation module (if not available we need to display an error message)
        if ( array_key_exists('ip_to_nation', $this->EE->addons->get_installed('modules')))
        {
            // Load the models
            include_once PATH_MOD.'ip_to_nation/models/ip_to_nation_data.php';
            $this->EE->ip_data = new Ip_to_nation_data();
            $this->EE->load->model('country_to_region_model', 'c2r');
        }
        else
        {
            return $this->EE->output->show_user_error('general', $this->EE->lang->line('ip2nation_err_message'));
        }

        // Set the default country (@todo make this a config variable)
        $this->default_country = 'gb';

        // A home for debug
        $this->debug = array();
    }

    /**
     * Get Regions
     *
     * @return string '|' delimited string of the "prefix" region_names "suffix"
     */
    public function get_channel_regions()
    {
        // Get the user data....
        $user = $this->_get_user_settings();

        // Get the pre and suffixes
        $prefix = trim($this->EE->TMPL->fetch_param('prefix'));
        $suffix = trim($this->EE->TMPL->fetch_param('suffix'));

        // Get all regions with the country code and convert into channel names using the prefix and suffix
        foreach ($user['region_names'] as $region)
        {
            $region_names[] = $prefix.str_replace(' ', '_', strtolower($region)).$suffix;
        }

        // Join the regions together with a '|' for multi channel goodness
        return $this->return_data = implode('|', $region_names);
    }

    public function get_region_name()
    {
        // Get the user data....
        $user = $this->_get_user_settings();
        
        // Return title cased country with the _ swapped for a <space>
        return $this->return_data = ucwords(str_replace('_', ' ', $user['region_names'][0]));
    }

    public function get_current_region_fieldname()
    {
        // Get the user data....
        $user = $this->_get_user_settings();
        
        // Return title cased country with the _ swapped for a <space>
        return $this->return_data = str_replace('-', '_', $this->_convert_region($user['region_names'][0]));
    }

    public function get_category_id()
    {
        // Get the user data....
        $user = $this->_get_user_settings();

        // Get the category group id
        $group_id = trim($this->EE->TMPL->fetch_param('group_id'));

        // Get all of the regions with the country code and get their matching category_id
        foreach ($user['region_names'] as $region)
        {
            // Deal with the fact the region might have a space or a hyphen (consistency in code issue)
            $region = $this->_convert_region($region);

            // Build the query
            $this->EE->db->select('cat_id');
            $this->EE->db->from('exp_categories');
            $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
            $this->EE->db->where('cat_url_title', $region);

            if ($results = $this->EE->db->get()) {
                $category_id[] = $results->row('cat_id');
            } else {
                // Gone wrong :-(
            }
        }

        // Join the id's together with a '|' for multi channel goodness
        return $this->return_data = implode('|', $category_id);
    }

    public function set_regions()
    {
        // Get the user data
        $user = $this->_get_user_settings();

        // Set the cookies (this will only work if the user has accepted the cookie policy)
        $this->EE->functions->set_cookie('c2r_country', $user['country'], 3600);
        $this->EE->functions->set_cookie('c2r_region', $user['region'], 3600);

        return;
    }

    /**
     * 
     * 
     */
    public function list_regions()
    {
        // Need to get the regions and then build an array using the template
        $regions = $this->EE->c2r->get_regions();

        // Get the user data
        $user = $this->_get_user_settings();

        // Loop through the regions, set the selected from the cookie and send to the template parser (happy days)
        $variables = array();
        foreach ($regions as $region) {
            $variables[] = array(
                'region_name'       => $region->region_name,
                'region_name_link'  => strtolower(str_replace(' ', '_', $region->region_name)),
                'selected'          => (strtolower(str_replace(' ', '_', $region->region_name)) === strtolower(str_replace(' ', '_', $user['region_names'][0]))) ? ' selected' : '',
            );
        }

        // Return the built block
        return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
    }

    private function _convert_region($region)
    {
        if (strpos($region, '_') !== false)
        {
            $region = strtolower(str_replace('_', '-', $region));
        }
        else
        {
            $region = strtolower(str_replace(' ', '-', $region));
        }

        return $region;
    }

    private function _get_user_settings()
    {
        // Build the user information from all sources
        $user = array(
            'ip'                => trim($this->EE->session->userdata('ip_address')),
            'debug_country'     => $this->EE->TMPL->fetch_param('debug'),
            'cookie_region'     => $this->EE->input->cookie('c2r_region'),
            'cookie_country'    => $this->EE->input->cookie('c2r_country'),
        );

        // Get the country code from the IP
        $user['ip_country'] = $this->EE->ip_data->find($user['ip']);

        // Set the fall back ?
        if (!$user['debug_country'] && !$user['ip_country'] && !$user['cookie_country'])
        {
            // Failed all checks, revert to the default
            $user['country'] = $this->default_country;
            $this->debug[] = 'Setting to default country:'.$user['country'];
        }
        elseif (!$user['debug_country'] && !$user['ip_country'])
        {
            // Setting the country using the cookie
            $user['country'] = $user['cookie_country'];
            $this->debug[] = 'Setting to cookie country:'.$user['country'];
        }
        elseif (!$user['debug_country'])
        {
            // Setting the country using the IP
            $user['country'] = $user['ip_country'];
            $this->debug[] = 'Setting to IP country:'.$user['country'];
        }
        else
        {
            // Set the cookie using the debug address
            $user['country'] = $user['debug_country'];
            $this->debug[] = 'Setting to debug country:'.$user['country'];
        }

        // Add in the cookie region if the cookie country or region is set
        if ($user['cookie_region'])
        {
            $user['region_names'][] = str_replace('+', ' ', $user['cookie_region']);
        }
        else
        {
            // Get the regions IDs
            $user['region_ids'] = $this->EE->c2r->get_country_code_regions($user['country']);

            // Get the region names
            foreach ($user['region_ids'] as $region)
            {
                $user['region_names'][] = $this->EE->c2r->get_region_name($region->region_id);
            }
        }
        
        return $user;
    }


    public function debug()
    {
        // var_dump($this->debug);
        // exit;
        // return 'DEBUG:'.implode('<br/>\n', $this->debug);
    }

    /**
     * Usage
     *
     * Plugin Usage
     *
     * @access  public
     * @return  string
     */
    public function usage()
    {
        ob_start(); 
        ?>

        Returns the users region based on their IP address, uses IP 2 Nation to map the IP address to a country
        
        Single Tag
        {exp:country_to_region ip="{ip_address}"}

        Double Tag
        {exp:country_to_region}{ip_address}{/exp:country_to_region}

        Returns the regions the visitors country is assigned to as a | delimited list

        Get Cookie

        Set Cookie

        List Regions
        Loop through the regions

        Testing / Debugging
        To test the country / region mapping an optional "debug" parameter can be used to set the country code
        - The debug parameter is only used if an invalid IP address is found

        Version 1.0
        ******************
        - Initial version of plugin

        Todo
        ******************
        - Tidy up the region modification code (Needs more DRY)
        - Check for duplicate region names
        - Allow for nested regions to be created
        - Move debug feature into control panel
        - Add configurable flag images URL to control panel
        - Add in default geo-graphical regions as a load option
        - Check IP 2 Nation (Dependancy) is installed on activation
        - Move this usage info into the language file ;-)
        - Set / Get cookies and use this as a preference over the IP Address
        - Set a default country for the user in the admin
        - Make the debug work

        <?php
        $buffer = ob_get_contents();
    
        ob_end_clean(); 

        return $buffer;
    }

}
// END CLASS

/* End of file pi.country_to_region.php */
/* Location: ./system/expressionengine/third_party/country_to_region/pi.country_to_region.php */