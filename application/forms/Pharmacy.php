<?php

require_once("Pms/Form.php");

class Application_Form_Pharmacy extends Pms_Form 
{

    protected $_model = 'Pharmacy';
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('pharmacy'), "cols"=>array("Pharmacy"=>"pharmacy")),
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('phone1'), "cols"=>array("Pharmacy" => "phone")),
            array("label"=>$this->translate('fax'), "cols"=>array("Pharmacy" => "fax")),
        );
    }
    
    
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("Pharmacy"=>"pharmacy")),
            array(array("nice_name")),
            array(array("Pharmacy"=>"street1")),
            array(array("Pharmacy"=>"zip"), array("Pharmacy"=>"city")),
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

			$fdoc = new Pharmacy();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->pharmacy = $post['pharmacy'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
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

			if(!empty($_GET['pharmacy_id']) && $_GET['pharmacy_id'] == $post['hidd_pharmacyid'])
			{
				$fdoc = Doctrine::getTable('Pharmacy')->findOneByIdAndIndrop($post['hidd_pharmacyid'], "1");
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
				$fdoc = new Pharmacy();
				$fdoc->pharmacy = $post['pharmacy'];
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
				
				if (empty($post['hidd_pharmacyid'])) {
				    $this->_manual_pharmacy_message_send($fdoc);
				}
				
			}
			return $fdoc;
		}

		public function UpdateData($post)
		{

			$fdoc = Doctrine::getTable('Pharmacy')->find($post['did']);
			$fdoc->pharmacy = $post['pharmacy'];
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
			$fdoc->comments = $post['comments'];
			$fdoc->save();
		}

		

	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 *
	 * @param Pharmacy $fdoc
	 * @return void
	 */
	private function _manual_pharmacy_message_send(Pharmacy $fdoc)
	{
	
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }

	    $doctor_first_last_name = $fdoc->pharmacy . ',  ' . $fdoc->first_name . " " . $fdoc->last_name;
	
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


        $message_title = $this->translate("New pharmacy was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = $this->translate('New pharmacy %s was manualy added, please take action on %s', $doctor_first_last_name, $patientLink);
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
        $message_body = $this->translate('New pharmacy %s was manualy added, please take action on %s', $doctor_first_last_name, $patientMasterData['epid']);
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
	 * .. this should have been in a form PatientPharmacy
	 * @cla on 26.06.2018
	 *
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_pharmacy($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName, "save_form_patient_pharmacy");
	    
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("pharmacy");
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
// 	    if (!empty($options)) dd($options);
	
	    if ( ! isset($options['Pharmacy'])) {
	        $options['Pharmacy'] = $options;
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
            'value'        => isset($options['self_id']) ? $options['pharmacy_id'] : $options['pharmacy_id'] ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'pharmacy_id', array(
            'value'        => $options['pharmacy_id'] ? $options['pharmacy_id'] : -1 ,
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
        $subform->addElement('text', 'pharmacy', array(
            'value'        => $options['Pharmacy']['pharmacy'] ,
            'label'        => 'pharmacy',
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
            'data-livesearch'  => 'Pharmacy',
        ));

		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Pharmacy']['first_name'] ,
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
            'value'        => $options['Pharmacy']['last_name'] ,
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
            'value'        => $options['Pharmacy']['salutation'] ,
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
            'value'        => $options['Pharmacy']['street1'] ,
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
            'value'        => $options['Pharmacy']['zip'] ,
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
            'value'        => $options['Pharmacy']['city'],
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
            'value'        => $options['Pharmacy']['phone'],
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
            'value'        => $options['Pharmacy']['fax'],
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
            'value'        => $options['Pharmacy']['email'],
            'label'        => 'email',
            'required'   => false,
            'filters'    => array('StringTrim'),
	        'validators' => array('EmailAddress'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

         
        $subform->addElement('textarea', 'pharmacy_comment', array(
            'value'        => ! empty($options['pharmacy_comment']) ?  $options['pharmacy_comment'] : $options['Pharmacy']['comments'],
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


        //ispc-2291
        if ($this->_patientMasterData['ModulePrivileges'][182]) {
            

//             "Pharmacy delivers from" radio "YES" NO "
//      "Delivery rhythm" - like in order management "once a week"
//      "Pharmacy produces infusion" radio "YES" NO "
//                      Rhythm of preparation - TEXT FIELD
            
            $subform->addElement('radio', 'is_delivering', array(
        
                'value'    => ! empty($options['Pharmacy']['is_delivering']) ? $options['Pharmacy']['is_delivering'] : null,
        
                'multiOptions' => $this->getColumnMapping('is_delivering'),
                'label'      => "Pharmacy delivers from",
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),        
            ));
             
            $subform->addElement('select', 'order_interval', array(
                'value'        => $options['Pharmacy']['order_interval'],
                'multiOptions' => $this->getColumnMapping('order_interval'),
                'label'        => 'Delivery rhythm',
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
                'onChange' => "if (this.value != 'every_x_days' && this.value != 'selected_days_of_the_week') {\$(this).parents('table').find('.selector_order_interval_options').hide();} else {\$(this).parents('table').find('.selector_order_interval_options').show();  if (this.value == 'every_x_days') {\$(this).parents('table').find('.every_x_days').show(); \$(this).parents('table').find('.selected_days_of_the_week').hide(); } else {\$(this).parents('table').find('.every_x_days').hide(); \$(this).parents('table').find('.selected_days_of_the_week').show();}}"
            ));
            
            
            $display_none = $options['Pharmacy']['order_interval'] != 'every_x_days' && $options['Pharmacy']['order_interval'] != 'selected_days_of_the_week' ? "display_none": "";
            $rowinterval = $this->subFormTableRow(['class'=>"selector_order_interval_options {$display_none}"]);
            
            $display_none = $options['Pharmacy']['order_interval'] != 'every_x_days'  ? "display_none": "";
            $rowinterval->addElement('text', 'every_x_days', array(
                'value'        => $options['Pharmacy']['order_interval_options']['every_x_days'],
                'required'     => false,
                'label'         => null,
                'filters'      => array('StringTrim', 'Digits'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array('HtmlTag', array('tag' => 'div' , 'class'=> "selector_order_interval_options every_x_days label_same_size_auto {$display_none}")),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
                    array('Label', array('tag' => 'td')),
                ),

                'data-match' => "[1-7]",
            ));
            
            $display_none = $options['Pharmacy']['order_interval'] != 'selected_days_of_the_week'  ? "display_none": "";
            $rowinterval->addElement('multiCheckbox', 'selected_days_of_the_week', array(
                'multiOptions' => $this->getColumnMapping('selected_days_of_the_week'),
                'value'        => $options['Pharmacy']['order_interval_options']['selected_days_of_the_week'],
                'separator'    => PHP_EOL,
                'required'     => false,
                'label'         => null,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array('HtmlTag', array('tag' => 'div' , 'class'=> "selector_order_interval_options selected_days_of_the_week label_same_size_auto {$display_none}")),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                ),
            ));
            $subform->addSubForm($rowinterval, 'order_interval_options');
            
            
            
            $subform->addElement('radio', 'produces_infusion', array(
                'value'    => ! empty($options['Pharmacy']['produces_infusion']) ? $options['Pharmacy']['produces_infusion'] : null,
            
                'multiOptions' => $this->getColumnMapping('produces_infusion'),
                'label'      => "Pharmacy produces infusion",
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            
            ));
            
            
            $subform->addElement('text', 'rhythm_preparation', array(
                'value'        => $options['Pharmacy']['rhythm_preparation'],
                'label'        => 'Rhythm of preparation',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', )),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        }
        
        return $this->filter_by_block_name($subform, $__fnName);
	
	}
	
	

	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param number $indrop 0 = in the liveSearch, 1 = not
	 * @return void|Doctrine_Record
	 */
	public function save_form_patient_pharmacy($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientPharmacy';
	    $relationModel  = 'Pharmacy';
	
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
                $this->_manual_pharmacy_message_send($relationEntity);
            }
	    }
	    //IPSC-2614
	    $pc_listener->setOption('disabled', false);
	    //--
	    
	    //ISPC-2614 Ancuta :: Hack to re-trigger listner -  
	    $entity->pharmacy_comment = $data['pharmacy_comment'].' ';
	    $entity->save();
	    //-- 
	    
	    return $entity;
	
	}
	
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        'is_delivering' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'produces_infusion' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'order_interval' => [//added like this cause translations
	            ''  => '---' //extra empty value for select
	        ],
	        'selected_days_of_the_week' => [
	            1,2,3,4,5,6,7
	        ]
        
	    ];
	
	
	    $values = Doctrine_Core::getTable($this->_model)->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}

?>