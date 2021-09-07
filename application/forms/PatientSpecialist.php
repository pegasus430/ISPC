<?php
/**
 * this is the 'same' form as /application/forms/Specialists.php
 * 
 * @author claudiu 
 * 11 2017
 *
 */
class Application_Form_PatientSpecialist extends Pms_Form
{
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('specialist_type'), "cols"=>array("master"=>"medical_speciality")),
            array("label"=>$this->translate('practice_phone'), "cols"=>array("master" => "phone_practice")),
            array("label"=>$this->translate('phone_private'), "cols"=>array("master" => "phone_private")),
    
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("nice_name")),
            array(array("master"=>"street1")),
            array(array("master"=>"zip"), array("master"=>"city")),
        );
    }

	/**
	 * PatientSpecialist formular
	 * @claudiu 27.11.2017
	 * @param array $options, optional values to populate the form
	 * @return Zend_Form_SubForm
	 */
	public function create_form_specialist($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__, "save_form_specialist");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('specialist'));
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
	    
	    if ( ! isset($options['master'])) {
	        $options['master'] = $options;
	    }
	    
	    
	    /* start with the hidden fields */
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	
	        ),
	    ));
	    //TODO: add column self_id in Specialist table.. so you know what-id you selected from the dropdown
	    $subform->addElement('hidden', 'self_id', array(
	        'value'        => isset($options['self_id']) ? $options['self_id'] : $options['master']['id'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    $subform->addElement('hidden', 'sp_id', array(
	        'value'        => isset($options['sp_id']) ? $options['master']['id'] : $options['master']['id'] ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    $subform->addElement('hidden', 'street2', array(
	        'value'        => $options['master']['street2'] ? $options['master']['street2'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    $subform->addElement('hidden', 'salutation_letter', array(
	        'value'        => $options['master']['salutation_letter'] ? $options['master']['salutation_letter'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    $subform->addElement('hidden', 'title_letter', array(
	        'value'        => $options['master']['title_letter'] ? $options['master']['title_letter'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	            
	    ));
	    $subform->addElement('hidden', 'kv_no', array(
	        'value'        => $options['master']['kv_no'] ? $options['master']['kv_no'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    $subform->addElement('hidden', 'valid_from', array(
	        'value'        => $options['master']['valid_from'] ? $options['master']['valid_from'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	          
	    ));
	    $subform->addElement('hidden', 'valid_till', array(
	        'value'        => $options['master']['valid_till'] ? $options['master']['valid_till'] : null ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	             
	        ),
	    ));
	
	    
	    /* continue wityh visible fields */
	    $specialists_types_array = array();
	    $m_specialists_types = new SpecialistsTypes();
	    $sp_types  = $m_specialists_types->get_specialists_types($this->logininfo->clientid);
        foreach($sp_types as $row)
        {
            $specialists_types_array[$row['id']] = $row['name'];
        }
        
        //$specialists_types_array = $this->getSpecialistsTypesArray();
        
	    $subform->addElement('select', 'medical_speciality', array(
	        'value'        => isset($options['medical_speciality']) ? $options['medical_speciality'] : $options['master']['medical_speciality'],
	        'label'        => $this->translate('specialist_type'),
	        'multiOptions' => $specialists_types_array,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty', 'Int'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	    ));
	    $subform->addElement('text', 'practice', array(
	        'value'        => $options['master']['practice'] ,
	        'label'        => $this->translate('practice'),
	        'data-livesearch' => "Specialist",
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
	    $subform->addElement('text', 'title', array(
	        'value'        => $options['master']['title'] ,
	        'label'        => $this->translate('title'),
	        'data-livesearch' => "Specialists",
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
		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['master']['first_name'] ,
            'label'        => $this->translate('firstname'),
            'required'     => false,           //ISPC-2490 Lore 26.02.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
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
	        'value'        => $options['master']['last_name'] ,
	        'label'        => $this->translate('lastname'),
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
	    ));
	    $subform->addElement('text', 'salutation', array(
	        'value'        => $options['master']['salutation'] ,
	        'label'        => $this->translate('salutation'),
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
	        'value'        => $options['master']['street1'] ,
	        'label'        => $this->translate('address'),
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
	        'value'        => $options['master']['zip'] ,
	        'label'        => $this->translate('zip'),
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
	        'value'        => $options['master']['city'],
	        'label'        => $this->translate('city'),
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
	    $subform->addElement('text', 'phone_practice', array(
	        'value'        => $options['master']['phone_practice'],
	        'label'        => $this->translate('practice_phone'),
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
	    $subform->addElement('text', 'phone_private', array(
	        'value'        => $options['master']['phone_private'],
	        'label'        => $this->translate('phone_private'),
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
	    $subform->addElement('text', 'phone_cell', array(
	        'value'        => $options['master']['phone_cell'],
	        'label'        => $this->translate('phone2'),
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
	        'value'        => $options['master']['fax'],
	        'label'        => $this->translate('fax'),
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
	        'value'        => $options['master']['email'],
	        'label'        => $this->translate('email'),
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
	    $subform->addElement('text', 'doctornumber', array(
	        'value'        => $options['master']['doctornumber'],
	        'label'        => $this->translate('LANR'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    $subform->addElement('textarea', 'comment', array(
	        'value'        => ! empty($options['comment']) ?  $options['comment'] : $options['master']['comments'],
	        'label'        => $this->translate('comment'),
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
	        'cols'         => 60,
	    ));
	
	
	    return $subform;
	
	}
	
	

	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param number $indrop 0 = in the liveSearch, 1 = not
	 * @return void|Doctrine_Record
	 */
	public function save_form_specialist($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientSpecialists';
	    $relationModel  = 'Specialists';
	
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
                $this->_manual_specialist_message_send($relationEntity);
            }
	    }
	    
	    
	    //IPSC-2614
	    $pc_listener->setOption('disabled', false);
	    //--
	    //!!!!!!!!!!!!! TYPEEE - for 
	    //ISPC-2614 Ancuta :: Hack to re-trigger listner -
	    $entity->comment = $data['comment'].' ';
	    $entity->save();
	    //-- 
	
	    return $entity;
	
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
	
	
	public function getSpecialistsTypesArray()
	{
	    $specialists_types_array = array();
	    
	    $m_specialists_types = new SpecialistsTypes();
	    $sp_types  = $m_specialists_types->get_specialists_types($this->logininfo->clientid);
	    foreach($sp_types as $row)
	    {
	        $specialists_types_array[$row['id']] = $row['name'];
	    }
	    
	    return $specialists_types_array;
	}
	
	
}
?>