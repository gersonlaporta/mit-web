<?php
$id_provincia= (isset($_GET['id']))? $_GET['id']: '';
$id_comunidades= (isset($_GET['id_comunidades']))? $_GET['id_comunidades'] : '' ;

include_once $_SERVER['DOCUMENT_ROOT'].'/include/provincias.php';
$provincias= new provincia('xml');
header ("Content-Type:application/xml");

if($id_provincia=='' && $id_comunidades==''){
	echo $provincias->getAll();
	//echo $provincias->getBy('active', 1);
}else if($id_provincia!='' && $id_comunidades==''){
	//echo $provincias->get($id_provincia);
	echo $provincias->getBy('id',$id_provincia);
}else if($id_provincia=='' && $id_comunidades!=''){
	echo $provincias->getAll('','',$id_comunidades);
}
