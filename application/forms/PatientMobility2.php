<?php
class Application_Form_PatientMobility2 extends Pms_Form
{
//     public function getVersorgerExtract() {
//         return array(); // this is inlineEdit form via ajax
//     }
    
//     public function getVersorgerAddress()
//     {
//         return array(); // this is inlineEdit form via ajax
//     }
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientMobility2';
    


    public function create_form_mobility2($values =  array() , $elementsBelongTo = null)
    {
        
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_mobility2");
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Mobility II:'));
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $mobilitys = PatientMobility2::getEnumValuesDefaults();
        
        $subform->addElement('multiCheckbox', 'selected_value', array(
            'label'      => null,
            'required'   => false,
            'multiOptions'=> $mobilitys,
            'value' => $values['selected_value'],
            'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
            'separator'  => ' ',//'&nbsp;',
        ));
    
        return $this->filter_by_block_name($subform, __FUNCTION__);
    }
    
    
    public function save_form_mobility2($ipid = '', $data = array())
    {        
        if (empty($ipid)) {
            return;
        }
        $entity  = new PatientMobility2();
        $records = $entity->findByIpid($ipid, Doctrine_Core::HYDRATE_RECORD);
        
        $formid = "grow55";
        
        $saveData = [];
        $old_values = [];
        
        if ( ! $records->count()) {
            //nothing saved, add just our own
            foreach ($data['selected_value'] as $key => $value) {
                $saveData [$key] = $data;
                $saveData [$key] ['selected_value'] = $value;
                $saveData [$key] ['ipid'] = $ipid;   
            }
            
        } else {
            
            //delete what is not sent
            foreach ($records->getIterator() as $row) {
                if ( ! in_array($row->selected_value, $data['selected_value'])) {  
                    $row->delete();
                    
                    $this->_save_box_History($ipid, $row, 'selected_value', $formid, 'text');
                     
                    return $newEntity;
                }
                $old_values[] = $row->selected_value;
            }
            $records->save(); //save the delete
            
            foreach ($data['selected_value'] as $key => $value) {
            
                if ( ! in_array($value, $old_values)) {
                    //this is new value
                    $saveData [$key] = $data;
                    $saveData [$key] ['selected_value'] = $value;
                    $saveData [$key] ['ipid'] = $ipid;
                }
            
            }
        }
        
        if ( ! empty($saveData)) {
            $records = new Doctrine_Collection('PatientMobility2');
            $records->fromArray($saveData);
            $records->save();
            
            foreach ($records->getIterator() as $row) {
                $this->_save_box_History($ipid, $row, 'selected_value', $formid, 'text');
            }
        }
        
        return $records;
        
    }

    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text = null)
    {
    
        $newModifiedValues = $newEntity->getLastModified();
    
        $history = [];
        
        if (isset($newModifiedValues['isdelete']) && $newModifiedValues['isdelete'] == 1) {
            //this is delete
            $history[] = [
                'ipid' => $ipid,
                'clientid' => $this->logininfo->clientid,
                'formid' => $formid,
                'fieldname' => $newEntity->{$fieldname},
                'fieldvalue' => 0,
            ];
            
        } else {
            $history[] = [
                //this is insert
                'ipid' => $ipid,
                'clientid' => $this->logininfo->clientid,
                'formid' => $formid,
                'fieldname' => $newEntity->{$fieldname},
                'fieldvalue' => 1,
                ];
        }
        
        if ( ! empty($history)) {
            $coll = new Doctrine_Collection("BoxHistory");
            $coll->fromArray($history);
            $coll->save();
        }
    
    }
}
?>