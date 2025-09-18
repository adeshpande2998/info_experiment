<?php
require_once(__DIR__ . '/mysql_compat.php');
require_once("./include/fg_membersite.php");

$fgmembersite = new FGMembersite();

$fgmembersite->SetWebsiteName('samuel-brown.com (Individual Decision-Making Experiment)');
$fgmembersite->SetAdminEmail('sam@samuel-brown.com');

/* For Laragon defaults:
   host: 'localhost'
   user: 'root'
   password: ''  (empty)
   dbname: 'infoexp16'  (create this DB first; see below)
   table: 'fgusers3'
*/
$fgmembersite->InitDB('localhost', 'root', '', 'infoexp', 'fgusers3');

$fgmembersite->SetRandomKey('5tSu3AzD4gn8o6Q');