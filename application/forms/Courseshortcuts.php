<?php

require_once("Pms/Form.php");

class Application_Form_Courseshortcuts extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['shortcut'])){
			$this->error_message['shortcut_error']=$Tr->translate('entershortcut'); $error=1;
		}
		if(!$val->isstring($post['course_fullname'])){
			$this->error_message['course_fullname_error']=$Tr->translate('entercoursefullname'); $error=2;
		}

		if($_GET['id']<1)
		{
			if($logininfo->usertype=="SA")
			{
				$clientid = 0;
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			$course = Doctrine_Query::create()
			->select('*')
			->from('Courseshortcuts')
			->where('isdelete = ?',0)
			->andWhere('clientid = ?',$clientid)
			->andWhere('shortcut="'.$post['shortcut'].'"');


			$courseq  = $course->execute();
			$coursearray = $courseq->toarray();

			if(count($coursearray)>0)
			{
				$this->error_message['shortcut_error'] = $Tr->translate("shortcutalreadyexists");$error=3;
			}
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($logininfo->usertype=="SA")
		{
			$clientid = 0;
		}

		$clientid = $logininfo->clientid;
			
		$course = new Courseshortcuts();
		$course->clientid = $clientid;
		$course->shortcut = $post['shortcut'];
		$course->isfilter = $post['isfilter'];
		$course->font_color = $post['font_color'];
		$course->isbold = $post['isbold'];
		$course->isitalic = $post['isitalic'];
		$course->isunderline = $post['isunderline'];
		$course->course_fullname = $post['course_fullname'];
		$course->save();

	}

	public function UpdateData($post)
	{

		$res = Doctrine::getTable('Courseshortcuts')->find($_GET['id']);
		$res->shortcut = $post['shortcut'];
		$res->isfilter = $post['isfilter'];
		$res->font_color = $post['font_color'];
		$res->isbold = $post['isbold'];
		$res->isitalic = $post['isitalic'];
		$res->isunderline = $post['isunderline'];
		$res->course_fullname = $post['course_fullname'];
		$res->save();
	}
	 


}

?>