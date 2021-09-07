<?php
//TODO-3993 ISPC Exchange email issue
use PHPMailer\PHPMailer\SMTP;
// -- //
/**
 * 
 * @author claudiu
 * ;extension=php_openssl.dll
 */
class ClientSMTPSettings extends BaseClientSMTPSettings
{

	private $_default_server_smtp = array();
	
	public function construct()
	{
		parent::construct();
		
		$this->_default_server_smtp = array(
				"host"			=> ISPC_SMTP_SERVER,
				"config"		=> Zend_Registry::get('mail_transport_cfg'),
				"sender_email"	=> ISPC_SENDER,
				"sender_name"	=> ISPC_SENDERNAME,
		);
	}
	
	/**
	 * fn used in for the viewer
	 * @param number $clientid
	 * @return mixed
	 */
	public function get_smtp_settings(  $clientid = 0 ) 
	{
		$salt = Zend_Registry::get('salt');
		
		$result = Doctrine_Query::create()
		->select('
				*,
				AES_DECRYPT( sender_name, :salt ) as sender_name,
				AES_DECRYPT( sender_email, :salt )  as sender_email,
				AES_DECRYPT( smtp_username, :salt )  as smtp_username,
				AES_DECRYPT( smtp_password, :salt )  as smtp_password'
		)
		->from('ClientSMTPSettings')
		->Where('clientid = :clientid')
		->andWhere('isdelete = 0')
		->limit(1)
		->fetchOne(array(
				"clientid"=>$clientid,
				"salt"=>$salt			
				) , 
				Doctrine_Core::HYDRATE_ARRAY
				);
					
		return $result;
	}
	
	
	/**
	 * Aug 10, 2017 @claudiu added: return $this->$_default_server_smtp; so we can use more easely
	 * Dec 05, 2017 @claudiu added: $getDefault to force usage of the ISPC config smtp
	 * 
	 * fn used in php for Zend_Mail_Transport_Smtp
	 * @param number $clientid
	 * @return boolean|Ambigous <unknown, multitype:unknown multitype:string unknown  mixed >
	 * 
	 * example:
	 * $c_smpt_s = new ClientSMTPSettings();
	 * $smtp_settings = $c_smpt_s->get_mail_transport_cfg( $logininfo->clientid );
	 * $mail_transport 	= new Zend_Mail_Transport_Smtp( $smtp_settings['host'], $smtp_settings['config'] );
	 */
	public function get_mail_transport_cfg(  $clientid = 0, $for_zend =  true , $getDefault = false)
	{
	    if ($getDefault) {
	        return $this->_default_server_smtp;
	    }
	    
		$salt = Zend_Registry::get('salt');
		
		$cfg = Doctrine_Query::create()
		->select('
				*,
				AES_DECRYPT( sender_name, :salt ) as sender_name,
				AES_DECRYPT( sender_email, :salt )  as sender_email,
				AES_DECRYPT( smtp_username, :salt )  as smtp_username,
				AES_DECRYPT( smtp_password, :salt )  as smtp_password'
		)
		->from('ClientSMTPSettings')
		->Where('clientid = :clientid')
		->andWhere('isdelete = 0')
		->limit(1)
		->fetchOne(array(
				"clientid" => $clientid,
				"salt" => $salt
		) ,
				Doctrine_Core::HYDRATE_ARRAY
		);
			
		if (empty($cfg)) {
			
			//use ispc default smtp
			if( $for_zend){
				return $this->_default_server_smtp;
			} else {
				return false; // this is a test-smtp request
			}			
		}

		if ( ! $for_zend) {
			return $cfg; //$cfg has the format wee need in the test fn
		}
		
		//client defined smpt settings
		$config = array(
				"host"			=> $cfg['smtp_server'],
				"config"		=> array(
						"port"		=> "",
						"auth"		=> "login",
						"username"	=> $cfg['smtp_username'],
						"password"	=> $cfg['smtp_password'],
				),
				
				"sender_email"	=> $cfg['sender_email'],
				"sender_name"	=> $cfg['sender_name'],
				
		);
		
		if ($cfg['tls_require'] == "YES") {
			$config['config']['ssl']	= "tls";
			$config['config']['port']	= $cfg['tls_port'];
				
		} elseif ($cfg['ssl_require'] == "YES") {
			$config['config']['ssl']	= "ssl";
			$config['config']['port']	= $cfg['ssl_port'];
				
		}  else {
			$config['config']['port']	= $cfg['smtp_port'];
			
		}

		return $config;
	}
	

	
	
	/**
	 * http://stackoverflow.com/questions/38275823/is-there-a-way-to-validate-an-smtp-configuration-using-zend-mail
	 * @return string|boolean
	 */
	//TODO-3993 ISPC Exchange email issue - changed whole validation function below
	public function validSMTP ( $cfg = array()) {


		$mail_transport 	= new Zend_Mail_Transport_Smtp( $cfg['host'], $cfg['config'] );
		$mail_FromEmail		= $cfg['sender_email'];
		$mail_FromName		= $cfg['sender_name'];

		$mail = new Zend_Mail('UTF-8');
		try {
			$mail->setFrom($mail_FromEmail, $mail_FromName)
				->setReplyTo($mail_FromEmail, $mail_FromName)
				->setSubject('test email delivery')
				->setBodyText('This is a test email')
				->addTo('trash+ispc@smart-q.de');
			$mail_transport->send($mail);
		} catch (\Exception $e) {
		    //self::_log_error($e);
			return "There was an error sending mail with these settings";
		}

		return true;

	}
	
	private function status_match ( $socket, $expected ) {
		// Initialize the response string
		$response = '';
		// Get response until nothing but code is visible
		while ( substr ( $response, 3, 1) != ' ' ) {
			// Receive 250 bytes
			if ( !( $response = fgets ( $socket, 256 ) ) ) {
				// Break if nothing else to read
				break;
			}
		}
		// If the status code is not what was expected
		if ( !( substr ( $response, 0, 3 ) == $expected ) ) {
			// Return false
			return false;
		}
		// Otherwise return true
		return true;
	}
	
	public function realvalidSMTP ($config) {
	  
	    // Create a new Zend transport SMTP object
	    $transport = new Zend_Mail_Transport_Smtp ( $config ["hostname"], [
	        "auth"      =>  "login",
	        "ssl"       =>  $config ["protocol"],
	        "port"      =>  $config ["port"],
	        "username"  =>  $config ["username"],
	        "password"  =>  $config ["password"]
	    ]);
	    // Create a new message and send it to dummy email
	    $mail = new Zend_Mail ("UTF-8");
	    $mail->setBodyText ( "null" );
	    $mail->setFrom ( $config ["from"] );
	    $mail->addTo ( "trash@smart-q.de" );
	    $mail->setSubject ( "Test" );
	    // Attempt to send the email
	    try {
	        // Send the email out
	        $mail->send ( $transport );
	        // If all is well, return true
	        return true;
	    }
	    // Catch all Zend exceptions
	    catch ( Zend_Exception $exception ) {
	        // Invalid configuration
	        return false;
	    }
	}
	
	/**
	 *
	 * @param string $message
	 */
	protected static function _log_info($message)
	{
	    parent::_log_info($message, 12);
	}
	
	/**
	 *
	 * @param string $message
	 */
	protected static function _log_error($message)
	{
	    parent::_log_error($message, 13);
	}

}
?>
