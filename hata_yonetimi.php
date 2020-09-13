<?php
$old_error_handle=set_error_handler("hatayakala",E_ALL); // hata yakalayıcı değiştirme ,E_USER_WARNING
function hatayakalaMA(){}
function hatayakala($errno, $errstr, $errfile, $errline)
  {
  $ek="";
  $err="HATA";
  switch ($errno)
  {
 	case 2:
 		$err="UYARI";
 		$ek="(Hata bize bildirildi)";
 		break;
  	case 8:
 		$err="ONEMLi BiLGi";
 		$ek="(Hata bize bildirildi)";
 		break;
  	case 256:
 		$err="KULLANICI HATASI";
 		$errfile="";
 		break;
  	case 512:
 		$err="KULLANICIYA UYARI";
 		break;
  	case 1024:
 		$err="DiKKAT";
 		break;
  	case 4096:
 		$err="KURTARILABiLiR HATA";
 		$ek="(Hata bize bildirildi)";
 		break;
   	case 8192:
 		$err="HATA";
 		$ek="(Hata bize bildirildi)";
 		break;
  }
  echo "<center><hr><h3>! <font style='BACKGROUND-COLOR: yellow;' color=red>$err : $errstr <br> <i> $errfile </i> @$errline<br></font></h3>$ek <hr></center>";

  }
  function xdie($msj){
  echo substr($msj,0,3);
  hatayakala(0,$msj,0,0);
  die();
}

?>