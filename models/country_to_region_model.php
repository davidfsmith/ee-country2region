<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Country To Region Model File
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
 * @link      http://www.wirewool.com/forms/
 * @see       http://www.wirewool.com/ee/country_to_region
 */
class Country_to_region_model
{

    // Set the table names
    private $_region_table   = 'c2r_region';
    private $_c2r_table      = 'c2r_region_to_country';

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
     * Get Regions
     * Used to get a list of all defined regsions
     * (TODO - Cater for paging later?)
     * 
     * @return array Object Resultset
     */
    public function get_regions()
    {
        $this->EE->db->order_by('region_name', 'ASC');
        $regions = $this->EE->db->get($this->_region_table)->result();
        
        return $regions;
    }

    /**
     * Get Region Count
     * Used to get the number of regions
     * 
     * @return int Number of regions
     */
    public function get_region_count()
    {
        return sizeof($this->get_regions());
    }

    /**
     * Get Region Data
     * Used to get the region data
     * 
     * @param int $region_id The Region ID
     * 
     * @return array Object Resultset
     */
    public function get_region_data($region_id)
    {
        return $this->EE->db->get_where($this->_region_table, array('region_id' => $region_id))->result();
    }

    /**
     * Get Region Name
     * Used to get the name of the region
     * 
     * @param int $region_id The Region ID
     * 
     * @return string Region name
     */
    public function get_region_name($region_id)
    {
        $region_data = $this->get_region_data($region_id);
        return $region_data[0]->region_name;
    }
    
    /**
     * Get Region Country Count
     * Used to get the number of countries in a region
     * 
     * @param int $region_id The Region ID
     * 
     * @return int Number of countries assigned to the region
     */
    public function get_region_country_count($region_id)
    {
        $this->EE->db->select()->from($this->_c2r_table)->where('region_id', $region_id);

        return $this->EE->db->count_all_results();
    }

    public function get_country_code_regions($country_code)
    {
        $region_ids = $this->EE->db->get_where($this->_c2r_table, array('country_code' => $country_code))->result();
        return $region_ids;
    }

    /**
     * Add Region
     * Used to add a country region
     * (TODO - Check to ensure the region name doesn't already exist?)
     * 
     * @param string $region_name   Name of the region to be created
     * @param int    $sub_region_id Id of a child region (optional)
     * 
     * @return int ID of newly created record
     */
    public function add_region($region_name, $sub_region_id = 0)
    {
        // Build the data
        $data = array(
            'sub_region_id' => $sub_region_id,
            'region_name'   => $region_name,
        ); 

        $this->EE->db->query($this->EE->db->insert_string($this->_region_table, $data));
        return $this->EE->db->insert_id();
    }

    /**
     * Add Countries to Region
     * Used to add countries to a region
     * 
     * @param int   $region_id     Region ID to associate countries with
     * @param array $country_codes Country codes that form the region
     * 
     * @return boolean true on success
     */
    public function add_countries_to_region($region_id, $country_codes)
    {
        // Build the data
        $data = array();
        foreach ($country_codes as $country)
        {
            $data[] = array(
                'region_id'     => $region_id,
                'country_code'  => $country,
            );
        }

        // Insert the data
        $this->EE->db->insert_batch($this->_c2r_table, $data);
        $row_count = $this->EE->db->affected_rows();

        // Check we have created some data
        return ($row_count > 0) ? true : false;
    }

    /**
     * Mod Region
     * Used to modify a region
     * (TODO - Check to ensure the region name doesn't already exist?)
     * 
     * @param int    $region_id     Id of the region to be updated
     * @param string $region_name   Name of the region
     * @param int    $sub_region_id Id of a child region (optional)
     * 
     * @return boolean true on success
     */
    public function mod_region($region_id, $region_name, $sub_region_id = 0)
    {
        // Build the data
        $data = array(
            'sub_region_id' => $sub_region_id,
            'region_name'   => $region_name,
        ); 

        $this->EE->db->where('region_id', $region_id);
        $this->EE->db->update($this->_region_table, $data);
        // $row_count = $this->EE->db->affected_rows();

        // Check we have deleted some data
        // return ($row_count > 0) ? true : false;
        return true;
    }

    /**
     * Get Countries for a Region
     * Used to get all countries for a region
     * 
     * @param int $region_id Region ID to associate countries with
     * 
     * @return array Object Resultset
     */
    public function get_region_countries($region_id)
    {
        return $this->EE->db->select('country_code')->get_where($this->_c2r_table, array('region_id' => $region_id))->result();
    }

    /**
     * Delete Countries from Region
     * Used to delete all countries for a region
     * 
     * @param int $region_id Region ID to associate countries with
     * 
     * @return boolean true on success
     */
    public function del_countries_from_region($region_id)
    {
        $this->EE->db->delete($this->_c2r_table, array('region_id' => $region_id));
        $row_count = $this->EE->db->affected_rows();

        // Check we have deleted some data
        return ($row_count > 0) ? true : false;
    }

    /**
     * Delete Region
     * Used to delete a region
     * 
     * @param int $region_id Region ID to associate countries with
     * 
     * @return boolean true on success
     */
    public function del_region($region_id)
    {
        // Delete the countries from the region first
        if ($this->del_countries_from_region($region_id))
        {
            $this->EE->db->delete($this->_region_table, array('region_id' => $region_id));
            $row_count = $this->EE->db->affected_rows();
        }

        // Check we have deleted some data
        return ($row_count > 0) ? true : false;
    }

}
// END CLASS

/* End of file country_to_region.php */
/* Location: ./system/expressionengine/third_party/country_to_region/modesl/country_to_region.php */