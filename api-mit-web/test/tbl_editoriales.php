<?php

$id_editoriales= (isset($_GET['id']))? $_GET['id']: '';

include_once $_SERVER['DOCUMENT_ROOT'].'/include/editoriales.php';
$editoriales= new editorial('xml');
header ("Content-Type:application/xml");

if($id_editoriales==''){
	echo $editoriales->getAll();
	//echo $editoriales->getBy('active', 1);
}else{
	echo $editoriales->get($id_editoriales);
	//echo $editoriales->getBy('id',$id_editoriales);
}
?>