<?php
//ISPC-2776 Lore 15.12.2020

require_once("Pms/Form.php");

class Application_Form_PatientChildrenDiseases extends Pms_Form
{
    protected $_model = 'PatientChildrenDiseases';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('rotavirus'), "cols" => array("rotavirus")),
            array( "label" => $this->translate('varicella'), "cols" => array("varicella")),
            array( "label" => $this->translate('measles'), "cols" => array("measles")),
            array( "label" => $this->translate('mumps'), "cols" => array("mumps")),
            array( "label" => $this->translate('rubella'), "cols" => array("rubella")),
            array( "label" => $this->translate('pertussis'), "cols" => array("pertussis")),
            array( "label" => $this->translate('diphtheria'), "cols" => array("diphtheria")),
            array( "label" => $this->translate('tetanus'), "cols" => array("tetanus")),
            array( "label" => $this->translate('hib'), "cols" => array("hib")),
            array( "label" => $this->translate('polio'), "cols" => array("polio")),
            array( "label" => $this->translate('pneumococci'), "cols" => array("pneumococci")),
            array( "label" => $this->translate('meningococci'), "cols" => array("meningococci")),
            array( "label" => $this->translate('hepatit_a'), "cols" => array("hepatit_a")),
            array( "label" => $this->translate('hepatit_b'), "cols" => array("hepatit_b")),
            array( "label" => $this->translate('tuberculosis'), "cols" => array("tuberculosis")),
            array( "label" => $this->translate('hpv'), "cols" => array("hpv")),
            array( "label" => $this->translate('Free text'), "cols" => array("other")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientChildrenDiseases';
    
	
    public function create_form_block_patient_childrendiseases($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_children_diseases");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_childrendiseases');
        $subform->setAttrib("class", "label_same_size {$__fnName}");
        
        
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
               
        $rotavirus_arr = PatientChildrenDiseases::getRotavirus();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'rotavirus_opt', array(
                'label'      => "rotavirus",
                'multiOptions' => $rotavirus_arr,
                'value'    => isset($options['rotavirus_opt']) && ! is_array($options['rotavirus_opt']) ? array_map('trim', explode(",", $options['rotavirus_opt'])) : $options['rotavirus_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        }else {
            $subform->addElement('multiCheckbox', 'rotavirus_opt', array(
                'label'      => "rotavirus",
                'multiOptions' => $rotavirus_arr,
                'value'    => isset($options['rotavirus_opt']) && ! is_array($options['rotavirus_opt']) ? array_map('trim', explode(",", $options['rotavirus_opt'])) : $options['rotavirus_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "13" && this.checked) {$(".rotavirus_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".rotavirus_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $rotavirus_opt_arr = explode(",", $options['rotavirus_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['rotavirus_opt'])){
                $subform->addElement('text',  'rotavirus_last_vaccination', array(
                    'value'        => $options['rotavirus_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            
            $display_rotavirus_last_vaccination = in_array('11', $rotavirus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rotavirus_last_vaccination', array(
                'value'        => empty($options['rotavirus_last_vaccination']) || $options['rotavirus_last_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_last_vaccination_show ", 'style' => $display_rotavirus_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('12', $options['rotavirus_opt'])){
                $subform->addElement('text',  'rotavirus_next_vaccination', array(
                    'value'        => $options['rotavirus_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_rotavirus_next_vaccination = in_array('12', $rotavirus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rotavirus_next_vaccination', array(
                'value'        => empty($options['rotavirus_next_vaccination']) || $options['rotavirus_next_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_next_vaccination_show ", 'style' => $display_rotavirus_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('13', $options['rotavirus_opt'])){
                $subform->addElement('text',  'rotavirus_text', array(
                    'value'        => $options['rotavirus_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_rotavirus_text = in_array('13', $rotavirus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rotavirus_text', array(
                'value'        => $options['rotavirus_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_text_show ", 'style' => $display_rotavirus_text ))
                ),
            ));
        }

        
        
        $varicella_arr = PatientChildrenDiseases::getVaricella();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'varicella_opt', array(
                'label'      => "varicella",
                'multiOptions' => $varicella_arr,
                'value'    => isset($options['varicella_opt']) && ! is_array($options['varicella_opt']) ? array_map('trim', explode(",", $options['varicella_opt'])) : $options['varicella_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'varicella_opt', array(
                'label'      => "varicella",
                'multiOptions' => $varicella_arr,
                'value'    => isset($options['varicella_opt']) && ! is_array($options['varicella_opt']) ? array_map('trim', explode(",", $options['varicella_opt'])) : $options['varicella_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "11" && this.checked) {$(".varicella_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".varicella_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".varicella_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".varicella_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".varicella_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".varicella_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $varicella_opt_arr = explode(",", $options['varicella_opt']);
        
        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['varicella_opt'])){
                $subform->addElement('text',  'varicella_last_vaccination', array(
                    'value'        => $options['varicella_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_varicella_last_vaccination = in_array('9', $varicella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'varicella_last_vaccination', array(
                'value'        => empty($options['varicella_last_vaccination']) || $options['varicella_last_vaccination'] == "0000-00-00 00:00:00" || $options['varicella_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['varicella_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_last_vaccination_show ", 'style' => $display_varicella_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['varicella_opt'])){
                $subform->addElement('text',  'varicella_next_vaccination', array(
                    'value'        => $options['varicella_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_varicella_next_vaccination = in_array('10', $varicella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'varicella_next_vaccination', array(
                'value'        => empty($options['varicella_next_vaccination']) || $options['varicella_next_vaccination'] == "0000-00-00 00:00:00" || $options['varicella_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['varicella_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_next_vaccination_show ", 'style' => $display_varicella_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['varicella_opt'])){
                $subform->addElement('text',  'varicella_text', array(
                    'value'        => $options['varicella_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_varicella_text = in_array('11', $varicella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'varicella_text', array(
                'value'        => $options['varicella_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_text_show ", 'style' => $display_varicella_text ))
                ),
            ));
        }

        
        
        $measles_arr = PatientChildrenDiseases::getMeasles();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'measles_opt', array(
                'label'      => "measles",
                'multiOptions' => $measles_arr,
                'value'    => isset($options['measles_opt']) && ! is_array($options['measles_opt']) ? array_map('trim', explode(",", $options['measles_opt'])) : $options['measles_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'measles_opt', array(
                'label'      => "measles",
                'multiOptions' => $measles_arr,
                'value'    => isset($options['measles_opt']) && ! is_array($options['measles_opt']) ? array_map('trim', explode(",", $options['measles_opt'])) : $options['measles_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "12" && this.checked) {$(".measles_text_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".measles_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".measles_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".measles_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".measles_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".measles_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }
            
                    
        $measles_opt_arr = explode(",", $options['measles_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['measles_opt'])){
                $subform->addElement('text',  'measles_last_vaccination', array(
                    'value'        => $options['measles_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_measles_last_vaccination = in_array('10', $measles_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'measles_last_vaccination', array(
                'value'        => empty($options['measles_last_vaccination']) || $options['measles_last_vaccination'] == "0000-00-00 00:00:00" || $options['measles_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['measles_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_last_vaccination_show ", 'style' => $display_measles_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['measles_opt'])){
                $subform->addElement('text',  'measles_next_vaccination', array(
                    'value'        => $options['measles_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_measles_next_vaccination = in_array('11', $measles_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'measles_next_vaccination', array(
                'value'        => empty($options['measles_next_vaccination']) || $options['measles_next_vaccination'] == "0000-00-00 00:00:00" || $options['measles_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['measles_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_next_vaccination_show ", 'style' => $display_measles_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['measles_opt'])){
                $subform->addElement('text',  'measles_text', array(
                    'value'        => $options['measles_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_measles_text = in_array('12', $measles_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'measles_text', array(
                'value'        => $options['measles_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_text_show ", 'style' => $display_measles_text ))
                ),
            ));
        }

        
        $mumps_arr = PatientChildrenDiseases::getMumps();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'mumps_opt', array(
                'label'      => "mumps",
                'multiOptions' => $mumps_arr,
                'value'    => isset($options['mumps_opt']) && ! is_array($options['mumps_opt']) ? array_map('trim', explode(",", $options['mumps_opt'])) : $options['mumps_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'mumps_opt', array(
                'label'      => "mumps",
                'multiOptions' => $mumps_arr,
                'value'    => isset($options['mumps_opt']) && ! is_array($options['mumps_opt']) ? array_map('trim', explode(",", $options['mumps_opt'])) : $options['mumps_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "10" && this.checked) {$(".mumps_text_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".mumps_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "8" && this.checked) {$(".mumps_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "8") {$(".mumps_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".mumps_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".mumps_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }
            
        $mumps_opt_arr = explode(",", $options['mumps_opt']);
        
        if($options['formular_type'] == 'pdf' ){
            if( in_array('8', $options['mumps_opt'])){
                $subform->addElement('text',  'mumps_last_vaccination', array(
                    'value'        => $options['mumps_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_mumps_last_vaccination = in_array('8', $mumps_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'mumps_last_vaccination', array(
                'value'        => empty($options['mumps_last_vaccination']) || $options['mumps_last_vaccination'] == "0000-00-00 00:00:00" || $options['mumps_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['mumps_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_last_vaccination_show ", 'style' => $display_mumps_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['mumps_opt'])){
                $subform->addElement('text',  'mumps_next_vaccination', array(
                    'value'        => $options['mumps_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_mumps_next_vaccination = in_array('9', $mumps_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'mumps_next_vaccination', array(
                'value'        => empty($options['mumps_next_vaccination']) || $options['mumps_next_vaccination'] == "0000-00-00 00:00:00" || $options['mumps_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['mumps_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_next_vaccination_show ", 'style' => $display_mumps_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        
        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['mumps_opt'])){
                $subform->addElement('text',  'mumps_text', array(
                    'value'        => $options['mumps_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_mumps_text = in_array('10', $mumps_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'mumps_text', array(
                'value'        => $options['mumps_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_text_show ", 'style' => $display_mumps_text ))
                ),
            ));
        }

        
        
        $rubella_arr = PatientChildrenDiseases::getRubella();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'rubella_opt', array(
                'label'      => "rubella",
                'multiOptions' => $rubella_arr,
                'value'    => isset($options['rubella_opt']) && ! is_array($options['rubella_opt']) ? array_map('trim', explode(",", $options['rubella_opt'])) : $options['rubella_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'rubella_opt', array(
                'label'      => "rubella",
                'multiOptions' => $rubella_arr,
                'value'    => isset($options['rubella_opt']) && ! is_array($options['rubella_opt']) ? array_map('trim', explode(",", $options['rubella_opt'])) : $options['rubella_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "11" && this.checked) {$(".rubella_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".rubella_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".rubella_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".rubella_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".rubella_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".rubella_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $rubella_opt_arr = explode(",", $options['rubella_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['rubella_opt'])){
                $subform->addElement('text',  'rubella_last_vaccination', array(
                    'value'        => $options['rubella_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_rubella_last_vaccination = in_array('9', $rubella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rubella_last_vaccination', array(
                'value'        => empty($options['rubella_last_vaccination']) || $options['rubella_last_vaccination'] == "0000-00-00 00:00:00" || $options['rubella_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rubella_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_last_vaccination_show ", 'style' => $display_rubella_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['rubella_opt'])){
                $subform->addElement('text',  'rubella_next_vaccination', array(
                    'value'        => $options['rubella_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_rubella_next_vaccination = in_array('10', $rubella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rubella_next_vaccination', array(
                'value'        => empty($options['rubella_next_vaccination']) || $options['rubella_next_vaccination'] == "0000-00-00 00:00:00" || $options['rubella_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rubella_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_next_vaccination_show ", 'style' => $display_rubella_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['rubella_opt'])){
                $subform->addElement('text',  'rubella_text', array(
                    'value'        => $options['rubella_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_rubella_text = in_array('11', $rubella_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'rubella_text', array(
                'value'        => $options['rubella_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_text_show ", 'style' => $display_rubella_text ))
                ),
            ));
        }

        
        $pertussis_arr = PatientChildrenDiseases::getPertussis();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'pertussis_opt', array(
                'label'      => "pertussis",
                'multiOptions' => $pertussis_arr,
                'value'    => isset($options['pertussis_opt']) && ! is_array($options['pertussis_opt']) ? array_map('trim', explode(",", $options['pertussis_opt'])) : $options['pertussis_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'pertussis_opt', array(
                'label'      => "pertussis",
                'multiOptions' => $pertussis_arr,
                'value'    => isset($options['pertussis_opt']) && ! is_array($options['pertussis_opt']) ? array_map('trim', explode(",", $options['pertussis_opt'])) : $options['pertussis_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "16" && this.checked) {$(".pertussis_text_show", $(this).parents(\'table\')).show();} else if(this.value == "16") {$(".pertussis_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".pertussis_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".pertussis_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "15" && this.checked) {$(".pertussis_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".pertussis_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $pertussis_opt_arr = explode(",", $options['pertussis_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('14', $options['pertussis_opt'])){
                $subform->addElement('text',  'pertussis_last_vaccination', array(
                    'value'        => $options['pertussis_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pertussis_last_vaccination = in_array('14', $pertussis_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pertussis_last_vaccination', array(
                'value'        => empty($options['pertussis_last_vaccination']) || $options['pertussis_last_vaccination'] == "0000-00-00 00:00:00" || $options['pertussis_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pertussis_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_last_vaccination_show ", 'style' => $display_pertussis_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('15', $options['pertussis_opt'])){
                $subform->addElement('text',  'pertussis_next_vaccination', array(
                    'value'        => $options['pertussis_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pertussis_next_vaccination = in_array('15', $pertussis_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pertussis_next_vaccination', array(
                'value'        => empty($options['pertussis_next_vaccination']) || $options['pertussis_next_vaccination'] == "0000-00-00 00:00:00" || $options['pertussis_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pertussis_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_next_vaccination_show ", 'style' => $display_pertussis_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('16', $options['pertussis_opt'])){
                $subform->addElement('text',  'pertussis_text', array(
                    'value'        => $options['pertussis_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pertussis_text = in_array('16', $pertussis_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pertussis_text', array(
                'value'        => $options['pertussis_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_text_show ", 'style' => $display_pertussis_text ))
                ),
            ));
        }

        
        
        $diphtheria_arr = PatientChildrenDiseases::getDiphtheria();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'diphtheria_opt', array(
                'label'      => "diphtheria",
                'multiOptions' => $diphtheria_arr,
                'value'    => isset($options['diphtheria_opt']) && ! is_array($options['diphtheria_opt']) ? array_map('trim', explode(",", $options['diphtheria_opt'])) : $options['diphtheria_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'diphtheria_opt', array(
                'label'      => "diphtheria",
                'multiOptions' => $diphtheria_arr,
                'value'    => isset($options['diphtheria_opt']) && ! is_array($options['diphtheria_opt']) ? array_map('trim', explode(",", $options['diphtheria_opt'])) : $options['diphtheria_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "15" && this.checked) {$(".diphtheria_text_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".diphtheria_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "13" && this.checked) {$(".diphtheria_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".diphtheria_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".diphtheria_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".diphtheria_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }
            

        
        $diphtheria_opt_arr = explode(",", $options['diphtheria_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('13', $options['diphtheria_opt'])){
                $subform->addElement('text',  'diphtheria_last_vaccination', array(
                    'value'        => $options['diphtheria_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_diphtheria_last_vaccination = in_array('13', $diphtheria_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'diphtheria_last_vaccination', array(
                'value'        => empty($options['diphtheria_last_vaccination']) || $options['diphtheria_last_vaccination'] == "0000-00-00 00:00:00" || $options['diphtheria_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['diphtheria_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_last_vaccination_show ", 'style' => $display_diphtheria_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('14', $options['diphtheria_opt'])){
                $subform->addElement('text',  'diphtheria_next_vaccination', array(
                    'value'        => $options['diphtheria_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_diphtheria_next_vaccination = in_array('14', $diphtheria_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'diphtheria_next_vaccination', array(
                'value'        => empty($options['diphtheria_next_vaccination']) || $options['diphtheria_next_vaccination'] == "0000-00-00 00:00:00" || $options['diphtheria_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['diphtheria_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_next_vaccination_show ", 'style' => $display_diphtheria_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('15', $options['diphtheria_opt'])){
                $subform->addElement('text',  'diphtheria_text', array(
                    'value'        => $options['diphtheria_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_diphtheria_text = in_array('15', $diphtheria_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'diphtheria_text', array(
                'value'        => $options['diphtheria_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_text_show ", 'style' => $display_diphtheria_text ))
                ),
            ));
        }

        
        
        $tetanus_arr = PatientChildrenDiseases::getTetanus();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'tetanus_opt', array(
                'label'      => "tetanus",
                'multiOptions' => $tetanus_arr,
                'value'    => isset($options['tetanus_opt']) && ! is_array($options['tetanus_opt']) ? array_map('trim', explode(",", $options['tetanus_opt'])) : $options['tetanus_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'tetanus_opt', array(
                'label'      => "tetanus",
                'multiOptions' => $tetanus_arr,
                'value'    => isset($options['tetanus_opt']) && ! is_array($options['tetanus_opt']) ? array_map('trim', explode(",", $options['tetanus_opt'])) : $options['tetanus_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "16" && this.checked) {$(".tetanus_text_show", $(this).parents(\'table\')).show();} else if(this.value == "16") {$(".tetanus_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".tetanus_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".tetanus_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "15" && this.checked) {$(".tetanus_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".tetanus_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $tetanus_opt_arr = explode(",", $options['tetanus_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('14', $options['tetanus_opt'])){
                $subform->addElement('text',  'tetanus_last_vaccination', array(
                    'value'        => $options['tetanus_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_tetanus_last_vaccination = in_array('14', $tetanus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'tetanus_last_vaccination', array(
                'value'        => empty($options['tetanus_last_vaccination']) || $options['tetanus_last_vaccination'] == "0000-00-00 00:00:00" || $options['tetanus_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['tetanus_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_last_vaccination_show ", 'style' => $display_tetanus_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('15', $options['tetanus_opt'])){
                $subform->addElement('text',  'tetanus_next_vaccination', array(
                    'value'        => $options['tetanus_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_tetanus_next_vaccination = in_array('15', $tetanus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'tetanus_next_vaccination', array(
                'value'        => empty($options['tetanus_next_vaccination']) || $options['tetanus_next_vaccination'] == "0000-00-00 00:00:00" || $options['tetanus_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['tetanus_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_next_vaccination_show ", 'style' => $display_tetanus_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('16', $options['tetanus_opt'])){
                $subform->addElement('text',  'tetanus_text', array(
                    'value'        => $options['tetanus_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_tetanus_text = in_array('16', $tetanus_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'tetanus_text', array(
                'value'        => $options['tetanus_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_text_show ", 'style' => $display_tetanus_text ))
                ),
            ));
        }
        
        
        $hib_arr = PatientChildrenDiseases::getHib();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'hib_opt', array(
                'label'      => "hib",
                'multiOptions' => $hib_arr,
                'value'    => isset($options['hib_opt']) && ! is_array($options['hib_opt']) ? array_map('trim', explode(",", $options['hib_opt'])) : $options['hib_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'hib_opt', array(
                'label'      => "hib",
                'multiOptions' => $hib_arr,
                'value'    => isset($options['hib_opt']) && ! is_array($options['hib_opt']) ? array_map('trim', explode(",", $options['hib_opt'])) : $options['hib_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "13" && this.checked) {$(".hib_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".hib_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".hib_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hib_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".hib_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".hib_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }
            

        $hib_opt_arr = explode(",", $options['hib_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['hib_opt'])){
                $subform->addElement('text',  'hib_last_vaccination', array(
                    'value'        => $options['hib_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hib_last_vaccination = in_array('11', $hib_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hib_last_vaccination', array(
                'value'        => empty($options['hib_last_vaccination']) || $options['hib_last_vaccination'] == "0000-00-00 00:00:00" || $options['hib_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hib_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_last_vaccination_show ", 'style' => $display_hib_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('12', $options['hib_opt'])){
                $subform->addElement('text',  'hib_next_vaccination', array(
                    'value'        => $options['hib_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hhib_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hib_next_vaccination = in_array('12', $hib_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hib_next_vaccination', array(
                'value'        => empty($options['hib_next_vaccination']) || $options['hib_next_vaccination'] == "0000-00-00 00:00:00" || $options['hib_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hib_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_next_vaccination_show ", 'style' => $display_hib_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('13', $options['hib_opt'])){
                $subform->addElement('text',  'hib_text', array(
                    'value'        => $options['hib_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hib_text = in_array('13', $hib_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hib_text', array(
                'value'        => $options['hib_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_text_show ", 'style' => $display_hib_text ))
                ),
            ));
        }

        
        $polio_arr = PatientChildrenDiseases::getPolio();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'polio_opt', array(
                'label'      => "polio",
                'multiOptions' => $polio_arr,
                'value'    => isset($options['polio_opt']) && ! is_array($options['polio_opt']) ? array_map('trim', explode(",", $options['polio_opt'])) : $options['polio_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'polio_opt', array(
                'label'      => "polio",
                'multiOptions' => $polio_arr,
                'value'    => isset($options['polio_opt']) && ! is_array($options['polio_opt']) ? array_map('trim', explode(",", $options['polio_opt'])) : $options['polio_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "14" && this.checked) {$(".polio_text_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".polio_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".polio_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".polio_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "13" && this.checked) {$(".polio_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".polio_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $polio_opt_arr = explode(",", $options['polio_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('12', $options['polio_opt'])){
                $subform->addElement('text',  'polio_last_vaccination', array(
                    'value'        => $options['polio_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_polio_last_vaccination = in_array('12', $polio_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'polio_last_vaccination', array(
                'value'        => empty($options['polio_last_vaccination']) || $options['polio_last_vaccination'] == "0000-00-00 00:00:00" || $options['polio_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['polio_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_last_vaccination_show ", 'style' => $display_polio_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('13', $options['polio_opt'])){
                $subform->addElement('text',  'polio_next_vaccination', array(
                    'value'        => $options['polio_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_polio_next_vaccination = in_array('13', $polio_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'polio_next_vaccination', array(
                'value'        => empty($options['polio_next_vaccination']) || $options['polio_next_vaccination'] == "0000-00-00 00:00:00" || $options['polio_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['polio_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_next_vaccination_show ", 'style' => $display_polio_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('14', $options['polio_opt'])){
                $subform->addElement('text',  'polio_text', array(
                    'value'        => $options['polio_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_polio_text = in_array('14', $polio_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'polio_text', array(
                'value'        => $options['polio_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_text_show ", 'style' => $display_polio_text ))
                ),
            ));
        }

        
        
        $pneumococci_arr = PatientChildrenDiseases::getPneumococci();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'pneumococci_opt', array(
                'label'      => "pneumococci",
                'multiOptions' => $pneumococci_arr,
                'value'    => isset($options['pneumococci_opt']) && ! is_array($options['pneumococci_opt']) ? array_map('trim', explode(",", $options['pneumococci_opt'])) : $options['pneumococci_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'pneumococci_opt', array(
                'label'      => "pneumococci",
                'multiOptions' => $pneumococci_arr,
                'value'    => isset($options['pneumococci_opt']) && ! is_array($options['pneumococci_opt']) ? array_map('trim', explode(",", $options['pneumococci_opt'])) : $options['pneumococci_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "12" && this.checked) {$(".pneumococci_text_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".pneumococci_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".pneumococci_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".pneumococci_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".pneumococci_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".pneumococci_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $pneumococci_opt_arr = explode(",", $options['pneumococci_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['pneumococci_opt'])){
                $subform->addElement('text',  'pneumococci_last_vaccination', array(
                    'value'        => $options['pneumococci_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pneumococci_last_vaccination = in_array('10', $pneumococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pneumococci_last_vaccination', array(
                'value'        => empty($options['pneumococci_last_vaccination']) || $options['pneumococci_last_vaccination'] == "0000-00-00 00:00:00" || $options['pneumococci_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pneumococci_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_last_vaccination_show ", 'style' => $display_pneumococci_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['pneumococci_opt'])){
                $subform->addElement('text',  'pneumococci_next_vaccination', array(
                    'value'        => $options['pneumococci_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pneumococci_next_vaccination = in_array('11', $pneumococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pneumococci_next_vaccination', array(
                'value'        => empty($options['pneumococci_next_vaccination']) || $options['pneumococci_next_vaccination'] == "0000-00-00 00:00:00" || $options['pneumococci_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pneumococci_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_next_vaccination_show ", 'style' => $display_pneumococci_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('12', $options['pneumococci_opt'])){
                $subform->addElement('text',  'pneumococci_text', array(
                    'value'        => $options['pneumococci_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_pneumococci_text = in_array('12', $pneumococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'pneumococci_text', array(
                'value'        => $options['pneumococci_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_text_show ", 'style' => $display_pneumococci_text ))
                ),
            ));
        }

        
        
        $meningococci_arr = PatientChildrenDiseases::getMeningococci();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'meningococci_opt', array(
                'label'      => "meningococci",
                'multiOptions' => $meningococci_arr,
                'value'    => isset($options['meningococci_opt']) && ! is_array($options['meningococci_opt']) ? array_map('trim', explode(",", $options['meningococci_opt'])) : $options['meningococci_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'meningococci_opt', array(
                'label'      => "meningococci",
                'multiOptions' => $meningococci_arr,
                'value'    => isset($options['meningococci_opt']) && ! is_array($options['meningococci_opt']) ? array_map('trim', explode(",", $options['meningococci_opt'])) : $options['meningococci_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "9" && this.checked) {$(".meningococci_text_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".meningococci_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "7" && this.checked) {$(".meningococci_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "7") {$(".meningococci_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "8" && this.checked) {$(".meningococci_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "8") {$(".meningococci_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $meningococci_opt_arr = explode(",", $options['meningococci_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('7', $options['meningococci_opt'])){
                $subform->addElement('text',  'meningococci_last_vaccination', array(
                    'value'        => $options['meningococci_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_meningococci_last_vaccination = in_array('7', $meningococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'meningococci_last_vaccination', array(
                'value'        => empty($options['meningococci_last_vaccination']) || $options['meningococci_last_vaccination'] == "0000-00-00 00:00:00" || $options['meningococci_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['meningococci_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_last_vaccination_show ", 'style' => $display_meningococci_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('8', $options['meningococci_opt'])){
                $subform->addElement('text',  'meningococci_next_vaccination', array(
                    'value'        => $options['meningococci_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_meningococci_next_vaccination = in_array('8', $meningococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'meningococci_next_vaccination', array(
                'value'        => empty($options['meningococci_next_vaccination']) || $options['meningococci_next_vaccination'] == "0000-00-00 00:00:00" || $options['meningococci_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['meningococci_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_next_vaccination_show ", 'style' => $display_meningococci_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['meningococci_opt'])){
                $subform->addElement('text',  'meningococci_text', array(
                    'value'        => $options['meningococci_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_meningococci_text = in_array('9', $meningococci_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'meningococci_text', array(
                'value'        => $options['meningococci_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_text_show ", 'style' => $display_meningococci_text ))
                ),
            ));
        }

        
        
        $hepatit_a_arr = PatientChildrenDiseases::getHepatitA();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'hepatit_a_opt', array(
                'label'      => "hepatit_a",
                'multiOptions' => $hepatit_a_arr,
                'value'    => isset($options['hepatit_a_opt']) && ! is_array($options['hepatit_a_opt']) ? array_map('trim', explode(",", $options['hepatit_a_opt'])) : $options['hepatit_a_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'hepatit_a_opt', array(
                'label'      => "hepatit_a",
                'multiOptions' => $hepatit_a_arr,
                'value'    => isset($options['hepatit_a_opt']) && ! is_array($options['hepatit_a_opt']) ? array_map('trim', explode(",", $options['hepatit_a_opt'])) : $options['hepatit_a_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "11" && this.checked) {$(".hepatit_a_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hepatit_a_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".hepatit_a_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".hepatit_a_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".hepatit_a_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".hepatit_a_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $hepatit_a_opt_arr = explode(",", $options['hepatit_a_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['hepatit_a_opt'])){
                $subform->addElement('text',  'hepatit_a_last_vaccination', array(
                    'value'        => $options['hepatit_a_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_a_last_vaccination = in_array('9', $hepatit_a_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_a_last_vaccination', array(
                'value'        => empty($options['hepatit_a_last_vaccination']) || $options['hepatit_a_last_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_a_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_a_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_last_vaccination_show ", 'style' => $display_hepatit_a_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['hepatit_a_opt'])){
                $subform->addElement('text',  'hepatit_a_next_vaccination', array(
                    'value'        => $options['hepatit_a_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_a_next_vaccination = in_array('10', $hepatit_a_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_a_next_vaccination', array(
                'value'        => empty($options['hepatit_a_next_vaccination']) || $options['hepatit_a_next_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_a_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_a_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_next_vaccination_show ", 'style' => $display_hepatit_a_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['hepatit_a_opt'])){
                $subform->addElement('text',  'hepatit_a_text', array(
                    'value'        => $options['hepatit_a_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_a_text = in_array('11', $hepatit_a_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_a_text', array(
                'value'        => $options['hepatit_a_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_text_show ", 'style' => $display_hepatit_a_text ))
                ),
            ));
        }

        
        
        $hepatit_b_arr = PatientChildrenDiseases::getHepatitB();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'hepatit_b_opt', array(
                'label'      => "hepatit_b",
                'multiOptions' => $hepatit_b_arr,
                'value'    => isset($options['hepatit_b_opt']) && ! is_array($options['hepatit_b_opt']) ? array_map('trim', explode(",", $options['hepatit_b_opt'])) : $options['hepatit_b_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
             ));
        } else {
            $subform->addElement('multiCheckbox', 'hepatit_b_opt', array(
                'label'      => "hepatit_b",
                'multiOptions' => $hepatit_b_arr,
                'value'    => isset($options['hepatit_b_opt']) && ! is_array($options['hepatit_b_opt']) ? array_map('trim', explode(",", $options['hepatit_b_opt'])) : $options['hepatit_b_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "13" && this.checked) {$(".hepatit_b_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".hepatit_b_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".hepatit_b_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hepatit_b_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".hepatit_b_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".hepatit_b_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $hepatit_b_opt_arr = explode(",", $options['hepatit_b_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['hepatit_b_opt'])){
                $subform->addElement('text',  'hepatit_b_last_vaccination', array(
                    'value'        => $options['hepatit_b_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_b_last_vaccination = in_array('11', $hepatit_b_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_b_last_vaccination', array(
                'value'        => empty($options['hepatit_b_last_vaccination']) || $options['hepatit_b_last_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_b_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_b_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_last_vaccination_show ", 'style' => $display_hepatit_b_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('12', $options['hepatit_b_opt'])){
                $subform->addElement('text',  'hepatit_b_next_vaccination', array(
                    'value'        => $options['hepatit_b_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_b_next_vaccination = in_array('12', $hepatit_b_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_b_next_vaccination', array(
                'value'        => empty($options['hepatit_b_next_vaccination']) || $options['hepatit_b_next_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_b_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_b_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_next_vaccination_show ", 'style' => $display_hepatit_b_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('13', $options['hepatit_b_opt'])){
                $subform->addElement('text',  'hepatit_b_text', array(
                    'value'        => $options['hepatit_b_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hepatit_b_text = in_array('13', $hepatit_b_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hepatit_b_text', array(
                'value'        => $options['hepatit_b_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_text_show ", 'style' => $display_hepatit_b_text ))
                ),
            ));
        }

        
        $tuberculosis_arr = PatientChildrenDiseases::getTuberculosis();
        $subform->addElement('multiCheckbox', 'tuberculosis_opt', array(
            'label'      => "tuberculosis",
            'multiOptions' => $tuberculosis_arr,
            'value'    => isset($options['tuberculosis_opt']) && ! is_array($options['tuberculosis_opt']) ? array_map('trim', explode(",", $options['tuberculosis_opt'])) : $options['tuberculosis_opt'],
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "4" && this.checked) {$(".tuberculosis_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".tuberculosis_text_show", $(this).parents(\'table\')).hide().val(\'\');} ',
        ));
        

        $tuberculosis_opt_arr = explode(",", $options['tuberculosis_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('4', $options['tuberculosis_opt'])){
                $subform->addElement('text',  'tuberculosis_text', array(
                    'value'        => $options['tuberculosis_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tuberculosis_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_tuberculosis_text = in_array('4', $tuberculosis_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'tuberculosis_text', array(
                'value'        => $options['tuberculosis_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tuberculosis_text_show ", 'style' => $display_tuberculosis_text ))
                ),
            ));
        }

        
        
        $hpv_arr = PatientChildrenDiseases::getHpv();
        if($options['formular_type'] == 'pdf' ){
            $subform->addElement('multiCheckbox', 'hpv_opt', array(
                'label'      => "hpv",
                'multiOptions' => $hpv_arr,
                'value'    => isset($options['hpv_opt']) && ! is_array($options['hpv_opt']) ? array_map('trim', explode(",", $options['hpv_opt'])) : $options['hpv_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
        } else {
            $subform->addElement('multiCheckbox', 'hpv_opt', array(
                'label'      => "hpv",
                'multiOptions' => $hpv_arr,
                'value'    => isset($options['hpv_opt']) && ! is_array($options['hpv_opt']) ? array_map('trim', explode(",", $options['hpv_opt'])) : $options['hpv_opt'],
                'required'   => false,
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "11" && this.checked) {$(".hpv_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hpv_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".hpv_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".hpv_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".hpv_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".hpv_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
        }

        
        $hpv_opt_arr = explode(",", $options['hpv_opt']);
        if($options['formular_type'] == 'pdf' ){
            if( in_array('9', $options['hpv_opt'])){
                $subform->addElement('text',  'hpv_last_vaccination', array(
                    'value'        => $options['hpv_last_vaccination'],
                    'label'        => $this->translate('last_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_last_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hpv_last_vaccination = in_array('9', $hpv_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hpv_last_vaccination', array(
                'value'        => empty($options['hpv_last_vaccination']) || $options['hpv_last_vaccination'] == "0000-00-00 00:00:00" || $options['hpv_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hpv_last_vaccination'])),
                'label'        => $this->translate('last_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_last_vaccination_show ", 'style' => $display_hpv_last_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('10', $options['hpv_opt'])){
                $subform->addElement('text',  'hpv_next_vaccination', array(
                    'value'        => $options['hpv_next_vaccination'],
                    'label'        => $this->translate('next_vaccination'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_next_vaccination_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hpv_next_vaccination = in_array('10', $hpv_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hpv_next_vaccination', array(
                'value'        => empty($options['hpv_next_vaccination']) || $options['hpv_next_vaccination'] == "0000-00-00 00:00:00" || $options['hpv_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hpv_next_vaccination'])),
                'label'        => $this->translate('next_vaccination'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_next_vaccination_show ", 'style' => $display_hpv_next_vaccination ))
                ),
                'class' => 'date allow_future',
                'data-mask' => "99.99.9999",
                'data-altfield' => 'start_date',
                'data-altformat' => 'yy-mm-dd',
            ));
        }

        if($options['formular_type'] == 'pdf' ){
            if( in_array('11', $options['hpv_opt'])){
                $subform->addElement('text',  'hpv_text', array(
                    'value'        => $options['hpv_text'],
                    'label'        => $this->translate('Sonstiges:'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_text_show " ))
                    ),
                ));
            }
            
        } else{
            $display_hpv_text = in_array('11', $hpv_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'hpv_text', array(
                'value'        => $options['hpv_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_text_show ", 'style' => $display_hpv_text ))
                ),
            ));
        }

        
        $subform->addElement('text',  'other_text', array(
            'value'        => $options['other_text'],
            'label'        => ' ',
            'required'     => false,
            'class'        => 'w400',
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' ))
            ),
        ));
        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_children_diseases($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_children_diseases");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientChildrenDiseases_legend'));
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
	    
	    
/* 	    $subform->addElement('note', 'label_rotavirus', array(
	        'value' => $this->translate('rotavirus'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array(
	                'tag' => 'div',
	                'id' => 'label_goal',
	                'style' => 'width: 35%;  display: inline-block;')),
	        ),
	    ));
	    
	    foreach ($rotavirus_arr as $needle => $tr) {
	        
	        $cb = $subform->createElement('checkbox', 'rotavirus_opt'.$needle, array(
	            'multiOptions' => [$needle => $tr],
	            'checkedValue'    => '1',
	            'uncheckedValue'  => '0',
	            'label'        => $tr,
	            'value'        => array($needle),
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
	            ),
	            'onChange' => 'if (this.value == "13" && this.checked) {$(".rotavirus_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".rotavirus_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	        ));

	        $subform->addElement($cb, "cb{$needle}");
	        

	        
	    }
	    $rotavirus_opt_arr = explode(",", $options['rotavirus_opt']);
	    $display_rotavirus_last_vaccination = in_array('11', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_last_vaccination', array(
	        'value'        => empty($options['rotavirus_last_vaccination']) || $options['rotavirus_last_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_last_vaccination'])),
	        'label'        => '',
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        'label'        => null,
	        'multiOptions' => array( 1 => ''),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'class' => 'rotavirus_last_vaccination_show', 'style' => $display_rotavirus_last_vaccination)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
	        ),
	        'class' => 'date',
	    ));
	    
	    $display_rotavirus_next_vaccination = in_array('12', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_next_vaccination', array(
	        'value'        => empty($options['rotavirus_next_vaccination']) || $options['rotavirus_next_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_next_vaccination'])),
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'class' => "rotavirus_next_vaccination_show ", 'style' => $display_rotavirus_next_vaccination)),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' , 'closeOnly' => true))
	        ),
	        'class' => 'date',
	    ));
	    
	    $display_rotavirus_text = in_array('13', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_text', array(
	        'value'        => $options['rotavirus_text'],
	        'label'        => '',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'class' => "rotavirus_text_show ", 'style' => $display_rotavirus_text )),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' , 'closeOnly' => true))
	        ),
	    )); */
	    
	    $rotavirus_arr = PatientChildrenDiseases::getRotavirus();
	    $subform->addElement('multiCheckbox', 'rotavirus_opt', array(
	        'label'      => "rotavirus",
	        'multiOptions' => $rotavirus_arr,
	        'value'    => isset($options['rotavirus_opt']) && ! is_array($options['rotavirus_opt']) ? array_map('trim', explode(",", $options['rotavirus_opt'])) : $options['rotavirus_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "13" && this.checked) {$(".rotavirus_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".rotavirus_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".rotavirus_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".rotavirus_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $rotavirus_opt_arr = explode(",", $options['rotavirus_opt']);
	    $display_rotavirus_last_vaccination = in_array('11', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_last_vaccination', array(
	        'value'        => empty($options['rotavirus_last_vaccination']) || $options['rotavirus_last_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_last_vaccination_show ", 'style' => $display_rotavirus_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_rotavirus_next_vaccination = in_array('12', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_next_vaccination', array(
	        'value'        => empty($options['rotavirus_next_vaccination']) || $options['rotavirus_next_vaccination'] == "0000-00-00 00:00:00" || $options['rotavirus_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rotavirus_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_next_vaccination_show ", 'style' => $display_rotavirus_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_rotavirus_text = in_array('13', $rotavirus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rotavirus_text', array(
	        'value'        => $options['rotavirus_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rotavirus_text_show ", 'style' => $display_rotavirus_text ))
	        ),
	    ));

	    	    
	    $varicella_arr = PatientChildrenDiseases::getVaricella();
	    $subform->addElement('multiCheckbox', 'varicella_opt', array(
	        'label'      => "varicella",
	        'multiOptions' => $varicella_arr,
	        'value'    => isset($options['varicella_opt']) && ! is_array($options['varicella_opt']) ? array_map('trim', explode(",", $options['varicella_opt'])) : $options['varicella_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "11" && this.checked) {$(".varicella_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".varicella_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".varicella_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".varicella_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".varicella_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".varicella_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $varicella_opt_arr = explode(",", $options['varicella_opt']);
	    $display_varicella_last_vaccination = in_array('9', $varicella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'varicella_last_vaccination', array(
	        'value'        => empty($options['varicella_last_vaccination']) || $options['varicella_last_vaccination'] == "0000-00-00 00:00:00" || $options['varicella_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['varicella_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_last_vaccination_show ", 'style' => $display_varicella_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_varicella_next_vaccination = in_array('10', $varicella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'varicella_next_vaccination', array(
	        'value'        => empty($options['varicella_next_vaccination']) || $options['varicella_next_vaccination'] == "0000-00-00 00:00:00" || $options['varicella_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['varicella_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_next_vaccination_show ", 'style' => $display_varicella_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_varicella_text = in_array('11', $varicella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'varicella_text', array(
	        'value'        => $options['varicella_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "varicella_text_show ", 'style' => $display_varicella_text ))
	        ),
	    ));
	    
	    
	    $measles_arr = PatientChildrenDiseases::getMeasles();
	    $subform->addElement('multiCheckbox', 'measles_opt', array(
	        'label'      => "measles",
	        'multiOptions' => $measles_arr,
	        'value'    => isset($options['measles_opt']) && ! is_array($options['measles_opt']) ? array_map('trim', explode(",", $options['measles_opt'])) : $options['measles_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "12" && this.checked) {$(".measles_text_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".measles_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".measles_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".measles_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".measles_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".measles_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $measles_opt_arr = explode(",", $options['measles_opt']);
	    $display_measles_last_vaccination = in_array('10', $measles_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'measles_last_vaccination', array(
	        'value'        => empty($options['measles_last_vaccination']) || $options['measles_last_vaccination'] == "0000-00-00 00:00:00" || $options['measles_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['measles_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_last_vaccination_show ", 'style' => $display_measles_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_measles_next_vaccination = in_array('11', $measles_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'measles_next_vaccination', array(
	        'value'        => empty($options['measles_next_vaccination']) || $options['measles_next_vaccination'] == "0000-00-00 00:00:00" || $options['measles_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['measles_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_next_vaccination_show ", 'style' => $display_measles_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_measles_text = in_array('12', $measles_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'measles_text', array(
	        'value'        => $options['measles_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "measles_text_show ", 'style' => $display_measles_text ))
	        ),
	    ));
	    
	    $mumps_arr = PatientChildrenDiseases::getMumps();
	    $subform->addElement('multiCheckbox', 'mumps_opt', array(
	        'label'      => "mumps",
	        'multiOptions' => $mumps_arr,
	        'value'    => isset($options['mumps_opt']) && ! is_array($options['mumps_opt']) ? array_map('trim', explode(",", $options['mumps_opt'])) : $options['mumps_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "10" && this.checked) {$(".mumps_text_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".mumps_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "8" && this.checked) {$(".mumps_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "8") {$(".mumps_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".mumps_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".mumps_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $mumps_opt_arr = explode(",", $options['mumps_opt']);
	    $display_mumps_last_vaccination = in_array('8', $mumps_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'mumps_last_vaccination', array(
	        'value'        => empty($options['mumps_last_vaccination']) || $options['mumps_last_vaccination'] == "0000-00-00 00:00:00" || $options['mumps_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['mumps_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_last_vaccination_show ", 'style' => $display_mumps_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_mumps_next_vaccination = in_array('9', $mumps_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'mumps_next_vaccination', array(
	        'value'        => empty($options['mumps_next_vaccination']) || $options['mumps_next_vaccination'] == "0000-00-00 00:00:00" || $options['mumps_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['mumps_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_next_vaccination_show ", 'style' => $display_mumps_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	
	    ));
	    
	    $display_mumps_text = in_array('10', $mumps_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'mumps_text', array(
	        'value'        => $options['mumps_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "mumps_text_show ", 'style' => $display_mumps_text ))
	        ), 
	    ));
	    
	    
	    $rubella_arr = PatientChildrenDiseases::getRubella();
	    $subform->addElement('multiCheckbox', 'rubella_opt', array(
	        'label'      => "rubella",
	        'multiOptions' => $rubella_arr,
	        'value'    => isset($options['rubella_opt']) && ! is_array($options['rubella_opt']) ? array_map('trim', explode(",", $options['rubella_opt'])) : $options['rubella_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "11" && this.checked) {$(".rubella_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".rubella_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".rubella_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".rubella_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".rubella_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".rubella_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $rubella_opt_arr = explode(",", $options['rubella_opt']);
	    $display_rubella_last_vaccination = in_array('9', $rubella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rubella_last_vaccination', array(
	        'value'        => empty($options['rubella_last_vaccination']) || $options['rubella_last_vaccination'] == "0000-00-00 00:00:00" || $options['rubella_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rubella_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_last_vaccination_show ", 'style' => $display_rubella_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_rubella_next_vaccination = in_array('10', $rubella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rubella_next_vaccination', array(
	        'value'        => empty($options['rubella_next_vaccination']) || $options['rubella_next_vaccination'] == "0000-00-00 00:00:00" || $options['rubella_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['rubella_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_next_vaccination_show ", 'style' => $display_rubella_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_rubella_text = in_array('11', $rubella_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'rubella_text', array(
	        'value'        => $options['rubella_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "rubella_text_show ", 'style' => $display_rubella_text ))
	        ),
	    ));
	    
	    $pertussis_arr = PatientChildrenDiseases::getPertussis();
	    $subform->addElement('multiCheckbox', 'pertussis_opt', array(
	        'label'      => "pertussis",
	        'multiOptions' => $pertussis_arr,
	        'value'    => isset($options['pertussis_opt']) && ! is_array($options['pertussis_opt']) ? array_map('trim', explode(",", $options['pertussis_opt'])) : $options['pertussis_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "16" && this.checked) {$(".pertussis_text_show", $(this).parents(\'table\')).show();} else if(this.value == "16") {$(".pertussis_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".pertussis_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".pertussis_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "15" && this.checked) {$(".pertussis_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".pertussis_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $pertussis_opt_arr = explode(",", $options['pertussis_opt']);
	    $display_pertussis_last_vaccination = in_array('14', $pertussis_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pertussis_last_vaccination', array(
	        'value'        => empty($options['pertussis_last_vaccination']) || $options['pertussis_last_vaccination'] == "0000-00-00 00:00:00" || $options['pertussis_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pertussis_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_last_vaccination_show ", 'style' => $display_pertussis_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_pertussis_next_vaccination = in_array('15', $pertussis_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pertussis_next_vaccination', array(
	        'value'        => empty($options['pertussis_next_vaccination']) || $options['pertussis_next_vaccination'] == "0000-00-00 00:00:00" || $options['pertussis_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pertussis_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_next_vaccination_show ", 'style' => $display_pertussis_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_pertussis_text = in_array('16', $pertussis_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pertussis_text', array(
	        'value'        => $options['pertussis_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pertussis_text_show ", 'style' => $display_pertussis_text ))
	        ),
	    ));
	    
	    
	    $diphtheria_arr = PatientChildrenDiseases::getDiphtheria();
	    $subform->addElement('multiCheckbox', 'diphtheria_opt', array(
	        'label'      => "diphtheria",
	        'multiOptions' => $diphtheria_arr,
	        'value'    => isset($options['diphtheria_opt']) && ! is_array($options['diphtheria_opt']) ? array_map('trim', explode(",", $options['diphtheria_opt'])) : $options['diphtheria_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "15" && this.checked) {$(".diphtheria_text_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".diphtheria_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "13" && this.checked) {$(".diphtheria_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".diphtheria_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".diphtheria_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".diphtheria_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $diphtheria_opt_arr = explode(",", $options['diphtheria_opt']);
	    $display_diphtheria_last_vaccination = in_array('13', $diphtheria_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'diphtheria_last_vaccination', array(
	        'value'        => empty($options['diphtheria_last_vaccination']) || $options['diphtheria_last_vaccination'] == "0000-00-00 00:00:00" || $options['diphtheria_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['diphtheria_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_last_vaccination_show ", 'style' => $display_diphtheria_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_diphtheria_next_vaccination = in_array('14', $diphtheria_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'diphtheria_next_vaccination', array(
	        'value'        => empty($options['diphtheria_next_vaccination']) || $options['diphtheria_next_vaccination'] == "0000-00-00 00:00:00" || $options['diphtheria_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['diphtheria_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_next_vaccination_show ", 'style' => $display_diphtheria_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_diphtheria_text = in_array('15', $diphtheria_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'diphtheria_text', array(
	        'value'        => $options['diphtheria_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "diphtheria_text_show ", 'style' => $display_diphtheria_text ))
	        ),
	    ));
	    
	    
	    $tetanus_arr = PatientChildrenDiseases::getTetanus();
	    $subform->addElement('multiCheckbox', 'tetanus_opt', array(
	        'label'      => "tetanus",
	        'multiOptions' => $tetanus_arr,
	        'value'    => isset($options['tetanus_opt']) && ! is_array($options['tetanus_opt']) ? array_map('trim', explode(",", $options['tetanus_opt'])) : $options['tetanus_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "16" && this.checked) {$(".tetanus_text_show", $(this).parents(\'table\')).show();} else if(this.value == "16") {$(".tetanus_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "14" && this.checked) {$(".tetanus_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".tetanus_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "15" && this.checked) {$(".tetanus_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "15") {$(".tetanus_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $tetanus_opt_arr = explode(",", $options['tetanus_opt']);
	    $display_tetanus_last_vaccination = in_array('14', $tetanus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'tetanus_last_vaccination', array(
	        'value'        => empty($options['tetanus_last_vaccination']) || $options['tetanus_last_vaccination'] == "0000-00-00 00:00:00" || $options['tetanus_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['tetanus_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_last_vaccination_show ", 'style' => $display_tetanus_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_tetanus_next_vaccination = in_array('15', $tetanus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'tetanus_next_vaccination', array(
	        'value'        => empty($options['tetanus_next_vaccination']) || $options['tetanus_next_vaccination'] == "0000-00-00 00:00:00" || $options['tetanus_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['tetanus_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_next_vaccination_show ", 'style' => $display_tetanus_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_tetanus_text = in_array('16', $tetanus_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'tetanus_text', array(
	        'value'        => $options['tetanus_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tetanus_text_show ", 'style' => $display_tetanus_text ))
	        ),
	    ));
	    
	    
	    $hib_arr = PatientChildrenDiseases::getHib();
	    $subform->addElement('multiCheckbox', 'hib_opt', array(
	        'label'      => "hib",
	        'multiOptions' => $hib_arr,
	        'value'    => isset($options['hib_opt']) && ! is_array($options['hib_opt']) ? array_map('trim', explode(",", $options['hib_opt'])) : $options['hib_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "13" && this.checked) {$(".hib_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".hib_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".hib_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hib_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".hib_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".hib_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $hib_opt_arr = explode(",", $options['hib_opt']);
	    $display_hib_last_vaccination = in_array('11', $hib_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hib_last_vaccination', array(
	        'value'        => empty($options['hib_last_vaccination']) || $options['hib_last_vaccination'] == "0000-00-00 00:00:00" || $options['hib_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hib_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_last_vaccination_show ", 'style' => $display_hib_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hib_next_vaccination = in_array('12', $hib_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hib_next_vaccination', array(
	        'value'        => empty($options['hib_next_vaccination']) || $options['hib_next_vaccination'] == "0000-00-00 00:00:00" || $options['hib_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hib_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_next_vaccination_show ", 'style' => $display_hib_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hib_text = in_array('13', $hib_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hib_text', array(
	        'value'        => $options['hib_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hib_text_show ", 'style' => $display_hib_text ))
	        ),
	    ));
	    
	    
	    $polio_arr = PatientChildrenDiseases::getPolio();
	    $subform->addElement('multiCheckbox', 'polio_opt', array(
	        'label'      => "polio",
	        'multiOptions' => $polio_arr,
	        'value'    => isset($options['polio_opt']) && ! is_array($options['polio_opt']) ? array_map('trim', explode(",", $options['polio_opt'])) : $options['polio_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "14" && this.checked) {$(".polio_text_show", $(this).parents(\'table\')).show();} else if(this.value == "14") {$(".polio_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".polio_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".polio_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "13" && this.checked) {$(".polio_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".polio_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $polio_opt_arr = explode(",", $options['polio_opt']);
	    $display_polio_last_vaccination = in_array('12', $polio_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'polio_last_vaccination', array(
	        'value'        => empty($options['polio_last_vaccination']) || $options['polio_last_vaccination'] == "0000-00-00 00:00:00" || $options['polio_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['polio_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_last_vaccination_show ", 'style' => $display_polio_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_polio_next_vaccination = in_array('13', $polio_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'polio_next_vaccination', array(
	        'value'        => empty($options['polio_next_vaccination']) || $options['polio_next_vaccination'] == "0000-00-00 00:00:00" || $options['polio_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['polio_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_next_vaccination_show ", 'style' => $display_polio_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_polio_text = in_array('14', $polio_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'polio_text', array(
	        'value'        => $options['polio_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "polio_text_show ", 'style' => $display_polio_text ))
	        ),
	    ));
	    
	    
	    $pneumococci_arr = PatientChildrenDiseases::getPneumococci();
	    $subform->addElement('multiCheckbox', 'pneumococci_opt', array(
	        'label'      => "pneumococci",
	        'multiOptions' => $pneumococci_arr,
	        'value'    => isset($options['pneumococci_opt']) && ! is_array($options['pneumococci_opt']) ? array_map('trim', explode(",", $options['pneumococci_opt'])) : $options['pneumococci_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "12" && this.checked) {$(".pneumococci_text_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".pneumococci_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".pneumococci_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".pneumococci_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".pneumococci_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".pneumococci_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $pneumococci_opt_arr = explode(",", $options['pneumococci_opt']);
	    $display_pneumococci_last_vaccination = in_array('10', $pneumococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pneumococci_last_vaccination', array(
	        'value'        => empty($options['pneumococci_last_vaccination']) || $options['pneumococci_last_vaccination'] == "0000-00-00 00:00:00" || $options['pneumococci_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pneumococci_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_last_vaccination_show ", 'style' => $display_pneumococci_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_pneumococci_next_vaccination = in_array('11', $pneumococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pneumococci_next_vaccination', array(
	        'value'        => empty($options['pneumococci_next_vaccination']) || $options['pneumococci_next_vaccination'] == "0000-00-00 00:00:00" || $options['pneumococci_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['pneumococci_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_next_vaccination_show ", 'style' => $display_pneumococci_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_pneumococci_text = in_array('12', $pneumococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'pneumococci_text', array(
	        'value'        => $options['pneumococci_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "pneumococci_text_show ", 'style' => $display_pneumococci_text ))
	        ),
	    ));
	    
	    
	    $meningococci_arr = PatientChildrenDiseases::getMeningococci();
	    $subform->addElement('multiCheckbox', 'meningococci_opt', array(
	        'label'      => "meningococci",
	        'multiOptions' => $meningococci_arr,
	        'value'    => isset($options['meningococci_opt']) && ! is_array($options['meningococci_opt']) ? array_map('trim', explode(",", $options['meningococci_opt'])) : $options['meningococci_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "9" && this.checked) {$(".meningococci_text_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".meningococci_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "7" && this.checked) {$(".meningococci_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "7") {$(".meningococci_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "8" && this.checked) {$(".meningococci_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "8") {$(".meningococci_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $meningococci_opt_arr = explode(",", $options['meningococci_opt']);
	    $display_meningococci_last_vaccination = in_array('7', $meningococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'meningococci_last_vaccination', array(
	        'value'        => empty($options['meningococci_last_vaccination']) || $options['meningococci_last_vaccination'] == "0000-00-00 00:00:00" || $options['meningococci_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['meningococci_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_last_vaccination_show ", 'style' => $display_meningococci_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_meningococci_next_vaccination = in_array('8', $meningococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'meningococci_next_vaccination', array(
	        'value'        => empty($options['meningococci_next_vaccination']) || $options['meningococci_next_vaccination'] == "0000-00-00 00:00:00" || $options['meningococci_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['meningococci_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_next_vaccination_show ", 'style' => $display_meningococci_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_meningococci_text = in_array('9', $meningococci_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'meningococci_text', array(
	        'value'        => $options['meningococci_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "meningococci_text_show ", 'style' => $display_meningococci_text ))
	        ),
	    ));
	    
	    
	    $hepatit_a_arr = PatientChildrenDiseases::getHepatitA();
	    $subform->addElement('multiCheckbox', 'hepatit_a_opt', array(
	        'label'      => "hepatit_a",
	        'multiOptions' => $hepatit_a_arr,
	        'value'    => isset($options['hepatit_a_opt']) && ! is_array($options['hepatit_a_opt']) ? array_map('trim', explode(",", $options['hepatit_a_opt'])) : $options['hepatit_a_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "11" && this.checked) {$(".hepatit_a_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hepatit_a_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".hepatit_a_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".hepatit_a_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".hepatit_a_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".hepatit_a_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $hepatit_a_opt_arr = explode(",", $options['hepatit_a_opt']);
	    $display_hepatit_a_last_vaccination = in_array('9', $hepatit_a_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_a_last_vaccination', array(
	        'value'        => empty($options['hepatit_a_last_vaccination']) || $options['hepatit_a_last_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_a_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_a_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_last_vaccination_show ", 'style' => $display_hepatit_a_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hepatit_a_next_vaccination = in_array('10', $hepatit_a_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_a_next_vaccination', array(
	        'value'        => empty($options['hepatit_a_next_vaccination']) || $options['hepatit_a_next_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_a_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_a_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_next_vaccination_show ", 'style' => $display_hepatit_a_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hepatit_a_text = in_array('11', $hepatit_a_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_a_text', array(
	        'value'        => $options['hepatit_a_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_a_text_show ", 'style' => $display_hepatit_a_text ))
	        ),
	    ));
	    
	    
	    $hepatit_b_arr = PatientChildrenDiseases::getHepatitB();
	    $subform->addElement('multiCheckbox', 'hepatit_b_opt', array(
	        'label'      => "hepatit_b",
	        'multiOptions' => $hepatit_b_arr,
	        'value'    => isset($options['hepatit_b_opt']) && ! is_array($options['hepatit_b_opt']) ? array_map('trim', explode(",", $options['hepatit_b_opt'])) : $options['hepatit_b_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "13" && this.checked) {$(".hepatit_b_text_show", $(this).parents(\'table\')).show();} else if(this.value == "13") {$(".hepatit_b_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "11" && this.checked) {$(".hepatit_b_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hepatit_b_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "12" && this.checked) {$(".hepatit_b_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "12") {$(".hepatit_b_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $hepatit_b_opt_arr = explode(",", $options['hepatit_b_opt']);
	    $display_hepatit_b_last_vaccination = in_array('11', $hepatit_b_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_b_last_vaccination', array(
	        'value'        => empty($options['hepatit_b_last_vaccination']) || $options['hepatit_b_last_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_b_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_b_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_last_vaccination_show ", 'style' => $display_hepatit_b_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hepatit_b_next_vaccination = in_array('12', $hepatit_b_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_b_next_vaccination', array(
	        'value'        => empty($options['hepatit_b_next_vaccination']) || $options['hepatit_b_next_vaccination'] == "0000-00-00 00:00:00" || $options['hepatit_b_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hepatit_b_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_next_vaccination_show ", 'style' => $display_hepatit_b_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hepatit_b_text = in_array('13', $hepatit_b_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hepatit_b_text', array(
	        'value'        => $options['hepatit_b_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hepatit_b_text_show ", 'style' => $display_hepatit_b_text ))
	        ),
	    ));
	    
	    $tuberculosis_arr = PatientChildrenDiseases::getTuberculosis();
	    $subform->addElement('multiCheckbox', 'tuberculosis_opt', array(
	        'label'      => "tuberculosis",
	        'multiOptions' => $tuberculosis_arr,
	        'value'    => isset($options['tuberculosis_opt']) && ! is_array($options['tuberculosis_opt']) ? array_map('trim', explode(",", $options['tuberculosis_opt'])) : $options['tuberculosis_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "4" && this.checked) {$(".tuberculosis_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".tuberculosis_text_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $tuberculosis_opt_arr = explode(",", $options['tuberculosis_opt']);
	    $display_tuberculosis_text = in_array('4', $tuberculosis_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'tuberculosis_text', array(
	        'value'        => $options['tuberculosis_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "tuberculosis_text_show ", 'style' => $display_tuberculosis_text ))
	        ),
	    ));
	    
	    
	    $hpv_arr = PatientChildrenDiseases::getHpv();
	    $subform->addElement('multiCheckbox', 'hpv_opt', array(
	        'label'      => "hpv",
	        'multiOptions' => $hpv_arr,
	        'value'    => isset($options['hpv_opt']) && ! is_array($options['hpv_opt']) ? array_map('trim', explode(",", $options['hpv_opt'])) : $options['hpv_opt'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'children_diseases')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "11" && this.checked) {$(".hpv_text_show", $(this).parents(\'table\')).show();} else if(this.value == "11") {$(".hpv_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "9" && this.checked) {$(".hpv_last_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "9") {$(".hpv_last_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "10" && this.checked) {$(".hpv_next_vaccination_show", $(this).parents(\'table\')).show();} else if(this.value == "10") {$(".hpv_next_vaccination_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));
	    
	    $hpv_opt_arr = explode(",", $options['hpv_opt']);
	    $display_hpv_last_vaccination = in_array('9', $hpv_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hpv_last_vaccination', array(
	        'value'        => empty($options['hpv_last_vaccination']) || $options['hpv_last_vaccination'] == "0000-00-00 00:00:00" || $options['hpv_last_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hpv_last_vaccination'])),
	        'label'        => $this->translate('last_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_last_vaccination_show ", 'style' => $display_hpv_last_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hpv_next_vaccination = in_array('10', $hpv_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hpv_next_vaccination', array(
	        'value'        => empty($options['hpv_next_vaccination']) || $options['hpv_next_vaccination'] == "0000-00-00 00:00:00" || $options['hpv_next_vaccination'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['hpv_next_vaccination'])),
	        'label'        => $this->translate('next_vaccination'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_next_vaccination_show ", 'style' => $display_hpv_next_vaccination ))
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',	 
	    ));
	    
	    $display_hpv_text = in_array('11', $hpv_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'hpv_text', array(
	        'value'        => $options['hpv_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "hpv_text_show ", 'style' => $display_hpv_text ))
	        ),
	    ));
	    
	    $subform->addElement('text',  'other_text', array(
	        'value'        => $options['other_text'],
	        'label'        => ' ',
	        'required'     => false,
	        'class'        => 'w400',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'children_diseases_label w50', 'style'=>'width:100px;')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' ))
	        ),
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_children_diseases($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if (!in_array('11',$data['rotavirus_opt'])){
	        $data['rotavirus_last_vaccination'] = "";
	    } else {
	        $data['rotavirus_last_vaccination'] = date('Y-m-d', strtotime($data['rotavirus_last_vaccination']));
	    }
	    if (!in_array('12',$data['rotavirus_opt'])){
	        $data['rotavirus_next_vaccination'] = "";
	    } else {
	        $data['rotavirus_next_vaccination'] = date('Y-m-d', strtotime($data['rotavirus_next_vaccination']));
	    }
	    if (!in_array('13',$data['rotavirus_opt'])){
	        $data['rotavirus_text'] = "";
	    }
	    $data['rotavirus_opt'] = isset($data['rotavirus_opt']) ?  implode(",", $data['rotavirus_opt']) : null;
	    
	    if (!in_array('9',$data['varicella_opt'])){
	        $data['varicella_last_vaccination'] = "";
	    } else {
	        $data['varicella_last_vaccination'] = date('Y-m-d', strtotime($data['varicella_last_vaccination']));
	    }
	    if (!in_array('10',$data['varicella_opt'])){
	        $data['varicella_next_vaccination'] = "";
	    } else {
	        $data['varicella_next_vaccination'] = date('Y-m-d', strtotime($data['varicella_next_vaccination']));
	    }
	    if (!in_array('11',$data['varicella_opt'])){
	        $data['varicella_text'] = "";
	    }
	    $data['varicella_opt'] = isset($data['varicella_opt']) ?  implode(",", $data['varicella_opt']) : null;
	    
	    if (!in_array('10',$data['measles_opt'])){
	        $data['measles_last_vaccination'] = "";
	    } else {
	        $data['measles_last_vaccination'] = date('Y-m-d', strtotime($data['measles_last_vaccination']));
	    }
	    if (!in_array('11',$data['measles_opt'])){
	        $data['measles_next_vaccination'] = "";
	    } else {
	        $data['measles_next_vaccination'] = date('Y-m-d', strtotime($data['measles_next_vaccination']));
	    }
	    if (!in_array('12',$data['measles_opt'])){
	        $data['measles_text'] = "";
	    }
	    $data['measles_opt'] = isset($data['measles_opt']) ?  implode(",", $data['measles_opt']) : null;
	    
	    if (!in_array('8',$data['mumps_opt'])){
	        $data['mumps_last_vaccination'] = "";
	    } else {
	        $data['mumps_last_vaccination'] = date('Y-m-d', strtotime($data['mumps_last_vaccination']));
	    }
	    if (!in_array('9',$data['mumps_opt'])){
	        $data['mumps_next_vaccination'] = "";
	    } else {
	        $data['mumps_next_vaccination'] = date('Y-m-d', strtotime($data['mumps_next_vaccination']));
	    }
	    if (!in_array('10',$data['mumps_opt'])){
	        $data['mumps_text'] = "";
	    }
	    $data['mumps_opt']   = isset($data['mumps_opt']) ?  implode(",", $data['mumps_opt']) : null;
	    
	    if (!in_array('9',$data['rubella_opt'])){
	        $data['rubella_last_vaccination'] = "";
	    } else {
	        $data['rubella_last_vaccination'] = date('Y-m-d', strtotime($data['rubella_last_vaccination']));
	    }
	    if (!in_array('10',$data['rubella_opt'])){
	        $data['rubella_next_vaccination'] = "";
	    } else {
	        $data['rubella_next_vaccination'] = date('Y-m-d', strtotime($data['rubella_next_vaccination']));
	    }
	    if (!in_array('11',$data['rubella_opt'])){
	        $data['rubella_text'] = "";
	    }
	    $data['rubella_opt'] = isset($data['rubella_opt']) ?  implode(",", $data['rubella_opt']) : null;
	    
	    if (!in_array('14',$data['pertussis_opt'])){
	        $data['pertussis_last_vaccination'] = "";
	    } else {
	        $data['pertussis_last_vaccination'] = date('Y-m-d', strtotime($data['pertussis_last_vaccination']));
	    }
	    if (!in_array('15',$data['pertussis_opt'])){
	        $data['pertussis_next_vaccination'] = "";
	    } else {
	        $data['pertussis_next_vaccination'] = date('Y-m-d', strtotime($data['pertussis_next_vaccination']));
	    }
	    if (!in_array('16',$data['pertussis_opt'])){
	        $data['pertussis_text'] = "";
	    }
	    $data['pertussis_opt']  = isset($data['pertussis_opt']) ?  implode(",", $data['pertussis_opt']) : null;
	    
	    if (!in_array('13',$data['diphtheria_opt'])){
	        $data['diphtheria_last_vaccination'] = "";
	    } else {
	        $data['diphtheria_last_vaccination'] = date('Y-m-d', strtotime($data['diphtheria_last_vaccination']));
	    }
	    if (!in_array('14',$data['diphtheria_opt'])){
	        $data['diphtheria_next_vaccination'] = "";
	    } else {
	        $data['diphtheria_next_vaccination'] = date('Y-m-d', strtotime($data['diphtheria_next_vaccination']));
	    }
	    if (!in_array('15',$data['diphtheria_opt'])){
	        $data['diphtheria_text'] = "";
	    }
	    $data['diphtheria_opt'] = isset($data['diphtheria_opt']) ?  implode(",", $data['diphtheria_opt']) : null;
	    
	    if (!in_array('14',$data['tetanus_opt'])){
	        $data['tetanus_last_vaccination'] = "";
	    } else {
	        $data['tetanus_last_vaccination'] = date('Y-m-d', strtotime($data['tetanus_last_vaccination']));
	    }
	    if (!in_array('15',$data['tetanus_opt'])){
	        $data['tetanus_next_vaccination'] = "";
	    } else {
	        $data['tetanus_next_vaccination'] = date('Y-m-d', strtotime($data['tetanus_next_vaccination']));
	    }
	    if (!in_array('16',$data['tetanus_opt'])){
	        $data['tetanus_text'] = "";
	    }
	    $data['tetanus_opt'] = isset($data['tetanus_opt']) ?  implode(",", $data['tetanus_opt']) : null;
	    
	    if (!in_array('11',$data['hib_opt'])){
	        $data['hib_last_vaccination'] = "";
	    } else {
	        $data['hib_last_vaccination'] = date('Y-m-d', strtotime($data['hib_last_vaccination']));
	    }
	    if (!in_array('12',$data['hib_opt'])){
	        $data['hib_next_vaccination'] = "";
	    } else {
	        $data['hib_next_vaccination'] = date('Y-m-d', strtotime($data['hib_next_vaccination']));
	    }
	    if (!in_array('13',$data['hib_opt'])){
	        $data['hib_text'] = "";
	    }
	    $data['hib_opt']     = isset($data['hib_opt']) ?  implode(",", $data['hib_opt']) : null;
	    
	    if (!in_array('12',$data['polio_opt'])){
	        $data['polio_last_vaccination'] = "";
	    } else {
	        $data['polio_last_vaccination'] = date('Y-m-d', strtotime($data['polio_last_vaccination']));
	    }
	    if (!in_array('13',$data['polio_opt'])){
	        $data['polio_next_vaccination'] = "";
	    } else {
	        $data['polio_next_vaccination'] = date('Y-m-d', strtotime($data['polio_next_vaccination']));
	    }
	    if (!in_array('14',$data['polio_opt'])){
	        $data['polio_text'] = "";
	    }
	    $data['polio_opt']   = isset($data['polio_opt']) ?  implode(",", $data['polio_opt']) : null;
	    
	    if (!in_array('10',$data['pneumococci_opt'])){
	        $data['pneumococci_last_vaccination'] = "";
	    } else {
	        $data['pneumococci_last_vaccination'] = date('Y-m-d', strtotime($data['pneumococci_last_vaccination']));
	    }
	    if (!in_array('11',$data['pneumococci_opt'])){
	        $data['pneumococci_next_vaccination'] = "";
	    } else {
	        $data['pneumococci_next_vaccination'] = date('Y-m-d', strtotime($data['pneumococci_next_vaccination']));
	    }
	    if (!in_array('12',$data['pneumococci_opt'])){
	        $data['pneumococci_text'] = "";
	    }
	    $data['pneumococci_opt']  = isset($data['pneumococci_opt']) ?  implode(",", $data['pneumococci_opt']) : null;
	    
	    if (!in_array('7',$data['meningococci_opt'])){
	        $data['meningococci_last_vaccination'] = "";
	    } else {
	        $data['meningococci_last_vaccination'] = date('Y-m-d', strtotime($data['meningococci_last_vaccination']));
	    }
	    if (!in_array('8',$data['meningococci_opt'])){
	        $data['meningococci_next_vaccination'] = "";
	    } else {
	        $data['meningococci_next_vaccination'] = date('Y-m-d', strtotime($data['meningococci_next_vaccination']));
	    }
	    if (!in_array('9',$data['meningococci_opt'])){
	        $data['meningococci_text'] = "";
	    }
	    $data['meningococci_opt'] = isset($data['meningococci_opt']) ?  implode(",", $data['meningococci_opt']) : null;
	    
	    if (!in_array('9',$data['hepatit_a_opt'])){
	        $data['hepatit_a_last_vaccination'] = "";
	    } else {
	        $data['hepatit_a_last_vaccination'] = date('Y-m-d', strtotime($data['hepatit_a_last_vaccination']));
	    }
	    if (!in_array('10',$data['hepatit_a_opt'])){
	        $data['hepatit_a_next_vaccination'] = "";
	    } else {
	        $data['hepatit_a_next_vaccination'] = date('Y-m-d', strtotime($data['hepatit_a_next_vaccination']));
	    }
	    if (!in_array('11',$data['hepatit_a_opt'])){
	        $data['hepatit_a_text'] = "";
	    }
	    $data['hepatit_a_opt']    = isset($data['hepatit_a_opt']) ?  implode(",", $data['hepatit_a_opt']) : null;
	    
	    if (!in_array('11',$data['hepatit_b_opt'])){
	        $data['hepatit_b_last_vaccination'] = "";
	    } else {
	        $data['hepatit_b_last_vaccination'] = date('Y-m-d', strtotime($data['hepatit_b_last_vaccination']));
	    }
	    if (!in_array('12',$data['hepatit_b_opt'])){
	        $data['hepatit_b_next_vaccination'] = "";
	    } else {
	        $data['hepatit_b_next_vaccination'] = date('Y-m-d', strtotime($data['hepatit_b_next_vaccination']));
	    }
	    if (!in_array('13',$data['hepatit_b_opt'])){
	        $data['hepatit_b_text'] = "";
	    }
	    $data['hepatit_b_opt']    = isset($data['hepatit_b_opt']) ?  implode(",", $data['hepatit_b_opt']) : null;
	    
	    if (!in_array('4',$data['tuberculosis_opt'])){
	        $data['tuberculosis_text'] = "";
	    }
	    $data['tuberculosis_opt'] = isset($data['tuberculosis_opt']) ?  implode(",", $data['tuberculosis_opt']) : null;
	    
	    if (!in_array('9',$data['hpv_opt'])){
	        $data['hpv_last_vaccination'] = "";
	    } else {
	        $data['hpv_last_vaccination'] = date('Y-m-d', strtotime($data['hpv_last_vaccination']));
	    }
	    if (!in_array('10',$data['hpv_opt'])){
	        $data['hpv_next_vaccination'] = "";
	    } else {
	        $data['hpv_next_vaccination'] = date('Y-m-d', strtotime($data['hpv_next_vaccination']));
	    }
	    if (!in_array('11',$data['hpv_opt'])){
	        $data['hpv_text'] = "";
	    }
	    $data['hpv_opt'] = isset($data['hpv_opt']) ?  implode(",", $data['hpv_opt']) : null;
	    
	    $r = PatientChildrenDiseasesTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>