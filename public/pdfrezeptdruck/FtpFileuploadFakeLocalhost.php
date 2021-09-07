<?php

//this is a fake-duplicate of ftp-class, to use on localhost-ftp upload, with copy instead of ftp_put

class FtpFileupload
{
	
	private static $ftp_pwd_login = FTP_PWD; // the rootbase / was used on the v.1 , in order to ftp_chdir back to base
			
	private static $duplicate_file_retrys =  10; // how many new_names for a duplicate file to try 
	
	CONST DUPLICATE_FILE_PREFIX = "_DUPLICATE_"; // this will be used for download too.. so if you change here... we will see what hapens
	
	CONST FOSTER_BASEPATH = "fosterfiles/"; // this is the main folder that parents foster directory
	
	public function __construct() {
		
		$args = func_get_args();
		
		if (is_array($args[0]))
		foreach ($args[0] as $k => $v) {
			
			if (isset($this->{$k})) {
				$this->{$k} =  $v;
			}
		}
		return true;
	}
	
	public static function ftpconnect()
	{
		return true;
	}

	public function ftpconclose()
	{
		return true;
	}

	/**
	 * check directory structure and create folders acording to new path formula: base/(files)/(int)cleintid/([a-z])/
	 * @param string $ftpath
	 * @return boolean
	 */
	public static function ftp_chk_dirs($ftpath)
	{
		$ftpath_dir = dirname($ftpath);

		// check if path exists /files/clientid/zip_file_first_letter/zip_file.zip
		if (! file_exists(self::$ftp_pwd_login . $ftpath_dir)) {
			
			$parts = explode('/', $ftpath);
			//remove the zip_file.zip (there was a folder with zip name!!!)
			unset($parts[count($parts) - 1]);

			$all_part = '';
			foreach ($parts as $part) {
				
				$all_part .= $part. "/";
				 
				if ( ! file_exists(self::$ftp_pwd_login . $all_part)) {
					mkdir(self::$ftp_pwd_login . $all_part);
				}
			}			
		}
		return true;
	}
	
	public static function fileupload($connid, $localpath, $ftpath, $old_upload = false, $client = null, $foster_file = false)
	{
		if ( ! file_exists($localpath))
		{
			return FALSE;
		}
		
		if (strpos($ftpath, 'uploads') !== false && strpos($ftpath, 'clientuploads') === false && $old_upload === false && $foster_file === false) {
			
			$new_ftpath = self::old_path2new($ftpath, $client);
			
			self::ftp_chk_dirs($new_ftpath); // check directory structure and create folders if needed
			
		} elseif ($foster_file === true) {

			$new_ftpath = self::foster_path_string($ftpath, $client);
			
			self::ftp_chk_dirs($new_ftpath);
			
		} else {
			$new_ftpath = $ftpath;
		}
		//no race-condition on ftp
		//check file allready exists, using ftp_size(),  to know if we need to upload with another prefix'_'filename
		$duplicate_file = false;		
		$prefix = '';
		$i=0;
		
		$new_ftpath_tempname = $new_ftpath;
		
		
		while ( file_exists(self::$ftp_pwd_login . $new_ftpath_tempname) && ($i <= self::$duplicate_file_retrys)) {
				
			$prefix = substr(md5(rand(1, 9999) . microtime()), 0, 5) . "_";
			$new_ftpath_tempname = dirname($new_ftpath) . "/" . $prefix . self::DUPLICATE_FILE_PREFIX . basename($new_ftpath);
			
			if ($i++ > self::$duplicate_file_retrys) {
				$this -> log_error("{$localpath} - cannot create new filename on ftp for a duplicate file ");
				return false;
			}
		}
		
		if ($new_ftpath != $new_ftpath_tempname) {
			
			$new_ftpath = $new_ftpath_tempname;
			$duplicate_file = true;
		}


		if ( ! copy($localpath , self::$ftp_pwd_login  . $new_ftpath)) {
			echo "error1";
			return false;
			
		} else {
			
			return true;
		}
	}

	public function foster_path_string($old_link = false, $client = null) {
		if(is_null($client)) {
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		} else {
			$clientid = $client;
		}
		if($old_link && $clientid > '0')
		{
			$new_path = self::FOSTER_BASEPATH . dirname($old_link). "/". $clientid . '/' . basename($old_link);

			return $new_path;
			
		} else {
			
			return false;
		}
		
	}
	
	public static function old_path2new($old_link = false, $client = null)
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
	
	
	public static function get_duplicate_file_prefix () 
	{
		return self::DUPLICATE_FILE_PREFIX;
	}
	
}





?>