<?php

class StatsController extends Zend_Controller_Action
{
	public function init()
	{
		//setcookie("openmenu","admin_menu");
	}

	public function statsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('client',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}


		$columnarray = array("pk"=>"id","cn"=>"CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1)","ctry"=>"country","fn"=>"CONVERT(AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."')  using latin1)","ln"=>"CONVERT(AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."')  using latin1)","em"=>"CONVERT(AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."')  using latin1)","ph"=>"CONVERT(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."')  using latin1)");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];

		$client = Doctrine_Query::create()
		->select('count(*)')
		->from('Client')
		->where('isdelete = 0')
		->orderBy("CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1) ASC");
		$clientexec = $client->execute();
		$clientarray = $clientexec->toArray();

		$limit = 1000;
		$client->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone,AES_DECRYPT(fax,'".Zend_Registry::get('salt')."') as fax");
		$client->where('isdelete = 0');
		$client->orderBy("CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1) ASC");
		$client->limit($limit);
		$client->offset($_GET['pgno']*$limit);

		$clientlimitexec = $client->execute();
		$this->view->clientlimit = $clientlimitexec->toArray();
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('client',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}


		$columnarray = array("pk"=>"id","cn"=>"CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1)","ctry"=>"country","fn"=>"CONVERT(AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."')  using latin1)","ln"=>"CONVERT(AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."')  using latin1)","em"=>"CONVERT(AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."')  using latin1)","ph"=>"CONVERT(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."')  using latin1)");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];

		$client = Doctrine_Query::create()
		->select('count(*)')
		->from('Client')
		->where('isdelete = 0')
		->orderBy("CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1) ASC");
		$clientexec = $client->execute();
		$clientarray = $clientexec->toArray();

		$limit = 50;
		$client->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone,AES_DECRYPT(fax,'".Zend_Registry::get('salt')."') as fax");
		$client->where('isdelete = 0');
		$client->orderBy("CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')  using latin1) ASC");
		$client->limit($limit);
		$client->offset($_GET['pgno']*$limit);

		$clientlimitexec = $client->execute();
		$clientlimit = $clientlimitexec->toArray();


		$grid = new Pms_Grid($clientlimit,1,$clientarray[0]['count'],"liststatistics.html");
		$this->view->clientgrid = $grid->renderGrid();

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['statslist'] =$this->view->render('stats/fetchlist.html');

		echo json_encode($response);
		exit;
	}

// 	public function fileuploadAction()
// 	{
// 		$logininfo= new Zend_Session_Namespace('Login_Info');
// 		if($this->getRequest()->isPost())
// 		{
// 			$fluplod = Doctrine::getTable('PatientFileUpload')->find($_POST['fileid']);
// 			$fluplod->file_name = Pms_CommonData::aesEncrypt(addslashes($_SESSION['filename']));
// 			$fluplod->file_type = Pms_CommonData::aesEncrypt($_SESSION['filetype']);
// 			$fluplod->save();

// 			unset($_POST['fileid'],$_SESSION['filename'],$_SESSION['filetype']);

// 			header("location: ".APP_BASE."stats/fileupload?flg=suc");

// 		}
// 	}

// 	public function uploadifyAction()
// 	{
// 		$logininfo= new Zend_Session_Namespace('Login_Info');

// 		$extension = explode(".",$_FILES['qqfile']['name']);

// 		$_SESSION['filetype'] = $extension[1];
// 		$_SESSION['filetitle'] = $extension[0];
// 		$timestamp_filename = time()."_file.".$extension[1];

// 		$folderpath = time();
// 		mkdir("uploads/".$folderpath);
// 		$filename= "uploads/".$folderpath."/".trim($timestamp_filename);
// 		$_SESSION['filename'] = $folderpath."/".trim($timestamp_filename);
// 		move_uploaded_file($_FILES['qqfile']['tmp_name'],$filename);

// 		$cmd = "zip -9 -r -P ".$logininfo->filepass." uploads/".$folderpath.".zip  uploads/".$folderpath ."; rm -r uploads/".$folderpath.";";
// 		exec($cmd);
// 		$_SESSION['zipname'] = $folderpath.".zip";
// 		$zipname = $folderpath.".zip";
// 		$con_id = Pms_FtpFileupload::ftpconnect();
// 		if($con_id)
// 		{
// 			$upload = Pms_FtpFileupload::fileupload($con_id,'uploads/'.$zipname,'uploads/'.$zipname);
// 			Pms_FtpFileupload::ftpconclose($con_id);
// 		}
// 		echo json_encode(array(success=>true));
// 		exit;
// 	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function patientfileuploadAction()
	{		
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;


		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();

		if($_GET['doc_id'] > 0)
		{
			$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('PatientFileUpload')
					->where('id= ?', $_GET['doc_id']);
			$fl = $patient->execute();

			
			if($fl)
			{
				$flarr = $fl->toArray();

				$explo = explode("/", $flarr[0]['file_name']);

				$fdname = $explo[0];
				$flname = utf8_decode($explo[1]);

				//medication plan pdf issue only
				if($flarr[0]['title'] == "Medikamentenplan" && strpos($flarr[0]['file_name'], '.zip') !== false)
				{
					//overwrite folder name so the file can be downloaded
					$fdname = str_replace(".zip", "", $flname);

					//overwrite the file name so the file can be served to user
					$flname = "medication.pdf";
				}
			}
			
/*
			$con_id = Pms_FtpFileupload::ftpconnect();

			if($con_id)
			{
				$old = false;
				if($_REQUEST['old'])
				{
					$old = true;
				}
				$upload = Pms_FtpFileupload::filedownload($con_id, 'uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip', $old);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
*/
			
			//			check if was uploaded after/in T0 date
			$client_merge_date = strtotime('2013-12-18 13:00:00'); //client merge date
			$req_file_date = strtotime(date('Y-m-d H:i:s', strtotime($flarr[0]['create_date'])));
			$file_password = '';

			//if uploaded before client_merge_date use old client(62) password to open it
			if($req_file_date < $client_merge_date && $clientid == '61')
			{
				//check the patient in epid_ipid
				$patient_epid = Pms_CommonData::getEpidcharsandNum($ipid);

				if($patient_epid['char'] == 'DST') //patient belongs to the OLD client(62)
				{
					//OLD client upload password
					$file_password = 'j5qqil01gklqolq';

				}
				else //patient not found in old client => use curent client pass
				{
					$file_password = $logininfo->filepass;
				}
			}
			else //uploaded after client_merge_date use new client password to open it
			{
				$file_password = $logininfo->filepass;
			}
/*
			$cmd = "unzip -P " . $file_password . " uploads/" . $fdname . ".zip;";
			exec($cmd);
*/			
			
// 			$create_date =  date ("Y-m-d", strtotime($flarr[0]['create_date']));
// 			$today_date =  date("Y-m-d");
// 			if ($create_date == $today_date) {
// 				//file was created today... firt search localhost to download
// 				$first_location2search = 'local';
// 			} else {
// 				$first_location2search = 'ftp';
// 			}
			
			$old = $_REQUEST['old'] ? true : false;
			if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , null , $flarr[0]['file_name'], "PatientFileUpload", $flarr[0]['id'] )) === false){
				//failed to download/extract file
				$path = "uploads/" . $fdname ;
			}
			//returns the full path to the file
			//$path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $fdname . "/"; // change the path to fit your websites document structure
			$fullPath = $path . "/". $flname;
						
			if( ! file_exists($fullPath)) {
				
				$flname = $explo[1];
				$fullPath = $path . "/". $flname;
				
				if( ! file_exists($fullPath)) {
					
					$flname = Pms_CommonData::unicode_conv($flname , true);
					$fullPath = $path . "/". $flname;
					
					if( ! file_exists($fullPath)) {
						$flname = Pms_CommonData::unicode_conv($flname , false);
						$fullPath = $path . "/". $flname;
					}
					
				}
			}
			

// 			clearstatcache();
			
			//if($fd = fopen($fullPath, "r")) // why worry about file reading permisions ? was this file not created by us?
			if(file_exists($fullPath))
			{
				$fsize = filesize($fullPath);
				$path_parts = pathinfo($fullPath);
				
				$path_parts["basename"] = Pms_CommonData::unicode_conv($path_parts["basename"]);

				$ext = strtolower($path_parts["extension"]);
				ob_end_clean();
				ob_start();
				switch($ext)
				{
					case "pdf":
						header('Content-Description: File Transfer');
						header("Content-type: application/pdf"); // add here more headers for diff. extensions
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						if($_COOKIE['mobile_ver'] != 'yes' || ($_COOKIE['mobile_ver'] == 'yes' && stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false))
						{ //if on mobile version don't send content-disposition to play nice with iPad
							header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
						}
						break;
					
					default;
						header('Content-Description: File Transfer');
						header("Content-type: application/octet-stream");
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						if($_COOKIE['mobile_ver'] != 'yes' || ($_COOKIE['mobile_ver'] == 'yes' && stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false))
						{ //if on mobile version don't send content-disposition to play nice with iPad
							header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
						}
				}
				header("Content-length: $fsize");
				header("Cache-control: private"); //use this to open files directly
				//RFC2616 section 14.9.1 Indicates that all or part of the response message is intended for a single user and MUST NOT be cached by a shared cache, such as a proxy server.
				
// 				echo readfile($fullPath);
				@readfile($fullPath);
				unlink($fullPath);
			}
			//fclose($fd);
			else {
				//failed to download file, redirect user from wher it came and alert him and admin about missing file
				$exception = __CLASS__ . " >> ".__FUNCTION__ . " >> "." FILE download missing : ".$fullPath;
				
// 				$logininfo = new Zend_Session_Namespace('Login_Info');
// 				$mailmessage .= "Page name :" . $_SERVER['REQUEST_URI'];
// 				$mailmessage .= "<br /><div> Browser: " . $_SERVER['HTTP_USER_AGENT'] . "</div><br />";
// 				$mailmessage .= "<div>" . $exception . " </div>";
// 				$mailmessage .= "<div> Date: " . date("d.m.Y H:i:s", time()) . "</div><br />";
// 				$mailmessage .= "<div> Username: " . $logininfo->username . "</div><br />";
// 				$mailmessage .= "<div> IP-Address: " . $_SERVER['REMOTE_ADDR'] . "</div><br />";
				
				if (defined("APPLICATION_ENV") && APPLICATION_ENV != 'production'){
				    die($exception . PHP_EOL . "! ON PRODUCTION THIS message IS IN A redirect + an email with the missing file info !");
				} else {
				    
				    $this->_helper->log($exception, 0);
				    
    				$referer =  $_SERVER['HTTP_REFERER'] . "&MISSING_FILE";
    				
    				$this->_redirect($referer);
				}
				
			}
			exit;
		}
	}

	/**
	 * ISPC-2891 Ancuta 29.04.2021
	 */
	public function patientclientfileuploadAction()
	{		
		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		if(isset($_REQUEST['clientid'])){
		    $clientid = $_REQUEST['clientid'];
            $client_obj = new Client();
            $client_details = $client_obj->fetchById($clientid);
            
            if(empty($client_details)){
                
                $exception = __CLASS__ . " >> ".__FUNCTION__ . " >> "." FILE Client is missing : ".$clientid;
                
                
                if (defined("APPLICATION_ENV") && APPLICATION_ENV != 'production'){
                    die($exception . PHP_EOL . "! ON PRODUCTION THIS message IS IN A redirect + an email with the missing client info !");
                } else {
                    
                    $this->_helper->log($exception, 0);
                    
                    $referer =  $_SERVER['HTTP_REFERER'] . "&ClientidMissing";
                    
                    $this->_redirect($referer);
                }
            }
            
            $client_filepass = $client_details[$clientid]['fileupoadpass'];

		}
		else{
    		$logininfo = new Zend_Session_Namespace('Login_Info');
	       	$clientid = $logininfo->clientid;
	       	$client_filepass = $logininfo->filepass;
		    
		}

// 		var_dump($clientid);
// 		var_dump($client_filepass);
 

		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();

		if($_REQUEST['doc_id'] > 0)
		{
			$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('PatientFileUpload')
					->where('id= ?', $_REQUEST['doc_id']);
			$fl = $patient->execute();

			
			if($fl)
			{
				$flarr = $fl->toArray();

				$explo = explode("/", $flarr[0]['file_name']);

				$fdname = $explo[0];
				$flname = utf8_decode($explo[1]);

				//medication plan pdf issue only
				if($flarr[0]['title'] == "Medikamentenplan" && strpos($flarr[0]['file_name'], '.zip') !== false)
				{
					//overwrite folder name so the file can be downloaded
					$fdname = str_replace(".zip", "", $flname);

					//overwrite the file name so the file can be served to user
					$flname = "medication.pdf";
				}
			}
			
 
			
			//			check if was uploaded after/in T0 date
			$client_merge_date = strtotime('2013-12-18 13:00:00'); //client merge date
			$req_file_date = strtotime(date('Y-m-d H:i:s', strtotime($flarr[0]['create_date'])));
			$file_password = '';

			//if uploaded before client_merge_date use old client(62) password to open it
			if($req_file_date < $client_merge_date && $clientid == '61')
			{
				//check the patient in epid_ipid
				$patient_epid = Pms_CommonData::getEpidcharsandNum($ipid);

				if($patient_epid['char'] == 'DST') //patient belongs to the OLD client(62)
				{
					//OLD client upload password
					$file_password = 'j5qqil01gklqolq';

				}
				else //patient not found in old client => use curent client pass
				{
//					$file_password = $logininfo->filepass;
					$file_password = $client_filepass;
				}
			}
			else //uploaded after client_merge_date use new client password to open it
			{
//				$file_password = $logininfo->filepass;
				$file_password = $client_filepass;
			}
			
			$old = $_REQUEST['old'] ? true : false;
			if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , $clientid , $flarr[0]['file_name'], "PatientFileUpload", $flarr[0]['id'] )) === false){
				//failed to download/extract file
				$path = "uploads/" . $fdname ;
			}
			//returns the full path to the file
			//$path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $fdname . "/"; // change the path to fit your websites document structure
			$fullPath = $path . "/". $flname;
						
			if( ! file_exists($fullPath)) {
				
				$flname = $explo[1];
				$fullPath = $path . "/". $flname;
				
				if( ! file_exists($fullPath)) {
					
					$flname = Pms_CommonData::unicode_conv($flname , true);
					$fullPath = $path . "/". $flname;
					
					if( ! file_exists($fullPath)) {
						$flname = Pms_CommonData::unicode_conv($flname , false);
						$fullPath = $path . "/". $flname;
					}
					
				}
			}
			

// 			clearstatcache();
// 			var_dump($fullPath); 
// 			exit;
			//if($fd = fopen($fullPath, "r")) // why worry about file reading permisions ? was this file not created by us?
			if(file_exists($fullPath))
			{
				$fsize = filesize($fullPath);
				$path_parts = pathinfo($fullPath);
				
				$path_parts["basename"] = Pms_CommonData::unicode_conv($path_parts["basename"]);

				$ext = strtolower($path_parts["extension"]);
				ob_end_clean();
				ob_start();
				switch($ext)
				{
					case "pdf":
						header('Content-Description: File Transfer');
						header("Content-type: application/pdf"); // add here more headers for diff. extensions
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						if($_COOKIE['mobile_ver'] != 'yes' || ($_COOKIE['mobile_ver'] == 'yes' && stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false))
						{ //if on mobile version don't send content-disposition to play nice with iPad
							header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
						}
						break;
					
					default;
						header('Content-Description: File Transfer');
						header("Content-type: application/octet-stream");
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						if($_COOKIE['mobile_ver'] != 'yes' || ($_COOKIE['mobile_ver'] == 'yes' && stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false))
						{ //if on mobile version don't send content-disposition to play nice with iPad
							header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
						}
				}
				header("Content-length: $fsize");
				header("Cache-control: private"); //use this to open files directly
				//RFC2616 section 14.9.1 Indicates that all or part of the response message is intended for a single user and MUST NOT be cached by a shared cache, such as a proxy server.
				
// 				echo readfile($fullPath);
				@readfile($fullPath);
				unlink($fullPath);
			}
			//fclose($fd);
			else {
				//failed to download file, redirect user from wher it came and alert him and admin about missing file
				$exception = __CLASS__ . " >> ".__FUNCTION__ . " >> "." FILE download missing : ".$fullPath;
				
				
				if (defined("APPLICATION_ENV") && APPLICATION_ENV != 'production'){
				    die($exception . PHP_EOL . "! ON PRODUCTION THIS message IS IN A redirect + an email with the missing file info !");
				} else {
				    
				    $this->_helper->log($exception, 0);
				    
    				$referer =  $_SERVER['HTTP_REFERER'] . "&MISSING_FILE";
    				
    				$this->_redirect($referer);
				}
				
			}
			exit;
		}
	}

	public function crossdbjoinAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();

		$manager = Doctrine_Manager::getInstance();
		$manager->setCurrentConnection('IDAT');
		$conispc1 =  $manager->getCurrentConnection();

		$manager = Doctrine_Manager::getInstance();
		$manager->setCurrentConnection('MDAT');
		$conispc2 =  $manager->getCurrentConnection();

		$manager = Doctrine_Manager::getInstance();
		$manager->setCurrentConnection('SYSDAT');
		$conispc3 =  $manager->getCurrentConnection();
		echo "SELECT * FROM  ".$conispc1.".patient_master join  ".$conispc2.".patient_discharge on
		".$conispc1.".patient_master.ipid=".$conispc2.".patient_discharge.ipid";
		$clientdata = mysql_query("SELECT * FROM  ".$conispc1.".patient_master join  ".$conispc2.".patient_discharge on
				".$conispc1.".patient_master.ipid=".$conispc2.".patient_discharge.ipid");
		$num = mysql_num_fields($clientdata);

		if($clientdata)
		{
			$row = mysql_fetch_array($clientdata);
			print_r($row);

		}
	}

}
?>