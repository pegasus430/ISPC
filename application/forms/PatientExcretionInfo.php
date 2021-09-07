<?php
//ISPC-2791 Lore 13.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientExcretionInfo extends Pms_Form
{
    protected $_model = 'PatientExcretionInfo';

    public function getVersorgerExtract($param = null)
    {
        return array(
            //array( "label" => $this->translate('General'), "cols" => array("General")),            
            array( "label" => $this->translate('independence'), "cols" => array("independence")),
            array( "label" => $this->translate('incontinence'), "cols" => array("incontinence")),
            array( "label" => $this->translate('wears_diapers_opt'), "cols" => array("wears_diapers")),
            array( "label" => $this->translate('toilet_training_opt'), "cols" => array("toilet_training")),
            array( "label" => $this->translate('toilet_chair_opt'), "cols" => array("toilet_chair")),
            array( "label" => $this->translate('abdominal_massages_opt'), "cols" => array("abdominal_massages")),
            //array( "label" => $this->translate('bowel_movement'), "cols" => array("bowel_movement")),
            array( "label" => $this->translate('Last bowel movement'), "cols" => array("bowel_movement_text")),
            array( "label" => $this->translate('consistency'), "cols" => array("consistency_text")),
            array( "label" => $this->translate('frequency_text'), "cols" => array("frequency_text")),
            array( "label" => $this->translate('stimulate_opt'), "cols" => array("stimulate")),
            array( "label" => $this->translate('intestinal_tube_opt'), "cols" => array("intestinal_tube")),
            array( "label" => $this->translate('digital_clearing_opt'), "cols" => array("digital_clearing")),
            //array( "label" => $this->translate('urninating'), "cols" => array("urninating")),
            array( "label" => $this->translate('uses_templates_opt'), "cols" => array("uses_templates")),
            array( "label" => $this->translate('urine_bottle_opt'), "cols" => array("urine_bottle")),
            array( "label" => $this->translate('urine_condom_opt'), "cols" => array("urine_condom")),
            array( "label" => $this->translate('catheterization_opt'), "cols" => array("catheterization")),
            
            array( "label" => $this->translate('Menstruation'), "cols" => array("menstruation")),
            
            array( "label" => $this->translate('vomit_opt'), "cols" => array("vomit")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientExcretionInfo';
    
	
    public function create_form_block_patient_excretioninfo($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_excretion_info");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_excretioninfo');
        $subform->setAttrib("class", "label_same_size class_tablewidth100la100 {$__fnName}");
        
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        $subform->addElement('hidden', 'id', array(
            'value'        => $options['id'] ? $options['id'] : 0 ,
            'required'     => false,
            'label'        => "",
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                
            ),
        ));
        
        $subform->addElement('note',  "General", array(
            'value' => $this->translate("General"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
/*         $general_opt_arr = isset($options['general_opt']) && ! is_array($options['general_opt']) ? array_map('trim', explode(",", $options['general_opt'])) : $options['general_opt'];
        $value_general_opt_1 = array_search('1', $general_opt_arr) !== false ? array('1') : array('0');

        $subform->addElement('multiCheckbox', 'general_opt_1', array(
            'value'        => $value_general_opt_1,
            'multiOptions' => array('1' => 'independence'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        if($options['formular_type'] == 'pdf' ){
            $independence = PatientExcretionInfo::getIndependence();
            $independence[4] = 'Sonstiges: '.$options['independence_text'];
            $subform->addElement('multiCheckbox', 'independence_opt', array(
                'label'      => $this->translate("independence"),
                'multiOptions' => $independence,
                'required'   => false,
                'value'    => isset($options['independence_opt']) && ! is_array($options['independence_opt']) ? array_map('trim', explode(",", $options['independence_opt'])) : $options['independence_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));

        } else {
            $independence = PatientExcretionInfo::getIndependence();
            $subform->addElement('multiCheckbox', 'independence_opt', array(
                'label'      => $this->translate("independence"),
                'multiOptions' => $independence,
                'required'   => false,
                'value'    => isset($options['independence_opt']) && ! is_array($options['independence_opt']) ? array_map('trim', explode(",", $options['independence_opt'])) : $options['independence_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "4" && this.checked) {$(".independence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".independence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_independence_opt = $subform->getElement('independence_opt')->getValue();
            $display_independence_text = in_array('4', $selected_value_independence_opt) ? '' : 'display:none';
            $subform->addElement('text',  'independence_text', array(
                'value'        => $options['independence_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'independence_text_show',
                'style' => $display_independence_text
            ));
            
            $subform->addElement('note',  "blank_1", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }
            
/*         $value_general_opt_2 = array_search('2', $general_opt_arr) !== false ? array('1') : array('0');
        $subform->addElement('multiCheckbox', 'general_opt_2', array(
            'value'        => $value_general_opt_2,
            'multiOptions' => array('1' => 'incontinence'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        if($options['formular_type'] == 'pdf' ){
            $incontinence = PatientExcretionInfo::getIncontinence();
            $incontinence[4]= 'Sonstiges: '.$options['incontinence_text'];
            $subform->addElement('multiCheckbox', 'incontinence_opt', array(
                'label'      => $this->translate("incontinence"),
                'multiOptions' => $incontinence,
                'required'   => false,
                'value'    => isset($options['incontinence_opt']) && ! is_array($options['incontinence_opt']) ? array_map('trim', explode(",", $options['incontinence_opt'])) : $options['incontinence_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));
            

        } else {
            $incontinence = PatientExcretionInfo::getIncontinence();
            $subform->addElement('multiCheckbox', 'incontinence_opt', array(
                'label'      => $this->translate("incontinence"),
                'multiOptions' => $incontinence,
                'required'   => false,
                'value'    => isset($options['incontinence_opt']) && ! is_array($options['incontinence_opt']) ? array_map('trim', explode(",", $options['incontinence_opt'])) : $options['incontinence_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "4" && this.checked) {$(".incontinence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".incontinence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_incontinence_opt = $subform->getElement('incontinence_opt')->getValue();
            $display_incontinence_text = in_array('4', $selected_value_incontinence_opt) ? '' : 'display:none';
            $subform->addElement('text',  'incontinence_text', array(
                'value'        => $options['incontinence_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'incontinence_text_show',
                'style' => $display_incontinence_text
            ));
            
            $subform->addElement('note',  "blank_2", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }
            
/*         $value_general_opt_3 = array_search('3', $general_opt_arr) !== false ? array('1') : array('0');
        $subform->addElement('multiCheckbox', 'general_opt_3', array(
            'value'        => $value_general_opt_3,
            'multiOptions' => array('1' => 'wears_diapers_opt'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'wears_diapers_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja: '.$options['wears_diapers_text']),
                'value'        => $options['wears_diapers_opt'],
                'label'        => $this->translate("wears_diapers_opt"),
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
            ));

        } else {
            $subform->addElement('radio',  'wears_diapers_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['wears_diapers_opt'],
                'label'        => $this->translate("wears_diapers_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '3') { $('.wears_diapers_text_show', $(this).parents('table')).show();} else { $('.wears_diapers_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_wears_diapers_text = $options['wears_diapers_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'wears_diapers_text', array(
                'value'        => $options['wears_diapers_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'wears_diapers_text_show',
                'style' => $display_wears_diapers_text
            ));
            
            $subform->addElement('note',  "blank_3", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }

/*         $value_general_opt_4 = array_search('4', $general_opt_arr) !== false ? array('1') : array('0');
        $subform->addElement('multiCheckbox', 'general_opt_4', array(
            'value'        => $value_general_opt_4,
            'multiOptions' => array('1' => 'toilet_training_opt'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'toilet_training_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'Ja, tags', '3' => 'Ja, nachts', '4' => 'Sonstiges: '.$options['toilet_training_text']),
                'value'        => $options['toilet_training_opt'],
                'label'        => $this->translate("toilet_training_opt"),
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
            ));
            
        } else {
            $subform->addElement('radio',  'toilet_training_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'Ja, tags', '3' => 'Ja, nachts', '4' => 'Sonstiges'),
                'value'        => $options['toilet_training_opt'],
                'label'        => $this->translate("toilet_training_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '4') { $('.toilet_training_text_show', $(this).parents('table')).show();} else { $('.toilet_training_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_toilet_training_text = $options['toilet_training_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'toilet_training_text', array(
                'value'        => $options['toilet_training_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    // array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'toilet_training_text_show',
                'style' => $display_toilet_training_text
            ));
        }

/*         $value_general_opt_5 = array_search('5', $general_opt_arr) !== false ? array('1') : array('0');
        $subform->addElement('multiCheckbox', 'general_opt_5', array(
            'value'        => $value_general_opt_5,
            'multiOptions' => array('1' => 'toilet_chair_opt'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
            
        $subform->addElement('radio',  'toilet_chair_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['toilet_chair_opt'],
            'label'        => $this->translate("toilet_chair_opt"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
/*         $value_general_opt_6 = array_search('6', $general_opt_arr) !== false ? array('1') : array('0');
        $subform->addElement('multiCheckbox', 'general_opt_6', array(
            'value'        => $value_general_opt_6,
            'multiOptions' => array('1' => 'abdominal_massages_opt'),
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        $subform->addElement('radio',  'abdominal_massages_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['abdominal_massages_opt'],
            'label'        => $this->translate("abdominal_massages_opt"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('note',  "bowel_movement", array(
            'value' => $this->translate("bowel_movement"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        $subform->addElement('text', 'bowel_movement_text', array(
            'value'        => $options['bowel_movement_text'],
            'label'        => $this->translate("Last bowel movement"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'consistency_text', array(
            'value'        => $options['consistency_text'],
            'label'        => $this->translate("consistency"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'frequency_text', array(
            'value'        => $options['frequency_text'],
            'label'        => $this->translate("frequency_text"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('radio',  'stimulate_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['stimulate_opt'],
            'label'        => $this->translate("stimulate_opt"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('radio',  'intestinal_tube_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['intestinal_tube_opt'],
            'label'        => $this->translate("intestinal_tube_opt"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('radio',  'digital_clearing_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['digital_clearing_opt'],
            'label'        => $this->translate("digital_clearing_opt"),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('note',  "urninating", array(
            'value' => $this->translate("urninating"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'uses_templates_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja: '.$options['uses_templates_text'] ),
                'value'        => $options['uses_templates_opt'],
                'label'        => $this->translate("uses_templates_opt"),
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
            ));
                        

        } else {
            $subform->addElement('radio',  'uses_templates_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['uses_templates_opt'],
                'label'        => $this->translate("uses_templates_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '3') { $('.uses_templates_text_show', $(this).parents('table')).show();} else { $('.uses_templates_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_uses_templates_text = $options['uses_templates_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'uses_templates_text', array(
                'value'        => $options['uses_templates_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'uses_templates_text_show',
                'style' => $display_uses_templates_text
            ));
            
            $subform->addElement('note',  "blank_4", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'urine_bottle_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja: '.$options['urine_bottle_text'] ),
                'value'        => $options['urine_bottle_opt'],
                'label'        => $this->translate("urine_bottle_opt"),
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
            ));
            
 
        } else {
            $subform->addElement('radio',  'urine_bottle_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['urine_bottle_opt'],
                'label'        => $this->translate("urine_bottle_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '3') { $('.urine_bottle_text_show', $(this).parents('table')).show();} else { $('.urine_bottle_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_urine_bottle_text = $options['urine_bottle_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'urine_bottle_text', array(
                'value'        => $options['urine_bottle_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'urine_bottle_text_show',
                'style' => $display_urine_bottle_text
            ));
            
            $subform->addElement('note',  "blank_5", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }
            
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'urine_condom_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja: '.$options['urine_condom_text']),
                'value'        => $options['urine_condom_opt'],
                'label'        => $this->translate("urine_condom_opt"),
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
            ));

        } else {
            $subform->addElement('radio',  'urine_condom_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['urine_condom_opt'],
                'label'        => $this->translate("urine_condom_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '3') { $('.urine_condom_text_show', $(this).parents('table')).show();} else { $('.urine_condom_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_urine_condom_text = $options['urine_condom_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'urine_condom_text', array(
                'value'        => $options['urine_condom_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'urine_condom_text_show',
                'style' => $display_urine_condom_text
            ));
            
            $subform->addElement('note',  "blank_6", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
                ),
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'catheterization_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja: '.$options['catheterization_text'] ),
                'value'        => $options['catheterization_opt'],
                'label'        => $this->translate("catheterization_opt"),
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
            ));

        } else {
            $subform->addElement('radio',  'catheterization_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['catheterization_opt'],
                'label'        => $this->translate("catheterization_opt"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '3') { $('.catheterization_text_show', $(this).parents('table')).show();} else { $('.catheterization_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_catheterization_text = $options['catheterization_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'catheterization_text', array(
                'value'        => $options['catheterization_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'catheterization_text_show',
                'style' => $display_catheterization_text
            ));
        }

        
        $subform->addElement('note',  "Menstruation", array(
            'value' => $this->translate("Menstruation"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $menstruation = PatientExcretionInfo::getMenstruation();
            $menstruation[4] = 'Sonstiges: '.$options['menstruation_text'];
            $subform->addElement('multiCheckbox', 'menstruation_opt', array(
                'label'      => "",
                'multiOptions' => $menstruation,
                'required'   => false,
                'value'    => isset($options['menstruation_opt']) && ! is_array($options['menstruation_opt']) ? array_map('trim', explode(",", $options['menstruation_opt'])) : $options['menstruation_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));

        } else {
            $menstruation = PatientExcretionInfo::getMenstruation();
            $subform->addElement('multiCheckbox', 'menstruation_opt', array(
                'label'      => "",
                'multiOptions' => $menstruation,
                'required'   => false,
                'value'    => isset($options['menstruation_opt']) && ! is_array($options['menstruation_opt']) ? array_map('trim', explode(",", $options['menstruation_opt'])) : $options['menstruation_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "4" && this.checked) {$(".menstruation_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".menstruation_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_menstruation_opt= $subform->getElement('menstruation_opt')->getValue();
            $display_menstruation_text = in_array('4', $selected_menstruation_opt) ? '' : 'display:none';
            $subform->addElement('text',  'menstruation_text', array(
                'value'        => $options['menstruation_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'menstruation_text_show',
                'style' => $display_menstruation_text
            ));
        }

        
        $subform->addElement('note',  "vomit", array(
            'value' => $this->translate("vomit_opt"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        $subform->addElement('radio',  'vomit_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['vomit_opt'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
            
                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_excretion_info($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_excretion_info");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientExcretionInfo_legend'));
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

	    $subform->addElement('note',  "General", array(
	        'value' => $this->translate("General"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
/* 	    $subform->addElement('multiCheckbox', 'general_opt_1', array(
	        'value'        => strpbrk($options['general_opt'], '1') ? '1' : null,
	        'multiOptions' => array('1' => 'independence'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */

	    $independence = PatientExcretionInfo::getIndependence();
	    $subform->addElement('multiCheckbox', 'independence_opt', array(
	        'label'      => $this->translate("independence"),
	        'multiOptions' => $independence,
	        'required'   => false,
	        'value'    => isset($options['independence_opt']) && ! is_array($options['independence_opt']) ? array_map('trim', explode(",", $options['independence_opt'])) : $options['independence_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "4" && this.checked) {$(".independence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".independence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_independence_opt = $subform->getElement('independence_opt')->getValue();
	    $display_independence_text = in_array('4', $selected_value_independence_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'independence_text', array(
	        'value'        => $options['independence_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'independence_text_show',
	        'style' => $display_independence_text
	    ));
	    
	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
/* 	    $subform->addElement('multiCheckbox', 'general_opt_2', array(
	        'value'        => strpbrk($options['general_opt'], '2') ? '1' : null,
	        'multiOptions' => array('1' => 'incontinence'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */
	    
	    $incontinence = PatientExcretionInfo::getIncontinence();
	    $subform->addElement('multiCheckbox', 'incontinence_opt', array(
	        'label'      => $this->translate("incontinence"),
	        'multiOptions' => $incontinence,
	        'required'   => false,
	        'value'    => isset($options['incontinence_opt']) && ! is_array($options['incontinence_opt']) ? array_map('trim', explode(",", $options['incontinence_opt'])) : $options['incontinence_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "4" && this.checked) {$(".incontinence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".incontinence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_incontinence_opt = $subform->getElement('incontinence_opt')->getValue();
	    $display_incontinence_text = in_array('4', $selected_value_incontinence_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'incontinence_text', array(
	        'value'        => $options['incontinence_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'incontinence_text_show',
	        'style' => $display_incontinence_text
	    ));
	    
	    $subform->addElement('note',  "blank_2", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));

	    
/* 	    $subform->addElement('multiCheckbox', 'general_opt_3', array(
	        'value'        => strpbrk($options['general_opt'], '3') ? '1' : null,
	        'multiOptions' => array('1' => 'wears_diapers_opt'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */
	    
	    $subform->addElement('radio',  'wears_diapers_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['wears_diapers_opt'],
	        'label'        => $this->translate("wears_diapers_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '3') { $('.wears_diapers_text_show', $(this).parents('table')).show();} else { $('.wears_diapers_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_wears_diapers_text = $options['wears_diapers_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'wears_diapers_text', array(
	        'value'        => $options['wears_diapers_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'wears_diapers_text_show',
	        'style' => $display_wears_diapers_text
	    ));
	    
	    $subform->addElement('note',  "blank_3", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
/* 	    $subform->addElement('multiCheckbox', 'general_opt_4', array(
	        'value'        => strpbrk($options['general_opt'], '4') ? '1' : null,
	        'multiOptions' => array('1' => 'toilet_training_opt'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */
	    
 	    $subform->addElement('radio',  'toilet_training_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'Ja, tags', '3' => 'Ja, nachts', '4' => 'Sonstiges'),
	        'value'        => $options['toilet_training_opt'],
 	        'label'        => $this->translate("toilet_training_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '4') { $('.toilet_training_text_show', $(this).parents('table')).show();} else { $('.toilet_training_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
 	    $display_toilet_training_text = $options['toilet_training_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'toilet_training_text', array(
	        'value'        => $options['toilet_training_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	           // array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'toilet_training_text_show',
	        'style' => $display_toilet_training_text
	    ));
	    
/* 	    $subform->addElement('multiCheckbox', 'general_opt_5', array(
	        'value'        => strpbrk($options['general_opt'], '5') ? '1' : null,
	        'multiOptions' => array('1' => 'toilet_chair_opt'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */
	    
	    $subform->addElement('radio',  'toilet_chair_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['toilet_chair_opt'],
	        'label'        => $this->translate("toilet_chair_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	
/* 	    $subform->addElement('multiCheckbox', 'general_opt_6', array(
	        'value'        => strpbrk($options['general_opt'], '6') ? '1' : null,
	        'multiOptions' => array('1' => 'abdominal_massages_opt'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); */
	    
	    $subform->addElement('radio',  'abdominal_massages_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['abdominal_massages_opt'],
	        'label'        => $this->translate("abdominal_massages_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));

	    $subform->addElement('note',  "bowel_movement", array(
	        'value' => $this->translate("bowel_movement"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('text', 'bowel_movement_text', array(
	        'value'        => $options['bowel_movement_text'],
	        'label'        => $this->translate("Last bowel movement"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'consistency_text', array(
	        'value'        => $options['consistency_text'],
	        'label'        => $this->translate("consistency"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('text', 'frequency_text', array(
	        'value'        => $options['frequency_text'],
	        'label'        => $this->translate("frequency_text"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'stimulate_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['stimulate_opt'],
	        'label'        => $this->translate("stimulate_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'intestinal_tube_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['intestinal_tube_opt'],
	        'label'        => $this->translate("intestinal_tube_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'digital_clearing_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['digital_clearing_opt'],
	        'label'        => $this->translate("digital_clearing_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $subform->addElement('note',  "urninating", array(
	        'value' => $this->translate("urninating"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'uses_templates_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['uses_templates_opt'],
	        'label'        => $this->translate("uses_templates_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '3') { $('.uses_templates_text_show', $(this).parents('table')).show();} else { $('.uses_templates_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_uses_templates_text = $options['uses_templates_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'uses_templates_text', array(
	        'value'        => $options['uses_templates_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'uses_templates_text_show',
	        'style' => $display_uses_templates_text
	    ));
	    
	    $subform->addElement('note',  "blank_4", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'urine_bottle_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['urine_bottle_opt'],
	        'label'        => $this->translate("urine_bottle_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '3') { $('.urine_bottle_text_show', $(this).parents('table')).show();} else { $('.urine_bottle_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_urine_bottle_text = $options['urine_bottle_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'urine_bottle_text', array(
	        'value'        => $options['urine_bottle_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'urine_bottle_text_show',
	        'style' => $display_urine_bottle_text
	    ));
	    
	    $subform->addElement('note',  "blank_5", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'urine_condom_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['urine_condom_opt'],
	        'label'        => $this->translate("urine_condom_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '3') { $('.urine_condom_text_show', $(this).parents('table')).show();} else { $('.urine_condom_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_urine_condom_text = $options['urine_condom_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'urine_condom_text', array(
	        'value'        => $options['urine_condom_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'urine_condom_text_show',
	        'style' => $display_urine_condom_text
	    ));
	    
	    $subform->addElement('note',  "blank_6", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'catheterization_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['catheterization_opt'],
	        'label'        => $this->translate("catheterization_opt"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => "if(this.value == '3') { $('.catheterization_text_show', $(this).parents('table')).show();} else { $('.catheterization_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_catheterization_text = $options['catheterization_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'catheterization_text', array(
	        'value'        => $options['catheterization_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'catheterization_text_show',
	        'style' => $display_catheterization_text
	    ));
	    
	    $subform->addElement('note',  "Menstruation", array(
	        'value' => $this->translate("Menstruation"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));

	    $menstruation = PatientExcretionInfo::getMenstruation();
	    $subform->addElement('multiCheckbox', 'menstruation_opt', array(
	        'label'      => "",
	        'multiOptions' => $menstruation,
	        'required'   => false,
	        'value'    => isset($options['menstruation_opt']) && ! is_array($options['menstruation_opt']) ? array_map('trim', explode(",", $options['menstruation_opt'])) : $options['menstruation_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "4" && this.checked) {$(".menstruation_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".menstruation_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_menstruation_opt= $subform->getElement('menstruation_opt')->getValue();
	    $display_menstruation_text = in_array('4', $selected_menstruation_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'menstruation_text', array(
	        'value'        => $options['menstruation_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-30px; bottom: -4px")),
	            array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'menstruation_text_show',
	        'style' => $display_menstruation_text
	    ));
	    
	    $subform->addElement('note',  "vomit", array(
	        'value' => $this->translate("vomit_opt"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'vomit_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['vomit_opt'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_excretion_info($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
/* 	    $data['general_opt_1'] = isset($data['general_opt_1']) ?  '1,' : null;
	    $data['general_opt_2'] = isset($data['general_opt_2']) ?  '2,' : null;
	    $data['general_opt_3'] = isset($data['general_opt_3']) ?  '3,' : null;
	    $data['general_opt_4'] = isset($data['general_opt_4']) ?  '4,' : null;
	    $data['general_opt_5'] = isset($data['general_opt_5']) ?  '5,' : null;
	    $data['general_opt_6'] = isset($data['general_opt_6']) ?  '6' : null;
	    $data['general_opt'] = $data['general_opt_1'].$data['general_opt_2'].$data['general_opt_3'].$data['general_opt_4'].$data['general_opt_5'].$data['general_opt_6'];
 */	    
	    if (!in_array('4',$data['independence_opt'])){
	        $data['independence_text'] = "";
	    }
	    $data['independence_opt'] = isset($data['independence_opt']) ?  implode(",", $data['independence_opt']) : null;
	    
	    if (!in_array('4',$data['incontinence_opt'])){
	        $data['incontinence_text'] = "";
	    }
	    $data['incontinence_opt'] = isset($data['incontinence_opt']) ?  implode(",", $data['incontinence_opt']) : null;
	        
	    if ($data['wears_diapers_opt'] != 3){
	        $data['wears_diapers_text'] = "";
	    }

	    if ($data['toilet_training_opt'] != 4){
	        $data['toilet_training_text'] = "";
	    }

	    if ($data['uses_templates_opt'] != 3){
	        $data['uses_templates_text'] = "";
	    }
	    
	    if ($data['urine_bottle_opt'] != 3){
	        $data['urine_bottle_text'] = "";
	    }
	    
	    if ($data['urine_condom_opt'] != 3){
	        $data['urine_condom_text'] = "";
	    }
	    
	    if ($data['catheterization_opt'] != 3){
	        $data['catheterization_text'] = "";
	    }
	    
	    if (!in_array('4',$data['menstruation_opt'])){
	        $data['menstruation_text'] = "";
	    }
	    $data['menstruation_opt'] = isset($data['menstruation_opt']) ?  implode(",", $data['menstruation_opt']) : null;
	    
	    
	    $r = PatientExcretionInfoTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>