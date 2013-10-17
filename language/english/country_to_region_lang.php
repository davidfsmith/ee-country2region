<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Country to Region - Languge File - English
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
$lang = array(

// Required for MODULES page

'country_to_region_module_name'			=> 'Country To Region',
'country_to_region_module_description'  => 'Map users IP address to a region for localised channels',

//----------------------------------------

// Additional Key => Value pairs go here

// END
'add_region'		=> 'Add Region',
'del_region'		=> 'Delete Region',
'mod_region'		=> 'Modify Region',
'config'            => 'C2R Config',
'country_code'		=> 'Country Code',
'country_name'		=> 'Name',
'country_flag'		=> 'Country Flag',
'region_name'		=> 'Region Name',
'region_updated'	=> 'Region Updated',
'country_count'     => '# of Countries',
'database_error'    => 'There was a problem updating the region',
'region_name_notes' => 'Name must be unique - min 5 chars',

'msg_no_regions_defined'	=> 'There are currently no regions defined',
'ip2nation_err_message'     => 'Error: IP 2 Nation module not enabled! (Required by Country To Region plugin)',

// Plugin text //
'invalid_ip_address'    => 'Invalid IP Address',

);

/* End of file country_to_region_lang.php */
/* Location: ./system/expressionengine/third_party/country_to_region/language/english/country_to_region_lang.php */