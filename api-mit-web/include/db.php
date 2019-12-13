<?php
/**
 * Class db
 *
 * Class con los metodos de acceso a la base de datos
 *
 * @author Alumne
 * @copyright 2019 Alumne
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-02-07
 * @link https://github.com/alumne/bilbioteca
 */
class db {

  private $host		='localhost';
  private $user		='u_cifo_pqtm19';
  private $password	='12345';
  private $database	='cifo_pqtm19';

  public $mysql;      // Objeto conector
  public $error;      // Mensajes de error
  public $returnType; // Tipo de salida
  public $tabla;      // Nombre de la tabla

  public $inicio;       // marca el inicio de pagina
  public $pagina;       // indica el numero de pagina
  public $registros=15; // establece el numero de registros por pagina

  public $total_registros; // Contiene el numero total de registros
  public $total_paginas;   // Contiene el numero total de paginas

  /**
   * Constructor de la class db
   * @param   string $type Definición del formato de salida
   * @example string |null   : Array associativo| Recordset
   * @example string 'json' : JSON
   * @example string 'xml'  : Documento XML
   * @return  null
   */
  function __construct($type=''){
  	$this->mysql =  new mysqli($this->host,$this->user,$this->password,$this->database);
  	if($this->mysql->connect_errno){
  		die('La base de datos no esta disponible');
  	}
    $this->mysql->set_charset("utf8");
    $this->returnType   = $type;
  }

  /**
   * Obtener registros por un campo determinado con un valor determinado
   * @param string $field Nombre del campo
   * @param string $value Valor del campo buscado
   * @return boolean | string | array $arrRow   JSON o un XML o un Array asociativo
   */
  public function getBy($field, $value){
    $sql="SELECT {$this->tabla}.*
            FROM {$this->tabla}
            WHERE {$this->tabla}.$field= '$value'   ";
    $rsRows=$this->mysql->query($sql);

    // Fields Information
    $fields= $rsRows->fetch_fields();
    $first_field=true;

    if($rsRows->num_rows==1){
      $arrRow=$rsRows->fetch_assoc();
      switch($this->returnType){
        case 'json' :
          return json_encode($arrRow);
          break;
        case 'xml':
        	$dom = new DOMImplementation();
        	$dtd=  $this->tabla.' [';
        	$dtd.= '<!ENTITY nbsp "&#160;">';
        	$dtd.= '<!ELEMENT '.$this->tabla.' 	(';
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

            $rootTag=$xml->createElement($this->tabla);
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
    }else{
      $arrRows= $rsRows->fetch_all(MYSQLI_ASSOC);
      switch($this->returnType){
        case 'json' :
          return json_encode($arrRows);
          break;
        case 'xml':
        	$dom = new DOMImplementation();
        	$dtd=  $this->tabla.' [';
        	$dtd.= '<!ENTITY nbsp "&#160;">';
        	$dtd.= '<!ELEMENT '.$this->tabla.' (row*)>  ';
        	$dtd.= '<!ELEMENT row (';
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
        	//$xml= new DOMDocument();
        	$xml=$dom->createDocument(null, null, $doctype);
        	$xml->encoding ='UTF-8';

            $rootTag=$xml->createElement($this->tabla);
            for($k=0; $k<count($arrRows);$k++){
              $itemTag=$xml->createElement('row');
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

  /**
  *  Inserta datos en la tabla
  *
  * @param array $data Array asociativo con los campos=>valores (sin id)
  * @return boolean | array Un false o un arrray asociativo del nuevo registro insertado
  */
  public function insert($data){
    $fields=array();
    $values=array();
    foreach($data as $key=>$value){
      if($key=='password'){
        $fields[]= $key;
        $values[]= "PASSWORD('$value')";
      }else{
        $fields[]= $key;
        $values[]= "'".str_replace("'" , "''", $value)."'";
      }
    }
    $sql="INSERT INTO {$this->tabla}  (".implode(",",$fields).")
          VALUES (".implode(",",$values)."); ";
    $this->mysql->query($sql);
    if($this->mysql->errno){
      $this->error= $this->mysql->error;
      return false;
    }else{
      return $this->get($this->mysql->insert_id);
    }
  }
  /**
   * update: Actualiza datos en la tabla
   *
   * @param integer $id Primary key del registro
   * @param array $data Array asociativo de los campos a actualizar
   * @return string $sql
   */
  public function update($id , $data){
    $fields=array();
    foreach($data as $key=>$value){
      if($key=='password'){
        $fields[]=$key ."=PASSWORD('".str_replace("'" , "''", $value)."')";
      }else{
        $fields[]=$key ."='".str_replace("'" , "''", $value)."'";
      }

    }
    $sql="UPDATE {$this->tabla} SET ".implode(",", $fields)." WHERE id=$id ;";
    $this->mysql->query($sql);
    if($this->mysql->errno){
      $this->error= $this->mysql->error;
      return false;
    }else{
      return $this->get($id);
    }
  }
  /**
   * delete: Elimina registro de la tabla
   *
   * @param integer $id  Primary key del registro
   * @return string
   */
  public function delete($id){
    $sql="DELETE FROM {$this->tabla}  WHERE id=$id ;";
    $this->mysql->query($sql);
    if($this->mysql->errno){
      $this->error= $this->mysql->error;
      return false;
    }else{
      switch($this->returnType){
        case 'json' :
          return json_encode(array("id"=>$id,"msg"=>"Deleted"));
          break;
        case 'xml': break;
          $xml= new DOMDocument();
          $xml->encoding ='UTF-8';
          $rootTag=$xml->createElement($this->tabla);
          $tag= $xml->createElement('id',$id);
          $rootTag->appendChild($tag);
          $tag= $xml->createElement('msg','Deleted');
          $rootTag->appendChild($tag);
          $xml->appendChild($rootTag);
          return $xml->saveXML();
          break;
        default :
          return $id;
      }
    }
  }
  /**
   * Contruye los enlaces de paginacion
   * @return string
   */
  public function getLinks($strClass=''){
    $echo="";
    if ($this->total_registros) {
      $echo.="<nav aria-label=\"navigation\">
              <ul class=\" $strClass \">
        ";
      /*
       * Primer Item
       */
      if (($this->pagina - 1) > 0) {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" href=\"./?pag=1\">Primero</a></li>";
      } else {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" name=\"primero\">Primero</a></li>";
      }

      /**
       * Acá activamos o desactivamos la opción "< Anterior", si estamos
       * en la pagina 1 nos dará como resultado 0 por ende NO
       * activaremos el primer if y pasaremos al else en donde
       * se desactiva la opción anterior. Pero si el resultado es mayor
       * a 0 se activara el href del link para poder retroceder.
       */
      if (($this->pagina - 1) > 0) {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" href=\"?pag=".($this->pagina-1)."\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
      } else {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" name=\"anterior\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
      }

      // Generamos el ciclo para mostrar la cantidad de paginas que tenemos.


      $start      = ( ( $this->pagina - 5 ) > 0 ) ? $this->pagina -5: 1;
      $end        = ( ( $this->pagina + 5 ) < $this->total_paginas ) ? $this->pagina + 5 : $this->total_paginas;
      for ($i = $start; $i <= $end; $i++) {

        if ($this->pagina == $i) {
          $echo .= "<li class=\"page-item active\"><a class=\"page-link \" href=\"#\">". $this->pagina ."</a></li>";
        } else {
          $echo.= "<li class=\"page-item\"><a class=\"page-link \" href=\"?pag=$i\">$i</a></li>";
        }
      }

      /**
       * Igual que la opción primera de "< Anterior", pero acá para la opción "Siguiente >",
       *  si estamos en la ultima pagina no podremos
       * utilizar esta opción.
       */
      if (($this->pagina + 1)<=$this->total_paginas) {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" href=\"?pag=".($this->pagina+1)."\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
      } else {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" ><span aria-hidden=\"true\">&raquo;</span></a></li>";
      }
      /*
       * Ultimo
       */
      if (($this->pagina + 1)<=$this->total_paginas) {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" href=\"?pag=".$this->total_paginas."\">Último</a></li>";
      } else {
        $echo.= "<li class=\"page-item\"><a class=\"page-link \" >Último</a></li>";
      }
      $echo .= "</ul></nav>";
      echo "<span style=\"float:right\">Página:".$this->pagina." de ".$this->total_paginas."</span>";
      echo "<div style=\"clear:both;\"></div>";
    }

    return $echo;
  }
}






