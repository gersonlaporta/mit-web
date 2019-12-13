<?php
$id_comunidad= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/comunidades.php';
$comunidades= new comunidad('xml');
header ("Content-Type:application/xml");

if($id_comunidad==''){
	echo $comunidades->getAll(4,5);
	//echo $comunidades->getBy('active', 1);
}else{
	echo $comunidades->get($id_comunidad);
	//echo $comunidades->getBy('id',$id_comunidad);
}
?>
