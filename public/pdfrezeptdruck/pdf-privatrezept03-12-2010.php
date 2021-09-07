<?php ob_start();
session_start();
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../../library'),
	realpath(dirname(__FILE__).'/font'),
	get_include_path()
)));

error_reporting(E_ALL);
require_once '../../library/Zend/Config.php';
require_once '../../library/Zend/Config/Ini.php';
require_once 'fpdf.php';
require_once 'ftpfileupload.php';

$config = new Zend_Config_Ini("privatrezept.ini", "privatrezept");
$zendconfig = new Zend_Config_Ini(APPLICATION_PATH."/configs/application.ini", "production");

function mm2dpi($mm){$faktor = 25.4/72;return round($mm / $faktor);}
function top($mm){global $config;return $config->page->height-$mm;};
function position($value, $faktor=1000){ return round($value/$faktor); }
function setText($value, $key, $align="L", $multi=0, $lineheight=3) {
	global $pdf, $config;

	$pdf->SetFont(($config->$key->font->family!="" ? $config->$key->font->family : $config->font->family), '', ($config->$key->font->size!="" ? $config->$key->font->size : $config->font->size));
	if($align=="L" && $multi==0){
		$pdf->Text(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top), utf8_decode($value));
	}else{
		$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
		$pdf->MultiCell(0, $lineheight, utf8_decode($value), 0, $align);
		$pdf->SetXY(0,0);
	}
}

	$display_stamp = 0;
	$medicationtxt = "";
	$stamp = "";
	
	
	$pdf = new FPDF('P', 'mm', array(position($config->page->width), position($config->page->height))); 
	$pdf->AddPage(); 
	setText($_SESSION['medicationtxt'], "textbox", "L", 1);
	if($_SESSION['display_stamp']==1) { setText($_SESSION['stamp'], "stampbox", "C", 1, 4); }

		if(isset($_SESSION['btnsave']) && $_SESSION['btnsave']=='save')
		{
			mkdir("uploads/".$_SESSION['rcfolder']);
			$pdf->Output('uploads/'.$_SESSION['rcfolder'].'/privatrezept.pdf', 'F');
 			
			$_SESSION['filename']=$_SESSION['rcfolder'].'/privatrezept.pdf';
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
			header("location: ".$zendconfig->jsfilepath."/patient/patientfileupload?id=".$_SESSION['id']);	
		}elseif(isset($_SESSION['btnopen']) && $_SESSION['btnopen']=='open')
		{
				mkdir("uploads/".$_SESSION['rcfolder']);
				$pdf->Output('uploads/'.$_SESSION['rcfolder'].'/privatrezept.pdf', 'F');
 			
				$_SESSION['filename']=$_SESSION['rcfolder'].'/privatrezept.pdf';
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
				//header("location: ".$zendconfig->jsfilepath."/patient/patientfileupload?id=".$_SESSION['id']);	
				ob_end_clean();
				ob_start();
				$pdf->Output("uploads/privatrezept.pdf", 'I'); 
				//header('Content-type: application/pdf');
				//header('Content-Disposition: attachment: filename="datei.pdf"');
				//echo $string;
	}
?>