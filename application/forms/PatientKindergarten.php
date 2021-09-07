<?php
/**
 * @author carmen
 * ISPC-2672 21.10.2020
 *
 */
class Application_Form_PatientKindergarten extends Pms_Form
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
            array( "label" => $this->translate('name_of_kindergarten'), "cols" => array("name_of_kindergarten")),
            //array( "label" => $this->translate('type_of_kindergarten'), "cols" => array("type_of_kindergarten")),
            //array( "label" => $this->translate('contactperson'), "cols" => array("first_name","last_name", "function")),
            array( "label" => $this->translate('contactperson'), "cols" => array("contactperson", "function")),
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
    public function create_form_patient_kindergarten($options =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_patient_kindergarten");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('PatientKindergarten_legend'));
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
        
        $subform->addElement('text', 'name_of_kindergarten', array(
        		'value'        => $options['name_of_kindergarten'] ,
        		'label'        => $this->translate('name_of_kindergarten'),
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
        
        $subform->addElement('select', 'type_of_kindergarten', array(
        		'label' 	   => self::translate('type_of_kindergarten'),
        		'multiOptions' => $this->getColumnMapping('type_of_kindergarten'),
        		'value'        => $options['type_of_kindergarten'],
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
        
        $subform->addElement('text', 'contactperson', array(
        		'value'        => $options['contactperson'] ,
        		'label'        => $this->translate('kindergarten_contactperson'),
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
        
        /* $subform->addElement('text', 'last_name', array(
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
        )); */
        
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
        
        $subsub = new Zend_Form_SubForm();
        $subsub->removeDecorator('DtDdWrapper');
        $subsub->removeDecorator('Fieldset');
      
        $this->__setElementsBelongTo($subsub, "last_visit");
       
        $subsub->addElement('multiCheckbox', 'last_visit_type', array(
        		'multiOptions' => $this->getColumnMapping('last_visit_type'),
        		'value'        => $options['last_visit_type'],
        		'label'        => self::translate('kindergarten_last_visit'),
        		'required'   => false,
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        		'onChange' => 'if ($(this).val() == "date_field") {if($(this).is(":checked")) { $(".visit_date", $(this).parents("table")).show();} else {$(".visit_date", $(this).parents("table")).hide(); $("#visit_date").val("");}}
        					   if ($(this).val() == "other") {if($(this).is(":checked")) { $(".visit_other", $(this).parents("table")).show();} else {$(".visit_other", $(this).parents("table")).hide(); $("#visit_other").val("");}}',
        ));
        
        $display = (!in_array('date_field', $options['last_visit_type'])) ? 'display:none' : null;
        $subsub->addElement('text', 'last_visit_date', array(
        		'label'        => self::translate('kindergarten_last_visit_date'),
        		'value'        => (! empty($options['last_visit_date']) && $options['last_visit_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($options['last_visit_date'])) : '',
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'class'        => 'date option_date',
        		'id' => 'visit_date',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'visit_date', 'style' => $display)),
        		),
        
        ));
        
        $display = (!in_array('other', $options['last_visit_type'])) ? 'display:none' : '';
        $subsub->addElement('text', 'last_visit_other', array(
        		'value'        => $options['last_visit_other'],
        		'label'        => $this->translate('kindergarten_last_visit_other'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'id' => 'visit_other',
        		'validators'   => array('NotEmpty'),
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'visit_other', 'style' => $display )),
        		),
        ));
        $subform->addSubForm($subsub, 'last_visit');
        
        $subsub = new Zend_Form_SubForm();
        $subsub->removeDecorator('DtDdWrapper');
        $subsub->removeDecorator('Fieldset');
        $this->__setElementsBelongTo($subsub, "picked_up_brought_home");
        
        $subsub->addElement('radio', 'picked_up_brought_home_yes_no', array(
        		'label' 	   => self::translate('kindergarten_picked_up_brought_home'),
        		'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
        		'value'        => $options['picked_up_brought_home_yes_no'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'separator'  => '&nbsp;',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        		'onChange' => 'if ($(this).val() == "yes") { $(".picked_up_brought_home_other", $(this).parents("table")).show();} else {$(".picked_up_brought_home_other", $(this).parents("table")).hide(); $("#picked_up_brought_home_other").val("");}',
        ));
        
        $display = ($options['picked_up_brought_home_yes_no'] != 'yes') ? 'display:none' : null;
        $subsub->addElement('text', 'picked_up_brought_home_other', array(
        		'value'        => $options['picked_up_brought_home_other'],
        		'label'        => $this->translate('kindergarten_picked_up_brought_home_other'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'id' => 'picked_up_brought_home_other',
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'picked_up_brought_home_other', 'style' => $display )),
        		),
        ));
        $subform->addSubForm($subsub, 'picked_up_brought_home');
        
        $subsub = new Zend_Form_SubForm();
        $subsub->removeDecorator('DtDdWrapper');
        $subsub->removeDecorator('Fieldset');
        $this->__setElementsBelongTo($subsub, "accompaniment_required");
        
        $subsub->addElement('radio', 'accompaniment_required_yes_no', array(
        		'label' 	   => self::translate('kindergarten_accompaniment_required'),
        		'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
        		'value'        => $options['accompaniment_required_yes_no'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'separator'  => '&nbsp;',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        		'onChange' => 'if ($(this).val() == "yes") { $(".accompaniment_required_other", $(this).parents("table")).show();} else {$(".accompaniment_required_other", $(this).parents("table")).hide(); $("#accompaniment_required_other").val("");}',
        ));
        
        $display = ($options['accompaniment_required_yes_no'] != 'yes') ? 'display:none' : null;
        $subsub->addElement('text', 'accompaniment_required_other', array(
        		'value'        => $options['accompaniment_required_other'],
        		'label'        => $this->translate('kindergarten_accompaniment_required_other'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'id' => 'accompaniment_required_other',
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'accompaniment_required_other', 'style' => $display )),
        		),
        ));
        $subform->addSubForm($subsub, 'accompaniment_required');
        
        $subsub = new Zend_Form_SubForm();
        $subsub->removeDecorator('DtDdWrapper');
        $subsub->removeDecorator('Fieldset');
        $this->__setElementsBelongTo($subsub, "accompaniment_required_in_kindergarten");
        
        $subsub->addElement('radio', 'accompaniment_required_in_kindergarten_yes_no', array(
        		'label' 	   => self::translate('kindergarten_accompaniment_required_in_kindergarten'),
        		'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
        		'value'        => $options['accompaniment_required_in_kindergarten_yes_no'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        	   //'separator'  => '&nbsp;',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        		'onChange' => 'if ($(this).val() == "yes") { $(".accompaniment_required_in_kindergarten_other", $(this).parents("table")).show();} else {$(".accompaniment_required_in_kindergarten_other", $(this).parents("table")).hide(); $("#accompaniment_required_in_kindergarten_other").val("");}',
        ));
        
        $display = ($options['accompaniment_required_in_kindergarten_yes_no'] != 'yes') ? 'display:none' : null;
        $subsub->addElement('text', 'accompaniment_required_in_kindergarten_other', array(
        		'value'        => $options['accompaniment_required_in_kindergarten_other'],
        		'label'        => $this->translate('kindergarten_accompaniment_required_in_kindergarten_other'),
        		'required'   => false,
        		'validators'   => array('NotEmpty'),
        		'filters'    => array('StringTrim'),
        		'id' => 'accompaniment_required_in_kindergarten_other',
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'accompaniment_required_in_kindergarten_other', 'style' => $display )),
        		),
        ));
        $subform->addSubForm($subsub, 'accompaniment_required_in_kindergarten');
        
        $subsub = new Zend_Form_SubForm();
        $subsub->removeDecorator('DtDdWrapper');
        $subsub->removeDecorator('Fieldset');
        $this->__setElementsBelongTo($subsub, "aids_available_in_kindergarten");
        
        $subsub->addElement('radio', 'aids_available_in_kindergarten_yes_no', array(
        		'label' 	   => self::translate('kindergarten_aids_available_in_kindergarten'),
        		'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
        		'value'        => $options['aids_available_in_kindergarten_yes_no'],
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		//'separator'  => '&nbsp;',
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
        		),
        		'onChange' => 'if ($(this).val() == "yes") { $(".aids_available_in_kindergarten_other", $(this).parents("table")).show();} else {$(".aids_available_in_kindergarten_other", $(this).parents("table")).hide(); $("#aids_available_in_kindergarten_other").val("");}',
        ));
        
        $display = ($options['aids_available_in_kindergarten_yes_no'] != 'yes') ? 'display:none' : null;
        $subsub->addElement('text', 'aids_available_in_kindergarten_other', array(
        		'value'        => $options['aids_available_in_kindergarten_other'],
        		'label'        => $this->translate('kindergarten_aids_available_in_kindergarten_other'),
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'validators'   => array('NotEmpty'),
        		'id' => 'aids_available_in_kindergarten_other',
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				array('Label', array('tag' => 'td','tagClass'=>'w100' )),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_available_in_kindergarten_other', 'style' => $display )),
        		),
        ));
        $subform->addSubForm($subsub, 'aids_available_in_kindergarten');
        
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
    
    
    public function save_form_patient_kindergarten($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);

	    $r = PatientKindergartenTable::getInstance()->findOrCreateOneBy( ['id', 'ipid'], [$data['id'], $ipid], $data );
	    	    
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
		
		if( $fieldName == 'type_of_kindergarten')
		{
			$values_empty[''] = self::translate('select'); //ISPC-266 carmen 31.08.2020
			$values = $values_empty+ $values;
		}
		
		if( $fieldName == 'last_visit_type')
		{
			$values = array(
					'date_field' => self::translate('kindergarten_date_field'),
					'regularly' => self::translate('kindergarten_regularly'),
					'irregular' => self::translate('kindergarten_irregular'),
					'unknown' => self::translate('kindergarten_unknown'),
					'other' => self::translate('kindergarten_other')
			);
		}
		
		return $values;
	
	}
    
    
    
}


?>