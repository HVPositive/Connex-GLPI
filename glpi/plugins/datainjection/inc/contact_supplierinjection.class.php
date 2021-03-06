<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 LICENSE

 This file is part of the datainjection plugin.

 Datainjection plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Datainjection plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with datainjection. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   datainjection
 @author    the datainjection plugin team
 @copyright Copyright (c) 2010-2017 Datainjection plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsGLPI/datainjection
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginDatainjectionContact_SupplierInjection extends Contact_Supplier
                                                   implements PluginDatainjectionInjectionInterface
{


   static function getTable($classname = null) {

      $parenttype = get_parent_class();
      return $parenttype::getTable();

   }


   function isPrimaryType() {

      return false;
   }


   function connectedTo() {

      return array('Contact', 'Supplier');
   }


    /**
    * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::getOptions()
   **/
   function getOptions($primary_type='') {

      $tab[100]['table']         = 'glpi_suppliers';
      $tab[100]['field']         = 'name';
      $tab[100]['linkfield']     = 'suppliers_id';
      $tab[100]['name']          = __('Associated suppliers');
      $tab[100]['displaytype']   = 'relation';
      $tab[100]['relationclass'] = 'Contact_Supplier';
      $tab[100]['injectable']    = true;

      return $tab;
   }


    /**
    * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::addOrUpdateObject()
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


    /**
    * @param $primary_type
    * @param $values
   **/
   function addSpecificNeededFields($primary_type,$values) {

      $fields[getForeignKeyFieldForTable(getTableForItemType($primary_type))]
          = $values[$primary_type]['id'];
      return $fields;
   }

}
