<?php
/*
   ------------------------------------------------------------------------
   Plugin Maps for GLPI
   Copyright (C) 2013 by IPM France - Frédéric MOHIER.

   https://forge.indepnet.net/projects/maps/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Maps project.

   Plugin Maps for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Maps for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Maps. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Maps for GLPI
   @author    Frédéric MOHIER
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2013 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Frédéric MOHIER
// Purpose of file: Plugin setup
// ----------------------------------------------------------------------

// Init the hooks of the plugins -Needed
function plugin_init_maps() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   // Params : plugin name - string type - ID - Array of attributes
   Plugin::registerClass('PluginMapsDropdown');

   Plugin::registerClass('PluginMapsExample',
                         array('notificationtemplates_types' => true,
                               'addtabon'                    => array('Central', 'Preference')));

   Plugin::registerClass('PluginMapsProfile',
        array('addtabon' => array('Profile')));
        
   // Display a menu entry ?
   if (isset($_SESSION["glpi_plugin_maps_profile"])) { // Right set in change_profile hook
      $PLUGIN_HOOKS['menu_entry']['maps'] = 'front/example.php';

      $PLUGIN_HOOKS['menu_entry']['maps'] = true;
      
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['title'] = "Search";
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['page']  = '/plugins/maps/front/example.php';
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['links']['search'] = '/plugins/maps/front/example.php';
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['links']['add']    = '/plugins/maps/front/example.form.php';
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['links']['config'] = '/plugins/maps/index.php';
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['links']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_showall.png' title='".__s('Show all')."' alt='".__s('Show all')."'>"] = '/plugins/maps/index.php';
      $PLUGIN_HOOKS['submenu_entry']['maps']['options']['optionname']['links'][__s('Test link', 'maps')] = '/plugins/maps/index.php';

      $PLUGIN_HOOKS["helpdesk_menu_entry"]['maps'] = true;
   }

   // Configure current profile ...
   $PLUGIN_HOOKS['change_profile']['maps'] = array('PluginMapsProfile','changeprofile');
   
/*
   // Config page ... not needed.
   if (Session::haveRight('config','w')) {
      $PLUGIN_HOOKS['config_page']['maps'] = 'config.php';
   }
*/

/*
   // Item action event // See define.php for defined ITEM_TYPE
   $PLUGIN_HOOKS['pre_item_update']['maps'] = array('Computer' => 'plugin_pre_item_update_maps');
   $PLUGIN_HOOKS['item_update']['maps']     = array('Computer' => 'plugin_item_update_maps');

   $PLUGIN_HOOKS['item_empty']['maps']     = array('Computer' => 'plugin_item_empty_maps');

   // Example using a method in class
   $PLUGIN_HOOKS['pre_item_add']['maps']    = array('Computer' => array('PluginMapsExample',
                                                                           'pre_item_add_computer'));
   $PLUGIN_HOOKS['post_prepareadd']['maps'] = array('Computer' => array('PluginMapsExample',
                                                                           'post_prepareadd_computer'));
   $PLUGIN_HOOKS['item_add']['maps']        = array('Computer' => array('PluginMapsExample',
                                                                           'item_add_computer'));

   $PLUGIN_HOOKS['pre_item_delete']['maps'] = array('Computer' => 'plugin_pre_item_delete_maps');
   $PLUGIN_HOOKS['item_delete']['maps']     = array('Computer' => 'plugin_item_delete_maps');

   // Example using the same function
   $PLUGIN_HOOKS['pre_item_purge']['maps'] = array('Computer' => 'plugin_pre_item_purge_maps',
                                                      'Phone'    => 'plugin_pre_item_purge_maps');
   $PLUGIN_HOOKS['item_purge']['maps']     = array('Computer' => 'plugin_item_purge_maps',
                                                      'Phone'    => 'plugin_item_purge_maps');

   // Example with 2 different functions
   $PLUGIN_HOOKS['pre_item_restore']['maps'] = array('Computer' => 'plugin_pre_item_restore_maps',
                                                         'Phone'   => 'plugin_pre_item_restore_maps2');
   $PLUGIN_HOOKS['item_restore']['maps']     = array('Computer' => 'plugin_item_restore_maps');

   // Add event to GLPI core itemtype, event will be raised by the plugin.
   // See plugin_maps_uninstall for cleanup of notification
   $PLUGIN_HOOKS['item_get_events']['maps']
                                 = array('NotificationTargetTicket' => 'plugin_maps_get_events');

   // Add datas to GLPI core itemtype for notifications template.
   $PLUGIN_HOOKS['item_get_datas']['maps']
                                 = array('NotificationTargetTicket' => 'plugin_maps_get_datas');

   $PLUGIN_HOOKS['item_transfer']['maps'] = 'plugin_item_transfer_maps';
*/

   //redirect
   // Simple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_2 (ID 2 du form)
   // $PLUGIN_HOOKS['redirect_page']['maps'] = 'maps.form.php';
   // Multiple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_one_2 (ID 2 du form)
   // Multiple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_two_2 (ID 2 du form)
   $PLUGIN_HOOKS['redirect_page']['maps']['one'] = 'example.form.php';
   $PLUGIN_HOOKS['redirect_page']['maps']['two'] = 'example2.form.php';

   // Massive Action definition
   // $PLUGIN_HOOKS['use_massive_action']['maps'] = 1;

   // $PLUGIN_HOOKS['assign_to_ticket']['maps'] = 1;

   // Add specific files to add to the header : javascript or css
   $PLUGIN_HOOKS['add_javascript']['maps'] = 'maps.js';
   $PLUGIN_HOOKS['add_css']['maps']        = 'maps.css';

   // request more attributes from ldap
   //$PLUGIN_HOOKS['retrieve_more_field_from_ldap']['maps']="plugin_retrieve_more_field_from_ldap_maps";

   // Retrieve others datas from LDAP
   //$PLUGIN_HOOKS['retrieve_more_data_from_ldap']['maps']="plugin_retrieve_more_data_from_ldap_maps";

   $PLUGIN_HOOKS['post_init']['maps'] = 'plugin_maps_postinit';

   $PLUGIN_HOOKS['status']['maps'] = 'plugin_maps_Status';
   
   // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
   $PLUGIN_HOOKS['csrf_compliant']['maps'] = true;
}


// Get the name and the version of the plugin - Needed
function plugin_version_maps() {

   return array('name'           => 'Plugin Maps',
                'version'        => '0.2',
                'author'         => 'Frédéric MOHIER',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/maps',
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_maps_check_prerequisites() {

   // Strict version check (could be less strict, or could allow various version)
   if (version_compare(GLPI_VERSION,'0.84','lt') /*|| version_compare(GLPI_VERSION,'0.84','gt')*/) {
      echo "This plugin requires GLPI >= 0.84";
      return false;
   }
   return true;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_maps_check_config($verbose=false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'maps');
   }
   return false;
}
?>
