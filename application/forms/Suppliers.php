<?php

	require_once("Pms/Form.php");

	class Application_Form_Suppliers extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if(!$val->isstring($post['city']))
			{
				$this->error_message['city'] = $Tr->translate('city_error');
				$error = 7;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($post['indrop'] == 1)
			{
				if(strlen($post['pharmlast_name']) > 0)
				{
					$post['last_name'] = $post['pharmlast_name'];
				}
			}

			$fdoc = new Suppliers();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->supplier = $post['supplier'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->type = $post['type'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->indrop = $post['indrop'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->fax = $post['fax'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];
			$fdoc->save();

			return $fdoc;
		}

		public function InsertFromTabData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($_GET['supplier_id']) && $_GET['supplier_id'] == $post['hidd_supplierid'])
			{
				$fdoc = Doctrine::getTable('Suppliers')->findOneByIdAndIndrop($post['hidd_supplierid'], "1");
				$fdoc->type = $post['type'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->phone = $post['phone'];
				$fdoc->save();
			}
			else
			{
				$fdoc = new Suppliers();
				$fdoc->supplier = $post['supplier'];
				$fdoc->type = $post['type'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->phone = $post['phone'];
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->save();
			}

			return $fdoc;
		}

		public function UpdateData($post)
		{

			$fdoc = Doctrine::getTable('Suppliers')->find($post['did']);
			$fdoc->supplier = $post['supplier'];
			if($post['clientid'] > 0)
			{
				$fdoc->clientid = $post['clientid'];
			}
			$fdoc->type = $post['type'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->fax = $post['fax'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];
			$fdoc->save();
		}

	}

?>