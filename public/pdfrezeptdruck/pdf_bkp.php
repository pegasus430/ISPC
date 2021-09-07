<?php
/*set_include_path(implode(PATH_SEPARATOR, array(
	realpath(dirname(__FILE__) . "/../../library/Zend/Config"),
	realpath(dirname(__FILE__) . "/../../library/Zend"),
	realpath(dirname(__FILE__) . "/../../library"),
	realpath(dirname(__FILE__) . "/"),
    get_include_path(),
)));*/
error_reporting(E_ALL);
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

$patient['last_name'] = $_POST['patientname'];
//$patient['first_name'] = "Florian";
$patient['street'] = $_POST['street'];
$patient['zipcity'] = $_POST['zipcode'].' '.$_POST['city'];
$patient['versnummer'] = $_POST['insuranceno'];
$patient['birthd'] = $_POST['birthdate'];
$patient['versstatus'] = $_POST['status'];
$patient['vkgueltigbis'] = "09/12";
$patient['kassennummer'] = $_POST['kassenno'];
$krankenkasse = $_POST['insurancecomname'];
$krankenkasse = $_POST['insurancecomname'];
$lanr = $_POST['lanr'];
$ik = "260590106";
$datum = date("d.m.y");
$bsnr = $_POST['betriebsstatten_nr'];

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
setText($_POST['med1']." ".$_POST['pckgr1']." ".$_POST['meinh1']." ".$_POST['anz1'], "medizeile1");
setText($_POST['med2']." ".$_POST['med3'], "medizeile2");
setText($_POST['med4']." ".$_POST['pckgr2']." ".$_POST['meinh2']." ".$_POST['anz2'], "medizeile3");
setText($_POST['med5']." ".$_POST['med6'], "medizeile4");
setText($_POST['med7']." ".$_POST['pckgr3']." ".$_POST['meinh3']." ".$_POST['anz3'], "medizeile5");
setText($_POST['med8']." ".$_POST['med9'], "medizeile6");


		  

setText($_POST['userstamp1']."\n".$_POST['userstamp2']."\n".$_POST['userstamp3']."\n".$_POST['userstamp4']."\n".$_POST['userstamp5']."\n".$_POST['userstamp6']."\n".$_POST['userstamp7'], "stempel", "C");

setText("X", "boxGF");
setText("X", "boxGP");
setText("X", "boxNC");

			$tmpstmp = time();
			 mkdir("temp/".$tmpstmp);
			 $pdf->Output('temp/'.$tmpstmp.'/Receipt.pdf', 'F');
			$_SESSION['filename']=$tmpstmp.'/Receipt.pdf';
			 $cmd = "zip -9 -r -P ".$logininfo->filepass." temp/".$tmpstmp.".zip temp/".$tmpstmp.";"; //rm -r temp/".$tmpstmp;
			 exec($cmd);
				$zipname = $tmpstmp.".zip";	
				$filename = "temp/".$tmpstmp.".zip";
				/*$con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id,$filename,'temp/'.$zipname);
					Pms_FtpFileupload::ftpconclose($con_id);
								
				}
			
			 $cust = new PatientFileUpload();
			 $cust->title = Pms_CommonData::aesEncrypt($pdfname);
			 $cust->ipid = $ipid;
			 $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
			 $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			 $cust->save(); */
			 
			
		   /* ob_end_clean();
			ob_start();
		 	//$pdf->Output($pdfname.'.pdf', 'D');*/

		//$string = $pdf->Output("temp/".$tmpstmp."/Receipt.pdf", 'F'); 
		$string = $pdf->Output("temp/".$tmpstmp."/Receipt.pdf", 'I'); 

//header('Content-type: application/pdf');
//header('Content-Disposition: attachment: filename="datei.pdf"');
//echo $string;
?>