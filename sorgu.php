<?php
$zaman=date("Y-m-d H:i:s"); 
$starttime = microtime();
$starttime = explode(" ", $starttime);
$starttime = $starttime[1] + $starttime[0];
function gecikme(){
	global $starttime;
$time = microtime();
$time = explode(" ", $time);
$time = $time[1] + $time[0];
return ($time - $starttime);
}
ob_start(); 
require_once "api_parola.php";
$apiparola = isset($_GET["apiparola"]) ? $_GET["apiparola"] : "";
$apiparola .= isset($_POST["apiparola"]) ? $_POST["apiparola"] : "";
$onay=false;
foreach ($API_PAROLALARI as $parola)
	{
		if ($parola==$apiparola) $onay=true;
	}
if(!$onay){
	echo "YANLIŞ API PAROLASI";
	exit;
}

$txt = '<?php
require_once "hata_yonetimi.php";
require_once "fc_ram.php";
';
$txt .= isset($_GET["komut"]) ? $_GET["komut"] : "";
$txt .= isset($_POST["komut"]) ? $_POST["komut"] : "";
$txt .='
?>';
        if(file_exists("exec.php"))
        	unlink('exec.php');
        $dosya=fopen("exec.php","a+");
        fwrite($dosya,$txt);
        fclose($dosya);
include('exec.php');
unlink('exec.php');

if ( isset($_GET["geridon"])){
	session_start();
	$_SESSION["gecikme"]=gecikme();
	$_SESSION["cmd"]= isset($_GET["komut"]) ? $_GET["komut"] : "";
	$_SESSION["cmd"].=isset($_POST["komut"]) ? $_POST["komut"] : "";
	$_SESSION["donen"]=ob_get_contents();
	yonlendir('fc_ram_gui.php');
}
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
function help(){
	YARDIM();
}
function YARDIM(){
	echo '<img src="sema.png"/><br>Yapı:<br>
Ana Kontrolcu -> veri tabanlarını kontrol eder<br>
Veri tabanı -> Veri tabanları ad verilmez. No verilir. (1den65555e kadar) içinde DeğişkenDökümanlarını ve onların adını <br>saklar<br>
DeğişkenDökümanı -> 2 türlü olabilir. <br>
1. JSON Şekline getirmiş php değişkeni saklar <br>
2. si salt yazı/string saklar<br>
<br>
Sorgu sistemi:<br>
sorgu sisteminde , sorgulama PHP dilinde yapılır.Buda büyük özgürlükler sağlar. NOT: her satırdan sonra \";\" kullanın<br>
eğer fc_ram php kodlarınınız arada kullanıyorsunuz. classı kopyala/yapıştır yapıp kullanabilrsiniz.<br>
eğer phpWebGui veya dışardan erişiyorsanınız. sorgu.php \'ye GET veya POST ile \'apiparola\' ve \'komut\' değerlerini <br>göndermeniz gerekir. parola doğruysa sorgu.php sorgunuzu işleyip geri döndürür.<br>
<br>
Veri Tabanı Oluşturma :: $ram o an oluşturulan veritabanının seçim değişkenidir. Farklı isim verebilirsiniz<br>
$ram=new fc_ram(VT_NO,VT_BOYUT);<br>
<br>
Veri tabanını seçme $ram o an oluşturulan veritabanının seçim değişkenidir. Farklı isim verebilirsiniz<br>
$ram=new fc_ram(VT_NO);<br>
<br>
Veri tabanından php değişkeni alma/okuma SELECT<br>
$alınanphpdeğişken = $ram->oku(\'ramdaki dökümandegisken adı\');<br>
<br>
Veri tabanından yazı/string alma/okuma SELECT<br>
echo $ram->okudata(\'ramdaki dökümandegisken adı\');<br>
$phpstring = okudata(\'ramdaki dökümandegisken adı\');<br>
<br>
Veri tabanıda php değişkeni ekleme/oluşturma INSERT INTO ve UPDATE<br>
$ram->yaz(\'değişkeninVTdekiadı\' , $phpdegiskeni );<br>
<br>
Veri tabanıda php değişkenini değiştirme INSERT INTO ve UPDATE<br>
$ram->yaz(\'değişkeninVTdekiadı\' , $phpdegiskeni );<br>
<br>
Veri tabanıda php değişkenini silme DELETE <br>
$ram->yaz(\'değişkeninVTdekiadı\' , NULL );<br>
<br>
Veri tabanına yazı/string eklme/gönderme/oluşturma INSERT INTO ve UPDATE<br>
$ram->yazdata(\'değişkeninVTdekiadı\' , \"YAZIMIZZZZZZZZZZZ\" );<br>
$ram->yazdata(\'değişkeninVTdekiadı\' , $phpstring );<br>
<br>
Veri tabanında ki yazı/string değiştirme INSERT INTO ve UPDATE<br>
$ram->yazdata(\'değişkeninVTdekiadı\' , \"YAZIMIZZZZZZZZZZZ\" );<br>
$ram->yazdata(\'değişkeninVTdekiadı\' , $phpstring );<br>
<br>
Veri tabanında ki yazı/string i silme DELETE<br>
$ram->yazdata(\'değişkeninVTdekiadı\' , NULL );<br>
<br>
<br>
eğer $php değişkeninden 2 boyutlu array (yani tablo) alıyorsanınız. sql deki ORDER BY, WHERE veya LIMIT<br>
fonksiyonlarını karşılamak için. PHPde foreach , sort , unset fonksiyonlarını öğrenmeniz gerekir. (bknz. <br>http://www.php.net)<br>
<br>
sorgulan şeyi almak için<br>
echo $phpstring;<br>
echo $ram->okudata(\'ramdaki dökümandegisken adı\');<br>
var_dump($phpdeğişkeni);<br>
var_dump($ram->oku(\'ramdaki dökümandegisken adı\'));<br>
<br>
fonksiyonlar:<br>
$ram->yaz : bir php değişkenini türü ne olursa olsun JSON şekline çevirip ramde saklar<br>
$ram->yazdata : bir yazıyı veya dökümanı ek işlem yapmadan RAMe yazar<br>
$ram->oku : veritabanındaki JSON şekline çevrilmiş php değişkenini geri getirir<br>
$ram->okudata : bir yazıyı / dökümanı veya JSON şeklindeki php değişkeni json olarak okur<br>
$ram->kapat : değişken arrayındaki son değişiklikleri ram kaydeder. <br>
$ram->log : ram bilgi kaydı ekler<br>
$ram->vt_kontrolcu: ramda veritabanı yoksa yeni birtane oluşturur ve bu ram boşalana kadar devam eder. oluştururken <br>yukle fonksiyonunu çalıştırır.<br>
$ram->yukle: oclass verride idilerek kullanılır. veritabanı ilk oluşturuldugunda yapılacak şeylerdir<br>
<br>
# fc_RAM classı hakkında<br>
#  fc_RAM class by Ö. Faruk CAN<br>
#  Bu bir in-memory database\'dir. PHPde çalışır. MongoDB den ilham alınarak tasarlanmıştır. Veritabanı ve <br>DeğişkenDökümanı sistemi vardır.<br>
#  Bu Kütüphane verileri RAMde sürekli şekilde saklama yeteneğine sahip bir veritabanı görevi görür<br>
#  Veriler JSON halinde RAMde saklanır<br>
#  Veri performansı arttırır. Sık kullanılanın verilerde kullanmanız tavsiye edilir :)<br>
#  (Windows.PHP 5.3 üzerinde) Yaklaşık 0.006 sn de işlem görür. localhost üzerine kurulu MySQLden yaklaşık 4 kat daha <br>hızlıdır.<br>
# - Gereksinimler:<br>
#  	*php_shmod eklentisi gerektirir ki bu birçok sunucuda kurulu halde gelir :)<br>
#  	*xdie() fonksiyonu gerektirir.Bu hataları yönetime bildirmek içindir. yoksa die() kullanabilirsin. fonksiyon <br>için farukcan.net<br>
# - Kullanımım:<br>
# Bu veritabanında veritabanlarına ad verilmez, numara verilir. Bir veritabanı söyle seçilir:
# 	* $ram=new fc_Ram([veritabanı nosu : 1],[veritabanı boyutu : 32000*byte*]); // [SEÇİME BAĞLI] veritabanı nosu 1 <br>den 66555e kadar desteklenir. <br>
#	* $ram->yaz(\'dökümandeğişkeniadı\' , $phpdegiskeni); // phpdeki her türlü arrayı class saklayabilirsiniz. mysql <br>de altığının 2 boyutlu arrayları bile.<br>
#	* $phpdegiskeni = $ram->oku(\'dökümandeğişkeniadı\'); //değişkeni önceden gönderdiğiniz gibi alırsınız.<br>
# NOT : -WHERE- & -ORDER BY- komutları nerde diyorsanır. PHPnin sort ve array fonksiyonları göz atın.<br>
# NOT : Vt boyutu maximum kapasitesine ulaştığı zaman hata verir ve sıfırlanır. Lütfen Max Boyutu ona göre ayarlatın.<br>
# NOT : Classlara RAMdan değerlerini atamak için classı  extends ramdanyuklebilir yapmanız gerekir. ramdanyuklebilir <br>classı için farukcan.net<br>';
	}
ob_end_clean();
?>

