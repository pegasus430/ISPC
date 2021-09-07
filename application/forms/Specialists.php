<?php
require_once("Pms/Form.php");
class Application_Form_Specialists extends Pms_Form
{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if (!$val->isstring($post['city']))
		{
			$this->error_message['city'] = $Tr->translate('city_error');
			$error = 7;
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function InsertData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		if ($post['indrop'] == 1)
		{
			if (strlen($post['doclast_name']) > 0)
			{
				$post['last_name'] = $post['doclast_name'];
			}
		}

		$fdoc = new Specialists();
		$fdoc->clientid = $logininfo->clientid;
		$fdoc->practice = $post['practice'];
		$fdoc->title = $post['title'];
		$fdoc->salutation = $post['salutation'];
		$fdoc->medical_speciality = $post['medical_speciality'];
		$fdoc->clientid = $logininfo->clientid;
		$fdoc->last_name = $post['last_name'];
		$fdoc->first_name = $post['first_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->indrop = $post['indrop'];
		$fdoc->city = $post['city'];
		$fdoc->phone_practice = $post['phone_practice'];
		$fdoc->phone_cell = $post['phone_cell'];
		$fdoc->phone_private = $post['phone_private'];
		$fdoc->fax = $post['fax'];
		$fdoc->email = $post['email'];
		$fdoc->doctornumber = $post['doctornumber'];
		$fdoc->comments = $post['comments'];
		$fdoc->save();

		return $fdoc;
	}

	public function InsertFromTabData ( $post, $admission = false )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if ($post['hidd_specialist_id'] > 0)
		{

			$newdoc = 0;
			$retain = Doctrine::getTable('Specialists')->find($post['hidd_specialist_id']);
			if ($retain)
			{
				$retainarr = $retain->toArray();

				if (count($retainarr) > 0)
				{
					if ($retainarr['indrop'] == 1)
					{
						$newdoc = 1;
					}
				}
			}

			if ($post['updatemain'] == 1 || $newdoc == 1)
			{
				if ($admission)
				{
					$fdoc = Doctrine::getTable('Specialists')->find($post['hidd_specialist_id']);
					$fdoc->clientid = $clientid;
					$fdoc->practice = $post['practice'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];

					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->title_letter = $post['title_letter'];
					$fdoc->salutation_letter = $post['salutation_letter'];

					$fdoc->street1 = $post['street1'];
					$fdoc->street2 = $post['street2'];
					$fdoc->zip = $post['zip'];
					$fdoc->city = $post['city'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->fax = $post['fax'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->email = $post['email'];
					$fdoc->kv_no = $post['kv_no'];
					$fdoc->indrop = '1';
					$fdoc->medical_speciality = $post['medical_speciality'];
					$fdoc->valid_from = $post['valid_from'];
					$fdoc->valid_till = $post['valid_till'];
					$fdoc->save();
				}
				else
				{

					$fdoc = Doctrine::getTable('Specialists')->find($post['hidd_specialist_id']);
					$fdoc->clientid = $clientid;
					$fdoc->practice = $post['practice'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];

					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->title_letter = $post['title_letter'];
					$fdoc->salutation_letter = $post['salutation_letter'];

					$fdoc->street1 = $post['street1'];
					$fdoc->street2 = $post['street2'];
					$fdoc->zip = $post['zip'];
					$fdoc->city = $post['city'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->fax = $post['fax'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->email = $post['email'];
					$fdoc->kv_no = $post['kv_no'];
					$fdoc->indrop = '1';
					$fdoc->medical_speciality = $post['medical_speciality'];
					$fdoc->valid_from = $post['valid_from'];
					$fdoc->valid_till = $post['valid_till'];
					$fdoc->save();
				}
			}
			else
			{
				if ($admission)
				{
					$fdoc = new Specialists();
					$fdoc->clientid = $clientid;
					$fdoc->practice = $post['practice'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];

					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->title_letter = $post['title_letter'];
					$fdoc->salutation_letter = $post['salutation_letter'];

					$fdoc->street1 = $post['street1'];
					$fdoc->street2 = $post['street2'];
					$fdoc->zip = $post['zip'];
					$fdoc->city = $post['city'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->fax = $post['fax'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->email = $post['email'];
					$fdoc->kv_no = $post['kv_no'];
					$fdoc->indrop = '1';
					$fdoc->medical_speciality = $post['medical_speciality'];
					$fdoc->valid_from = $post['valid_from'];
					$fdoc->valid_till = $post['valid_till'];
					$fdoc->save();
				}
				else
				{
					$fdoc = new Specialists();
					$fdoc->clientid = $clientid;
					$fdoc->practice = $post['practice'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];

					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->title_letter = $post['title_letter'];
					$fdoc->salutation_letter = $post['salutation_letter'];

					$fdoc->street1 = $post['street1'];
					$fdoc->street2 = $post['street2'];
					$fdoc->zip = $post['zip'];
					$fdoc->city = $post['city'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->fax = $post['fax'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->email = $post['email'];
					$fdoc->kv_no = $post['kv_no'];
					$fdoc->indrop = '1';
					$fdoc->medical_speciality = $post['medical_speciality'];
					$fdoc->valid_from = $post['valid_from'];
					$fdoc->valid_till = $post['valid_till'];
					$fdoc->save();
				}
			}
		}
		else
		{
			if ($admission)
			{
				$fdoc = new Specialists();
				$fdoc->clientid = $clientid;
				$fdoc->practice = $post['practice'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];

				$fdoc->title = $post['title'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->title_letter = $post['title_letter'];
				$fdoc->salutation_letter = $post['salutation_letter'];

				$fdoc->street1 = $post['street1'];
				$fdoc->street2 = $post['street2'];
				$fdoc->zip = $post['zip'];
				$fdoc->city = $post['city'];
				$fdoc->doctornumber = $post['doctornumber'];
				$fdoc->phone_practice = $post['phone_practice'];
				$fdoc->fax = $post['fax'];
				$fdoc->phone_private = $post['phone_private'];
				$fdoc->phone_cell = $post['phone_cell'];
				$fdoc->email = $post['email'];
				$fdoc->kv_no = $post['kv_no'];
				$fdoc->indrop = '1';
				$fdoc->medical_speciality = $post['medical_speciality'];
				$fdoc->valid_from = $post['valid_from'];
				$fdoc->valid_till = $post['valid_till'];
				$fdoc->save();
			}
			else
			{
				$fdoc = new Specialists();
				$fdoc->clientid = $clientid;
				$fdoc->practice = $post['practice'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];

				$fdoc->title = $post['title'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->title_letter = $post['title_letter'];
				$fdoc->salutation_letter = $post['salutation_letter'];

				$fdoc->street1 = $post['street1'];
				$fdoc->street2 = $post['street2'];
				$fdoc->zip = $post['zip'];
				$fdoc->city = $post['city'];
				$fdoc->doctornumber = $post['doctornumber'];
				$fdoc->phone_practice = $post['phone_practice'];
				$fdoc->fax = $post['fax'];
				$fdoc->phone_private = $post['phone_private'];
				$fdoc->phone_cell = $post['phone_cell'];
				$fdoc->email = $post['email'];
				$fdoc->kv_no = $post['kv_no'];
				$fdoc->indrop = '1';
				$fdoc->medical_speciality = $post['medical_speciality'];
				$fdoc->valid_from = $post['valid_from'];
				$fdoc->valid_till = $post['valid_till'];
				$fdoc->save();
			}
			
			$this->_manual_specialist_message_send($fdoc);
		}

		return $fdoc;
	}

	public function UpdateData ( $post )
	{

		$fdoc = Doctrine::getTable('Specialists')->find($post['did']);

		$fdoc->practice = $post['practice'];
		$fdoc->title = $post['title'];
		$fdoc->last_name = $post['last_name'];
		$fdoc->first_name = $post['first_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->salutation = $post['salutation'];
		$fdoc->medical_speciality = $post['medical_speciality'];
		$fdoc->city = $post['city'];
		$fdoc->phone_practice = $post['phone_practice'];
		$fdoc->phone_cell = $post['phone_cell'];
		$fdoc->phone_private = $post['phone_private'];
		$fdoc->fax = $post['fax'];
		$fdoc->email = $post['email'];
		$fdoc->doctornumber = $post['doctornumber'];
		$fdoc->comments = $post['comments'];
		$fdoc->save();
	}
	
	
	

	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 *
	 * @param FamilyDoctor $fdoc
	 * @return void
	 */
	private function _manual_specialist_message_send(Specialists $fdoc)
	{
	
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }
	     
	    $doctor_first_last_name = $fdoc->first_name . " " . $fdoc->last_name;
	
	    $patientMasterData =  $this->_patientMasterData;
	    $pat_encoded_id = $patientMasterData['id'] ? Pms_Uuid::encrypt($patientMasterData['id']) : 0;
	    $patientLink = "<a href='patientcourse/patientcourse?id={$pat_encoded_id}'>{$patientMasterData['epid']}</a>";
	
	    $users = User::get_AllByClientid($this->logininfo->clientid, array('us.manual_familydoc_message', 'username'));
	     
	    //remove inactive and deleted, and the ones with clientid=0
	    $users = array_filter($users, function($user) {
	        return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0) && ($user['UserSettings']['manual_familydoc_message'] == 'yes');
	    });
	         
	         
        if (empty($users)) {
            return; // no settings
        }

        //remove inactive and deleted, and the ones with clientid=0
        $users_with_emails = array_filter($users, function($user) {
            return strlen(trim($user['emailid']));
        });
	
	             
        $message_title = $this->translate("New Specialist was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = $this->translate('New Specialist %s was manualy added, please take action on %s', $doctor_first_last_name, $patientLink);
        $message_body = Pms_CommonData::br2nl($message_body);
        $message_body_enc = Pms_CommonData::aesEncrypt($message_body);

        
        $recipients = array_column($users, 'id');
         
        $records_template = array(
            "sender" => $this->logininfo->userid,
            "clientid" => $this->logininfo->clientid,
            "recipient" => null,
            "recipients" => implode(",", $recipients),
            "msg_date" => date("Y-m-d H:i:s", time()),
            "title" => $message_title_enc,
            "content" => $message_body_enc,
            "create_date" => date("Y-m-d", time()),
            "create_user" => $this->logininfo->userid,
        );
         
        $records_array = array();
        foreach($users as $user) {
            $record = $records_template;
            $record['recipient'] = $user['id'];
            $records_array[] = $record;
        }
        if ( ! empty($records_array)) {
            $collection = new Doctrine_Collection('Messages');
            $collection->fromArray($records_array);
            $collection->save();
        }
         
         
        //send email too ??
        $additional_text = 
        $message_body = $this->translate('New Specialist %s was manualy added, please take action on %s', $doctor_first_last_name, $patientMasterData['epid']);
        //$message_body .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>"; // link to ISPC
        // ISPC-2475 @Lore 31.10.2019
        $message_body .= $this->translate('system_wide_email_text_login');
        
        //TODO-3164 Ancuta 08.09.2020
        $email_data = array();
        $email_data['additional_text'] = $additional_text;
        $message_body = "";//overwrite
        $message_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
        //--
        
        $this->_mail_forceDefaultSMTP = false;
        foreach($users_with_emails  as $user) {
            $this->sendEmail( $user['emailid'] , "ISPC - {$message_title}", $message_body);
        }
         
        return;
	
	}

}
?>