<?php

/**
 * Install / Uninstall and updates the modules
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

if ( ! defined('C2R_NAME'))
{
	define('C2R_NAME',			'Country To Region');
	define('C2R_MODULE_NAME',	'Country_to_region');
	define('C2R_VERSION',		'1.0');
}

$config['name'] 		= C2R_NAME;
$config['module_name']	= C2R_MODULE_NAME;
$config['version'] 		= C2R_VERSION;

//$config['nsm_addon_updater']['versions_xml'] = 'http://www.wirewool.com/ee/country_to_region/feed';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/country_to_region/config.php */