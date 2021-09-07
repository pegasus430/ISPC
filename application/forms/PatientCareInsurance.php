<?php
//ISPC-2667 Lore 21.09.2020

require_once("Pms/Form.php");

class Application_Form_PatientCareInsurance extends Pms_Form
{
    protected $_model = 'PatientCareInsurance';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('kind_of_insurance'), "cols" => array("kind_of_insurance_x")),
            array( "label" => $this->translate('name'), "cols" => array("first_name","last_name")),
            array( "label" => $this->translate('name_of_insurer'), "cols" => array("name_of_insurer")),
            array( "label" => $this->translate('phone'), "cols" => array("phone") ),
            array( "label" => $this->translate('phonefax'), "cols" => array("phonefax")),
        );
    }

    
    public function getVersorgerAddress()
    {
/*         return array(
            array(array("first_name")),
            array(array("last_name")),
            array(array("name_of_insurer")),
            array(array("street")),
            array(array('zip'), array("city")),
        ); */
    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientCareInsurance';
    
	
    public function create_form_block_patient_ci($options =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_care_insurance");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_ci');
        $subform->setAttrib("class", "label_same_size {$__fnName}");
        
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        $subform->addElement('hidden', 'id', array(
            'value'        => $options['id'] ? $options['id'] : 0 ,
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
        
        $subform->addElement('note',  "kind_of_insurance", array(
            'value' => $this->translate("kind_of_insurance"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'font-weight: bold', 'colspan'=>'2')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'separator' => PHP_EOL,
        ));
        
        $subform->addElement('checkbox', 'kind_ins_legally', array(
            'label'      => $this->translate('kind_ins_legally') ,
            'value'      => $options['kind_ins_legally'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'w100' , 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
        ));
        $subform->addElement('checkbox', 'kind_ins_private', array(
            'label'      => $this->translate('kind_ins_private') ,
            'value'      => $options['kind_ins_private'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
        ));
        $subform->addElement('checkbox', 'kind_ins_no', array(
            'label'      => $this->translate('kind_ins_no') ,
            'value'      => $options['kind_ins_no'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
        ));
        
        $subform->addElement('checkbox', 'kind_ins_others', array(
            'label'      => $this->translate('kind_ins_others') ,
            'value'      => $options['kind_ins_others'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => "if($(this).is(':checked')) { $('.kind_ins_others_valid', $(this).parents('table')).show();} else { $('.kind_ins_others_valid', $(this).parents('table')).hide();} ",
            'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
        ));
        
        $display_kind_ins_others = $options['kind_ins_others'] != 1 ? 'display:none' : '';
        
        $subform->addElement('text', 'kind_ins_others_text', array(
            'label'      => '' ,
            'value'      => $options['kind_ins_others_text'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'kind_ins_others_valid', 'style' => $display_kind_ins_others)),
            ),
            'class'=>'w100percent'
        ));
        
        
        $show_kind_ins_extrafields = '';
        if ($options['kind_ins_legally'] == 0 && $options['kind_ins_private'] == 0 && $options['kind_ins_no'] == 0 && $options['kind_ins_others'] == 0){
            $show_kind_ins_extrafields = 'display:none';
        }
        
        $subform->addElement('note',  "power", array(
            'value' => $this->translate("power"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'font-weight: bold', 'colspan'=>'2')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
            ),
            'separator' => PHP_EOL
        ));
        
        $subform->addElement('checkbox', 'power_ins_daily_care', array(
            'label'      => $this->translate('power_ins_daily_care') ,
            'value'      => $options['power_ins_daily_care'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'w100' , 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
            ),
        ));
        $subform->addElement('checkbox', 'power_ins_nursing_pension', array(
            'label'      => $this->translate('power_ins_nursing_pension') ,
            'value'      => $options['power_ins_nursing_pension'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
            ),
        ));
        $subform->addElement('checkbox', 'power_ins_nursing_care', array(
            'label'      => $this->translate('power_ins_nursing_care') ,
            'value'      => $options['power_ins_nursing_care'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 110px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
            ),
        ));
        
        $subform->addElement('checkbox', 'power_ins_others', array(
            'label'      => $this->translate('power_ins_others') ,
            'value'      => $options['power_ins_others'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 70px;width: 125px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
            ),
            'onChange' => "if($(this).is(':checked')) { $('.power_ins_others_valid', $(this).parents('table')).show();} else { $('.power_ins_others_valid', $(this).parents('table')).hide();} ",
        ));
        
        $display_power_ins_others = $options['power_ins_others'] != 1 ? 'display:none' : '';
        
        $subform->addElement('text', 'power_ins_others_text', array(
            'label'      => '' ,
            'value'      => $options['power_ins_others_text'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'power_ins_others_valid', 'style' => $display_power_ins_others)),
            ),
            'class'=>'w100percent'
        ));
        
        $subform->addElement('text', 'name_of_insurer', array(
            'value'        => $options['name_of_insurer'] ,
            'label'        => $this->translate('name_of_insurer'),
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
        ));
        
        $subform->addElement('text', 'contactperson', array(
            'value'        => $options['contactperson'] ,
            'label'        => $this->translate('function_contactperson'),
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
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
            'class'=>'w100percent'
        ));
        
        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
	public function create_form_patient_care_insurance($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_care_insurance");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('PatientCareInsurance_legend'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
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
	    
	    $subform->addElement('note',  "kind_of_insurance", array(
	        'value' => $this->translate("kind_of_insurance"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'font-weight: bold')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'separator' => PHP_EOL,
	    ));
	    
	    $subform->addElement('checkbox', 'kind_ins_legally', array(
	        'label'      => $this->translate('kind_ins_legally') ,
	        'value'      => $options['kind_ins_legally'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
	    ));
	    $subform->addElement('checkbox', 'kind_ins_private', array(
	        'label'      => $this->translate('kind_ins_private') ,
	        'value'      => $options['kind_ins_private'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
	    ));
	    $subform->addElement('checkbox', 'kind_ins_no', array(
	        'label'      => $this->translate('kind_ins_no') ,
	        'value'      => $options['kind_ins_no'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
	    ));
	    
	    $subform->addElement('checkbox', 'kind_ins_others', array(
	        'label'      => $this->translate('kind_ins_others') ,
	        'value'      => $options['kind_ins_others'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => "if($(this).is(':checked')) { $('.kind_ins_others_valid', $(this).parents('table')).show();} else { $('.kind_ins_others_valid', $(this).parents('table')).hide();} ",
	        'onclick' => 'show_kind_ins_extrafields($(this).is(":checked"));' ,
	    ));
	    
	    $display_kind_ins_others = $options['kind_ins_others'] != 1 ? 'display:none' : '';
	    
	    $subform->addElement('text', 'kind_ins_others_text', array(
	        'label'      => '' ,
	        'value'      => $options['kind_ins_others_text'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'kind_ins_others_valid', 'style' => $display_kind_ins_others)),
	        ),
	    ));
	    
	    $show_kind_ins_extrafields = '';
	    if ($options['kind_ins_legally'] == 0 && $options['kind_ins_private'] == 0 && $options['kind_ins_no'] == 0 && $options['kind_ins_others'] == 0){
	        $show_kind_ins_extrafields = 'display:none';
	    }
	    
	    $subform->addElement('note',  "power", array(
	        'value' => $this->translate("power"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'font-weight: bold')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	        'separator' => PHP_EOL
	    ));
	    
	    $subform->addElement('checkbox', 'power_ins_daily_care', array(
	        'label'      => $this->translate('power_ins_daily_care') ,
	        'value'      => $options['power_ins_daily_care'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	    ));
	    $subform->addElement('checkbox', 'power_ins_nursing_pension', array(
	        'label'      => $this->translate('power_ins_nursing_pension') ,
	        'value'      => $options['power_ins_nursing_pension'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	    ));
	    $subform->addElement('checkbox', 'power_ins_nursing_care', array(
	        'label'      => $this->translate('power_ins_nursing_care') ,
	        'value'      => $options['power_ins_nursing_care'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'style'=>"margin-left: 70px;width: 110px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'power_ins_others', array(
	        'label'      => $this->translate('power_ins_others') ,
	        'value'      => $options['power_ins_others'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 70px;width: 125px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	        'onChange' => "if($(this).is(':checked')) { $('.power_ins_others_valid', $(this).parents('table')).show();} else { $('.power_ins_others_valid', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_power_ins_others = $options['power_ins_others'] != 1 ? 'display:none' : '';
	    
	    $subform->addElement('text', 'power_ins_others_text', array(
	        'label'      => '' ,
	        'value'      => $options['power_ins_others_text'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'power_ins_others_valid', 'style' => $display_power_ins_others)),
	        ),
	    ));
	    
	    $subform->addElement('text', 'name_of_insurer', array(
	        'value'        => $options['name_of_insurer'] ,
	        'label'        => $this->translate('name_of_insurer'),
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
	        'label'        => $this->translate('function_contactperson'),
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
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'kind_ins_view_extrafields', 'style' => $show_kind_ins_extrafields )),
	        ),
	    ));
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_care_insurance($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);

	    
	    $r = PatientCareInsuranceTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
	
	public function InsertData($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	
	        
	    $frm = new PatientCareInsurance();
	    $frm->ipid = $post['ipid'];
	    $frm->kind_ins_legally = $post['kind_ins_legally'];
	    $frm->kind_ins_private = $post['kind_ins_private'];
	    $frm->kind_ins_no      = $post['kind_ins_no'];
	    $frm->kind_ins_others = $post['kind_ins_others'];
	    $frm->kind_ins_others_text = $post['kind_ins_others_text'];
	    $frm->power_ins_daily_care = $post['power_ins_daily_care'];
	    $frm->power_ins_nursing_pension = $post['power_ins_nursing_pension'];
	    $frm->power_ins_nursing_care = $post['power_ins_nursing_care'];
	    $frm->power_ins_others = $post['power_ins_others'];
	    $frm->power_ins_others_text = $post['power_ins_others_text'];
	    $frm->name_of_insurer = $post['name_of_insurer'];
	    $frm->first_name = $post['first_name'];
	    $frm->last_name = $post['last_name'];
	    $frm->contactperson      = $post['contactperson'];
	    $frm->street = $post['street'];
	    $frm->number = $post['number'];
	    $frm->zip = $post['zip'];
	    $frm->city = $post['city'];
	    $frm->phone = $post['phone'];
	    $frm->phone2 = $post['phone2'];
	    $frm->phonefax = $post['phonefax'];
	    $frm->email = $post['email'];
	    $frm->zip_mailbox = $post['zip_mailbox'];
	    $frm->description = $post['description'];
	    $frm->save();    	    
	 
	}
	
	public function UpdateData($post)
	{
	    
	    if ($fdoc = Doctrine::getTable('PatientCareInsurance')->find($post['ipid']))
	    {
	        $fdoc->kind_ins_legally = $post['kind_ins_legally'];
	        $fdoc->kind_ins_private = $post['kind_ins_private'];
	        $fdoc->kind_ins_no      = $post['kind_ins_no'];
	        $fdoc->kind_ins_others = $post['kind_ins_others'];
	        $fdoc->kind_ins_others_text = $post['kind_ins_others_text'];
	        $fdoc->power_ins_daily_care = $post['power_ins_daily_care'];
	        $fdoc->power_ins_nursing_pension = $post['power_ins_nursing_pension'];
	        $fdoc->power_ins_nursing_care = $post['power_ins_nursing_care'];
	        $fdoc->power_ins_others = $post['power_ins_others'];
	        $fdoc->power_ins_others_text = $post['power_ins_others_text'];
	        $fdoc->name_of_insurer = $post['name_of_insurer'];
	        $fdoc->first_name = $post['first_name'];
	        $fdoc->last_name = $post['last_name'];
	        $fdoc->contactperson      = $post['contactperson'];
	        $fdoc->street = $post['street'];
	        $fdoc->number = $post['number'];
	        $fdoc->zip = $post['zip'];
	        $fdoc->city = $post['city'];
	        $fdoc->phone = $post['phone'];
	        $fdoc->phone2 = $post['phone2'];
	        $fdoc->phonefax = $post['phonefax'];
	        $fdoc->email = $post['email'];
	        $fdoc->zip_mailbox = $post['zip_mailbox'];
	        $fdoc->description = $post['description'];
	        
	        $fdoc->save();
	        
	        
	    }
	}
	
	public function getInsuranceStatusArray()
	{
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    $loc = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientCareInsurance')
	    ->andWhere("ipid= ? ", $ipid);
	    $disarr = $loc->fetchArray();
	    
	    $result = '';
	    if($disarr[0]['kind_ins_legally'] == 1){
	        $result .= $this->translate('kind_ins_legally');
	    }
	    if($disarr[0]['kind_ins_private'] == 1){
	        if(!empty($result)){
	            $result .=', ';
	        }
	        $result .= 'Privat';
	    }
	    if($disarr[0]['kind_ins_no'] == 1){
	        if(!empty($result)){
	            $result .=', ';
	        }
	        $result .= $this->translate('kind_of_insurance');
	    }
	    if($disarr[0]['kind_ins_others'] == 1){
	        if(!empty($result)){
	            $result .=', ';
	        }
	        $result .= $disarr[0]['kind_ins_others_text'];
	    }
	    
	    //dd($result);

 return $result;
	    
	}
	
	
}




?>