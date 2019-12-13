<?php
include_once  $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
/**
 * Class oferta
 *
 * Class de oferta con los metodos de acceso a la tabla de tbl_ofertas
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-01-01
 * @link https://github.com/joraymes/cifo
 */
class oferta extends  db {
    /**
     * Constructor de la class oferta
     * @param string $type Definición del formato de salida
     * @example string|null   : Array associativo| Recordset
     * @example string 'json' : JSON
     * @example string 'xml'  : Documento XML
     * @return null
     */
    function __construct($type='') {
        parent::__construct($type);
        $this->tabla='tbl_ofertas';
    }
    /**
     * Obtener oferta por id
     * @param integer $id Valor de ID
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
    */
    public function get($id){
        $sql =  " SELECT {$this->tabla}.*
				  FROM {$this->tabla} 
				  WHERE 1 AND {$this->tabla}.id=$id  ";

        $rsRows=$this->mysql->query($sql);
        
        // Fields Information
        $fields= $rsRows->fetch_fields();
        $first_field=true;
		// ------------------- Cursos ----------------------------------------------
        $sql =  " SELECT tbl_cursos.*
				  FROM tbl_cursos
                  INNER JOIN {$this->tabla} ON tbl_cursos.id={$this->tabla}.id_cursos  
				  WHERE 1 AND {$this->tabla}.id=$id";
        $rsRowsCursos = $this->mysql->query($sql);
        // Fields Information
        $fieldsCursos= $rsRowsCursos->fetch_fields();
        $first_fieldCursos=true;
        $arrRowCurso=$rsRowsCursos->fetch_assoc();
        // -------------------- Areas ----------------------------------------------
        $sql =  " SELECT tbl_areas.*
				  FROM tbl_areas
				  INNER JOIN tbl_cursos ON tbl_areas.id=tbl_cursos.id_areas
                  INNER JOIN {$this->tabla} ON tbl_cursos.id={$this->tabla}.id_cursos
				  WHERE 1 AND {$this->tabla}.id=$id";
        $rsRowsAreas = $this->mysql->query($sql);
        $arrRowArea=$rsRowsAreas->fetch_assoc();
        // -------------------- Centros ---------------------------------------------
        $sql =  " SELECT tbl_centros.*
				  FROM tbl_centros
				  INNER JOIN {$this->tabla} ON tbl_centros.id={$this->tabla}.id_centros
				  WHERE 1 AND {$this->tabla}.id=$id";
        $rsRowsCentros= $this->mysql->query($sql);
        $arrRowCentro=$rsRowsCentros->fetch_assoc();
        // --------------------- Usuarios -------------------------------------------
        $sql =  " SELECT tbl_usuarios.nombre,tbl_usuarios.apellidos, tbl_usuarios.email
				  FROM tbl_usuarios
				  INNER JOIN {$this->tabla} ON tbl_usuarios.id={$this->tabla}.id_profesores
				  WHERE 1 AND {$this->tabla}.id=$id";
        $rsRowsUsuarios= $this->mysql->query($sql);
        $arrRowUsuario=$rsRowsUsuarios->fetch_assoc();
        
        $arrRow=$rsRows->fetch_assoc();
        $arrRow['curso']=$arrRowCurso;
        $arrRow['area']=$arrRowArea;
        $arrRow['centro']=$arrRowCentro;
        $arrRow['profesor']=$arrRowUsuario;
        
		switch($this->returnType){
        	case 'json' :
                    return json_encode($arrRow);
                    break;
        	case 'xml':
        		    // to do : XML DTD correct
                	$dom = new DOMImplementation();
                	$dtd=  'oferta [';
                	$dtd.= '<!ENTITY nbsp "&#160;">';
                	$dtd.= '<!ELEMENT oferta 	(';
                	foreach($fields as $field){
                		$dtd.= ($first_field)? '':',';
                		$first_field=false;
                		$dtd.= $field->name;
                	}
                	$dtd.= ',curso,area,centro,profesor)>';
                	foreach($fields as $field){
                		$dtd.= '<!ELEMENT '.$field->name.'	(#PCDATA)>';
                	}
                	
                	$dtd.= '<!ELEMENT curso 	(';
                	foreach($fieldsCursos as $fieldCurso){
                		$dtd.= ($first_fieldCursos)? '':',';
                		$first_fieldCursos=false;
                		$dtd.= $fieldCurso->name;
                	}
                	$dtd.= ')>';
                	
                	
                	
                	$dtd.= ']';
                	$doctype = $dom->createDocumentType($dtd);
                	$xml=$dom->createDocument(null, null, $doctype);
                	$xml->encoding ='UTF-8';
                    $rootTag=$xml->createElement('oferta');
                    foreach($arrRow as $key=>$value){
                    	if($key=='curso'){
                    		$cursoTag=$xml->createElement('curso');
                    		foreach($value as $keyCurso=>$valueCurso){
                    			$tag= $xml->createElement($keyCurso,$valueCurso);
                    			$cursoTag->appendChild($tag);
                    		}
                    		$rootTag->appendChild($cursoTag);
                    	}else if($key=='area'){
                    		$areaTag=$xml->createElement('area');
                    		foreach($value as $keyArea=>$valueArea){
                    			$tag= $xml->createElement($keyArea,$valueArea);
                    			$areaTag->appendChild($tag);
                    		}
                    		$rootTag->appendChild($areaTag);
                    	}else if($key=='centro'){
                    		$centroTag=$xml->createElement('centro');
                    		foreach($value as $keyCentro=>$valueCentro){
                    			$tag= $xml->createElement($keyCentro,$valueCentro);
                    			$centroTag->appendChild($tag);
                    		}
                    		$rootTag->appendChild($centroTag);
                    	}else if($key=='profesor'){
                    		$profesorTag=$xml->createElement('profesor');
                    		foreach($value as $keyProfesor=>$valueProfesor){
                    			$tag= $xml->createElement($keyProfesor,$valueProfesor);
                    			$profesorTag->appendChild($tag);
                    		}
                    		$rootTag->appendChild($profesorTag);
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
     * Obtener oferta , permite pasar parametros de paginacion & filtro
     * @param integer $start Valor de inicio de registro
     * @param integer $page Valor de numero de registros
     * @param integer $id_cursos Id de curso
     * @param integer $id_areas Id de area
     * @param integer $id_centros Id de centro
     * @param integer $id_usuarios Id de usuario
     * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
     */
    public function getAll ($start='',$page='',$id_cursos=0,$id_areas=0,$id_centros=0,$id_usuarios=0 ){
    	$sql =  " SELECT {$this->tabla}.*,tbl_cursos.nombre as curso , ";
    	$sql .= " tbl_centros.nombre as centro, tbl_areas.nombre as area, ";
		$sql .= " tbl_usuarios.nombre, tbl_usuarios.apellidos ";
		$sql .= " FROM {$this->tabla} ";
    	$sql .= " LEFT JOIN tbl_cursos   ON tbl_cursos.id	= {$this->tabla}.id_cursos ";
    	$sql .= " LEFT JOIN tbl_areas    ON tbl_areas.id	= tbl_cursos.id_areas ";
    	$sql .= " LEFT JOIN tbl_centros  ON tbl_centros.id	= {$this->tabla}.id_centros ";
    	$sql .= " LEFT JOIN tbl_usuarios ON tbl_usuarios.id	= {$this->tabla}.id_profesores ";
    	$sql .= " WHERE 1   ";
    	if($id_cursos!=0){
    		$sql .= " AND id_cursos=$id_cursos   ";
    	}
    	if($id_areas!=0){
    		$sql .= " AND id_areas=$id_areas   ";
    	}
    	if($id_centros!=0){
    		$sql .= " AND id_centros=$id_centros   ";
    	}
    	if($id_usuarios!=0){
    		$sql .= " AND id_usuarios=$id_usuarios   ";
    	}
        $sql .= " ORDER BY tbl_ofertas.id    ";
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
            	$dtd=  'ofertas [';
            	$dtd.= '<!ENTITY nbsp "&#160;">';
            	$dtd.= '<!ELEMENT ofertas (oferta*)>  ';
            	$dtd.= '<!ELEMENT oferta 	(';
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
                $rootTag=$xml->createElement('ofertas');
                for($k=0; $k<count($arrRows);$k++){
                    $itemTag=$xml->createElement('oferta');
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
    public function pagination($pag, $reg, $id_cursos=0,$id_areas=0,$id_centros=0,$id_usuarios=0){
        $this->registros=$reg;
        if (!$pag) {
            $this->inicio = 0;
            $this->pagina = 1;
        } else {
            $this->pagina=$pag;
            $this->inicio = ($this->pagina - 1) * $this->registros;
        }
        /** Capturem el número total de registres*/
        $sql  = " SELECT * FROM tbl_ofertas ";
		$sql .= " INNER JOIN tbl_cursos ON tbl_cursos.id=tbl_ofertas.id_cursos ";
        $sql .= " INNER JOIN tbl_areas ON tbl_areas.id=tbl_cursos.id_areas ";
		$sql .= " WHERE 1 ";
        if($id_cursos!=0){
        	$sql .= " AND id_cursos=$id_cursos   ";
        }
        if($id_areas!=0){
        	$sql .= " AND id_areas=$id_areas   ";
        }
        if($id_centros!=0){
        	$sql .= " AND id_centros=$id_centros   ";
        }
        if($id_usuarios!=0){
        	$sql .= " AND id_usuarios=$id_usuarios   ";
        }
        $sql .= " ORDER BY tbl_ofertas.id    ";

        $rsRows=$this->mysql->query($sql);
        $this->total_registros =    $rsRows->num_rows ;
        /** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
        $this->total_paginas = ceil($this->total_registros / $this->registros);
        return $this->getAll($this->inicio,$this->registros,$id_cursos,$id_areas,$id_centros,$id_usuarios);
    }

}
?>