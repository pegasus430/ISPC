<?php
//ISPC-2792 Lore 15.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientPersonalHygiene extends Pms_Form
{
    protected $_model = 'PatientPersonalHygiene';

    public function getVersorgerExtract($param = null)
    {
        return array(
            //array( "label" => $this->translate('General'), "cols" => array("General")), 
            
            array( "label" => $this->translate('maintenance_condition'), "cols" => array("maintenance_condition")),
            array( "label" => $this->translate('mucosal_texture'), "cols" => array("mucosal_texture")),
            array( "label" => $this->translate('skin_texture'), "cols" => array("skin_texture")),
            array( "label" => $this->translate('assessment_scale_opt'), "cols" => array("assessment_scale")),
            array( "label" => $this->translate('pressure_ulcer_risk_opt'), "cols" => array("pressure_ulcer_risk")),
            //array( "label" => $this->translate('personal_hygiene'), "cols" => array("personal_hygiene")),
            array( "label" => $this->translate('pressure_ulcer_opt'), "cols" => array("pressure_ulcer")),
            array( "label" => $this->translate('own_care_products_opt'), "cols" => array("own_care_products")),
            array( "label" => $this->translate('nail_care_allowed_opt'), "cols" => array("nail_care_allowed")),
            array( "label" => $this->translate('basal_stimulation_opt'), "cols" => array("basal_stimulation")),
            array( "label" => $this->translate('habits_particularities'), "cols" => array("habits_particularities")),
            array( "label" => $this->translate('mattress_opt'), "cols" => array("mattress")),
            array( "label" => $this->translate('tools_opt'), "cols" => array("tools")),
            //array( "label" => $this->translate('Dental_and_oral_care'), "cols" => array("Dental_and_oral_care")),
            array( "label" => $this->translate('dental_care_opt'), "cols" => array("dental_care")),
            array( "label" => $this->translate('braces_opt'), "cols" => array("braces")),
            array( "label" => $this->translate('oral_care_opt'), "cols" => array("oral_care")),

        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientPersonalHygiene';
    
	
    public function create_form_block_patient_personalhygiene($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_personal_hygiene");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_personalhygiene');
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
        
        $subform->addElement('text', 'maintenance_condition', array(
            'value'        => $options['maintenance_condition'],
            'label'        => $this->translate("maintenance_condition"),
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
        
        $subform->addElement('text', 'mucosal_texture', array(
            'value'        => $options['mucosal_texture'],
            'label'        => $this->translate("mucosal_texture"),
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
        
        $subform->addElement('text', 'skin_texture', array(
            'value'        => $options['skin_texture'],
            'label'        => $this->translate("skin_texture"),
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
        
        if($options['formular_type'] == 'pdf' ){
            $assessment_scale = PatientPersonalHygiene::getAssessment_scale();
            $assessment_scale[3] = 'Sonstiges: '.$options['assessment_scale_text'];
            $subform->addElement('radio',  'assessment_scale_opt', array(
                'multiOptions' => $assessment_scale,
                'value'        => $options['assessment_scale_opt'],
                'label'        => $this->translate("assessment_scale_opt"),
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
            $assessment_scale = PatientPersonalHygiene::getAssessment_scale();
            $subform->addElement('radio',  'assessment_scale_opt', array(
                'multiOptions' => $assessment_scale,
                'value'        => $options['assessment_scale_opt'],
                'label'        => $this->translate("assessment_scale_opt"),
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
                'onChange' => "if(this.value == '3') { $('.assessment_scale_text_show', $(this).parents('table')).show();} else { $('.assessment_scale_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_assessment_scale = $options['assessment_scale_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'assessment_scale_text', array(
                'value'        => $options['assessment_scale_text'],
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
                'class'=>'assessment_scale_text_show',
                'style' => $display_assessment_scale
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
        

        if($options['formular_type'] == 'pdf' ){
            $pressure_ulcer_risk = PatientPersonalHygiene::getPressure_ulcer_risk();
            $pressure_ulcer_risk[5] = 'Anmerkungen: '.$options['pressure_ulcer_risk_text'];
            $subform->addElement('radio',  'pressure_ulcer_risk_opt', array(
                'multiOptions' => $pressure_ulcer_risk,
                'value'        => $options['pressure_ulcer_risk_opt'],
                'label'        => $this->translate("pressure_ulcer_risk_opt"),
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
            $pressure_ulcer_risk = PatientPersonalHygiene::getPressure_ulcer_risk();
            $subform->addElement('radio',  'pressure_ulcer_risk_opt', array(
                'multiOptions' => $pressure_ulcer_risk,
                'value'        => $options['pressure_ulcer_risk_opt'],
                'label'        => $this->translate("pressure_ulcer_risk_opt"),
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
                'onChange' => "if(this.value == '5') { $('.pressure_ulcer_risk_text_show', $(this).parents('table')).show();} else { $('.pressure_ulcer_risk_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_pressure_ulcer_risk_text = $options['pressure_ulcer_risk_opt'] == 5 ? '' : array('display:none');
            $subform->addElement('text', 'pressure_ulcer_risk_text', array(
                'value'        => $options['pressure_ulcer_risk_text'],
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
                'class'=>'pressure_ulcer_risk_text_show',
                'style' => $display_pressure_ulcer_risk_text
            ));
        }

        
        $subform->addElement('note',  "personal_hygiene", array(
            'value' => $this->translate("personal_hygiene"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'pressure_ulcer_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['pressure_ulcer_opt'],
                'label'        => $this->translate("pressure_ulcer_opt"),
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
        
            $display_pressure_ulcer_text = $options['pressure_ulcer_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'pressure_ulcer_text', array(
                'value'        => $options['pressure_ulcer_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'pressure_ulcer_text_show', 'style' => $display_pressure_ulcer_text)),
                ),
            ));
        } else {
            $subform->addElement('radio',  'pressure_ulcer_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['pressure_ulcer_opt'],
                'label'        => $this->translate("pressure_ulcer_opt"),
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
                'onChange' => "if(this.value == '3') { $('.pressure_ulcer_text_show', $(this).parents('table')).show();} else { $('.pressure_ulcer_text_show', $(this).parents('table')).hide();} ",
                
            ));
            
            $display_pressure_ulcer_text = $options['pressure_ulcer_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'pressure_ulcer_text', array(
                'value'        => $options['pressure_ulcer_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'pressure_ulcer_text_show', 'style' => $display_pressure_ulcer_text)),
                ),
            ));
        }
        
        $subform->addElement('radio',  'own_care_products_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['own_care_products_opt'],
            'label'        => $this->translate("own_care_products_opt"),
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
        
        $subform->addElement('radio',  'nail_care_allowed_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['nail_care_allowed_opt'],
            'label'        => $this->translate("nail_care_allowed_opt"),
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
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'basal_stimulation_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['basal_stimulation_opt'],
                'label'        => $this->translate("basal_stimulation_opt"),
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
            
            $subform->addElement('text', 'basal_stimulation_text', array(
                'value'        => $options['basal_stimulation_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('radio',  'basal_stimulation_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['basal_stimulation_opt'],
                'label'        => $this->translate("basal_stimulation_opt"),
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
                'onChange' => "if(this.value == '3') { $('.basal_stimulation_text_show', $(this).parents('table')).show();} else { $('.basal_stimulation_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_basal_stimulation_text = $options['basal_stimulation_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'basal_stimulation_text', array(
                'value'        => $options['basal_stimulation_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'basal_stimulation_text_show', 'style' => $display_basal_stimulation_text)),
                ),
            ));
        }

        
        $subform->addElement('text', 'habits_particularities', array(
            'value'        => $options['habits_particularities'],
            'label'        => $this->translate("habits_particularities"),
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
        
        if($options['formular_type'] == 'pdf' ){
            $mattress = PatientPersonalHygiene::getMattress();
            $mattress[3] = 'Sonstiges: '.$options['mattress_text'];
            $subform->addElement('multiCheckbox', 'mattress_opt', array(
                'label'      => $this->translate("mattress_opt"),
                'multiOptions' => $mattress,
                'required'   => false,
                'value'    => isset($options['mattress_opt']) && ! is_array($options['mattress_opt']) ? array_map('trim', explode(",", $options['mattress_opt'])) : $options['mattress_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));
        } else {
            $mattress = PatientPersonalHygiene::getMattress();
            $subform->addElement('multiCheckbox', 'mattress_opt', array(
                'label'      => $this->translate("mattress_opt"),
                'multiOptions' => $mattress,
                'required'   => false,
                'value'    => isset($options['mattress_opt']) && ! is_array($options['mattress_opt']) ? array_map('trim', explode(",", $options['mattress_opt'])) : $options['mattress_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "3" && this.checked) {$(".mattress_text_show", $(this).parents(\'table\')).show();} else if(this.value == "3") {$(".mattress_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_mattress_opt = $subform->getElement('mattress_opt')->getValue();
            $display_mattress_text = in_array('3', $selected_value_mattress_opt) ? '' : 'display:none';
            $subform->addElement('text', 'mattress_text', array(
                'value'        => $options['mattress_text'],
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
                'class'=>'mattress_text_show',
                'style' => $display_mattress_text
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

        $tools = PatientPersonalHygiene::getTools();
        $subform->addElement('multiCheckbox', 'tools_opt', array(
            'label'      => $this->translate("tools_opt"),
            'multiOptions' => $tools,
            'required'   => false,
            'value'    => isset($options['tools_opt']) && ! is_array($options['tools_opt']) ? array_map('trim', explode(",", $options['tools_opt'])) : $options['tools_opt'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('note',  "Dental_and_oral_care", array(
            'value' => $this->translate("Dental_and_oral_care"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        $subform->addElement('radio',  'dental_care_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['dental_care_opt'],
            'label'        => $this->translate("dental_care_opt"),
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
            'onChange' => "if(this.value == '3') { $('.dental_care_text_show', $(this).parents('table')).show();} else { $('.dental_care_text_show', $(this).parents('table')).hide();} ",
        ));
        
        $display_dental_care_text = $options['dental_care_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'dental_care_text', array(
            'value'        => $options['dental_care_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'dental_care_text_show', 'style' => $display_dental_care_text)),
            ),
        ));

        
        $subform->addElement('radio',  'braces_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['braces_opt'],
            'label'        => $this->translate("braces_opt"),
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
            'onChange' => "if(this.value == '3') { $('.braces_text_show', $(this).parents('table')).show();} else { $('.braces_text_show', $(this).parents('table')).hide();} ",
        ));
        
        $display_braces_text = $options['braces_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'braces_text', array(
            'value'        => $options['braces_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'braces_text_show', 'style' => $display_braces_text)),
            ),
        ));
        
        $subform->addElement('radio',  'oral_care_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['oral_care_opt'],
            'label'        => $this->translate("oral_care_opt"),
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
            'onChange' => "if(this.value == '3') { $('.oral_care_text_show', $(this).parents('table')).show();} else { $('.oral_care_text_show', $(this).parents('table')).hide();} ",
        ));
        
        $display_oral_care_text = $options['oral_care_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'oral_care_text', array(
            'value'        => $options['oral_care_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'oral_care_text_show', 'style' => $display_oral_care_text)),
            ),
        ));
            
                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_personal_hygiene($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_personal_hygiene");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientPersonalHygiene_legend'));
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

	    $subform->addElement('text', 'maintenance_condition', array(
	        'value'        => $options['maintenance_condition'],
	        'label'        => $this->translate("maintenance_condition"),
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
	    
	    $subform->addElement('text', 'mucosal_texture', array(
	        'value'        => $options['mucosal_texture'],
	        'label'        => $this->translate("mucosal_texture"),
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
	    
	    $subform->addElement('text', 'skin_texture', array(
	        'value'        => $options['skin_texture'],
	        'label'        => $this->translate("skin_texture"),
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
	    
	    $assessment_scale = PatientPersonalHygiene::getAssessment_scale();
	    $subform->addElement('radio',  'assessment_scale_opt', array(
	        'multiOptions' => $assessment_scale,
	        'value'        => $options['assessment_scale_opt'],
	        'label'        => $this->translate("assessment_scale_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.assessment_scale_text_show', $(this).parents('table')).show();} else { $('.assessment_scale_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_assessment_scale = $options['assessment_scale_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'assessment_scale_text', array(
	        'value'        => $options['assessment_scale_text'],
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
	        'class'=>'assessment_scale_text_show',
	        'style' => $display_assessment_scale
	    ));
	    
	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $pressure_ulcer_risk = PatientPersonalHygiene::getPressure_ulcer_risk();
	    $subform->addElement('radio',  'pressure_ulcer_risk_opt', array(
	        'multiOptions' => $pressure_ulcer_risk,
	        'value'        => $options['pressure_ulcer_risk_opt'],
	        'label'        => $this->translate("pressure_ulcer_risk_opt"),
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
	        'onChange' => "if(this.value == '5') { $('.pressure_ulcer_risk_text_show', $(this).parents('table')).show();} else { $('.pressure_ulcer_risk_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_pressure_ulcer_risk_text = $options['pressure_ulcer_risk_opt'] == 5 ? '' : array('display:none');
	    $subform->addElement('text', 'pressure_ulcer_risk_text', array(
	        'value'        => $options['pressure_ulcer_risk_text'],
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
	        'class'=>'pressure_ulcer_risk_text_show',
	        'style' => $display_pressure_ulcer_risk_text
	    ));
	    
	    
	    $subform->addElement('note',  "personal_hygiene", array(
	        'value' => $this->translate("personal_hygiene"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'pressure_ulcer_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['pressure_ulcer_opt'],
	        'label'        => $this->translate("pressure_ulcer_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.pressure_ulcer_text_show', $(this).parents('table')).show();} else { $('.pressure_ulcer_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_pressure_ulcer_text = $options['pressure_ulcer_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'pressure_ulcer_text', array(
	        'value'        => $options['pressure_ulcer_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'pressure_ulcer_text_show', 'style' => $display_pressure_ulcer_text)),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'own_care_products_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['own_care_products_opt'],
	        'label'        => $this->translate("own_care_products_opt"),
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
	    
	    $subform->addElement('radio',  'nail_care_allowed_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['nail_care_allowed_opt'],
	        'label'        => $this->translate("nail_care_allowed_opt"),
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

	    $subform->addElement('radio',  'basal_stimulation_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['basal_stimulation_opt'],
	        'label'        => $this->translate("basal_stimulation_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.basal_stimulation_text_show', $(this).parents('table')).show();} else { $('.basal_stimulation_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_basal_stimulation_text = $options['basal_stimulation_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'basal_stimulation_text', array(
	        'value'        => $options['basal_stimulation_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'basal_stimulation_text_show', 'style' => $display_basal_stimulation_text)),
	        ),
	    ));
	    
	    $subform->addElement('text', 'habits_particularities', array(
	        'value'        => $options['habits_particularities'],
	        'label'        => $this->translate("habits_particularities"),
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
	    
	    $mattress = PatientPersonalHygiene::getMattress();
	    $subform->addElement('multiCheckbox', 'mattress_opt', array(
	        'label'      => $this->translate("mattress_opt"),
	        'multiOptions' => $mattress,
	        'required'   => false,
	        'value'    => isset($options['mattress_opt']) && ! is_array($options['mattress_opt']) ? array_map('trim', explode(",", $options['mattress_opt'])) : $options['mattress_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "3" && this.checked) {$(".mattress_text_show", $(this).parents(\'table\')).show();} else if(this.value == "3") {$(".mattress_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_mattress_opt = $subform->getElement('mattress_opt')->getValue();
	    $display_mattress_text = in_array('3', $selected_value_mattress_opt) ? '' : 'display:none';
	    $subform->addElement('text', 'mattress_text', array(
	        'value'        => $options['mattress_text'],
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
	        'class'=>'mattress_text_show',
	        'style' => $display_mattress_text
	    ));
	    
	    $subform->addElement('note',  "blank_2", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $tools = PatientPersonalHygiene::getTools();
	    $subform->addElement('multiCheckbox', 'tools_opt', array(
	        'label'      => $this->translate("tools_opt"),
	        'multiOptions' => $tools,
	        'required'   => false,
	        'value'    => isset($options['tools_opt']) && ! is_array($options['tools_opt']) ? array_map('trim', explode(",", $options['tools_opt'])) : $options['tools_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	    $subform->addElement('note',  "Dental_and_oral_care", array(
	        'value' => $this->translate("Dental_and_oral_care"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'dental_care_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['dental_care_opt'],
	        'label'        => $this->translate("dental_care_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.dental_care_text_show', $(this).parents('table')).show();} else { $('.dental_care_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_dental_care_text = $options['dental_care_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'dental_care_text', array(
	        'value'        => $options['dental_care_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'dental_care_text_show', 'style' => $display_dental_care_text)),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'braces_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['braces_opt'],
	        'label'        => $this->translate("braces_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.braces_text_show', $(this).parents('table')).show();} else { $('.braces_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_braces_text = $options['braces_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'braces_text', array(
	        'value'        => $options['braces_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'braces_text_show', 'style' => $display_braces_text)),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'oral_care_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['oral_care_opt'],
	        'label'        => $this->translate("oral_care_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.oral_care_text_show', $(this).parents('table')).show();} else { $('.oral_care_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_oral_care_text = $options['oral_care_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'oral_care_text', array(
	        'value'        => $options['oral_care_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'oral_care_text_show', 'style' => $display_oral_care_text)),
	        ),
	    ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_personal_hygiene($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if ($data['assessment_scale_opt'] != 3){
	        $data['assessment_scale_text'] = "";
	    }
	    if ($data['pressure_ulcer_risk_opt'] != 5){
	        $data['pressure_ulcer_risk_text'] = "";
	    }
	    if ($data['pressure_ulcer_opt'] != 3){
	        $data['pressure_ulcer_text'] = "";
	    }
	    if ($data['basal_stimulation_opt'] != 3){
	        $data['basal_stimulation_text'] = "";
	    }
	    
	    if (!in_array('3',$data['mattress_opt'])){
	        $data['mattress_text'] = "";
	    }
	    $data['mattress_opt'] = isset($data['mattress_opt']) ?  implode(",", $data['mattress_opt']) : null;
	    $data['tools_opt'] = isset($data['tools_opt']) ?  implode(",", $data['tools_opt']) : null;
	    
	    if ($data['dental_care_opt'] != 3){
	        $data['dental_care_text'] = "";
	    }
	    if ($data['braces_opt'] != 3){
	        $data['braces_text'] = "";
	    }
	    if ($data['oral_care_opt'] != 3){
	        $data['oral_care_text'] = "";
	    }

	    $r = PatientPersonalHygieneTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>