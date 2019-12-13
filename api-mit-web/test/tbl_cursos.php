<?php
$id_curso= (isset($_GET['id']))? $_GET['id']: '';
$id_areas= (isset($_GET['id_areas']))? $_GET['id_areas'] : '' ;

include_once $_SERVER['DOCUMENT_ROOT'].'/include/cursos.php';
$cursos= new curso('xml');
header ("Content-Type:application/xml");

if($id_curso=='' && $id_areas==''){
	echo $cursos->getAll();
	//echo $cursos->getBy('active', 1);
}else if($id_curso!='' && $id_areas==''){
	echo $cursos->get($id_curso);
	//echo $cursos->getBy('id',$id_provincia);
}else if($id_curso=='' && $id_areas!=''){
	echo $cursos->getAll('','',$id_areas);
}
