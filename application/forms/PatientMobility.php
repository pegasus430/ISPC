<?php
class Application_Form_PatientMobility extends Pms_Form
{

	public function InsertData($post)
	{

		$frm = new PatientMobility();
		$frm->ipid = $post['ipid'];
		$frm->bed = $post['bed'];
		$frm->walker = $post['walker'];
		$frm->wheelchair = $post['wheelchair'];
		$frm->goable = $post['goable'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientMobility')
		->set('bed', "'".$post['bed']."'")
		->set('walker', "'".$post['walker']."'")
		->set('wheelchair', "'".$post['wheelchair']."'")
		->set('goable', "'".$post['goable']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();
	}

	
	

	/**
	 * @cla on 11.07.2018
	 * Versorgung = PatientMobility
	 *
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_mobility($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_mobility");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('mobility');
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
	    
	    $subform->addElement('checkbox', 'bed', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'bed',
	        'required'   => false,
	        'value' => $values['bed'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".bedmore", $(this).parents("table")).show();} else {$(".bedmore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['bed'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'bedmore', array(
	        'label'        => null,
	        'value'        => $values['bedmore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'bedmore comments', 'style' => $display )),
	        ),
	    
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'walker', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'walker',
	        'value' => $values['walker'] ,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".walkermore", $(this).parents("table")).show();} else {$(".walkermore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['walker'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'walkermore', array(
	        'label'        => null,
	        'value'        => $values['walkermore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'walkermore comments', 'style' => $display )),
	        ),
	         
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'wheelchair', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'wheelchair',
	        'value' => $values['wheelchair'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".wheelchairmore", $(this).parents("table")).show();} else {$(".wheelchairmore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['wheelchair'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'wheelchairmore', array(
	        'label'        => null,
	        'value'        => $values['wheelchairmore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'wheelchairmore comments', 'style' => $display )),
	        ),
	         
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'goable', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'goable',
	        'value' => $values['goable'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".goablemore", $(this).parents("table")).show();} else {$(".goablemore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['goable'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'goablemore', array(
	        'label'        => null,
	        'value'        => $values['goablemore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'goablemore comments', 'style' => $display )),
	        ),
	    
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'nachtstuhl', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'nachtstuhl',
	        'value' => $values['nachtstuhl'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".nachtstuhlmore", $(this).parents("table")).show();} else {$(".nachtstuhlmore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['nachtstuhl'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'nachtstuhlmore', array(
	        'label'        => null,
	        'value'        => $values['nachtstuhlmore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'nachtstuhlmore comments', 'style' => $display )),
	        ),
	         
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'wechseldruckmatraze', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'wechseldruckmatraze',
	        'value' => $values['wechseldruckmatraze'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange' => 'if (this.checked) { $(".wechseldruckmatrazemore", $(this).parents("table")).show();} else {$(".wechseldruckmatrazemore", $(this).parents("table")).hide();}',
	         
	    ));
	    $display = $values['wechseldruckmatraze'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'wechseldruckmatrazemore', array(
	        'label'        => null,
	        'value'        => $values['wechseldruckmatrazemore'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'placeholder'  => $this->translate('freetext'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'wechseldruckmatrazemore comments', 'style' => $display )),
	        ),
	    
	    ));
	     
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	public function save_form_patient_mobility($ipid =  '' , $data = array())
	{
	    //this is cb + text
	    if(empty($ipid)) {
	        return;
	    }
	
	    $entity = new PatientMobility();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    
	    foreach ($this->getCbValuesArray() as $kv => $tr) {
	        $this->_save_box_History($ipid, $newEntity, $kv, 'grow5', 'text');
	        $this->_save_box_History($ipid, $newEntity, "{$kv}more", 'grow5', 'text');
	    }
	    
	    return $newEntity;
	}
	
	
	public function getCbValuesArray()
	{
	    return [
	        'bed' => $this->translate('bed'),
	        'walker' => $this->translate('walker'),
	        'wheelchair' => $this->translate('wheelchair'),
	        'goable' => $this->translate('goable'),
	        'nachtstuhl' => $this->translate('nachtstuhl'),
	        'wechseldruckmatraze' => $this->translate('wechseldruckmatraze'),
	         
	    ];
	}
	
	
	

	private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text)
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