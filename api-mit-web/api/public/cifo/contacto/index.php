<?php
/**
 * API CIFO /api/public/cifo/contacto
 *
 * Punt d'entrada de API REST de l'aplicació CIFO
 *
 * @author Quim Aymerih <quim.aymerich@gmail.com>
 * @copyright 2019 Quim Aymerih
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version 2019-10-01
 * @link https://gitlab.com/quim.aymerich/app.cifo.local
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/SMTP.php';


header ( 'content-type:application/json; charset=UTF-8' );
header ( 'Access-Control-Allow-Origin: *' );
header ( 'Access-Control-Allow-Credentials: true' );
header ( 'Access-Control-Allow-Methods:POST,OPTIONS' );
header ( 'Access-Control-Allow-Headers: Access-Control-Allow-Headers,authorization, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers' );

if (isset ( $_SERVER ['REDIRECT_QUERY_STRING'] )) {
	$arrURI = explode ( "/", $_SERVER ['REDIRECT_QUERY_STRING'] );
} else {
	$arrURI = [ ];
}
// $user = (isset($_SERVER['PHP_AUTH_USER']))? $_SERVER['PHP_AUTH_USER'] :"" ;
// $password = (isset($_SERVER['PHP_AUTH_PW']))? $_SERVER['PHP_AUTH_PW'] :"" ;

switch ($_SERVER ['REQUEST_METHOD']) {
	
	case 'POST' :
		$data = json_decode ( file_get_contents ( "php://input" ) );
		
		if(isset($data->{'g-recaptcha-response'})){
		
			// ======================== API POST reCaptcha Google ==============================
			$curl = curl_init();
			
			$recaptcha_verify_url 		= "https://www.google.com/recaptcha/api/siteverify";
			$recaptcha_response 		= htmlspecialchars($data->{'g-recaptcha-response'});
			$recaptcha_site_secret		= "6LfUyLoUAAAAADQQcaCkR8jWEelOEGFEnE_rhfAz";
			
			curl_setopt($curl, CURLOPT_VERBOSE, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_URL,$recaptcha_verify_url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, "secret=".$recaptcha_site_secret."&response=".$recaptcha_response);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$recaptcha_output = curl_exec ($curl);
			if ($recaptcha_output === FALSE) {
				var_dump(curl_getinfo($curl));
				var_dump(curl_error($curl));
				exit;
			}
			curl_close ($curl);
			
			$decoded_captcha = json_decode($recaptcha_output);
			
			if( $decoded_captcha->success === FALSE){ //validation result to a variable.
				echo '{
	          			"status"  : false,
						"msg"	  : "",
	          			"records" :'. json_encode($decoded_captcha->{'error-codes'}).'
	       			  }';
				exit; // Return if the captcha is invalid
			}
		
		}
		$mail= new PHPMailer();
		//Server settings
		$mail->SMTPDebug = 0;            // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'webmaster.cifo@gmail.com';                 // SMTP username
		$mail->Password = '1dm3n.c3f4';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;
		$mail->CharSet = "UTF-8";
		$mail->setFrom('webmaster.cifo@gmail.com','Webmaster Cifo');
		$mail->AddReplyTo($data->email,$data->nombre);
		$mail->addAddress('webmaster.cifo@gmail.com','Webmaster Cifo');
		$mail->Subject='Mensaje Contacto web.cifo.local';
		$mail->isHTML(true);
		$mail->Body= $data->message;
		if($mail->send()){
			echo '{
        			"status"  : true,
					 "msg"	  : "",
        			"records" : "Mensaje enviado con exito."
       		}';
			exit;
		}else {
			echo '{
        			"status"  : false,
					"msg"	  : "",
        			"records" : "'. $mail->ErrorInfo.'"
       			}';
			exit;
		}
		break;
	case 'OPTIONS' :
		header('HTTP/1.1 200 OK');
		break;
	default :
		header ( 'HTTP/1.1 405 Method Not Allowed' );
		header ( 'Allow:  POST,OPTION' );
		break;
}

?>