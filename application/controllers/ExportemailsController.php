<?php

class ExportemailsController extends Zend_Controller_Action
{
	public function init()
	{
	}

	public	function exportemailAction()
	{
		set_time_limit(0);
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();

		$this->view->usertype = $logininfo->usertype;
		$uem = Doctrine_Query::create()
		->select('distinct(emailid) as emailid ')
		->from('User')
		->where("isdelete=0");
		$uemexc = $uem->execute();

		$userarray = $uemexc->toArray();
		$comma="";
		$emails ="Emails :\n";
		foreach($userarray as $key=>$val)
		{
			if(strlen($val["emailid"])>0)
			{
				$emails .= $comma.$val["emailid"];
				$comma=",\n";
			}
		}
		$emails .= "\n";
		echo $emails;

		$fileName = "Export_emails.csv";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$fileName);
		exit;
	  
	}

	private function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}
		
	private function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}
		
	private function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}
		
	private function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}

	}

}
?>