<?php
/**
 * API CIFO /api/public/formacion/centros
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

include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/centros.php';

if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
	$arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
	$arrURI=[];
}


switch ($_SERVER ['REQUEST_METHOD']) {
	case 'GET' :
		$objCentro = new centro ( 'json' );
		if (isset ( $arrURI [0] )) {		// /api/public/formacion/centros/{id}
			
			echo '{ 
					"status"	: true,
					"msg"		: "",
					"record"	:'. $objCentro->get ( $arrURI [0] ).'
				}';
		} else {							// /api/public/formacion/centros
			
			echo '{
					"status"	: true,
					"msg"		: "",
					"records"	: '.$objCentro->getAll().'
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
    