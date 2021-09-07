<?php
class Application_Form_PatientHospizverein extends Pms_Form{
    
    
    public function getVersorgerExtract() {
        return array(
    
            array( "label" => $this->translate('Hospizverein verständigen'), "cols" => array("hospizverein")),
            array( "label" => null, "cols" => array("hospizverein_txt")),
        );
    }
    
    
    
	public function InsertData($post)
	{

		$frm = new PatientHospizverein();
		$frm->ipid = $post['ipid'];
		$frm->hospizverein = $post['hospizverein'];
		$frm->hospizverein_txt = $post['hospizverein_txt'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientHospizverein')
		->set('hospizverein', "'".$post['hospizverein']."'")
		->set('hospizverein_txt', "'".$post['hospizverein_txt']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();
	}
	

	/**
	 * this form is used in WLAssessment ... and is different that the one in patient detail (create_form_patient_hospizverein)
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_hospice_association ($values =  array() , $elementsBelongTo = null)
	{
	
	    $subform = $this->subFormTable();
	    $subform->setLegend($this->translate('Outpatient hospice service:'));
	    $subform->setAttrib("class", "label_same_size_auto");
	        
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }

	    
	    $el = $this->createElement('radio', 'hospizverein', array(
	        'multiOptions'  => array(1 => 'schon eingebunden'),
	        'value'         => $values['hospizverein'],
	        'required'      => false,
	        'decorators'    => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true )),    
	        ),	
// 	        'isArray' => true,
            'onChange' => 'if (this.value==1) {$("input:radio.necessity_radio", $(this).parents(\'table\')).attr("checked", false);  $(".necessity_label", $(this).parents(\'table\')).hide(); $("textarea.comments", $(this).parents("table")).show();};'

	    ));
	    $subform->addElement($el, 'yes');
	    
	    $display = $values['hospizverein'] == 1 ? '' : 'display:none';
	    $subform->addElement('textarea', 'hospizverein_txt', array(
	        'value'        => $values['hospizverein_txt'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )), 
	        ),
	        'style' => $display,
	        'class' => 'comments',
	        'cols'  => 40,
	        'rows'  => 3,
	    ));
	    
	    $el = $this->createElement('radio', 'hospizverein', array(
	        'multiOptions'  => array(2=>'Notwendigkeit'),
	        'value'         => $values['hospizverein'],
	        'required'      => false,
	        'decorators'    => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true )),
	        ),
// 	        'belongsTo' => 'hospizverein',
// 	        'isArray' => true,
	        'onChange' => 'if (this.value==2) {$("textarea.comments", $(this).parents("table")).val("").hide(); $(".necessity_label", $(this).parents(\'table\')).show();};'
	    ));
	    $subform->addElement($el, 'no');
	    
	     
	    $display = $values['hospizverein'] == 2 ? '' : 'display:none';
	    $subform->addElement('radio', 'necessity', array(
	        'value'        => $values['necessity'],
	        'multiOptions' => array('yes'=> 'ja' ,'no' => 'nein'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	        ),
	        'class'        => 'necessity_radio',
	        'labelClass'   => 'necessity_label',
	        'labelStyle'   => $display,
	        'separator'    => '&nbsp;',
	    ));
	
	    return $subform;
	}
	

	
	public function save_form_hospice_association($ipid = '', $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	 
	    $entity  = new PatientHospizverein();
	    return $entity->findOrCreateOneBy('ipid', $ipid , $data);
	
	}
	
	/**
	 * @see create_form_hospice_association
	 * 
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_hospizverein($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_hospizverein");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('Hospizverein');
	    $subform->setAttrib("class", "" . __FUNCTION__);
	
	
// 	        if(!empty($values)) dd($values);
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    
	    if ( $this->logininfo->clientid == 48) {
	        
	        //this IF was hardcoded in PatientController.php -> $displayhospizverein_special = 1
	        
	        $subform->addElement('note', 'labelTop', array(
    	        'value'        => $this->translate('Hospizverein verständigen?'),
    	        'required'     => false,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
	        ));
	        
	        $subform->addElement('radio', 'hospizverein', array(
	            'multiOptions'  => array(1 => 'Ja', 2 => 'Nein'),
	            'value'         => $values['hospizverein'],
	            'required'      => false,
	            'decorators'    => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr',  )),
	            ),
	            // 	        'belongsTo' => 'hospizverein',
	            // 	        'isArray' => true,
	        ));
	        
	        $subform->addElement('note', 'labelBottom', array(
	            'value'        => $this->translate('anderer Hospizdienst bereits aktiv?'),
	            'required'     => false,
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	    }
	    
	    
	    $subform->addElement('textarea', 'hospizverein_txt', array(
	        'value'        => $values['hospizverein_txt'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	        ),
	        'rows' => 5,
	    ));
	
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	}
	
	public function save_form_patient_hospizverein($ipid = '', $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    	
	    $entity  = new PatientHospizverein();
	    
	    $result = $entity->findOrCreateOneBy('ipid', $ipid , $data);
	    
	    if (isset($data['hospizverein']) && $data['hospizverein'] == 1) {
	        $this->_send_hospizverein_message($ipid);
	    }
	    
	    return $result;
	    
	}
	
	
    //SEND MESSAGE
	private function _send_hospizverein_message($ipid = null)
	{
	    $result = null;
        if ($this->logininfo->clientid == 48) {
            
            $obj = new User();
            $coordinators_users = $obj->fetchKoordinatorsUsers($this->logininfo->clientid);

        
            $patname = $this->_patientMasterData['nice_name'];
            
            $message_entry .= "Der Patient " . $patname . " möchte eine Begleitung durch den Hospizdienst.";
            
            $content = Pms_CommonData::aesEncrypt($message_entry);
            
            $title = Pms_CommonData::aesEncrypt('Hospizverein');
            
            // for user id = 339
//             $usertosend = 339; // andrealisske on clientid = 48
            //$usertosend = 370; // ancuta  on pms
            //modified ISPC 1189
            
            $msg_date = date("Y-m-d H:i:s", time());
            $create_date = date("Y-m-d", time());
            
            $mail = [];
            
            $coordinators_users_ids = array_column($coordinators_users, 'id');
            $coordinators_users_ids = implode(',', $coordinators_users_ids);
            
            foreach($coordinators_users as $kusr => $user)
            {
                $mail[] = [
                    'sender' => $this->logininfo->userid,
                    'clientid' => $this->logininfo->clientid,
                    'recipient' => $user['id'],
                    'msg_date' => $msg_date,
                    'title' => $title,
                    'content' => $content,
                    'recipients' => $coordinators_users_ids,
                    'create_date' => $create_date,
                    'create_user' => $this->logininfo->userid,
                    'read_msg' => '0',
                ];
            }
            
            
            if ( ! empty($mail)) {
                $collection = new Doctrine_Collection('Messages');
                $collection->fromArray($mail);
                $collection->save();
                
                $result = $collection;
            }
        }
    
        return $result;
	
	}
	
	
	
	
}

?>