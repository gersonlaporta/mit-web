<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
/**
 * Class municipio
 *
 * Class de municipio con los metodos de acceso a la tabla de tbl_municipios
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-01-01
 * @link https://github.com/joraymes/cifo
 */
class municipio extends db {
    /**
     * Constructor de la class municipio
     * @param string $type Definición del formato de salida
     * @example strin|null    : Array associativo| Recordset
     * @example string 'json' : JSON
     * @example string 'xml'  : Documento XML
     * @return null
     */
    function __construct($type='') {
    	parent::__construct($type);
    	$this->tabla='tbl_municipios';
    }
    /**
     * Obtener municipio por id
     * @param integer $id Valor de ID
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
     */
    public function get($id){
        $sql="SELECT {$this->tabla}.*,
            tbl_provincias.nombre as provincia,
			tbl_comunidades.nombre as comunidad
            FROM {$this->tabla}
            LEFT JOIN tbl_provincias  ON tbl_provincias.id= {$this->tabla}.id_provincias
			LEFT JOIN tbl_comunidades ON tbl_comunidades.id=tbl_provincias.id_comunidades
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
                	$dtd=  'municipio [';
                	$dtd.= '<!ENTITY nbsp "&#160;">';
                	$dtd.= '<!ELEMENT municipio (';
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
                    $rootTag=$xml->createElement('municipio');
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
     * Obtener municipios , permite pasar parametros de paginacion y id_provincias
     * @param integer $start Valor de inicio de registro
     * @param integer $page Valor de numero de registros
     * @param integer $id_provincias Valor de id de provincias por el que filtrar
     * @param integer $id_comunidades Valor de id de comunidades por el que filtrar
     * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
     */
    public function getAll ($start='',$page='',$id_provincias=0,$id_comunidades=0){

        $where	= ($id_provincias!=0)? 	" AND {$this->tabla}.id_provincias=$id_provincias  " 	: "" ;
        $where	.=($id_comunidades!=0)? " AND tbl_comunidades.id=$id_comunidades  " 			: "" ;

        $sql="SELECT {$this->tabla}.*,
            tbl_provincias.nombre as provincia,
			tbl_comunidades.nombre as comunidad
            FROM {$this->tabla}
            LEFT JOIN tbl_provincias  ON tbl_provincias.id ={$this->tabla}.id_provincias
            LEFT JOIN tbl_comunidades ON tbl_comunidades.id=tbl_provincias.id_comunidades
			WHERE 1 $where
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
            	$dtd=  'municipios [';
            	$dtd.= '<!ELEMENT municipios (municipio*)>  ';
            	$dtd.= '<!ELEMENT municipio (';
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
                $rootTag=$xml->createElement('municipios');
                for($k=0; $k<count($arrRows);$k++){
                    $itemTag=$xml->createElement('municipio');
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
    public function pagination($pag,$reg,$id_provincias=0,$id_comunidades=0){
        $this->registros=$reg;
        if (!$pag) {
            $this->inicio = 0;
            $this->pagina = 1;
        } else {
            $this->pagina=$pag;
            $this->inicio = ($this->pagina - 1) * $this->registros;
        }
        /** Capturem el número total de registres*/
        $where	 =($id_provincias!=0)? 	" AND {$this->tabla}.id_provincias=$id_provincias  " 	: "" ;
        $where	.=($id_comunidades!=0)? " AND tbl_comunidades.id=$id_comunidades  " 			: "" ;
        
        $sql="SELECT * FROM {$this->tabla} 
			LEFT JOIN tbl_provincias  ON tbl_provincias.id= {$this->tabla}.id_provincias
			LEFT JOIN tbl_comunidades ON tbl_comunidades.id=tbl_provincias.id_comunidades
			WHERE 1 $where";
        $rsRows=$this->mysql->query($sql);
        $this->total_registros =    $rsRows->num_rows ;

        /** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
        $this->total_paginas = ceil($this->total_registros / $this->registros);

        return $this->getAll($this->inicio,$this->registros,$id_provincias);
    }
}
?>