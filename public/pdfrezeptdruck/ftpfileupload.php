<?php

defined('FTP_UPLOAD') || define('FTP_UPLOAD', "localhost"); // the string "localhost" is used to copy files localy, and not use ftp
if (defined('FTP_UPLOAD') && FTP_UPLOAD == "localhost") {
	defined('FTP_PWD') || define('FTP_PWD', "/home/claudiu/ftp_fake_local/"); //please append the trailing slash here
	require_once 'FtpFileuploadFakeLocalhost.php';
}
else {

	class FtpFileupload {

		public static function ftpconnect($server, $user, $passwd)
		{
			$conn_id = ftp_connect($server, '2121', 3);
			// login with username and password
			$login_result = ftp_login($conn_id, $user, $passwd);
			// check connection
			ftp_pasv($conn_id, true);
			if((!$conn_id) || (!$login_result))
			{

				//$error_message = "FTP connection has failed!";
				return $conn_id; //$error_message;
				//exit;
			}
			else
			{
				//$error_message = "FTP connected successfully...!";
				return $conn_id; //$error_message;
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

		public function old_path2new($old_link = false)
		{
			$clientid = $_SESSION['Login_Info']['clientid'];

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

		public static function fileupload($con, $localpath, $ftpath, $old_upload = false)
		{
			if(strpos($ftpath, 'uploads') !== false && $old_upload === false)
			{
				$new_ftpath = FtpFileupload::old_path2new($ftpath);
				FtpFileupload::ftp_chk_dirs($con, $new_ftpath);
			}
			elseif($old_upload !== false)
			{
				$new_ftpath = $ftpath;
			}

			$upload = ftp_put($con, $new_ftpath, $localpath, FTP_BINARY);

			if(!$upload)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		public static function filedownload($con, $localpath, $ftpath, $old_upload = false)
		{
			//convert old path 2 new path
			if(strpos($ftpath, 'uploads') !== false)
			{
				$new_path = FtpFileupload::old_path2new($ftpath);
			}
			else
			{
				$new_path = $ftpath;
			}

			//check if file exists in "new path" or "old path"
			$download = ftp_get($con, $localpath, $new_path, FTP_BINARY);

			if(!$download)
			{
				$download = ftp_get($con, $localpath, $ftpath, FTP_BINARY);

				if(!$download)
				{
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

	}


}
?>