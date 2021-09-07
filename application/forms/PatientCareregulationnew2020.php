<?php
//ISPC-2777 Dragos 25.01.2021 (full file)
/**
 * 
 * @author carmen
 * 
 * 07.06.2018
 *
 */
class Application_Form_PatientCareregulationnew2020 extends Pms_Form
{
	protected $_phealthinsurance = null;
	
	protected $_user = null;
	
	protected $_diagnosis = null;
	
	protected $_page_lang = null;
	
	protected $_multiple_stamps = null;
	
	protected $_user_stamps = null;
	
	protected $_fr_index = null;
	
	protected $_cl_index = null;
	
	protected $_sp_index = null;
	
	public function __construct($options = null)
	{		
		if (isset($options['_phealthinsurance'])) {
			$this->_phealthinsurance = $options['_phealthinsurance'];
			unset($options['_phealthinsurance']);
		}
		
		/*if (isset($options['_user'])) {
			$this->_user = $options['_user'];
			unset($options['_user']);
		}*/
		
		if (isset($options['_diagnosis'])) {
			$this->_diagnosis = $options['_diagnosis'];
			unset($options['_diagnosis']);
		}
		
		parent::__construct($options);
		
		$this->_page_lang = $this->translate ( 'careregulationnew_lang' );
		
		//$userarray = $this->_patientMasterData['User'];
		
		$users = new User();
		$userarray = $users->getUserByClientid($this->logininfo->clientid, 0, true);
		
		if ( ! empty($userarray)) {
			User::beautifyName($userarray);
		} else {
			//some sort of SA or other... fetch his data
			$userarray = User::get_AllByClientid($this->logininfo->clientid);
		}
		
		if($userarray)
		{
			$user_data = array();
			$user_ids = array();
			foreach($userarray as $user)
			{
				$user_data[$user['id']]['name'] = trim($user['last_name']) . " " . trim($user['first_name']);
				$user_data[$user['id']]['businessnr'] = $user['betriebsstattennummer'];
				$user_data[$user['id']]['doctornr'] = $user['LANR'];
				$user_data_ids[] = $user['id'];
			}
				
		$this->_user = $user_data;	
	//var_dump($userarray); exit;			
			//$user_data['businessnr'] = $uarray['betriebsstattennummer'];
			//$user_data['doctornr'] = $uarray['LANR'];
		}
		
		if($this->logininfo->usertype == 'SA' || $this->logininfo->usertype == 'CA')
		{
			$isadmin = '1';
		}
		
		if($isadmin == 1)
		{
			$showselect = 1;
		}
		else
		{
			// show select to all
			$showselect = 1;
		}
		//$this->view->showselect = $showselect;
		
		$ustamp = new UserStamp();
		$multipleuser_stamp = $ustamp->getAllUsersActiveStamps($user_data_ids);
		
		foreach($multipleuser_stamp as $ks => $uspamp)
		{
			$multiple_user_stamps[$uspamp['userid']]['user_id'] = $uspamp['userid'];
			$multiple_user_stamps[$uspamp['userid']]['user_name'] = $user_data[$uspamp['userid']];
			$multiple_user_stamps[$uspamp['userid']]['user_stamps'][$uspamp['id']] = $uspamp['stamp_name'];
		}
		
		$this->_user_stamps = array();
		$this->_user_stamps['0'] = $this->translate('please select');
		
		if($this->_clientModules['64'] === true)
		{
			foreach($multiple_user_stamps as $kus=>$vus)
			{
				$user_stamps = array();
				foreach($vus['user_stamps'] as $kst=>$vst)
				{
					$user_stamps[$kus.'-'.$kst] = $vst;
				}
					
				$this->_user_stamps[$vus['user_name']['name']] = $user_stamps;
			}
		}
		else 
		{
			foreach($this->_user as $ku=>$vu)
			{
				$this->_user_stamps[$ku] = $vu['name'];
			}
		}
		
		$this->_fr_index = 0;
		$this->_cl_index = 0;
		$this->_sp_index = 0;
	}	
	
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
        
	    /*$el = $this->createElement('button', 'button_action', array(
	        'type'         => 'submit',
	        'value'        => 'savepdf',
// 	        'content'      => $this->translate('submit'),
	        'label'        => $this->_page_lang['save pdf'],
// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	        'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	        'decorators'   => array('ViewHelper'),
	    
	    ));
	    $subform->addElement($el, 'savepdf');*/
	    
	    /* $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'preprint_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pre pdf'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    
	    ));
	    $subform->addElement($el, 'preprintpdf'); */
	    
	   /*  $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'print_pag1_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pdf page1'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    	  
	    ));
	    $subform->addElement($el, 'printpdf_page1');
	    
	    $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'print_pag2_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pdf page2'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    
	    ));
	    $subform->addElement($el, 'printpdf_page2'); */
	    
	    $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'print_pag1_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pdf page1'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    
	    ));
	    $subform->addElement($el, 'print_pag1_pdf');	    
	    
	    $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'preprint_pag1_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pre print page1 pdf'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    	  
	    ));
	    $subform->addElement($el, 'preprint_pag1_pdf');
	    
	    /* $el = $this->createElement('button', 'button_action', array(
	    		'type'         => 'submit',
	    		'value'        => 'preprint_pag2_pdf',
	    		// 	        'content'      => $this->translate('submit'),
	    		'label'        => $this->_page_lang['generate pre print page2 pdf'],
	    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
	    		'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	    		'decorators'   => array('ViewHelper'),
	    
	    ));
	    $subform->addElement($el, 'preprint_pag2_pdf');  */   
	    
	    return $subform;
	
	}
	
	
	public function create_form_patient_careregulationnew2020( $options = array(), $elementsBelongTo = null)
	{
	    
	    $this->clearDecorators();
	    //$this->addDecorator('HtmlTag', array('tag' => 'table'));
	    $this->addDecorator('FormElements');
	    //$this->addDecorator('Fieldset', array());
	    $this->addDecorator('Form');
	     
	    if ( ! is_null($elementsBelongTo)) {
		    $this->setOptions(array(
		        'elementsBelongTo' => $elementsBelongTo
		    ));
		}
		
		//kvheader
		/*$sf_kvhead = new Application_Form_PatientKvheader(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_phealthinsurance'		=> $this->_phealthinsurance,
				'_user'					=> $this->_user
		));
		
		$kvheader_details_form = $sf_kvhead->_create_form_kvheader();
		$this->addSubForm($kvheader_details_form, 'kvheader_details');
		
		//the top form
		$form_details = $this->_create_form_details_topform($options, $elementsBelongTo);
		$this->addSubform($form_details, 'topform_details');

		
		//the treatment care form
		$form_details = $this->_create_form_details_treatcare($options, $elementsBelongTo);
		$this->addSubform($form_details, 'treatcare_details');
		*/
		//the page1
		$form_details = $this->_create_form_details_page1($options, $elementsBelongTo);
		$this->addSubform($form_details, 'page1');
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$this->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		//the page2
		/* $form_details = $this->_create_form_details_page2($options, $elementsBelongTo);
		$this->addSubform($form_details, 'page2'); */
		
		/*//the nursing service form
		$form_details = $this->_create_form_details_nursingservice($options, $elementsBelongTo);
		$this->addSubform($form_details, 'PatientCareregulationnursingcare');
		*/
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
		
		return $this;
		
		
	}
	
	private function _create_form_details_page1($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'page1')),
		));
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		//kvheader
		$sf_kvhead = new Application_Form_PatientKvheader(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_phealthinsurance'		=> $this->_phealthinsurance,
				'_user'					=> $this->_user
		));
		
		$kvheader_details_form = $sf_kvhead->_create_form_kvheader();
		$subform->addSubForm($kvheader_details_form, 'kvheader_details');
		
		//the top form
		$form_details = $this->_create_form_details_topform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'topform_details');
		
		
		//the treatment care form
		$form_details = $this->_create_form_details_treatcare($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'treatcare_details');
		
		//the basic/home care table
		$form_details = $this->_create_form_details_basiccare($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'basiccare_details');
		
		return $subform;
		
	}
	
	private function _create_form_details_page2($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'page2')),
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$insured_name_firstname = $this->_patientMasterData['last_name'] . ', ' . $this->_patientMasterData['last_name'] . "; ";
		$insured_name_firstname .= $this->_patientMasterData['street1'] . " ";
		$insured_name_firstname .= ($this->_patientMasterData['zip'] != "" ? $this->_patientMasterData['zip'] . " " . $this->_patientMasterData['city'] : $this->_patientMasterData['city']);
		 
		$subform->addElement('textarea', 'insured_name_firstname', array(
				'value'        => $insured_name_firstname,
				'required'     => false,
				//'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty')
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div56')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'header2'),
						)
				),
				'rows' => '2'
		));
		
		$subform->addElement('text', 'health_insurance_nr', array(
				'value'        => $this->_phealthinsurance['kvnumber'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div20')),
				),
		));
		$subform->addElement('text', 'health_insurance_patient_number', array(
				'value'        => $this->_phealthinsurance['insurance_no'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div22')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'header2'),
						)
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		//the application form
		$form_details = $this->_create_form_details_application($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'application_details');
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		//the application form
		$form_details = $this->_create_form_details_information($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'information_details');
		
		return $subform;
	}
	
	private function _create_form_details_topform($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'topform')),
				array(array('cleartag' => 'HtmlTag'), array(
						'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	
		$subform->addElement('text', 'icdnumber1', array(
				'value'        => (isset($this->_diagnosis['0']) ? $this->_diagnosis['0'] : ''),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('righttoptag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'toprightform'),
						)
						
				),
				'class' => 'icdshort livesearchicd',
				'id' => 'icdnumber1'
		));
		
		$subform->addElement('text', 'icdnumber2', array(
				'value'        => (isset($this->_diagnosis['1']) ? $this->_diagnosis['1'] : ''),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
				),
				'class' => 'icdshort livesearchicd',
				'id' => 'icdnumber2'
		));
		
		$subform->addElement('text', 'icdnumber3', array(
				'value'        => (isset($this->_diagnosis['2']) ? $this->_diagnosis['2'] : ''),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
				),
				'class' => 'icdshort livesearchicd',
				'id' => 'icdnumber3'
		));
		
		$subform->addElement('text', 'icdnumber4', array(
				'value'        => (isset($this->_diagnosis['3']) ? $this->_diagnosis['3'] : ''),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
				),
				'class' => 'icdshort livesearchicd',
				'id' => 'icdnumber4'
		));
		 
		$subform->addElement('textarea', 'topform_textarea', array(
				'value'        => '',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('righttoptag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'toprightform'),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'rows' => '3'
		));
		
		$subform->addElement('Checkbox', 'reg_type_0', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div', 'class'=>'formlongdiv extratopmarg', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
						array(array('regtoptag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'regtopform'),
						)
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'reg_type_0'
		));
		
		$subform->addElement('note', 'label_dummy'.reg_type_0, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
	    						'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'formlongdiv extratopmarg', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
						
				)
		));
		
		$subform->addElement('Checkbox', 'reg_type_1', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'formlongdiv extralong extratopmarg', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND))
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'reg_type_1'
		));
		
		$subform->addElement('note', 'label_dummy'.reg_type_1, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'formlongdiv extralong extratopmarg', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
		
		
				)
		));
		
		$subform->addElement('Checkbox', 'reg_type_2', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'formshortdiv extralong extratopmarg', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'reg_type_2'
		));
		
		$subform->addElement('note', 'label_dummy'.reg_type_2, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'formshortdiv extratopmarg', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
						array(array('regtoptag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'regtopform'),
						)
		
				)
		));
		
		$subform->addElement('text', 'from', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'fromtoform'),
						)
				),
				'class' => 'decimal fromto',
				'maxlength' => '6'
		));
		
		$subform->addElement('text', 'to', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'fromtoform'),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'class' => 'decimal fromto',
				'maxlength' => '6'
		));
	
		return $subform;
	}
	
	private function _create_form_details_treatcare($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'careform'))
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad', 'style'=>'margin-top: 35px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'prep_med_0', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'prep_med')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careleft')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'prep_med_1', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careleft')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
//		$subform->addElement('text', 'prep_med_2', array(
//				'value'        => '',
//				'required'     => false,
//				'filters'      => array('StringTrim'),
//				'decorators' => array(
//						'ViewHelper',
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'class'=>'careleft'),
//						),
//				),
//		));
//
//		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
//		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
//		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'med_box', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careleft' => 'HtmlTag'), array(
						'tag' => 'div',
						'class'=>'careleft', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'med_box'
		));
		
		$subform->addElement('note', 'label_dummy'.med_box, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'careleft', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
		
		
				)
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform ipad_4'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$el_right = 1;
		
		$subform->addElement('Checkbox', 'prep_med_4', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careleft' => 'HtmlTag'), array(
						'tag' => 'div',
						'class'=>'careleft extracaremed4', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'prep_med_4'
		));
		
		$subform->addElement('note', 'label_dummy'.prep_med_4, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'careleft extracaremed4', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
		
		
				)
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'inject', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'medlongdiv extrainject',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						)
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'inject'
		));
		
		$subform->addElement('note', 'label_dummy'.inject, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'medlongdiv extrainject',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				)
		));
		
		$subform->addElement('Checkbox', 'made', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'),
						array('tag' => 'div', 'class' => 'medshortdiv extrainject', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'made'				
		));
		
		$subform->addElement('note', 'label_dummy'.made, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
						array('tag' => 'div', 'class' => 'medshortdiv extrainject', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'intram', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'),
						array('tag' => 'div', 'class' => 'medshortdiv extrainject', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'intram'
		));
		
		$subform->addElement('note', 'label_dummy'.intram, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
						array('tag' => 'div', 'class' => 'medshortdiv extrainject', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'subcut', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'),
						array('tag' => 'div', 'class' => 'enddiv extrainject', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'subcut'
		));
		
		$subform->addElement('note', 'label_dummy'.subcut, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv extrainject', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft')
								
						)
				)
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_appoint', 'style'=>'padding-top: 24px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'first_new_appoint', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'halfdiv extranewapp', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'first_new_appoint'
		));
		
		$subform->addElement('note', 'label_dummy'.first_new_appoint, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'halfdiv extranewapp', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'intens_insulint', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv extrainsul', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'intens_insulint'
		));
		
		$subform->addElement('note', 'label_dummy'.intens_insulint, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'enddiv extrainsul', 'closeOnly' => true,
									'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 6px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'label_compress', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'halfdiv')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						),
				),
		));
		
		$subform->addElement('Checkbox', 'right_side', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div15 extraright', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, )),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'right_side'
		));
		
		$subform->addElement('note', 'label_dummy'.right_side, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'div15 extraright', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'left_side', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div14 extraleft', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'left_side'
		));
		
		$subform->addElement('note', 'label_dummy'.left_side, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'div14 extraleft', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'both_sides', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'enddiv extraboth', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, )),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'both_sides'
		));
		
		$subform->addElement('note', 'label_dummy'.both_sides, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'enddiv extraboth', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						),
				),
				
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 4px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'comp_attract', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'halfdiv extraattr', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'style' => 'margin-top: -4px;')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'comp_attract'
		));
		
		$subform->addElement('note', 'label_dummy'.comp_attract, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'halfdiv extraattr', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'comp_pulloff', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'enddiv  extrapull', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'comp_pulloff'
		));
		
		$subform->addElement('note', 'label_dummy'.comp_pulloff, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'enddiv  extrapull', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'comp_incr', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'halfdiv extraincr', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'comp_incr'
		));
		
		$subform->addElement('note', 'label_dummy'.comp_incr, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'halfdiv extraincr', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('Checkbox', 'comp_decr', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'enddiv extradecr', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, )),
						
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'comp_decr'
		));
		
		$subform->addElement('note', 'label_dummy'.comp_decr, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'enddiv extradecr', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 10px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'support', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'halfdiv', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'support'
		));
		
		$subform->addElement('note', 'label_dummy'.support, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'halfdiv', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$subform->addElement('text', 'support_ext', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div43')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 25px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
//		$subform->addElement('note', 'label_wound', array(
//				'value' => '&nbsp;',
//				'decorators' => array(
//						'ViewHelper',
//						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div32')),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'openOnly' => true,
//								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft')
//						)
//				),
//		));
		
		$subform->addElement('text', 'wound_care_1', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div67')),
						array(array('careleft' => 'HtmlTag'), array(
							'tag' => 'div',
							'class'=>'careleft')
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
		
//		$subform->addElement('text', 'wound_care_2', array(
//				'value'        => '',
//				'required'     => false,
//				'filters'      => array('StringTrim'),
//				'decorators' => array(
//						'ViewHelper',
//						array(array('divtag' => 'HtmlTag'), array(
//								'tag' => 'div', 'class'=>'fulldiv')),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'class'=>'careleft'),
//						),
//				),
//		));

		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 10px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
//		$subform->addElement('Checkbox', 'wound_care_3', array(
//				'value' => '0',
//				'required'     => false,
//				'decorators' => array(
//						'ViewHelper',
//						array('Errors'),
//						array(array('divtag' => 'HtmlTag'), array(
//								'tag' => 'div', 'class'=>'div32', 'openOnly' => true,
//										'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'openOnly' => true,
//								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft')
//						)
//				),
//				'style' => 'display:none',
//				'class' => 'checkdummy',
//				'id'=> 'wound_care_3'
//		));
//
//		$subform->addElement('note', 'label_dummy'.wound_care_3, array(
//				'value' => '&nbsp;',
//				'decorators' => array(
//						'ViewHelper',
//						array('Errors'),
//						array(array('spantag' => 'HtmlTag'), array(
//								'tag' => 'span', 'class' => 'dummy')),
//						array(array('divtag' => 'HtmlTag'),
//								array('tag' => 'div', 'class' => 'div32', 'closeOnly' => true,
//										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
//				),
//		));
		
		$subform->addElement('text', 'wound_care_4', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div28')),
						array(array('careleft' => 'HtmlTag'), array(
							'tag' => 'div',
							'openOnly' => true,
							'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft')
						)
				),
		));
		
		$subform->addElement('text', 'wound_care_5', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div19')),
				),
		));
		
		$subform->addElement('text', 'wound_care_6', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div18')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft')
						)
				),
		));
		
//		$belongsto = 'frequency['.$this->_fr_index.']';
//		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
//		$subform->addSubform($form_details, $this->_fr_index);
//		$this->_fr_index++;

		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;

		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
			'FormElements',
			array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 12px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
//		$subform->addElement('Checkbox', 'wound_care_7', array(
//				'value' => '0',
//				'required'     => false,
//				'decorators' => array(
//						'ViewHelper',
//						array('Errors'),
//						array(array('divtag' => 'HtmlTag'), array(
//								'tag' => 'div', 'class'=>'div32 extrawound7', 'openOnly' => true,
//										'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'openOnly' => true,
//								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft')
//						)
//				),
//				'style' => 'display:none',
//				'class' => 'checkdummy',
//				'id'=> 'wound_care_7'
//		));
//
//		$subform->addElement('note', 'label_dummy'.wound_care_7, array(
//				'value' => '&nbsp;',
//				'decorators' => array(
//						'ViewHelper',
//						array('Errors'),
//						array(array('spantag' => 'HtmlTag'), array(
//								'tag' => 'span', 'class' => 'dummy')),
//						array(array('divtag' => 'HtmlTag'),
//								array('tag' => 'div', 'class' => 'div32 extrawound7', 'closeOnly' => true,
//										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
//				),
//		));
		
		$subform->addElement('text', 'wound_care_8', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div29')),
						array(array('careleft' => 'HtmlTag'), array(
							'tag' => 'div',
							'class'=>'careleft')
						)
				),
		));
		
//		$subform->addElement('text', 'wound_care_9', array(
//				'value'        => '',
//				'required'     => false,
//				'filters'      => array('StringTrim'),
//				'decorators' => array(
//						'ViewHelper',
//						array(array('divtag' => 'HtmlTag'), array(
//								'tag' => 'div', 'class'=>'div19')),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'closeOnly' => true,
//								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft')
//						)
//				),
//		));
		
//		$belongsto = 'frequency['.$this->_fr_index.']';
//		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
//		$subform->addSubform($form_details, $this->_fr_index);
//		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 10px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
//		$subform->addElement('text', 'wound_care_10', array(
//				'value'        => '',
//				'required'     => false,
//				'filters'      => array('StringTrim'),
//				'decorators' => array(
//						'ViewHelper',
//						array(array('divtag' => 'HtmlTag'), array(
//								'tag' => 'div', 'class'=>'fulldiv')),
//						array(array('careleft' => 'HtmlTag'), array(
//								'tag' => 'div',
//								'class'=>'careleft')
//						)
//				),
//		));

		$subform->addElement('Checkbox', 'wound_care_10', array(
			'value' => '0',
			'required'     => false,
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('divtag' => 'HtmlTag'), array(
					'tag' => 'div', 'class'=>'halfdiv extraattr', 'openOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'style' => 'margin-top: -4px;')),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'openOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
				),
			),
			'style' => 'display:none',
			'class' => 'checkdummy',
			'id'=> 'wound_care_10'
		));

		$subform->addElement('note', 'label_dummy'.wound_care_10, array(
			'value' => '&nbsp;',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('spantag' => 'HtmlTag'), array(
					'tag' => 'span', 'class' => 'dummy')),
				array(array('divtag' => 'HtmlTag'),
					array('tag' => 'div', 'class' => 'halfdiv extraattr', 'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
			),
		));

		$subform->addElement('Checkbox', 'wound_care_11', array(
			'value' => '0',
			'required'     => false,
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('divtag' => 'HtmlTag'), array(
					'tag' => 'div', 'class'=>'enddiv  extrapull', 'openOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),

			),
			'style' => 'display:none',
			'class' => 'checkdummy',
			'id'=> 'wound_care_11'
		));

		$subform->addElement('note', 'label_dummy'.wound_care_11, array(
			'value' => '&nbsp;',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('spantag' => 'HtmlTag'), array(
					'tag' => 'span', 'class' => 'dummy')),
				array(array('divtag' => 'HtmlTag'),
					array('tag' => 'div', 'class' => 'enddiv  extrapull', 'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'closeOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
				),
			),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;

		$subform->addElement('Checkbox', 'wound_care_12', array(
			'value' => '0',
			'required'     => false,
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('divtag' => 'HtmlTag'), array(
					'tag' => 'div', 'class'=>'fulldiv', 'openOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'style' => 'margin-top: -4px;')),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'openOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
				),
			),
			'style' => 'display:none',
			'class' => 'checkdummy',
			'id'=> 'wound_care_12'
		));

		$subform->addElement('note', 'label_dummy'.wound_care_12, array(
			'value' => '&nbsp;',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('spantag' => 'HtmlTag'), array(
					'tag' => 'span', 'class' => 'dummy')),
				array(array('divtag' => 'HtmlTag'),
					array('tag' => 'div', 'class' => 'fulldiv', 'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'closeOnly' => true,
					'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
				),
			),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;

		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
			'FormElements',
			array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_wound_12', 'style'=>'padding-top: 25px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;

		$subform->addElement('text', 'wound_care_13', array(
			'value'        => '',
			'required'     => false,
			'filters'      => array('StringTrim'),
			'decorators' => array(
				'ViewHelper',
				array(array('divtag' => 'HtmlTag'), array(
					'tag' => 'div', 'class'=>'fulldiv')),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'class'=>'careleft'),
				)
			),
		));

		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;

		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;

		$subform->addElement('text', 'wound_care_14', array(
			'value'        => '',
			'required'     => false,
			'filters'      => array('StringTrim'),
			'decorators' => array(
				'ViewHelper',
				array(array('divtag' => 'HtmlTag'), array(
					'tag' => 'div', 'class'=>'fulldiv')),
				array(array('careleft' => 'HtmlTag'), array(
					'tag' => 'div',
					'class'=>'careleft'),
				)
			),
		));

		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;

		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;

		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_wound_12', 'style'=>'padding-top: 20px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'wound_care_15', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'fulldiv')),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careleft'),
						)
				),
		));
		
		$subform->addElement('text', 'care_number', array(
				'value'		   => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
					array(array('divtag' => 'HtmlTag'), array(
						'tag' => 'div', 'class'=>'care_number_right')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright'),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
							
				),
		));
		
		return $subform;
	}
	
	private function _create_form_details_basiccare($options, $elementsBelongTo)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'careform'))
		));
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$subform->addElement('Checkbox', 'basic_1', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div', 'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND,
								'class'=>'careleft ipad_basic_1',),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'basic_1'
		));
		
		$subform->addElement('note', 'label_dummy'.basic_1, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'careleft', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'basic_2', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careleft', 'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND,),
						),
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'basic_2'
		));
		
		$subform->addElement('note', 'label_dummy'.basic_2, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'careleft', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'basic_3', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div59 extrabasic3', 'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND,)),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						)
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'basic_3'
		));
		
		$subform->addElement('note', 'label_dummy'.basic_3, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'div59 extrabasic3', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform pc_stamp', 'style'=>'width: 36%;'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_closediv($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'close1');
		
		$subform->addElement('select', 'userstamps', array(
				'multiOptions' => $this->_user_stamps,
				'value'		   => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				// 	        'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright'),
						),
				),
				'id' => 'stampusers_doct'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('Checkbox', 'basic_4', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div59 extrabasic4', 'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND, )),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'careleft'),
						)
				),
				'style' => 'display:none',
				'class' => 'checkdummy',
				'id'=> 'basic_4'
		));
		
		$subform->addElement('note', 'label_dummy'.basic_4, array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span', 'class' => 'dummy')),
						array(array('divtag' => 'HtmlTag'),
								array('tag' => 'div', 'class' => 'div59 extrabasic4', 'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND,)),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform', 'style'=>'width: 36%;'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_closediv($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'close2');
		
		$subform->addElement('textarea', 'basic_textarea', array(
				'value'        => '',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careleft'),
						)
				),
				'rows' => '4'
		));
		
		$subform->addElement('note', 'stamp_alert', array(
				'value'        => $this->translate("no stamp information"),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',  'class' => 'stamp_alert')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright'),
						),
				),
		));
		
		$subform->addElement('note', 'user_stamp_block', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'id' => 'user_stamp_block')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright'),
						)
				),
		));
		
		return $subform;
	}
	
	private function _create_form_details_frequencyform($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform'))
		));
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		$el_right = 1;
		//subform right elements
		$subform->addElement('text', 'right_'.$el_right, array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div15'))
		
				),
		));
		$el_right++;
		$subform->addElement('text', 'right_'.$el_right, array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div15')),
				),
		));
		$el_right++;
		$subform->addElement('text', 'right_'.$el_right, array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div15')),
				),
		));
		$el_right++;
		$subform->addElement('text', 'right_'.$el_right, array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div26')),
				),
		));
		$el_right++;
		$subform->addElement('text', 'right_'.$el_right, array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class'=>'div25'))
				),
		));
		
		return $subform;
	}
	
	private function _create_form_details_clearform($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'clear'))
		));
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		return $subform;
	}
	
	private function _create_form_details_sepform($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		return $subform;
	}
	
	private function _create_form_details_closediv($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'careleft'),
						)
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	
		return $subform;
	}
	
	private function _create_form_details_application($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'application'))
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_appl', 'style'=>'padding-top: 38px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'from', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'fromtoform'),
						)
				),
				'class' => 'decimal fromto',
				'style' => 'width: 25%;',
				'maxlength' => '6'
		));
		
		$subform->addElement('text', 'to', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'fromtoform'),
						)
				),
				'class' => 'decimal fromto',
				'style' => 'width: 25%;',
				'maxlength' => '6'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 20px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'appl_1', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_2', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div36')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_3', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 6px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'hmarg_1', array(
				'value' => '&nbsp;',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_4', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div36')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_5', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 2px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'hmarg_2', array(
				'value' => '&nbsp;',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_6', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div36')),
				),
		));
		
		$subform->addElement('Checkbox', 'appl_7', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_appl_8', 'style'=>'padding-top: 20px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'hmarg_3', array(
				'value' => '&nbsp;',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('text', 'appl_8', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 10px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'hmarg_4', array(
				'value' => '&nbsp;',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('text', 'appl_9', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 3px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('note', 'hmarg_5', array(
				'value' => '&nbsp;',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div23')),
				),
		));
		
		$subform->addElement('text', 'appl_10', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div12'),
						)
				),
				'class' => 'decimal appldec',
				'maxlength' => '5'
		));
		
		$subform->addElement('text', 'appl_11', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div51')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 12px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'appl_12', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'div63 pc_appl_stamp')),
				),
		));
		
		$subform->addElement('select', 'userstamps', array(
				'multiOptions' => $this->_user_stamps,
				'value'		   => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				// 	        'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright'),
						),
				),
				'id' => 'stampusers_repr'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('note', 'hmarg_6', array(
				'value'        => '&nbsp;',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div6')),
						array(array('stamplefttag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'stampleft'),
						)
				),
		));
		
		$subform->addElement('text', 'appl_13', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div92')),
				),
				'style' => 'margin-top: 0px;'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('note', 'hmarg_7', array(
				'value'        => '&nbsp;',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div6')),
				),
		));
		
		$subform->addElement('text', 'appl_14', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div92')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 6px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'appl_15', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 6px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('Checkbox', 'appl_16', array(
				'value' => '0',
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'enddiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_appl_data', 'style'=>'padding-top: 4px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'appl_data', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'fromtoform', 'style' => 'margin-left: 68%;'),
						),
						array(array('stamplefttag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'stampleft'),
						)
				),
				'class' => 'decimal fromto',
				'maxlength' => '6'
		));
		
		$subform->addElement('note', 'stamp_alert_repr', array(
				'value'        => $this->translate("no stamp information"),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',  'class' => 'stamp_alert_repr')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright', 'style' => 'width: 23%;'),
						),
				),
		));
		
		$subform->addElement('note', 'user_stamp_block_repr', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'id' => 'user_stamp_block_repr')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright', 'style' => 'width: 23%;'),
						)
				),
		));
		
		return $subform;
	}
	
	private function _create_form_details_information($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'information'))
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_from_to', 'style'=>'padding-top: 58px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'from', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'fromtoform'),
						)
				),
				'class' => 'decimal fromto',
				'style' => 'width: 25%;',
				'maxlength' => '6'
		));
		
		$subform->addElement('text', 'to', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'fromtoform'),
						)
				),
				'class' => 'decimal fromto',
				'style' => 'width: 25%;',
				'maxlength' => '6'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 12px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_1', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'inf_2', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_3', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63 ipad_4')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform ipad_4'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'inf_4', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63 ipad_4')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform ipad_4'))
		));
		$subform->addSubform($form_details, $this->_fr_index);		
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'inf_5', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63 ipad_4')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform ipad_4'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'inf_6', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63 ipad_4')),
				),
		));
		
		$belongsto = 'frequency['.$this->_fr_index.']';
		$form_details = $this->_create_form_details_frequencyform($options, $belongsto);
		$form_details->clearDecorators()->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'frequencyform ipad_4'))
		));
		$subform->addSubform($form_details, $this->_fr_index);
		$this->_fr_index++;
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator ipad_inf_7', 'style'=>'padding-top: 20px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_7', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 8px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;

		$subform->addElement('text', 'inf_8', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div63')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 8px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;

		$subform->addElement('text', 'inf_9', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div12'),
						)
				),
				'class' => 'decimal appldec',
				'maxlength' => '5'
		));
		
		$subform->addElement('text', 'inf_10', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div51')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 5px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_11', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div22', 'style' => 'margin-right: 41%;'),
						)
				),
				'class' => 'decimal appldec',
				'maxlength' => '9'
		));
		
		$subform->addElement('select', 'userstamps', array(
				'multiOptions' => $this->_user_stamps,
				'value'		   => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				// 	        'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright pc_inf_stamp'),
						),
				),
				'id' => 'stampusers_pfle'
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$subform->addElement('text', 'inf_12', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'fulldiv')),
						array(array('stamplefttag' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'stampleft'),
						)
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 3px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_13', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div49')),
				),
		));
		
		$subform->addElement('text', 'inf_14', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'div49')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 3px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_15', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('careleft' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'fulldiv')),
				),
		));
		
		$form_details = $this->_create_form_details_clearform($options, $elementsBelongTo);
		$subform->addSubform($form_details, 'clear'.$this->_cl_index);
		$this->_cl_index++;
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'separator', 'style'=>'padding-top: 26px;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index++);
		$this->_sp_index++;
		
		$subform->addElement('text', 'inf_data', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fromttoag' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'fromtoform', 'style' => 'margin-left: 68%;'),
						),
						array(array('stamplefttag' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'stampleft'),
						)
				),
				'class' => 'decimal fromto',
				'maxlength' => '6'
		));
		
		$subform->addElement('note', 'stamp_alert_pfle', array(
				'value'        => $this->translate("no stamp information"),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div',  'class' => 'stamp_alert_pfle')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright', 'style' => 'width: 23%;'),
						),
				),
		));
		
		$subform->addElement('note', 'user_stamp_block_pfle', array(
				'value'        => '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('spantag' => 'HtmlTag'), array(
								'tag' => 'span')),
						array(array('divtag' => 'HtmlTag'), array(
								'tag' => 'div', 'id' => 'user_stamp_block_pfle')),
						array(array('careright' => 'HtmlTag'), array(
								'tag' => 'div',
								'class'=>'careright', 'style' => 'width: 23%;'),
						)
				),
		));
		
		return $subform;
	}
	
}

