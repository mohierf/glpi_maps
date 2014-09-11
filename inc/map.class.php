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
// Purpose of file: Map class
// ----------------------------------------------------------------------

// Class of the defined type
class PluginMapsMap extends CommonDBTM {


   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'Map Type';
   }


   static function canCreate() {

      if (isset($_SESSION["glpi_plugin_maps_profile"])) {
         return ($_SESSION["glpi_plugin_maps_profile"]['maps'] == 'w');
      }
      return false;
   }


   static function canView() {

      if (isset($_SESSION["glpi_plugin_maps_profile"])) {
         return ($_SESSION["glpi_plugin_maps_profile"]['maps'] == 'w'
                 || $_SESSION["glpi_plugin_maps_profile"]['maps'] == 'r');
      }
      return false;
   }


   function getSearchOptions() {

      $tab = array();
      $tab['common'] = "Header Needed";

      $tab[1]['table']     = 'glpi_plugin_maps_maps';
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = __('Name');

      $tab[2]['table']     = 'glpi_plugin_maps_dropdowns';
      $tab[2]['field']     = 'name';
      $tab[2]['name']      = __('Dropdown');

      $tab[3]['table']     = 'glpi_plugin_maps_maps';
      $tab[3]['field']     = 'serial';
      $tab[3]['name']      = __('Serial number');
      $tab[3]['usehaving'] = true;
      $tab[3]['searchtype'] = 'equals';

      $tab[30]['table']     = 'glpi_plugin_maps_maps';
      $tab[30]['field']     = 'id';
      $tab[30]['name']      = __('ID');

      return $tab;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Profile' :
            case 'Phone' :
            case 'ComputerDisk' :
            case 'Supplier' :
            case 'Computer' :
            case 'Preference':
            case 'Notification':
               break;
            case 'Central' :
               if (PluginMapsProfile::haveRight("centralpage", 'r')) {
                  return array(1 => __('Map', 'maps'));
               } else {
                  return '';
               }
               break;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $DB;
      global $CFG_GLPI;

      switch ($item->getType()) {
         case 'Central' :
            if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Request to show map ...\n");
            
            if (! PluginMapsProfile::haveRight("centralpage", 'r')) {
               if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "No right in profile to show map\n");
               return '';
            }
            
            PluginMapsMap::showMap();
            break;

         default :
            //TRANS: %1$s is a class name, %2$d is an item ID
            printf(__('Plugin maps CLASS=%1$s id=%2$d', 'maps'), $item->getType(), $item->getField('id'));
            break;
      }
      return true;
   }

   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'serial' :
            return "S/N: ".$values[$field];
      }
      return '';
   }

   /**
    * Get an history entry message
    *
    * @param $data Array from glpi_logs table
    *
    * @since GLPI version 0.84
    *
    * @return string
   **/
   static function getHistoryEntry($data) {

      switch($data['linked_action'] - Log::HISTORY_PLUGIN) {
         case 0:
            return __('History from plugin maps', 'maps');
      }

      return '';
   }

   static function showMap() {
      global $DB;
      global $CFG_GLPI;

      if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Starting to show map ...\n");
      
      echo '<table class="tab_cadre_fixe">';
      echo '<tr class="tab_bg_1"><th colspan="3">';
      echo __("Map of the computers ...", 'maps');
      echo '</th></tr>';

      // echo '<tr class="tab_bg_1">';
      // echo '<td><input id="txt_latlng" type="text" name="name" value="" size="30"/></td>';
      // echo '<td><input id="txt_latlng" type="text" name="name" value="" size="30"/></td>';
      // echo '<td><input id="txt_latlng" type="text" name="name" value="" size="30"/></td>';
      // echo '</tr>';

      echo '<tr class="tab_bg_1">
         <!-- HTML map container -->
         <td class="map_container" colspan="3">
           <div id="map">
             <p>Loading map ...</p>
           </div>
         </td>
      </tr>
      </table>';


      echo '<script>
         var hostsInfo = [';
         
         $query = "SELECT 
               `glpi_computers`.`name` AS name
               , `glpi_computers`.`id` AS id_Host
               , `glpi_plugin_monitoring_hosts`.*
               , filterQuery.`id` AS id_monitoring
               , `glpi_locations`.`building` AS gps
               , `glpi_locations`.`name` AS short_location
               , `glpi_locations`.`completename` AS location
               , `glpi_states`.`id` AS id_State, `glpi_states`.`completename` AS status
               FROM `glpi_computers` 
               LEFT JOIN `glpi_locations` ON `glpi_locations`.`id` = `glpi_computers`.`locations_id` 
               LEFT JOIN `glpi_states` ON `glpi_states`.`id` = `glpi_computers`.`states_id` 
               LEFT JOIN `glpi_plugin_monitoring_hosts` ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`
               LEFT JOIN (SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts` GROUP BY `items_id`) filterQuery ON `glpi_computers`.`id` = filterQuery.`items_id` 
               WHERE `glpi_computers`.`entities_id` IN (".$_SESSION['glpiactiveentities_string'].") 
               ORDER BY `name`";
      if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Requesting computers to show : $query\n");
      
      $result = $DB->query($query);

      $i=0;
      while ($data=$DB->fetch_array($result)) {
         // Default GPS coordinates ...
         $data['lat'] = 45.054485;
         $data['lng'] = 5.081413;
         if (! empty($data['gps'])) {
            $split = explode(',', $data['gps']);
            if (count($split) > 1) {
               // At least 2 elements, let us consider as GPS coordinates ...
               $data['lat'] = trim($split[0]);
               $data['lng'] = trim($split[1]);
            }
            unset ($data['gps']);
         }
         
         if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Computer ".$data['id_Host'].", GPS: ".$data['lat']." , ".$data['lng']."\n");
         
         // Link to computer form ...
         $data['link'] = $CFG_GLPI['root_doc'].
            "/front/computer.form.php?id=".$data['id_Host'];
         // Link to computer services ...
         $data['linkServices'] = $CFG_GLPI['root_doc'].
            "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset".
            "&field[0]=20&searchtype[0]=equals&contains[0]=".$data['id_Host'].
            "&itemtype=PluginMonitoringService&start=0'";

         // If computer is used in monitoring plugin ...
         if (! empty($data['id_monitoring'])) {
            if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Computer is monitored.\n");
            $data['monitoring'] = True;
            

            $query2 = "SELECT 
               `glpi_plugin_monitoring_components`.`name`,
               `glpi_plugin_monitoring_components`.`description`,
               `glpi_plugin_monitoring_services`.`state`,
               `glpi_plugin_monitoring_services`.`state_type`,
               `glpi_plugin_monitoring_services`.`event`,
               `glpi_plugin_monitoring_services`.`last_check`,
               `glpi_plugin_monitoring_services`.`is_acknowledged`,
               `glpi_plugin_monitoring_services`.`acknowledge_comment`
               FROM `glpi_plugin_monitoring_services` 
               INNER JOIN `glpi_plugin_monitoring_components`
               ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
               WHERE `glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` IN (SELECT id FROM `glpi_plugin_monitoring_componentscatalogs_hosts` WHERE `glpi_plugin_monitoring_componentscatalogs_hosts`.items_id ='".$data['id_Host']."') 
               ORDER BY `name`";
            if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Requesting services for computer ".$data['id_Host']." : $query2\n");

            $result2 = $DB->query($query2);
            
            $data['services'] = Array();
            $j=0;
            while ($data2=$DB->fetch_array($result2)) {
               // if (PluginMapsProfile::haveRight('logs','w')) Toolbox::logInFile("maps", "Service ".$data2['name']." is ".$data2['state'].", state : ".$data2['event']."\n");
               $data['services'][$j++] = $data2;
            }
         } else {
            $data['monitoring'] = False;
         }

         if ($i++ != 0) echo ',';
         
         echo json_encode($data);
      }

      echo '
      ];';
      
      echo '
      Ext.onReady(function(){
         // Overloading global variables defined in the maps.js script ...
         // debugJs:
         //    true/false to activate debug in console.log
            debugJs=false; 
         // Map layer : 
         // - "" to use google maps tiles
         // - "OSM" to use Open street map tiles
            mapLayer="";
         // Directory where to find images/icons used on map 
            imagesDir = "' . $CFG_GLPI['root_doc']."/plugins/maps/pics" . '";
         // Directory where to find extra scripts loaded
         scriptsDir = "' . $CFG_GLPI['root_doc']."/plugins/maps/javascript" . '";

         Ext.Loader.load([ "http://maps.googleapis.com/maps/api/js?sensor=false&callback=mapInit" ], function() {
            apiLoaded=true;
            if (debugJs) console.log("Google maps API loaded ...");
         });
      });
      </script>';
   }
}
?>