<?php
$id_municipio= (isset($_GET['id']))? $_GET['id']: '';
$id_provincias= (isset($_GET['id_provincias']))? $_GET['id_provincias'] : '' ;

include_once $_SERVER['DOCUMENT_ROOT'].'/include/municipios.php';
$municipios= new municipio('xml');
header ("Content-Type:application/xml");

if($id_municipio=='' && $id_provincias==''){
	echo $municipios->getAll();
	//echo $municipios->getBy('active', 1);
}else if($id_municipio!='' && $id_provincias==''){
	echo $municipios->get($id_municipio);
	//echo $municipios->getBy('id',$id_municipio);
}else if($id_municipio=='' && $id_provincias!=''){
	echo $municipios->getAll('','',$id_provincias);
}