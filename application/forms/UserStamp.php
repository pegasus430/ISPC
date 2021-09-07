<?php

require_once("Pms/Form.php");

class Application_Form_UserStamp extends Pms_Form
{
	public function validate($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();

		if (!$val->isstring($post['stamp_name']))
		{
			$this->error_message['stampname'] = $Tr->translate('please enter stamp name');
			$error = 1;
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}
	public function InsertData($post)
	{
		$st = new UserStamp();
		$stamparr = $st->getUserStamp($post['userid']);
		$temp = Doctrine_Query::create()
		->select("*")
		->from('UserStamp')
		->where('userid = '.$post['userid'])
		->limit(1)
		->orderBy('id DESC');

		$tempexec = $temp->execute();
		$usrarr = array();
		if($tempexec)
		{
			$usrarr = $tempexec->toArray();
			if(count($usrarr)>0)
			{
				$us = Doctrine::getTable('UserStamp')->find($usrarr[0]['id']);
				$us->valid_till =  date('Y.m.d',time());
				$us->save();
			}
		}

		$user = new UserStamp();
		$user->row1 = $post['row1'];
		$user->row2 = $post['row2'];
		$user->row3 = $post['row3'];
		$user->row4 = $post['row4'];
		$user->row5 = $post['row5'];
		$user->row6 = $post['row6'];
		$user->row7 = $post['row7'];
		$user->userid = $post['userid'];
		$user->valid_from = date('Y.m.d',time());
		$user->save();

	}


	public function InsertDataMultiple($post)
	{
		$user = new UserStamp();
		$user->stamp_name = $post['stamp_name'];
		$user->stamp_lanr = $post['stamp_lanr'];
		$user->stamp_bsnr = $post['stamp_bsnr'];
		$user->row1 = $post['row1'];
		$user->row2 = $post['row2'];
		$user->row3 = $post['row3'];
		$user->row4 = $post['row4'];
		$user->row5 = $post['row5'];
		$user->row6 = $post['row6'];
		$user->row7 = $post['row7'];
		$user->userid = $post['userid'];
		$user->valid_from = date('Y.m.d',time());
		$user->save();
	}


	public function UpdateDataStamp($post)
	{
		$st = new UserStamp();
		$stamparr = $st->getUserStampById($post['userid'],$post['stamp_id']);


		if(count($stamparr )>0)
		{
			$us = Doctrine::getTable('UserStamp')->find($stamparr[0]['id']);
			$us->valid_till =  date('Y.m.d',time());
			$us->save();
		}

		$user = new UserStamp();
		$user->stamp_name = $post['stamp_name'];
		$user->stamp_lanr = $post['stamp_lanr'];
		$user->stamp_bsnr = $post['stamp_bsnr'];
		$user->row1 = $post['row1'];
		$user->row2 = $post['row2'];
		$user->row3 = $post['row3'];
		$user->row4 = $post['row4'];
		$user->row5 = $post['row5'];
		$user->row6 = $post['row6'];
		$user->row7 = $post['row7'];
		$user->userid = $post['userid'];
		$user->valid_from = date('Y.m.d',time());
		$user->save();
	}


}

?>