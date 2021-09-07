<?php
class Application_Form_PatientSpiritualAttitude extends Pms_Form
{
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientSpiritualAttitude';
    
    
    
    public function create_form_spiritual_attitude($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Assessment of the spiritual attitude:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $obj = new PatientSpiritualAttitude();
        $list = $obj->getEnumValuesDefaults();
        
        $subform->addElement('note', 'note', array(
            'label'      => null,
            'required'   => false,
            'value' => $this->translate('Pat wants spiritual guidance'),
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('multiCheckbox', 'selected_value', array(
            'label'      => null,
            'required'   => false,
            'multiOptions'=> $list,
            'value' => $values['selected_value'],
            'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
            'separator'  => '</td></tr>'.PHP_EOL.'<tr><td>',
        ));
    
        return $subform;
    }
    
    
    public function save_form_spiritual_attitude($ipid = '', $data = array())
    {        
        if (empty($ipid)) {
            return;
        }
        
        
        $entity  = new PatientSpiritualAttitude();
        $records = $entity->findByIpid($ipid, Doctrine_Core::HYDRATE_RECORD);
        
        if (empty($data)) {
            //all checkboxes must be empty .. delete all that was saved
            if ($records->count()) {
                $records->delete();
            }
        }
        
        $sync_values = array();
           
        foreach ($data as $colum => $values) {
            
            if ( ! is_array($values)) continue;
            $old_values =  array();
            
            if ($records->count()) {
                //we have some, update them
                foreach ($records->getIterator() as $row) {
                    if ( ! in_array($row->$colum, $values)) { 
                        //this was deleted
                        $row->wlassessment_id = $data['wlassessment_id'];
                        $row->delete();
                        $sync_values[] = $row->toArray();
                    } else {
                        //value was found, no need to re-insert
                        $sync_values[] = $row->toArray();
                        $old_values[] = $row->$colum;
                    }
                }
            }  else {
                $records =  new Doctrine_Collection('PatientSpiritualAttitude');
            }
            
            foreach (array_diff($values, $old_values) as $new) {
                //this are brand new, must pe inserted
                $sync_values[] = array(
                    'ipid'  => $ipid,
                    $colum  => $new,
                    'wlassessment_id' => $data['wlassessment_id'],
                );   
            }
            
           $records->synchronizeWithArray($sync_values);
           
        }
        
        if( ! empty ($sync_values)) {           
            $records->save();
        }
        
        return $records;
    }
    
    //TODO
//     private function _saveBoxHistory($data = array())
//     {
//         //save box history
//         $history = new BoxHistory();
//         $history->ipid = $ipid;
//         $history->clientid = $clientid;
//         $history->fieldname = $_GET['fldname'];
//         $history->fieldvalue = $_GET['chkval'];
//         $history->formid = $_GET['formid'] ;
//         $history->save();
        
//         $this->_helper->json->sendJson(array(
//             'msg'		=> "Success",
//             'formid'	=> $_GET['formid'],
        
//         ));
//     }
    
}
?>