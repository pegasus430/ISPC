<?php

	class Pms_FtpFileupload {

		public static function ftpconnect()
		{
			$conn_id = ftp_connect(Zend_Registry::get('ftpserver'), Zend_Registry::get('ftpserverport'), 3);
			// login with username and password
			if($conn_id !== false)
			{
				$login_result = ftp_login($conn_id, Zend_Registry::get('ftpserveruser'), Zend_Registry::get('ftpserverpasswd'));
				// check connection
				ftp_pasv($conn_id, true);
				if((!$conn_id) || (!$login_result))
				{

					//$error_message = "FTP connection has failed!";
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					$log = new Zend_Log($writer);

					$message = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri() . "\n" . 'Cannot login to FTP server: ' . "\n" . Zend_Registry::get('ftpserveruser') . ':' . Zend_Registry::get('ftpserverpasswd') . '@' . Zend_Registry::get('ftpserver') . ":" . Zend_Registry::get('ftpserverport');

					if($log)
					{
						$log->crit($message);
					}
					self::errormail($message);

					return $conn_id; //$error_message;
					//exit;
				}
				else
				{
					//$error_message = "FTP connected successfully...!";
					return $conn_id; //$error_message;
				}
			}
			else
			{
				$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
				$log = new Zend_Log($writer);

				$message = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri() . "\n" . 'Cannot connect to FTP server: ' . "\n" . Zend_Registry::get('ftpserver') . ":" . Zend_Registry::get('ftpserverport');

				if($log)
				{
					$log->crit($message);
				}
				self::errormail($message);
			}
		}

		public static function ftpconclose($conn_id)
		{
			ftp_close($conn_id);
			//$error_message = "FTP connected successfully...!";
			//return  $error_message;
		}

		public static function ftpmkdir($con, $dirname)
		{

			if(ftp_mkdir($con, $dirname))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function ftp_chk_dirs($con, $ftpath)
		{

			// check if path exists /files/clientid/zip_file_first_letter/zip_file.zip
			if(!@ftp_chdir($con, $ftpath))
			{
				$parts = explode('/', $ftpath);
				//remove the zip_file.zip (there was a folder with zip name!!!)
				unset($parts[count($parts) - 1]);

				foreach($parts as $part)
				{
					if(!@ftp_chdir($con, $part))
					{
						ftp_mkdir($con, $part);
						ftp_chdir($con, $part);
					}
				}

				//reset the curent directory back to base
				ftp_chdir($con, "/");
			}
		}

		public function old_path2new($old_link = false, $client = null)
		{
			
			if(empty($client)) {
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
			} else {
				$clientid = $client;
			}

			//magic requires some ingredients
			if($old_link && $clientid > '0')
			{
				//extract path data
				$path_parts = pathinfo($old_link);

				//uploaded zip file
				$zip_file = $path_parts['basename'];
				//zip file first letter
				$zip_file_fl = substr($zip_file, '0', '1');

				//resulted "rabit" is here
				$new_path = 'files/' . $clientid . '/' . $zip_file_fl . '/' . $zip_file;

				return $new_path;
			}
		}

		public static function fileupload($con, $localpath, $ftpath, $old_upload = false, $client = null)
		{	
// 			$controllername = (Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
// 			$actionname = (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
// 			echo "<pre>";
// 			print_r($_REQUEST);
// 			print_r(func_get_args());
			
// 			die($controllername ." " .$actionname ." <br><br> old ftp fileupload<hr>PLEASE ALERT ADMIN U HAVE SEEN THIS MESSAGE !!!<hr>");
			if(strpos($ftpath, 'uploads') !== false && strpos($ftpath, 'clientuploads') === false && $old_upload === false)
			{
				$new_ftpath = Pms_FtpFileupload::old_path2new($ftpath, $client);
				Pms_FtpFileupload::ftp_chk_dirs($con, $new_ftpath);
			}
			else
			{
				$new_ftpath = $ftpath;
			}

// 			die($new_ftpath . "<br>". $localpath);
			$upload = ftp_put($con, $new_ftpath, $localpath, FTP_BINARY);
			$message = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri() . "\n" . 'File could`t be uploaded to FTP server: ' . "\n" . Zend_Registry::get('ftpserver') . ":" . Zend_Registry::get('ftpserverport') . "\n Local Path: " . $localpath . "\n FTP Path: " . $new_ftpath . "\n\n";
			if(!$upload)
			{
				self::errormail($message);
				return false;
			}
			else
			{
				return true;
			}
		}

		public static function filedownload($con, $localpath, $ftpath, $old_upload = false, $client = null)
		{
			/*$controllername = (Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
			$actionname = (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
			echo "<pre>";
			print_r($_REQUEST);
			print_r(func_get_args());
				
			die($controllername ." " .$actionname ." <br><br> old ftp filedownload<hr>PLEASE ALERT ADMIN U HAVE SEEN THIS MESSAGE !!!<hr>");
			*/
			//convert old path 2 new path
			if(strpos($ftpath, 'uploads') !== false)
			{
				$new_path = Pms_FtpFileupload::old_path2new($ftpath, $client);
			}
			else
			{
				$new_path = $ftpath;
			}

			//check if file exists in "new path" or "old path"
			$download = ftp_get($con, $localpath, $new_path, FTP_BINARY);
			$message_both = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri() . "\n" . 'File could`t be downloaded from NEW & OLD FTP path: ' . "\n" . Zend_Registry::get('ftpserver') . ":" . Zend_Registry::get('ftpserverport') . "\n Local Path: " . $localpath . "\n NEW-FTP Path: " . $new_path . "OLD-FTP Path: " . $ftpath . "\n\n";

			if(!$download)
			{

				$download = ftp_get($con, $localpath, $ftpath, FTP_BINARY);

				if(!$download)
				{
					self::errormail($message_both);
					return false;
				}
				else
				{
					return $download;
				}
			}
			else
			{
				return $download;
			}
		}

		private function errormail($exception)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$mailmessage .= "Page name :" . $_SERVER['REQUEST_URI'];
			$mailmessage .= "<br /><div> Browser: " . $_SERVER['HTTP_USER_AGENT'] . "</div><br />";
			$mailmessage .= "<div>" . $exception . " </div>";
			$mailmessage .= "<div> Date: " . date("d.m.Y H:i:s", time()) . "</div><br />";
			$mailmessage .= "<div> Username: " . $logininfo->username . "</div><br />";
			$mailmessage .= "<div> IP-Address: " . $_SERVER['REMOTE_ADDR'] . "</div><br />";

			$mail = new Zend_Mail();
			$mail->setBodyHtml($mailmessage)
				->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
				->addTo(ISPC_ERRORMAILTO, ISPC_ERRORSENDERNAME)
				->setSubject('ISPC Error - ' . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'] . ' (' . date("d.m.Y H:i:s") . ')')
				->send();
		}

	}

?>