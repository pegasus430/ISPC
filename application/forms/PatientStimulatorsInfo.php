<?php
//ISPC-2787 Lore 11.01.2021

require_once("Pms/Form.php");

class Application_Form_PatientStimulatorsInfo extends Pms_Form
{
    protected $_model = 'PatientStimulatorsInfo';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('vagus_opt'), "cols" => array("vagus")),
            array( "label" => $this->translate('pacemaker_opt'), "cols" => array("pacemaker")),           
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientStimulatorsInfo';
    
	
    public function create_form_block_patient_stimulatorsinfo($options =  array() , $elementsBelongTo = null)
    {
        //called from contact form
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_stimulators_info");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_stimulatorsinfo');
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
        
        $subform->addElement('radio',  'vagus_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['vagus_opt'],
            'label'        => $this->translate("vagus_opt"),
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
            'onChange' => "if(this.value == '3') { $('.vagus_text_show', $(this).parents('table')).show();} else { $('.vagus_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_vagus_text = $options['vagus_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'vagus_text', array(
            'value'        => $options['vagus_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'vagus_text_show', 'style' => $display_vagus_text)),
            ),
        ));
        

        $subform->addElement('radio',  'pacemaker_opt', array(
            'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
            'value'        => $options['pacemaker_opt'],
            'label'        => $this->translate("pacemaker_opt"),
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
            'onChange' => "if(this.value == '3') { $('.pacemaker_text_show', $(this).parents('table')).show();} else { $('.pacemaker_text_show', $(this).parents('table')).hide();} ",
            
        ));
        
        $display_pacemaker_text = $options['pacemaker_opt'] == 3 ? '' : array('display:none');
        $subform->addElement('text', 'pacemaker_text', array(
            'value'        => $options['pacemaker_text'],
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td' )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'pacemaker_text_show', 'style' => $display_pacemaker_text )),
            ),
        ));
        

                
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_stimulators_info($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_stimulators_info");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientStimulatorsInfo_legend'));
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
	    
	    $subform->addElement('radio',  'vagus_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['vagus_opt'],
	        'label'        => $this->translate("vagus_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.vagus_text_show', $(this).parents('table')).show();} else { $('.vagus_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_vagus_text = $options['vagus_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'vagus_text', array(
	        'value'        => $options['vagus_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr','class'=>'vagus_text_show', 'style' => $display_vagus_text)),
	        ),
	    ));
	    
	    
	    $subform->addElement('radio',  'pacemaker_opt', array(
	        'multiOptions' => array('1' => 'Nein', '2' => 'keine Angabe', '3' => 'Ja'),
	        'value'        => $options['pacemaker_opt'],
	        'label'        => $this->translate("pacemaker_opt"),
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
	        'onChange' => "if(this.value == '3') { $('.pacemaker_text_show', $(this).parents('table')).show();} else { $('.pacemaker_text_show', $(this).parents('table')).hide();} ",
	        
	    ));
	    
	    $display_pacemaker_text = $options['pacemaker_opt'] == 3 ? '' : array('display:none');
	    $subform->addElement('text', 'pacemaker_text', array(
	        'value'        => $options['pacemaker_text'],
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td' )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'pacemaker_text_show', 'style' => $display_pacemaker_text )),
	        ),
	    ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_stimulators_info($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    if ($data['vagus_opt'] != 3){
	        $data['vagus_text'] = "";
	    }
	    if ($data['pacemaker_opt'] != 3 ){
	        $data['pacemaker_text'] = "";
	    }

	    $r = PatientStimulatorsInfoTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
}




?>