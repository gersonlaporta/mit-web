<?php
/*include_once $_SERVER['DOCUMENT_ROOT'].'/include/usuarios.php';
$usuarios= new usuario('xml');
header ("Content-Type:application/xml");

echo $usuarios->login('admin@cifo.cat', '12345',1);*/


$id_usuario= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/usuarios.php';
$usuarios= new usuario('xml');
header ("Content-Type:application/xml");

if($id_usuario==''){
	echo $usuarios->getAll();
	//echo $usuarios->getBy('active', 1);
}else{
	echo $usuarios->get($id_usuario);
	//echo $usuarios->getBy('id',$id_usuario);
}

