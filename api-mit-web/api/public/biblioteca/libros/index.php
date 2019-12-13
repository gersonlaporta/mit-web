<?php
/**
 * API CIFO /api/public/biblioteca/libros
 *
 * Punt d'entrada de API REST de l'aplicació CIFO
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-10-01
 * @link https://gitlab.com/quim.aymerich/app.cifo.local
 */
header ('content-type:application/json; charset=UTF-8' );
header ('Access-Control-Allow-Origin: *' );
header ('Access-Control-Allow-Credentials: true');
header ('Access-Control-Allow-Methods:GET,OPTIONS');
header ('Access-Control-Allow-Headers: Access-Control-Allow-Headers,authorization, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');

include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/libros.php';

if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
  $arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
  $arrURI=[];
}

switch ($_SERVER ['REQUEST_METHOD']) {
  case 'GET' :
    $objLibro = new libro ( 'json' );
    if (count( $arrURI)>2) {    // /api/public/biblioteca/libros/?
      $id_editoriales=0;
      $id_temas=0;
      $id_autores=0;
      $id_secciones=0;
      for($i=0 ; $i<count($arrURI);$i++){
      	if($arrURI[$i]=="editorial"){ 	$id_editoriales=$arrURI[$i+1];	}
      	if($arrURI[$i]=="tema"){ 		$id_temas=$arrURI[$i+1];		}
      	if($arrURI[$i]=="seccion"){ 	$id_secciones=$arrURI[$i+1];		}
      	if($arrURI[$i]=="autor"){ 		$id_autores=$arrURI[$i+1];		}
      }
      
      // /api/public/biblioteca/libros/editorail/{$id_editoriales}/tema/{$id_temas}/autor/{$id_autores}
        echo '{
              "status"  : true,
			  "msg"		:"",
              "records" : '.$objLibro->getAll('','',$id_editoriales,$id_temas,$id_secciones,$id_autores ).'
            }';
    }else if(count( $arrURI)>=1){ // /api/public/biblioteca/libros/{id}
      echo '{
              "status"  : true,
			  "msg"		: "",
              "record"  :'. $objLibro->get ($arrURI [0] ).'
          }';
      
    } else {              // //api/public/formacion/ofertas
      echo '{
          "status"  : true,
	      "msg"		: "",
          "records" : '.$objLibro->getAll().'
        }';
    }
    break;
  case 'OPTIONS' :
    header('HTTP/1.1 200 OK');
    break;
  default :
    header ( 'HTTP/1.1 405 Method Not Allowed' );
    header ( 'Allow: GET, OPTIONS' );
    break;
}