<?php

$id_ofertas= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/ofertas.php';
$ofertas= new oferta('xml');
header ("Content-Type:application/xml");

if($id_ofertas==''){
	echo $ofertas->getAll();
	//echo $ofertas->getBy('active', 1);
}else{
	echo $ofertas->get($id_ofertas);
	//echo $ofertas->getBy('id',$id_ofertas);
}
?>