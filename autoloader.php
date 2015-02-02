<?php
function LoadClass($name){
	if(file_exists($name.'.class.php')){
		include_once('classes' . $name . '.class.php');
	}
}

function GDS_LoadClass($name){
	$name = str_replace('\\',DIRECTORY_SEPARATOR,$name);
	$FileName = 'classes' . DIRECTORY_SEPARATOR . $name . '.class.php';
	if(file_exists($FileName)){
		include_once($FileName);
	}
}

spl_autoload_register('GDS_LoadClass');
spl_autoload_register('LoadClass');

?>