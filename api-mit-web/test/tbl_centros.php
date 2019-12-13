<?php

$id_centros= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/centros.php';
$centros= new centro('xml');
//header ("Content-Type:application/json");
header ("Content-Type:application/xml");
if($id_centros==''){
	echo $centros->getAll();
	//echo $centros->getBy('active', 1);
}else{
	echo $centros->get($id_centros);
	//echo $centros->getBy('id',$id_centros);
}
?>