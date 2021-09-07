<?php
/**
 * @author carmen
 * ISPC-2672 26.10.2020
 *
 */
class Application_Form_PatientYouthWelfareOffice extends Pms_Form
{
	
	public function __construct($options = null)
	{
		
		parent::__construct($options);
	}
	
	public function isValid($data)
	{
			
		return parent::isValid($data);
	}
    
    public function getVersorgerExtract()
    {
            return array(
            array( "label" => $this->translate('name_of_children_hospice_service'), "cols" => array("name_of_children_hospice_service")),
            array( "label" => $this->translate('contactperson'), "cols" => array("first_name","last_name", "function")),
            array( "label" => $this->translate('phone'), "cols" => array("phone") ),
            array( "label" => $this->translate('phonefax'), "cols" => array("phonefax")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("street"), array("number")),
            array(array("zip"), array("city")),
        );
    }
    
    
    
    
    
    /**
     *
     *
     * @param array $options, optional values to populate the form
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_youth_welfare_office($options =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_patient_youth_welfare_office");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('PatientYouthWelfareOffice_legend'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
     
        $subform->addElement('hidden', 'id', array(
        		'value'        => $options['id'] ? $options['id'] : '' ,
        		'required'     => false,
        		'label'        => null,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
        
        		),
        ));
        
        $subform->addElement('text', 'name_of_youth_welfare_office', array(
        		'value'        => $options['name_of_youth_welfare_office'] ,
        		'label'        => $this->translate('name_of_youth_welfare_office'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'department_of_youth_welfare_office', array(
        		'value'        => $options['department_of_youth_welfare_office'] ,
        		'label'        => $this->translate('department_of_youth_welfare_office'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'last_name', array(
        		'value'        => $options['last_name'] ,
        		'label'        => $this->translate('Name'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'first_name', array(
        		'value'        => $options['first_name'] ,
        		'label'        => $this->translate('firstname'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'contactperson', array(
        		'value'        => $options['contactperson'] ,
        		'label'        => $this->translate('youth_contactperson'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'function', array(
        		'value'        => $options['function'] ,
        		'label'        => $this->translate('function'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        $subform->addElement('text', 'street', array(
        		'value'        => $options['street'] ,
        		'label'        => $this->translate('street'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'number', array(
        		'value'        => $options['number'] ,
        		'label'        => $this->translate('Hausnummer'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'zip', array(
        		'value'        => $options['zip'] ,
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
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'city', array(
        		'value'        => $options['city'],
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
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        $subform->addElement('text', 'phone', array(
        		'value'        => $options['phone'],
        		'label'        => $this->translate('Telefon 1'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'phone2', array(
        		'value'        => $options['phone2'],
        		'label'        => $this->translate('Telefon 2'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'phonefax', array(
        		'value'        => $options['phonefax'],
        		'label'        => $this->translate('phonefax'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		// 	        'validators'   => array('NotEmpty', 'EmailAddress'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'email', array(
        		'value'        => $options['email'],
        		'label'        => $this->translate('email'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'validators' => array('EmailAddress'),
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        $subform->addElement('text', 'zip_mailbox', array(
        		'value'        => $options['zip_mailbox'],
        		'label'        => $this->translate('mailbox'),
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
       
        $subform->addElement('text', 'description', array(
        		'value'        => $options['description'],
        		'label'        => $this->translate('description_care_ins'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        ));
        
        return $this->filter_by_block_name($subform, $__fnName);
    
    }
    
    
    public function save_form_patient_youth_welfare_office($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);

	    $r = PatientYouthWelfareOfficeTable::getInstance()->findOrCreateOneBy( ['id', 'ipid'], [$data['id'], $ipid], $data );
	    	    
	    return $r;
	    
	}
    
	public function getColumnMapping($fieldName, $revers = false)
	{
		$overwriteMapping = [
		//             'option_status' => array('ok' => 'in Ordnung', 'not ok' => 'Nicht in Ordnung')
		];
		
		$values = PatientKindergartenTable::getInstance()->getEnumValues($fieldName);
		
		
		$values = array_combine($values, array_map("self::translate", $values));
		
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName] + $values;
		}
		
		return $values;
	
	}
    
    
    
}


?>