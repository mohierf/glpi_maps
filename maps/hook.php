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

// Define dropdown relations
function plugin_maps_getDatabaseRelations() {
   return array("glpi_plugin_maps_dropdowns" => array("glpi_plugin_maps" => "plugin_maps_dropdowns_id"));
}


// Define Dropdown tables to be manage in GLPI :
function plugin_maps_getDropdown() {
   // Table => Name
   return array('PluginMapsDropdown' => __("Plugin Maps Dropdown", 'maps'));
}



////// SEARCH FUNCTIONS ///////(){

// Define Additionnal search options for types (other than the plugin ones)
function plugin_maps_getAddSearchOptions($itemtype) {

   $sopt = array();
   if ($itemtype == 'Computer') {
         // Just for example, not working...
         $sopt[1001]['table']     = 'glpi_plugin_maps_dropdowns';
         $sopt[1001]['field']     = 'name';
         $sopt[1001]['linkfield'] = 'plugin_maps_dropdowns_id';
         $sopt[1001]['name']      = __('Maps plugin', 'maps');
   }
   return $sopt;
}

// See also PluginMapsExample::getSpecificValueToDisplay()
function plugin_maps_giveItem($type,$ID,$data,$num) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_maps_examples.name" :
         $out = "<a href='".Toolbox::getItemTypeFormURL('PluginMapsExample')."?id=".$data['id']."'>";
         $out .= $data["ITEM_$num"];
         if ($_SESSION["glpiis_ids_visible"] || empty($data["ITEM_$num"])) {
            $out .= " (".$data["id"].")";
         }
         $out .= "</a>";
         return $out;
   }
   return "";
}


function plugin_maps_displayConfigItem($type, $ID, $data, $num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   // Example of specific style options
   // No need of the function if you do not have specific cases
   switch ($table.'.'.$field) {
      case "glpi_plugin_maps_examples.name" :
         return " style=\"background-color:#DDDDDD;\" ";
   }
   return "";
}


function plugin_maps_addDefaultJoin($type, $ref_table, &$already_link_tables) {

   // Example of default JOIN clause
   // No need of the function if you do not have specific cases
   switch ($type) {
//       case "PluginMapsExample" :
      case "MyType" :
         return Search::addLeftJoin($type, $ref_table, $already_link_tables,
                                    "newtable", "linkfield");
   }
   return "";
}


function plugin_maps_addDefaultSelect($type) {

   // Example of default SELECT item to be added
   // No need of the function if you do not have specific cases
   switch ($type) {
//       case "PluginMapsExample" :
      case "MyType" :
         return "`mytable`.`myfield` = 'myvalue' AS MYNAME, ";
   }
   return "";
}


function plugin_maps_addDefaultWhere($type) {

   // Example of default WHERE item to be added
   // No need of the function if you do not have specific cases
   switch ($type) {
//       case "PluginMapsExample" :
      case "MyType" :
         return " `mytable`.`myfield` = 'myvalue' ";
   }
   return "";
}


function plugin_maps_addLeftJoin($type, $ref_table, $new_table, $linkfield) {

   // Example of standard LEFT JOIN  clause but use it ONLY for specific LEFT JOIN
   // No need of the function if you do not have specific cases
   switch ($new_table) {
      case "glpi_plugin_maps_dropdowns" :
         return " LEFT JOIN `$new_table` ON (`$ref_table`.`$linkfield` = `$new_table`.`id`) ";
   }
   return "";
}


function plugin_maps_forceGroupBy($type) {

   switch ($type) {
      case 'PluginMapsExample' :
         // Force add GROUP BY IN REQUEST
         return true;
   }
   return false;
}


function plugin_maps_addWhere($link, $nott, $type, $ID, $val, $searchtype) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   $SEARCH = Search::makeTextSearch($val,$nott);

   // Example of standard Where clause but use it ONLY for specific Where
   // No need of the function if you do not have specific cases
    switch ($table.".".$field) {
       /*case "glpi_plugin_example.name" :
          $ADD = "";
          if ($nott && $val!="NULL") {
             $ADD = " OR `$table`.`$field` IS NULL";
          }
          return $link." (`$table`.`$field` $SEARCH ".$ADD." ) ";*/
         case "glpi_plugin_maps_examples.serial" :
            return $link." `$table`.`$field` = '$val' ";
    }
   return "";
}


// This is not a real example because the use of Having condition in this case is not suitable
function plugin_maps_addHaving($link, $nott, $type, $ID, $val, $num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   $SEARCH = Search::makeTextSearch($val,$nott);

   // Example of standard Having clause but use it ONLY for specific Having
   // No need of the function if you do not have specific cases
   switch ($table.".".$field) {
      case "glpi_plugin_maps.serial" :
         $ADD = "";
         if (($nott && $val!="NULL")
             || $val == '^$') {
            $ADD = " OR ITEM_$num IS NULL";
         }
         return " $LINK ( ITEM_".$num.$SEARCH." $ADD ) ";
   }
   return "";
}


function plugin_maps_addSelect($type,$ID,$num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

// Example of standard Select clause but use it ONLY for specific Select
// No need of the function if you do not have specific cases
// switch ($table.".".$field) {
//    case "glpi_plugin_maps.name" :
//       return $table.".".$field." AS ITEM_$num, ";
// }
   return "";
}


function plugin_maps_addOrderBy($type,$ID,$order,$key=0) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

// Example of standard OrderBy clause but use it ONLY for specific order by
// No need of the function if you do not have specific cases
// switch ($table.".".$field) {
//    case "glpi_plugin_maps.name" :
//       return " ORDER BY $table.$field $order ";
// }
   return "";
}


//////////////////////////////
////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////


// Define actions :
function plugin_maps_MassiveActions($type) {

   switch ($type) {
      // New action for core and other plugin types : name = plugin_PLUGINNAME_actionname
      case 'Computer' :
         return array("plugin_maps_DoIt" => __("plugin_maps_DoIt", 'maps'));

      // Actions for types provided by the plugin
      case 'PluginMapsExample' :
         return array("add_document" => _x('button', 'Add a document'),         // GLPI core one
                      "do_nothing"   => __('Do Nothing - just for fun', 'maps'));  // Specific one
   }
   return array();
}


// How to display specific actions ?
// options contain at least itemtype and and action
function plugin_maps_MassiveActionsDisplay($options=array()) {

   switch ($options['itemtype']) {
      case 'Computer' :
         switch ($options['action']) {
            case "plugin_maps_DoIt" :
               echo "&nbsp;<input type='hidden' name='toto' value='1'>".
                    "<input type='submit' name='massiveaction' class='submit' value='".
                      __s('Post')."'> ".__('Write in item history', 'maps');
            break;
         }
         break;

      case 'PluginMapsExample' :
         switch ($options['action']) {
            // No case for add_document : use GLPI core one
            case "do_nothing" :
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' value='".
                     __s('Post')."'> ".__('but do nothing :)', 'maps');
            break;
         }
         break;
   }
   return "";
}


// How to process specific actions ?
// May return array of stats containing number of process: ok / ko / noright count to display stats
function plugin_maps_MassiveActionsProcess($data) {

   $ok      = 0;
   $ko      = 0;
   $noright = 0;

   switch ($data['action']) {
      case 'plugin_maps_DoIt' :
         if ($data['itemtype'] == 'Computer') {
            $comp = new Computer();
            Session::addMessageAfterRedirect(__("Right it is the type I want...", 'maps'));
            Session::addMessageAfterRedirect(__('Write in item history', 'maps'));
            $changes = array(0, 'old value', 'new value');
            foreach ($data['item'] as $key => $val) {
               if ($val == 1) {
                  if ($comp->getFromDB($key)) {
                     Session::addMessageAfterRedirect("- ".$comp->getField("name"));
                     Log::history($key, 'Computer', $changes, 'PluginMapsExample', Log::HISTORY_PLUGIN);
                     $ok++;
                  } else {
                     // Example of ko count
                     $ko++;
                  }
               }
            }
         }
         break;

      case 'do_nothing' :
         if ($data['itemtype'] == 'PluginMapsExample') {
            $ex = new PluginMapsExample();
            Session::addMessageAfterRedirect(__("Right it is the type I want...", 'maps'));
            Session::addMessageAfterRedirect(__("But... I say I will do nothing for:", 'maps'));
            foreach ($data['item'] as $key => $val) {
               if ($val == 1) {
                  if ($ex->getFromDB($key)) {
                     Session::addMessageAfterRedirect("- ".$ex->getField("name"));
                     $ok++;
                  } else {
                     // Example for noright / Maybe do it with can function is better
                     $noright++;
                  }
               }
            }
         }
         break;
   }
   return array('ok'      => $ok,
                'ko'      => $ko,
                'noright' => $noright);

}


// How to display specific update fields ?
// options must contain at least itemtype and options array
function plugin_maps_MassiveActionsFieldsDisplay($options=array()) {
   //$type,$table,$field,$linkfield

   $table     = $options['options']['table'];
   $field     = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];

   if ($table == getTableForItemType($options['itemtype'])) {
      // Table fields
      switch ($table.".".$field) {
         case 'glpi_plugin_maps_examples.serial' :
            _e("Not really specific - Just for example", 'maps');
            //Html::autocompletionTextField($linkfield,$table,$field);
            // Dropdown::showYesNo($linkfield);
            // Need to return true if specific display
            return true;
      }

   } else {
      // Linked Fields
      switch ($table.".".$field) {
         case "glpi_plugin_maps_dropdowns.name" :
            _e("Not really specific - Just for example", 'maps');
            // Need to return true if specific display
            return true;
      }
   }
   // Need to return false on non display item
   return false;
}


// How to display specific search fields or dropdown ?
// options must contain at least itemtype and options array
// MUST Use a specific AddWhere & $tab[X]['searchtype'] = 'equals'; declaration
function plugin_maps_searchOptionsValues($options=array()) {

   $table = $options['searchoption']['table'];
   $field = $options['searchoption']['field'];

    // Table fields
   switch ($table.".".$field) {
      case "glpi_plugin_maps_examples.serial" :
            _e("Not really specific - Use your own dropdown - Just for example", 'maps');
            Dropdown::show(getItemTypeForTable($options['searchoption']['table']),
                                               array('value'    => $options['value'],
                                                     'name'     => $options['name'],
                                                     'comments' => 0));
            // Need to return true if specific display
            return true;
   }
   return false;
}



// Do special actions for dynamic report
function plugin_maps_dynamicReport($parm) {

   if ($parm["item_type"] == 'PluginMapsExample') {
      // Do all what you want for export depending on $parm
      echo "Personalized export for type ".$parm["display_type"];
      echo 'with additional datas : <br>';
      echo "Single data : add1 <br>";
      print $parm['add1'].'<br>';
      echo "Array data : add2 <br>";
      Html::printCleanArray($parm['add2']);
      // Return true if personalized display is done
      return true;
   }
   // Return false if no specific display is done, then use standard display
   return false;
}


// Add parameters to Html::printPager in search system
function plugin_maps_addParamFordynamicReport($itemtype) {

   if ($itemtype == 'PluginMapsExample') {
      // Return array data containing all params to add : may be single data or array data
      // Search config are available from session variable
      return array('add1' => $_SESSION['glpisearch'][$itemtype]['order'],
                   'add2' => array('tutu' => 'Second Add',
                                   'Other Data'));
   }
   // Return false or a non array data if not needed
   return false;
}


// Install process for plugin : need to return true if succeeded
function plugin_maps_install() {
   global $DB;

   if (!TableExists("glpi_plugin_maps_examples")) {
      $query = "CREATE TABLE `glpi_plugin_maps_examples` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `serial` varchar(255) collate utf8_unicode_ci NOT NULL,
                  `plugin_maps_dropdowns_id` int(11) NOT NULL default '0',
                  `is_deleted` tinyint(1) NOT NULL default '0',
                  `is_template` tinyint(1) NOT NULL default '0',
                  `template_name` varchar(255) collate utf8_unicode_ci default NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_maps_examples ". $DB->error());

      $query = "INSERT INTO `glpi_plugin_maps_examples`
                       (`id`, `name`, `serial`, `plugin_maps_dropdowns_id`, `is_deleted`,
                        `is_template`, `template_name`)
                VALUES (1, 'example 1', 'serial 1', 1, 0, 0, NULL),
                       (2, 'example 2', 'serial 2', 2, 0, 0, NULL),
                       (3, 'example 3', 'serial 3', 1, 0, 0, NULL)";
      $DB->query($query) or die("error populate glpi_plugin_example ". $DB->error());
   }

   if (!TableExists("glpi_plugin_maps_dropdowns")) {
      $query = "CREATE TABLE `glpi_plugin_maps_dropdowns` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `comment` text collate utf8_unicode_ci,
                PRIMARY KEY  (`id`),
                KEY `name` (`name`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_maps_dropdowns". $DB->error());

      $query = "INSERT INTO `glpi_plugin_maps_dropdowns`
                       (`id`, `name`, `comment`)
                VALUES (1, 'dp 1', 'comment 1'),
                       (2, 'dp2', 'comment 2')";

      $DB->query($query) or die("error populate glpi_plugin_maps_dropdowns". $DB->error());

   }

   if (!TableExists("glpi_plugin_maps_profiles")) {
      $query = "CREATE TABLE `glpi_plugin_maps_profiles` (
                  `id` int(11) NOT NULL DEFAULT '0',
                  `dashboard` char(1) collate utf8_unicode_ci default '',
                  `homepage` char(1) collate utf8_unicode_ci default '',
                PRIMARY KEY  (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_maps_profiles". $DB->error());

      if (isset($_SESSION['glpiactiveprofile']['id'])) {
         $query = "INSERT INTO `glpi_plugin_maps_profiles`
                          (`id`, `dashboard`, `homepage`)
                   VALUES (".$_SESSION['glpiactiveprofile']['id'].", 'w', 'w')";

         $DB->query($query) or die("error populate glpi_plugin_maps_profiles". $DB->error());
      }
   }

   return true;
}


// Uninstall process for plugin : need to return true if succeeded
function plugin_maps_uninstall() {
   global $DB;


   $notif = new Notification();
   $options = array('itemtype' => 'Ticket',
                    'event'    => 'plugin_example',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   // Current version tables
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

   return true;
}


function plugin_maps_AssignToTicket($types) {

   $types['PluginMapsExample'] = "Example";
   return $types;
}


function plugin_maps_get_events(NotificationTargetTicket $target) {
   $target->events['plugin_example'] = __("Example event", 'example');
}


function plugin_maps_get_datas(NotificationTargetTicket $target) {
   $target->datas['##ticket.example##'] = __("Example datas", 'example');
}


function plugin_maps_postinit() {
   global $CFG_GLPI;

   // All plugins are initialized, so all types are registered
   foreach ($CFG_GLPI["infocom_types"] as $type) {
      // do something
   }
}


/**
 * Hook to add more data from ldap
 * fields from plugin_retrieve_more_field_from_ldap_example
 *
 * @param $datas   array
 *
 * @return un tableau
 **/
function plugin_retrieve_more_data_from_ldap_maps(array $datas) {
   return $datas;
}


/**
 * Hook to add more fields from LDAP
 *
 * @param $fields   array
 *
 * @return un tableau
 **/
function plugin_retrieve_more_field_from_ldap_maps($fields) {
   return $fields;
}

// Check to add to status page
function plugin_maps_Status($param) {
   // Do checks (no check for example)
   $ok = true;
   echo "example plugin: example";
   if ($ok) {
      echo "_OK";
   } else {
      echo "_PROBLEM";
      // Only set ok to false if trouble (global status)
      $param['ok'] = false;
   }
   echo "\n";
   return $param;
}
?>