<?php

require_once("Pms/Form.php");

class Application_Form_Supplies extends Pms_Form 
{

    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('supplies'), "cols"=>array("Supplies"=>"supplier")),
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('phone1'), "cols"=>array("Supplies" => "phone")),
            array("label"=>$this->translate('fax'), "cols"=>array("Supplies" => "fax")),
    
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("Supplies"=>"supplier")),
            array(array("nice_name")),
            array(array("Supplies"=>"street1")),
            array(array("Supplies"=>"zip"), array("Supplies"=>"city")),
        );
    }
    
    
    
    

    
    
    
    
    
    
    
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

			$fdoc = new Supplies();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->supplier = $post['supplier'];
			$fdoc->salutation = $post['salutation'];
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
			if(strlen($post['logo']) > '0')
			{
				$fdoc->logo = $post['logo'];
			}
			$fdoc->save();


			if(!empty($_SESSION['supplies_filename']))
			{
				$this->move_uploaded_icon($fdoc->id);
			}

			return $fdoc;
		}

		public function InsertFromTabData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($_GET['supplier_id']) && $_GET['supplier_id'] == $post['hidd_supplierid'])
			{
				$fdoc = Doctrine::getTable('Supplies')->findOneByIdAndIndrop($post['hidd_supplierid'], "1");
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->phone = $post['phone'];
				if(strlen($post['logo']) > '0')
				{
					$fdoc->logo = $post['logo'];
				}
				$fdoc->save();
			}
			else
			{
				$fdoc = new Supplies();
				$fdoc->supplier = $post['supplier'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->phone = $post['phone'];
				if(strlen($post['logo']) > '0')
				{
					$fdoc->logo = $post['logo'];
				}
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->save();
				
				if (empty($post['hidd_supplier_id'])) {
				    $this->_manual_supplies_message_send($fdoc);
				}
				
			}

			if(!empty($_SESSION['supplies_filename']))
			{
				$this->move_uploaded_icon($fdoc->id);
			}

			return $fdoc;
		}

		public function UpdateData($post)
		{
			if(!empty($_SESSION['supplies_filename']) && strlen($post['logo']) == "0")
			{
				$this->move_uploaded_icon($post['did']);
			}

			$fdoc = Doctrine::getTable('Supplies')->find($post['did']);
			$fdoc->supplier = $post['supplier'];
			if($post['clientid'] > 0)
			{
				$fdoc->clientid = $post['clientid'];
			}
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->fax = $post['fax'];
			$fdoc->email = $post['email'];
			if(strlen($post['logo']) > '0')
			{
				$fdoc->logo = $post['logo'];
			}
			$fdoc->comments = $post['comments'];
			$fdoc->save();
		}

		public function InsertFromDischargePlanning($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$fdoc = new Supplies();
			$fdoc->supplier = $post['supplier'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->fax = $post['fax'];
			$fdoc->email = $post['email'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->indrop = 1;
			$fdoc->clientid = $clientid;
			$fdoc->save();

			$supplier_id = $fdoc->id;

			if(!empty($_SESSION['supplies_filename']))
			{
				$this->move_uploaded_icon($supplier_id);
			}

			return $supplier_id;
		}

		private function move_uploaded_icon($inserted_icon_id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if(!empty($_SESSION['supplies_filename']))
			{
				$filename_arr = explode(".", $_SESSION['supplies_filename']);

				if(count($filename_arr >= '2'))
				{
					$filename_ext = $filename_arr[count($filename_arr) - 1];
				}
				else
				{
					$filename_ext = 'jpg';
				}

				//move icon file to desired destination /public/icons/clientid/pflege/icon_db_id.ext
				$icon_upload_path = 'icons_system/' . $_SESSION['supplies_filename'];
				$icon_new_path = 'icons_system/' . $clientid . '/supplies/' . $inserted_icon_id . '.' . $filename_ext;

				copy($icon_upload_path, $icon_new_path);
				unlink($icon_upload_path);

				$update = Doctrine::getTable('Supplies')->find($inserted_icon_id);
				if($update)
				{
					$update->logo = $clientid . '/supplies/' . $inserted_icon_id . '.' . $filename_ext;
					$update->save();
					unset($_SESSION['supplies_filename']);
				}
			}
		}


	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 *
	 * @param Pharmacy $fdoc
	 * @return void
	 */
	private function _manual_supplies_message_send(Supplies $fdoc)
	{
	
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }
	
	    $doctor_first_last_name = $fdoc->supplier . ',  ' . $fdoc->first_name . " " . $fdoc->last_name;
	
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
    
    
        $message_title = $this->translate("New Supplier was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = $this->translate('New Supplier %s was manualy added, please take action on %s', $doctor_first_last_name, $patientLink);
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
        $message_body = $this->translate('New Supplier %s was manualy added, please take action on %s', $doctor_first_last_name, $patientMasterData['epid']);
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
	
	
	/**
	 * 
	 * Supplies formular ... this should have been PatientSupplies
	 * @cla on 25.06.2018
	 * 
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_supplies($options =  array() , $elementsBelongTo = null)
	{
	     
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__, "save_form_patient_supplies");

	    
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("supplies");
	    $subform->setAttrib("class", "label_same_size");
	    
	    	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }

	    
	    if ( ! isset($options['Supplies'])) {
	        $options['Supplies'] = $options;
	    }
	     
	     
	    /* start with the hidden fields */
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        //'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	        // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	
	    ),
	    ));
	    
	    
	         
        //TODO: add column self_id in Pflegedienstes table.. so you know what-id you selected from the dropdown
        $subform->addElement('hidden', 'self_id', array(
            'value'        => isset($options['self_id']) ? $options['supplier_id'] : $options['supplier_id'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'supplier_id', array(
            'value'        => $options['supplier_id'] ? $options['supplier_id'] : -1 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
        ),
        ));

         
        
        
        
        /* visible inputs */
        $subform->addElement('text', 'supplier', array(
            'value'        => $options['Supplies']['supplier'] ,
            'label'        => 'supplies',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    
            ),
            'data-livesearch'  => 'Supplies',
        ));
		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Supplies']['first_name'] ,
            'label'        => 'firstname',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'last_name', array(
            'value'        => $options['Supplies']['last_name'] ,
            'label'        => 'lastname',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'salutation', array(
            'value'        => $options['Supplies']['salutation'] ,
            'label'        => 'salutation',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'street1', array(
            'value'        => $options['Supplies']['street1'] ,
            'label'        => 'address',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'zip', array(
            'value'        => $options['Supplies']['zip'] ,
            'label'        => 'zip',
            'data-livesearch'  => 'zip',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),doctornumber
        ));
        $subform->addElement('text', 'city', array(
            'value'        => $options['Supplies']['city'],
            'label'        => 'city',
            'data-livesearch'   => 'city',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'phone', array(
            'value'        => $options['Supplies']['phone'],
            'label'        => 'phone1',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'fax', array(
            'value'        => $options['Supplies']['fax'],
            'label'        => 'fax',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'email', array(
            'value'        => $options['Supplies']['email'],
            'label'        => 'email',
            'required'   => false,
            'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty', 'EmailAddress'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
         
        $subform->addElement('textarea', 'supplier_comment', array(
            'value'        => ! empty($options['supplier_comment']) ?  $options['supplier_comment'] : $options['Supplies']['comments'],
            'label'        => 'comments',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'rows'         => 3,
            //'cols'         => 60,
        ));
    
    
        return $subform;
	
	}
	
	/*
	public function save_form_patient_supplies($ipid =  '' , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	
	    $entity = new Supplies();
	     
	    //$entity->ipid = $ipid;
	     
	    $care_service = $entity->findOrCreateOneBy('id', $data['supplier_id'], $data);
	
	    if ($data['supplier_id'] != $care_service->id) {
	        //new one
	        $data['supplier_id'] = $care_service->id;
	         
	        if (empty($data['self_id']) ) {
	            $this->_manual_supplies_message_send($care_service, $ipid);
	        }
	    }
	     
	    $entity = new PatientSupplies();
	    return $entity->findOrCreateOneByIpidAndId( $ipid, $data['id'], $data);
	
	}
	*/
	


	/**
     *
     * @param string $ipid
     * @param array $data
     * @param number $indrop 0 = in the liveSearch, 1 = not
     * @return void|Doctrine_Record
     */
    public function save_form_patient_supplies($ipid =  '', $data = array(), $indrop = 1)
    {
        $patientModel   = 'PatientSupplies';
        $relationModel  = 'Supplies';
        
        $ipid = ! empty($ipid) ? $ipid : $this->_ipid ;
    
        if (empty($ipid) || empty($data)) {
            return;//fail-safe
        }
        
        $entity = new $patientModel();
        //IPSC-2614
        $pc_listener = $entity->getListener()->get('IntenseConnectionListener');
        $pc_listener->setOption('disabled', true);
        //--
        $entity = $entity->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);

        if ( ! $entity) {
            return; //fail-safe
        }
    
        $localField = null;
        $foreignField = null;
        
        if ($relation = $entity->getTable()->getRelation($relationModel, false)) {
            $relation = $relation->toArray();            
            $localField = $relation['local'];
            $foreignField = $relation['foreign'];
        }
        
        
        if ( ! is_null($localField) && ! is_null($foreignField) && $data[$localField] != $entity->{$localField}) {
            $data[$localField] = $entity->{$localField};
        }
    
        $data['indrop'] = $indrop;
    
        
    
        $relationEntity = new $relationModel();
        $relationEntity = $relationEntity->findOrCreateOneBy(['id', 'clientid'], [$data[$localField], $this->logininfo->clientid], $data);
    
        if ($relationEntity && ! is_null($localField) && $entity->{$localField} != $relationEntity->{$foreignField}) {
            //it was a new one
            $entity->{$localField} = $relationEntity->{$foreignField};
            $entity->save();
            
            if (empty($data['self_id']) ) {
                $this->_manual_supplies_message_send($relationEntity);
            }
        }
    
        //IPSC-2614
        $pc_listener->setOption('disabled', false);
        //--
        
        //ISPC-2614 Ancuta :: Hack to re-trigger listner -
        $entity->supplier_comment = $data['supplier_comment'].' ';
        $entity->save();
        //-- 
        
        return $entity;
    
    }
	
	
}

?>