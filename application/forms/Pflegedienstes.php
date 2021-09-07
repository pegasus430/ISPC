<?php

require_once("Pms/Form.php");

class Application_Form_Pflegedienstes extends Pms_Form
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

		$fdoc = new Pflegedienstes();
		$fdoc->clientid = $logininfo->clientid;
		$fdoc->nursing = $post['nursing'];
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
		$fdoc->phone_private = $post['phone_private'];
		$fdoc->phone_emergency = $post['phone_emergency'];
		$fdoc->fax = $post['fax'];
		$fdoc->email = $post['email'];
		$fdoc->doctornumber = $post['doctornumber'];
		$fdoc->comments = $post['comments'];
		$fdoc->palliativpflegedienst = $post['palliativpflegedienst'];
		$fdoc->ppd = $post['ppd'];
		$fdoc->ik_number = $post['ik_number'];
		$fdoc->save();

		$this->move_uploaded_icon($fdoc->id);

		return $fdoc;
	}

	public function InsertDataFromAdmission($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$fdoc = new Pflegedienstes();
		$fdoc->clientid = $logininfo->clientid;
		$fdoc->last_name = $post['doclast_name'];
		$fdoc->street1 = $post['doc_street1'];
		$fdoc->zip = $post['doc_zip'];
		$fdoc->indrop = $post['indrop'];
		$fdoc->city = $post['doc_city'];
		$fdoc->phone_practice =$post['phone_practice'];
		$fdoc->fax =$post['doc_fax'];
		$fdoc->email =$post['doc_email'];

		$fdoc->save();

		return $fdoc;

	}

	public function InsertFromTabData($post)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if(!empty($_REQUEST['pflid'])){
			if(!empty($post['hidd_pflegeid'])){
 
				if($fdoc = Doctrine::getTable('Pflegedienstes')->findByIdAndIndrop($post['hidd_pflegeid'],1)){
					$fdoc = $fdoc{0};
				}
				else
				{
					$fdoc = new Pflegedienstes();
				}
				$fdoc->nursing = $post['pflegedienste'];
				$fdoc->clientid = $clientid;
				$fdoc->indrop  = 1;
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax =$post['fax'];
				$fdoc->email =$post['email'];
				$fdoc->city = $post['city'];
				$fdoc->phone_practice =$post['phone_practice'];
				$fdoc->phone_emergency =$post['phone_emergency'];
				$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
				$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
				$fdoc->ik_number = $post['ik_number'];
				
				$fdoc->ipid = $post['ipid'];
				$fdoc->is_contact = $post['is_contact'];
				
				$fdoc->save();
			}
			else
			{
				$fdoc = new Pflegedienstes();
				$fdoc->nursing = $post['pflegedienste'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax =$post['fax'];
				$fdoc->email =$post['email'];
				$fdoc->city = $post['city'];
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->phone_practice =$post['phone_practice'];
				$fdoc->phone_emergency =$post['phone_emergency'];
				$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
				$fdoc->ik_number = $post['ik_number'];
				
				$fdoc->ipid = $post['ipid'];
				$fdoc->is_contact = $post['is_contact'];
				
				$fdoc->save();
			}
		} else {
			$fdoc = new Pflegedienstes();
			$fdoc->nursing = $post['pflegedienste'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->fax =$post['fax'];
			$fdoc->email =$post['email'];
			$fdoc->city = $post['city'];
			$fdoc->indrop = 1;
			$fdoc->clientid = $clientid;
			$fdoc->phone_practice =$post['phone_practice'];
			$fdoc->phone_emergency =$post['phone_emergency'];
			$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
			$fdoc->ik_number = $post['ik_number'];
			
			$fdoc->ipid = $post['ipid'];
			$fdoc->is_contact = $post['is_contact'];
			
			$fdoc->save();
			
			if (empty($post['hidd_pflegeid'])) {
			    $this->_manual_nurse_message_send($fdoc);
			}
			
		}
		return $fdoc;
	}

	public function UpdateData ( $post )
	{

		if (!empty($_SESSION['filename']))
		{
			$this->move_uploaded_icon($post['did']);
		}
		$fdoc = Doctrine::getTable('Pflegedienstes')->find($post['did']);
		$fdoc->nursing = $post['nursing'];
		$fdoc->title = $post['title'];
		if ($post['clientid'] > 0)
		{
			$fdoc->clientid = $post['clientid'];
		}
		$fdoc->last_name = $post['last_name'];
		$fdoc->first_name = $post['first_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->salutation = $post['salutation'];
		$fdoc->medical_speciality = $post['medical_speciality'];
		$fdoc->city = $post['city'];
		$fdoc->phone_practice = $post['phone_practice'];
		$fdoc->phone_private = $post['phone_private'];
		$fdoc->phone_emergency = $post['phone_emergency'];
		$fdoc->fax = $post['fax'];
		$fdoc->email = $post['email'];
		$fdoc->doctornumber = $post['doctornumber'];
		$fdoc->comments = $post['comments'];
		$fdoc->palliativpflegedienst = $post['palliativpflegedienst'];
		$fdoc->ppd = $post['ppd'];
		$fdoc->ik_number = $post['ik_number'];
		$fdoc->save();
	}

	private function move_uploaded_icon ( $inserted_icon_id )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		if(!empty($_SESSION['filename'])){
			$filename_arr = explode(".", $_SESSION['filename']);

			if (count($filename_arr >= '2'))
			{
				$filename_ext = $filename_arr[count($filename_arr) - 1];
			}
			else
			{
				$filename_ext = 'jpg';
			}

			//move icon file to desired destination /public/icons/clientid/pflege/icon_db_id.ext
			$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
			$icon_new_path = 'icons_system/' . $clientid . '/pflege/' . $inserted_icon_id . '.' . $filename_ext;

			copy($icon_upload_path, $icon_new_path);
			unlink($icon_upload_path);

			$update = Doctrine::getTable('Pflegedienstes')->find($inserted_icon_id);
			$update->logo = $clientid . '/pflege/' . $inserted_icon_id . '.' . $filename_ext;
			$update->save();
		}
	}

	public function InsertFromDischargePlanning($post,$pflegedienst)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$fdoc = new Pflegedienstes();
		$fdoc->nursing = $pflegedienst;
		$fdoc->first_name = $post['first_name'];
		$fdoc->last_name = $post['last_name'];
		$fdoc->street1 = $post['street1'];
		$fdoc->zip = $post['zip'];
		$fdoc->fax =$post['fax'];
		$fdoc->email =$post['email'];
		$fdoc->city = $post['city'];
		$fdoc->indrop = 1;
		$fdoc->clientid = $clientid;
		$fdoc->phone_practice =$post['phone_practice'];
		$fdoc->phone_emergency =$post['phone_emergency'];
		$fdoc->save();
		return $fdoc;
	}

	

	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 *
	 * @param Pharmacy $fdoc
	 * @return void
	 */
	private function _manual_nurse_message_send(Pflegedienstes $fdoc)
	{
	    
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }
	
	    $doctor_first_last_name = $fdoc->nursing . ',  ' . $fdoc->first_name . " " . $fdoc->last_name;
	
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


        $message_title = $this->translate("New Nurse was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = $this->translate('New Nurse (%s) was manualy added, please take action on %s', $doctor_first_last_name, $patientLink);
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
        $message_body = $this->translate('New Nurse (%s) was manualy added, please take action on %s', $doctor_first_last_name, $patientMasterData['epid']);
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