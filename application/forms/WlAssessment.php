<?php
/**
 * 
 * @author claudiu
 * 
 * 16.11.2017
 *
 */
class Application_Form_WlAssessment extends Pms_Form
{
	
	private $triggerformid = WlAssessment::TRIGGER_FORMID;
	private $triggerformname = WlAssessment::TRIGGER_FORMNAME;
	protected $_translate_lang_array = WlAssessment::LANGUAGE_ARRAY;
	
	public function isValid($data)
	{
	    
	    return parent::isValid($data);
	    
	}
	
	
	private function _create_formular_actions($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions')),
	    ));
	    
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
       
        
	    $el = $this->createElement('button', 'button_action', array(
	        'type'         => 'submit',
	        'value'        => 'save',
// 	        'content'      => $this->translate('submit'),
	        'label'        => $this->translate('submit'),
// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	        'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	        'decorators'   => array('ViewHelper'),
	    
	    ));
// 	    dd($el->getAttrib('content'));
	    $subform->addElement($el, 'save');
	    
	    
	    $el = $this->createElement('button', 'button_action', array(
	        'type'         => 'submit',
	        'value'        => 'print_pdf',
// 	        'content'      => $this->translate('generatepdf'),
	        'label'        => $this->translate('save AND Print'),
// 	        'onclick'      => '$(this).parents("form").attr("target", "_blank"); if(checkclientchanged(\'wlassessment_form\')){ setTimeout("window.location.reload()", 1000); return true;} else {return false;}',
	        'onclick'      => '$(this).parents("form").attr("target", "_blank"); window.formular_button_action = this.value;',
	        'decorators'   => array('ViewHelper'),
	         
	    ));
	    $subform->addElement($el, 'print_pdf');
	    
	    return $subform;
	
	}
	
	
	public function create_form_wl_assessment( $options = array(), $elementsBelongTo = null)
	{
	    
	    //@todo $subform or $this? this is the question
	    
	    $tabs_counter = 7;
	    
	    //@todo ! re-move this into the view ! if you intend to append this into your form
// 		$this->setMethod(self::METHOD_POST);
// 		$this->setAttrib("id", "wlassessment_form");
// 		$this->setAttrib("class", "wlassessment_form_class livesearchZipCities livesearchHealthInsurance livesearchDiagnosisIcd livesearchFamilyDoctor");
// 		$this->setAttrib("onsubmit", "return checkclientchanged('wlassessment_form');");
		
		$this->setDecorators(array(
		    'FormElements',
// 		    array('HtmlTag',array('tag' => 'table')),
		    'Form'
		));
		
// 		$this->setIsArray(true);
		

		if ( ! is_null($elementsBelongTo)) {
		    $this->setOptions(array(
		        'elementsBelongTo' => $elementsBelongTo
		    ));
		}
		
		//add navigation tabs to this form
		$tabs_navi = $this->_tabs_navigation($tabs_counter, $this->translate('Page %s'));
		$this->addSubform($tabs_navi, 'tabs_navi');
		
		
		
		//add pages
		for($i=1; $i<= $tabs_counter; $i++) {
		    $fn_name = "_page_{$i}";
		    $this->addSubform( call_user_func(array($this, $fn_name), $options), $fn_name);
		}
		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
		
		
		return $this;
		

		
	}
	
	
	
	private function _tabs_navigation($tabs_counter = 1, $tabs_label = 'Page %s') 
	{
	    $tabs = new Zend_Form_SubForm();
	    $tabs->clearDecorators()->addDecorators(array('FormElements', array('HtmlTag', array('tag' => 'ul', 'class' => 'tabs_gray_class'))));
	    
	    //add tab butons .. 1 to $tabs_counter
	    for($i=1; $i<= $tabs_counter; $i++) {
	        $tabs_li = new Zend_Form_SubForm();
	        $tabs_li->clearDecorators()->addDecorators(array('FormElements', array('HtmlTag', array('tag' => 'li'))));
	        $tabs_li->addElement(new Zend_Form_Element_Note(array(
	            'name' => "p{$i}_nav",
	            'value' => sprintf($tabs_label, $i),
	            'disableLoadDefaultDecorators' => true,
	            'decorators' => array(
	                array('ViewHelper'),
	                array('HtmlTag', array(
	                    'tag' => 'a',
	                    'href' => "#page-{$i}",//$this->getView()->url(array()),
	                    // 		            'class' => 'element',
	                )),
	            )
	        )), "nav-{$i}");
	        $tabs->addSubform($tabs_li, "tabs_navi_{$i}");
	    }
	    return $tabs;
	    
	}
	
	private function _create_formular_details($options = array(), $elementsBelongTo = null) 
	{
	    //if old fomular use him
	    //fetch data about the loghedin user, either from patient, or new query
	    //31.01.2018- user_id is the "Visit carried out by", not the $this->logininfo->userid ( 0 = please select)
	    
	    $userid = ! empty($options['user_id']) ? $options['user_id'] : 0;
	    
	    $available_users = $this->_patientMasterData['User'];
	    if ( ! empty($available_users)) {
	        User::beautifyName($available_users);	       
	    } else {
	        //some sort of SA or other... fetch his data
	        $available_users = User::get_AllByClientid($this->logininfo->clientid);
	    }
	    $active_users = array();
	    $formular_users = array();
	    
	    if ( ! empty($available_users)) {
	        $active_users = array_filter($available_users, function ($usr) {return ($usr['isactive']== 0 && $usr['isdelete']==0);});	

	        //order $active_users by last_name
	        uasort($active_users, array(new Pms_Sorter('last_name'), "_strcmp"));
    	    foreach ($active_users as $user) {
    	        $formular_users[$user['id']] =  $user['nice_name'];
    	    }
	    }
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Formular Details'));
	    $subform->setAttrib("class", "label_same_size label_same_size_80");
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    //hidden user_id, ipid, previous wlassessment_id
// 	    $subform->addElement('hidden', 'user_id', array(
// 	        'label'        => null,
// 	        'value'        => $userid,
// 	        'required'     => true,
// 	        'readonly'     => true,
// 	        'filters'      => array('StringTrim', 'Int'),
// 	        'validators'   => array('NotEmpty', 'Int'),
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 , 'openOnly'=>true)),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none', 'openOnly'=>true)),
	    
// 	        ),
// 	    ));
	    $subform->addElement('hidden', 'wlassessment_id', array(
	        'label'        => null,
	        'value'        => $options['wlassessment_id'],
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 , 'openOnly'=>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none', 'openOnly'=>true)),
	    
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'wlassessment_current_page', array(
	        'label'        => null,
	        'value'        => $options['wlassessment_current_page'],
	        'required'     => false,
	        'readonly'     => true,
	        'decorators'   => array('ViewHelper'),
	        'class'        => 'wlassessment_current_page',
	    ));
	    
	    $subform->addElement('hidden', 'patient_id', array(
	        'label'        => null,
	        'value'        => Pms_Uuid::encrypt($this->_patientMasterData['id']),
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly'=>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	             
	        ),
	    ));
	    
	    
// 	    $subform->addElement('note', 'allowed_formular_date', array(
// 	        'label'        => null,
// 	        'value'        => "<script type='text/javascript'> var __allowed_formular_date = " . json_encode($options['__allowed_formular_date']) . ";</script>",
// 	        'required'     => false,
// 	        'escape'       => false,
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 )),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	             
// 	        ),
// 	    ));
	    
	    
	    $subform->addElement('text', 'formular_date', array(
	        'label'        => $this->translate('Visit from:'),
	        'value'        => ! empty($options['formular_date']) ? date('d.m.Y', strtotime($options['formular_date'])) : date('d.m.Y'),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'date formular_date',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),
	        
	        'data-allowranges' => json_encode($options['__allowed_formular_date']),
	        
	        'onChange' => "reCreateDgpKern(this.value); return false;" ,
	    
	    ));
	    
	    
	    $subform->addElement('text', 'start_time', array(
	        'label'        => $this->translate('clock:'),
	        'value'        => ! empty($options['start_time']) ? $options['start_time'] : date("H:i"),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'time formular_time_start',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'class'=>'align_right')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	    
	        ),
	         
	    ));
	    $subform->addElement('text', 'end_time', array(
	        'label'        => $this->translate('to:'),
	        'value'        => ! empty($options['end_time']) ? $options['end_time'] : date("H:i", strtotime("+45 minutes")),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'time formular_time_end',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'class'=>'align_right')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	    
	        ),
	    ));
	   
	    //add the option = 'please select' to our user array
	    $formular_users = array('0'=>$this->translate('please select')) + $formular_users;
	    
	    $subform->addElement('select', 'user_id', array(
	        'label'        => $this->translate('Visit carried out by'),
	        'multiOptions' => $formular_users,
	        'value'        => $userid,
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
// 	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>5)),
	            array('Label', array('tag' => 'td', 'class'=>'size_140')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    
	        ),
	    ));
	    
	    return $subform;
	}
	

	private function _create_formular_reason($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('What is the reason of contact, what is the treatment goal:'));
	    $subform->setAttrib("class", "label_same_size_80");
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
    	//formular
    	$subform->addElement('textarea', 'formular_reason', array(
    	    'label'        => null,
    	    'value'        => $options['formular_reason'],
    	    'rows'         => 5,
    	    'cols'         => 90,
    	    'required'   => false,
    	    'filters'    => array('StringTrim'),
    	    'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
    	));
    	
    	return $subform; 
	}
	
	private function _page_1($options = array())
	{
	    
	    $page_number = 1;
	    
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
// 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
	    
	    
	    //formular
	    $subform = $this->_create_formular_details($options['formular'] , 'formular');
	    $page->addSubForm($subform, 'formular_details');
	     
	    //patient master
	    $af_pm =  new Application_Form_PatientMaster(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $this->_block_name,
	        '_clientForms'          => $this->_clientForms,
	        '_clientModules'        => $this->_clientModules,
	        '_client'               => $this->_client,
	    ));
	    $patient_details_form = $af_pm->create_form_patient_details();
	    $page->addSubForm($patient_details_form, 'patient_details');
	    

	    $af_pd = new Application_Form_PatientDiagnosis(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $this->_block_name,
	        '_clientForms'          => $this->_clientForms,
	        '_clientModules'        => $this->_clientModules,
	        '_client'               => $this->_client,
	    ));
	    
	    $patient_diagnosis_form = $af_pd->create_form_diagnosis($options["_page_1"]['PatientDiagnosis']);
	    $page->addSubForm($patient_diagnosis_form, 'PatientDiagnosis');
	    
	    
	    //formular
	    $subform = $this->_create_formular_reason($options['formular'] , 'formular');
	    $page->addSubForm($subform, 'formular_reason');
	    
	    
	    
	    $af_pa33a = new Application_Form_PatientAnlage33a();
	    $subform = $af_pa33a->create_form_anlage33a($options['_page_1']['PatientAnlage33a']);
	    $page->addSubForm($subform, 'PatientAnlage33a');
	    
	    
	    
// 	    PatientHealthInsurance
	    $af_hi = new Application_Form_PatientHealthInsurance(array(
	        "_patientMasterData" => $this->_patientMasterData,
	    ));
	    $health_insurance_form = $af_hi->create_form_health_insurance($options['_page_1']['PatientHealthInsurance']);
	    $page->addSubForm($health_insurance_form, 'PatientHealthInsurance');
	    
	   
	    $af_pdk = new Application_Form_PatientDgpKern();
	    $subform = $af_pdk->create_form_partners($options['_page_1']['PatientDgpKern']);
	    $page->addSubForm($subform, 'PatientDgpKern');
	    
	    
	    
	    return $page;
	}
	
	
	private function _page_2($options = array())
	{
	    
	    $page_number = 2;
	    

	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
	     
	    //patient Contact_Persons multiple
	    $af_cpm = new Application_Form_ContactPersonMaster();
	    
	    $cp_counter = 0;
	    $saved_patient_contacts_forms = new Zend_Form_SubForm();
	    $saved_patient_contacts_forms->removeDecorator('DtDdWrapper');
	    $saved_patient_contacts_forms->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'contact_person_accordion accordion_c'));
	    $saved_patient_contacts_forms->setLegend($this->translate('contactperson'));
	    $saved_patient_contacts_forms->setAttrib("class", "label_same_size");
	    foreach($options["_page_2"]['Contact_Persons'] as $cp) {
	        
	        $cp_arr = array($cp);
	        ContactPersonMaster::beautifyName($cp_arr);
	        $cp = $cp_arr[0];
	        
	        $contact_person_form = $af_cpm->create_form_contact_person($cp);
	        $contact_person_form->setLegend($contact_person_form->getLegend() . ' : ' .$cp['nice_name']);	        
	        $saved_patient_contacts_forms->addSubForm($contact_person_form, $cp_counter);
	        $cp_counter++;
	    }
	    //add button to add new contacts
	    $saved_patient_contacts_forms->addElement('button', 'addnew_contactperson', array(
	        'onClick'      => 'Contact_Person_addnew(this, \'_page_2[Contact_Persons]\'); return false;',
	        'value'        => '1',
	        'label'        => $this->translate('Add new contact person'),
            'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
	            array('HtmlTag', array('tag' => 'div')),
	        ),
	        'class'        =>'button',
	    ));
	    $page->addSubform($saved_patient_contacts_forms, 'Contact_Persons');
	    
	    
// 	    //Living_Will
	    $af_pacp = new Application_Form_PatientACP([
	        '_block_name'           => $this->_block_name,
	        '_patientMasterData'    => $this->_patientMasterData,
        ]);
	    $contact_persons_arr = array();    
	    
	    ContactPersonMaster::beautifyName($options["_page_2"]['Contact_Persons']);
	    $contact_persons_arr = array_column($options["_page_2"]['Contact_Persons'], 'nice_name', 'id');
	    
	    $options["_page_2"]['Patient_ACP'] = is_array($options["_page_2"]['Patient_ACP']) ? $options["_page_2"]['Patient_ACP'] : array();
	    $acp_options = array_merge($options["_page_2"]['Patient_ACP'], array('contact_persons_arr' => $contact_persons_arr));
	    
	    $patient_acp_form = $af_pacp->create_form_acp($acp_options);
	    $page->addSubform($patient_acp_form, 'Patient_ACP');
	    
	    
	    
	    //patient Family_Doctor is only one
	    $af_fd = new Application_Form_Familydoctor(array(
	        '_patientMasterData' => $this->_patientMasterData   
	    ));	    
	    $family_doctor_form = $af_fd->create_form_family_doctor($options["_page_2"]['Family_Doctor']);	    
	    $page->addSubform($family_doctor_form, 'Family_Doctor');
	    
	    
	    //patient Specialists multiple
	    $af_ps = new Application_Form_PatientSpecialist();
	   
	    $cp_counter = 0;
	    $saved_patient_specialists = new Zend_Form_SubForm();
	    $saved_patient_specialists->removeDecorator('DtDdWrapper');
	    $saved_patient_specialists->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'specialists_accordion accordion_c'));
	    $saved_patient_specialists->setLegend($this->translate('specialists'));
	    $saved_patient_specialists->setAttrib("class", "label_same_size");
	    foreach($options["_page_2"]['Specialists'] as $cp) {
	         
	        $cp_arr = array($cp);
	        PatientSpecialists::beautifyName($cp_arr);
	        $cp = $cp_arr[0];
	        
	        $specialist_form = $af_ps->create_form_specialist($cp);
	        $specialist_form->setLegend($specialist_form->getLegend() . ' : ' .$cp['nice_name']);
	        $saved_patient_specialists->addSubForm($specialist_form, $cp_counter);
	        $cp_counter++;
	    }
	    //add button to add new contacts
	    $saved_patient_specialists->addElement('button', 'addnew_specialist', array(
	        'onClick'      => 'Patient_Specialist_addnew(this, \'_page_2[Specialists]\'); return false;',
	        'value'        => '1',
	        'label'        => $this->translate('Add new specialist'),
	        'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
	            array('HtmlTag', array('tag' => 'div')),
	        ),
	        'class'        =>'button',
	    ));
	    $page->addSubform($saved_patient_specialists, 'Specialists');
	    
	    
	    
	    $af_s = new Application_Form_Stammdatenerweitert();
	    $subform = $af_s->create_form_marital_status($options['_page_2']['Marital_status']);
	    $page->addSubform($subform, 'Marital_status');
	    
	    $subform = $af_s->create_form_nationality($options['_page_2']['Nationality']);
	    $page->addSubform($subform, 'Nationality');
	    
	    
	    $af_pr =  new Application_Form_PatientReligions();
	    $subform = $af_pr->create_form_religion($options['_page_2']['Religion']);
	    $page->addSubform($subform, 'Religion');
	    
	    

	    $af_pr = new Application_Form_PatientRemedy();
	    $subform = $af_pr->create_form_remedies($options['_page_2']['Remedies']);
	    $page->addSubform($subform, 'Remedies');
	    
	    return $page;
	}
	
	
	
	private function _page_3($options = array())
	{
	
	    $page_number = 3;
	     
	
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        // 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
		     
	    
	    $af_s = new Application_Form_Stammdatenerweitert();
	    $subform = $af_s->create_form_vigilanz($options['_page_3']['Vigilance']);
	    $page->addSubform($subform, 'Vigilance');
	    
	    
// 	    dd($options['page_3']['Orientation2']['values']);
	    $af_po2 = new Application_Form_PatientOrientation2();
	    $subform = $af_po2->create_form_orientation2($options['_page_3']['Orientation2']);
	    $page->addSubform($subform, 'Orientation2');
	    
	    	  
	    $af_pm2 = new Application_Form_PatientMobility2();
	    $subform = $af_pm2->create_form_mobility2($options['_page_3']['Mobility2']);
	    $page->addSubform($subform, 'Mobility2');
	    

	    //maintenancestage
	    $af_pm =  new Application_Form_PatientMaintainanceStage(array(
	    		'_patientMasterData'    => $this->_patientMasterData,
	    		'_block_name'           => $this->_block_name,
	    		'_clientForms'          => $this->_clientForms,
	    		'_clientModules'        => $this->_clientModules,
	    		'_client'               => $this->_client,
	    ));
	    
	    $patient_stage_form = $af_pm->create_form_maintenance_stage($options['_page_3']['PatientMaintainanceStage']);
	    $page->addSubForm($patient_stage_form, 'maintenancestage');
	     
// 	    $subform = new Zend_Form_SubForm();
// 	    $subform->addDecorator('HtmlTag', array('tag'=>'div'))->removeDecorator('DtDdWrapper');
// 	    $subform->setAttrib("class", "label_same_size_100");
// 	    //TODO remove this when ok
// 	    $subform->setAttrib('style', "border: 2px solid red;");
	     
// 	    $subform->setLegend($this->translate('Nursing degree:'));
// 	    $subform->addElement('text', 'name', array(
// 	        'label'      => $this->translate('phone'),
// 	        'required'   => false,
// 	        'filters'    => array('StringTrim'),
// 	        'validators' => array('NotEmpty'),
// 	    ));
// 	    $subform->setIsArray(true);
// 	    $page->addSubform($subform, 'Nursing_degree');
	    
	    
	    
	    
	    //patient PatientPflegedienst multiple
	    $af_pp = new Application_Form_PatientPflegedienst();
	     
	    $cp_counter = 0;
	    $saved_forms = new Zend_Form_SubForm();
	    $saved_forms->removeDecorator('DtDdWrapper');
	    $saved_forms->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'patient_pflegedienst_accordion accordion_c'));
	    $saved_forms->setLegend($this->translate('pflegedienste'));
	    $saved_forms->setAttrib("class", "label_same_size");
	    foreach($options["_page_3"]['PatientPflegediensts'] as $cp) {
	        
	        $cp_arr = array($cp);
	        PatientPflegedienste::beautifyName($cp_arr); 
	        $cp = $cp_arr[0];
	        
	        $one_form = $af_pp->create_form_patient_pflegedienst($cp);
	        $one_form->setLegend($one_form->getLegend() . ' : ' .$cp['nice_name']);
	        $saved_forms->addSubForm($one_form, $cp_counter);
	        $cp_counter++;
	    }
	    //add button to add new contacts
	    $saved_forms->addElement('button', 'addnew_patientpflegedienst', array(
	        'onClick'      => 'PatientPflegedienst_addnew(this, \'_page_3[PatientPflegediensts]\'); return false;',
	        'value'        => '1',
	        'label'        => $this->translate('Add new patient nursing'),
	        'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
	            array('HtmlTag', array('tag' => 'div')),
	        ),
	        'class'        =>'button',
	    ));
	    $page->addSubform($saved_forms, 'PatientPflegediensts');
	    
	    
	    
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'div'))->removeDecorator('DtDdWrapper');
	    $subform->setAttrib("class", "label_same_size_100");
	    
	    //TODO remove this when ok
	    $subform->setAttrib('style', "border: 2px solid red;");
	    
	    $subform->setLegend($this->translate('Service of the nursing service:'));
	    $subform->addElement('text', 'name', array(
	        'label'      => $this->translate('phone'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	    ));
	    $subform->setIsArray(true);
// 	    $page->addSubform($subform, 'Service_of_nursing');
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'div'))->removeDecorator('DtDdWrapper');
	    $subform->setAttrib("class", "label_same_size_100");
	    
	    //TODO remove this when ok
	    $subform->setAttrib('style', "border: 2px solid red;");
	    
	    $subform->setLegend($this->translate('Care situation of the patient:'));
	    $subform->addElement('text', 'name', array(
	        'label'      => $this->translate('phone'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	    ));
	    $subform->setIsArray(true);
// 	    $page->addSubform($subform, 'Care_situation');
	    
	    
	    
	    


	    $af_s = new Application_Form_PatientLocation(array(
        		'_patientMasterData'    => $this->_patientMasterData,
        		'_block_name'           => $this->_block_name,
        		'_clientForms'          => $this->_clientForms,
        		'_clientModules'        => $this->_clientModules,
        		'_client'               => $this->_client,
        ));
	    $subform = $af_s->create_form_patient_location($options['_page_3']['PatientLocation']);
	    $page->addSubform($subform, 'PatientLocation');
	    
	    $af_s = new Application_Form_Stammdatenerweitert();
	    $subform = $af_s->create_form_artificial_exits($options['_page_3']['Artificial_exits']);
	    $page->addSubform($subform, 'Artificial_exits');
	    
	    
	   
	    $af_s = new Application_Form_Stammdatenerweitert();
	    $subform = $af_s->create_form_excretion($options['_page_3']['Excretion']);
	    $page->addSubform($subform, 'Excretion');


	    $af_pp = new Application_Form_PatientPort();
	    $subform = $af_pp->create_form_port($options['_page_3']['Port']);
	    $page->addSubform($subform, 'Port');
	     
	    
	    

	    
	    return $page;
	}
	
	private function _page_4($options = array())
	{
	
	    $page_number = 4;
	
	
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        // 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
	

	    $af_wd = new Application_Form_WoundDocumentation();
// 	    $wound_type = $af_wd->create_form_wound_type($options, $subform->getName());
	    $wound_type = $af_wd->create_form_wound_type($options["_page_4"]['Wound_Type']);
	    $page->addSubform($wound_type, 'Wound_Type');
	    
	    $wound_localization = $af_wd->create_form_wound_localization($options["_page_4"]['Wound_Localization']);
	    $page->addSubform($wound_localization, 'Wound_Localization');
	    
	    
// 	    $af = new Application_Form_PatientKarnofsky();
// 	    $subform = $af->create_form_karnofsky($options["_page_4"]['PatientKarnofsky']);
// 	    $page->addSubform($subform, 'PatientKarnofsky');
	    
	    $af_pdk = new Application_Form_PatientDgpKern();
	    $subform = $af_pdk->create_form_ecog($options['_page_4']['PatientDgpKern']);
	    $page->addSubForm($subform, 'PatientDgpKern');
	     
	     
	    
	    
	   //this is just a holder, medication is just xhr.. try iframe 
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'div', 'class' => 'Medications2_holder_div'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Medications:'));
	    $page->addSubform($subform, 'Medications');

	    
	    
	    $af_pdpa = new Application_Form_PatientDrugPlanAllergies();
	    $subform = $af_pdpa->create_form_alergies($options["_page_4"]['Alergies']);
	    $page->addSubform($subform, 'Alergies');
	    
	    
	    return $page;
	}
	
	private function _page_5($options = array())
	{
	
	    $page_number = 5;
	    
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        // 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
	    
	    
	    $af_ps = new Application_Form_PatientSymptomatology();
	    $subform = $af_ps->create_form_symptomatology($options["_page_5"]['PatientSymptpomatology']);
	    $page->addSubform($subform, 'PatientSymptpomatology');
	    
	    
	    $af_pes = new Application_Form_PatientExpectedSymptoms();
	    $subform = $af_pes->create_form_expected_symptoms($options["_page_5"]['PatientExpectedSymptoms']);
	    $page->addSubform($subform, 'PatientExpectedSymptoms');
	    
	    $af_ph = new Application_Form_PatientHospizverein();
	    $subform = $af_ph->create_form_hospice_association($options["_page_5"]['PatientHospizverein']);
	    $page->addSubform($subform, 'PatientHospizverein');
	    
	    $af_phc = new Application_Form_PatientHospiceCertification();
	    $subform = $af_phc->create_form_hospice_certification($options["_page_5"]['PatientHospiceCertification']);
	    $page->addSubform($subform, 'PatientHospiceCertification');
	    
	    
	    $af_pp = new Application_Form_PatientPsychooncological();
	    $subform = $af_pp->create_form_psycho_oncological_support($options["_page_5"]['PatientPsychooncological']);
	    $page->addSubform($subform, 'PatientPsychooncological');
	    
	    return $page;
	}
	
	private function _create_formular_next_date($options = array(), $elementsBelongTo = null)
	{
	    $subform = $this->subFormTable();
	    $subform->setLegend($this->translate('Report to the QPA on the definition of a care / treatment plan and, if necessary, necessary further action will be taken on:'));
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    $subform->addElement('text', 'next_date', array(
	        'label'         => null,
	        'required'      => false,
	        'value'         => !empty($options['next_date']) ? date('d.m.Y', strtotime($options['next_date'])) : null,
	        'decorators'    => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'comments', 'style' => $display)),
	        ),
	        'class' => 'date allow_future',
	    ));
	     
	    return $subform;
	}
	private function _page_6($options = array())
	{
	    
	    $page_number = 6;
	    
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        // 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));

	    
	    $af = new Application_Form_PatientChildMourning();
	    $subform = $af->create_form_child_mourning($options["_page_6"]['PatientChildMourning']);
	    $page->addSubform($subform, 'PatientChildMourning');
	    
	    $af = new Application_Form_PatientSpiritualAttitude();
	    $subform = $af->create_form_spiritual_attitude($options["_page_6"]['PatientSpiritualAttitude']);
	    $page->addSubform($subform, 'PatientSpiritualAttitude');
	    
	  
	    $af = new Application_Form_PatientDivergentAttitude();
	    $subform = $af->create_form_divergent_attitude($options["_page_6"]['PatientDivergentAttitude']);
	    $page->addSubform($subform, 'PatientDivergentAttitude');
	    
	    
	    //formular
	    $subform = $this->_create_formular_next_date($options['formular'] , 'formular');
	    $page->addSubForm($subform, 'formular_details');
	    
	    
	    $af = new Application_Form_PatientGeneralPractitionerInitial();
	    $subform = $af->create_form_general_practitioner_initial($options["_page_6"]['PatientGeneralPractitionerInitial']);
	    $page->addSubform($subform, 'PatientGeneralPractitionerInitial');
	    
	    $af = new Application_Form_PatientNextContactBy();
	    $subform = $af->create_form_next_contact_by($options["_page_6"]['PatientNextContactBy']);
	    $page->addSubform($subform, 'PatientNextContactBy');
	    
	    
	    return $page;
	}
	
	
	private function _create_formular_other($options = array(), $elementsBelongTo = null)
	{
	    $subform = $this->subFormTable();
	    $subform->setLegend($this->translate('Other:'));
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $subform->addElement('textarea', 'comment', array(
            'label'         => null,
            'required'      => false,
            'value'         => $options['comment'],
            'decorators'    => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'comments', 'style' => $display)),
            ),
            'rows'      => 3,
            'cols'      => 60,
        ));
	    
	    return $subform;
	     
	}
	private function _page_7($options = array())
	{
	
	    $page_number = 7;
	
	
	    $page = new Zend_Form_SubForm();
	    $page->clearDecorators()
	    ->setDecorators( array(
	        // 	        'FormErrors',//errors on top?
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}")),
	    ))
	    ->setLegend(sprintf($this->translate('Page %s'), $page_number));
	
	    
	    
	    $af = new Application_Form_PatientCloseContact();
	    $subform = $af->create_form_close_contact($options["_page_7"]['PatientCloseContact']);
	    $page->addSubform($subform, 'PatientCloseContact');
	     
	    

	    $subform = $this->_create_formular_other($options['formular'] , 'formular');
	    $page->addSubform($subform, 'formular_details');
	    
	    
	    return $page;
	}

	
	
	/**
	 * 
	 * @param unknown $ipid
	 * @param unknown $data
	 * @throws Exception
	 * @return NULL|Doctrine_Record
	 */
	public function save_form_wl_assessment($ipid, $data = array())
	{ 
	    if (empty($ipid)) {
	        throw new Exception('Contact Admin, formular cannot be saved.', 0);
	    }
// 	    die_ancuta($data); 
	    
	    //formular will be saved first so we have a wlassessment_id
	    $wlassessment_data = array();
	    $wlassessment_data['ipid'] = $ipid;
	    foreach ($data as $page) {
	        if (isset($page['formular'])) {
	            $wlassessment_data = array_merge($wlassessment_data, $page['formular']);
	        }
	    }
	    //manual format german date to Y-m-d
	    $wlassessment_data['formular_date'] = empty($wlassessment_data['formular_date']) ? date('Y-m-d') : date('Y-m-d', strtotime($wlassessment_data ['formular_date']));
	    $wlassessment_data['next_date'] = empty($wlassessment_data['next_date']) ? NULL : date('Y-m-d', strtotime($wlassessment_data ['next_date']));
	    
	    
	    
// 	    array_walk($wlassessment_data, function(&$item, $key){
// 	        if (empty($item)) $item = NULL;//empty values are null;
// 	    });
        $entity  = new WlAssessment();
        $wlassessment =  $entity->findOrCreateOneByIdAndIpid( $wlassessment_data['wlassessment_id'], $ipid, $wlassessment_data);
// 	    dd($wlassessment_data, $wlassessment->toArray());
        
        
        if ( ! $wlassessment->id) {
            throw new Exception('Contact Admin, formular cannot be saved.', 1);
            return null;//we cannot save... contact admin
        }
        
        //add wlassessment_id to all the boxes
        //not a good idea... will have to change
        foreach ($data as $k=>&$page) {
            if (is_array($page) && strpos($k, '_page_')===0) 
            foreach ($page as &$fieldset) {
                $fieldset['wlassessment_id'] = $wlassessment->id;
            }
        }
        
        /**
         * page 1 save
         */
        $af = new Application_Form_PatientMaster(array(
            '_block_name'   => $this->_block_name,
        ));
//         $patient_details = $af->save_form_patient_details($ipid, $data['_page_1']['patient_details']);
        
        $af = new Application_Form_PatientHealthInsurance(array(
	        "_patientMasterData" => $this->_patientMasterData,
	    ));
        $health_insurance = $af->save_form_health_insurance($ipid, $data['_page_1']['PatientHealthInsurance']);
        //dd($health_insurance->toArray());
        
        $af = new Application_Form_PatientAnlage33a();
        $af->save_form_anlage33a($ipid, $data['_page_1']['PatientAnlage33a']);
        
        $af = new Application_Form_PatientDgpKern();
        $af->save_form_partners($ipid, array_merge($data['_page_1']['PatientDgpKern'], ['__formular' => $wlassessment_data]));
        

        $af = new Application_Form_PatientDiagnosis();
        $patient_details = $af->save_form_diagnosis($ipid, $data['_page_1']['PatientDiagnosis']);
         
        
        /**
         * page 2 save
         */
        $af = new Application_Form_ContactPersonMaster();
        foreach ( $data['_page_2']['Contact_Persons'] as $Contact_Person) {
            if (is_array($Contact_Person)) $af->save_form_contact_person($ipid, $Contact_Person);
        }
        
        $af = new Application_Form_Familydoctor(
            array(
                '_patientMasterData'    => $this->_patientMasterData,
                '_block_name'           => $this->_block_name,
                '_clientForms'          => $this->_clientForms,
                '_clientModules'        => $this->_clientModules,
                '_client'               => $this->_client,
        ));
        $af->save_form_family_doctor($ipid, $data['_page_2']['Family_Doctor'] );
        
        $af = new Application_Form_PatientSpecialist(array(
                '_patientMasterData'    => $this->_patientMasterData,
                '_block_name'           => $this->_block_name,
                '_clientForms'          => $this->_clientForms,
                '_clientModules'        => $this->_clientModules,
                '_client'               => $this->_client,
        ));
        foreach ( $data['_page_2']['Specialists'] as $PatientSpecialist) {
            if (is_array($PatientSpecialist)) $af->save_form_specialist($ipid, $PatientSpecialist);
        }
        
        $af = new Application_Form_PatientACP();
        foreach ( $data['_page_2']['Patient_ACP'] as $key=>$division) {
            $division['qquuid'] =  $data['qquuid'];
            $af->save_form_acp($ipid, $division);
        }
        
        $af = new Application_Form_Stammdatenerweitert();
        $af->save_form_marital_status($ipid, $data['_page_2']['Marital_status']);
         
        $af->save_form_nationality($ipid, $data['_page_2']['Nationality']);
         
        $af = new Application_Form_PatientReligions();
        $af->save_form_religion($ipid, $data['_page_2']['Religion']);
        
        $af = new Application_Form_PatientRemedy();
        $af->save_form_remedies($ipid, $data['_page_2']['Remedies']);
         
        
        /**
         * page 3 save
         */
        $af = new Application_Form_Stammdatenerweitert();
        $af->save_form_vigilanz($ipid, $data['_page_3']['Vigilance']);
        
        $af = new Application_Form_PatientOrientation2();
        $af->save_form_orientation2($ipid, $data['_page_3']['Orientation2']);
                
        $af = new Application_Form_PatientMobility2();
        $af->save_form_mobility2($ipid, $data['_page_3']['Mobility2']);
        
        $af = new Application_Form_PatientPflegedienst(
            array(
                '_patientMasterData'    => $this->_patientMasterData,
                '_block_name'           => $this->_block_name,
                '_clientForms'          => $this->_clientForms,
                '_clientModules'        => $this->_clientModules,
                '_client'               => $this->_client,
        ));
        foreach ( $data['_page_3']['PatientPflegediensts'] as $PatientPflegedienst) {
            if (is_array($PatientPflegedienst)) $af->save_form_patient_pflegedienst($ipid, $PatientPflegedienst);
        }
        
        $af = new Application_Form_Stammdatenerweitert();
        $af->save_form_artificial_exits($ipid, $data['_page_3']['Artificial_exits']);
        
        $af->save_form_excretion($ipid, $data['_page_3']['Excretion']);
         
        
        $af = new Application_Form_PatientPort();
        $af->save_form_port($ipid, $data['_page_3']['Port']);
        
        
        $af = new Application_Form_PatientMaintainanceStage();
        $af->save_maintenancestage($ipid, $data['_page_3']['maintenancestage']);
        
        

        $af =  new Application_Form_PatientLocation(array(
        		'_patientMasterData'    => $this->_patientMasterData,
        		'_block_name'           => $this->_block_name,
        		'_clientForms'          => $this->_clientForms,
        		'_clientModules'        => $this->_clientModules,
        		'_client'               => $this->_client,
        ));
        $af->save_patientlocation($ipid,$data['_page_3']['PatientLocation']);
        
        
        
        
        
        /**
         * page 4 save
         */
        
        $af = new Application_Form_WoundDocumentation();
        $canvas_container = reset($data['canvas_container']);
        if ( ! empty($canvas_container)) { //save only if not empty, so we don't overwrite
            $data['_page_4']['Wound_Type']['w_localisation'] = $canvas_container;
        }
        
        $wound_type = $af->save_form_wound($ipid, $data['_page_4']['Wound_Type']);
        
        if ($wound_type instanceof WoundDocumentation) {
            $wlassessment->wound_documentation_id = $wound_type->id;
            $wlassessment->save();
        }
        
        $af = new Application_Form_PatientKarnofsky();
        $karnofsky = $af->save_form_karnofsky($ipid, $data['_page_4']['PatientKarnofsky']);
        if ($karnofsky instanceof PatientKarnofsky) {
            $wlassessment->patient_karnofsky_id = $karnofsky->id;
            $wlassessment->save();
        }
        
        $af = new Application_Form_PatientDgpKern();
        $af->save_form_ecog($ipid, array_merge($data['_page_4']['PatientDgpKern'], ['__formular' => $wlassessment_data]));
        

        $af = new Application_Form_PatientDrugPlanAllergies();
        $af->save_form_alergies($ipid, $data['_page_4']['Alergies']);
        
        
        $med_form =  new Application_Form_PatientDrugPlan(array(
        		'_patientMasterData'    => $this->_patientMasterData,
        		'_block_name'           => $this->_block_name,
        		'_clientForms'          => $this->_clientForms,
        		'_clientModules'        => $this->_clientModules,
        		'_client'               => $this->_client,
        ));
        $med_form->save_medication($ipid,$data);
 
        
        $af = new Application_Form_PatientDrugPlanAllergies();
        $af->save_form_alergies($ipid, $data['_page_4']['ECOG_status']);
        
        
        
        
        /**
         * page 5
         */
        $af = new Application_Form_PatientSymptomatology();
        $af->save_form_symptomatology($ipid, $data['_page_5']['PatientSymptpomatology']);
         
         
        $af = new Application_Form_PatientExpectedSymptoms();
        $af->save_form_expected_symptoms($ipid, $data['_page_5']['PatientExpectedSymptoms']);
         
        $af = new Application_Form_PatientHospizverein();
        $r = $af->save_form_hospice_association($ipid, $data['_page_5']['PatientHospizverein']);
         
        $af = new Application_Form_PatientHospiceCertification();
        $af->save_form_hospice_certification($ipid, $data['_page_5']['PatientHospiceCertification']);
         
         
        $af = new Application_Form_PatientPsychooncological();
        $af->save_form_psycho_oncological_support($ipid, $data['_page_5']['PatientPsychooncological']);
         
        
        
        
        /**
         * page 6
         */
        $af = new Application_Form_PatientChildMourning();
        $af->save_form_child_mourning($ipid, $data["_page_6"]['PatientChildMourning']);

        $af = new Application_Form_PatientSpiritualAttitude();
        $af->save_form_spiritual_attitude($ipid, $data["_page_6"]['PatientSpiritualAttitude']);

        $af = new Application_Form_PatientDivergentAttitude();
        $af->save_form_divergent_attitude($ipid, $data["_page_6"]['PatientDivergentAttitude']);

        $af = new Application_Form_PatientGeneralPractitionerInitial();
        $af->save_form_general_practitioner_initial($ipid, $data["_page_6"]['PatientGeneralPractitionerInitial']);

        $af = new Application_Form_PatientNextContactBy();
        $af->save_form_next_contact_by($ipid, $data["_page_6"]['PatientNextContactBy']);

//         //save page 7
//         $af = new Application_Form_PatientCloseContact();
//         $af->save_form_close_contact($this->ipid, $post["_page_7"]['PatientCloseContact']);
   
        
        /**
         * page 7
         */

        $af = new Application_Form_PatientCloseContact();
        $af->save_form_close_contact($ipid, $data["_page_7"]['PatientCloseContact']);
        
         

        return $wlassessment;
    }
	
	
	
	//TODO move this to FORM
    private function temporary_files_delete($folder, $age = '86400')
    {
        if($handle = opendir($folder))
        {
            while(false !== ($entry = readdir($handle)))
            {
                $filename = $folder . '/' . $entry;
                $mtime = @filemtime($filename);
                if(is_file($filename) && $mtime && (time() - $mtime > $age))
                {
                    @unlink($filename);
                }
            }
            closedir($handle);
        }
    }
    private function temporary_image_create($data, $type = 'svg', $stype = 'human')
    {
        $tmp_file = uniqid('img' . rand(1000, 9999));
        $tmp_file_path = APPLICATION_PATH . '/../public/temp/' . $tmp_file . '.png';
        $tmp_folder = APPLICATION_PATH . '/../public/temp';
        $this->temporary_files_delete($tmp_folder, '7200'); //delete all files older than 2 hours
    
        switch($type)
        {
            case 'svg':
                if(get_magic_quotes_gpc())
                {
                    $data = stripslashes($data);
                }
    
                $data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $data;
    
                $handle = fopen($tmp_file_path, 'w+');
                fclose($handle);
    
                $im = new Imagick();
                $im->readImageBlob($data);
                $im->setImageFormat("jpeg");
                $im->writeImage($tmp_file_path);
                $im->clear();
                $im->destroy();
    
                break;
    
            case 'base64':
                $data = substr($data, stripos($data, '64,') + 3);
                $data = base64_decode($data);
    
                //transparent answer image
                $im = @imagecreatefromstring($data);
                $rgb = imagecolorat($im, 1, 1);
                $colors = imagecolorsforindex($im, $rgb);
    
                if($colors['alpha'] > 0 && $colors['red'] == 0)
                {
                    //stupid hack CHANGE THIS!!!!!
                    imagecolortransparent($im, imagecolorallocatealpha($im, 0, 0, 0, 127));
                }
                elseif($colors['red'] == 255)
                {
                    imagecolortransparent($im, imagecolorallocatealpha($im, 255, 255, 255, 127));
                }
    
                //human body background
                if($stype == 'human-big')
                {
                    $bg = imagecreatefromjpeg(APPLICATION_PATH . '/../public/images/human_big.jpg');
                }
                else if($stype == 'human-huge')
                {
                    $bg = imagecreatefrompng(APPLICATION_PATH . '/../public/images/painlocation.png');
                }
                else
                {
                    $bg = imagecreatefromjpeg(APPLICATION_PATH . '/../public/images/human_small.jpg');
                }
    
                if($stype == 'human-big')
                {
                    imagecopymerge($bg, $im, 0, 0, 0, 0, 850, 600, 100);
                }
                else
                {
                    imagecopymerge($bg, $im, 0, 0, 0, 0, 550, 388, 100);
                }
    
                imagepng($bg, $tmp_file_path);
                imagedestroy($bg);
    
                break;
    
            default:
                break;
        }
    
        if(is_readable($tmp_file_path))
        {
            return $tmp_file_path;
        }
        else
        {
            return false;
        }
    }
    
    
    public function create_form_history( $options = array(), $elementsBelongTo = null)
    {
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Formular History:'));
        $subform->setAttrib("class", "label_same_size");
         
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        
        $subform->addElement('note', 'counter_td', array(
            'label'        => null,
            'value'        => '1',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),
        ));
        
        $subform->addElement('text', 'date', array(
            'label'      => $this->translate('Date of recording:'),
            // 	        'placeholder' => 'Search my date',
            'required'   => true,
            'value'    => date('d.m.Y', strtotime($this->_patientMasterData['admission_date'])),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'class'    => 'date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
         
        $subform->addElement('text', 'download', array(
            'label'      => $this->translate('first_name'),
            'required'   => true,
            'value'    => $this->_patientMasterData['first_name'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'last_name', array(
            'label'      => $this->translate('last_name'),
            'value'    => $this->_patientMasterData['last_name'],
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'birthd', array(
            'label'      => $this->translate('birthd'),
            'value'    => $this->_patientMasterData['birthd'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'class'    => 'date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        //TODO move this into icd form
        
        $subform->addElement('text', 'street1', array(
            'label'      => $this->translate('street1'),
            'value'    => $this->_patientMasterData['street1'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'zip', array(
            'label'      => $this->translate('zip'),
            'value'    => $this->_patientMasterData['zip'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'data-livesearch'   => 'zip',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'city', array(
            'label'      => $this->translate('city'),
            'value'    => $this->_patientMasterData['city'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'data-livesearch'   => 'city',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'phone', array(
            'label'      => $this->translate('phone'),
            'value'    => $this->_patientMasterData['phone'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        
        return $subform;
    
    }
         
    
}

