<?php
class Application_Form_PatientAnlage33a extends Pms_Form
{

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientAnlage33a::TRIGGER_FORMID;
    private $triggerformname = PatientAnlage33a::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientAnlage33a::LANGUAGE_ARRAY;
    
    
    public function create_form_anlage33a ($values =  array() , $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Annexes 3 and 3a:'));
        $subform->setAttrib("class", "label_same_size");
        
        $obj = new PatientAnlage33a();
        $list = $obj->getEnumValuesDefaults();
        
        
        $subform->addElement('multiCheckbox', 'selected_value', array(
	        'label'      => null,//$this->translate('enable/disable module'),
	        'separator'  => '</td>'.PHP_EOL.'<td>',
	        'required'   => false,
	        'multiOptions' => $list,
		    'value' => $values['selected_value'],
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            //'Label',
// 	            array('Label', array('tag' => 'td')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	         
	    ));
    
    
        return $this->filter_by_block_name($subform , __FUNCTION__);
    }

    
    
    public function save_form_anlage33a($ipid =  null , $data = array())
    {
        if ( empty($ipid) || ! is_array($data)) {
            return;
        }
        
        $entity = new PatientAnlage33a();
        return $entity->findOrCreateMultipleBy('ipid', $ipid, $data);
    }
}
?>