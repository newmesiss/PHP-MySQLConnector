<?php
include_once('autoloader.php');
include_once('config/Config.DataBase.php');

$MySQL = new GreyDogSystems\Database\MySQL($Config['DATABASE']);
$Debug = new GreyDogSystems\Developer\Debug();

echo $Debug->VariableDumper($MySQL->GetQueryResults('SELECT VERSION();'));

?>