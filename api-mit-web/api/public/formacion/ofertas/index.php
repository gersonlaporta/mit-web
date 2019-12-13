<?php
/**
 * API CIFO /api/public/formacion/ofertas
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

include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/ofertas.php';

if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
  $arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
  $arrURI=[];
}

switch ($_SERVER ['REQUEST_METHOD']) {
  case 'GET' :
  	$objOferta = new oferta ( 'json' );
  	if (count( $arrURI)>2) {    // /api/public/formacion/ofertas/?
  		$id_cursos=0;
  		$id_areas=0;
  		$id_centros=0;
  		for($i=0 ; $i<count($arrURI);$i++){
  			if($arrURI[$i]=="curso"){ $id_cursos=$arrURI[$i+1];}
  			if($arrURI[$i]=="area"){ $id_areas=$arrURI[$i+1];}
  			if($arrURI[$i]=="centro"){ $id_centros=$arrURI[$i+1];}
  		}
  		
  		// /api/public/formacion/ofertas/area/{id_areas}/curso/{id_cursos}/centro/{id_centros}
  			echo '{
		          "status"  : true,
				  "msg"		: "",
		          "records" : '.$objOferta->getAll('','',$id_cursos,$id_areas,$id_centros ).'
		        }';
  	}else if(count( $arrURI)>=1){ // /api/public/formacion/ofertas/{id}
  		echo '{
		          "status"  : true,
				  "msg"		: "",
		          "record"  :'. $objOferta->get ($arrURI [0] ).'
      		}';
  		
  	} else {              // //api/public/formacion/ofertas
  		echo '{
          "status"  : true,
		  "msg"		: "",
          "records" : '.$objOferta->getAll().'
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