<?php
/**
 * 
 * @author carmen
 * 
 * 13.06.2018
 * ISPC-2370
 */
class Application_Form_PatientKvheader extends Pms_Form
{
	protected $_phealthinsurance = null;
	
	protected $_user = null;
	
	protected $_kvheader_lang = null;
	
	protected $_elementsBelongTo = null;
	
	public function __construct($options = null)
	{		
		if (isset($options['_phealthinsurance'])) {
			$this->_phealthinsurance = $options['_phealthinsurance'];
			unset($options['_phealthinsurance']);
		}
		
		if (isset($options['_user'])) {
			$this->_user = $options['_user'];
			unset($options['_user']);
		}
		if (isset($options['elementsBelongTo'])) {
			$this->_elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
		parent::__construct($options);
		
		$this->_kvheader_lang = $this->translate ('kvheader_lang');

	}	
	
	public function isValid($data)
	{
	    
	    return parent::isValid($data);
	    
	}	
	
	public function _create_form_kvheader($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('Fieldset');
	    $subform->setDecorators( array(
	    		'FormElements',
	    		array('HtmlTag', array('tag'=>'div', 'class'=>'kvheaderform'))
	    ));
	   
	    if($this->_elementsBelongTo)
	    {
	    	$this->__setElementsBelongTo($subform, $this->_elementsBelongTo );
	    }
		else if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $subform->addElement('note', 'label_pheathinsurance_name', array(
		    'value' => $this->_kvheader_lang['phealthinsurance_name'],
		    'decorators' => array(
		        'ViewHelper',
		    	array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),	
	        ),
		));
	    
	    $subform->addElement('text', 'phealthinsurance_name', array(
	    		'value'        => $this->_phealthinsurance['company_name'],
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		// 		    'validators'   => array('NotEmpty'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array('Errors'),
	    				array(array('innerline' => 'HtmlTag'), array(
	    						'tag' => 'div', 'class'=>'innerline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	    		
	    		),
				'class' => 'insurance'
	    ));
	    
	    $subform->addElement('note', 'label_insured_name_firstname', array(
	    		'value' => $this->_kvheader_lang['insured_name_firstname'],
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'openOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formleftdiv'))
	    				,
	    		),
	    		
	    		
	    		
	    	
	    ));
	    
// 	    $insured_name_firstname = $this->_patientMasterData['last_name'] . ', ' . $this->_patientMasterData['last_name'] . "\n";
	    $insured_name_firstname = $this->_patientMasterData['last_name'] . ', ' . $this->_patientMasterData['first_name'] . "\n"; // /ISPC-2370 Ancuta ( comment from 04.11.2019 )
	    $insured_name_firstname .= $this->_patientMasterData['street1'] . "\n";
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
		    				'closeOnly' => true,
		    				'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formleftdiv'))
	    		),
	    		'rows' => '3'
	    ));
	    
	    $subform->addElement('note', 'label_born_on', array(
	    		'value' => $this->_kvheader_lang['born_on'],
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('ltag' => 'HtmlTag'), array('tag' => 'label', 'class' => 'label_born_on')),
	    				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'openOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formrightdiv'))
	    		),
	    ));
	    
	     $subform->addElement('text', 'born_on', array(
	       		'value'        => $this->_patientMasterData['birthd'],
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		// 		    'validators'   => array('NotEmpty'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array('Errors'),
	    				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formlongdiv')),
	    				array(array('cleartag' => 'HtmlTag'), array(
	    						'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
	    				array(array('innerline' => 'HtmlTag'), array(
	    						'tag' => 'div', 'class'=>'innerline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	    		),
	     		'class' => 'formular_date'
	    ));
	    
	     $subform->addElement('note', 'label_health_insurance_nr', array(
	     		'value' => $this->_kvheader_lang['health_insurance_nr'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'openOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formlongdiv'))
	     		),
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
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formlongdiv')),
	     				array(array('verline' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'verline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	     		),
	     ));
	     
	     $subform->addElement('note', 'label_health_insurance_patient_number', array(
	     		'value' => $this->_kvheader_lang['health_insurance_patient_number'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'openOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formlongdiv pat_number'))
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
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formlongdiv')),
	     				array(array('verline' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'verline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	     		),
	     ));
	     
	     $subform->addElement('note', 'label_health_insurance_patient_status', array(
	     		'value' => $this->_kvheader_lang['health_insurance_patient_status'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	     						'tag' => 'div',
	     						'openOnly' => true,
	     						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formshortdiv stat'))
	     				),
	     ));
	     
	     $subform->addElement('text', 'health_insurance_patient_status', array(
	     		'value'        => $this->_phealthinsurance['insurance_status'],
	     		'required'     => false,
	     		'filters'      => array('StringTrim'),
	     		// 		    'validators'   => array('NotEmpty'),
	     		'decorators' => array(
	     				'ViewHelper',
	     				array('Errors'),
	     				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formshortdiv')),
	     				array(array('cleartag' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'clear', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
	     				array(array('innerline' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'innerline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	     		),
	     ));
	    
	     $subform->addElement('note', 'label_stamp_user_bsnr', array(
	     		'value' => $this->_kvheader_lang['stamp_user_bsnr'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	     						'tag' => 'div',
	     						'openOnly' => true,
	     						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formlongdiv'))
	     				),
	     ));
	     
	     $subform->addElement('text', 'stamp_user_bsnr', array(
	     		'value'        => $this->_user[$this->logininfo->userid]['businessnr'],
	     		'required'     => false,
	     		'filters'      => array('StringTrim'),
	     		// 		    'validators'   => array('NotEmpty'),
	     		'decorators' => array(
	     				'ViewHelper',
	     				array('Errors'),
	     				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formlongdiv')),
	     				array(array('verline' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'verline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	     		),
	     		'id' => 'stamp_user_bsnr'
	     ));
	     
	     $subform->addElement('note', 'label_stamp_user_lanr', array(
	     		'value' => $this->_kvheader_lang['stamp_user_lanr'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	     						'tag' => 'div',
	     						'openOnly' => true,
	     						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formlongdiv'))
	     				),
	     ));
	     
	     $subform->addElement('text', 'stamp_user_lanr', array(
	     		'value'        => $this->_user[$this->logininfo->userid]['doctornr'],
	     		'required'     => false,
	     		'filters'      => array('StringTrim'),
	     		// 		    'validators'   => array('NotEmpty'),
	     		'decorators' => array(
	     				'ViewHelper',
	     				array('Errors'),
	     				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formlongdiv')),
	     				array(array('verline' => 'HtmlTag'), array(
	     						'tag' => 'div', 'class'=>'verline', 'placement' => Zend_Form_Decorator_Abstract::APPEND))
	     		),
	     		'id' => 'stamp_user_lanr'
	     ));
	     
	     $subform->addElement('note', 'label_formular_date', array(
	     		'value' => $this->_kvheader_lang['formular_date'],
	     		'decorators' => array(
	     				'ViewHelper',
	     				array(array('ltag' => 'HtmlTag'), array('tag' => 'label')),
	    				array(array('divtag' => 'HtmlTag'), array(
	     						'tag' => 'div',
	     						'openOnly' => true,
	     						'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'formshortdiv'))
	     				),
	     ));
	     
	     $subform->addElement('text', 'formular_date', array(
	     		'value'        => date('d.m.Y'),
	     		'required'     => false,
	     		'filters'      => array('StringTrim'),
	     		// 		    'validators'   => array('NotEmpty'),
	     		'decorators' => array(
	     				'ViewHelper',
	     				array('Errors'),
	     				array(array('divtag' => 'HtmlTag'), array(
	    						'tag' => 'div',
	    						'closeOnly' => true,
	    						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'formshortdiv'))
	     		),
	     		'class' => 'date formular_date'
	     ));
		
	    return $subform;
	}
	
}

