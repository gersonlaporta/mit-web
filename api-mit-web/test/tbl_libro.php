<?php

$id_libro= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/libros.php';
$libros= new libro('xml');
header ("Content-Type:application/xml");
//header ("Content-Type:application/json");
if($id_libro==''){
	
	echo $libros->getAll();
	//echo $libros->pagination(1,2);
}else{
	echo $libros->get($id_libro);
	//echo $libros->getAutores($id_libro);
}
?>