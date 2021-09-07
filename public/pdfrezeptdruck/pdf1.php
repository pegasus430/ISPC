<?php
/*set_include_path(implode(PATH_SEPARATOR, array(
	realpath(dirname(__FILE__) . "/../../library/Zend/Config"),
	realpath(dirname(__FILE__) . "/../../library/Zend"),
	realpath(dirname(__FILE__) . "/../../library"),
	realpath(dirname(__FILE__) . "/"),
    get_include_path(),
)));*/

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../../library'),
	realpath(dirname(__FILE__).'/font'),
//	realpath(dirname(__FILE__).'/models'),
	//realpath(dirname(__FILE__).'/models/generated'),
	get_include_path()
)));

//echo get_include_path();

//error_reporting(E_ALL);

require_once '../../library/Zend/Config.php';
require_once '../../library/Zend/Config/Ini.php';
require_once 'fpdf.php';
//require_once 'rotate.php';

$angle = 90;
/*try {
$config = new Zend_Config_Ini("sizes.ini", "kvrezept");
} catch (Exception $e) {
	die(nl2br($e->getMessage())); 
}*/

$config = new Zend_Config_Ini("sizes.ini", "kvrezept");
//var_dump($config);

$patient['last_name'] = "Brinkmann";
$patient['first_name'] = "Florian";
$patient['street'] = "Am Sattelgut 76a";
$patient['zipcity'] = "44879 Bochum";
$patient['versnummer'] = "2807821766";
$patient['birthd'] = "28.07.1982";
$patient['versstatus'] = "1     1";
$patient['vkgueltigbis'] = "09/12";
$patient['kassennummer'] = "3477503";
$krankenkasse = "Techniker Krankenkasse";
$krankenkasse = "Techniker Krankenkasse";
$lanr = "766311401";
$ik = "260590106";
$datum = date("d.m.y");
$bsnr = "188412900";

function mm2dpi($mm){$faktor = 25.4/72;return round($mm / $faktor);}
function top($mm){global $config;return $config->page->height-$mm;};
function position($value, $faktor=1000){ return round($value/$faktor); }
function setText($value, $key, $align="L") {
	global $pdf, $config;

	$pdf->SetFont(($config->$key->font->family!="" ? $config->$key->font->family : $config->font->family), '', ($config->$key->font->size!="" ? $config->$key->font->size : $config->font->size));
	if($align=="L"){
		$pdf->Text(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top), utf8_decode($value));
	}else{
		$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
		$pdf->MultiCell(0,3, utf8_decode($value), 0, $align);
		$pdf->SetXY(0,0);
	}
}


$pdf = new FPDF('P', 'mm', array(position(148000), position(105000))); 
$pdf->AddPage(); 

setText($datum, "datum");
setText($patient['last_name'], "patientnachname");
setText($patient['first_name'], "patientvorname");
setText($patient['street'], "patientstrasse");
setText($patient['zipcity'], "patientzipcity");
setText($patient['birthd'], "patientbirthd");
setText($patient['versnummer'], "patientversichertennummer");
setText($patient['versstatus'], "patientstatus");
setText($bsnr, "betriebsstaette");
setText($krankenkasse, "krankenkasse");
setText($patient['kassennummer'], "patientkassennummer");
setText($lanr, "vertragsarztnummer");
setText("Tantum Verde Gurgellösung mit Sprühkopf", "medizeile1");
setText("Lösung 60ml N1", "medizeile2");
setText("Tantum Verde Gurgellösung mit Sprühkopf", "medizeile3");
setText("Lösung 60ml N1", "medizeile4");
setText("Tantum Verde Gurgellösung mit Sprühkopf", "medizeile5");
setText("Lösung 60ml N1", "medizeile6");

setText("Klaus Blum\nFacharzt für Allgemeinmedizin\nGartenstraße 113\n44869 Bochum\nTel.: 02327 - 71278\nFax: 02327 - 790248\nBSNR: 188412900   LANR:766311401", "stempel", "C");

setText("X", "boxGF");
setText("X", "boxGP");
setText("X", "boxNC");

$string = $pdf->Output( 'temp/test.pdf', 'I'); 


//header('Content-type: application/pdf');
//header('Content-Disposition: attachment: filename="datei.pdf"');
//echo $string;
?>