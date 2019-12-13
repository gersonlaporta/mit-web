<?php
$id_rol= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/roles.php';
$roles= new rol('xml');
header ("Content-Type:application/xml");

if($id_rol==''){
	echo $roles->getAll();
	//echo $roles->getBy('active', 1);
}else{
	echo $roles->get($id_rol);
	//echo $roles->getBy('id',$id_rol);
}
?>
