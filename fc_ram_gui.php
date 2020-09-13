<?php
function yonlendir($url){
    if (!headers_sent()){  
        header('Location: '.$url); exit; 
    }else{ 
        $sayfaKod.= '<script type="text/javascript">'; 
        $sayfaKod.= 'window.location.href="'.$url.'";'; 
        $sayfaKod.= '</script>'; 
        $sayfaKod.= '<noscript>'; 
        $sayfaKod.= '<meta http-equiv="refresh" content="0;url='.$url.'" />'; 
        $sayfaKod.= '</noscript>'; exit; 
    }
}
session_start();
if(!isset($_COOKIE["APIPAROLASI"]))
{
	if(isset($_POST["APIPAROLASI"]))
	{
		setcookie("APIPAROLASI", $_POST["APIPAROLASI"], time()+3600);
		yonlendir("fc_ram_gui.php");
	}
	else
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><form action="fc_ram_gui.php" method="POST"> <h1>ERİŞİM YASAK</h1>API PAROLASI :<input id="APIPAROLASI" name="APIPAROLASI" type="text" /><input type="submit" /></form>* api_parola.php ye göz atın.';
		exit;
	}
}
require_once "api_parola.php";
$onay=false;
foreach ($API_PAROLALARI as $parola)
	{
		if ($parola==$_COOKIE["APIPAROLASI"]) $onay=true;
	}
if (!$onay) {
	setcookie("APIPAROLASI", "", time()-3600);
	yonlendir("fc_ram_gui.php");
}
$apikey=$_COOKIE["APIPAROLASI"];
$cmd = isset($_SESSION["cmd"]) ? $_SESSION["cmd"] : '$ram = new fc_ram(1);' . "\nYARDIM();";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="stil.css">
<title>fc_ram GUI - fc_ram Veritabanı Arayüzü</title>
</head>
<body><center>
<img src="icon.png"/><font size=50> fc_ram </font>inmemory/nosql DB by farukcan.net<br> <br>
< ? php <br>require_once "hata_yonetimi.php";<br>require_once "fc_ram.php"; 
<form action="sorgu.php?geridon" method="POST"><textarea id='komut' name='komut' ><?php echo $cmd; ?></textarea><br>? ><br>
<input  id='apiparola' name='apiparola' value="<?php echo $apikey; ?>" type="hidden" />
<input value="Komudu Çalıştır" type="submit" />
</form>

<?php

require_once "hata_yonetimi.php"; // fc ram için gerekli hata fonksiyonları
require_once "fc_ram.php"; // fcanın kendisi
echo  isset($_SESSION["donen"]) ? "<h1>Sonuç:</h1><hr>". $_SESSION["donen"]."<hr>" : "";
echo isset($_SESSION["gecikme"]) ? "Sorgulama " . $_SESSION["gecikme"] . "sn sürdü" : "";
$_SESSION="";
session_destroy();
$gui = new fc_ram(999,500);
$gui->kontrol["vtler"];
$vtler=explode(';',$gui->kontrol["vtler"]);
$ram_vt = array();
foreach ($vtler as $vt){
	settype($vt,'int');
	if (($vt!=999) AND ($vt!=0)){
	$boyutu=$gui->kontrol["vt" .  $vt . "_maxboyut"];
	$ram_vt[$vt]= new fc_ram($vt,$boyutu);		
	}
}
echo "<h3>KAYITLAR</h3>" . $gui->kontrol["log"];
if(is_array($ram_vt))
{
	foreach  ($ram_vt as $vt)
		{
			$by=$gui->kontrol["vt" .  ($vt->vtno-1)/2 . "_boyut"];
			$by_max=$gui->kontrol["vt" .  ($vt->vtno-1)/2 . "_maxboyut"];
			$by_oran=$by/$by_max*100;
			echo "<hr><h1>vt".($vt->vtno-1)/2 ."</h1> <b>Boyut</b>: ".$by."/<i>".$by_max."</i>byte ($by_oran %)<table border=1><tr><td><b>DökümanAdı</b></td><td><b>Döküman</b></td><tr>";
			if(is_array($vt->ram_vt))
			{
				foreach ($vt->ram_vt as $dokumanad => $dokuman){
				echo "<tr><td> $dokumanad</td><td> " . $dokuman ."</td></tr>";
				}	
			}

		echo "</table>";
		}
}
echo "<br><br><img src='fc_ram.png'/><br>";
echo($gui->kontrol['vtler']);	
?></center></body></html> 