<?php
/**
 * 
 * @author carmen
 * 
 * 22.08.2019
 *
 */
class Application_Form_PatientSurveySettings extends Pms_Form
{
	
    protected $_model = 'PatientSurveySettings';
    
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmPatientSurveySettings";  //define the name if you want to piggyback some triggers
		
	protected $_translate_lang_array = 'PatientSurveySettings_box_lang';
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_PatientSurveySettings( $options = array(), $elementsBelongTo = null)
	{
	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    	  
// 	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	     
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_PatientSurveySettings");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'div' , 'class' => 'acp_accordion accordion_c'));
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('box_title'));
	    $subform->setAttrib("class", "label_same_size inlineEdit {$__fnName}");
		$this->__setElementsBelongTo($subform, $elementsBelongTo);

		if ( ! isset($options['patient_emails_arr'][0])) {
			$options['patient_emails_arr'] = is_array($options['patient_emails_arr']) ? $options['patient_emails_arr'] : [];
			$options['patient_emails_arr'] = array(0 => '') + $options['patient_emails_arr'];
		}		 
		
		$subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	    
	        ),
	    ));
		
		$subform->addElement('radio',  'status', array(
				'value'        => isset($options['status']) && ! empty($options['status']) ? $options['status'] : "disabled",
				'label'        => $this->translate('Status'),
				'required'     => false,
				'multiOptions' => array('disabled' => 'Deaktiviert', 'enabled' => 'Aktiviert'),
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array(
								'tag' => 'tr',
						)),
				),
				'onChange' => "if(this.value == 'enabled') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
		
		));
		 
		$display = $options['status'] == 'enabled' ? '' : 'display:none;';
		if ( ! empty($options['patient_emails_arr']) && count($options['patient_emails_arr']) > 1) {
			$subform->addElement('select',  'receiver', array(
					'value'        => $options['receiver'],
					'label'        => $this->translate('survey_email'),
					'required'     => false,
					'multiOptions' => $options['patient_emails_arr'],
					'filters'      => array('StringTrim'),
					'validators'   => array('Int'),
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array('Label', array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
								
					),
					'style' => 'width: 225px;'
			));
	 
			$subform->addElement('text',  'start_date', array(
					'value'        => $options['start_date'] && $options['start_date']!="0000-00-00" ? date('d.m.Y', strtotime($options['start_date'])) : date('d.m.Y'),
					'label'        => $this->translate('start_date'),
					'required'     => false,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'class'        => 'date',
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array('Label', array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
					),
			));
			
			$subform->addElement('text',  'interval_days', array(
					'value'        => $options['interval_days'],
					'label'        => $this->translate('survey_interval_days'),
					'required'     => false,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array('Label', array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
			
					),
			    'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
			    'pattern'          => "^[0-9,]*( ml)$",
			));
		}
		else
		{
			$subform->addElement('note',  'no_email', array(
					'label'        => null,
					'required'     => false,
					'value'        => $this->translate('no_survey_email'),
					'decorators' => array(
							'ViewHelper',
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'qq_file_uploader_label')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
					),
			));
		}

		return $this->filter_by_block_name($subform, $__fnName);
		
	}
	
	public function save_form_PatientSurveySettings($ipid =  null , $data = array())
	{
	    if (empty($ipid) || ! is_array($data)) {
	        return;
	    }
	    
	    if($data['start_date'] != "")
	    {
	    	$data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
	    }
	    else 
	    {
	    	$data['start_date'] = '0000-00-00 00:00:00';
	    }
	    
	    if($data['receiver'] != "0")
	    {
	    	if(substr($data['receiver'], 0, 1) == 'p')
	    	{
	    		$data['parent_table'] = 'PatientMaster';	    		
	    	}
	    	else
	    	{
	    		$data['parent_table'] = 'ContactPersonMaster';
	    	}
	    	$data['table_id'] = substr($data['receiver'],1);
	    }
	    
	    //print_r($data); exit;
	    $entity = PatientSurveySettingsTable::getInstance()->findOrCreateOneBy(['ipid'], [$ipid], $data);
	  	$this->_save_box_History($ipid, $entity, 'status', 'grow70', 'text');
	    $this->_save_box_History($ipid, $entity, 'receiver', 'grow70', 'text');
	    $this->_save_box_History($ipid, $entity, 'start_date', 'grow70', 'text');
	    $this->_save_box_History($ipid, $entity, 'interval_days', 'grow70', 'text');
	    
	    
	    // SEND SURVEY
	    $send_on_save = "0";
	    if($send_on_save == "1"){
    	    $logininfo = new Zend_Session_Namespace('Login_Info');
    	    $clientid = $logininfo->clientid;
    	     
    	    $allow_surveys = Modules::checkModulePrivileges("197", $clientid);
    	    if($allow_surveys){
    	         
    	        if (Zend_Registry::isRegistered('mypain')) {
    	             
    	            $mypain_cfg = Zend_Registry::get('mypain');
    	            $ipos_survey_id = $mypain_cfg['ipos']['chain'];
    	            	
    	            $form = new Application_Form_ClientSurveySettings(); //why in foreach? i forgot
    	            $result_survey = $form->create_pateint_survey_email($clientid,$ipos_survey_id,$ipid);
    	        }
    	   }
	   }
	     
	    
	    return $entity;

	}
	
	private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $division_tab )
	{
	
	    $newModifiedValues = $newEntity->getLastModified();
	    
	    if (isset($newModifiedValues[$fieldname])) {
	        
	        $new_values = $newModifiedValues[$fieldname];
	        
	        if($fieldname == 'receiver')
	        {
	        	if(substr($data['receiver'], 0, 1) == 'p')
	        	{
	        		$new_values = $this->_patientMasterData['last_name'] . ' ' . $this->_patientMasterData['first_name'] . ' - ' . $this->_patientMasterData['email'];
	        	}
	        	else
	        	{
	        		$new_values = $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_last_name'] . ' ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_first_name'] . ' - ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_email'];
	        	}
	        }
	        
	        $history = [
	            'ipid' => $ipid,
	            'clientid' => $this->logininfo->clientid,
	            'formid' => $formid,
	            'fieldname' => $fieldname,
	            'fieldvalue' => $new_values,
	        ];
	        
	        $newH = new BoxHistory();
	        $newH->fromArray($history);
	        $newH->save();
	
	    }
	
	}
	
}

