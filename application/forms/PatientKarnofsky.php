<?php

//TODO : move the saved values to DgpKern and then delete all this model PatientKarnofsky

class Application_Form_PatientKarnofsky extends Pms_Form
{
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientKarnofsky';
    
    
    /**
     * @claudiu 2017.12.08
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_karnofsky ($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('ECOG status:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        $karnofsky_values  = Pms_CommonData::get_karnofsky();
        $karnofsky_values_array = array();
        $karnofsky_values_array[''] = $this->translate('please_select');
        
        foreach($karnofsky_values as $k=>$data){
            $karnofsky_values_array[$data['value']] = $data['label'];
        }
         
        $subform->addElement('select', 'karnofsky', array(
            'value'        => $values['karnofsky'],
            'multiOptions' => $karnofsky_values_array,
            'label'        => $this->translate('wound width:'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
         
    
        return $subform;
    }

    
       
    
    public function save_form_karnofsky($ipid = '', $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
        
        //TODO change to save multiple by wlassessment_id
        $entity = new PatientKarnofsky();
        return $entity->findOrCreateOneByIpidAndWlassessmentid( $ipid , $data['wlassessment_id'] , $data);
    
        
    }
    
    
    
}
?>