<?php
/**
 *
 * @author Lore
 * ISPC-2673 Lore 25.09.2020
 *
 */
class Application_Form_FormBlockResources extends Pms_Form
{
    
    protected $_model = 'FormBlockResources';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockResources::TRIGGER_FORMID;
    private $triggerformname = FormBlockResources::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockResources::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    
    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('motor_skills_label'), "cols" => array("motor")),
            array( "label" => $this->translate('sensors_label'), "cols" => array("sensors_listen", "sensors_see", "sensors_smel", "sensors_feel")),
            array( "label" => $this->translate('communication_label'), "cols" => array("communication")),
            array( "label" => $this->translate('social_behavior_label'), "cols" => array("social")),
            array( "label" => $this->translate('independence_label'), "cols" => array("independence")),
            array( "label" => $this->translate('employment_label'), "cols" => array("employment")),
        );
    }
    
    public function create_form_resources ($options =  array() , $elementsBelongTo = null)
    {
        
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        $this->mapSaveFunction($__fnName , "save_form_resources");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('Ressourcen');
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        $subform->addElement('hidden', 'id', array(
            'label'        => null,
            'value'        => ! empty($options['id']) ? $options['id'] : '',
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan'=>2,
                    'style'=>array('display:none')
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class'    => 'dontPrint',
                )),
            ),
        ));
        
        $subform_motor = $this->_create_form_details_motor($options, $elementsBelongTo);
        $subform->addSubform($subform_motor, 'motor');
        
        $subform_sensors = $this->_create_form_details_sensors($options, $elementsBelongTo);
        $subform->addSubform($subform_sensors, 'sensors');
        
        $subform_communication = $this->_create_form_details_communication($options, $elementsBelongTo);
        $subform->addSubform($subform_communication, 'communication');
        
        $subform_social_bh = $this->_create_form_details_social_behavior($options, $elementsBelongTo);
        $subform->addSubform($subform_social_bh, 'social_behavior');
        
        $subform_independence = $this->_create_form_details_independence($options, $elementsBelongTo);
        $subform->addSubform($subform_independence, 'independence');
        
        $subform_employment = $this->_create_form_details_employment($options, $elementsBelongTo);
        $subform->addSubform($subform_employment, 'employment');
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    public function create_form_block_resources ($options =  array() , $elementsBelongTo = null)
    {
        
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        $this->mapSaveFunction($__fnName , "save_form_resources");
        
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:70%')));
        $subform->setLegend($this->translate('FormBlockResources_legend'));
        
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
                
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        

        
        $subform_motor = $this->_create_form_details_motor($options, $elementsBelongTo);
        $subform->addSubform($subform_motor, 'motor');
        
        $subform_sensors = $this->_create_form_details_sensors($options, $elementsBelongTo);
        $subform->addSubform($subform_sensors, 'sensors');
        
        $subform_communication = $this->_create_form_details_communication($options, $elementsBelongTo);
        $subform->addSubform($subform_communication, 'communication');
        
        $subform_social_bh = $this->_create_form_details_social_behavior($options, $elementsBelongTo);
        $subform->addSubform($subform_social_bh, 'social_behavior');
        
        $subform_independence = $this->_create_form_details_independence($options, $elementsBelongTo);
        $subform->addSubform($subform_independence, 'independence');
        
        $subform_employment = $this->_create_form_details_employment($options, $elementsBelongTo);
        $subform->addSubform($subform_employment, 'employment');
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    private function _create_form_details_motor($options = array(), $elementsBelongTo = null)
    {
        
        $subform = new Zend_Form_SubForm();
        // 	    $subform->removeDecorator('Fieldset');
        $subform->setLegend('motor');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable motor', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        $subform->addElement('hidden', 'id', array(
            'label'        => null,
            'value'        => ! empty($options['id']) ? $options['id'] : '',
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan'=>2,
                    'style'=>array('display:none')
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class'    => 'dontPrint',
                )),
            ),
        ));
        
        $subform->addElement('note',  "motor", array(
            'value' => $this->translate("motor_skills_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $motor_arr = FormBlockResources::getMotoroptions();
        $subform->addElement('multiCheckbox', 'motor_skills_opt', array(
            'label'      => "Meilensteine",
            'multiOptions' => $motor_arr,
            'required'   => false,
            'value'    => isset($options['motor_skills_opt']) && ! is_array($options['motor_skills_opt']) ? array_map('trim', explode(",", $options['motor_skills_opt'])) : $options['motor_skills_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
            'onChange' => 'if (this.value == "27" && this.checked) {$(".motor_text_show", $(this).parents(\'table\')).show();} else if(this.value == "27") {$(".motor_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_motor_skills_opt = $subform->getElement('motor_skills_opt')->getValue();
        $display_motor_skills_text = in_array('27', $selected_value_motor_skills_opt) ? '' : 'display:none';
        $subform->addElement('text',  'motor_skills_text', array(
            'value'        => $options['motor_skills_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
            ),
            'class'=>'motor_text_show',
            'style' => $display_motor_skills_text
        ));
        
        return $subform;
        
    }
    
    private function _create_form_details_sensors($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('Fieldset');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable sensors', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        $subform->addElement('note',  "sensors", array(
            'value' => $this->translate("sensors_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'separator' => PHP_EOL,
        ));
        
        $sensors_listen_arr = FormBlockResources::getSensors_listen_options();
        $subform->addElement('multiCheckbox', 'sensors_listen_opt', array(
            'label'      => "listen",
            'multiOptions' => $sensors_listen_arr,
            'required'   => false,
            'value'    => isset($options['sensors_listen_opt']) && ! is_array($options['sensors_listen_opt']) ? array_map('trim', explode(",", $options['sensors_listen_opt'])) : $options['sensors_listen_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "6" && this.checked) {$(".sensors_listen_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".sensors_listen_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_sensors_listen_opt = $subform->getElement('sensors_listen_opt')->getValue();
        $display_sensors_listen_text = in_array('6', $selected_value_sensors_listen_opt) ? '' : 'display:none';
        $subform->addElement('text',  'sensors_listen_text', array(
            'value'        => $options['sensors_listen_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr')),
            ),
            'class'=>'sensors_listen_text_show', 
            'style' => $display_sensors_listen_text 
        ));
        
        $sensors_see_arr = FormBlockResources::getSensors_see_options();
        $subform->addElement('multiCheckbox', 'sensors_see_opt', array(
            'label'      => "see",
            'multiOptions' => $sensors_see_arr,
            'required'   => false,
            'value'    => isset($options['sensors_see_opt']) && ! is_array($options['sensors_see_opt']) ? array_map('trim', explode(",", $options['sensors_see_opt'])) : $options['sensors_see_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "8" && this.checked) {$(".sensors_see_text_show", $(this).parents(\'table\')).show();} else if(this.value == "8") {$(".sensors_see_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_sensors_see_opt = $subform->getElement('sensors_see_opt')->getValue();
        $display_sensors_see_text = in_array('8', $selected_value_sensors_see_opt) ? '' : 'display:none';
        $subform->addElement('text',  'sensors_see_text', array(
            'value'        => $options['sensors_see_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'class'=>'sensors_see_text_show', 
            'style' => $display_sensors_see_text
        ));
        
        $subform->addElement('checkbox', 'sensors_smel_opt', array(
            'value'        => $options['sensors_smel_opt'],
            'label'        => 'smeel',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150' )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('checkbox', 'sensors_feel_opt', array(
            'value'        => $options['sensors_feel_opt'],
            'label'        => 'feel',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150' )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        return $subform;
        
    }
    
    private function _create_form_details_communication($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('Fieldset');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable communication', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        $subform->addElement('note',  "communication", array(
            'value' => $this->translate("communication_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'separator' => PHP_EOL,
        ));
        
        $communication_arr = FormBlockResources::getCommunicationoptions();
        $subform->addElement('multiCheckbox', 'communication_opt', array(
            'label'      => "",
            'multiOptions' => $communication_arr,
            'required'   => false,
            'value'    => isset($options['communication_opt']) && ! is_array($options['communication_opt']) ? array_map('trim', explode(",", $options['communication_opt'])) : $options['communication_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150 w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "20" && this.checked) {$(".communication_text_show", $(this).parents(\'table\')).show();} else if(this.value == "20") {$(".communication_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_communication_opt = $subform->getElement('communication_opt')->getValue();
        $display_communication_text = in_array('20', $selected_value_communication_opt) ? '' : 'display:none';
        $subform->addElement('text',  'communication_text', array(
            'value'        => $options['communication_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr')),
            ),
            'class'=>'communication_text_show', 
            'style' => $display_communication_text 
        ));
        
        return $subform;
        
    }
    
    private function _create_form_details_social_behavior($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('Fieldset');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable social_behavior', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        
        $subform->addElement('note',  "social_behavior", array(
            'value' => $this->translate("social_behavior_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $social_arr = FormBlockResources::getSocialoptions();
        $subform->addElement('multiCheckbox', 'social_behavior_opt', array(
            'label'      => "",
            'multiOptions' => $social_arr,
            'required'   => false,
            'value'    => isset($options['social_behavior_opt']) && ! is_array($options['social_behavior_opt']) ? array_map('trim', explode(",", $options['social_behavior_opt'])) : $options['social_behavior_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "15" && this.checked) {$(".social_behavior_text_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".social_behavior_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_social_behavior_opt = $subform->getElement('social_behavior_opt')->getValue();
        $display_social_behavior_text = in_array('15', $selected_value_social_behavior_opt) ? '' : 'display:none';
        $subform->addElement('text',  'social_behavior_text', array(
            'value'        => $options['social_behavior_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr')),
            ),
            'class'=>'social_behavior_text_show', 
            'style' => $display_social_behavior_text 
        ));
        
        return $subform;
        
    }
    
    private function _create_form_details_independence($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('Fieldset');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable independence', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        
        $subform->addElement('note',  "independence", array(
            'value' => $this->translate("independence_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $independence_arr = FormBlockResources::getIndependenceoptions();
        $subform->addElement('multiCheckbox', 'independence_opt', array(
            'label'      => "",
            'multiOptions' => $independence_arr,
            'required'   => false,
            'value'    => isset($options['independence_opt']) && ! is_array($options['independence_opt']) ? array_map('trim', explode(",", $options['independence_opt'])) : $options['independence_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "11" && this.checked) {$(".independence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".independence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
        ));
        
        $selected_value_independence_opt = $subform->getElement('independence_opt')->getValue();
        $display_independence_text = in_array('11', $selected_value_independence_opt) ? '' : 'display:none';
        $subform->addElement('text',  'independence_text', array(
            'value'        => $options['independence_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr')),
            ),
            'class'=>'independence_text_show', 
            'style' => $display_independence_text 
        ));
        
        return $subform;
        
    }
    
    private function _create_form_details_employment($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('Fieldset');
        $subform->setDecorators( array(
            'FormElements',
            array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable employment', 'cellpadding'=>"3", 'cellspacing'=>"0", "style"=>'width: 100%;')),
        ));
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //FOR PDF [Ancuta] - overwrite post
        if(isset($options['FormBlockResources']) && !empty($options['FormBlockResources'])){
            $options = $options['FormBlockResources'];
        }
        
        
        $subform->addElement('note',  "employment", array(
            'value' => $this->translate("employment_label"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('textarea',  'preferences_and_interests', array(
            'value'        => $options['preferences_and_interests'],
            'label'        => 'preferences_and_interests',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150 Valign')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'rows'         => 5,
            'cols'         => 80,
        ));
        
        $subform->addElement('textarea',  'habits', array(
            'value'        => $options['habits'],
            'label'        => 'habits',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150 Valign')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'rows'         => 5,
            'cols'         => 80,
        ));
        
        $subform->addElement('textarea',  'dislikes', array(
            'value'        => $options['dislikes'],
            'label'        => 'dislikes',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150 Valign')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'rows'         => 5,
            'cols'         => 80,
        ));
        
        $subform->addElement('textarea',  'sole_occupations', array(
            'value'        => $options['sole_occupations'],
            'label'        => 'sole_occupations',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150 Valign')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'rows'         => 5,
            'cols'         => 80,
        ));
        
        return $subform;
        
    }
    
    
    public function save_form_resources ($ipid =  null , $data =  array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
        
        //dd($data);
        if(isset($data['patientDetails'])){     // came from stamdaten
            
            $data['id'] = $data['patientDetails']['FormBlockResources']['id'];
            $data['contact_form_id'] = 0;
            
            $data_post = $data['patientDetails']['FormBlockResources'];
            $data_post['contact_form_id'] = 0;
            $data_post['motor_skills_opt']    = isset($data['patientDetails']['FormBlockResources']['motor_skills_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['motor_skills_opt']) : null;
            $data_post['sensors_listen_opt']  = isset($data['patientDetails']['FormBlockResources']['sensors_listen_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['sensors_listen_opt']) : null;
            $data_post['sensors_see_opt']     = isset($data['patientDetails']['FormBlockResources']['sensors_see_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['sensors_see_opt']) : null;
            //$data_post['sensors_smel_opt']    = isset($data['patientDetails']['FormBlockResources']['sensors_smel_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['sensors_smel_opt']) : null;
            //$data_post['sensors_feel_opt']    = isset($data['patientDetails']['FormBlockResources']['sensors_feel_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['sensors_feel_opt']) : null;
            $data_post['communication_opt']   = isset($data['patientDetails']['FormBlockResources']['communication_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['communication_opt']) : null;
            $data_post['social_behavior_opt'] = isset($data['patientDetails']['FormBlockResources']['social_behavior_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['social_behavior_opt']) : null;
            $data_post['independence_opt']    = isset($data['patientDetails']['FormBlockResources']['independence_opt']) ?  implode(",", $data['patientDetails']['FormBlockResources']['independence_opt']) : null;
            
            
        } else {            // came from contact form
            
            //if user not alowed to this form, duplicate the block
            $this->__save_form_resources_copy_old_if_not_allowed($ipid , $data);
            
            //create patientcourse
            $this->__save_form_resources_patient_course($ipid , $data);
            
            //set the old block values as isdelete
            $this->__save_form_resources_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
            
            $data_post = $data['FormBlockResources'];
            $data_post['motor_skills_opt']    = isset($data['FormBlockResources']['motor_skills_opt']) ?  implode(",", $data['FormBlockResources']['motor_skills_opt']) : null;
            $data_post['sensors_listen_opt']  = isset($data['FormBlockResources']['sensors_listen_opt']) ?  implode(",", $data['FormBlockResources']['sensors_listen_opt']) : null;
            $data_post['sensors_see_opt']     = isset($data['FormBlockResources']['sensors_see_opt']) ?  implode(",", $data['FormBlockResources']['sensors_see_opt']) : null;
            //$data_post['sensors_smel_opt']    = isset($data['FormBlockResources']['sensors_smel_opt']) ?  implode(",", $data['FormBlockResources']['sensors_smel_opt']) : null;
            //$data_post['sensors_feel_opt']    = isset($data['FormBlockResources']['sensors_feel_opt']) ?  implode(",", $data['FormBlockResources']['sensors_feel_opt']) : null;
            $data_post['communication_opt']   = isset($data['FormBlockResources']['communication_opt']) ?  implode(",", $data['FormBlockResources']['communication_opt']) : null;
            $data_post['social_behavior_opt'] = isset($data['FormBlockResources']['social_behavior_opt']) ?  implode(",", $data['FormBlockResources']['social_behavior_opt']) : null;
            $data_post['independence_opt']    = isset($data['FormBlockResources']['independence_opt']) ?  implode(",", $data['FormBlockResources']['independence_opt']) : null;
       
            //update the inreg from stamdaten - if exist
            $data['contact_form_id_zero'] = 0;
            $stamdaten_entity = FormBlockResourcesTable::getInstance()->findOrCreateOneBy([ 'ipid', 'contact_form_id'], [ $ipid, $data['contact_form_id_zero']], $data_post);
            
        }
        
        
        $entity = FormBlockResourcesTable::getInstance()->findOrCreateOneBy(['id', 'ipid', 'contact_form_id'], [$data['id'], $ipid, $data['contact_form_id']], $data_post);
     
        return $entity;
    }
    
    
    
    /**
     * !! $data used by reference
     *
     * copy-paste the old saved values of the block, when this user has no access to this block
     *
     * @param string $ipid
     * @param array $data
     */
    private function __save_form_resources_copy_old_if_not_allowed($ipid =  null , &$data =  array())
    {
        if (empty($ipid) || empty($data)
            || in_array('resources', $data['__formular']['allowed_blocks'])
            )
        {
            return;
        }
        
        
        $oldValues = FormBlockResourcesTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
        
        if ( ! empty($oldValues)) {
            
            unset($oldValues[FormBlockResourcesTable::getInstance()->getIdentifier()]);
            
            $data = array_merge($data, $oldValues);
        }
        
    }
    
    /**
     * write or erase the patientcourse text
     *
     * @param string $ipid
     * @param unknown $data
     */
    private function __save_form_resources_patient_course($ipid =  null , $data =  array())
    {
        if (empty($ipid) || empty($data)
            || ! in_array('resources', $data['__formular']['allowed_blocks']))
        {
            return;
        }
        
        $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
        $formular          = $data['__formular'];
        
        if ( ! in_array('resources', $data['__formular']['allowed_blocks'])) {
            return;
        }
        
        $course_arr_OLD    = [];
        $course_arr        = $this->__save_form_resources_patient_course_format($data);
        
        
        if (empty($data['__formular']['old_contact_form_id'])) {
            //this is from a new cf, so we add to patient_course
            $save_2_PC = true ;
        } else {
            
            $oldValues = FormBlockResourcesTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
            
            if (empty($oldValues)) {
                
                //missing previous values, so we save
                $save_2_PC = true ;
                
            } else {
                
                $course_arr_OLD =  $this->__save_form_resources_patient_course_format($oldValues);
                
                $changes = 0;
                
                foreach($course_arr_OLD as $old_cls=>$old_valuses){
                    
                    if( ! isset($course_arr[$old_cls])){
                        // remove this from PC
                        $pc_entity = new PatientCourse();
                        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockresources::PATIENT_COURSE_TABNAME,'yes',true,$old_valuses['shortcut']);
                    }
                }
                
                foreach($course_arr as $cls=>$vcs_val){
                    
                    if(!isset($course_arr_OLD[$cls])){
                        $changes++;
                    } else {
                        
                        
                    }
                }
                
                if ( $changes == 0 ) {
                    //same pc... nothing to insert
                } else {
                    $save_2_PC = true ;
                }
                
            }
            
        }
        
        
        if ($save_2_PC
            && ! empty($course_arr)
            && ($pc_listener = FormBlockResourcesTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
        {
            //dd($course_arr);
            $course_str = 'Ressourcen: '."\r\n";
            $course_str_check = '';
            
            foreach($course_arr as $kc => $vcarr){
                $course_str .= $vcarr['name'].': '.$vcarr['comment']."\r\n";
            }
            
            $vc_values['shortcut'] = 'K' ;
            
            
            if(strlen($course_str) > 0 ){
                
                $cust = new PatientCourse();
                //skip Trigger()
                $cust->triggerformid = null;
                $cust->triggerformname = null;
                
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt($vc_values['shortcut']);
                $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
                $cust->user_id = $this->logininfo->userid;
                $cust->done_date = $done_date;
                $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                $cust->done_id = $data['contact_form_id'];
                //$cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
                // ISPC-2071 - added tabname, this entry must be grouped/sorted
                $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockresources::PATIENT_COURSE_TABNAME);
                
                $cust->save();
            }
            
            
            
            
            
        } elseif ($save_2_PC
            && empty($course_arr)
            && ! empty($formular['old_contact_form_id']))
        {
            //must manualy remove from PC this option
            $pc_entity = new PatientCourse();
            $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockresources::PATIENT_COURSE_TABNAME);
            
        }
        
    }
    
    
    /**
     * format the patientcourse title message
     *
     * @param unknown $data
     * @return multitype:string
     */
    private function __save_form_resources_patient_course_format($data = [])
    {
        $course_arr = [];
        
        $motor = FormBlockResources::getMotoroptions();
        $Communication = FormBlockResources::getCommunicationoptions();
        $Social = FormBlockResources::getSocialoptions();
        $Independence = FormBlockResources::getIndependenceoptions();
        $Sensors_listen = FormBlockResources::getSensors_listen_options();
        $Sensors_see = FormBlockResources::getSensors_see_options();
        
        if(isset($data['FormBlockResources']['motor_skills_opt']) && !empty($data['FormBlockResources']['motor_skills_opt'])){
            $course_arr['motor']['name'] = $this->translate('motor_skills_label');
            foreach($data['FormBlockResources']['motor_skills_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['motor']['comment'] .= ', ';
                }
                $course_arr['motor']['comment'] .= $motor[$vm];
                if($vm == 27){
                    $course_arr['motor']['comment'] .= '('.$data['FormBlockResources']['motor_skills_text'].')';
                }
            }
        }
        
        if(isset($data['FormBlockResources']['sensors_listen_opt']) && !empty($data['FormBlockResources']['sensors_listen_opt'])){
            $course_arr['sensors_listen']['name'] = $this->translate('sensors_label').'-'.$this->translate('listen');
            foreach($data['FormBlockResources']['sensors_listen_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['sensors_listen']['comment'] .= ', ';
                }
                $course_arr['sensors_listen']['comment'] .= $Sensors_listen[$vm];
                if($vm == 6){
                    $course_arr['sensors_listen']['comment'] .= '('.$data['FormBlockResources']['sensors_listen_text'].')';
                }
            }
        }
        
        if(isset($data['FormBlockResources']['sensors_see_opt']) && !empty($data['FormBlockResources']['sensors_see_opt'])){
            $course_arr['sensors_see']['name'] = $this->translate('sensors_label').'-'.$this->translate('see');
            foreach($data['FormBlockResources']['sensors_see_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['sensors_see']['comment'] .= ', ';
                }
                $course_arr['sensors_see']['comment'] .= $Sensors_see[$vm];
                if($vm == 8){
                    $course_arr['sensors_see']['comment'] .= '('.$data['FormBlockResources']['sensors_see_text'].')';
                }
            }
        }
        
        if(isset($data['FormBlockResources']['sensors_smel_opt']) && !empty($data['FormBlockResources']['sensors_smel_opt'])){
            $course_arr['sensors_smel']['name'] = $this->translate('sensors_label');
            $course_arr['sensors_smel']['comment'] = $this->translate('smeel');
        }
        
        if(isset($data['FormBlockResources']['sensors_feel_opt']) && !empty($data['FormBlockResources']['sensors_feel_opt'])){
            $course_arr['sensors_feel']['name'] = $this->translate('sensors_label');
            $course_arr['sensors_feel']['comment'] = $this->translate('feel');
        }
        
        if(isset($data['FormBlockResources']['communication_opt']) && !empty($data['FormBlockResources']['communication_opt'])){
            $course_arr['communication']['name'] = $this->translate('communication_label');
            foreach($data['FormBlockResources']['communication_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['communication']['comment'] .= ', ';
                }
                $course_arr['communication']['comment'] .= $Communication[$vm];
                if($vm == 20){
                    $course_arr['communication']['comment'] .= '('.$data['FormBlockResources']['communication_text'].')';
                }

            }
        }
        
        if(isset($data['FormBlockResources']['social_behavior_opt']) && !empty($data['FormBlockResources']['social_behavior_opt'])){
            $course_arr['social_behavior']['name'] = $this->translate('social_behavior_label');
            foreach($data['FormBlockResources']['social_behavior_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['social_behavior']['comment'] .= ', ';
                }
                $course_arr['social_behavior']['comment'] .= $Social[$vm];
                if($vm == 15){
                    $course_arr['social_behavior']['comment'] .= '('.$data['FormBlockResources']['social_behavior_text'].')';
                }

            }
        }
        
        if(isset($data['FormBlockResources']['independence_opt']) && !empty($data['FormBlockResources']['independence_opt'])){
            $course_arr['independence']['name'] = $this->translate('independence_label');
            foreach($data['FormBlockResources']['independence_opt'] as $km => $vm){
                if($km > 0 ){
                    $course_arr['independence']['comment'] .= ', ';
                }
                $course_arr['independence']['comment'] .= $Independence[$vm];
                if($vm == 11){
                    $course_arr['independence']['comment'] .= '('.$data['FormBlockResources']['independence_text'].')';
                }

            }
        }
        
        if(isset($data['FormBlockResources']['preferences_and_interests']) && !empty($data['FormBlockResources']['preferences_and_interests'])){
            $course_arr['employment_1']['name'] = $this->translate('employment_label').': '.$this->translate('preferences_and_interests');
            $course_arr['employment_1']['comment'] = $data['FormBlockResources']['preferences_and_interests'];
        }
        if(isset($data['FormBlockResources']['habits']) && !empty($data['FormBlockResources']['habits'])){
            $course_arr['employment_2']['name'] = $this->translate('employment_label').': '.$this->translate('habits');
            $course_arr['employment_2']['comment'] = $data['FormBlockResources']['habits'];
        }
        if(isset($data['FormBlockResources']['dislikes']) && !empty($data['FormBlockResources']['dislikes'])){
            $course_arr['employment_3']['name'] = $this->translate('employment_label').': '.$this->translate('dislikes');
            $course_arr['employment_3']['comment'] = $data['FormBlockResources']['dislikes'];
        }
        if(isset($data['FormBlockResources']['sole_occupations']) && !empty($data['FormBlockResources']['sole_occupations'])){
            $course_arr['employment_4']['name'] = $this->translate('employment_label').': '.$this->translate('sole_occupations');
            $course_arr['employment_4']['comment'] = $data['FormBlockResources']['sole_occupations'];
        }
        
        
        return $course_arr;
    }
    
    /**
     * set isdelete = 1 for the old block
     *
     * @param string $ipid
     * @param number $contact_form_id
     * @return boolean
     */
    private function __save_form_resources_clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if ( ! empty($contact_form_id) && ! empty($ipid))
        {
            FormBlockResourcesTable::getInstance()->createQuery('del')
            ->delete()
            ->where("contact_form_id = ?", $contact_form_id)
            ->andWhere('ipid = ?', $ipid)
            ->execute();
            
            return true;
        }
    }
    
    
    
}