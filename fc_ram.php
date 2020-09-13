<?php
class fc_ram{
#  fc_RAM class by Ö. Faruk CAN
#  Bu bir in-memory database'dir. PHPde çalışır. MongoDB den ilham alınarak tasarlanmıştır. Veritabanı ve DeğişkenDökümanı sistemi vardır.
#  Bu Kütüphane verileri RAMde sürekli şekilde saklama yeteneğine sahip bir veritabanı görevi görür
#  Veriler JSON halinde RAMde saklanır
#  Veri performansı arttırır. Sık kullanılanın verilerde kullanmanız tavsiye edilir :)
#  (Windows.PHP 5.3 üzerinde) Yaklaşık 0.006 sn de işlem görür. localhost üzerine kurulu MySQLden yaklaşık 4 kat daha hızlıdır.
# - Gereksinimler:
#  	*php_shmod eklentisi gerektirir ki bu birçok sunucuda kurulu halde gelir :)
#  	*xdie() fonksiyonu gerektirir.Bu hataları yönetime bildirmek içindir. yoksa die() kullanabilirsin. fonksiyon için farukcan.net
# - Kullanımım:
# Bu veritabanında veritabanlarına ad verilmez, numara verilir. Bir veritabanı söyle seçilir:
# 	* $ram=new fc_Ram([veritabanı nosu : 1],[veritabanı boyutu : 32000*byte*]); // [SEÇİME BAĞLI] veritabanı nosu 1 den 66555e kadar desteklenir. 
#	* $ram->yaz('dökümandeğişkeniadı' , $phpdegiskeni); // phpdeki her türlü arrayı class saklayabilirsiniz. mysql de altığının 2 boyutlu arrayları bile.
#	* $phpdegiskeni = $ram->oku('dökümandeğişkeniadı'); //değişkeni önceden gönderdiğiniz gibi alırsınız.
# NOT : -WHERE- & -ORDER BY- komutları nerde diyorsanır. PHPnin sort ve array fonksiyonları göz atın.
# NOT : Vt boyutu maximum kapasitesine ulaştığı zaman hata verir ve sıfırlanır. Lütfen Max Boyutu ona göre ayarlatın.
# NOT : Classlara RAMdan değerlerini atamak için classı  extends ramdanyuklebilir yapmanız gerekir. ramdanyuklebilir classı için farukcan.net
#  Ayrıntılı bilgi için farukcan.net
var $ram_vt=array();
var $vtno=0;
var $vtnohzr=0;
var $kontrol;
var $boyut;
var $degisim=false;
var $kont_degisim=false;
	function fc_ram($vt=1,$boyut=3200){
		$this->boyut=$boyut;
		$this->ana_kontrolcu();
		$this->vtno=2*$vt+1;
		$this->vtnohzr=2*$vt;
		while(!$this->vt_kontrolcu()) // ramda vt yoksa oluştur
			{/*bekle*/}			
		$id=shmop_open($this->vtno, "a", 0644, 0); //ac
		$veri = shmop_read($id, 0,  shmop_size($id));
		$this->ram_vt=unserialize(trim($veri));
		shmop_close($id);		
	}
	function vt_kontrolcu(){
	$old_error_handle=set_error_handler("hatayakalaMA",E_ALL); // HATA DENETİMİNİ KAPAT error_reporting(0) kullanabilirsin
	$id=shmop_open($this->vtnohzr, "a", 0644, 0) ; //ac
	$veri = shmop_read($id, 0, shmop_size($id) );
	shmop_close($id);		
	$old_error_handle=set_error_handler("hatayakala",E_ALL);  // HATA DENEMETİMİ GERİ AÇ error_reporting(-1)
	if (!$veri=="HAZIR"){
		$this->ram_vt=array("AKTIF" => true);
		$id=shmop_open($this->vtno, "c", 0666, $this->boyut); //ramda yeni bir vt oluştur.
		shmop_close($id);
		$this->kontrol["vt" .  ($this->vtno-1)/2 . "_maxboyut"]= $this->boyut;
		$this->kontrol["vt" .  ($this->vtno-1)/2 . "_boyut"]=0;
		$this->kontrol["vtler"].= ($this->vtno-1)/2 . ';';
		$this->log("Yeni vt oluşturuldu :" . ($this->vtno-1)/2);
		$this->kontrol_kaydet();
		$id=shmop_open($this->vtnohzr, "c", 0666, strlen("HAZIR")); // alan ata vtnin hazır oldugunu bildir
		$shm_bytes_written = shmop_write($id, "HAZIR", 0);
		shmop_close($id); // Veritabanı aktifleşti
		if ($shm_bytes_written !=strlen("HAZIR"))
		    xdie("ERR_SHMOD");
		$this->yukle(); // Yapılacak yüklemeler
		$this->kaydet();
		return false;
	}		
	return true;
	}
	function ana_kontrolcu(){ // bu 1 adlı alanda bütün veri tabanlarını bulundurur.
	$old_error_handle=set_error_handler("hatayakalaMA",E_ALL); 
	$id=shmop_open(1, "a", 0666, 0) ; //ac
	$veri = unserialize(trim(shmop_read($id, 0, shmop_size($id))));
	shmop_close($id);		
	$old_error_handle=set_error_handler("hatayakala",E_ALL); 
		if(!isset($veri["anakontrol"])){

			$veri=array("anakontrol"=>true,"vtler"=>"","log"=> date("Y-m-d H:i:s") . " - kontrol mekanizması oluşturuldu");
			$data=serialize($veri);
			$id=shmop_open(1, "c", 0666, 1000);
			$shm_bytes_written = shmop_write($id,$data, 0);
			shmop_close($id);		
			$this->kontrol=$veri;
			if ($shm_bytes_written !=strlen($data))
			    xdie("ERR_SHMOD");			
		}
		else
		{
			$this->kontrol=$veri;
		}
	}
	function log($log){
		$this->kontrol["log"].= "<br>" . date("Y-m-d H:i:s") . "- " . $log;
		$this->kont_degisim=true;
	}
	function kontrol_kaydet(){
		$id=shmop_open(1, "w", 0666, 0);
		$data=serialize($this->kontrol);
		$shm_bytes_written = shmop_write($id,$data,0);
		if ($shm_bytes_written !=strlen($data))
		    xdie("ERR_SHMOD");		
		shmop_close($id);
		$this->degisim=FALSE;
	}
	function yukle(){
		# buraya yaz() komutundan oluşan satırlar eklenir. override edilebilir. rama ilk yüklenen dökümanlardır.
	}	
	function yaz($degisken,$deger){
		if($deger==null){
			if(isset($this->ram_vt[$degisken])){
				unset($this->ram_vt[$degisken]);
				$this->degisim=true;
			}	
		}
		else
		{
		$this->ram_vt[$degisken]=serialize($deger);
		$this->degisim=true;
		}
	}
	function yazdata($degisken,$deger){
		if($deger==null){
			if(isset($this->ram_vt[$degisken])){
				unset($this->ram_vt[$degisken]);
				$this->degisim=true;
			}	
		}
		else
		{
		settype($deger,'string');
			$this->ram_vt[$degisken]=$deger;
			$this->degisim=true;			
		}	
	}
	function oku($degisken){
		if(isset($this->ram_vt[$degisken]))
			return unserialize($this->ram_vt[$degisken]);
		else
			return null;
	}
	function okudata($degisken){
		if(isset($this->ram_vt[$degisken]))
			return $this->ram_vt[$degisken];
		else
			return null;		
	}
	function kaydet(){
		$veri=serialize($this->ram_vt);
		$id=shmop_open($this->vtno, "w",0666,0);
		$shm_bytes_written = shmop_write($id,$veri,0);
		shmop_close($id);
		$len=strlen($veri);
		$this->kontrol["vt" .  ($this->vtno-1)/2 . "_boyut"]=$len;
		$this->kont_degisim=true;
		if ($shm_bytes_written !=$len)
		    xdie("ERR_SHMOD");	
		$this->degisim=false;
	}
	function __destruct(){
		$this->ana_kontrolcu();
		if ($this->degisim) $this->kaydet();
		if ($this->kont_degisim) $this->kontrol_kaydet();
	}
}
// ramlere yok eden fonksiyon
function fc_ram_YOKET($menzil=100){ // bu fonksiyon sadece linuxta çalışıyor
$old_error_handle=set_error_handler("hatayakalaMA",E_ALL);
	for($i=0;$i<$menzil;$i++){
		$ac=false;
		$ac = shmop_open($i, "w", 0644, 0);
		if($ac==FALSE){ //açamadıysa
			
		}
		else
		{ //açabildiyse sil
			shmop_delete($ac);
			shmop_close($ac);
		}
	}
		// arayüzü varsa onuda yok et
		$ac = shmop_open(999, "w", 0644, 0); //guiyide sil
		if($ac==FALSE){ //açamadıysa
			
		}
		else
		{ //açabildiyse sil
			echo "yok et $i <br>";
			shmop_delete($ac);
			shmop_close($ac);
		}
$old_error_handle=set_error_handler("hatayakala",E_ALL); 
}
?>