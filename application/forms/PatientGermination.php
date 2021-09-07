<?php

class Application_Form_PatientGermination extends Pms_Form
{

	public function InsertData($post)
	{
		$frm = new PatientGermination();
		$frm->ipid = $post['ipid'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientGermination')
		->execute();
	}
	
	
	
	



	/**
	 * @cla on 11.07.2018
	 * Versorgung = PatientMobility
	 *
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_germination($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_germination");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('germination');
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
	
// 	     if (!empty($values))dd($values,  $values['germination_cbox']);
	     
	    // TODO-1890:: Ancuta 20.11.2018
	    $subform->addElement('checkbox', 'germination_cbox', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'germination_cbox_label',
	        'required'   => false,
	        'value' => $values['germination_cbox'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),//TODO-3848 Ancuta 19.02.2021 - remove "closeOnly" => true
	        ),
	        'onChange' => 'if (this.checked) { $(".germination_options", $(this).parents("table")).show();} else {$(".germination_options", $(this).parents("table")).hide();}',
	    ));
	    

	    $display = ($values['germination_cbox'] != 1 ? 'display:none' : null);
	    
	    $subform->addElement('text', 'germination_text', array(
	        'label'      => $this->translate('germination_text'),
	        'value' => $values['germination_text'] ,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),//TODO-3848 Ancuta 19.02.2021 - remove "closeOnly" => true
	        ),
	        'style'        => $display,
	        'class'        => 'comments germination_options',
	    ));
	     
	    $subform->addElement('checkbox', 'iso_cbox', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'iso_cbox_label',
	        'required'   => false,
	        'value' => $values['iso_cbox'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND', 'class'=>'germination_options','style'=> $display)),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),//TODO-3848 Ancuta 19.02.2021 - remove "closeOnly" => true
	        ),
	        'style'        => $display,
	        'class'        => 'germination_options',
	    ));
	     
	
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	public function save_form_patient_germination($ipid =  '' , $data = array())
	{
	    //this is cb+text
	    if(empty($ipid)) {
	        return;
	    }
	
	    $entity = new PatientGermination();
	    
	    //ISPC-2807 Lore 24.02.2021
	    $this->save_form_patient_germination_toVerlauf($ipid, $data);
	    //.
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    $this->_save_box_History($ipid, $newEntity, 'iso_cbox', 'grow52', 'text');
	    $this->_save_box_History($ipid, $newEntity, 'germination_cbox', 'grow52', 'text');
	    $this->_save_box_History($ipid, $newEntity, 'germination_text', 'grow52', 'text');
	     
	    return $newEntity;
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
	
	//ISPC-2807 Lore 25.02.2021
	public function save_form_patient_germination_toVerlauf($ipid, $data)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	    
	    $model = new PatientGermination();
	    $olddatesarr = $model->getPatientGermination($ipid);
	    
	    $box_name = $this->translate("[PatientGermination Box Name]");
	    
	    $course_title = '';
	    if($data['germination_cbox'] == '1' && $data['germination_cbox'] != $olddatesarr['germination_cbox'] ){
	        $text_box = !empty($data['germination_text']) ? ' ('.$data['germination_text'].')' : "";
	        $course_title .= "Der ".$box_name." wurde geändert: Keimbesiedelung". $text_box ."\n\r" ;
	    }
	    if($data['iso_cbox'] == '1' && $data['iso_cbox'] != $olddatesarr['iso_cbox']){
	        $course_title .= "Der ".$box_name." wurde geändert: Isolationspflichtig". "\n\r" ;
	    }
	    
	    $recordid = $olddatesarr[$ipid]['id'];
	    if(!empty($course_title)){                 //TODO-3930 Lore 08.03.2021
	        $insert_pc = new PatientCourse();
	        $insert_pc->ipid =  $ipid;
	        $insert_pc->course_date = date("Y-m-d H:i:s", time());
	        $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
	        $insert_pc->recordid = $recordid;
	        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
	        $insert_pc->user_id = $userid;
	        $insert_pc->save();
	    }

	    
	    
	}
	
	
}
?>