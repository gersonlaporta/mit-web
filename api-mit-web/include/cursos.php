<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
/**
 * Class curso
 *
 * Class de curso con los metodos de acceso a la tabla de tbl_cursos
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-01-01
 * @link https://github.com/joraymes/cifo
 */
class curso extends  db {

    /**
     * Constructor de la class curso
     * @param string $type Definición del formato de salida
     * @example string|null   : Array associativo| Recordset
     * @example string 'json' : JSON
     * @example string 'xml'  : Documento XML
     * @return null
     */
    function __construct($type='') {
    	parent::__construct($type);
    	$this->tabla='tbl_cursos';
    }

    /**
     * Obtener curso por id
     * @param integer $id Valor de ID
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
    */
    public function get($id){
        $sql="SELECT {$this->tabla}.*, tbl_areas.nombre as area
            FROM {$this->tabla}
			LEFT JOIN tbl_areas ON tbl_areas.id={$this->tabla}.id_areas
            WHERE {$this->tabla}.id=$id  ";

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
            	$dtd=  'curso [';
            	$dtd.= '<!ENTITY nbsp "&#160;">';
            	$dtd.= '<!ELEMENT curso 	(';
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
                $rootTag=$xml->createElement('curso');
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
     * Obtener cursos , permite pasar parametros de paginacion
     * @param integer $start Valor de inicio de registro
     * @param integer $page Valor de numero de registros
     * @param integer $id_areas Valor de id_areas
     * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
     */
    public function getAll ($start='',$page='', $id_areas=0 ){
         $sql="SELECT DISTINCT {$this->tabla}.*,tbl_areas.nombre as area
            FROM {$this->tabla}
			LEFT JOIN tbl_areas ON tbl_areas.id={$this->tabla}.id_areas
            WHERE 1 ";
         if($id_areas!=0){
         	$sql.=" AND {$this->tabla}.id_areas=$id_areas ";
         }
         $sql.=" ORDER BY {$this->tabla}.id
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
            	$dtd=  'cursos [';
            	$dtd.= '<!ENTITY nbsp "&#160;">';
            	$dtd.= '<!ELEMENT cursos (curso*)>  ';
            	$dtd.= '<!ELEMENT curso  (';
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
                $rootTag=$xml->createElement('cursos');
                for($k=0; $k<count($arrRows);$k++){
                    $itemTag=$xml->createElement('curso');
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
     * @param integer $id_areas Id del area con el que se puede filtrar la lista de cursos
     * @return object $this->getAll()
     */
    public function pagination($pag,$reg,$id_areas=0){
        $this->registros=$reg;
        if (!$pag) {
            $this->inicio = 0;
            $this->pagina = 1;
        } else {
            $this->pagina=$pag;
            $this->inicio = ($this->pagina - 1) * $this->registros;
        }
        /** Capturem el número total de registres*/
        $sql="SELECT * FROM tbl_cursos WHERE 1 ";
        if($id_areas!=0){
        	$sql.=" AND id_areas=$id_areas ";
        }
        $rsRows=$this->mysql->query($sql);
        $this->total_registros =    $rsRows->num_rows ;
        /** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
        $this->total_paginas = ceil($this->total_registros / $this->registros);
        return $this->getAll($this->inicio,$this->registros,$id_areas);
    }

}
?>