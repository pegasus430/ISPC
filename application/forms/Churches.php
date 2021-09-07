<?php

require_once("Pms/Form.php");

class Application_Form_Churches extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		if(empty($post['name']) || strlen($post['name']) == 0 )
		{
			$this->error_message['name'] = $Tr->translate('enterchurch');
			$error++;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}
	
	public function insertdata ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$ins = new Churches();
		
		/*if ($post['indrop'] == 1)
		{
			$ins->id_master = $post['id_master'];
		} else {
			$ins->id_master = 0;
		}*/
		
		$ins->clientid = $logininfo->clientid;
		$ins->name = $post['name'];
		$ins->contact_firstname = $post['contact_firstname'];
		$ins->contact_lastname = $post['contact_lastname'];
		$ins->street = $post['street'];
		$ins->zip = $post['zip'];
		$ins->city = $post['city'];
		$ins->phone = $post['phone'];
		$ins->phone_cell = $post['phone_cell'];
		$ins->email = $post['email'];
		$ins->indrop = $post['indrop'];
		$ins->save();
	
		return $ins;
	}
	
	public function updatedata ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$upd = Doctrine::getTable('Churches')->findOneById($post['id']);
	
		/*if ($post['indrop'] == 1)
		{
			$upd->id_master = $post['id_master'];
		} else {
			$upd->id_master = 0;
		}*/
	
		$upd->clientid = $logininfo->clientid;
		$upd->name = $post['name'];
		$upd->contact_firstname = $post['contact_firstname'];
		$upd->contact_lastname = $post['contact_lastname'];
		$upd->street = $post['street'];
		$upd->zip = $post['zip'];
		$upd->city = $post['city'];
		$upd->phone = $post['phone'];
		$upd->phone_cell = $post['phone_cell'];
		$upd->email = $post['email'];
		$upd->indrop = $post['indrop'];
		$upd->save();
	
		return $upd;
	}
	
	public function deletedata ( $chsid )
	{
		$del = Doctrine::getTable('Churches')->findOneById($chsid);
		$del->isdelete = 1;
		$del->save();
	}
	
	public function InsertFromTabData($post)
	{
	
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
	
		if(!empty($_REQUEST['chsid'])){
			if(!empty($post['hidd_chsid'])){
				$instab = Doctrine::getTable('Churches')->find($post['hidd_chsid']);				
				$instab->name = $post['name'];
				$instab->contact_firstname = $post['first_name'];
				$instab->contact_lastname = $post['last_name'];
				$instab->street = $post['street'];
				$instab->zip = $post['zip'];				
				$instab->email =$post['email'];
				$instab->city = $post['city'];
				$instab->phone =$post['phone'];
				$instab->phone_cell =$post['phone_cell'];				
				$instab->save();
			}
			else
			{
				$instab = new Churches();
				$instab->id_master = $post['hidd_chsid'];
				$instab->name = $post['name'];
				$instab->contact_firstname = $post['first_name'];
				$instab->contact_lastname = $post['last_name'];
				$instab->street = $post['street'];
				$instab->zip = $post['zip'];
				$instab->email =$post['email'];
				$instab->city = $post['city'];
				$instab->phone =$post['phone'];
				$instab->phone_cell =$post['phone_cell'];
				$instab->indrop = 1;
				$instab->clientid = $clientid;
				$instab->save();
			}
		} else {
				$instab = new Churches();
				$instab->id_master = $post['hidd_chsid'];
				$instab->name = $post['name'];
				$instab->contact_firstname = $post['first_name'];
				$instab->contact_lastname = $post['last_name'];
				$instab->street = $post['street'];
				$instab->zip = $post['zip'];
				$instab->email =$post['email'];
				$instab->city = $post['city'];
				$instab->phone =$post['phone'];
				$instab->phone_cell =$post['phone_cell'];
				$instab->indrop = 1;
				$instab->clientid = $clientid;
				$instab->save();				
		}

		return $instab;
	}
}

?>