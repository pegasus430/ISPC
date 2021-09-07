<?php
//ISPC-2793 Lore 18.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientCommunicationEmployment extends Pms_Form
{
    protected $_model = 'PatientCommunicationEmployment';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('verbal_utterances_opt'), "cols" => array("verbal_utterances")),
            array( "label" => $this->translate('speech_understanding_opt'), "cols" => array("speech_understanding")),
            array( "label" => $this->translate('communication_opt'), "cols" => array("communication")),
            array( "label" => $this->translate('tools_opt'), "cols" => array("tools")),
            array( "label" => $this->translate('restlessness_opt'), "cols" => array("restlessness")),
            array( "label" => $this->translate('preferences_interests'), "cols" => array("preferences_interests")),
            array( "label" => $this->translate('habits'), "cols" => array("habits")),
            array( "label" => $this->translate('dislikes'), "cols" => array("dislikes")),
            array( "label" => $this->translate('sole_occupations'), "cols" => array("sole_occupations")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientCommunicationEmployment';
    
	
    public function create_form_block_patient_communicationemployment($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_communication_employment");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_comm_employ');
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
        
 
        $subform->addElement('note',  "communication", array(
            'value' => $this->translate("communication"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'verbal_utterances_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['verbal_utterances_opt'],
                'label'        => $this->translate("verbal_utterances_opt"),
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

            $subform->addElement('text', 'verbal_utterances_text', array(
                'value'        => $options['verbal_utterances_text'],
                'label'        => '',
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
        }else {
            $subform->addElement('radio',  'verbal_utterances_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['verbal_utterances_opt'],
                'label'        => $this->translate("verbal_utterances_opt"),
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
                'onChange' => "if(this.value == '3') { $('.verbal_utterances_text_show', $(this).parents('table')).show();} else { $('.verbal_utterances_text_show', $(this).parents('table')).hide();} ",
                
            ));
            $display_verbal_utterances_text = $options['verbal_utterances_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'verbal_utterances_text', array(
                'value'        => $options['verbal_utterances_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'verbal_utterances_text_show', 'style' => $display_verbal_utterances_text)),
                ),
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('radio',  'speech_understanding_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['speech_understanding_opt'],
                'label'        => $this->translate("speech_understanding_opt"),
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
            $subform->addElement('text', 'speech_understanding_text', array(
                'value'        => $options['speech_understanding_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));
        } else {
            $subform->addElement('radio',  'speech_understanding_opt', array(
                'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
                'value'        => $options['speech_understanding_opt'],
                'label'        => $this->translate("speech_understanding_opt"),
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
                'onChange' => "if(this.value == '3') { $('.speech_understanding_text_show', $(this).parents('table')).show();} else { $('.speech_understanding_text_show', $(this).parents('table')).hide();} ",
                
            ));
            $display_speech_understanding_text = $options['speech_understanding_opt'] == 3 ? '' : array('display:none');
            $subform->addElement('text', 'speech_understanding_text', array(
                'value'        => $options['speech_understanding_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'speech_understanding_text_show', 'style' => $display_speech_understanding_text)),
                ),
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            $communication = PatientCommunicationEmployment::getCommunication();
            $communication[5] = "Sonstiges: ".$options['communication_text'];
            $subform->addElement('multiCheckbox', 'communication_opt', array(
                'label'      => $this->translate("communication_opt"),
                'multiOptions' => $communication,
                'required'   => false,
                'value'    => isset($options['communication_opt']) && ! is_array($options['communication_opt']) ? array_map('trim', explode(",", $options['communication_opt'])) : $options['communication_opt'],
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
            $communication = PatientCommunicationEmployment::getCommunication();
            $subform->addElement('multiCheckbox', 'communication_opt', array(
                'label'      => $this->translate("communication_opt"),
                'multiOptions' => $communication,
                'required'   => false,
                'value'    => isset($options['communication_opt']) && ! is_array($options['communication_opt']) ? array_map('trim', explode(",", $options['communication_opt'])) : $options['communication_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "5" && this.checked) {$(".communication_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".communication_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            $selected_value_communication_opt = $subform->getElement('communication_opt')->getValue();
            $display_communication_text = in_array('5', $selected_value_communication_opt) ? '' : 'display:none';
            $subform->addElement('text', 'communication_text', array(
                'value'        => $options['communication_text'],
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
                'class'=>'communication_text_show',
                'style' => $display_communication_text
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
            $tools = PatientCommunicationEmployment::getTools();
            $tools[5] = "Sonstiges: ".$options['tools_text'];
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
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));

        } else {
            $tools = PatientCommunicationEmployment::getTools();
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
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
                    //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => 'if (this.value == "5" && this.checked) {$(".tools_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".tools_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
            ));
            $selected_value_tools_opt = $subform->getElement('tools_opt')->getValue();
            $display_tools_text = in_array('5', $selected_value_tools_opt) ? '' : 'display:none';
            $subform->addElement('text', 'tools_text', array(
                'value'        => $options['tools_text'],
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
                'class'=>'tools_text_show',
                'style' => $display_tools_text
            ));
        }
        
        $subform->addElement('radio',  'restlessness_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['restlessness_opt'],
            'label'        => $this->translate("restlessness_opt"),
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
            'onChange' => "if(this.value == '3') { $('.restlessness_text_show', $(this).parents('table')).show();} else { $('.restlessness_text_show', $(this).parents('table')).hide();} ",
            
        ));
        $display_restlessness_text = $options['restlessness_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'restlessness_text', array(
            'value'        => $options['restlessness_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'restlessness_text_show', 'style' => $display_restlessness_text)),
            ),
        ));
        
        $subform->addElement('note',  "employment", array(
            'value' => $this->translate("employment"),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
            ),
        ));
        
        
        $subform->addElement('text', 'preferences_interests', array(
            'value'        => $options['preferences_interests'],
            'label'        => $this->translate("preferences_interests"),
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
        
        $subform->addElement('text', 'habits', array(
            'value'        => $options['habits'],
            'label'        => $this->translate("habits"),
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
        
        $subform->addElement('text', 'dislikes', array(
            'value'        => $options['dislikes'],
            'label'        => $this->translate("dislikes"),
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
        
        $subform->addElement('text', 'sole_occupations', array(
            'value'        => $options['sole_occupations'],
            'label'        => $this->translate("sole_occupations"),
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
        
                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_communication_employment($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_communication_employment");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientCommunicationEmployment_legend'));
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

	    $subform->addElement('note',  "communication", array(
	        'value' => $this->translate("communication"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));

	    $subform->addElement('radio',  'verbal_utterances_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['verbal_utterances_opt'],
	        'label'        => $this->translate("verbal_utterances_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.verbal_utterances_text_show', $(this).parents('table')).show();} else { $('.verbal_utterances_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    $display_verbal_utterances_text = $options['verbal_utterances_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'verbal_utterances_text', array(
	        'value'        => $options['verbal_utterances_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'verbal_utterances_text_show', 'style' => $display_verbal_utterances_text)),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'speech_understanding_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['speech_understanding_opt'],
	        'label'        => $this->translate("speech_understanding_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.speech_understanding_text_show', $(this).parents('table')).show();} else { $('.speech_understanding_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    $display_speech_understanding_text = $options['speech_understanding_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'speech_understanding_text', array(
	        'value'        => $options['speech_understanding_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'speech_understanding_text_show', 'style' => $display_speech_understanding_text)),
	        ),
	    ));

	    $communication = PatientCommunicationEmployment::getCommunication();
	    $subform->addElement('multiCheckbox', 'communication_opt', array(
	        'label'      => $this->translate("communication_opt"),
	        'multiOptions' => $communication,
	        'required'   => false,
	        'value'    => isset($options['communication_opt']) && ! is_array($options['communication_opt']) ? array_map('trim', explode(",", $options['communication_opt'])) : $options['communication_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "5" && this.checked) {$(".communication_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".communication_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    $selected_value_communication_opt = $subform->getElement('communication_opt')->getValue();
	    $display_communication_text = in_array('5', $selected_value_communication_opt) ? '' : 'display:none';
	    $subform->addElement('text', 'communication_text', array(
	        'value'        => $options['communication_text'],
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
	        'class'=>'communication_text_show',
	        'style' => $display_communication_text
	    ));

	    $subform->addElement('note',  "blank_1", array(
	        'value' => "<br/>",
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
	        ),
	    ));
	    
	    $tools = PatientCommunicationEmployment::getTools();
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
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td','tagClass'=>'print_column_first w150')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'onChange' => 'if (this.value == "5" && this.checked) {$(".tools_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".tools_text_show", $(this).parents(\'table\')).hide().val(\'\');}',
	    ));
	    $selected_value_tools_opt = $subform->getElement('tools_opt')->getValue();
	    $display_tools_text = in_array('5', $selected_value_tools_opt) ? '' : 'display:none';
	    $subform->addElement('text', 'tools_text', array(
	        'value'        => $options['tools_text'],
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
	        'class'=>'tools_text_show',
	        'style' => $display_tools_text
	    ));
	    
	    $subform->addElement('radio',  'restlessness_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['restlessness_opt'],
	        'label'        => $this->translate("restlessness_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.restlessness_text_show', $(this).parents('table')).show();} else { $('.restlessness_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    $display_restlessness_text = $options['restlessness_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'restlessness_text', array(
	        'value'        => $options['restlessness_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'restlessness_text_show', 'style' => $display_restlessness_text)),
	        ),
	    ));
	    
	    $subform->addElement('note',  "employment", array(
	        'value' => $this->translate("employment"),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'th','colspan'=>4, 'style'=>"font-weight:bold; padding: 5px;")),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>"background-color:#e8e3e3; font-size:15px;")),
	        ),
	    ));
	    

	    $subform->addElement('text', 'preferences_interests', array(
	        'value'        => $options['preferences_interests'],
	        'label'        => $this->translate("preferences_interests"),
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
	    
	    $subform->addElement('text', 'habits', array(
	        'value'        => $options['habits'],
	        'label'        => $this->translate("habits"),
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
	    
	    $subform->addElement('text', 'dislikes', array(
	        'value'        => $options['dislikes'],
	        'label'        => $this->translate("dislikes"),
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
	    
	    $subform->addElement('text', 'sole_occupations', array(
	        'value'        => $options['sole_occupations'],
	        'label'        => $this->translate("sole_occupations"),
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
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_communication_employment($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if ($data['verbal_utterances_opt'] != 3){
	        $data['verbal_utterances_text'] = "";
	    }
	    if ($data['speech_understanding_opt'] != 3){
	        $data['speech_understanding_text'] = "";
	    }
	    
	    if (!in_array('5',$data['communication_opt'])){
	        $data['communication_text'] = "";
	    }
	    $data['communication_opt'] = isset($data['communication_opt']) ?  implode(",", $data['communication_opt']) : null;
	    
	    if (!in_array('5',$data['tools_opt'])){
	        $data['tools_text'] = "";
	    }
	    $data['tools_opt'] = isset($data['tools_opt']) ?  implode(",", $data['tools_opt']) : null;
	    
	    if ($data['restlessness_opt'] != 3){
	        $data['restlessness_text'] = "";
	    }

	    $r = PatientCommunicationEmploymentTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>