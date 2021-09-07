<?php
//ISPC-2670 Lore 24.09.2020

require_once("Pms/Form.php");

class Application_Form_PatientEvn extends Pms_Form
{
    protected $_model = 'PatientEvn';

    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientEvn';
    
	
    public function create_form_block_patient_evn($options =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_patient_evn");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_evn');
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
        

        if($options['formular_type'] == 'pdf'){
            $evn_options_arr = PatientEvn::getEvnoptions();
            $subform->addElement('radio',  'evn_option', array(
                'value'        => isset($options['evn_option']) ? $options['evn_option'] : null,
                'label'        => '',
                'separator'    => '<br>',
                'required'     => false,
                'multiOptions' => $evn_options_arr,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2 )),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
            ));
            
            $display_evn_text = $options['evn_option'] == 5 ? '' : 'display:none;';
            $subform->addElement('text',  'evn_text', array(
                'value'        => $options['evn_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),  
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'style'        => $display_evn_text." position: relative; top:100px",
                'class'        => 'display_evn_text',
            ));
        } else {
            $evn_options_arr = PatientEvn::getEvnoptions();
            $subform->addElement('radio',  'evn_option', array(
                'value'        => isset($options['evn_option']) ? $options['evn_option'] : null,
                'label'        => 'EVN vorliegend',
                'separator'    => '<br>',
                'required'     => false,
                'multiOptions' => $evn_options_arr,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'onChange' => "if(this.value == '5') { $('.display_evn_text', $(this).parents('table')).show();} else { $('.display_evn_text', $(this).parents('table')).hide();} ",
            ));
            
            
            $display_evn_text = $options['evn_option'] == 5 ? '' : 'display:none;';
            $subform->addElement('text',  'evn_text', array(
                'value'        => $options['evn_text'],
                'label'        => '',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
                ),
                'style'        => $display_evn_text,
                'class'        => 'display_evn_text',
            ));
        }
            

        
        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
	
	
    public function save_form_patient_evn($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    $r = PatientEvnTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
	

	

	

	
	
}




?>