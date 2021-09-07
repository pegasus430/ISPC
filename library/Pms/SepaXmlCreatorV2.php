<?php

/**
 * main configuration file
 */

$sepa_include_path = APPLICATION_PATH . '/../library/Pms/SEPA/';

require_once($sepa_include_path . 'class.SEPADirectDebitTransaction.php');
require_once($sepa_include_path . 'class.SEPAException.php');
require_once($sepa_include_path . 'class.SEPAGroupHeader.php');
require_once($sepa_include_path . 'class.SEPAMessage.php');
require_once($sepa_include_path . 'class.SEPAPaymentInfo.php');
require_once($sepa_include_path . 'class.URLify.php');


class Pms_SepaXmlCreatorV2 extends SEPAMessage 
{
	// XML-Errors
	var $xmlerrors = array();
	
	private $_errors = array();
	
	//added like this... maybe in 4 months we will have to change again the php controller :(
	public function get_SEPADirectDebitTransaction() {
		return new SEPADirectDebitTransaction();
	}
	public function get_SEPAException() {
		return new SEPAException();
	}
	public function get_SEPAGroupHeader() {
		return new SEPAGroupHeader();
	}
	public function get_SEPAPaymentInfo() {
		return new SEPAPaymentInfo();
	}
	public function get_URLify() {
		return new URLify();
	}
	
	
	
	public function getXmlErrors() 
	{
		return $this->xmlerrors;
		
	}
	
	public function validateXML( $schemePath ) 
	{
		libxml_use_internal_errors(true);
	
		$dom = new DOMDocument();
		$result = $dom->loadXML($this->getXML()->asXML());
	
		if ($result === false) {
			$this->xmlerrors[] = "Document is not well formed\n";
			return false;
		}
		
		if (@($dom->schemaValidate($schemePath))) {
	
			return true;
			
		} else {
			
			$this->xmlerrors[] = "! Document is not valid:\n";
			$errors = libxml_get_errors();
	
			foreach ($errors as $error) {
				if (defined('DEBUGMODE')  && DEBUGMODE) {
					$this->xmlerrors[] = "---\n" . sprintf("file: %s, line: %s, column: %s, level: %s, code: %s\nError: %s",
							basename($error->file),
							$error->line,
							$error->column,
							$error->level,
							$error->code,
							$error->message
					);
				} else {
					$this->xmlerrors[] = "---\n" . sprintf("Error: %s", $error->message);
				}
			}
		}
		
		return false;
	}

	public function saveXML($filename, $options = null)
	{	
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($this->getXML()->asXML());
		$result = $dom->save ($filename, $options = null);
		
		return $result;
		
	}
	
}


?>