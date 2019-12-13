<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
 /**
 * Class libro
 *
 * Class con los metodos de acceso a la tabla tbl_libros
 *
 * @author Alumne
 * @copyright 2019 Alumne
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-02-07
 * @link https://github.com/alumne/biblioteca
 */
class libro extends  db {
	/**
	 * Constructor de la class libro
	 * @param string $type Definición del formato de salida
	 * @example string|null   : Array associativo| Recordset
	 * @example string 'json' : JSON
	 * @example string 'xml'  : Documento XML
	 * @return null
	 */
	function __construct($type='') {
		parent::__construct($type);
		$this->tabla='tbl_libro';
	}
    /**
     * get: Obtiene un registro de la tabla tbl_libros
     *
     * @param number $id Primary Key
     * @return string|array Una cadena JSON o XML o un array asociativo
     */
	public function get($id=0){
		$sql =" SELECT {$this->tabla}.* , 
			  tbl_editoriales.nombre AS editorial, 
			  tbl_temas.nombre AS tema,
			  tbl_secciones.nombre AS seccion
  			  FROM {$this->tabla}
			  LEFT JOIN tbl_editoriales ON tbl_editoriales.id={$this->tabla}.id_editoriales
		      LEFT JOIN tbl_temas ON tbl_temas.id={$this->tabla}.id_temas
			  LEFT JOIN tbl_secciones ON tbl_secciones.id=tbl_temas.id_secciones
			  WHERE  {$this->tabla}.id=$id ;";
		//echo $sql;exit;
		$rsRows= $this->mysql->query($sql);

		// Fields Information
		$fields= $rsRows->fetch_fields();
		$first_field=true;

		$arrRow=$rsRows->fetch_assoc();
		// -------------------- Lista de Autores -------------------------------
		$sql="SELECT tbl_autor.* 
			FROM tbl_autor
			LEFT JOIN tbl_autor_libro  ON tbl_autor_libro.id_autor=tbl_autor.id
			WHERE 1 AND  tbl_autor_libro.id_libro=$id ";
		
		$rsRowsAutor= $this->mysql->query($sql);
		$fieldsAutor= $rsRowsAutor->fetch_fields();
		$first_fieldAutor=true;
		$arrRowsAutor=$rsRowsAutor->fetch_all(MYSQLI_ASSOC);
		
		$arrRow["autores"]=$arrRowsAutor;
		
		switch($this->returnType){
			case 'json' :
				return json_encode($arrRow);
				break;
			case 'xml':
				$dom = new DOMImplementation();
				$dtd=  'libro [';
				$dtd.= '<!ENTITY nbsp "&#160;">';
				$dtd.= '<!ELEMENT libro 	(';
				foreach($fields as $field){
					$dtd.= ($first_field)? '':',';
					$first_field=false;
					$dtd.= $field->name;
				}
				$dtd.= ')>';
				foreach($fields as $field){
					if($field->name=="autores"){
						$dtd.= '<!ELEMENT autores (autor*)>  ';
						$dtd.= '<!ELEMENT autor 	(';
						foreach($fieldsAutor as $fieldAutor){
							$dtd.= ($first_fieldAutor)? '':',';
							$first_fieldAutor=false;
							$dtd.= $fieldAutor->name;
						}
						$dtd.= ')>';
						foreach($fieldsAutor as $fieldAutor){
							$dtd.=($fieldAutor->name!='id')? '<!ELEMENT '.$fieldAutor->name.'	(#PCDATA)>':'';
						}
					}else{
						$dtd.= '<!ELEMENT '.$field->name.'	(#PCDATA)>';
					}
					
				}
				$dtd.= ']';
				$doctype = $dom->createDocumentType($dtd);
				$xml=$dom->createDocument(null, null, $doctype);
				$xml->encoding ='UTF-8';
				$rootTag=$xml->createElement('libro');
				foreach($arrRow as $key=>$value){
					if($key=="autores"){
						$autoresTag=$xml->createElement('autores');
						//var_dump($arrRow[$key]);
						for($i=0; $i<count($arrRow[$key]);$i++){
							$autorTag=$xml->createElement('autor');
							foreach ($arrRow[$key][$i] as $key1=>$value1){
								$tag= $xml->createElement($key1,$value1);
								$autorTag->appendChild($tag);
							}
							$autoresTag->appendChild($autorTag);
						}
						$rootTag->appendChild($autoresTag);
					}else{
						$tag= $xml->createElement($key,$value);
						$rootTag->appendChild($tag);
					}

				}
				$xml->appendChild($rootTag);
				return $xml->saveXML();
				break;
			default :
				return $arrRow;
		}
	}
	/**
	 * Obtener libros , permite pasar parametros de paginacion
	 * @param integer $start Valor de inicio de registro
	 * @param integer $page Valor de numero de registros
	 * @param integer $id_editoriales Id de editorial por el que filtrar
	 * @param integer $id_tema Id de tema por el que filtrar
	 * @param integer $id_autores Id de autor por el que filtrar
	 * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
	 */
	public function getAll($start='',$page='',$id_editoriales=0,$id_temas=0,$id_secciones=0,$id_autores=0 ){
		
		$where  = ($id_editoriales!=0)	?  " AND id_editoriales=$id_editoriales "			: '' ;
		$where .= ($id_autores!=0)		?  " AND tbl_autor.id=$id_autores "					: '' ;
		$where .= ($id_temas!=0)		?  " AND id_temas=$id_temas "						: '' ;
		$where .= ($id_secciones!=0)	?  " AND tbl_temas.id_secciones=$id_secciones "		: '' ;

		$sql =" SELECT {$this->tabla}.* , 
              tbl_editoriales.nombre AS editorial, 
              tbl_temas.nombre AS tema,
			  tbl_secciones.nombre AS seccion

  			  FROM {$this->tabla}

			  LEFT JOIN tbl_editoriales
              ON tbl_editoriales.id={$this->tabla}.id_editoriales

			  LEFT JOIN tbl_temas
              ON tbl_temas.id={$this->tabla}.id_temas

			  LEFT JOIN tbl_secciones
              ON tbl_secciones.id=tbl_temas.id_secciones

			  LEFT JOIN tbl_autor_libro
              ON tbl_autor_libro.id_libro={$this->tabla}.id

			  LEFT JOIN tbl_autor
			  ON tbl_autor_libro.id_autor=tbl_autor.id

			  WHERE 1 $where

			  GROUP BY {$this->tabla}.id
			  ORDER BY {$this->tabla}.id
            ";
		if($page!=''){
			$sql .= " LIMIT $start ,  $page ";
		}
		
		$rsRows=$this->mysql->query($sql);

		// Fields Information
		$fields= $rsRows->fetch_fields();
		$first_field=true;

		$arrRows= $rsRows->fetch_all(MYSQLI_ASSOC);
		
		/* --------------------- Autores ------------------------*/
		for($i=0 ; $i<count($arrRows);$i++){
			$sql =" SELECT tbl_autor.*
			  FROM tbl_autor
			  INNER JOIN tbl_autor_libro  ON tbl_autor_libro.id_autor=tbl_autor.id
			  WHERE 1 AND tbl_autor_libro.id_libro={$arrRows[$i]["id"]} ;";
			
			  $rsRowsAutores= $this->mysql->query($sql);
			  if($i==0){
				  // Fields Information
			  	  $fieldsAutor= $rsRowsAutores->fetch_fields();
				  $first_fieldAutor=true;
			  }
			  $arrRowsAutores=$rsRowsAutores->fetch_all(MYSQLI_ASSOC);
			  $arrRows[$i]["autores"]=$arrRowsAutores;
		}

		switch($this->returnType){
			case 'json' :
				return json_encode($arrRows);
				break;
			case 'xml':
				$dom = new DOMImplementation();
				$dtd=  'libros [';
				$dtd.= '<!ENTITY nbsp "&#160;">';
				$dtd.= '<!ELEMENT libros (libro*)>  ';
				$dtd.= '<!ELEMENT libro 	(';
				foreach($fields as $field){
					$dtd.= ($first_field)? '':',';
					$first_field=false;
					$dtd.= $field->name;
				}
				$dtd.= ')>';
				foreach($fields as $field){
					
					if($field->name=="autores"){
						$dtd.= '<!ELEMENT autores (autor*)>  ';
						$dtd.= '<!ELEMENT autor 	(';
						foreach($fieldsAutor as $fieldAutor){
							$dtd.= ($first_fieldAutor)? '':',';
							$first_fieldAutor=false;
							$dtd.= $fieldAutor->name;
						}
						$dtd.= ')>';
						foreach($fieldsAutor as $fieldAutor){
							$dtd.=($fieldAutor->name!='id')? '<!ELEMENT '.$fieldAutor->name.'	(#PCDATA)>':'';
						}
					}else{
						$dtd.= '<!ELEMENT '.$field->name.'	(#PCDATA)>';
					}
				}
				$dtd.= ']';
				$doctype = $dom->createDocumentType($dtd);
				$xml=$dom->createDocument(null, null, $doctype);
				$xml->encoding ='UTF-8';
				$rootTag=$xml->createElement('libros');
				for($k=0; $k<count($arrRows);$k++){
					$itemTag=$xml->createElement('libro');
					foreach(  $arrRows[$k]   as   $key  =>  $value){
						if($key=="autores"){
							$autoresTag=$xml->createElement('autores');
							//var_dump($arrRow[$key]);
							for($i=0; $i<count($arrRows[$k][$key]);$i++){
								$autorTag=$xml->createElement('autor');
								foreach ($arrRows[$k][$key][$i] as $key1=>$value1){
									$tag= $xml->createElement($key1,$value1);
									$autorTag->appendChild($tag);
								}
								$autoresTag->appendChild($autorTag);
							}
							$itemTag->appendChild($autoresTag);
						}else{
							$tag= $xml->createElement($key,$value);
							$itemTag->appendChild($tag);
						}
					}
					$rootTag->appendChild($itemTag);
				}
				$xml->appendChild($rootTag);
				return $xml->saveXML();
				break;
			default :
				return $arrRows;
		}
	}

	/**
	 * Contruye la respuesta paginada de la tabla
	 * @param integer $pag numero de pagina
	 * @param integer $reg numero de registros por pagina
	 * @return mixed
	 */
	public function pagination($pag,$reg,$id_editoriales=0,$id_temas=0,$id_secciones=0,$id_autores=0){
		$this->registros=$reg;
		if (!$pag) {
			$this->pagina = 1;
			$this->inicio = 0;
		} else {
			$this->pagina=$pag;
			$this->inicio = ($this->pagina - 1) * $this->registros;
		}
		/** Capturem el número total de registres*/
		$where  = ($id_editoriales!=0)	? " AND {$this->tabla}.id_editoriales=$id_editoriales "		: "" ;
		$where .= ($id_autores!=0)		? " AND tbl_autor_libro.id_autor=$id_autores "				: "" ;
		$where .= ($id_temas!=0)		? " AND {$this->tabla}.id_tema=$id_temas "					: "" ;
		$where .= ($id_secciones!=0)	? " AND tbl_temas.id_secciones=$id_secciones "				: '' ;

		$sql="SELECT * FROM {$this->tabla}
             LEFT JOIN tbl_autor_libro ON tbl_autor_libro.id_libro={$this->tabla}.id
			 LEFT JOIN tbl_temas       ON tbl_temas.id={$this->tabla}.id_temas
             WHERE 1 $where
             GROUP BY {$this->tabla}.id  ";
		//echo $sql; exit;
		$rsRows=$this->mysql->query($sql);
		$this->total_registros = $rsRows->num_rows ;
		/** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
		$this->total_paginas = ceil($this->total_registros / $this->registros);
		return $this->getAll($this->inicio,$this->registros,$id_editoriales,$id_temas,$id_secciones,$id_autores);
	}
	/**
     * getAutores: Obtiene lista de autores de un libro
     *
     * @param number $id_libro foreign Key La id del libro
     * @return string|array Una cadena JSON o XML o un array asociativo
     */
	public function getAutores($id_libro){
		$sql =" SELECT tbl_autor.*
			  FROM tbl_autor
			  INNER JOIN tbl_autor_libro  ON tbl_autor_libro.id_autor=tbl_autor.id
			  WHERE 1 AND tbl_autor_libro.id_libro=$id_libro ;";

		$rsRows= $this->mysql->query($sql);
		
		// Fields Information
		$fields= $rsRows->fetch_fields();
		$first_field=true;
		
		$arrRows=$rsRows->fetch_all(MYSQLI_ASSOC);

		switch($this->returnType){
			case 'json' :
				return json_encode($arrRows);
				break;
			case 'xml':
				$dom = new DOMImplementation();
				$dtd=  'autores [';
				$dtd.= '<!ENTITY nbsp "&#160;">';
				$dtd.= '<!ELEMENT autores (autor*)>  ';
				$dtd.= '<!ELEMENT autor 	(';
				foreach($fields as $field){
					$dtd.= ($first_field)? '':',';
					$first_field=false;
					$dtd.= $field->name;
				}
				$dtd.= ')>';
				foreach($fields as $field){
					$dtd.= '<!ELEMENT '.$field->name.'	(#PCDATA)>';
				}
				$dtd.= ']';
				$doctype = $dom->createDocumentType($dtd);
				$xml=$dom->createDocument(null, null, $doctype);
				$xml->encoding ='UTF-8';
				$rootTag=$xml->createElement('autores');
				for($k=0; $k<count($arrRows);$k++){
					$itemTag=$xml->createElement('autor');
					foreach(  $arrRows[$k]   as   $key  =>  $value){
						$tag= $xml->createElement($key,$value);
						$itemTag->appendChild($tag);
					}
					$rootTag->appendChild($itemTag);
				}
				$xml->appendChild($rootTag);
				return $xml->saveXML();
				break;
			default :
				return $arrRows;
		}
	}
}