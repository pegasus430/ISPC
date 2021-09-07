<?php
//ISPC-2790 Lore 12.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientFinalPhase extends Pms_Form
{
    protected $_model = 'PatientFinalPhase';

    public function getVersorgerExtract($param = null)
    {
        return array(
            //array( "label" => $this->translate('preparation'), "cols" => array("preparation")),
            array( "label" => $this->translate('death_discussed_opt'), "cols" => array("death_discussed")),
            array( "label" => $this->translate('undertaker_informed_opt'), "cols" => array("undertaker_informed")),
            array( "label" => $this->translate('coffin_chosen_opt'), "cols" => array("coffin_chosen")),
            array( "label" => $this->translate('how_was_informed_opt'), "cols" => array("was_informed")),
            //array( "label" => $this->translate('After_dying'), "cols" => array("After_dying")),
            array( "label" => $this->translate('care_child_opt'), "cols" => array("care_child")),
            array( "label" => $this->translate('laying_out_opt'), "cols" => array("laying_out")),
            array( "label" => $this->translate('memento_desired_opt'), "cols" => array("memento_desired")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientFinalPhase';
    
	
    public function create_form_block_patient_finalphase($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_final_phase");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_finalphase');
        $subform->setAttrib("class", "label_same_size finalphase {$__fnName}");
        
        
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
        
        $subform->addElement('note',  "preparation", array(
            'value' => $this->translate("preparation"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'death_discussed_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges: '.$options['death_discussed_text'] ),
                'value'        => $options['death_discussed_opt'],
                'label'        => $this->translate("death_discussed_opt"),
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
            $subform->addElement('radio',  'death_discussed_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
                'value'        => $options['death_discussed_opt'],
                'label'        => $this->translate("death_discussed_opt"),
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
                'onChange' => "if(this.value == '4') { $('.death_discussed_text_show', $(this).parents('table')).show();} else { $('.death_discussed_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_death_discussed_text = $options['death_discussed_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'death_discussed_text', array(
                'value'        => $options['death_discussed_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'class'        => 'w150',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'death_discussed_text_show',
                'style' => $display_death_discussed_text,
            ));
            
            $subform->addElement('note',  "blank_1", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th' )),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                ),
            ));
        }
        
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'undertaker_informed_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges: '. $options['undertaker_informed_text']),
                'value'        => $options['undertaker_informed_opt'],
                'label'        => $this->translate("undertaker_informed_opt"),
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
            $subform->addElement('radio',  'undertaker_informed_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
                'value'        => $options['undertaker_informed_opt'],
                'label'        => $this->translate("undertaker_informed_opt"),
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
                'onChange' => "if(this.value == '4') { $('.undertaker_informed_text_show', $(this).parents('table')).show();} else { $('.undertaker_informed_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_undertaker_informed_text = $options['undertaker_informed_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'undertaker_informed_text', array(
                'value'        => $options['undertaker_informed_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    // array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'undertaker_informed_text_show',
                'style' => $display_undertaker_informed_text
            ));
            $subform->addElement('note',  "blank_2", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                ),
            ));
        }
            
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'coffin_chosen_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges: '.$options['coffin_chosen_text'] ),
                'value'        => $options['coffin_chosen_opt'],
                'label'        => $this->translate("coffin_chosen_opt"),
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
            
        }else {
            $subform->addElement('radio',  'coffin_chosen_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
                'value'        => $options['coffin_chosen_opt'],
                'label'        => $this->translate("coffin_chosen_opt"),
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
                'onChange' => "if(this.value == '4') { $('.coffin_chosen_text_show', $(this).parents('table')).show();} else { $('.coffin_chosen_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_coffin_chosen_text = $options['coffin_chosen_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'coffin_chosen_text', array(
                'value'        => $options['coffin_chosen_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'coffin_chosen_text_show',
                'style' => $display_coffin_chosen_text
            ));
            $subform->addElement('note',  "blank_3", array(
                'value' => "<br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                ),
            ));
        }
            
        
        if($options['formular_type'] == 'pdf' ){
            $was_informed = PatientFinalPhase::getWasInformed();
            $was_informed[13] = 'Sonstiges: '.$options['how_was_informed_text'];
            $subform->addElement('multiCheckbox', 'how_was_informed_opt', array(
                'label'      => $this->translate("how_was_informed_opt"),
                'multiOptions' => $was_informed,
                'required'   => false,
                'value'    => isset($options['how_was_informed_opt']) && ! is_array($options['how_was_informed_opt']) ? array_map('trim', explode(",", $options['how_was_informed_opt'])) : $options['how_was_informed_opt'],
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
            
            $was_informed = PatientFinalPhase::getWasInformed();
            $subform->addElement('multiCheckbox', 'how_was_informed_opt', array(
                'label'      => $this->translate("how_was_informed_opt"),
                'multiOptions' => $was_informed,
                'required'   => false,
                'value'    => isset($options['how_was_informed_opt']) && ! is_array($options['how_was_informed_opt']) ? array_map('trim', explode(",", $options['how_was_informed_opt'])) : $options['how_was_informed_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'multipleinputs')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "13" && this.checked) {$(".how_was_informed_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".how_was_informed_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_how_was_informed_opt = $subform->getElement('how_was_informed_opt')->getValue();
            $display_how_was_informed_text = in_array('13', $selected_value_how_was_informed_opt) ? '' : 'display:none';
            $subform->addElement('text',  'how_was_informed_text', array(
                'value'        => $options['how_was_informed_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'how_was_informed_text_show',
                'style' => $display_how_was_informed_text
            ));
            $subform->addElement('note',  "blank_3", array(
                'value' => "<br/><br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                ),
            ));
        }

                
        $subform->addElement('note',  "After_dying", array(
            'value' => $this->translate("After_dying"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=> 4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        

        if($options['formular_type'] == 'pdf' ){
            $care_child = PatientFinalPhase::getCarechild();
            $care_child[5] = 'Sonstiges: '.$options['care_child_text'];
            $subform->addElement('multiCheckbox', 'care_child_opt', array(
                'label'      => $this->translate("care_child_opt"),
                'multiOptions' => $care_child,
                'required'   => false,
                'value'    => isset($options['care_child_opt']) && ! is_array($options['care_child_opt']) ? array_map('trim', explode(",", $options['care_child_opt'])) : $options['care_child_opt'],
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
            $care_child = PatientFinalPhase::getCarechild();
            $subform->addElement('multiCheckbox', 'care_child_opt', array(
                'label'      => $this->translate("care_child_opt"),
                'multiOptions' => $care_child,
                'required'   => false,
                'value'    => isset($options['care_child_opt']) && ! is_array($options['care_child_opt']) ? array_map('trim', explode(",", $options['care_child_opt'])) : $options['care_child_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "5" && this.checked) {$(".care_child_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".care_child_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            
            $selected_value_care_child_opt = $subform->getElement('care_child_opt')->getValue();
            $display_care_child_text = in_array('5', $selected_value_care_child_opt) ? '' : 'display:none';
            $subform->addElement('text',  'care_child_text', array(
                'value'        => $options['care_child_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
                ),
                'class'=>'care_child_text_show',
                'style' => $display_care_child_text
            ));
            $subform->addElement('note',  "blank_33", array(
                'value' => "<br/><br/>",
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'th')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
                ),
            ));
        }
 
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'laying_out_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges: '.$options['laying_out_text'] ),
                'value'        => $options['laying_out_opt'],
                'label'        => $this->translate("laying_out_opt"),
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
            $subform->addElement('radio',  'laying_out_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
                'value'        => $options['laying_out_opt'],
                'label'        => $this->translate("laying_out_opt"),
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
                'onChange' => "if(this.value == '4') { $('.laying_out_text_show', $(this).parents('table')).show();} else { $('.laying_out_text_show', $(this).parents('table')).hide();} ",
                
            ));
            
            $display_laying_out_text = $options['laying_out_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'laying_out_text', array(
                'value'        => $options['laying_out_text'],
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
                'class'=>'laying_out_text_show',
                'style' => $display_laying_out_text
            ));
        }
            

        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'memento_desired_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges: '.$options['memento_desired_text'] ),
                'value'        => $options['memento_desired_opt'],
                'label'        => $this->translate("memento_desired_opt"),
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
            $subform->addElement('radio',  'memento_desired_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
                'value'        => $options['memento_desired_opt'],
                'label'        => $this->translate("memento_desired_opt"),
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
                'onChange' => "if(this.value == '4') { $('.memento_desired_text_show', $(this).parents('table')).show();} else { $('.memento_desired_text_show', $(this).parents('table')).hide();} ",
            ));
            
            $display_memento_desired_text = $options['memento_desired_opt'] == 4 ? '' : array('display:none');
            $subform->addElement('text', 'memento_desired_text', array(
                'value'        => $options['memento_desired_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"vertical-align: bottom; position:relative; left:-210px; bottom: -4px")),
                    array('Label', array('tag' => 'td')),
                    // array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'class'=>'memento_desired_text_show',
                'style' => $display_memento_desired_text
            ));
        }

    
                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_final_phase($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_final_phase");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientFinalPhase_legend'));
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

	    $subform->addElement('note',  "preparation", array(
	        'value' => $this->translate("preparation"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'death_discussed_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
	        'value'        => $options['death_discussed_opt'],
	        'label'        => $this->translate("death_discussed_opt"),
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
	        'onChange' => "if(this.value == '4') { $('.death_discussed_text_show', $(this).parents('table')).show();} else { $('.death_discussed_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_death_discussed_text = $options['death_discussed_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'death_discussed_text', array(
	        'value'        => $options['death_discussed_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'class'        => 'w150',
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'death_discussed_text_show',
	        'style' => $display_death_discussed_text,
	    ));
	    
	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'undertaker_informed_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
	        'value'        => $options['undertaker_informed_opt'],
	        'label'        => $this->translate("undertaker_informed_opt"),
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
	        'onChange' => "if(this.value == '4') { $('.undertaker_informed_text_show', $(this).parents('table')).show();} else { $('.undertaker_informed_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_undertaker_informed_text = $options['undertaker_informed_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'undertaker_informed_text', array(
	        'value'        => $options['undertaker_informed_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	           // array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'undertaker_informed_text_show',
	        'style' => $display_undertaker_informed_text
	    ));
	    
	    $subform->addElement('note',  "blank_2", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'coffin_chosen_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
	        'value'        => $options['coffin_chosen_opt'],
	        'label'        => $this->translate("coffin_chosen_opt"),
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
	        'onChange' => "if(this.value == '4') { $('.coffin_chosen_text_show', $(this).parents('table')).show();} else { $('.coffin_chosen_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_coffin_chosen_text = $options['coffin_chosen_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'coffin_chosen_text', array(
	        'value'        => $options['coffin_chosen_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'coffin_chosen_text_show', 
	        'style' => $display_coffin_chosen_text
	    ));
	    
	    $subform->addElement('note',  "blank_3", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $was_informed = PatientFinalPhase::getWasInformed();
	    $subform->addElement('multiCheckbox', 'how_was_informed_opt', array(
	        'label'      => $this->translate("how_was_informed_opt"),
	        'multiOptions' => $was_informed,
	        'required'   => false,
	        'value'    => isset($options['how_was_informed_opt']) && ! is_array($options['how_was_informed_opt']) ? array_map('trim', explode(",", $options['how_was_informed_opt'])) : $options['how_was_informed_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','class'=>'final_other_width_label multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "13" && this.checked) {$(".how_was_informed_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".how_was_informed_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_how_was_informed_opt = $subform->getElement('how_was_informed_opt')->getValue();
	    $display_how_was_informed_text = in_array('13', $selected_value_how_was_informed_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'how_was_informed_text', array(
	        'value'        => $options['how_was_informed_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'how_was_informed_text_show',
	        'style' => $display_how_was_informed_text
	    ));
	    
	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('note',  "blank_7", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('note',  "After_dying", array(
	        'value' => $this->translate("After_dying"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>2, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));

	    $care_child = PatientFinalPhase::getCarechild();
	    $subform->addElement('multiCheckbox', 'care_child_opt', array(
	        'label'      => $this->translate("care_child_opt"),
	        'multiOptions' => $care_child,
	        'required'   => false,
	        'value'    => isset($options['care_child_opt']) && ! is_array($options['care_child_opt']) ? array_map('trim', explode(",", $options['care_child_opt'])) : $options['care_child_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'final_other_width_label multipleinputs')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "5" && this.checked) {$(".care_child_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".care_child_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    
	    $selected_value_care_child_opt = $subform->getElement('care_child_opt')->getValue();
	    $display_care_child_text = in_array('5', $selected_value_care_child_opt) ? '' : 'display:none';
	    $subform->addElement('text',  'care_child_text', array(
	        'value'        => $options['care_child_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'))
	        ),
	        'class'=>'care_child_text_show',
	        'style' => $display_care_child_text
	    ));
	    
	    $subform->addElement('note',  "blank_6", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'laying_out_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
	        'value'        => $options['laying_out_opt'],
	        'label'        => $this->translate("laying_out_opt"),
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
	        'onChange' => "if(this.value == '4') { $('.laying_out_text_show', $(this).parents('table')).show();} else { $('.laying_out_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_laying_out_text = $options['laying_out_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'laying_out_text', array(
	        'value'        => $options['laying_out_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'laying_out_text_show',
	        'style' => $display_laying_out_text
	    ));
	    
	    $subform->addElement('note',  "blank_5", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'memento_desired_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja', '4' => 'Sonstiges'),
	        'value'        => $options['memento_desired_opt'],
	        'label'        => $this->translate("memento_desired_opt"),
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
	        'onChange' => "if(this.value == '4') { $('.memento_desired_text_show', $(this).parents('table')).show();} else { $('.memento_desired_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_memento_desired_text = $options['memento_desired_opt'] == 4 ? '' : array('display:none');
	    $subform->addElement('text', 'memento_desired_text', array(
	        'value'        => $options['memento_desired_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', "style"=>"border-bottom:none;vertical-align: bottom; position:relative; left:-220px; bottom: -4px")),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'memento_desired_text_show',
	        'style' => $display_memento_desired_text
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_final_phase($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if ($data['death_discussed_opt'] != 4){
	        $data['death_discussed_text'] = "";
	    }
	    if ($data['undertaker_informed_opt'] != 4){
	        $data['undertaker_informed_text'] = "";
	    }
	    if ($data['coffin_chosen_opt'] != 4){
	        $data['coffin_chosen_text'] = "";
	    }
	    if (!in_array('13',$data['how_was_informed_opt'])){
	        $data['how_was_informed_text'] = "";
	    }
	    $data['how_was_informed_opt'] = isset($data['how_was_informed_opt']) ?  implode(",", $data['how_was_informed_opt']) : null;
	    
	    if (!in_array('5',$data['care_child_opt'])){
	        $data['care_child_text'] = "";
	    }
	    $data['care_child_opt'] = isset($data['care_child_opt']) ?  implode(",", $data['care_child_opt']) : null;
	    if ($data['laying_out_opt'] != 4){
	        $data['laying_out_text'] = "";
	    }
	    if ($data['memento_desired_opt'] != 4){
	        $data['memento_desired_text'] = "";
	    }
	    
	    $r = PatientFinalPhaseTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>