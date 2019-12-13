<?php
/**
 * API CIFO /api/private/cifo/roes
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
header ('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header ('Access-Control-Allow-Headers: Access-Control-Allow-Headers,authorization, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
// ------------------------ includes de class --------------------
include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/roles.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/include/usuarios.php';


if(isset($_SERVER ['REDIRECT_QUERY_STRING'])){
  $arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
}else{
  $arrURI=[];
}
if($_SERVER ['REQUEST_METHOD']=='OPTIONS'){
	header ( 'HTTP/1.1 200 OK');
}else{
	// ------------------------- User Password HTTP Basic Autentication ---------------
	$user   	= (isset($_SERVER['PHP_AUTH_USER']))? $_SERVER['PHP_AUTH_USER'] :false ;
	$password   = (isset($_SERVER['PHP_AUTH_PW']))?   $_SERVER['PHP_AUTH_PW']   :false ;
	
	// -------------------------- No User Or Password ---------------------------------
	if($user==false || $password==false){
		header ( 'HTTP/1.1 401 Unauthorized' );
		header ('WWW-Authenticate: Basic realm="Api Rest CIFO"');
		echo json_encode(array('status'=>false,'msg'=>'Need authorization'));
		exit;
	}
	
	// --------------------------- Autentication --------------------------------------
	$objUsuario = new usuario ( 'json' );
	$arrUser= $objUsuario->login($user,$password,"1"); //(email,password,id_roles=1 [Administrador])
	
	// -------------------------- Autentication error ---------------------------------
	if(!$arrUser){
		header ( 'HTTP/1.1 403 Forbidden' );
		echo json_encode(array('status'=>false,'msg'=>'Authorization failed'));
		exit;
	}
	switch ($_SERVER ['REQUEST_METHOD']) {
		case 'GET' :
			$objRol = new rol ( 'json' );
			if (isset ( $arrURI [0] )) {		// /api/private/cifo/roles/{id}
				echo '{
						"status"	: true,
						"msg"		: "",
						"record"	:'. $objRol->get ( $arrURI [0] ).'
					}';
			} else {							// //api/private/cifo/roles
				echo '{
						"status"	: true,
						"msg"		: "",
						"records"	: '.$objRol->getAll().'
					}';
			}
			break;
		case 'POST' :
			$data = json_decode ( file_get_contents ( "php://input" ) );
			$objRol = new rol ( 'json' );
			echo $objRol->insert ( $data );
			break;
		case 'PUT' :
			$data = json_decode ( file_get_contents ( "php://input" ) );
			$objRol = new rol( 'json' );
			if (isset ( $arrURI [0] )) {     	// api/private/usuarios/id
				echo $objRol->update ( $arrURI [0], $data );
			} else {                			// api/private/usuarios
				echo $objRol->update ( $data->id, $data );
			}
			break;
		case 'DELETE' :
			$data = json_decode ( file_get_contents ( "php://input" ) );
			$objRol = new rol ( 'json' );
			if (isset ( $arrURI [0] )) {
				echo $objRol->delete ( $arrURI [0] );
			} else {
				echo $objRol->delete ( $data->id );
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
}