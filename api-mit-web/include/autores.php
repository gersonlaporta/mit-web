<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
/**
 * Class autor
 *
 * Class con los metodos de acceso a la tabla tbl_autor
 *
 * @author Alumne
 * @copyright 2019 Alumne
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-02-07
 * @link https://github.com/alumne/biblioteca
 */
class autor extends  db {
	/**
	 * Constructor de la class autor
	 * @param string $type Definición del formato de salida
	 * @example string|null   : Array associativo| Recordset
	 * @example string 'json' : JSON
	 * @example string 'xml'  : Documento XML
	 * @return null
	 */
	function __construct($type='') {
		parent::__construct($type);
		$this->tabla='tbl_autor';
	}
	/**
	 * Obtiene un registro de la tabla tbl_autores
	 *
	 * @param number $id Primary Key
	 * @return string|array Una cadena JSON o XML o un array asociativo
	 */
	public function get($id=0){
		$sql =" SELECT {$this->tabla}.*
  			    FROM {$this->tabla}
			    WHERE  {$this->tabla}.id=$id ;";

		$rsRows= $this->mysql->query($sql);

		// Fields Information
		$fields= $rsRows->fetch_fields();
		$first_field=true;

		$arrRow=$rsRows->fetch_assoc();
		switch($this->returnType){
			case 'json' :
				return json_encode($arrRow);
				break;
			case 'xml':
				$dom = new DOMImplementation();
				$dtd=  'autor [';
				$dtd.= '<!ENTITY nbsp "&#160;">';
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
				$rootTag=$xml->createElement('autor');
				foreach($arrRow as $key=>$value){
					$tag= $xml->createElement($key,$value);
					$rootTag->appendChild($tag);
				}
				$xml->appendChild($rootTag);
				return $xml->saveXML();
				break;
			default :
				return $arrRow;
		}
	}
	/**
	 * Obtener autores , permite pasar parametros de paginacion
	 * @param integer $start Valor de inicio de registro
	 * @param integer $page Valor de numero de registros
	 * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
	 */
	public function getAll ($start='',$page='' ){
		$sql =" SELECT {$this->tabla}.*
  			    FROM {$this->tabla}
			    WHERE 1
            ";
		if($page!=''){
			$sql .= " LIMIT $start ,  $page ";
		}
		$rsRows=$this->mysql->query($sql);

		// Fields Information
		$fields= $rsRows->fetch_fields();
		$first_field=true;

		$arrRows= $rsRows->fetch_all(MYSQLI_ASSOC);

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

	/**
	 * Contruye la respuesta paginada de la tabla
	 * @param integer $pag numero de pagina
	 * @param integer $reg numero de registros por pagina
	 * @return mixed
	 */
	public function pagination($pag,$reg){
		$this->registros=$reg;
		if (!$pag) {
			$this->pagina = 1;
			$this->inicio = 0;
		} else {
			$this->pagina=$pag;
			$this->inicio = ($this->pagina - 1) * $this->registros;
		}
		/** Capturem el número total de registres*/
		$sql="SELECT * FROM {$this->tabla} WHERE 1 ";
		$rsRows=$this->mysql->query($sql);
		$this->total_registros = $rsRows->num_rows ;
		/** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
		$this->total_paginas = ceil($this->total_registros / $this->registros);
		return $this->getAll($this->inicio,$this->registros);
	}

}