<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Frédéric MOHIER
// Purpose of file:
// ----------------------------------------------------------------------

// Class of the defined type
class PluginMapsExample extends CommonDBTM {


   // Should return the localized name of the type
   static function getTypeName($nb = 0) {
      return 'Example Type';
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

      $tab[1]['table']     = 'glpi_plugin_maps_examples';
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = __('Name');

      $tab[2]['table']     = 'glpi_plugin_maps_dropdowns';
      $tab[2]['field']     = 'name';
      $tab[2]['name']      = __('Dropdown');

      $tab[3]['table']     = 'glpi_plugin_maps_examples';
      $tab[3]['field']     = 'serial';
      $tab[3]['name']      = __('Serial number');
      $tab[3]['usehaving'] = true;
      $tab[3]['searchtype'] = 'equals';

      $tab[30]['table']     = 'glpi_plugin_maps_examples';
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
               if (PluginMapsProfile::haveRight("homepage", 'r')) {
                  return array(1 => __('Computers map', 'maps'));
               } else {
                  return '';
               }
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $DB;
      global $CFG_GLPI;

      switch ($item->getType()) {
         case 'Central' :
            if (! PluginMapsProfile::haveRight("homepage", 'r')) {
               return '';
            }
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
               `glpi_computers`.*
               , `glpi_computers`.`id` AS id_Host
               , `glpi_plugin_monitoring_hosts`.*
               , `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` AS id_monitoring
               , `glpi_locations`.`id` AS id_Location, `glpi_locations`.`building` AS Location
               , `glpi_states`.`id` AS id_State, `glpi_states`.`completename` AS status
               FROM `glpi_computers` 
               LEFT JOIN `glpi_locations` ON `glpi_locations`.`id` = `glpi_computers`.`locations_id` 
               LEFT JOIN `glpi_states` ON `glpi_states`.`id` = `glpi_computers`.`states_id` 
               LEFT JOIN `glpi_plugin_monitoring_hosts` ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`
               LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts` ON `glpi_computers`.`id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`
               WHERE`glpi_computers`.`entities_id` IN (".$_SESSION['glpiactiveentities_string'].") 
               ORDER BY `name`";
      // Toolbox::logInFile("maps", "Computer query : ".$query."\n");
      $result = $DB->query($query);

      $i=0;
      while ($data=$DB->fetch_array($result)) {
         // Default GPS coordinates ...
         $data['lat'] = 45.054485;
         $data['lng'] = 5.081413;
         if (! empty($data['Location'])) {
            $split = explode(',', $data['Location']);
            if (count($split) > 1) {
               // At least 2 elements, let us consider as GPS coordinates ...
               $data['lat'] = $split[0];
               $data['lng'] = $split[1];
            }
         }
         
         // Link to computer form ...
         $data['link'] = $CFG_GLPI['root_doc']."/front/computer.form.php?id=".$data['id_Host'];
         
         // If computer is used in monitoring plugin ...
         if (! empty($data['id_monitoring'])) {
            // Toolbox::logInFile("maps", "Computer monitoring id  : ".$data['id_monitoring']."\n");
            $data['monitoring'] = True;
            
            $query = "SELECT 
                     `glpi_plugin_monitoring_services`.*
                     FROM `glpi_plugin_monitoring_services` 
                     WHERE`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = (".$data['id_monitoring'].") 
                     ORDER BY `name`";
            // Toolbox::logInFile("maps", "Services query : ".$query."\n");
            $result2 = $DB->query($query);
            
            $data['services'] = Array();
            $j=0;
            while ($data2=$DB->fetch_array($result2)) {
               // Toolbox::logInFile("maps", "Service data : ".json_encode($data2)."\n");
               $data['services'][$j++] = $data2;
            }
         } else {
            $data['monitoring'] = False;
         }
         
         // Toolbox::logInFile("maps", "Computer data : ".json_encode($data)."\n");

         if ($i++ != 0) echo ',';
         
         echo json_encode($data);
      }
echo '
];

Ext.onReady(function(){
   debugJs=true; 

   Ext.Loader.load([ "http://maps.googleapis.com/maps/api/js?sensor=false&callback=mapInit" ], function() {
      apiLoaded=true;
      if (debugJs) console.log("Google maps API loaded ...");
   });
});
</script>';
            break;

         case 'Preference' :
            // Complete form display
            $data = plugin_version_maps();

            echo "<form action='Where to post form'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='3'>".$data['name']." - ".$data['version'];
            echo "</th></tr>";

/*
            echo "<tr class='tab_bg_1'><td>Name of the pref</td>";
            echo "<td>Input to set the pref</td>";
            echo "<td><input class='submit' type='submit' name='submit' value='submit'></td>";
            echo "</tr>";
*/
            echo "<tr class='tab_bg_1'><td>Not yet developed ...</td>";
            echo "</tr>";

            echo "</table>";
            echo "</form>";
            break;

         case 'Notification' :
            _e("Plugin mailing action", 'maps');
            break;

         case 'ComputerDisk' :
         case 'Supplier' :
            if ($tabnum==1) {
               _e('First tab of Plugin maps', 'maps');
            } else {
               _e('Second tab of Plugin maps', 'maps');
            }
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

   // Parm contains begin, end and who
   // Create data to be displayed in the planning of $parm["who"] or $parm["who_group"] between $parm["begin"] and $parm["end"]
   static function populatePlanning($parm) {

      // Add items in the output array
      // Items need to have an unique index beginning by the begin date of the item to display
      // needed to be correcly displayed
      $output = array();
      $key = $parm["begin"]."$$$"."plugin_maps1";
      $output[$key]["begin"]  = date("Y-m-d 17:00:00");
      $output[$key]["end"]    = date("Y-m-d 18:00:00");
      $output[$key]["name"]   = __("test planning maps 1", 'maps');
      // Specify the itemtype to be able to use specific display system
      $output[$key]["itemtype"] = "PluginMapsExample";
      // Set the ID using the ID of the item in the database to have unique ID
      $output[$key][getForeignKeyFieldForItemType('PluginMapsExample')] = 1;
      return $output;
   }

   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    * @param $who ID of the user (0 if all)
    * @param $type position of the item in the time block (in, through, begin or end)
    * @param $complete complete display (more details)
    *
    * @return Nothing (display function)
    **/
   static function displayPlanningItem(array $val, $who, $type="", $complete=0) {

      // $parm["type"] say begin end in or from type
      // Add items in the items fields of the parm array
      switch ($type) {
         case "in" :
            //TRANS: %1$s is the start time of a planned item, %2$s is the end
            printf(__('From %1$s to %2$s :'),
                   date("H:i",strtotime($val["begin"])), date("H:i",strtotime($val["end"]))) ;
            break;

         case "through" :
            echo Html::resume_text($val["name"],80);
            break;

         case "begin" :
            //TRANS: %s is the start time of a planned item
            printf(__('Start at %s:'), date("H:i", strtotime($val["begin"]))) ;
            break;

         case "end" :
            //TRANS: %s is the end time of a planned item
            printf(__('End at %s:'), date("H:i", strtotime($val["end"]))) ;
         break;
      }
      echo "<br>";
      echo Html::resume_text($val["name"],80);
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
}
?>