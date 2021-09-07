<?php
/**
 * 
 * @author claudiu
 * 
 * 08.01.2018
 *
 */
class Application_Form_PatientSavoir extends Pms_Form
{
	
	private $triggerformid = PatientSavoir::TRIGGER_FORMID;
	private $triggerformname = PatientSavoir::TRIGGER_FORMNAME;
	protected $_translate_lang_array = PatientSavoir::LANGUAGE_ARRAY;
	
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
	    $subform->addElement($el, 'save');
	    
	    
	    return $subform;
	
	}
	
	
	public function create_form_patient_savoir( $options = array(), $elementsBelongTo = null)
	{
	    
	    $this->clearDecorators();
	    $this->addDecorator('HtmlTag', array('tag' => 'table'));
	    $this->addDecorator('FormElements');
	    $this->addDecorator('Fieldset', array('legend'=>$this->translate('Savoir Formular')));
	    $this->addDecorator('Form');
	     
	    if ( ! is_null($elementsBelongTo)) {
		    $this->setOptions(array(
		        'elementsBelongTo' => $elementsBelongTo
		    ));
		}

		//the top table
		$form_details = $this->_create_form_details($options, $elementsBelongTo);
		$this->addSubform($form_details, 'PatientSavoir');
		
		
		//create form that will list the sapv table like in patient stammdaten
		if ( ! empty($options['SapvVerordnung'])) {
    		$af = new Application_Form_PatientSavoirSapv();
    		$fn_name = 'create_form_patient_savoir_sapv';
    		$this->addSubform( call_user_func_array(array($af, $fn_name), array($options, $elementsBelongTo)), 'PatientSavoirSapv');
		}
		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
		
		return $this;
		
		
	}

	
	
	
	private function _create_form_details($options = array(), $elementsBelongTo = null)
	{
	
	    $subform = $this->subFormTable(array(
	        'columns' => null,
	        'class' => 'PatientSavoirTable',
	    ));
	    $subform->removeDecorator('Fieldset');
	    $subform->setAttrib("class", "label_same_size_auto");
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    $subform->addElement('note', 'label_row_consent_A', array(
		    'value' => $this->translate('consent A'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),
		));
	    
		$subform->addElement('radio', 'consent_A', array(
		    'multiOptions' => PatientSavoir::getDefaults('consent_A'),
		    'value'        => $options['consent_A'],
		    'required'     => false,
// 		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
// 		    'class'        => 'date formular_date',
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
// 		        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		     
		));
		
		
		if (is_null($this->_clientModules)) {
		    $modules =  new Modules();
		    $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
		}
		
		if ($this->_clientModules['159']) {
    		$subform->addElement('note', 'label_row_consent_B', array(
    		    'value' => $this->translate('consent B'),
    		    'decorators' => array(
    		        'ViewHelper',
    		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
    		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    		    ),
    		));
    		//$subform->addElement('radio', 'consent_B', array(
    		$subform->addElement('select', 'consent_B', array(
    		    'multiOptions' => PatientSavoir::getDefaults('consent_B'),
    		    'value'        => $options['consent_B'],
    		    'required'     => false,
    		    'filters'      => array('StringTrim'),
//     		    'validators'   => array('NotEmpty'),
    		    'decorators' => array(
    		        'ViewHelper',
    		        array('Errors'),
    		        // 		        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    		    ),
    		    'separator' => PHP_EOL,
    		));
		}
		
		
		$subform->addElement('note', 'label_row_consent_C', array(
		    'value' => $this->translate('consent C'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('radio', 'consent_C', array(
		    'multiOptions' => PatientSavoir::getDefaults('consent_C'),
		    'value'        => $options['consent_C'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        // 		        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		     
		));
		
		
		$subform->addElement('note', 'label_school_education', array(
		    'value' => $this->translate('school education'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('select', 'school_education', array(
		    'multiOptions' => PatientSavoir::getDefaults('school_education'),
		    'value'        => $options['school_education'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL, 
		));
		
		$subform->addElement('note', 'label_working_status', array(
		    'value' => $this->translate('working status'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('select', 'working_status', array(
		    'multiOptions' => PatientSavoir::getDefaults('working_status'),
		    'value'        => $options['working_status'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));
		
		$subform->addElement('note', 'label_job', array(
		    'value' => $this->translate('job'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('select', 'job', array(
		    'multiOptions' => PatientSavoir::getDefaults('job'),
		    'value'        => $options['job'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));

		$subform->addElement('note', 'label_countryofbirth', array(
		    'value' => $this->translate('country of birth'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('text', 'countryofbirth', array(
		    'value'        => $options['countryofbirth'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));

		$subform->addElement('note', 'label_countryofbirth_mother', array(
		    'value' => $this->translate('country of birth mother'),
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('text', 'countryofbirth_mother', array(
		    'value'        => $options['countryofbirth_mother'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));

		$subform->addElement('note', 'label_countryofbirth_father', array(
		    'value' => $this->translate('country of birth father') . "",
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('text', 'countryofbirth_father', array(
		    'value'        => $options['countryofbirth_father'],
		    'required'     => false,
		    'filters'      => array('StringTrim'),
// 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));
	
		

		$subform->addElement('note', 'label_rule_approach', array(
		    'value' => $this->translate('Rule Approach') . "",
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('text', 'rule_approach', array(
		    'value'        => $options['rule_approach'],
		    'required'     => false,
		    'filters'      => array('StringTrim', 'Digits'),
// 		    'validators'   => array('Digits'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		    'class' => 'mask_number',
		    'onkeyup' => "this.value = this.value.replace(/\D/g, '')",
		    'pattern' => "[0-9]*",
		));
		
		$subform->addElement('note', 'label_rule_arrival_time', array(
		    'value' => $this->translate('Rule arrival time') . "",
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));
		$subform->addElement('text', 'rule_arrival_time', array(
		    'value'        => $options['rule_arrival_time'],
		    'required'     => false,
		    'filters'      => array('StringTrim' , 'Digits'),
// 		    'validators'   => array('Digits'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		    'class' => 'mask_number',
		    'onkeyup' => "this.value = this.value.replace(/\D/g, '')",
		    'pattern' => "[0-9]*",
		));
		
		$subform->addElement('note', 'label_first_assessment_carried_by', array(
		    'value' => $this->translate('First Assessment carried out by') . "",
		    'decorators' => array(
		        'ViewHelper',
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    ),
		));

// 		"by a professional group alone" => "durch eine Berufsgruppe allein",
// 		"by at least 2 professional groups" => "durch mindestens 2 Berufsgruppen"
		
		$subform->addElement('select', 'first_assessment_carried_by', array(
		    'multiOptions' => PatientSavoir::getDefaults('first_assessment_carried_by'),
		    'value'        => $options['first_assessment_carried_by'],
		    'required'     => false,
		    'filters'      => array('StringTrim', 'Digits'),
		    // 		    'validators'   => array('NotEmpty'),
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    ),
		    'separator' => PHP_EOL,
		));
		
		
		
	    return $subform;
	}
	
	
	
	
	/**
	 * 
	 * @param unknown $ipid
	 * @param unknown $data
	 * @throws Exception
	 * @return NULL|Doctrine_Record
	 */
	public function save_form_patient_savoir($ipid = null, array $data = array())
	{ 
	    if (empty($ipid)) {
	        throw new Exception('Contact Admin, formular cannot be saved.', 0);
	    }
	    
	    $patientSavoir = null;
	    
	    //formular will be saved first so we have a id
	    if ( ! empty($data['PatientSavoir'])) {
	        
	        $data['PatientSavoir']['ipid'] = $ipid;
	        $entity  = new PatientSavoir();
	        $patientSavoir =  $entity->findOrCreateOneBy('id', null, $data['PatientSavoir']);
	        
            if ( ! $patientSavoir->id) {
                
                throw new Exception('Contact Admin, formular cannot be saved.', 1);
                return null;//we cannot save... contact admin
                
            } else {
                
                /*
                 * delete all the older formulars
                 * this should be a commitTransactions... but user whould still view the old data.. so on save what do we do?
                 * cascade= delete not working properly with softdelete... need to update the listener first
                 *
                 * 
                 */
    
                $patsav_del = $patientSavoir->getTable()->createQuery()
                ->select('id')
                ->where('ipid = ?', $ipid)
                ->andWhere('id != ?', $patientSavoir->id)               
                ->execute();
               
                if($patsav_del->count()) {
                    $patsav_del_ids = array_column($patsav_del->toArray(), 'id');
                    
                    //delete associated PatientSavoirSapv
                    $savoir = new PatientSavoirSapv();
                    $q = $savoir->getTable()->createQuery()
                    ->delete()
                    ->where('ipid = ?', $ipid)
                    ->andWhereIn('patient_savoir_id', $patsav_del_ids)
                    ->execute();
                    
                    $patsav_del->delete();
                }
               
               
                
                //save PatientSavoirSapv
                if ( ! empty($data['PatientSavoirSapv'])) {
    
                    //append id
                    foreach ($data['PatientSavoirSapv'] as &$sapv) {
                        $sapv['patient_savoir_id'] = $patientSavoir->id;
                    }
                    
                    $af = new Application_Form_PatientSavoirSapv();
                    $af->save_form_patient_savoir_sapv($ipid, $data['PatientSavoirSapv']);
                     
                }
                
            }
	    } else {
	        //nothing to save... you should not be here
	        throw new Exception('Contact Admin, empty formular cannot be saved.', 0);
	    }
        

        return $patientSavoir;
    }
	
	
	
}

