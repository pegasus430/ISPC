<?php
class Application_Form_PatientCloseContact extends Pms_Form
{
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientCloseContact';
    
    
    public function create_form_close_contact($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Close contact for:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
   
        $obj = new PatientCloseContact();
        $list = $obj->getEnumValuesDefaults();
        
//         $subform->addElement('note', 'note', array(
//             'label'      => null,
//             'required'   => false,
//             'value' => $this->translate('Question from the patient / relatives'),
//             'decorators' =>   array(
//                 'ViewHelper',
//                 array('Errors'),
//                 array(array('data' => 'HtmlTag'), array('tag' => 'td')),
//                 array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
//             ),
//         ));
        
        $subform->addElement('multiCheckbox', 'selected_value', array(
            'label'         => null,
            'required'      => false,
            'multiOptions'  => $list,
            'value'         => $values['selected_value'],
            'decorators'    => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
            'separator' => '</td></tr>'.PHP_EOL.'<tr><td>',
            'onChange' => 'if (this.value == "other" && this.checked) {$(".comments", $(this).parents(\'table\')).show();} else if(this.value == "other") {$(".comments", $(this).parents(\'table\')).hide().val(\'\');}',
            
        ));
        
        $display = ! in_array('other', $values['selected_value']) ? "display:none" : "";
        
        $subform->addElement('textarea', 'comment', array(
            'label'         => null,
            'required'      => false,
            'value'         => $values['comment'],
            'decorators'    => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'comments', 'style' => $display)),
            ),
            'rows'      => 3,
            'cols'      => 60,
        ));
        
        return $subform;
    }
    
    
    public function save_form_close_contact($ipid = '', $data = array())
    {        
        if (empty($ipid)) {
            return;
        }

        $entity  = new PatientCloseContact();
        $records = $entity->findByIpid($ipid, Doctrine_Core::HYDRATE_RECORD);
        
        if (empty($data)) {
            //all checkboxes must be empty .. delete all that was saved
            if ($records->count()) {
                $records->delete();
            }
        }
        
        $sync_values = array();

        $selected_value = $data['selected_value'];
      
        $old_values =  array();

        if ($records->count()) {
            //we have some, update them
            
            foreach ($records->getIterator() as $row) {
                if ( ! in_array($row->selected_value, $selected_value)) { 
                    //this was deleted
                    $row->wlassessment_id = $data['wlassessment_id'];
                    $row->delete();
                    $sync_values[] = $row->toArray();
                } else {
                    
                    
                    //value was found, no need to re-insert
                    if($row->selected_value == 'other') {
                        $row->comment = $data['comment'];
                    }
                    $sync_values[] = $row->toArray();
                    $old_values[] = $row->selected_value;
                }
                
            }
        } else {
            $records =  new Doctrine_Collection('PatientCloseContact');
        }
        
        foreach (array_diff($selected_value, $old_values) as $new) {
            //this are brand new, must pe inserted
            $sync_values[] = array(
                'ipid'              => $ipid,
                'selected_value'    => $new,
                'comment'           => $new == 'other' ? $data['comment'] : null,
            );   
        }

        if( ! empty ($sync_values)) {
            
            $records->synchronizeWithArray($sync_values);
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