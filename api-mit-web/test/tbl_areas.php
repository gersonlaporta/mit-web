<?php

$id_areas= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/areas.php';
$areas= new area('xml');
header ("Content-Type:application/xml");

if($id_areas==''){
	echo $areas->getAll();
	//echo $areas->getBy('active', 1);
}else{
	echo $areas->get($id_areas);
	//echo $areas->getBy('id',$id_areas);
}
?>