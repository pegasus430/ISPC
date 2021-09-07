<?php

	ob_start();
	session_start();
	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
	define('SCRIPT_PATH', realpath(dirname(__FILE__) . '/../../public/') . '/');
	set_include_path(implode(PATH_SEPARATOR, array(
		realpath(dirname(__FILE__) . '/../../library'),
		realpath(dirname(__FILE__) . '/font'),
		get_include_path()
	)));

	error_reporting(E_ALL);
	require_once '../../library/Zend/Config.php';
	require_once '../../library/Zend/Config/Ini.php';
	require_once 'fpdf.php';
	//require_once 'ftpfileupload.php';

	$config = new Zend_Config_Ini("privatrezept.ini", "privatrezept");
	//$zendconfig = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", "production");
	$zenddoctrine = new Zend_Config_Ini(APPLICATION_PATH."/configs/production/doctrine.ini", "production");
	$zendsecurity = new Zend_Config_Ini(APPLICATION_PATH."/configs/production/security.ini", "production");
	$zendapp = new Zend_Config_Ini(APPLICATION_PATH."/configs/production/app.ini", "production");
	
	function mm2dpi($mm)
	{
		$faktor = 25.4 / 72;
		return round($mm / $faktor);
	}

	function top($mm)
	{
		global $config;
		return $config->page->height - $mm;
	}

	;

	function position($value, $faktor = 1000)
	{
		return round($value / $faktor);
	}

	function setText($value, $key, $align = "L", $multi = 0, $lineheight = 3)
	{
		global $pdf, $config;

		$pdf->SetFont(($config->$key->font->family != "" ? $config->$key->font->family : $config->font->family), '', ($config->$key->font->size != "" ? $config->$key->font->size : $config->font->size));
		if($align == "L" && $multi == 0)
		{
			$pdf->Text(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top), utf8_decode($value));
		}
		else
		{
			$pdf->SetXY(position($config->position->left + $config->$key->left), position($config->position->top + $config->$key->top));
			$pdf->MultiCell(0, $lineheight, utf8_decode($value), 0, $align);
			$pdf->SetXY(0, 0);
		}
	}

	$display_stamp = 0;
	$medicationtxt = "";
	$stamp = "";


	$pdf = new FPDF('P', 'mm', array(position($config->page->width), position($config->page->height)));
	$pdf->AddPage();
	setText($_SESSION['medicationtxt'], "textbox", "L", 1);
	if($_SESSION['display_stamp'] == 1)
	{
		setText($_SESSION['stamp'], "stampbox", "C", 1, 4);
	}

	if(isset($_SESSION['btnsave']) && $_SESSION['btnsave'] == 'save')
	{
		if(!is_dir(SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder']))
		{
			mkdir(SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder']);
		}

		$local_pdf_file =  SCRIPT_PATH . 'uploads/' . $_SESSION['rcfolder'] . '/privatrezept.pdf';
		
		$pdf->Output($local_pdf_file, 'F');

		$_SESSION['filename'] = $_SESSION['rcfolder'] . '/privatrezept.pdf';
		
		$cmd = "cd " . SCRIPT_PATH . ";zip -9 -r -P " . $_SESSION['filepass'] . " uploads/" . $_SESSION['rcfolder'] . ".zip \"uploads/" . $_SESSION['rcfolder'] . "/\";"; // rm -r temp/".$tmpstmp;
		$cmd .= "rm " . $local_pdf_file . ";";
		
		system($cmd);
		
		$zipname = $_SESSION['rcfolder'] . ".zip";
		$filename = SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder'] . ".zip";

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
					
// 				copy( SCRIPT_PATH."uploads/".$zipname , FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']."/".$zipname);
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
						'pdf-privatrezept.php',
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
		$con_id = FtpFileupload::ftpconnect($zendconfig->ftpserver, $zendconfig->ftpserveruser, $zendconfig->ftpserverpasswd);
		if($con_id)
		{
			$upload = FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
			FtpFileupload::ftpconclose($con_id);
		}
		*/
		ob_end_clean();
		ob_start();
		$pdf->Output("uploads/privatrezept.pdf", 'I');
		header("location: " . $zendapp->jsfilepath . "/patient/patientfileupload?id=" . $_SESSION['id']);
	}
	elseif(isset($_SESSION['btnopen']) && $_SESSION['btnopen'] == 'open')
	{
		if(!is_dir(SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder']))
		{
			mkdir(SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder']);
		}
		
		$local_pdf_file =  SCRIPT_PATH . 'uploads/' . $_SESSION['rcfolder'] . '/privatrezept.pdf';
		
		$pdf->Output($local_pdf_file, 'F');

		$_SESSION['filename'] = $_SESSION['rcfolder'] . '/privatrezept.pdf';
		
		$cmd = "cd " . SCRIPT_PATH . ";zip -9 -r -P " . $_SESSION['filepass'] . " uploads/" . $_SESSION['rcfolder'] . ".zip uploads/" . $_SESSION['rcfolder'] . ";"; // rm -r temp/".$tmpstmp;
		$cmd .= "rm " . $local_pdf_file . ";";
		
		system($cmd);
		
		$zipname = $_SESSION['rcfolder'] . ".zip";
		$filename = SCRIPT_PATH . "uploads/" . $_SESSION['rcfolder'] . ".zip";

		
		
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
// 				copy( SCRIPT_PATH."uploads/".$zipname , FTP_QUEUE_PATH . "/pdfrezeptdruck/".(int)$_SESSION['Login_Info']['clientid']."/".$zipname);
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
						'pdf-privatrezept.php',
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
		$con_id = FtpFileupload::ftpconnect($zendconfig->ftpserver, $zendconfig->ftpserveruser, $zendconfig->ftpserverpasswd);

		if($con_id)
		{
			$upload = FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
			FtpFileupload::ftpconclose($con_id);
		}
		*/
		//header("location: ".$zendconfig->jsfilepath."/patient/patientfileupload?id=".$_SESSION['id']);
		ob_end_clean();
		ob_start();
		$pdf->Output("uploads/privatrezept.pdf", 'I');
		//header('Content-type: application/pdf');
		//header('Content-Disposition: attachment: filename="datei.pdf"');
		//echo $string;
	}
?>