<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
/**
 * Class usuario
 *
 * Class de usuario con los metodos de acceso a la tabla de tbl_usuarios
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-01-01
 * @link https://github.com/joraymes/cifo
 */
class usuario extends db {

    /**
	 * Constructor de la class usuario
	 * @param string $type Definición del formato de salida
     * @example strin|null    : Array associativo| Recordset
     * @example string 'json' : JSON
     * @example string 'xml'  : Documento XML
     * @return null
     */
    public function __construct($type='') {
    	parent::__construct($type);
    	$this->tabla='tbl_usuarios';
    }

    /**
     * login: Inicio de sesión
     * @param string $email Dirección nde correo del usuario
     * @param string $password La contraseña sin encriptar
     * @param integer $id_roles id del rol de usuario (1=>Administrador, 2=> Profesor , 3=> Alumno,usuario)
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
     */
    public function login($email,$password,$id_roles=0){
    	
    	$where= ($id_roles!=0)? " AND {$this->tabla}.id_roles=$id_roles    ": "" ;
    	
    	$sql="SELECT {$this->tabla}.*
            FROM {$this->tabla}
            WHERE {$this->tabla}.email='$email' AND
			{$this->tabla}.password=PASSWORD('$password') $where ";
		//echo $sql;
    	$rsRows=$this->mysql->query($sql);

    	// Fields Information
    	$fields= $rsRows->fetch_fields();
    	$first_field=true;

    	$arrRow=$rsRows->fetch_assoc();

    	if($arrRow==null){
    		return false;
    	}else{
    		switch($this->returnType){
    			case 'json' :
    				return json_encode($arrRow);
    				break;
    			case 'xml':
    				$dom = new DOMImplementation();
    				$dtd=  'usuario [';
    				$dtd.= '<!ENTITY nbsp "&#160;">';
    				$dtd.= '<!ELEMENT usuario (';
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

    				$rootTag=$xml->createElement('usuario');
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

    }
    /**
     * get: Obtener usuario por id
     * @param integer $id Valor de ID
     * @return boolean | string | array $arrRow  Serà un JSON o un XML o un Array associativo
    */
    public function get($id){
        $sql="SELECT {$this->tabla}.*, tbl_roles.nombre AS rol
            FROM {$this->tabla}
			LEFT JOIN tbl_roles ON tbl_roles.id={$this->tabla}.id_roles
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
                	$dtd=  'usuario [';
                	$dtd.= '<!ENTITY nbsp "&#160;">';
                	$dtd.= '<!ELEMENT usuario (';
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

                    $rootTag=$xml->createElement('usuario');
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
     * Obtener usuarios , permite pasar parametros de paginacion
     * @param integer $start Valor de inicio de registro
     * @param integer $page Valor de numero de registros
     * @return  string | recordset $arrRows  Serà un JSON o un XML o un RecordSet
    */
    public function getAll ($start='',$page='' ,$id_roles=0){
    	
    	$where=($id_roles!=0)? " AND {$this->tabla}.id_roles=$id_roles  " : "" ;
    	
         $sql="SELECT {$this->tabla}.*, tbl_roles.nombre AS rol
            FROM {$this->tabla}
			LEFT JOIN tbl_roles ON tbl_roles.id={$this->tabla}.id_roles
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
            	$dtd=  'usuarios [';
            	$dtd.= '<!ELEMENT usuarios (usuario*)>  ';
            	$dtd.= '<!ELEMENT usuario (';
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
				$rootTag=$xml->createElement('usuarios');
                for($k=0; $k<count($arrRows);$k++){
                    $itemTag=$xml->createElement('usuario');
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
    public function pagination($pag,$reg,$id_roles=0){
        $this->registros=$reg;
        if (!$pag) {
            $this->inicio = 0;
            $this->pagina = 1;
        } else {
            $this->pagina=$pag;
            $this->inicio = ($this->pagina - 1) * $this->registros;
        }

        /** Capturem el número total de registres*/
        
        $where=($id_roles!=0)? " AND {$this->tabla}.id_roles=$id_roles  " : "" ;
        $sql="SELECT * FROM {$this->tabla} WHERE 1 $where";

        $rsRows=$this->mysql->query($sql);
        $this->total_registros =    $rsRows->num_rows ;
        /** Amb ceil arrodonim el resultat total de las paginess 4.53213 = 5 */
        $this->total_paginas = ceil($this->total_registros / $this->registros);

        return $this->getAll($this->inicio,$this->registros,$id_roles);

    }
}
?>