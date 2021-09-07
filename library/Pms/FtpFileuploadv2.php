<?php
/**
 * @author claudiu
 * 
 * functions Taken from CodeIgniter class CI_FTP
 * php trows only warnings on ftp fails
 * 
 * TODO: a checksum feauture could be added to the ftp server, so a CRC function can be used on demand to test files after upload
 * TODO: after upload compare filesize of uploaded fith the local one
 * 
 * + refactored on 2018
 * 
 * you can overwrite all zend ini config on __construct, by passing an array with the properties
 *
 */
class Pms_FtpFileuploadv2
{
		
	/**
	 * holds info about last correcty uploaded file
	 */
	public $last_fileupload = array(
	    "localpath"        => NULL, 
	    "ftpath"           => NULL, 
	    "new_ftpath"       => NULL, 
	    "duplicate_name"   => FALSE
	); 

	
	/**
	 * @var resource a FTP stream on success or false on error
	 */
	var $conn_id	= NULL;
	
	
	/**
	 * ftp connection configs
	 */
	private $_ftp_host         = '';
	private $_ftp_port         = '';
	private $_ftp_user         = '';
	private $_ftp_pass         = '';
	private $_ftp_timeout      = 5;
	private $_ftp_passive      = TRUE;
	private $_ftp_pwd_login    = FTP_PWD;
	
	
	/**
	 * this is the main folder that parents foster directory
	 * here are saved the files that are NOT associated with a db record, so you cannot later download via the app interface
	 * must include the trailing slash
	 * hardcoded, please do not change here, use the zend ini file if you need to change
	 * default fosterfiles/
	 */
	private $_foster_basepath = "fosterfiles/";
	

	/**
	 * how many new_names for a duplicate file to try, before giving up
 	 * hardcoded, please do not change here, use the zend ini file if you need to change
 	 * default 10
	 */
	private $_duplicate_file_retrys =  10;
	
	
	/**
	 * a marker that confirms we avoided a colision
	 * hardcoded, please do not change
	 */
	private static $_duplicate_file_prefix = "_DUPLICATE_";
	
	
	/**
	 * @var Application_Controller_Helper_Log
	 */
	private $_logger = NULL;
	
	
	
	public function __construct() 
	{
		
	    if (($bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap')) && ($Ftpserver = $bootstrap->getResource('Ftpserver'))) 
	    {
	         
	        if (isset($Ftpserver['login'])) {
	            $this->_ftp_host       = $Ftpserver['login']['host'];
	            $this->_ftp_port       = $Ftpserver['login']['port'];
	            $this->_ftp_user       = $Ftpserver['login']['user'];
	            $this->_ftp_pass       = $Ftpserver['login']['passwd'];
	            $this->_ftp_timeout    = $Ftpserver['login']['timeout'];
	            $this->_ftp_timeout    = isset($Ftpserver['login']['timeout']) ? (int)$Ftpserver['login']['timeout'] : 5;
	            $this->_ftp_passive    = isset($Ftpserver['login']['passive']) ? boolval($Ftpserver['login']['passive']) : TRUE;
	        }
	        
	        if (isset($Ftpserver['folder'])) {
	            $this->_ftp_pwd_login          = isset($Ftpserver['folder']['pwd']) ? $Ftpserver['folder']['pwd'] : $this->_ftp_pwd_login;
	            $this->_foster_basepath        = isset($Ftpserver['folder']['fosterfiles']) ? $Ftpserver['folder']['fosterfiles'] : $this->_foster_basepath;
	            $this->_duplicate_file_retrys  = isset($Ftpserver['folder']['duplicate_file_retrys']) ? $Ftpserver['folder']['duplicate_file_retrys'] : $this->_duplicate_file_retrys;
	        }
	    }
	    

	    try {
	        $this->_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
	    } catch (Zend_Controller_Action_Exception $e) {
	        //die($e->getMessage());
	    }
	    
	    $args = func_get_args();
		
	    /*
	     * overwrite Ftpserver config from bootstrap
	     */
		if (is_array($args[0]))
    		foreach ($args[0] as $k => $v) {
    			if (isset($this->{$k})) {
    				$this->{$k} =  $v;
    			}
    		}
		
		return true;
	}
	
	public function ftpconnect()
	{
	
		$conn_id = ftp_connect($this->_ftp_host, $this->_ftp_port, $this->_ftp_timeout);
		
		if ($conn_id !== false) {
			
			// login with username and password
			if ($login_result = ftp_login($conn_id, $this->_ftp_user, $this->_ftp_pass)) {

				/*
				 * FTP connected successfully... !
				 * Login OK !
				 * Set passive mode if needed
				 * Please note that ftp_pasv() can only be called after a successful login or otherwise it will fail.
				 */
				if ($this->_ftp_passive == TRUE) {
					ftp_pasv($conn_id, TRUE);
				}
								
				$this->conn_id = $conn_id;
				$this->_ftp_pwd_login = ftp_pwd($this->conn_id);
				
				return TRUE;
			
			} else {
			
				/* 
				 * Login Failed !
				 */
				$message = "Cannot login to FTP server: {$this->_ftp_user}:-CENSOREDPORN-@{$this->_ftp_host}:{$this->_ftp_port}";

				$this->_log_error($message);

				return FALSE; //$error_message;
			} 
		} else {
			/*
			 * FTP Failed to connect !
			 */
			$message = "Cannot connect to FTP server: {$this->_ftp_host}:{$this->_ftp_port}";

			$this->_log_error($message);
			
			return FALSE;
		}
	}

	private function _is_conn()
	{
		if ( ! is_resource($this->conn_id)) {
			return FALSE;
		}
		return TRUE;
	}
	
	public function ftpconclose()
	{
		if ($this ->_is_conn()) {
			ftp_close($this->conn_id);
		}
	}


	public function ftpgetpwd()
	{
		if ( ! $this->_is_conn()) {
			return FALSE;
		} else {
			return ftp_pwd($this->conn_id);
		}
	}

	/**
	 * Change direcotry
	 */
	public function changedir($path = '')
	{
		if ($path == '' OR ! $this->_is_conn()) {
			return FALSE;
		}
	
		$result = @ftp_chdir($this->conn_id, $path);

		return $result;
	}
	
	/**
	 * Create a directory
	 */
	public function ftpmkdir($path = '' , $permissions = NULL)
	{

		if ($path == '' || !$this->_is_conn()) {
			return FALSE;
		}
		
		if (@ftp_mkdir($this->conn_id, $path) !== FALSE) {
			
			if ( !is_null($permissions))
			{
				$this->chmod($path, (int)$permissions);
			}
			
			return TRUE;
			
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Set file permissions
	 */
	public function chmod($path, $perm)
	{
		if ( ! $this->_is_conn()) {
			return FALSE;
		}
		// Permissions can only be set when running PHP >= 5
		$result = @ftp_chmod($this->conn_id, $perm, $path);
	
		if (@ftp_chmod($this->conn_id, $perm, $path) !== FALSE) {
			return TRUE;
		} else {
			return FALSE;
		}
	
		
	}
	

	/**
	 * check directory structure and create folders acording to new path formula: base/(files)/(int)cleintid/([a-z])/
	 * @param string $ftpath
	 * @return boolean
	 */
	public function ftp_chk_dirs($ftpath)
	{
		if ( ! $this->_is_conn()) {
			return FALSE;
		}

		// check if path exists /files/clientid/zip_file_first_letter/zip_file.zip
		if ( ! @ftp_chdir($this->conn_id, $ftpath)) {
			$parts = explode('/', $ftpath);
			//remove the zip_file.zip (there was a folder with zip name!!!)
			unset($parts[count($parts) - 1]);

			foreach ($parts as $part) {
				if (!@ftp_chdir($this->conn_id, $part)) {
					ftp_mkdir($this->conn_id, $part);
					ftp_chdir($this->conn_id, $part);
				}
			}
			//reset the curent directory back to base
			ftp_chdir($this->conn_id, $this->_ftp_pwd_login);
		}
		
		return true;
	}


	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if ( ! $this->_is_conn()) {
			return FALSE;
		}
		if ( ! file_exists($locpath)) {
			return FALSE;
		}
	
		// Set the mode if not specified
		if ($mode == 'auto') {
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($locpath);
			$mode = $this->_settype($ext);
		}
	
		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
	
		$result = @ftp_put($this->conn_id, $rempath, $locpath, $mode);
	
		if ($result === FALSE) {
			return FALSE;
		}
	
		// Set file permissions if needed
		if ( ! is_null($permissions)) {
			$this->chmod($rempath, (int)$permissions);
		}
	
		return TRUE;
	}
	
	
	public function fileupload($localpath, $ftpath, $old_upload = false, $client = null, $foster_file = false)
	{
		if ( ! $this->_is_conn()) {
			//try to conect again
			if ( $this->ftpconnect() !==true ) {
			    return FALSE;
			}
		}
		
		if ( ! file_exists($localpath)) {
			return FALSE;
		}
		
		if (strpos($ftpath, 'uploads') !== false && strpos($ftpath, 'clientuploads') === false && $old_upload === false && $foster_file === false) {
			
			$new_ftpath = $this -> old_path2new($ftpath, $client);
			
			$this -> ftp_chk_dirs($new_ftpath); // check directory structure and create folders if needed
			
		} elseif ($foster_file === true) {

			$new_ftpath = $this -> foster_path_string($ftpath, $client);
			
			$this -> ftp_chk_dirs($new_ftpath);
			
		} else {
			$new_ftpath = $ftpath;
		}
		//no race-condition on ftp
		//check file allready exists, using ftp_size(),  to know if we need to upload with another prefix'_'filename
		$duplicate_file = false;		
		$prefix = '';
		$i=0;
		
		$new_ftpath_tempname = $new_ftpath;
		
		while ((ftp_size($this->conn_id, $new_ftpath_tempname) != -1) && ($i <= $this->_duplicate_file_retrys)) {
				
			$prefix = substr(md5(rand(1, 9999) . microtime()), 0, 5) . "_";
			$new_ftpath_tempname = dirname($new_ftpath) . "/" . $prefix . $this->_duplicate_file_prefix . basename($new_ftpath);
			
			if ($i++ > $this->_duplicate_file_retrys) {
				$this -> _log_error("{$localpath} - cannot create new filename on ftp for a duplicate file ");
				return false;
			}
		}
		
		if ($new_ftpath != $new_ftpath_tempname) {
			
			$new_ftpath = $new_ftpath_tempname;
			$duplicate_file = true;
		}

		
	
		$upload  = ftp_put($this->conn_id, $new_ftpath, $localpath, FTP_BINARY);
		
		if ( ! $upload) {
			
		    $message = "File could`t be uploaded to FTP server: {$this->_ftp_host}:{$this->_ftp_port}" 
			         . PHP_EOL . "Local Path: {$localpath}" 
			         . PHP_EOL . "FTP Path: {$new_ftpath}";
			
			$this -> _log_error($message);
			
			return false;
			
		} else {
			
			$this->last_fileupload = array(
					"localpath"	=> $localpath,
					"ftpath"	=> $ftpath,
					"new_ftpath"=> $new_ftpath,
					"duplicate_name"=>$duplicate_file,
			);
			
			return true;
		}
	}

	
	
	public function get_last_fileupload()
	{
		return $this->last_fileupload;
	}
	
	public function filedownload($localpath, $ftpath, $old_upload = false, $client = null)
	{
		if ( ! $this->_is_conn()) {
			return FALSE;
		}
		
		//convert old path 2 new path
		if (strpos($ftpath, 'uploads') !== false) {
			$new_path = $this -> old_path2new($ftpath, $client);
		} else {
			$new_path = $ftpath;
		}

		//check if file exists in "new path" or "old path"
		$download     = ftp_get($this->conn_id, $localpath, $new_path, FTP_BINARY);

		if ( ! $download) {
		    
			$download = ftp_get($this->conn_id, $localpath, $ftpath, FTP_BINARY);

			if ( ! $download) {

				$message_both = "File could`t be downloaded from NEW & OLD FTP path: {$this->_ftp_host}:{$this->_ftp_port}" 
				                . PHP_EOL . "Local Path: {$localpath}" 
				                . PHP_EOL . "NEW-FTP Path: {$new_path}"
				                . PHP_EOL . "OLD-FTP Path: {$ftpath}";	
				
				$this -> _log_error($message_both);
				
				return false;
				
			} else {
			    
				return $download;
			}
		} else {
		    
			return $download;
		}
	}

	private function _log_error($message = '')
	{
	    if ($this->_logger) {
	        $this->_logger->ftperror($message);
	    }
	}

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _getext($filename)
	{
		if (FALSE === strpos($filename, '.'))
		{
			return 'txt';
		}
	
		$x = explode('.', $filename);
		return end($x);
	}
	
	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _settype($ext)
	{
		$text_types = array(
				'txt',
				'text',
				'php',
				'phps',
				'php4',
				'js',
				'css',
				'htm',
				'html',
				'phtml',
				'shtml',
				'log',
				'xml'
		);
	
	
		return (in_array($ext, $text_types)) ? 'ascii' : 'binary';
	}
	
	
	/**
	 * FTP List files in the specified directory
	 *
	 * @access	public
	 * @return	array
	 */
	public function list_files($path = '.', $include_fullpath = false)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}
		
		$ftp_nlist =  ftp_nlist($this->conn_id, $path);
		
		if ( ! $include_fullpath) {
			array_walk($ftp_nlist, function(&$val) {
				$pathinfo = pathinfo($val); 
				$val = $pathinfo['basename'];
			});
		}
		
		return $ftp_nlist;
	}

	public function foster_path_string($old_link = false, $client = null) 
	{
		if(is_null($client)) {
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
		} else {
		    
			$clientid = $client;	
		}
		
		if($old_link && $clientid > 0) {
		    
			$new_path = $this->_foster_basepath . dirname($old_link). "/". $clientid . '/' . basename($old_link);

			return $new_path;
			
		} else {
			
			return false;
		}
		
	}
	
	public function old_path2new($old_link = false, $client = null)
	{
			
		if(is_null($client)) {
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
		} else {
		    
			$clientid = $client;
		}
	
		//magic requires some ingredients
		if($old_link && $clientid > 0) {
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
		return self::$_duplicate_file_prefix;
	}
	
}





?>