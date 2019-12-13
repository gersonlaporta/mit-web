<?php
/*use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'].'/include/phpmailer/src/SMTP.php';

$mail= new PHPMailer();
//Server settings
$mail->SMTPDebug = 1;            // Enable verbose debug output
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'webmaster.cifo@gmail.com';                 // SMTP username
$mail->Password = '1dm3n.c3f4';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;
$mail->CharSet = "UTF-8";

$mail->setFrom("test@hotmail.com","test");
$mail->addAddress('webmaster.cifo@gmail.com','Webmaster Cifo');
$mail->Subject='Mensaje Contacto web.cifo.local';
$mail->isHTML(true);
$mail->Body= "Test";
if($mail->send()){
 echo "OK";
}else {
	echo $mail->ErrorInfo;
	
}*/

include_once $_SERVER['DOCUMENT_ROOT'].'/include/libros.php';
$libros= new libro('xml');
?>
<div id="demo"></div>
<script type="text/javascript">

function myLoop(x) {
    var i, y, xLen, txt;
    txt = "";
    x = x.childNodes;
    xLen = x.length;
    for (i = 0; i < xLen ;i++) {
      y = x[i];
      if (y.nodeType != 3) {
        if (y.childNodes[0] != undefined) {
          txt += myLoop(y);
        }
      } else {
      txt += y.nodeValue + "<br>";
      }
    }
    return txt;
  }
  var xml=`<?= $libros->getAll(); ?>`;
  var parser, xmlDoc;
  parser = new DOMParser();
  xmlDoc = parser.parseFromString(xml,"text/xml");

  document.getElementById("demo").innerHTML = myLoop(xmlDoc.documentElement);
  

  

  

</script>
