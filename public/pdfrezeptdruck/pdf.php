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
define('SCRIPT_PATH', realpath(dirname(__FILE__) . '/../../public/').'/');
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
//require_once 'ftpfileupload.php';
//require_once 'rotate.php';

$angle = 90;
/*try {
 $config = new Zend_Config_Ini("sizes.ini", "kvrezept");
 } catch (Exception $e) {
 die(nl2br($e->getMessage()));
 }*/

$config = new Zend_Config_Ini("sizes.ini", "kvrezept");
//$zendconfig = new Zend_Config_Ini(APPLICATION_PATH."/configs/application.ini", "production");
$zenddoctrine = new Zend_Config_Ini(APPLICATION_PATH."/configs/production/doctrine.ini", "production");

$zendsecurity = new Zend_Config_Ini(APPLICATION_PATH."/configs/production/security.ini", "production");
//var_dump($zendconfig);

$receipt_type = $_SESSION['receipt_type'];


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
// $lanr = $_SESSION['lanr'];
$ik_nr = $_SESSION['insurance_ik_number'];
$ik = "260590106";
//$datum = date("d.m.y");
$datum = $_SESSION['datum'];
// $bsnr = $_SESSION['betriebsstatten_nr'];

$bsnr = $_SESSION['betriebsstatten_nr'];
$lanr = $_SESSION['lanr'];
// new
$valid_till = $_SESSION['valid_till'];



function mm2dpi($mm){$faktor = 25.4/72;return round($mm / $faktor);}
function top($mm){global $config;return $config->page->height-$mm;};
function position($value, $faktor=1000){ return round($value/$faktor); }
function setText($value, $key, $align="L") {
	global $pdf, $config;

	$pdf->SetFont( ((isset($config->$key, $config->$key->font, $config->$key->font->family) && $config->$key->font->family !="") ? $config->$key->font->family : $config->font->family), '', ((isset($config->$key, $config->$key->font, $config->$key->font->size) && $config->$key->font->size!="") ? $config->$key->font->size : $config->font->size));
	if($align=="L"){
		$pdf->Text(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top), utf8_decode($value));
	}else if( $align=="J") {
		$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
		$pdf->MultiCell(78,3,utf8_decode($value),0,'J');
	}else if($align=="N"){
		$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
		$pdf->MultiCell(5,3, utf8_decode($value), 0, 'N');
		$pdf->SetXY(0,0);
	}else if($align=="C"){
		$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
		$pdf->MultiCell(50,3, utf8_decode($value), 0, 'C');
		$pdf->SetXY(0,0);
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
setText($krankenkasse, "krankenkasse");
setText($ik_nr, "iknummer");
setText($patient['kassennummer'], "patientkassennummer");

if($receipt_type == "kv"){
	setText($bsnr, "betriebsstaette");
	setText($lanr, "vertragsarztnummer");
} elseif($receipt_type == "btm"){
	setText($bsnr, "betriebsstaette");
	setText($lanr, "vertragsarztnummer");
}




//to do
//boxes on the left
if(is_array($_SESSION['getiuhrfrei']) && in_array(1,$_SESSION['getiuhrfrei'])){setText("X", "boxGF");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(2,$_SESSION['getiuhrfrei'])){setText("X", "boxGP");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(3,$_SESSION['getiuhrfrei'])){setText("X", "boxNC");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(4,$_SESSION['getiuhrfrei'])){setText("X", "boxSO");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(5,$_SESSION['getiuhrfrei'])){setText("X", "boxUN");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(6,$_SESSION['getiuhrfrei'])){setText("X", "boxAR");}
// boxes top right
if(strlen($_SESSION['bvg'])>0){ setText("X", "boxBVG");}
if(strlen($_SESSION['mttel'])>0){ setText("X", "boxHIL");}
if(strlen($_SESSION['soff'])>0){ setText("X", "boxIMP");}
if(strlen($_SESSION['bedaf'])>0){ setText("X", "boxSPR");}
if(strlen($_SESSION['pricht'])>0){ setText("X", "boxBEG");}



if(is_array($_SESSION['getiuhrfrei']) && in_array(7,$_SESSION['getiuhrfrei'])){

	setText("X", "boxREZ");//the box on the left (Rezeptur)
	setText($_SESSION['othertext'], "othertext","J");
	setText($_SESSION['userstamp1']."\n".$_SESSION['userstamp2']."\n".$_SESSION['userstamp3']."\n".$_SESSION['userstamp4']."\n".$_SESSION['userstamp5']."\n".$_SESSION['userstamp6']."\n".$_SESSION['userstamp7'], "stempel", "C");
} else{
//boxes on the left (autidem)
if(is_array($_SESSION['getiuhrfrei']) && in_array(8,$_SESSION['getiuhrfrei'])){setText("X", "boxAU1");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(9,$_SESSION['getiuhrfrei'])){setText("X", "boxAU2");}
if(is_array($_SESSION['getiuhrfrei']) && in_array(10,$_SESSION['getiuhrfrei'])){setText("X", "boxAU3");}
setText($_SESSION['med1'], "medizeile1");
setText($_SESSION['med2'], "medizeile2");
setText($_SESSION['med4'], "medizeile3");
setText($_SESSION['med5'], "medizeile4");
setText($_SESSION['med7'], "medizeile5");
setText($_SESSION['med8'], "medizeile6");
setText($_SESSION['userstamp1']."\n".$_SESSION['userstamp2']."\n".$_SESSION['userstamp3']."\n".$_SESSION['userstamp4']."\n".$_SESSION['userstamp5']."\n".$_SESSION['userstamp6']."\n".$_SESSION['userstamp7'], "stempel", "C");



}

















if ($_REQUEST['case'] == 1 || $_REQUEST['case'] == 2) { //normal or BTM receipt

	if(!is_dir(SCRIPT_PATH."uploads/".$_SESSION['rcfolder'])){
		mkdir(SCRIPT_PATH."uploads/".$_SESSION['rcfolder']);
	}

	if($_REQUEST['case'] == 2) { //btm
		$btm_file = 'BTM_';
	} else {
		$btm_file = '';
	}
	
	$local_pdf_file =  SCRIPT_PATH.'uploads/'.$_SESSION['rcfolder'].'/'.$btm_file.'Receipt.pdf';
	
	$pdf->Output($local_pdf_file, 'F');

	$_SESSION['filename']=$_SESSION['rcfolder'].'/'.$btm_file.'Receipt.pdf';
	
	$cmd = "cd ".SCRIPT_PATH.";zip -9 -r -P ".$_SESSION['filepass']." uploads/".$_SESSION['rcfolder'].".zip . -i \"uploads/".$_SESSION['rcfolder']."/".$btm_file."Receipt.pdf\";";// rm -r temp/".$tmpstmp;
	$cmd .= "rm " . $local_pdf_file . ";";
	
	
	exec($cmd);
	//echo $cmd;
	//exit;
	$zipname = $_SESSION['rcfolder'].".zip";
	$filename = "uploads/".$_SESSION['rcfolder'].".zip";
	
	if ((int)$_SESSION['Login_Info']['clientid'] > 0 ) {

		defined('FTP_QUEUE_PATH') || define('FTP_QUEUE_PATH', APPLICATION_PATH."/../ftp_put_cron");
		require_once 'parseDsn.php';

		try {			
			if (! is_dir(FTP_QUEUE_PATH . "/pdfrezeptdruck")) {
				@mkdir(FTP_QUEUE_PATH . "/pdfrezeptdruck");
			}
			if (! is_dir(FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid'])) {
				@mkdir(FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']);
			}
			
// 			copy( SCRIPT_PATH."uploads/".$zipname , FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']."/".$zipname);
			rename( SCRIPT_PATH."uploads/".$zipname , FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']."/".$zipname);
			
			$mysql = parseDsn($zenddoctrine->doctrine->dsn->{0});
		
			$dbh = new PDO( $mysql['dsn'] , $mysql['user'], $mysql['pass']);
			// $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql_query = "INSERT INTO `ftp_put_queue` 
					(
					`clientid` ,
					`local_file_path` ,
					`file_name` ,
					`legacy_path` ,
					`controllername` ,
					`actionname` ,
					`foster_file`,
					`create_date`,
					`create_user`
				)
				VALUES 
				(
					:clientid,
					:zipname,
					AES_ENCRYPT(:fileencrypted , :encrypt),
					'uploads',
					'receipt',
					'pdf.php',
					'NO',
					NOW(),
					:createuser
				);
		";
			$stmt = $dbh->prepare($sql_query);
			
			$stmt->execute(array(
					"clientid"		=> (int)$_SESSION['Login_Info']['clientid'],
					"zipname"		=> "pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']."/".$zipname,
					"fileencrypted"	=> $_SESSION['filename'],
					"encrypt"		=> $zendsecurity->salt,
					"createuser"	=> (int)$_SESSION['Login_Info']['userid']
			));
		
			$stmt = null;
			$dbh = null;
		
		}
		catch(PDOException $ex){
			//('Unable to connect');
		}
	}
	/*
	else {
		$con_id = FtpFileupload::ftpconnect($zendconfig->ftpserver,$zendconfig->ftpserveruser,$zendconfig->ftpserverpasswd);
	
		if($con_id)
		{
			$upload = FtpFileupload::fileupload($con_id,SCRIPT_PATH.$filename,'uploads/'.$zipname);
			FtpFileupload::ftpconclose($con_id);
			unset($_SESSION['clientid']);
		}
	}
	*/
	unset($_SESSION['lanr']);
	unset($_SESSION['betriebsstatten_nr']);
	unset($_SESSION['userstamp1']);
	unset($_SESSION['userstamp2']);
	unset($_SESSION['userstamp3']);
	unset($_SESSION['userstamp4']);
	unset($_SESSION['userstamp5']);
	unset($_SESSION['userstamp6']);
	unset($_SESSION['userstamp7']);
}

ob_end_clean();
ob_start();

//		 	$pdf->Output($pdfname.'.pdf', 'D');*/
if ($_REQUEST['case'] != 1 && $_REQUEST['case'] != 2) {
// 	$pdf->Output(SCRIPT_PATH."uploads/".$_SESSION['rcfolder']."/Receipt.pdf", 'D');
	$pdf->Output("Receipt.pdf", 'D');
} else {
	$pdf->Output(SCRIPT_PATH."uploads/".$_SESSION['rcfolder']."/".$btm_file."Receipt.pdf", 'F');
	header("Location: ".$_SESSION['redirurl']);
}

?>