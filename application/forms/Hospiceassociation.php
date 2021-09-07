<?php

require_once("Pms/Form.php");

class Application_Form_Hospiceassociation extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		// get associated clients of current clientid START
		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		if($connected_client){
		    $clientid = $connected_client;
		} else{
		    $clientid = $logininfo->clientid;
		}
		
		
		
		
		$fdoc = new Hospiceassociation();
		$fdoc->hospice_association = $post['hospice_association'];
		$fdoc->title = $post['title'];
		$fdoc->salutation = $post['salutation'];
		$fdoc->clientid = $clientid;
		$fdoc->last_name = $post['last_name'];
		$fdoc->first_name = $post['first_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->indrop = $post['indrop'];
		$fdoc->city = $post['city'];
		$fdoc->phone_practice =$post['phone_practice'];
		$fdoc->phone_private =$post['phone_private'];
		$fdoc->phone_emergency =$post['phone_emergency'];
		$fdoc->comments =$post['comments'];
		$fdoc->fax =$post['fax'];
		$fdoc->email =$post['email'];
		$fdoc->save();
		return $fdoc;
	}

	public function UpdateData($post)
	{
			
		$fdoc = Doctrine::getTable('Hospiceassociation')->find($post['did']);
		$fdoc->hospice_association = $post['hospice_association'];
		$fdoc->title = $post['title'];
		if($post['clientid']>0)
		{
			$fdoc->clientid = $post['clientid'];
		}
		$fdoc->last_name = $post['last_name'];
		$fdoc->first_name = $post['first_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->salutation = $post['salutation'];
		$fdoc->city = $post['city'];
		$fdoc->phone_practice =$post['phone_practice'];
		$fdoc->phone_private =$post['phone_private'];
		$fdoc->phone_emergency =$post['phone_emergency'];
		$fdoc->fax =$post['fax'];
		$fdoc->email =$post['email'];
		$fdoc->comments =$post['comments'];
		$fdoc->save();
	}


}

?>