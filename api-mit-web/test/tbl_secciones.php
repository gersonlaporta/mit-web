<?php

$id_secciones= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/secciones.php';
$seccion= new seccion('xml');
header ("Content-Type:application/xml");

if($id_secciones==''){
	echo $seccion->getAll();
	//echo $areas->getBy('active', 1);
}else{
	echo $seccion->get($id_secciones);
	//echo $areas->getBy('id',$id_areas);
}
?>