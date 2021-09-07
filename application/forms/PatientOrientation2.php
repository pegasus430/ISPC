<?php
class Application_Form_PatientOrientation2 extends Pms_Form
{
    
    //public function getVersorgerExtract() {
      //  return array(); // this is inlineEdit form via ajax
    //}
    
    //public function getVersorgerAddress()
    //{
      //  return array(); // this is inlineEdit form via ajax
    //}
    
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientOrientation2';
    
    
    /**
     * @claudiu 2017.12.08
     * @cla update 10.07.2018
     * 
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_orientation2 ($values =  array() , $elementsBelongTo = null)
    {
        
//         $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_orientation2");
         
       
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend($this->translate('Orientation II:'));
        $subform->setAttrib("class", "label_same_size_180 multipleCheckboxes inlineEdit " . __FUNCTION__);
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        $orientations = PatientOrientation::getDefaultOrientation();
        $communications = PatientOrientation::getDefaultCommunicationRestricted();
        
        
//         if (! empty($values)) { dd($values);}
        
        $subform->addElement('multiCheckbox', 'orientation', array(
            'label'      => null,
            'required'   => false,
            'multiOptions'=> $orientations,
            'value' => $values['orientation'],
            'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
            'separator'  => ' ', //&nbsp;',
//             'onclick' => 'if (this.value == "communication restricted") {$(".group_communication", $(this).parents("table")).show();} else { $(".group_communication", $(this).parents("table")).hide().find("input").each(function() { $(this).prop("checked", false); });}',
            'onChange' => 'if (this.value == "communication restricted") { if (this.checked) {$(".group_communication", $(this).parents("table")).show();} else { $(".group_communication", $(this).parents("table")).hide().find("input").each(function() { $(this).prop("checked", false); });}}',
        
        ));
        
        
        
        $display = ! in_array('communication restricted', $values['orientation']) ? 'display:none' : null;
        
        $subCommunication = $subform->createElement('multiCheckbox', 'orientation', array(
            'label'      => null,
            'separator'  => ' ', //'&nbsp;',
            'required'   => false,
            'multiOptions'=> $communications,
            'value' => $values['orientation'],
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'group_communication', 'style'=> $display)),
            ),
        ));
        $subform->addElement($subCommunication, 'communication');
        
    
    
        return $subform;
    }


    
    
//     public function save_form_orientation2($ipid =  '' , $data = array())
//     {
//         //radio + cb
//         if(empty($ipid) || empty($data)) {
//             return;
//         }
        
//         $result = array();
//         $entity = new PatientOrientation();
         
//         if( ! is_array($data['orientation'])) {
//             $data['orientation'] = array($data['orientation']);
//         }
//         if( ! is_array($data['communication'])) {
//             $data['communication'] = array($data['communication']);
//         }
        
//         $data['orientation'] = array_filter(array_merge($data['orientation'], $data['communication']));
      
        
//         //delete all previous
//         $q =  $entity->getTable()->createQuery()
//         ->delete()
//         ->where('ipid = ?', $ipid)
//         ->andWhere('isdelete = 0')
//         ->andWhereNotIn('orientation' , $data['orientation'])
//         ->execute();
    
//         //insert/update one by one
//         foreach ($data['orientation'] as $orientation) {
//             $row =  $data;
//             $row['orientation'] = $orientation;
//             $result[] = $entity->findOrCreateOneByIpidAndOrientation($ipid, $orientation, $row);
             
//         }
        
//         return $result;
    
//     }
    
    
    
    public function save_form_orientation2($ipid = '', $data = array())
    {
        //cb
        if (empty($ipid)) {
            return;
        }

        $formid = 'grow54';
        
        $entity  = new PatientOrientation();
        $records = $entity->findByIpid($ipid, Doctrine_Core::HYDRATE_RECORD);
    
        $saveData = [];
        $old_values = [];
    
        if ( ! $records->count()) {
            
            //nothing saved, add just our own
            foreach ($data['orientation'] as $key => $value) {
                $saveData [$key] = $data;
                $saveData [$key] ['orientation'] = $value;
                $saveData [$key] ['ipid'] = $ipid;
            }
    
        } else {
    
            //delete what is not sent
            foreach ($records->getIterator() as $row) {
                if ( ! in_array($row->orientation, $data['orientation'])) {
//                 dd($row->toArray());
                    $row->delete(); // this is hard-delete... softDelete not here
                    
                    $this->_save_box_History($ipid, $row, 'orientation', $formid, "delete");
                    
                }
                $old_values[] = $row->orientation;
            }
            //$records->save(); //save the delete
    
            foreach ($data['orientation'] as $key => $value) {
    
                if ( ! in_array($value, $old_values)) {
                    //this is new value
                    $saveData [$key] = $data;
                    $saveData [$key] ['orientation'] = $value;
                    $saveData [$key] ['ipid'] = $ipid;                    
                }
    
            }
        }
    
        if ( ! empty($saveData)) {
            $records = new Doctrine_Collection('PatientOrientation');
            $records->fromArray($saveData);
            $records->save();
            
            foreach ($records->getIterator() as $row) {
                $this->_save_box_History($ipid, $row, 'orientation', $formid, 'insert');
            }
            
        }
    
        return $records;
    
    }
    
    
    
    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $step = null)
    {
    
        $newModifiedValues = $newEntity->getLastModified();
    
        $history = [];
    
        
        if ($step == 'delete') {
            $history[] = [
                'ipid' => $ipid,
                'clientid' => $this->logininfo->clientid,
                'formid' => $formid,
                'fieldname' => $newEntity->{$fieldname},
                'fieldvalue' => 0,
                ];
    
        } elseif ($step == 'insert') {
            $history[] = [
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