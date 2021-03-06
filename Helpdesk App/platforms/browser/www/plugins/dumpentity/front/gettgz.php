<?php
/*
 * @version $Id: gettgz.php 144 2015-09-06 18:05:31Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Dumpentity is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Dumpentity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Dumpentity. If not, see <http://www.gnu.org/licenses/>.

 @package   dumpentity
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2015 Dumpentity plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/

$USEDBREPLICAT          = 1;
$DBCONNECTION_REQUIRED  = 1; // Really a big SQL request

ini_set("max_execution_time", "0");


if (isset($_GET["encode"])) {
   $encode = $_GET["encode"];
} else {
   $encode = '';
}

define('DO_NOT_CHECK_HTTP_REFERER', 1);
include ("../../../inc/includes.php");

// To avoid Pear warning...
restore_error_handler();
error_reporting(0);

require ("Archive/Tar.php");
if (!class_exists("Archive_Tar")) {
   die("Not supported");
}


function MakeArchive ($model, $filename, $entity, $recur, &$todel, $encode) {
   global $LANG;

   $totar = array();
   $tar   = new Archive_Tar($filename, true);

   $filelist = PLUGIN_DUMPENTITY_UPLOAD_DIR."$entity-tables.csv";
   $ficlist  = fopen($filelist, "wb");
   if ($ficlist) {
      fwrite($ficlist, "name;description;size\r\n");
   }

   foreach (PluginDumpentityModel::getTables() as $table => $descr) {
      if ($model->fields[$table]) {
         $file = PluginDumpentityModel::getCSV($table, $entity, $recur, 0, $encode);

         if (($size=filesize($file)) > 0) {
            $totar[] = basename($file);
         }
         if ($ficlist) {
            fwrite($ficlist, "$table;\"$descr\";$size\r\n");
         }
         $todel[] = $file;
      }
   }

   if ($ficlist) {
      fclose($ficlist);
      $totar[] = "$entity-tables.csv";
      $todel[] = $filelist;
   }
   chdir(PLUGIN_DUMPENTITY_UPLOAD_DIR);
   $tar->create($totar);
}

$model = new PluginDumpentityModel();
if (isset($_SESSION['glpi_plugin_dumpentity_profile'])
    && $model->getFromDB($_SESSION['glpi_plugin_dumpentity_profile']['models_id'])) {
   $entity    = $_SESSION['glpiactive_entity'];
   $recursive = false;

} else {
   // probably wget from a client
   $client = new PluginDumpentityClient();
   if (!$client->getFromDBForIP()) {
      header('HTTP/1.0 403 Forbidden');
      die('Unknown client');
   }
   if (!$model->getFromDB($client->fields['models_id'])) {
      header('HTTP/1.0 403 Forbidden');
      die('Unknown model');
   }
   $entity     = $client->fields["entities_id"];
   $recursive  = $client->fields["is_recursive"];

   if (isset($_GET['entity'])) {
      if ($entity == $_GET['entity']
          || ($recursive && in_array($_GET['entity'], getSonsOf('glpi_entities', $entity)))) {
         $entity    = $_GET['entity'];
         $recursive = false;
      } else {
         header('HTTP/1.0 403 Forbidden');
         die('Unknown entity');
      }
   }
}

$todel    = array();
$filename = PLUGIN_DUMPENTITY_UPLOAD_DIR."$entity-export.tar.gz";
$todel[]  = $filename;

MakeArchive($model, $filename, $entity, $recursive, $todel, $encode);

header("Content-type: application/x-gzip");
header("Content-Length: " . filesize($filename));
header("Content-Disposition: inline; filename=export.tar.gz");
readfile($filename);

// Clean work files
foreach ($todel as $file) {
   unlink($file);
}
