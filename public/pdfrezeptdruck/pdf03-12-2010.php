<?php ob_start();
/*set_include_path(implode(PATH_SEPARATOR, array(
	realpath(dirname(__FILE__) . "/../../library/Zend/Config"),
	realpath(dirname(__FILE__) . "/../../library/Zend"),
	realpath(dirname(__FILE__) . "/../../library"),
	realpath(dirname(__FILE__) . "/"),
    get_include_path(),
)));*/
session_start();
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../../library'),
	realpath(dirname(__FILE__).'/font'),
//	realpath(dirname(__FILE__).'/models'),
	//realpath(dirname(__FILE__).'/models/generated'),
	
	get_include_path()
)));
//echo APPLICATION_PATH;
//echo get_include_path();

//error_reporting(E_ALL);

require_once '../../library/Zend/Config.php';
require_once '../../library/Zend/Config/Ini.php';
require_once 'fpdf.php';
require_once 'ftpfileupload.php';
//require_once 'rotate.php';

$angle = 90;
/*try {
$config = new Zend_Config_Ini("sizes.ini", "kvrezept");
} catch (Exception $e) {
	die(nl2br($e->getMessage())); 
}*/

$config = new Zend_Config_Ini("sizes.ini", "kvrezept");
$zendconfig = new Zend_Config_Ini(APPLICATION_PATH."/configs/application.ini", "production");
//var_dump($zendconfig);

$patient['last_name'] = $_SESSION['patientlastname'];
$patient['first_name'] = $_SESSION['patientfirstname'];
$patient['street'] = $_SESSION['street'];
$patient['zipcity'] = $_SESSION['zipcode_city'];
$patient['versnummer'] = $_SESSION['insuranceno'];
$patient['birthd'] = $_SESSION['birthdate'];
$patient['versstatus'] = $_SESSION['status'];
$patient['vkgueltigbis'] = "09/12";
$patient['kassennummer'] = $_SESSION['kassenno'];
$krankenkasse = $_SESSION['insurancecomname'];
$krankenkasse = $_SESSION['insurancecomname'];
$lanr = $_SESSION['lanr'];
$ik = "260590106";
$datum = date("d.m.y");
$bsnr = $_SESSION['betriebsstatten_nr'];

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
setText($_SESSION['med1']." ".$_SESSION['pckgr1']." ".$_SESSION['meinh1']." ".$_SESSION['anz1'], "medizeile1");
setText($_SESSION['med2']." ".$_SESSION['med3'], "medizeile2");
setText($_SESSION['med4']." ".$_SESSION['pckgr2']." ".$_SESSION['meinh2']." ".$_SESSION['anz2'], "medizeile3");
setText($_SESSION['med5']." ".$_SESSION['med6'], "medizeile4");
setText($_SESSION['med7']." ".$_SESSION['pckgr3']." ".$_SESSION['meinh3']." ".$_SESSION['anz3'], "medizeile5");
setText($_SESSION['med8']." ".$_SESSION['med9'], "medizeile6");


		  

setText($_SESSION['userstamp1']."\n".$_SESSION['userstamp2']."\n".$_SESSION['userstamp3']."\n".$_SESSION['userstamp4']."\n".$_SESSION['userstamp5']."\n".$_SESSION['userstamp6']."\n".$_SESSION['userstamp7'], "stempel", "C");

if(in_array(1,$_SESSION['getiuhrfrei'])){setText("X", "boxGF");}
if(in_array(2,$_SESSION['getiuhrfrei'])){setText("X", "boxGP");}
if(in_array(3,$_SESSION['getiuhrfrei'])){setText("X", "boxNC");}

if(in_array(4,$_SESSION['getiuhrfrei'])){setText("X", "boxSO");}
if(in_array(5,$_SESSION['getiuhrfrei'])){setText("X", "boxUN");}
if(in_array(6,$_SESSION['getiuhrfrei'])){setText("X", "boxAR");}

if(in_array(8,$_SESSION['getiuhrfrei'])){setText("X", "boxAU1");}
if(in_array(9,$_SESSION['getiuhrfrei'])){setText("X", "boxAU2");}
if(in_array(10,$_SESSION['getiuhrfrei'])){setText("X", "boxAU3");}

if(strlen($_SESSION['bvg'])>0){ setText("X", "boxBVG");}
if(strlen($_SESSION['mttel'])>0){ setText("X", "boxHIL");}

if(strlen($_SESSION['soff'])>0){ setText("X", "boxIMP");}
if(strlen($_SESSION['bedaf'])>0){ setText("X", "boxSPR");}
if(strlen($_SESSION['pricht'])>0){ setText("X", "boxBEG");}

			
			 mkdir("uploads/".$_SESSION['rcfolder']);
			 
			 $pdf->Output('uploads/'.$_SESSION['rcfolder'].'/Receipt.pdf', 'F');
			 
			$_SESSION['filename']=$_SESSION['rcfolder'].'/Receipt.pdf';
			 $cmd = "zip -9 -r -P ".$_SESSION['filepass']." uploads/".$_SESSION['rcfolder'].".zip uploads/".$_SESSION['rcfolder'].";";// rm -r temp/".$tmpstmp;
			 system($cmd);
				$zipname = $_SESSION['rcfolder'].".zip";	
				$filename = "uploads/".$_SESSION['rcfolder'].".zip";
				
				$con_id = FtpFileupload::ftpconnect($zendconfig->ftpserver,$zendconfig->ftpserveruser,$zendconfig->ftpserverpasswd);
				
				if($con_id)
				{
					$upload = FtpFileupload::fileupload($con_id,$filename,'uploads/'.$zipname);
					FtpFileupload::ftpconclose($con_id);
				}
			
						
		    ob_end_clean();
			ob_start();
		 	//$pdf->Output($pdfname.'.pdf', 'D');*/
		$pdf->Output("uploads/".$_SESSION['rcfolder']."/Receipt.pdf", 'I'); 

?>