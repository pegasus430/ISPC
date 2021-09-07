<?php
class Application_Form_PatientHospiceCertification extends Pms_Form
{
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientHospiceCertification';
    
    
    /**
     * @claudiu 2017.12.08
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_hospice_certification ($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('Hospice need Certification:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    

        $phc_obj = new PatientHospiceCertification();
        $list = $phc_obj->getEnumValuesDefaults();
        
       

        $subform->addElement('radio', 'selected_value', array(
            'value'         => $values['selected_value'],
            'multiOptions'  => $list,
            'required'      => false,
            'decorators'    => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'SimpleTableTd')),
                array(array('row' => 'SimpleTableRow')),
            ),
            'separator' => '</td></tr>'. PHP_EOL.'<tr><td>',
            'onChange' => 'if(this.value==\'other\') {$(this).parents(\'table\').find(\'.comments\').show();} else {$(this).parents(\'table\').find(\'.comments\').hide();}',
        ));

    
        $selected_value = $subform->getElement('selected_value')->getValue();
        $display = $selected_value != 'other' ? 'display:none': "";
        $subform->addElement('textarea', 'comments', array(
            'value'         => $values['selected_value'],
            'required'      => false,
            'decorators'    => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'SimpleTableTd')),
                array(array('row' => 'SimpleTableRow')),
            ),
            'style' => $display,
            'class' => 'comments',
            'rows'  => 3,
            'cols'  => 60,
        ));
        
    
    
        return $subform;
    }
    
    public function save_form_hospice_certification($ipid = '', $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
    
        $entity  = new PatientHospiceCertification();
        return $entity->findOrCreateOneBy('ipid', $ipid , $data);
    
    
    }
    

}
?>