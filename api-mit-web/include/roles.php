<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';

/**
 * Class rol
 *
 * Class de rol con los metodos de acceso a la tabla de tbl_roles
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-01-01
 * @link https://github.com/joraymes/cifo
 */
class rol extends db {

    /**
     * Constructor de la class rol
     * @param string $type Definición del formato de salida
     * @example string|null    : Array associativo| Recordset
     * @example string 'json' : JSON
     * @example string 'xml'  : Documento XML
     * @return null
     */
    function __construct($type='') {
    	parent::__construct($type);
    	$this->tabla='tbl_roles';
    }
    /**
     * Obtener rol por id
     * @param integer $id Valor de ID
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
    */
    public function get($id){
        $sql="SELECT {$this->tabla}.*
            FROM {$this->tabla}
            WHERE {$this->tabla}.id=$id    ";

            $rsRows=$this->mysql->query($sql);

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
                	$dtd=  'rol [';
                	$dtd.= '<!ENTITY nbsp "&#160;">';
                	$dtd.= '<!ELEMENT rol (';
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

                    $rootTag=$xml->createElement('rol');
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
     * Obtener roles , permite pasar parametros de paginacion
     * @param integer $start Valor de inicio de registro
     * @param integer $page Valor de numero de registros
     * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
     */
    public function getAll ($start='',$page='' ){
         $sql="SELECT {$this->tabla}.*
            FROM {$this->tabla}
            WHERE 1
            ORDER BY {$this->tabla}.id
        ";
        if($page!=''){
            $sql .= "LIMIT $start ,  $page";
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
            	$dtd=  'roles [';
            	$dtd.= '<!ELEMENT roles (rol*)>  ';
            	$dtd.= '<!ELEMENT rol (';
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
                $rootTag=$xml->createElement('roles');
                for($k=0; $k<count($arrRows);$k++){
                    $itemTag=$xml->createElement('rol');
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
     * Genera salida paginada de registros
     * @param integer $pag Numero de pagina
     * @param integer $reg Nuemro de registros por pagina
     * @return object $this->getAll()
     */
    public function pagination($pag,$reg){
        $this->registros=$reg;
        if (!$pag) {
            $this->inicio = 0;
            $this->pagina = 1;
        } else {
            $this->pagina=$pag;
            $this->inicio = ($this->pagina - 1) * $this->registros;
        }

        /** Capturem el número total de registres*/
        $sql="SELECT * FROM {$this->tabla} WHERE 1";
        $rsRows=$this->mysql->query($sql);
        $this->total_registros =    $rsRows->num_rows ;
        /** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
        $this->total_paginas = ceil($this->total_registros / $this->registros);

        return $this->getAll($this->inicio,$this->registros);

    }
}
?>