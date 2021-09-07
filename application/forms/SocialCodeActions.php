<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodeActions extends Pms_Form
{
	public function validate($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['action_name'])){
			$this->error_message['action_name']=$Tr->translate('enter_action_name'); $error=1;
		}

		if(!$val->isstring($post['internal_nr'])){
			$this->error_message['internal_nr']=$Tr->translate('enter_internal_nr'); $error=2;
		}

		if($error==0)
		{
		 return true;
		}
		return false;
	}

	public function InsertData($post, $extra = false )
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');


		$user = new SocialCodeActions();
		$user->clientid = $logininfo->clientid;
		$user->internal_nr = $post['internal_nr'];
		$user->parent = $post['parent'];
		$user->action_name = $post['action_name'];
		$user->action_invoice_name = $post['action_invoice_name'];
		$user->description = trim($post['description']);
		$user->pos_nr = $post['pos_nr'];
		$user->max_per_day= $post['max_per_day'];
		$user->default_duration= $post['default_duration'];
		$user->price= $post['price'];
		$user->groupid= $post['group'];


		if($logininfo->usertype=='SA' || !empty($post['form_condition']))
		{
			if(empty($post['form_condition'])){
				$user->form_condition = "other";
			} else{
				$user->form_condition = $post['form_condition'];
			}
				
		} else{
				
			$user->form_condition = "other";
				
		}

		$user->extra= $post['extra'];
		$user->available = $post['available'];

		if($extra !== false){
				
			$user->extra = '1';
			$user->custom = '1';
			$user->parent_list= $post['parent_list'];

		} else{
				
			$user->extra = '0';
			$user->custom = '0';
		}

		$user->save();
			
		return $user->id;
	}

	public function UpdateData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$user = Doctrine::getTable('SocialCodeActions')->find($_GET['id']);
		$user->clientid = $logininfo->clientid;
		$user->parent= $post['parent'];
		$user->internal_nr = $post['internal_nr'];
		$user->action_name = $post['action_name'];
		$user->action_invoice_name = $post['action_invoice_name'];
		$user->description = trim($post['description']);
		$user->pos_nr = $post['pos_nr'];
		$user->max_per_day= $post['max_per_day'];
		$user->default_duration= $post['default_duration'];
		$user->price= $post['price'];
		$user->groupid= $post['group'];

		if($logininfo->usertype=='SA')
		{
			$user->form_condition = $post['form_condition'];
		}

		$user->available = $post['available'];

		$user->save();

	}


}

?>