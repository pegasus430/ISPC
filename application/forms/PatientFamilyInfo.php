<?php
//ISPC-2773 Lore 14.12.2020

require_once("Pms/Form.php");

class Application_Form_PatientFamilyInfo extends Pms_Form
{
    protected $_model = 'PatientFamilyInfo';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('Marital_status_of_parents'), "cols" => array("parents_marital_status_x")),
            array( "label" => $this->translate('parental_consanguinity'), "cols" => array("parental_consanguinity_x")),
            array( "label" => $this->translate('child_residing'), "cols" => array("child_residing_x")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientFamilyInfo';
    
	
    public function create_form_block_patient_familyinfo($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_family_info");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_familyinfo');
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
        
         $ms_arr = PatientFamilyInfo::getMaritalStatus();
         if($options['formular_type'] == 'pdf' ){
             
            
            $subform->addElement('multiCheckbox', 'parents_marital_status_opt', array(
                'label'      => "Marital_status_of_parents",
                'multiOptions' => $ms_arr,
                'required'   => false,
                'value'    => isset($options['parents_marital_status_opt']) && ! is_array($options['parents_marital_status_opt']) ? array_map('trim', explode(",", $options['parents_marital_status_opt'])) : $options['parents_marital_status_opt'],
                'filters'    => array('StringTrim'),
                'validators' => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class' => 'marks_options')),
                    array('Label', array('tag' => 'td', 'tagClass'=>'marks_label ')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                )
            ));
         } else{
            $subform->addElement('multiCheckbox', 'parents_marital_status_opt', array(
                'label'      => "Marital_status_of_parents",
                'multiOptions' => $ms_arr,
                'value'    => isset($options['parents_marital_status_opt']) && ! is_array($options['parents_marital_status_opt']) ? array_map('trim', explode(",", $options['parents_marital_status_opt'])) : $options['parents_marital_status_opt'],
                'required'   => false,
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.value == "6" && this.checked) {$(".other_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".other_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                               else if (this.value == "4" && this.checked) {$(".divorced_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".divorced_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                               else if (this.value == "5" && this.checked) {$(".widowed_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".widowed_text_show", $(this).parents(\'table\')).hide().val(\'\');} ',
            ));
         }
        
 
        
        
        if($options['formular_type'] == 'pdf' ){
            if( in_array('4', $options['parents_marital_status_opt'])){
                    
                $subform->addElement('text',  'divorced_text', array(
                    'value'        => $options['divorced_text'],
                    'label'        => $this->translate('divorced_text'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "divorced_text_show " ))
                    ),
                ));
            }
            
        } else{
            $parents_marital_status_opt_arr = explode(",", $options['parents_marital_status_opt']);
            $display_divorced_text = in_array('4', $parents_marital_status_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'divorced_text', array(
                'value'        => $options['divorced_text'],
                'label'        => $this->translate('divorced_text'),
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "divorced_text_show ", 'style' => $display_divorced_text ))
                ),
            ));
        }
        
        if($options['formular_type'] == 'pdf' ){
            
            if( in_array('5', $options['parents_marital_status_opt'])){
                $subform->addElement('text',  'widowed_text', array(
                    'value'        => $options['widowed_text'],
                    'label'        => $this->translate('widowed_text'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "widowed_text_show " ))
                    ),
                ));
                
            }
            
        } else{
        
            $display_widowed_text = in_array('5', $parents_marital_status_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'widowed_text', array(
                'value'        => $options['widowed_text'],
                'label'        => $this->translate('widowed_text'),
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "widowed_text_show ", 'style' => $display_widowed_text ))
                ),
            ));
        }
        
        
        if($options['formular_type'] == 'pdf' ){
            if( in_array('6', $options['parents_marital_status_opt'])){
                $subform->addElement('text',  'other_text', array(
                    'value'        => $options['other_text'],
                    'label'        => 'Sonstiges:',
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'validators'   => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                        array('Label', array('tag' => 'td')),
                        array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "other_text_show " ))
                    ),
                )); 
            }
        }
        else
        {
            $display_other_text = in_array('6', $parents_marital_status_opt_arr) ? '' : 'display:none';
            $subform->addElement('text',  'other_text', array(
                'value'        => $options['other_text'],
                'label'        => 'Sonstiges:',
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "other_text_show ", 'style' => $display_other_text ))
                ),
            )); 
        }
        
        $subform->addElement('radio',  'parental_consanguinity', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'Neutral', '3' => 'Ja'),
            'value'        => $options['parental_consanguinity'],
            'label'        => $this->translate("parental_consanguinity"),
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
        
        $cr_arr = PatientFamilyInfo::getChildResiding();
        
        $subform->addElement('radio',  'child_residing', array(
            'multiOptions' => $cr_arr,
            'value'        => $options['child_residing'],
            'label'        => $this->translate("child_residing"),
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
            'onChange' => "if(this.value == '10') { $('.child_residing_text_show', $(this).parents('table')).show();} else { $('.child_residing_text_show', $(this).parents('table')).hide();} ",
        ));
        
        if($options['formular_type'] == 'pdf' ){
            
            if($options['child_residing'] == 10 ){
                $subform->addElement('text', 'child_residing_text', array(
                    'value'        => $options['child_residing_text'],
                    'label'        => $this->translate('child_residing_text'),
                    'required'     => false,
                    'class'        => 'w400',
                    'filters'      => array('StringTrim'),
                    'decorators'   => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text)),
                        array('Label', array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text )),
                        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "child_residing_text_show" , 'style' => $display_child_residing_text )),
                    ),
                ));
            }
            
            
        } else{
            
            $display_child_residing_text = $options['child_residing'] == 10 ? '' : array('display:none');
            $subform->addElement('text', 'child_residing_text', array(
                'value'        => $options['child_residing_text'],
                'label'        => $this->translate('child_residing_text'),
                'required'     => false,
                'class'        => 'w400',
                'filters'      => array('StringTrim'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text)),
                    array('Label', array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text )),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "child_residing_text_show" , 'style' => $display_child_residing_text )),
                ),
            ));
        }
        
        

        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_family_info($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_family_info");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientFamilyInfo_legend'));
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
	    
	    $ms_arr = PatientFamilyInfo::getMaritalStatus();
	    $subform->addElement('multiCheckbox', 'parents_marital_status_opt', array(
	        'label'      => "Marital_status_of_parents",
	        'multiOptions' => $ms_arr,
	        'required'   => false,
	        'value'    => isset($options['parents_marital_status_opt']) && ! is_array($options['parents_marital_status_opt']) ? array_map('trim', explode(",", $options['parents_marital_status_opt'])) : $options['parents_marital_status_opt'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'parents_marital_status')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'parents_marital_status_label w100')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value == "6" && this.checked) {$(".other_text_show", $(this).parents(\'table\')).show();} else if(this.value == "6") {$(".other_text_show", $(this).parents(\'table\')).hide().val(\'\');}
                           else if (this.value == "4" && this.checked) {$(".divorced_text_show", $(this).parents(\'table\')).show();} else if(this.value == "4") {$(".divorced_text_show", $(this).parents(\'table\')).hide().val(\'\');} 
                           else if (this.value == "5" && this.checked) {$(".widowed_text_show", $(this).parents(\'table\')).show();} else if(this.value == "5") {$(".widowed_text_show", $(this).parents(\'table\')).hide().val(\'\');} ',
	    ));

	    $parents_marital_status_opt_arr = explode(",", $options['parents_marital_status_opt']);
	    $display_divorced_text = in_array('4', $parents_marital_status_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'divorced_text', array(
	        'value'        => $options['divorced_text'],
	        'label'        => $this->translate('divorced_text'),
	        'required'     => false,
	        'class'        => 'w300',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "divorced_text_show ", 'style' => $display_divorced_text ))
	        ),
	    ));
	    
	    $display_widowed_text = in_array('5', $parents_marital_status_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'widowed_text', array(
	        'value'        => $options['widowed_text'],
	        'label'        => $this->translate('widowed_text'),
	        'required'     => false,
	        'class'        => 'w300',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "widowed_text_show ", 'style' => $display_widowed_text ))
	        ),
	    ));
	    
	    $display_other_text = in_array('6', $parents_marital_status_opt_arr) ? '' : 'display:none';
	    $subform->addElement('text',  'other_text', array(
	        'value'        => $options['other_text'],
	        'label'        => 'Sonstiges:',
	        'required'     => false,
	        'class'        => 'w300',
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr', 'class' => "other_text_show ", 'style' => $display_other_text ))
	        ),
	    ));

	    $subform->addElement('radio',  'parental_consanguinity', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'Neutral', '3' => 'Ja'),
	        'value'        => $options['parental_consanguinity'],
	        'label'        => $this->translate("parental_consanguinity"),
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

	    $cr_arr = PatientFamilyInfo::getChildResiding();
	    
	    $subform->addElement('radio',  'child_residing', array(
	        'multiOptions' => $cr_arr,
	        'value'        => $options['child_residing'],
	        'label'        => $this->translate("child_residing"),
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
	        'onChange' => "if(this.value == '10') { $('.child_residing_text_show', $(this).parents('table')).show();} else { $('.child_residing_text_show', $(this).parents('table')).hide();} ",
	    ));
	    
	    $display_child_residing_text = $options['child_residing'] == 10 ? '' : array('display:none');
	    $subform->addElement('text', 'child_residing_text', array(
	        'value'        => $options['child_residing_text'],
	        'label'        => $this->translate('child_residing_text'),
	        'required'     => false,
	        'class'        => 'w300',
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text)),
	            array('Label', array('tag' => 'td', 'class'=>'child_residing_text_show', 'style' => $display_child_residing_text )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => "child_residing_text_show" , 'style' => $display_child_residing_text )),
	        ),
	    ));
	    
	    	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_family_info($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if (!in_array('4',$data['parents_marital_status_opt'])){
	        $data['divorced_text'] = "";
	    }
	    if (!in_array('5',$data['parents_marital_status_opt'])){
	        $data['widowed_text'] = "";
	    }
	    if (!in_array('6',$data['parents_marital_status_opt'])){
	        $data['other_text'] = "";
	    }
	    if ($data['child_residing'] != '10'){
	        $data['child_residing_text'] = "";
	    }
	    $data['parents_marital_status_opt'] = isset($data['parents_marital_status_opt']) ?  implode(",", $data['parents_marital_status_opt']) : null;
	    
	    $r = PatientFamilyInfoTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>