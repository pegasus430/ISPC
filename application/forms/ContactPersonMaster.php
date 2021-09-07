<?php
require_once("Pms/Form.php");
class Application_Form_ContactPersonMaster extends Pms_Form
{
    /*
     * this var only a string
     */
    protected $_model = "ContactPersonMaster";
    
    
    public function getVersorgerExtract() {
        return array(
    
            array( "label" => null, "cols" => array("nice_name")),
            
            array( "label" => $this->translate('name'), "cols" => array("nice_name")),
            array( "label" => $this->translate('phone'), "cols" => array("cnt_phone")),
            array( "label" => $this->translate('mobile'), "cols" => array("cnt_mobile")),
            array( "label" => $this->translate('hatversorgungsvollmacht'), "cols" => array("cnt_hatversorgungsvollmacht")),
            array( "label" => $this->translate('cnt_legalguardian'), "cols" => array("cnt_legalguardian")),
            array( "label" => $this->translate('patientrelationship'), "cols" => array("cnt_familydegree_id")),
            array( "label" => $this->translate('comment'), "cols" => array("cnt_comment")),
            
        );
    }
    
    
    
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("nice_name")),
            array(array("cnt_street1")),
            array(array("cnt_zip"), array("cnt_city")),
        );
    }
    
    
    protected $_block_name_allowed_inputs =  array(

        "WlAssessment" => [
            'create_form_contact_person' => [      
                //this are removed
                '__removed' => [
                    //this 4 have been introduced just for MamboAssessment
                    'cnt_residence_determination',
                    'cnt_representation_authorities',
                    'cnt_asset_custody',
                    'cnt_receipt_post',
                ],
                //only this are allowed
                '__allowed' => [], 
            ] 
        ],
        
        "PatientDetails" => [
            'create_form_contact_person' => [
                //this are removed
                '__removed' => [
                    //this 4 have been introduced just for MamboAssessment
                    'cnt_residence_determination',
                    'cnt_representation_authorities',
                    'cnt_asset_custody',
                    'cnt_receipt_post',
                ],
                //only this are allowed
                '__allowed' => [],
            ]
        ],
        
        
        "MamboAssessment" => [
            'create_form_contact_person' => [
                //this are removed
                '__removed' => [
                    'cnt_hatversorgungsvollmacht',
                    'notify_funeral',
                    'quality_control'
                ],
                //only this are allowed
                '__allowed' => [
                ],
            ],
        ],
    );
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_contact_person_all' => [
                "todo",
                "feedback", 
//                 "benefit_plan",
//     	        "heart_monitoring",         
//     	        "referral_to",
//     	        "further_assessment",       
//     	        "training_nutrition",      
//     	        "training_adherence",      
//     	        "training_device",      
//     	        "training_prevention",      
//     	        "training_incontinence",    
//     	        "organization_careaids",   
//     	        "inclusion_COPD",
//     	        "inclusion_measures",       
            ],
        ],
    ]; 
    

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();
// 		if (!$val->isstring($post['cnt_phone']))
// 		{
// 			$this->error_message['cnt_phone'] = $Tr->translate('phone_error');
// 			$error = 9;
// 		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function InsertData ( $post )
	{

        
		for ($i = 0; $i < count($post['cnts']); $i++)
		{
			$a_date = explode(".", $post['cnts'][$i]['cnt_birthd']);
        
	    // Maria:: Migration ISPC to CISPC 08.08.2020
			/* //ISPC-2590 Andrei 22.05.2020 allow more than one legal guardian for same patient
			if ($post['cnts'][$i]['cnt_legalguardian'] == 1)
			{
				$pt = Doctrine_Query::create()
				->select('*')
				->from('ContactPersonMaster')
				->where("ipid='" . $post['ipid'] . "'");
				$ptexec = $pt->execute();

				if ($ptexec)
				{
					$contactarr = $ptexec->toArray();
					foreach ($contactarr as $key => $val)
					{

						if ($val['cnt_legalguardian'] == 1)
						{
							$cust = Doctrine::getTable('ContactPersonMaster')->find($val['id']);
							$cust->cnt_legalguardian = 0;
							$cust->save();
						}
					}
				}
			}*/


			$cust = new ContactPersonMaster();
			$cust->ipid = $post['ipid'];
			$cust->cnt_first_name = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_first_name']);
			$cust->cnt_middle_name = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_middle_name']);
			$cust->cnt_last_name = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_last_name']);
			$cust->cnt_street1 = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_street1']);
			$cust->cnt_street2 = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_street2']);
			$cust->cnt_zip = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_zip']);
			$cust->cnt_city = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_city']);
			$cust->cnt_title = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_title']);
			$cust->cnt_salutation = Pms_CommonData::aesEncrypt($post['cnt_salutation']);
			$cust->cnt_hatversorgungsvollmacht = $post['cnts'][$i]['cnt_hatversorgungsvollmacht'];
			$cust->cnt_legalguardian = $post['cnts'][$i]['cnt_legalguardian'];

			$cust->notify_funeral = $post['cnts'][$i]['notify_funeral'];
			$cust->quality_control = $post['cnts'][$i]['quality_control'];
				
			$cust->cnt_phone = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_phone']);
			$cust->cnt_mobile = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_mobile']);
			$cust->cnt_email = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_email']);
			$cust->cnt_birthd = $a_date[2] . "-" . $a_date[1] . "-" . $a_date[0];
			$cust->cnt_sex = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_sex']);
			$cust->cnt_nation = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_nation']);
			$cust->cnt_familydegree_id = $post['cnts'][$i]['cnt_familydegree_id'];
			$cust->cnt_custody = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_custody']);
			$cust->cnt_comment = Pms_CommonData::aesEncrypt($post['cnts'][$i]['cnt_comment']);

			// TODO-1303
			$cust->is_contact = isset($post['cnts'][$i]['cnt_kontactnumber']) ? (int)$post['cnts'][$i]['cnt_kontactnumber']  : (int)$post['is_contact'] ;
			//$cust->is_contact = (int)$post['is_contact'];
			
			$cust->save();

			$cust1 = Doctrine::getTable('ContactPersonTempMaster')->find($post['cnts'][$i]['id']);
			if($cust1){
				$cust1->delete();
			}
		}
	}

	public function UpdateData ( $post )
	{
		//print_r($post); exit;
		$a_date = explode(".", $post['cnt_birthd']);

		$cust = Doctrine::getTable('ContactPersonMaster')->find($_GET['cid']);
		if ($cust) {
			$cust->cnt_first_name = Pms_CommonData::aesEncrypt($post['cnt_first_name']);
			$cust->cnt_middle_name = Pms_CommonData::aesEncrypt($post['cnt_middle_name']);
			$cust->cnt_last_name = Pms_CommonData::aesEncrypt($post['cnt_last_name']);
			$cust->cnt_street1 = Pms_CommonData::aesEncrypt($post['cnt_street1']);
			$cust->cnt_street2 = Pms_CommonData::aesEncrypt($post['cnt_street2']);
			$cust->cnt_zip = Pms_CommonData::aesEncrypt($post['cnt_zip']);
			$cust->cnt_city = Pms_CommonData::aesEncrypt($post['cnt_city']);
			$cust->cnt_title = Pms_CommonData::aesEncrypt($post['cnt_title']);
			$cust->cnt_salutation = Pms_CommonData::aesEncrypt($post['cnt_salutation']);
			$cust->cnt_phone = Pms_CommonData::aesEncrypt($post['cnt_phone']);
			$cust->cnt_hatversorgungsvollmacht = $post['cnt_hatversorgungsvollmacht'];
			$cust->cnt_legalguardian = $post['cnt_legalguardian'];
			$cust->notify_funeral = $post['notify_funeral'];
			$cust->quality_control = $post['quality_control'];
			$cust->cnt_mobile = Pms_CommonData::aesEncrypt($post['cnt_mobile']);
			$cust->cnt_email = Pms_CommonData::aesEncrypt($post['cnt_email']);
			$cust->cnt_birthd = $a_date[2] . "-" . $a_date[1] . "-" . $a_date[0];
			$cust->cnt_sex = Pms_CommonData::aesEncrypt($post['cnt_sex']);
			$cust->cnt_comment = Pms_CommonData::aesEncrypt($post['cnt_comment']);
			$cust->cnt_familydegree_id = $post['cnt_familydegree_id'];
			$cust->cnt_custody = Pms_CommonData::aesEncrypt($post['cnt_custody']);
			$cust->cnt_nation = Pms_CommonData::aesEncrypt($post['cnt_nation']);
			
			$cust->is_contact = (int)$post['is_contact'];				
			
			$cust->save();
		}
	}

	public function InsertDataSingle ( $post, $return = false )
	{
		
	    // Maria:: Migration ISPC to CISPC 08.08.2020
		/* ISPC-2590 Andrei 22.05.2020 allow more than one legal guardian for same patient
		if ($post['cnt_legalguardian'] == 1)
		{
			$pt = Doctrine_Query::create()
			->select('*')
			->from('ContactPersonMaster')
			->where("ipid='" . $post['ipid'] . "'");
			$ptexec = $pt->execute();
			if ($ptexec)
			{
				$contactarr = $ptexec->toArray();
				foreach ($contactarr as $key => $val)
				{
					if ($val['cnt_legalguardian'] == 1)
					{
						$cust = Doctrine::getTable('ContactPersonMaster')->find($val['id']);
						$cust->cnt_legalguardian = 0;
						$cust->save();
					}
				}
			}
		}*/

		if ($post['notify_funeral'] == 1)
		{
			$pt = Doctrine_Query::create()
			->select('*')
			->from('ContactPersonMaster')
			->where("ipid='" . $post['ipid'] . "'");
			$ptexec = $pt->execute();
			if ($ptexec)
			{
				$contactarr = $ptexec->toArray();
				foreach ($contactarr as $key => $val)
				{
					if ($val['notify_funeral'] == 1)
					{
						$cust = Doctrine::getTable('ContactPersonMaster')->find($val['id']);
						$cust->notify_funeral = 0;
						$cust->save();
					}
				}
			}
		}

		if ($post['quality_control'] == 1)
		{
			$pt = Doctrine_Query::create()
			->select('*')
			->from('ContactPersonMaster')
			->where("ipid='" . $post['ipid'] . "'");
			$ptexec = $pt->execute();
			if ($ptexec)
			{
				$contactarr = $ptexec->toArray();
				foreach ($contactarr as $key => $val)
				{
					if ($val['quality_control'] == 1)
					{
						$cust = Doctrine::getTable('ContactPersonMaster')->find($val['id']);
						$cust->quality_control = 0;
						$cust->save();
					}
				}
			}
		}


		$cust = new ContactPersonMaster();
		$cust->ipid = $post['ipid'];
		$cust->cnt_first_name = Pms_CommonData::aesEncrypt($post['cnt_first_name']);
		$cust->cnt_middle_name = Pms_CommonData::aesEncrypt($post['cnt_middle_name']);
		$cust->cnt_last_name = Pms_CommonData::aesEncrypt($post['cnt_last_name']);
		$cust->cnt_street1 = Pms_CommonData::aesEncrypt($post['cnt_street1']);
		$cust->cnt_street2 = Pms_CommonData::aesEncrypt($post['cnt_street2']);
		$cust->cnt_zip = Pms_CommonData::aesEncrypt($post['cnt_zip']);
		$cust->cnt_city = Pms_CommonData::aesEncrypt($post['cnt_city']);
		$cust->cnt_title = Pms_CommonData::aesEncrypt($post['cnt_title']);
		$cust->cnt_phone = Pms_CommonData::aesEncrypt($post['cnt_phone']);
		$cust->cnt_mobile = Pms_CommonData::aesEncrypt($post['cnt_mobile']);
		$cust->cnt_email = Pms_CommonData::aesEncrypt($post['cnt_email']);
		$cust->cnt_hatversorgungsvollmacht = $post['cnt_hatversorgungsvollmacht'];
		$cust->cnt_legalguardian = $post['cnt_legalguardian'];
		$cust->notify_funeral = $post['notify_funeral'];
		$cust->quality_control = $post['quality_control'];
		$cust->cnt_birthd = $a_date[2] . "-" . $a_date[1] . "-" . $a_date[0];
		$cust->cnt_sex = Pms_CommonData::aesEncrypt($post['cnt_sex']);
		$cust->cnt_nation = Pms_CommonData::aesEncrypt($post['cnt_nation']);
		$cust->cnt_custody = Pms_CommonData::aesEncrypt($post['cnt_custody']);
		$cust->cnt_familydegree_id = $post['cnt_familydegree_id'];
		$cust->cnt_comment = Pms_CommonData::aesEncrypt($post['cnt_comment']);
		
		$cust->is_contact = (int)$post['is_contact'];
		
		$cust->save();

		if($return)
		{
			$ret_data['id'] = $cust->id;
			$ret_data['cnt_first_name'] = $post['cnt_first_name'];
			$ret_data['cnt_last_name'] = $post['cnt_last_name'];

			return $ret_data;
		}
	}

	
	
	

	/**
	 * ContactPerson formular
	 * @claudiu 23.11.2017
	 * @param array $options, optional values to populate the form
	 * @return Zend_Form_SubForm
	 */
	public function create_form_contact_person($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_contact_person");
	    
	    //@todo $subform or $this? this is the question
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('contactperson'));
	    $subform->setAttrib("class", "label_same_size  {$__fnName}"); //has_feedback_options
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);

		// Maria:: Migration ISPC to CISPC 08.08.2020
	    ////TODO-3187 Ancuta 05.06.2020 - re-add module created in ISPC-1539
	    $modules = new Modules();
	    $show_custody = 0;
	    if($modules->checkModulePrivileges("102", $this->logininfo->clientid))
	    {
    	    $show_custody = 1;
	    }
	    
	    
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	
	        ),
	    ));
	    
	    $subform->addElement('text', 'cnt_first_name', array(
	        'value'        => $options['cnt_first_name'] ,
	        'label'        => $this->translate('firstname'),
	        //'placeholder' => 'Search my date',
	        //'data-livesearch' => "ContactPerson",
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	    ));
	    $subform->addElement('text', 'cnt_last_name', array(
	        'value'        => $options['cnt_last_name'] ,
	        'label'        => $this->translate('lastname'),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    

	    //this NEXT is JUST example
	    /*
	    $script = <<<EOT
<script>
    function prefill_contact_person(_this) {
        $(_this).parents('table').find('input[name$=\[cnt_street1\]]').val("{$this->_patientMasterData['street1']}");
        $( "#cnt_zip" ).val("{$this->_patientMasterData['zip']}");
        $( "#cnt_city" ).val("{$this->_patientMasterData['city']}");
        return false;
    }
</script>
EOT;
	    
	    $subform->addElement('note', 'javascript', array(
	        'value'        => '',//$script,
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'escape' => false,
	    ));
	    */
	    
	    $subform->addElement('text', 'cnt_street1', array(
	        'value'        => $options['cnt_street1'] ,
	        'label'        => '<button type="button" onclick=\'$("input[name$=\"\[cnt_street1\]\"]", $(this).parents("table")).val("' . $this->_patientMasterData['street1'] . '");$("input[name$=\"\[cnt_zip\]\"]", $(this).parents("table")).val("' . $this->_patientMasterData['zip'] . '");$("input[name$=\"\[cnt_city\]\"]", $(this).parents("table")).val("' . $this->_patientMasterData['city'] . '");\' class="prefill_contact_person_btn dontPrint" title="'. $this->translate('prefill_with_contact_person'). '"></button>'
	                           . $this->translate('street'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td' , 'escape' => false)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('text', 'cnt_zip', array(
	        'value'        => $options['cnt_zip'],
	        'label'        => $this->translate('zip'),
	        'data-livesearch'  => 'zip',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));

	    $subform->addElement('text', 'cnt_city', array(
	        'value'        => $options['cnt_city'],
	        'label'        => $this->translate('city'),
	        'data-livesearch'   => 'city',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
 
	    $subform->addElement('text', 'cnt_phone', array(
	        'value'        => $options['cnt_phone'],
	        'label'        => $this->translate('phone1'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
 
	    $subform->addElement('text', 'cnt_mobile', array(
	        'value'        => $options['cnt_mobile'],
	        'label'        => $this->translate('phone2'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
 
	    $subform->addElement('text', 'cnt_email', array(
	        'value'        => $options['cnt_email'],
	        'label'        => $this->translate('cnt_email'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty', 'EmailAddress'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'cnt_hatversorgungsvollmacht', array(
	        'value'        => $options['cnt_hatversorgungsvollmacht'],
	        'label'        => $this->translate('hatversorgungsvollmacht'),
	        'multiOptions' => array( 1 => ''),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'cnt_legalguardian', array(
	        'value'        => $options['cnt_legalguardian'],
	        'label'        => $this->translate('Legal guardian'),
	        'multiOptions' => array( 1 => ''),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'is_contact', array(
	        'value'        => $options['is_contact'],
	        'label'        => $this->translate('is the contact phone number'),
	        'multiOptions' => array( 1 => ''),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('select', 'notify_funeral', array(
	        'value'        => $options['notify_funeral'],
	        'label'        => $this->translate('notify_funeral'),
	        'multiOptions' => array( '' => '–' , 1 => 'yes_radio', 0 => 'no_radio'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'quality_control', array(
	        'value'        => $options['quality_control'],
	        'label'        => $this->translate('quality_control'),
	        'multiOptions' => array( 1 => ''),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $getFamilyDegrees_values = $this->getFamilyDegreeArray();
	    
	    $subform->addElement('select', 'cnt_familydegree_id', array(
	        'value'        => $options['cnt_familydegree_id'],
	        'label'        => $this->translate('patientrelationship'),
	        'multiOptions' => $getFamilyDegrees_values,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));	    
	    
	    
	    if($show_custody == 1){//TODO-3187 Ancuta 05.06.2020 - re-add module created in ISPC-1539
    	    $subform->addElement('checkbox', 'cnt_custody_val', array(
    	        'checkedValue'    => '1',
    	        'uncheckedValue'  => '0',
    	        'label'      => 'cnt_custody_val',
    	        'required'   => false,
    	        'value' => $options['cnt_custody_val'],
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>3)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	        'belongsTo' => 'PatientMoreInfo',
    	    ));
    	    
    	    /*
    	     * this was changed into a select
    	     $subform->addElement('textarea', 'cnt_custody', array(
    	     'value'        => $options['cnt_custody'],
    	     'label'        => 'cnt_custody',
    	     'required'     => false,
    	     'filters'      => array('StringTrim'),
    	     'validators'   => array('NotEmpty'),
    	     'decorators'   => array(
    	     'ViewHelper',
    	     array('Errors'),
    	     array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
    	     array('Label', array('tag' => 'td')),
    	     array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	     ),
    	     'rows'         => 3,
    	     'cols'         => 60,
    	     ));
    	     */
        }
	    //Aufenthaltsbestimmungsrecht = Residence determination
	    $subform->addElement('checkbox', 'cnt_residence_determination', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'cnt_residence_determination',
	        'required'   => false,
	        'value' => $options['cnt_residence_determination'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    //Vertretung vor Behörden und Ämtern = Representation before authorities and offices
	    $subform->addElement('checkbox', 'cnt_representation_authorities', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'cnt_representation_authorities',
	        'required'   => false,
	        'value' => $options['cnt_representation_authorities'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    //Vermögenssorge; - med. Gesundheitsfürsorge = Asset custody; - med. health care
	    $subform->addElement('checkbox', 'cnt_asset_custody', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'cnt_asset_custody',
	        'required'   => false,
	        'value' => $options['cnt_asset_custody'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    //Entgegennahme der Post = Receipt of the post
	    $subform->addElement('checkbox', 'cnt_receipt_post', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'cnt_receipt_post',
	        'required'   => false,
	        'value' => $options['cnt_receipt_post'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    
	    
	    
	    
	    
	     
	    
	    
	    	  	    
	    if($show_custody == 1){//TODO-3187 Ancuta 05.06.2020 - re-add module created in ISPC-1539
    	    $subform->addElement('select', 'cnt_custody', array(
    	        'value'        => $options['cnt_custody'],
    	        'label'        => 'cnt_custody',
    	        'multiOptions' => $this->getCntCustodyArray(),
    	        'required'     => false,
    	        'filters'      => array('StringTrim'),
    	        'validators'   => array('NotEmpty'),
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
	    }
	   
	    $subform->addElement('textarea', 'cnt_comment', array(
	        'value'        => $options['cnt_comment'],
	        'label'        => $this->translate('comment'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'rows'         => 3,
	        'cols'         => 60,
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
	

	public function create_form_contact_person_all($options =  array() , $elementsBelongTo = null) 
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
// 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_contact_person");
	    
    	$subform = new Zend_Form_SubForm();
    	$subform->removeDecorator('DtDdWrapper');
    	$subform->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'contact_person_accordion accordion_c'));
    	$subform->setLegend($this->translate('contactperson'));
    	$subform->setAttrib("class", "label_same_size {$__fnName}");
    	 
    
    	if ( ! empty($options) && is_array($options))
    	{
    	    $cp_counter = 0;
    	    
    	    foreach($options as $cp) {
    	
    	        $cp_arr = array($cp);
    	        ContactPersonMaster::beautifyName($cp_arr);
    	        $cp = $cp_arr[0];
    	
    	        $one_contact_person_form = $this->create_form_contact_person($cp);
    	        $one_contact_person_form->setLegend($one_contact_person_form->getLegend() . ' : ' .$cp['nice_name']);
    	        
    	        $subform->addSubForm($one_contact_person_form, $cp_counter);
    	        
    	        $cp_counter++;
    	    }
    	} else {
    	    $this->create_form_contact_person();//just so we have the mapping for save
    	}
    	 
    	
    	 
    	 
    	
    	//add button to add new contacts
    	$subform->addElement('button', 'addnew_contactperson', array(
    	    'onClick'      => "Contact_Person_addnew(this, 'Contact_Persons'); return false;",
    	    'value'        => '1',
    	    'label'        => $this->translate('Add new contact person'),
    	    'decorators'   => array(
    	        'ViewHelper',
    	        'FormElements',
    	        array('HtmlTag', array('tag' => 'div')),
    	    ),
    	    'class'        =>'button btnSubmit2018 plus_icon_bg dontPrint',
    	));
    	
	
    	return $this->filter_by_block_name($subform, $__fnName);
    	
	}
	
	
	
	public function save_form_contact_person($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    
	    if ($data['notify_funeral'] == '') {
	        $data['notify_funeral'] =  null;
	    }
	    
	    $entity = new ContactPersonMaster();
	    
	    $resultObj = $entity->findOrCreateOneByIpidAndId( $ipid, $data['id'], $data);
	    
	    $getLastModified = $resultObj->getLastModified();
	    
	    //ISPC-2252
	    if (isset($getLastModified['cnt_hatversorgungsvollmacht'])) {
	        
	        $division_tab = 'healthcare_proxy';
	        
	        $pacp_obj = new PatientAcp();
	        
	        if ($resultObj->cnt_hatversorgungsvollmacht == 1) {
	            /* //ISPC-2590 allow more than one attorney for same patient
	            //disable all other
	            Doctrine_Query::create()
	            ->update('ContactPersonMaster')
	            ->set('cnt_hatversorgungsvollmacht', 0)
	            ->where("ipid = ? ", $ipid)
	            ->andWhere('id != ?', $resultObj->id)
	            ->execute();*/
                //ISPC-2565,Elena,26.02.2021
	            
    	        $resultAcp = $pacp_obj->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab, $resultObj->id, [
    	            //'contactperson_master_id' => $resultObj->id,//ISPC-2565,Elena,26.02.2021
    	            'active' => 'yes'
    	        ]);
    	        
	        } else {
	            //set  'active' => 'no' and contactperson_master_id = nhull .. if this was set
                //ISPC-2565,Elena,26.02.2021
	            $resultAcp = $pacp_obj->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab, $resultObj->id, [
	               // 'contactperson_master_id' => $resultObj->id,
	                'active' => 'yes'
	            ]);
	            if ($resultAcp && ($resultAcp->contactperson_master_id == $resultObj->id)) {
                    //@todo i don't understand why we need to make a "dead" record, i find much better to reuse a same record - Elena //ISPC-2565,Elena,26.02.2021
	                //$resultAcp->contactperson_master_id = null;//ISPC-2565,Elena,26.02.2021
	                $resultAcp->active = 'no';
	                $resultAcp->save();
	                
	            }
	        }	        
	    }
	    
	    if (isset($getLastModified['cnt_legalguardian'])) {
	         
	        $division_tab = 'care_orders';
	         
	        $pacp_obj = new PatientAcp();
	         
	        if ($resultObj->cnt_legalguardian == 1) {
	            /* //ISPC-2590 Andrei 22.05.2020 allow more than one legal guardian for same patient
	            //disable all other
	            Doctrine_Query::create()
	            ->update('ContactPersonMaster')
	            ->set('cnt_legalguardian', 0)
	            ->where("ipid = ? ", $ipid)
	            ->andWhere('id != ?', $resultObj->id)
	            ->execute();*/
                //ISPC-2565,Elena,26.02.2021
	            $result = $pacp_obj->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab, $resultObj->id, [//ISPC-2565,Elena,26.02.2021
	                //'contactperson_master_id' => $resultObj->id,
	                'active' => 'yes'
	            ]);

	        } else {
	            //set  'active' => 'no' and contactperson_master_id = nhull .. if this was set
	            $resultAcp = $pacp_obj->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab, [//ISPC-2565,Elena,26.02.2021
	                //'contactperson_master_id' => $resultObj->id,
	                'active' => 'yes'
	            ]);
	            if ($resultAcp && ($resultAcp->contactperson_master_id == $resultObj->id)) {
	               // $resultAcp->contactperson_master_id = null;//ISPC-2565,Elena,26.02.2021
	                $resultAcp->active = 'no';
	                $resultAcp->save();
	                
	            }
	        }
	    }
	    
	    
	    return $resultObj;    
	}
	
	
	public function delete_form_contact_person($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    if ($entity = Doctrine_Core::getTable('ContactPersonMaster')->findOneByIdAndIpid($data['id'], $ipid)) {
	    
	        $entity->delete();
	        
	        //delete associated acp
	        if ($patient_acps = Doctrine_Core::getTable('PatientAcp')->findByIpid($ipid)) {
	            
	            foreach($patient_acps->getIterator() as $row) {
	                
	                if (in_array($row->division_tab, ['healthcare_proxy', 'care_orders']) 
	                    && $row->contactperson_master_id == $data['id']) 
	                {
	                    $row->contactperson_master_id = null;
	                    $row->active = 'no';
	                }
	            }
	            
	            $patient_acps ->save();
	        }
	    }
	    
	    return true;
	}
	
	public function getCntHatversorgungsvollmachtArray() 
	{
	    return [
	        0 => '',                   //TODO-3231 Lore 22.06.2020    $this->translate('no_radio'),
	        1 => $this->translate('yes_radio'),
	    ];
	}
	
	public function getCntLegalguardianArray() 
	{
	    return [
	        0 => '',                   //TODO-3231 Lore 22.06.2020    $this->translate('no_radio'),
	        1 => $this->translate('yes_radio'),
	    ];
	}
	
	public function getCntCustodyArray() 
	{
	    //this is a `fake` select .. cause we save plain text in db
	    return [
	        '' => 'please select',
	        'Mutter und Vater gemeinsam' => $this->translate('Mutter und Vater gemeinsam'),
	        'Mutter alleine' => $this->translate('Mutter alleine'),
	        'Vater alleine' => $this->translate('Vater alleine'),
	        'sonst. Person' => $this->translate('sonst. Person'),
	    ];
	}
		
	
	public function getFamilyDegreeArray()
	{
    	$fd = new FamilyDegree();
    	$getFamilyDegrees_values = array();
    	
    	//ISPC-2612 Ancuta 30.06.2020
    	$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('FamilyDegree',$this->logininfo->clientid);
    	if($client_is_follower){
    	    if($_REQUEST['id'])
    	    {
    	        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
    	        $logininfo = new Zend_Session_Namespace('Login_Info');
    	        $ipid = Pms_CommonData::getIpid($decid);
    	    }
    	    $rows = $fd->getFamilyDegrees(0,$ipid);
    	}
    	else
    	{
        	$rows =$fd->getTable()->findBy('clientid', $this->logininfo->clientid)->toArray();
    	}
    	
    	if ( ! empty($rows))
        	foreach ($rows as $row) {
        	    if($row['isdelete'] == 0){ // TODO-2262 - FamilyDegree  does not heve soft delete - isdelete=1  must be manualy removed 
            	    $getFamilyDegrees_values[$row['id']] = $row['family_degree'];
        	    }
        	}
        	
    	uasort($getFamilyDegrees_values, array(new Pms_Sorter(), "_strnatcmp"));
    	
    	$getFamilyDegrees_values = array( ''=> $this->translate('pleaseselect')) + $getFamilyDegrees_values;
    	
    	return $getFamilyDegrees_values;
	}
	
}
?>