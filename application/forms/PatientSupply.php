<?php

class Application_Form_PatientSupply extends Pms_Form
{


	public function InsertData($post)
	{
		$frm = new PatientSupply();
		$frm->ipid = $post['ipid'];
		$frm->even = $post['even'];
		$frm->spouse = $post['spouse'];
		$frm->member = $post['member'];
		$frm->private_support = $post['private_support'];
		$frm->nursing = $post['nursing'];
		$frm->heimpersonal = $post['heimpersonal'];
		$frm->save();
	}


	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientSupply')
		->set('even', "'".$post['even']."'")
		->set('spouse', "'".$post['spouse']."'")
		->set('member', "'".$post['member']."'")
		->set('private_support', "'".$post['private_support']."'")
		->set('nursing', "'".$post['nursing']."'")
		->set('heimpersonal', "'".$post['heimpersonal']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();
	}
	
	
	
	/**
	 * @cla on 10.07.2018
	 * Versorgung = PatientSupply
	 * 
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_supply($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_supply");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('supply');
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
	
// 	    if (!empty($values)) dd($values);
	    $cbValues = $this->getCbValuesArray();
	    
	    foreach ($cbValues as $key => $lang) {

    	    $subform->addElement('checkbox', $key, array(
    	        'checkedValue'    => '1',
    	        'uncheckedValue'  => '0',
    	        'label'      => $lang,
    	        'required'   => false,
    	        'value' => $values[$key],
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    
    	    ));
	    }
	    
// 	    $subform->addElement('multiCheckbox', 'values', array(
// 	        'label'      => null,
// 	        'separator'  => " ", //'&nbsp;',
// 	        'required'   => false,
// 	        'multiOptions'=> $cbValues,
// 	        'value' => $values,
// 	        'decorators'   => array(
// 	            'ViewHelper',
// 	            array('Errors'),
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
// 	        ),
// 	    ));
	    
	    
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	public function save_form_patient_supply($ipid =  '' , $data = array())
	{
	    //this is cb
	    if(empty($ipid)) {
	        return;
	    }
	
	    $entity = new PatientSupply();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    foreach($this->getCbValuesArray() as $kv => $tr) {
	        $this->_save_box_History($ipid, $newEntity, $kv, 'grow4', 'checkbox');
	    }
	     
	    return $newEntity;
	}
	
	
	public function getCbValuesArray()
	{
	    return PatientSupply::getCbValuesArray();
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