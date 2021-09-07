<?php
class Application_Form_PatientEmploymentsituation extends Pms_Form
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

    private $_date_format_datepicked = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
    private $_date_format_db = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY;
    
    
    //define this if you grouped the translations into an array for this form
//     protected $_translate_lang_array = 'Form_PatientOrientation2';
    
    
    /**
     * @claudiu 2018.09.05
     * 
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_employment_situation ($values =  array() , $elementsBelongTo = null)
    {
        
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_employment_situation");
         
       
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        $subform->setLegend('employment situation');
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
        
        $statuss = PatientEmploymentSituation::getStatusValuesArray();
        
//         if (! empty($values)) { dd($values);}
        
        $subform->addElement('multiCheckbox', 'status', array(
            'label'      => null,
            'required'   => false,
            'multiOptions'=> $statuss,
            'value' => $values['status'],
            'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
            'separator'  => ' ', //&nbsp;',

            'onChange' => 'if (this.checked) {$(".group_since_when", $(this).parents("table")).show();} else if ( ! $("input:checkbox:checked", $(this).parents("table")).length) { $(".group_since_when", $(this).parents("table")).hide().find("input").each(function() { $(this).val(""); });}',
        
        ));
        
        
        $display = empty($values['status']) ? 'display:none' : null;
        
        $subform->addElement('text',  'since_when', array(
            'value'        => empty($values['since_when']) || $values['since_when'] == "0000-00-00" || $values['since_when'] == "1970-01-01" ? "" : date('d.m.Y', strtotime($values['since_when'])),
        
            'label'        => 'since when',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
            	array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'style' => $display, 'class' => "group_since_when")),
            ),
            'class' => 'date allow_future',
            'data-mask' => "99.99.9999",
            'data-altfield' => 'since_when',
            'data-altformat' => 'yy-mm-dd',
             
             
        ));
        	
        $subform->addElement('note',  'supplementary_services_note', array(
            'value'        =>  $this->translate('supplementary services'),
            'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'class'=>'note')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('textarea',  'supplementary_services', array(
            'value'        =>  $values['supplementary_services'],
            'label'        => null,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),

        	'rows'         => 3,
        	'cols'         => 'auto',
        	'class'        => 'elastic comments',
        ));
        
        
        $subform->addElement('note',  'comments_note', array(
            'value'        =>  $this->translate('comments'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'class'=>'note')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('textarea',  'comments', array(
            'value'        =>  $values['comments'],
            'label'        => null,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),

        	'rows'         => 3,
        	'cols'         => 'auto',
        	'class'        => 'elastic comments',
        ));
        
        
        
        
        
        
        
        
        
        
        

        
//         status => object
//         since_when => date , Seit wann: , if checkbox
//         supplementary_services => text ,  ergänzende Leistungen
//         comments=> text, Kommenare
        
    
        return $this->filter_by_block_name($subform, __FUNCTION__);
    }
    
    public function save_form_patient_employment_situation($ipid = '', $data = array())
    {
        //cb
        if (empty($ipid)) {
            return;
        }
        
        
        if ( ! empty($data['since_when']) && Zend_Date::isDate($data['since_when'], $this->_date_format_datepicked)) {
            $date = new Zend_Date($data['since_when'],  $this->_date_format_datepicked);
            $data['since_when']  = $date->toString($this->_date_format_db);
        } else {
        	$data['since_when'] = null;
        }
        
        if ( ! isset($data['status'])) {
            $data['status'] = null;
            $data['since_when'] = null;
        }

        $formid = 'grow56';

        $entity  = new PatientEmploymentSituation();
        
        $entityObj = $entity->findOrCreateOneBy('ipid', $ipid, $data);
        
        
        $this->_save_box_History($ipid, $entityObj, 'status', $formid, 'checkbox');        
        $this->_save_box_History($ipid, $entityObj, 'since_when', $formid, 'text');
        $this->_save_box_History($ipid, $entityObj, 'supplementary_services', $formid, 'text');
        $this->_save_box_History($ipid, $entityObj, 'comments', $formid, 'text');
        
       
    
        return $entityObj;
    
    }
    
    
    private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text)
    {
    
        $newModifiedValues = $newEntity->getLastModified();
        $oldValues = $newEntity->getLastModified(true);
    
        if (isset($newModifiedValues[$fieldname]) || isset ($oldValues[$fieldname])) {
    
            $add_sufix = "";
            $remove_sufix = "";
            $added = [];
            $removed = [];
    
            switch ($checkbox_or_radio_or_text) {
    
                case  "checkbox" :
                    
                    $new_values = isset($newModifiedValues[$fieldname]) ? $newModifiedValues[$fieldname] : [];
                    $old_values = isset($oldValues[$fieldname]) ? $oldValues[$fieldname] : [];
                    
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