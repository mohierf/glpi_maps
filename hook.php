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
// Purpose of file: Plugin hooks
// ----------------------------------------------------------------------

// Install process for plugin : need to return true if succeeded
function plugin_maps_install() {
   global $DB;

   // if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Plugin installation\n");
   
   // This table is not yet necessary for the plugin ... intended for future features.
/*
   if (!TableExists("glpi_plugin_maps_maps")) {
      $query = "CREATE TABLE `glpi_plugin_maps_maps` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `serial` varchar(255) collate utf8_unicode_ci NOT NULL,
                  `is_deleted` tinyint(1) NOT NULL default '0', 
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_maps_maps". $DB->error());
   }
*/

   if (!TableExists("glpi_plugin_maps_profiles")) {
      $query = "CREATE TABLE `glpi_plugin_maps_profiles` (
                  `id` int(11) NOT NULL DEFAULT '0',
                  `logs` char(1) collate utf8_unicode_ci default '',
                  `centralpage` char(1) collate utf8_unicode_ci default '',
                  `mainpage` char(1) collate utf8_unicode_ci default '',
                PRIMARY KEY  (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_maps_profiles". $DB->error());

      if (isset($_SESSION['glpiactiveprofile']['id'])) {
         $query = "INSERT INTO `glpi_plugin_maps_profiles`
                          (`id`, `logs`, `centralpage`, `mainpage`)
                   VALUES (".$_SESSION['glpiactiveprofile']['id'].", 'w', 'w', 'w')";

         $DB->query($query) or die("error populate glpi_plugin_maps_profiles". $DB->error());
      }
   }

   return true;
}


// Uninstall process for plugin : need to return true if succeeded
function plugin_maps_uninstall() {
   global $DB;

   if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Plugin uninstallation\n");
   
   // Old version (< 1.0) tables
   $notif = new Notification();
   $options = array('itemtype' => 'Ticket',
                    'event'    => 'plugin_example',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   if (TableExists("glpi_plugin_maps_example")) {
      $query = "DROP TABLE `glpi_plugin_maps_example`";
      $DB->query($query) or die("error deleting glpi_plugin_maps_example");
   }
   if (TableExists("glpi_plugin_maps_examples")) {
      $query = "DROP TABLE `glpi_plugin_maps_examples`";
      $DB->query($query) or die("error deleting glpi_plugin_maps_examples");
   }
   if (TableExists("glpi_plugin_maps_dropdowns")) {
      $query = "DROP TABLE `glpi_plugin_maps_dropdowns`;";
      $DB->query($query) or die("error deleting glpi_plugin_maps_dropdowns");
   }
   if (TableExists("glpi_plugin_maps_profiles")) {
      $query = "DROP TABLE `glpi_plugin_maps_profiles`;";
      $DB->query($query) or die("error deleting glpi_plugin_maps_profiles");
   }

   // Current version tables
   $notif = new Notification();
   $options = array('itemtype' => 'Ticket',
                    'event'    => 'plugin_maps',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   if (TableExists("glpi_plugin_maps_maps")) {
      $query = "DROP TABLE `glpi_plugin_maps_maps`";
      $DB->query($query) or die("error deleting glpi_plugin_maps_maps");
   }
   if (TableExists("glpi_plugin_maps_profiles")) {
      $query = "DROP TABLE `glpi_plugin_maps_profiles`;";
      $DB->query($query) or die("error deleting glpi_plugin_maps_profiles");
   }
   
   return true;
}


function plugin_maps_postinit() {
   global $CFG_GLPI;

   // if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Plugin postinit\n");
   
   // All plugins are initialized, so all types are registered
   foreach ($CFG_GLPI["infocom_types"] as $type) {
      // do something
   }
}


// Check to add to status page
function plugin_maps_Status($param) {
   // Do checks (no check for example)
   $ok = true;
   echo "Maps plugin: ";
   if ($ok) {
      echo "_OK";
   } else {
      echo "_PROBLEM";
      // Only set ok to false if trouble (global status)
      $param['ok'] = false;
   }
   echo "\n";
   
   if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Plugin status is '$param'\n");
   
   return $param;
}
?>