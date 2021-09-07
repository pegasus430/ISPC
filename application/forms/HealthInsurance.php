<?php

	require_once("Pms/Form.php");

	class Application_Form_HealthInsurance extends Pms_Form {

		public function validate($post, $onlyclient = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$error = 0;
			$Tr = new Zend_View_Helper_Translate();
			$val = new Pms_Validation();

			if($onlyclient)
			{
				if($logininfo->clientid == 0 || empty($logininfo->clientid))
				{
					$this->error_message['clientid'] = $Tr->translate("selectclient");
					$error = 1;
				}
			}
			else
			{
				if(!$val->isstring($post['client_name']))
				{
					$this->error_message['clientid'] = $Tr->translate("selectclient");
					$error = 1;
				}
			}

			if(!$val->isstring($post['name']))
			{
				$this->error_message['name'] = $Tr->translate("entername hi");
				$error = 2;
			}
			
			
			// validate debitor number 
			if(!empty($post['debtor_number'])){
			    $hi = new HealthInsurance();
		        $company_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : false;
		        
			    if($hi->check_hi_debitor_exists($logininfo->clientid, trim($post['debtor_number']),$company_id) ){
				    $this->error_message['debtor_number'] = $Tr->translate("debitor number already exists in client");
				    $error = 2;
			        
			    }
			}
						

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
			//ISPC-1241 -  Health insurance save - Liliana U.

			/* $drop = Doctrine_Query::create()
			  ->select('id')
			  ->from('HealthInsurance')
			  ->limit(1)
			  ->orderBy('id DESC');
			  $dropexec = $drop->execute();
			  $droparray = $dropexec->toArray();

			  if (count($droparray))
			  {
			  $cust = Doctrine::getTable('HealthInsurance')->find($droparray[0]['id']);
			  $cust->valid_till = date("Y-m-d", time());
			  $cust->save();
			  } */

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$valifrom = explode(".", $post['valid_from']);
			$validtill = explode(".", $post['valid_till']);

			$hinsu = new HealthInsurance();
			$hinsu->clientid = $post['client_name'];
			$hinsu->name = $post['name'];
			$hinsu->name2 = $post['name2'];
			$hinsu->street1 = $post['street1'];
			$hinsu->street2 = $post['street2'];
			$hinsu->zip = $post['zip'];
			$hinsu->city = $post['city'];
			$hinsu->phone = $post['phone'];
			$hinsu->phonefax = $post['phonefax'];
			$hinsu->kvnumber = $post['kvnumber'];
			$hinsu->iknumber = $post['iknumber'];
			$hinsu->debtor_number = $post['debtor_number'];
			$hinsu->comments = $post['comments'];
			$hinsu->valid_from = date("Y-m-d", time());
			$hinsu->extra = 0;
			$hinsu->onlyclients = '0';
			$hinsu->price_sheet_group = $post['price_sheet_group'];

			$hinsu->he_price_list_type = $post['he_price_list_type'];
			
			// ISPC-2461 Ancuta 03.10.2019
			$hinsu->demstepcare_billing = $post['demstepcare_billing'];
			// --
			$hinsu->save();
		}

		public function InsertClientData($post)
		{
			//ISPC-1241 -  Health insurance save - Liliana U.

			/* $drop = Doctrine_Query::create()
			  ->select('id')
			  ->from('HealthInsurance')
			  ->limit(1)
			  ->orderBy('id DESC');
			  $dropexec = $drop->execute();
			  $droparray = $dropexec->toArray();

			  if (count($droparray))
			  {

			  $cust = Doctrine::getTable('HealthInsurance')->find($droparray[0]['id']);
			  $cust->valid_till = date("Y-m-d", time());
			  $cust->save();
			  } */


			$logininfo = new Zend_Session_Namespace('Login_Info');
			$valifrom = explode(".", $post['valid_from']);
			$validtill = explode(".", $post['valid_till']);

			$hinsu = new HealthInsurance();
			$hinsu->clientid = $logininfo->clientid;
			$hinsu->name = $post['name'];
			$hinsu->name2 = $post['name2'];
			$hinsu->insurance_provider = $post['insurance_provider'];
			$hinsu->street1 = $post['street1'];
			$hinsu->street2 = $post['street2'];
			$hinsu->zip = $post['zip'];
			$hinsu->city = $post['city'];
			$hinsu->phone = $post['phone'];
			$hinsu->phone2 = $post['phone2'];
			$hinsu->post_office_box = $post['post_office_box'];
			$hinsu->post_office_box_location = $post['post_office_box_location'];
			$hinsu->zip_mailbox = $post['zip_mailbox'];
			$hinsu->phonefax = $post['phonefax'];
			$hinsu->email = $post['email'];
			$hinsu->kvnumber = $post['kvnumber'];
			$hinsu->iknumber = $post['iknumber'];
			$hinsu->debtor_number = $post['debtor_number'];
			$hinsu->comments = $post['comments'];
			$hinsu->valid_from = date("Y-m-d", time());
			$hinsu->extra = 0;
			$hinsu->onlyclients = '1';
			$hinsu->price_sheet_group = $post['price_sheet_group'];

			$hinsu->he_price_list_type = $post['he_price_list_type'];
			// ISPC-2461 Ancuta 03.10.2019
			$hinsu->demstepcare_billing = $post['demstepcare_billing'];
			// --
			$hinsu->save();

			$company_id = $hinsu->id;

			if(!empty($post['subdivizions_permissions']))
			{
				foreach($post['subdivizion'] as $subdiv_id => $subdiv_details)
				{
					$insert = 0;
					foreach($subdiv_details as $inputs)
					{
						if(!empty($inputs))
						{
							$insert += 1;
						}
						else
						{
							$insert += 0;
						}
					}

					if($insert > 0)
					{
						$hinsu = new HealthInsurance2Subdivisions();
						$hinsu->clientid = $logininfo->clientid;
						$hinsu->company_id = $company_id;
						$hinsu->subdiv_id = $subdiv_id;
						$hinsu->name = $subdiv_details['name'];
						$hinsu->insurance_provider = $subdiv_details['insurance_provider'];
						$hinsu->name2 = $subdiv_details['name2'];
						$hinsu->contact_person = $subdiv_details['contact_person'];
						$hinsu->street1 = $subdiv_details['street1'];
						$hinsu->street2 = $subdiv_details['street2'];
						$hinsu->zip = $subdiv_details['zip'];
						$hinsu->city = $subdiv_details['city'];
						$hinsu->phone = $subdiv_details['phone'];
						$hinsu->phone2 = $subdiv_details['phone2'];
						$hinsu->post_office_box = $subdiv_details['post_office_box'];
						$hinsu->post_office_box_location = $subdiv_details['post_office_box_location'];
						$hinsu->zip_mailbox = $subdiv_details['zip_mailbox'];
						$hinsu->kvnumber = $subdiv_details['kvnumber'];
						$hinsu->iknumber = $subdiv_details['iknumber'];
						$hinsu->ikbilling = $subdiv_details['ikbilling'];
						$hinsu->debtor_number = $subdiv_details['debtor_number'];
						$hinsu->fax = $subdiv_details['fax'];
						$hinsu->email = $subdiv_details['email'];
						$hinsu->comments = $subdiv_details['comments'];
						$hinsu->valid_from = date("Y-m-d", time());
						$hinsu->patientonly = 0;
						$hinsu->onlyclients = '1';
						$hinsu->save();
					}
				}
			}
		}

		public function UpdateData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$valifrom = explode(".", $post['valid_from']);
			$validtill = explode(".", $post['valid_till']);

			$existing_company = array();	//ISPC-2452
			$hinsu = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
			$existing_company = $hinsu->toArray();				//ISPC-2452
			if($post['clientid'] > 0)
			{
				$hinsu->clientid = $post['clientid'];
			}
			$hinsu->name = $post['name'];
			$hinsu->name2 = $post['name2'];
			$hinsu->insurance_provider = $post['insurance_provider'];
			$hinsu->street1 = $post['street1'];
			$hinsu->street2 = $post['street2'];
			$hinsu->zip = $post['zip'];
			$hinsu->city = $post['city'];
			$hinsu->phone = $post['phone'];
			$hinsu->phone2 = $post['phone2'];
			$hinsu->post_office_box = $post['post_office_box'];
			$hinsu->post_office_box_location = $post['post_office_box_location'];
			$hinsu->zip_mailbox = $post['zip_mailbox'];
			$hinsu->phonefax = $post['phonefax'];
			$hinsu->email = $post['email'];
			$hinsu->kvnumber = $post['kvnumber'];
			$hinsu->iknumber = $post['iknumber'];
			$hinsu->debtor_number = $post['debtor_number'];
			$hinsu->comments = $post['comments'];
			$hinsu->valid_from = $valifrom[2] . "-" . $valifrom[1] . "-" . $valifrom[0];
			$hinsu->valid_till = $validtill[2] . "-" . $validtill[1] . "-" . $validtill[0];
			$hinsu->extra = 0;
			$hinsu->price_sheet_group = $post['price_sheet_group'];

			$hinsu->he_price_list_type = $post['he_price_list_type'];
			
			// ISPC-2461 Ancuta 03.10.2019
			$hinsu->demstepcare_billing = $post['demstepcare_billing'];
			// --
			$hinsu->save();

			$company_id = $_GET['id'];
			
			//ISPC-2452
			if($post['update_company_in_patients'] == '1'){
			    
			// UPDATE ALL PATIENTS DEBITOR NUMBERS
    			$ph = new PatientHealthInsurance();
    			$patients2company = $ph->get_client_hicompany_patients($post['clientid'], $company_id);
			
    			if(!empty($patients2company)){
    			    foreach($patients2company as $k=>$ph_info){
    			        if(trim($existing_company['name']) == trim($ph_info['company_name'])){
    			            
    			            $q = Doctrine_Query::create()
    			            ->update('PatientHealthInsurance')
    			            ->set('ins_debtor_number', "?" , Pms_CommonData::aesEncrypt($post['debtor_number']))
    			            ->set('change_date',"'".$ph_info['change_date']."'" )
    			            ->set('change_user',"'".$ph_info['change_user']."'" )
    			            ->where("ipid = ?", $ph_info['ipid'])
    			            ->andWhere("companyid = ?", $company_id)
    			            ;
    			            $q->execute();
    			        }
    			    }
    			}
			}
			//-- 


			if(!empty($post['subdivizions_permissions']))
			{
				$ph = Doctrine_Query::create()
					->delete('HealthInsurance2Subdivisions')
					->where("company_id='" . $company_id . "'")
					->andWhere("isdelete = 0 ")
					->andWhere("patientonly = 0 ")
					->andWhere("onlyclients = 1 ");
				$ph->execute();


				foreach($post['subdivizion'] as $subdiv_id => $subdiv_details)
				{
					$insert = 0;
					foreach($subdiv_details as $inputs)
					{
						if(!empty($inputs))
						{
							$insert += 1;
						}
						else
						{
							$insert += 0;
						}
					}

					if($insert > 0)
					{

						$hinsu = new HealthInsurance2Subdivisions();
						$hinsu->clientid = $logininfo->clientid;
						$hinsu->company_id = $company_id;
						$hinsu->subdiv_id = $subdiv_id;
						$hinsu->name = $subdiv_details['name'];
						$hinsu->insurance_provider = $subdiv_details['insurance_provider'];
						$hinsu->name2 = $subdiv_details['name2'];
						$hinsu->contact_person = $subdiv_details['contact_person'];
						$hinsu->street1 = $subdiv_details['street1'];
						$hinsu->street2 = $subdiv_details['street2'];
						$hinsu->zip = $subdiv_details['zip'];
						$hinsu->city = $subdiv_details['city'];
						$hinsu->phone = $subdiv_details['phone'];
						$hinsu->phone2 = $subdiv_details['phone2'];
						$hinsu->post_office_box = $subdiv_details['post_office_box'];
						$hinsu->post_office_box_location = $subdiv_details['post_office_box_location'];
						$hinsu->zip_mailbox = $subdiv_details['zip_mailbox'];
						$hinsu->kvnumber = $subdiv_details['kvnumber'];
						$hinsu->iknumber = $subdiv_details['iknumber'];
						$hinsu->ikbilling = $subdiv_details['ikbilling'];
						$hinsu->debtor_number = $subdiv_details['debtor_number'];
						$hinsu->fax = $subdiv_details['fax'];
						$hinsu->email = $subdiv_details['email'];
						$hinsu->comments = $subdiv_details['comments'];
						$hinsu->valid_from = date("Y-m-d", time());
						$hinsu->patientonly = 0;
						$hinsu->onlyclients = '1';
						$hinsu->save();
					}
				}
			}
		}

	}

?>