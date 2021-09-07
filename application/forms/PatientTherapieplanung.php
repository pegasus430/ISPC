<?php
/**
 * 
 * @author claudiu 
 * Jul 12, 2018
 *
 */
class Application_Form_PatientTherapieplanung extends Pms_Form
{
    
    
    /**
     *
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form
     */
    public function create_form_patient_therapieplanung($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_therapieplanung");
    
    
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('treatmentplanning');
        $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
    
        
//     if(!empty($values)) dd($values);
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        $cbValues = $this->getCbValuesArray();
        
        foreach ($cbValues as $cb => $tr) {
            $subform->addElement('checkbox', $cb, array(
                'checkedValue'    => '1',
                'uncheckedValue'  => '0',
                'label'      => $tr,
                'required'   => false,
                'value' => isset($values[$cb]) ? $values[$cb] : 0,
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                
//                 'onChange' => $cb== 'palliative' ? 'if (this.checked) { $(".show_hide", $(this).parents("table")).show(); $("input[name$=\"\[freetext\]\"]", $(this).parents("table")).val("");} else {$(".show_hide", $(this).parents("table")).hide(); $("input[name$=\"\[freetext\]\"]", $(this).parents("table")).val("");}' : null,        
            ));
        }
        
        
//         $display = $values['palliative'] != 1 ? 'display:none' : null;
        $subform->addElement('text', 'freetext', array(
            'value'        => $values['freetext'],
            'label'        => null,
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide')),
            ),        
        ));
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    public function save_form_patient_therapieplanung($ipid =  '' , $data = array())
    {
        //this is cb
        if(empty($ipid)) {
            return;
        }
    
        $entity = new PatientTherapieplanung();
        
        $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
        
        foreach ($this->getCbValuesArray() as $kv => $tr) {
            $this->_save_box_History($ipid, $newEntity, $kv, 'grow37', 'text');
        }
       
        $this->_save_box_History($ipid, $newEntity, 'freetext', 'grow37', 'text');
        
        
         
        return $newEntity;
        
        
        
    }
    
    
    public function getCbValuesArray()
    {
        return PatientTherapieplanung::getCbValuesArray();
    }
    
    

    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text = null)
    {
    
        $newModifiedValues = $newEntity->getLastModified();
    
        if (isset($newModifiedValues[$fieldname])) {
            $oldValues = $newEntity->getLastModified(true);
    
            $add_sufix = "";
            $remove_sufix = "";
            $added = [];
            $removed = [];
    
            switch ($checkbox_or_radio_or_text) {
    
                case  "checkbox" :
    
                    $new_values = explode(',', $newModifiedValues[$fieldname]);
                    $old_values = explode(',', $oldValues[$fieldname]);
    
                    $added = array_diff($new_values, $old_values);
                    $removed = array_diff($old_values , $new_values);
    
                    $add_sufix = "-1";
                    $remove_sufix = "-0";
    
                    break;
    
                case "radio" :
                case "text" :
                default:
    
                    $new_values = $newModifiedValues[$fieldname];
                    $old_values = $oldValues[$fieldname];
    
                    $added = [$new_values];
    
                    break;
            }
    
            $history = [];
    
            if ( ! empty($added)) {
                foreach ($added as $val) {
                    $history[] = [
                        'ipid' => $ipid,
                        'clientid' => $this->logininfo->clientid,
                        'formid' => $formid,
                        'fieldname' => $fieldname,
                        'fieldvalue' => $val . $add_sufix,
                    ];
                }
            }
    
    
            if ( ! empty($removed)) {
                foreach ($removed as $val) {
                    $history[] = [
                        'ipid' => $ipid,
                        'clientid' => $this->logininfo->clientid,
                        'formid' => $formid,
                        'fieldname' => $fieldname,
                        'fieldvalue' => $val . $remove_sufix,
                    ];
                }
            }
    
            if ( ! empty($history)) {
                $coll = new Doctrine_Collection("BoxHistory");
                $coll->fromArray($history);
                $coll->save();
            }
        }
    
    }
}
?>