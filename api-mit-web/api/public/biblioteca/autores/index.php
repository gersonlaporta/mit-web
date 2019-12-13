<?php
/**
 * API CIFO /api/public/biblioteca/autores
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

include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/autores.php';

if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
  $arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
  $arrURI=[];
}


switch ($_SERVER ['REQUEST_METHOD']) {
  case 'GET' :
    $objAutor = new autor ( 'json' );
    if (isset ( $arrURI [0] )) {    // /api/public/biblioteca/autores/{id}
      
      echo '{ 
          "status"  : true,
		   "msg"	:"",
          "record"  :'. $objAutor->get ( $arrURI [0] ).'
        }';
    } else {              // /api/public/biblioteca/autores
      
      echo '{
          "status"  : true,
		   "msg"	:"",
          "records" : '.$objAutor->getAll().'
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