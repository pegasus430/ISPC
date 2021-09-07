<?php
//ISPC-2788 Lore 08.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientNutritionInfo extends Pms_Form
{
    protected $_model = 'PatientNutritionInfo';

    public function getVersorgerExtract($param = null)
    {
        return array(
            //array( "label" => $this->translate('General'), "cols" => array("allgemein")),
            array( "label" => $this->translate('nutritional_status'), "cols" => array("nutritional_status")),
            array( "label" => $this->translate('allergies'), "cols" => array("allergies")),
            //array( "label" => $this->translate('food'), "cols" => array("food")),
            array( "label" => $this->translate('oral_opt'), "cols" => array("oral")),
            array( "label" => $this->translate('oral_offer_opt'), "cols" => array("oral_offer")),
            array( "label" => $this->translate('tube_feeding_opt'), "cols" => array("tube_feeding")),
            array( "label" => $this->translate('rinsing_required_opt'), "cols" => array("rinsing_required")),
            array( "label" => $this->translate('food_consistency_opt'), "cols" => array("food_consistency")),
            array( "label" => $this->translate('independence'), "cols" => array("independence_stm")),
            array( "label" => $this->translate('enrichment_required_opt'), "cols" => array("enrichment_required")),
            array( "label" => $this->translate('food_preferences_text'), "cols" => array("food_preferences_text")),
            array( "label" => $this->translate('food_dislikes_text'), "cols" => array("food_dislikes_text")),
            array( "label" => $this->translate('food_particular_label'), "cols" => array("food_particular_text")),
            array( "label" => $this->translate('food_meals_text'), "cols" => array("food_meals_text")),
            //array( "label" => $this->translate('liquid'), "cols" => array("liquid")),
            array( "label" => $this->translate('manufacturer_text'), "cols" => array("manufacturer_text")),
            array( "label" => $this->translate('thicken_opt'), "cols" => array("thicken")),
            array( "label" => $this->translate('administration_opt'), "cols" => array("administration")),
            array( "label" => $this->translate('amount_text'), "cols" => array("amount_text")),
            array( "label" => $this->translate('liquid_preferences_text'), "cols" => array("liquid_preferences_text")),
            array( "label" => $this->translate('liquid_dislikes_text'), "cols" => array("liquid_dislikes_text")),
            
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientNutritionInfo';
    
	
    public function create_form_block_patient_nutritioninfo($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_nutrition_info");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_nutritioninfo');
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
        
        $subform->addElement('note',  "general", array(
            'value' => $this->translate("General"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        $subform->addElement('text', 'nutritional_status', array(
            'value'        => $options['nutritional_status'] ,
            'label'        => $this->translate('nutritional_status'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('radio',  'allergies_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['allergies_opt'],
            'label'        => $this->translate("allergies_opt"),
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
            'onChange' => "if(this.value == '3') { $('.allergies_text_show', $(this).parents('table')).show();} else { $('.allergies_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_allergies_text = $options['allergies_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'allergies_text', array(
            'value'        => $options['allergies_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'allergies_text_show', 'style' => $display_allergies_text)),
            ),
        ));
        
        $subform->addElement('note',  "food", array(
            'value' => $this->translate("food"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));

        $subform->addElement('radio',  'oral_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['oral_opt'],
            'label'        => $this->translate("oral_opt"),
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
            'onChange' => "if(this.value == '3') { $('.oral_text_show', $(this).parents('table')).show();} else { $('.oral_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_oral_text = $options['oral_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'oral_text', array(
            'value'        => $options['oral_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td' )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'oral_text_show', 'style' => $display_oral_text )),
            ),
        ));
        
        $subform->addElement('radio',  'oral_offer_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['oral_offer_opt'],
            'label'        => $this->translate("oral_offer_opt"),
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
            'onChange' => "if(this.value == '3') { $('.oral_offer_text_show', $(this).parents('table')).show();} else { $('.oral_offer_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_oral_offer_text = $options['oral_offer_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'oral_offer_text', array(
            'value'        => $options['oral_offer_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td' )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'oral_offer_text_show', 'style' => $display_oral_offer_text )),
            ),
        ));
        
        $subform->addElement('radio',  'tube_feeding_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['tube_feeding_opt'],
            'label'        => $this->translate("tube_feeding_opt"),
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
            'onChange' => "if(this.value == '3') { $('.tube_feeding_text_show', $(this).parents('table')).show();} else { $('.tube_feeding_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_tube_feeding_text = $options['tube_feeding_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'tube_feeding_text', array(
            'value'        => $options['tube_feeding_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'tube_feeding_text_show', 'style' => $display_tube_feeding_text)),
            ),
        ));
        
        $subform->addElement('radio',  'rinsing_required_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['rinsing_required_opt'],
            'label'        => $this->translate("rinsing_required_opt"),
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
            'onChange' => "if(this.value == '3') { $('.rinsing_required_text_show', $(this).parents('table')).show();} else { $('.rinsing_required_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_rinsing_required_text = $options['rinsing_required_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'rinsing_required_text', array(
            'value'        => $options['rinsing_required_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'rinsing_required_text_show', 'style' => $display_rinsing_required_text)),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $food_consistency = PatientNutritionInfo::getFood_consistencys();
            $food_consistency[6] = $food_consistency[6].': '.$options['food_consistency_text'];
            $subform->addElement('multiCheckbox', 'food_consistency_opt', array(
                'label'      => $this->translate("food_consistency_opt"),
                'multiOptions' => $food_consistency,
                'required'   => false,
                'value'    => isset($options['food_consistency_opt']) && ! is_array($options['food_consistency_opt']) ? array_map('trim', explode(",", $options['food_consistency_opt'])) : $options['food_consistency_opt'],
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
            
        }else {
            $food_consistency = PatientNutritionInfo::getFood_consistencys();
            $subform->addElement('multiCheckbox', 'food_consistency_opt', array(
                'label'      => $this->translate("food_consistency_opt"),
                'multiOptions' => $food_consistency,
                'required'   => false,
                'value'    => isset($options['food_consistency_opt']) && ! is_array($options['food_consistency_opt']) ? array_map('trim', explode(",", $options['food_consistency_opt'])) : $options['food_consistency_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "6" && this.checked) {$(".food_consistency_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".food_consistency_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_food_consistency_opt = $subform->getElement('food_consistency_opt')->getValue();
            $display_food_consistency_text = in_array('6', $selected_value_food_consistency_opt) ? '' : 'display:none';
            $subform->addElement('text',  'food_consistency_text', array(
                'value'        => $options['food_consistency_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-170px; bottom: 0px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'food_consistency_text_show',
                'style' => $display_food_consistency_text
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
            $independence = PatientNutritionInfo::getIndependence();
            $independence[3] = $independence[3].': '.$options['independence_text'];
            $subform->addElement('multiCheckbox', 'independence', array(
                'label'      => $this->translate("independence"),
                'multiOptions' => $independence,
                'required'   => false,
                'value'    => isset($options['independence']) && ! is_array($options['independence']) ? array_map('trim', explode(",", $options['independence'])) : $options['independence'],
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
            $independence = PatientNutritionInfo::getIndependence();
            $subform->addElement('multiCheckbox', 'independence', array(
                'label'      => $this->translate("independence"),
                'multiOptions' => $independence,
                'required'   => false,
                'value'    => isset($options['independence']) && ! is_array($options['independence']) ? array_map('trim', explode(",", $options['independence'])) : $options['independence'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                   // array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "3" && this.checked) {$(".independence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "3") {$(".independence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_independence = $subform->getElement('independence')->getValue();
            $display_independence_text = in_array('3', $selected_value_independence) ? '' : 'display:none';
            $subform->addElement('text',  'independence_text', array(
                'value'        => $options['independence_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-170px; bottom: 0px")),
                    array('Label', array('tag' => 'td' )),
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
        

        
        $subform->addElement('radio',  'enrichment_required_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['enrichment_required_opt'],
            'label'        => $this->translate("enrichment_required_opt"),
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
            'onChange' => "if(this.value == '3') { $('.enrichment_required_text_show', $(this).parents('table')).show();} else { $('.enrichment_required_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_enrichment_required_text = $options['enrichment_required_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'enrichment_required_text', array(
            'value'        => $options['enrichment_required_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'enrichment_required_text_show', 'style' => $display_enrichment_required_text)),
            ),
        ));
        
        $subform->addElement('text', 'food_preferences_text', array(
            'value'        => $options['food_preferences_text'] ,
            'label'        => $this->translate('food_preferences_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('text', 'food_dislikes_text', array(
            'value'        => $options['food_dislikes_text'] ,
            'label'        => $this->translate('food_dislikes_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('text', 'food_particular_text', array(
            'value'        => $options['food_particular_text'] ,
            'label'        => $this->translate('food_particular_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('text', 'food_meals_text', array(
            'value'        => $options['food_meals_text'] ,
            'label'        => $this->translate('food_meals_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));

        $subform->addElement('note',  "liquid", array(
            'value' => $this->translate("liquid"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));

        $subform->addElement('text', 'manufacturer_text', array(
            'value'        => $options['manufacturer_text'] ,
            'label'        => $this->translate('manufacturer_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('radio',  'thicken_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['thicken_opt'],
            'label'        => $this->translate("thicken_opt"),
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
            'onChange' => "if(this.value == '3') { $('.thicken_text_show', $(this).parents('table')).show();} else { $('.thicken_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_thicken_text = $options['thicken_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'thicken_text', array(
            'value'        => $options['thicken_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'thicken_text_show', 'style' => $display_thicken_text)),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $administration = PatientNutritionInfo::getAdministration();
            $administration[6] = $administration[6].': '.$options['administration_text'];
            $subform->addElement('multiCheckbox', 'administration_opt', array(
                'label'      => $this->translate("administration_opt"),
                'multiOptions' => $administration,
                'required'   => false,
                'value'    => isset($options['administration_opt']) && ! is_array($options['administration_opt']) ? array_map('trim', explode(",", $options['administration_opt'])) : $options['administration_opt'],
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
            
        }else {
            $administration = PatientNutritionInfo::getAdministration();
            $subform->addElement('multiCheckbox', 'administration_opt', array(
                'label'      => $this->translate("administration_opt"),
                'multiOptions' => $administration,
                'required'   => false,
                'value'    => isset($options['administration_opt']) && ! is_array($options['administration_opt']) ? array_map('trim', explode(",", $options['administration_opt'])) : $options['administration_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "6" && this.checked) {$(".administration_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".administration_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_administration_opt = $subform->getElement('administration_opt')->getValue();
            $display_administration_text = in_array('6', $selected_administration_opt) ? '' : 'display:none';
            $subform->addElement('text',  'administration_text', array(
                'value'        => $options['administration_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-170px; bottom: 0px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'administration_text_show',
                'style' => $display_administration_text
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

        
        $subform->addElement('text', 'amount_text', array(
            'value'        => $options['amount_text'] ,
            'label'        => $this->translate('amount_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('text', 'liquid_preferences_text', array(
            'value'        => $options['liquid_preferences_text'] ,
            'label'        => $this->translate('liquid_preferences_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
        $subform->addElement('text', 'liquid_dislikes_text', array(
            'value'        => $options['liquid_dislikes_text'] ,
            'label'        => $this->translate('liquid_dislikes_text'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
        
                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_nutrition_info($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_nutrition_info");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientNutritionInfo_legend'));
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
	    
	    $subform->addElement('note',  "general", array(
	        'value' => $this->translate("General"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('text', 'nutritional_status', array(
	        'value'        => $options['nutritional_status'] ,
	        'label'        => $this->translate('nutritional_status'),
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
	    
	    $subform->addElement('radio',  'allergies_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['allergies_opt'],
	        'label'        => $this->translate("allergies_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.allergies_text_show', $(this).parents('table')).show();} else { $('.allergies_text_show', $(this).parents('table')).hide();} ",
	        
	    ));

	    $display_allergies_text = $options['allergies_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'allergies_text', array(
	        'value'        => $options['allergies_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'allergies_text_show', 'style' => $display_allergies_text)),
	        ),
	        'class'=>'allergies_text_show', 'style' => 'max-width:308px!important;'.$display_allergies_text
	    ));
	    
	    $subform->addElement('note',  "blank_2", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('note',  "food", array(
	        'value' => $this->translate("food"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('note',  "blank_3", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('radio',  'oral_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['oral_opt'],
	        'label'        => $this->translate("oral_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.oral_text_show', $(this).parents('table')).show();} else { $('.oral_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_oral_text = $options['oral_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'oral_text', array(
	        'value'        => $options['oral_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td' )),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'oral_text_show', 'style' => $display_oral_text )),
	        ),
	        'class'=>'oral_text_show', 'style' => 'max-width:308px!important;'.$display_oral_text
	    ));

	    $subform->addElement('note',  "blank_31", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('radio',  'oral_offer_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['oral_offer_opt'],
	        'label'        => $this->translate("oral_offer_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.oral_offer_text_show', $(this).parents('table')).show();} else { $('.oral_offer_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_oral_offer_text = $options['oral_offer_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'oral_offer_text', array(
	        'value'        => $options['oral_offer_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td' )),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'oral_offer_text_show', 'style' => $display_oral_offer_text )),
	        ),
	        'class'=>'oral_offer_text_show', 'style' => 'max-width:308px!important;'.$display_oral_offer_text
	    ));
	    
	    $subform->addElement('note',  "blank_32", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('radio',  'tube_feeding_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['tube_feeding_opt'],
	        'label'        => $this->translate("tube_feeding_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.tube_feeding_text_show', $(this).parents('table')).show();} else { $('.tube_feeding_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_tube_feeding_text = $options['tube_feeding_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'tube_feeding_text', array(
	        'value'        => $options['tube_feeding_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'tube_feeding_text_show', 'style' => $display_tube_feeding_text)),
	        ),
	        'class'=>'tube_feeding_text_show', 'style' => 'max-width:308px!important;'.$display_tube_feeding_text
	    ));
	    
	    $subform->addElement('note',  "blank_33", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'rinsing_required_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['rinsing_required_opt'],
	        'label'        => $this->translate("rinsing_required_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.rinsing_required_text_show', $(this).parents('table')).show();} else { $('.rinsing_required_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_rinsing_required_text = $options['rinsing_required_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'rinsing_required_text', array(
	        'value'        => $options['rinsing_required_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'rinsing_required_text_show', 'style' => $display_rinsing_required_text)),
	        ),
	        'class'=>'rinsing_required_text_show', 'style' => 'max-width:308px!important;'.$display_rinsing_required_text
	    ));
	    
	    $subform->addElement('note',  "blank_34", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $food_consistency = PatientNutritionInfo::getFood_consistencys();
	    $subform->addElement('multiCheckbox', 'food_consistency_opt', array(
	        'label'      => $this->translate("food_consistency_opt"),
	        'multiOptions' => $food_consistency,
	        'required'   => false,
	        'value'    => isset($options['food_consistency_opt']) && ! is_array($options['food_consistency_opt']) ? array_map('trim', explode(",", $options['food_consistency_opt'])) : $options['food_consistency_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'nutrition_other_width_label multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "6" && this.checked) {$(".food_consistency_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".food_consistency_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_food_consistency_opt = $subform->getElement('food_consistency_opt')->getValue();
	    $display_food_consistency_text = in_array('6', $selected_value_food_consistency_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'food_consistency_text', array(
	        'value'        => $options['food_consistency_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-288px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'food_consistency_text_show',
	        'style' => 'max-width:275px!important;'.$display_food_consistency_text
	    ));
	    
	    $subform->addElement('note',  "blank_a", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    )); 
	    
	    $independence = PatientNutritionInfo::getIndependence();
	    $subform->addElement('multiCheckbox', 'independence', array(
	        'label'      => $this->translate("independence"),
	        'multiOptions' => $independence,
	        'required'   => false,
	        'value'    => isset($options['independence']) && ! is_array($options['independence']) ? array_map('trim', explode(",", $options['independence'])) : $options['independence'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'nutrition_other_width_label multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	           // array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "3" && this.checked) {$(".independence_text_show", $(this).parents(\'table\')).show();} else if(this.value == "3") {$(".independence_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_independence = $subform->getElement('independence')->getValue();
	    $display_independence_text = in_array('3', $selected_value_independence) ? '' : 'display:none';
	    $subform->addElement('text',  'independence_text', array(
	        'value'        => $options['independence_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-288px; bottom: 0px")),
	            //array('Label', array('tag' => 'td' )),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'independence_text_show',
	        'style' => 'max-width:275px!important;'.$display_independence_text
	    ));
	    
	    $subform->addElement('note',  "blank_36", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'enrichment_required_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['enrichment_required_opt'],
	        'label'        => $this->translate("enrichment_required_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.enrichment_required_text_show', $(this).parents('table')).show();} else { $('.enrichment_required_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_enrichment_required_text = $options['enrichment_required_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'enrichment_required_text', array(
	        'value'        => $options['enrichment_required_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'enrichment_required_text_show', 'style' => $display_enrichment_required_text)),
	        ),
	        'class'=>'enrichment_required_text_show', 'style' => 'max-width:308px!important;'.$display_enrichment_required_text
	    ));
	    
	    $subform->addElement('text', 'food_preferences_text', array(
	        'value'        => $options['food_preferences_text'] ,
	        'label'        => $this->translate('food_preferences_text'),
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
	    
	    $subform->addElement('text', 'food_dislikes_text', array(
	        'value'        => $options['food_dislikes_text'] ,
	        'label'        => $this->translate('food_dislikes_text'),
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
	    
	    $subform->addElement('text', 'food_particular_text', array(
	        'value'        => $options['food_particular_text'] ,
	        'label'        => $this->translate('food_particular_text'),
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
	    
	    $subform->addElement('text', 'food_meals_text', array(
	        'value'        => $options['food_meals_text'] ,
	        'label'        => $this->translate('food_meals_text'),
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
	    
	    $subform->addElement('note',  "blank_4", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('note',  "liquid", array(
	        'value' => $this->translate("liquid"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    $subform->addElement('note',  "blank_5", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'manufacturer_text', array(
	        'value'        => $options['manufacturer_text'] ,
	        'label'        => $this->translate('manufacturer_text'),
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
	    
	    $subform->addElement('note',  "blank_41", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'thicken_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['thicken_opt'],
	        'label'        => $this->translate("thicken_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.thicken_text_show', $(this).parents('table')).show();} else { $('.thicken_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_thicken_text = $options['thicken_opt'] == 3 ? '' : 'display:none';
	    $subform->addElement('text', 'thicken_text', array(
	        'value'        => $options['thicken_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-320px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'thicken_text_show', 'style' => $display_thicken_text)),
	        ),
	        'class'=>'thicken_text_show', 'style' => 'max-width:308px!important;'.$display_thicken_text
	    ));
	    $subform->addElement('note',  "blank_42", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $administration = PatientNutritionInfo::getAdministration();
	    $subform->addElement('multiCheckbox', 'administration_opt', array(
	        'label'      => $this->translate("administration_opt"),
	        'multiOptions' => $administration,
	        'required'   => false,
	        'value'    => isset($options['administration_opt']) && ! is_array($options['administration_opt']) ? array_map('trim', explode(",", $options['administration_opt'])) : $options['administration_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "6" && this.checked) {$(".administration_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".administration_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_administration_opt = $subform->getElement('administration_opt')->getValue();
	    $display_administration_text = in_array('6', $selected_administration_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'administration_text', array(
	        'value'        => $options['administration_text'],
	        //'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none; vertical-align: bottom; position:relative; left:-288px; bottom: -2px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'administration_text_show',
	        'style' => 'max-width:275px!important;'.$display_administration_text
	    ));
	    
	    $subform->addElement('text', 'amount_text', array(
	        'value'        => $options['amount_text'] ,
	        'label'        => $this->translate('amount_text'),
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
	    
	    $subform->addElement('text', 'liquid_preferences_text', array(
	        'value'        => $options['liquid_preferences_text'] ,
	        'label'        => $this->translate('liquid_preferences_text'),
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
	    
	    $subform->addElement('text', 'liquid_dislikes_text', array(
	        'value'        => $options['liquid_dislikes_text'] ,
	        'label'        => $this->translate('liquid_dislikes_text'),
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
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_nutrition_info($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if ($data['allergies_opt'] != 3){
	        $data['allergies_text'] = "";
	    }
	    if ($data['oral_opt'] != 3 ){
	        $data['oral_text'] = "";
	    }
	    if ($data['oral_offer_opt'] != 3){
	        $data['oral_offer_text'] = "";
	    }
	    if ($data['tube_feeding_opt'] != 3){
	        $data['tube_feeding_text'] = "";
	    }
	    if ($data['rinsing_required_opt'] !=3 ){
	        $data['rinsing_required_text'] = "";
	    }
	    
	    if (!in_array('6',$data['food_consistency_opt'])){
	        $data['food_consistency_text'] = "";
	    }
	    $data['food_consistency_opt'] = isset($data['food_consistency_opt']) ?  implode(",", $data['food_consistency_opt']) : null;
	    
	    if (!in_array('3',$data['independence'])){
	        $data['independence_text'] = "";
	    }
	    $data['independence'] = isset($data['independence']) ?  implode(",", $data['independence']) : null;
	    
	    if ($data['enrichment_required_opt'] != 3){
	        $data['enrichment_required_text'] = "";
	    }
	    if ($data['thicken_opt'] != 3){
	        $data['thicken_text'] = "";
	    }
    
	    if (!in_array('6',$data['administration_opt'])){
	        $data['administration_text'] = "";
	    }
	    $data['administration_opt'] = isset($data['administration_opt']) ?  implode(",", $data['administration_opt']) : null;

	    
	    $r = PatientNutritionInfoTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>