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

define ("PLUGIN_MAPS_VERSION","0.84+0.3");

// Init the hooks of the plugins -Needed
function plugin_init_maps() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   // Map class
   Plugin::registerClass('PluginMapsMap',
                         array('notificationtemplates_types' => true,
                               'addtabon'                    => array('Central')));

   // Plugin profile management class
   Plugin::registerClass('PluginMapsProfile',
        array('addtabon' => array('Profile')));
        
   // Display a menu entry ?
   if (isset($_SESSION["glpi_plugin_maps_profile"])) { // Right set in change_profile hook
      // Plugins main menu ...
      $PLUGIN_HOOKS['menu_entry']['maps'] = 'front/map.php';
      $PLUGIN_HOOKS['menu_entry']['maps'] = true;
      
      // No menu in helpdesk interface ...
      $PLUGIN_HOOKS["helpdesk_menu_entry"]['maps'] = false;
   }

   // Configure current profile ...
   $PLUGIN_HOOKS['change_profile']['maps'] = array('PluginMapsProfile','changeprofile');
   
   // Config page ... actually, simple redirect to plugins page.
   if (Session::haveRight('config','w')) {
      $PLUGIN_HOOKS['config_page']['maps'] = 'config.php';
   }

   //redirect
   // Simple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_2 (ID 2 du form)
   // $PLUGIN_HOOKS['redirect_page']['maps'] = 'maps.form.php';
   // Multiple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_one_2 (ID 2 du form)
   // Multiple redirect : http://localhost/glpi/index.php?redirect=plugin_maps_two_2 (ID 2 du form)
   // $PLUGIN_HOOKS['redirect_page']['maps']['one'] = 'example.form.php';
   // $PLUGIN_HOOKS['redirect_page']['maps']['two'] = 'example2.form.php';

   // Add specific files to add to the header : javascript or css
   $PLUGIN_HOOKS['add_javascript']['maps'] = 'javascript/maps.js';
   $PLUGIN_HOOKS['add_css']['maps']        = 'javascript/maps.css';

   // All plugins are initialized ... nothing to do.
   // $PLUGIN_HOOKS['post_init']['maps'] = 'plugin_maps_postinit';
   // Plugin status
   $PLUGIN_HOOKS['status']['maps'] = 'plugin_maps_Status';
   
   // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
   $PLUGIN_HOOKS['csrf_compliant']['maps'] = true;
}


// Get the name and the version of the plugin
function plugin_version_maps() {

   return array('name'           => 'Plugin Maps',
                'version'        => PLUGIN_MAPS_VERSION,
                'author'         => 'Frédéric MOHIER',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/maps',
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_maps_check_prerequisites() {

   // GLPI must be at least 0.84 ...
   if (version_compare(GLPI_VERSION,'0.84','lt')) {
      echo "This plugin requires GLPI >= 0.84";
      return false;
   }
   return true;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_maps_check_config($verbose=false) {
   if (true) {
      // Always true ...
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'maps');
   }
   return false;
}
?>
