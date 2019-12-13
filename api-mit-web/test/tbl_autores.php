<?php

$id_autores= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/autores.php';
$autores= new autor('xml');
header ("Content-Type:application/xml");

if($id_autores==''){
	echo $autores->getAll();
	//echo $autores->getBy('active', 1);
}else{
	echo $autores->get($id_autores);
	//echo $autores->getBy('id',$id_autores);
}
?>