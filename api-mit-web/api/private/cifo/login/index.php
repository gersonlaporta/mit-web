<?php
/**
 * API CIFO /api/private/login
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
header ('Access-Control-Allow-Methods:POST,OPTIONS');
header ('Access-Control-Allow-Headers: Access-Control-Allow-Headers,authorization, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
// ------------------------ includes de class --------------------

include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/usuarios.php';

// ------------------------- Query String Parse ------------------
if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
  $arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
  $arrURI=[];
}

switch ($_SERVER ['REQUEST_METHOD']) {
		
	case 'POST' :
		$data = json_decode ( file_get_contents ( "php://input" ) );
		$objUsuario = new usuario ( 'json' );
		$arrUser= $objUsuario->login($data->user,$data->password,1); // ( email, password, id_roles [1=Administradores] )
		// -------------------------- Autentication error ---------------------------------
		if(!$arrUser){
			echo json_encode(array('status'=>false,'msg'=>'Login failed'));
			exit;
		}else{
			echo json_encode(array('status'=>true,'msg'=>'Login successful'));
			exit;	
		}
		break;
	
	case 'OPTIONS' :
		header ( 'HTTP/1.1 200 OK');
		break;
	default :
		header ( 'HTTP/1.1 405 Method Not Allowed' );
		header ( 'Allow: GET, OPTIONS' );
}