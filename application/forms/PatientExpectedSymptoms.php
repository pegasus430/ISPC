<?php
class Application_Form_PatientExpectedSymptoms extends Pms_Form
{
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientExpectedSymptoms';
    
    
    /**
     * @claudiu 2017.12.08
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_expected_symptoms ($values =  array() , $elementsBelongTo = null)
    {
    
        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('Expected Symptoms / Problems / Difficulties:'));
        $subform->setAttrib("class", "label_same_size_auto");
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    

        $pes_obj = new PatientExpectedSymptoms();
        $list = $pes_obj->getEnumValuesDefaults();      

        $subform->addElement('multiCheckbox', 'selected_value', array(
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
            //radio on change
//            'onChange' => 'if(this.value==\'other\') {$(this).parents(\'table\').find(\'.comments\').show();} else {$(this).parents(\'table\').find(\'.comments\').hide();}',
            'onChange' => 'if (this.value == "other" && this.checked) {$(".comments", $(this).parents(\'table\')).show();} else if(this.value == "other") {$(".comments", $(this).parents(\'table\')).hide().val(\'\');}',
        ));

        
        
    
        $selected_value = $subform->getElement('selected_value')->getValue();
        $display = in_array('other', $selected_value) ? '' : 'display:none';
        
        
        
        $subform->addElement('textarea', 'comment', array(
            'value'         => $values['comment'],
            'required'      => false,
            'filter'        => 'StringTrim',
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

    
       
    
//     public function save_form_expected_symptoms($ipid = '', $data = array())
//     {
//         if (empty($ipid) || empty($data)) {
//             return;
//         }
        
//         $entity  = new PatientExpectedSymptoms();
//         return $entity->findOrCreateOneBy('ipid', $ipid , $data);
    
        
//     }
    
    
    
    public function save_form_expected_symptoms($ipid = '', $data = array())
    {
        
        if (empty($ipid)) {
            return;
        }
    
        
        $model = 'PatientExpectedSymptoms';
        
        $entity  = new $model();
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
                        $update_row = $row->toArray();
                        $update_row['comment'] = $data['comment'];
                        $sync_values[] = $update_row;
                        $old_values[] = $row->$colum;
                    }
                }
            }  else {
                $records =  new Doctrine_Collection($model);
            }
    
            foreach (array_diff($values, $old_values) as $new) {
                //this are brand new, must pe inserted
                $sync_values[] = array(
                    'ipid'  => $ipid,
                    $colum  => $new,
                    'wlassessment_id' => $data['wlassessment_id'],
                    'comment' => $data['comment'],
                );
            }
    
            $records->synchronizeWithArray($sync_values);
             
        }
        
    
        if( ! empty ($sync_values)) {
            $records->save();
        }
    
        return $records;
    }
    
    
}
?>