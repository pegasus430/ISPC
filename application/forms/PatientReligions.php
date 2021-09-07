<?php

class Application_Form_PatientReligions extends Pms_Form
{

    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientReligions';
    
    
    
    
	public function InsertData($post)
	{
		$frm = new PatientReligions();
		$frm->ipid = $post['ipid'];
		$frm->religion = $post['religion'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientLives')
		->set('alone', "'".$post['alone']."'")
		->set('house_of_relatives', "'".$post['house_of_relatives']."'")
		->set('apartment', "'".$post['apartment']."'")
		->set('home', "'".$post['home']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();
	}
	
	
	
	public function create_form_religion($values =  array() , $elementsBelongTo = null)
	{
	    
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	     
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_religion");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Religion:'));
	    $subform->setAttrib("class", "label_same_size multipleCheckboxes inlineEdit " . __FUNCTION__);
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $religions = PatientReligions::getReligionsNames();
	
	    $subform->addElement('radio', 'religion', array(
	        'label'      => null,//$this->translate('enable/disable module'),
	        'separator'  => ' ', //'&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $religions,
	        'value' => $values['religion'],
	         
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.value != "7") {$(".religionfreetext", $(this).parents(\'table\')).hide().val(\'\');} else if (this.value == "7") {$(".religionfreetext", $(this).parents(\'table\')).show();}',
	    ));
	    
	    $display = ($values['religion'] != 7 ? 'display:none' : null);
	    
	    $subform->addElement('text', 'religionfreetext', array(
	        'label'        => "",
	        'value'        => $values['religionfreetext'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('religionfreetext'),
	        'decorators'   =>   array(
	            'ViewHelper',
	            array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'style'        => $display,
	        'class'        => 'comments  religionfreetext',
	    ));
	
	    return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	public function save_form_religion($ipid =  '' , $data = array())
	{
	    //radio
	    if(empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $entity = new PatientReligions();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    $this->_save_box_History($ipid, $newEntity, 'religion', 'grow8', 'radio');
	     
	    return $newEntity;
	     
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