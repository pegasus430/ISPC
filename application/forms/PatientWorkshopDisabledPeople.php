<?php
//ISPC-2672 Lore 26.10.2020

require_once("Pms/Form.php");

class Application_Form_PatientWorkshopDisabledPeople extends Pms_Form
{
    protected $_model = 'PatientWorkshopDisabledPeople';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('name_workshop'), "cols" => array("name")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientWorkshopDisabledPeople';
    
	   
	
    public function create_form_patient_workshop($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_workshop");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('PatientWorkshopDisabledPeople_legend'));
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
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
	    
	    $subform->addElement('text', 'name', array(
	        'value'        => $options['name'] ,
	        'label'        => $this->translate('name_workshop'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $subform->addElement('text', 'contactperson', array(
	        'value'        => $options['contactperson'] ,
	        'label'        => $this->translate('contactperson'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    $subform->addElement('text', 'func_cnt_person', array(
	        'value'        => $options['func_cnt_person'] ,
	        'label'        => $this->translate('function_contactperson'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    $subform->addElement('text', 'phonefax', array(
	        'value'        => $options['phonefax'],
	        'label'        => $this->translate('phonefax'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $lvpw_arr = PatientWorkshopDisabledPeople::getLastVisitWorkshop();
	    $subform->addElement('multiCheckbox', 'last_visit', array(
	        'label'      => "last_visit",
	        'multiOptions' => $lvpw_arr,
	        'required'   => false,
	        'value'    => isset($options['last_visit']) && ! is_array($options['last_visit']) ? array_map('trim', explode(",", $options['last_visit'])) : $options['last_visit'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'last_visit_options')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'last_visit_label')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "5" && this.checked) {$(".last_visit_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".last_visit_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "1" && this.checked) {$(".last_visit_date_show", $(this).parents(\'table\')).show();} else if(this.value == "1") {$(".last_visit_date_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $last_visit_arr = explode(",", $options['last_visit']);
	    $display_last_visit_text = in_array('5', $last_visit_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'last_visit_text', array(
	        'value'        => $options['last_visit_text'],
	        'label'        => 'Sonstiges',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "last_visit_text_show ", 'style' => $display_last_visit_text ))
	        ),
	    ));
	    $display_last_visit_date =  in_array('1', $last_visit_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'last_visit_date', array(
	        'value'        => $options['last_visit_date'],
	        'label'        => 'Datum',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "last_visit_date_show ", 'style' => $display_last_visit_date,))
	        ),
	        'class' => 'date',
	        'data-mask' => "99.99.9999",
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    $subform->addElement('radio',  'picked_up', array(
	        'multiOptions' => array('0' => 'Nein', '1' => 'Ja'),
	        'value'        => $options['picked_up'],
	        'label'        => $this->translate("picked_up"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),        
	        'onChange' => "if(this.value == '1') { $('.picked_up_text_from', $(this).parents('table')).show();} else { $('.picked_up_text_from', $(this).parents('table')).hide();} ",
	       ));
	    $display_picked_up_text = $options['picked_up'] == 1 ? '' : array('display:none');
	    $subform->addElement('text', 'picked_up_text', array(
	        'value'        => $options['picked_up_text'],
	        'label'        => $this->translate('picked_up_text'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'picked_up_text_from', 'style' => $display_picked_up_text)),
	            array('Label', array('tag' => 'td', 'class'=>'picked_up_text_from', 'style' => $display_picked_up_text )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "picked_up_text_from" , 'style' => $display_picked_up_text )),
	            ),
	    ));
	    
	    $subform->addElement('radio',  'accom_required', array(
	        'multiOptions' => array('0' => 'Nein', '1' => 'Ja'),
	        'value'        => $options['accom_required'],
	        'label'        => $this->translate("accom_required"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	            'onChange' => "if(this.value == '1') { $('.accom_required_text_by', $(this).parents('table')).show();} else { $('.accom_required_text_by', $(this).parents('table')).hide();} ",
	            ));
	    $display_accom_required_text = $options['accom_required'] == 1 ? '' : 'display:none';
	    $subform->addElement('text', 'accom_required_text', array(
	        'value'        => $options['accom_required_text'],
	        'label'        => $this->translate('accom_required_text'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'accom_required_text_by', 'style' => $display_accom_required_text)),
	            array('Label', array('tag' => 'td', 'class'=>'accom_required_text_by', 'style' => $display_accom_required_text )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "accom_required_text_by", 'style' => $display_accom_required_text )),
	            ),
	       ));

	    $subform->addElement('radio',  'accom_pg_required', array(
	        'multiOptions' => array('0' => 'Nein', '1' => 'Ja'),
	        'value'        => $options['accom_pg_required'],
	        'label'        => $this->translate("accom_pg_required_workshop"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'onChange' => "if(this.value == '1') { $('.accom_pg_required_text_by', $(this).parents('table')).show();} else { $('.accom_pg_required_text_by', $(this).parents('table')).hide();} ",
	    ));
	    $display_accom_pg_required_text = $options['accom_pg_required'] == 1 ? '' : array('display:none');
	    $subform->addElement('text', 'accom_pg_required_text', array(
	        'value'        => $options['accom_pg_required_text'],
	        'label'        => $this->translate('accom_required_text'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'accom_pg_required_text_by ', 'style' => $display_accom_pg_required_text)),
	            array('Label', array('tag' => 'td', 'class'=>'accom_pg_required_text_by ', 'style' => $display_accom_pg_required_text )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "accom_pg_required_text_by", 'style' => $display_accom_pg_required_text )),
	            ),
	    ));
	    
	    $subform->addElement('radio',  'aids_available', array(
	        'multiOptions' => array('0' => 'Nein', '1' => 'Ja'),
	        'value'        => $options['aids_available'],
	        'label'        => $this->translate("aids_available_workshop"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	            'onChange' => "if(this.value == '1') { $('.which_aids_available', $(this).parents('table')).show();} else { $('.which_aids_available', $(this).parents('table')).hide();} ",
	    ));
	    $display_aids_available_text = $options['aids_available'] == 1 ? '' : array('display:none');
	    $subform->addElement('text', 'aids_available_text', array(
	        'value'        => $options['aids_available_text'],
	        'label'        => $this->translate('aids_available_text'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'which_aids_available ', 'style' => $display_aids_available_text)),
	            array('Label', array('tag' => 'td', 'class'=>'which_aids_available ', 'style' => $display_aids_available_text )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "which_aids_available", 'style' => $display_aids_available_text )),
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
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_workshop($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);

	    $data['picked_up_text'] = $data['picked_up'] == 0 ? '' : $data['picked_up_text'];
	    $data['accom_required_text'] = $data['accom_required'] == 0 ? '' : $data['accom_required_text'];
	    $data['accom_pg_required_text'] = $data['accom_pg_required'] == 0 ? '' : $data['accom_pg_required_text'];
	    $data['aids_available_text'] = $data['aids_available'] == 0 ? '' : $data['aids_available_text'];
	    $data['last_visit_text'] = in_array('5',$data['last_visit']) ? $data['last_visit_text'] : '' ;
	    $data['last_visit_date'] = in_array('1',$data['last_visit']) ? $data['last_visit_date'] : '' ;
	    $data['last_visit'] = isset($data['last_visit']) ?  implode(",", $data['last_visit']) : null;
	    
	    $r = PatientWorkshopDisabledPeopleTable::getInstance()->findOrCreateOneBy( ['id', 'ipid'], [$data['id'], $ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>