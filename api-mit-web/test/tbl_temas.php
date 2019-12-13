<?php

$id_tema= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/temas.php';
$temas= new tema('xml');
header ("Content-Type:application/xml");

if($id_tema==''){
	//echo $areas->getAll();
	echo $temas->getAll();
}else{
	//echo $areas->get($id_areas);
	echo $temas->get($id_tema);
}
?>