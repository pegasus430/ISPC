<?php

require_once("Pms/Form.php");

class Application_Form_WoundDocumentation extends Pms_Form {

    protected $_model = 'WoundDocumentation';
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_wound_localization' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
            'create_form_wound_type' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
        ],
    ];
    
		public function insert($ipid, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
			
			
			$ins = new WoundDocumentation();
			$ins->ipid = $ipid;
			
			$ins->w_name = $post['w_name'];
			$ins->w_type = implode(',', $post['w_type']);
			$ins->w_type_degree = $post['w_type_degree'];
			$ins->w_type_more = $post['w_type_more'];
			$ins->w_size = $post['w_size'];
			$ins->w_depth = $post['w_depth'];
			$ins->w_width = $post['w_width'];
			
			$ins->w_wounddescription = $post['w_wounddescription']; //ISPC-2465
			$ins->w_localisation = $post['human'];
			
			$ins->w_treatment_goals = implode(',', $post['w_treatment_goals']);
			$ins->w_change_day = $post['w_change_day'];
			$ins->w_change_week = $post['w_change_week'];
			$ins->w_treatment_other = $post['w_treatment_other'];
			$ins->w_wet = implode(',', $post['w_wet']);
			
			$ins->w_clean = implode(',', $post['w_clean']);
			$ins->w_clean_more = $post['w_clean_more'];
			
			$ins->w_disinfection = implode(',', $post['w_disinfection']);
			$ins->w_disinfection_gel = $post['w_disinfection_gel'];
			$ins->w_disinfection_more = $post['w_disinfection_more'];
			
			$ins->w_dressings = implode(',', $post['w_dressings']);
			$ins->w_dressings_product = $post['w_dressings_product'];
			$ins->w_dressings_comment = $post['w_dressings_comment'];
			
			$ins->w_dressings_second = implode(',', $post['w_dressings_second']);
			$ins->w_dressings_second_more = $post['w_dressings_second_more'];
			$ins->w_dressings_second_product = $post['w_dressings_second_product'];
			$ins->w_dressings_second_comment = $post['w_dressings_second_comment'];
			
			$ins->w_surrounding_skin_protect = implode(',', $post['w_surrounding_skin_protect']);
			$ins->w_surrounding_skin_protect_more = $post['w_surrounding_skin_protect_more'];
			$ins->w_surrounding_skin_protect_product = $post['w_surrounding_skin_protect_product'];
			$ins->w_surrounding_skin_protect_comment = $post['w_surrounding_skin_protect_comment'];
			
			$ins->w_odor = implode(',', $post['w_odor']);
			$ins->w_odor_more = $post['w_odor_more'];
			$ins->w_exudation_therapy = implode(',', $post['w_exudation_therapy']);
			$ins->w_exudation_therapy_more = $post['w_exudation_therapy_more'];
			$ins->save();
			
			$result = $ins->id;
			
			$tab_name = "wound_documentation";
			$comment = 'Wunddokumentation '.$post['w_name'].' hinzugefügt';
			
			
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
			return $ins->id;
		}

		public function update($formid, $post)
		{/* print_r($post);exit; */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			
			$upd = Doctrine::getTable('WoundDocumentation')->find($formid);
			$upd->w_name = $post['w_name'];
			$upd->w_type = implode(',', $post['w_type']);
			$upd->w_type_degree = $post['w_type_degree'];
			$upd->w_type_more = $post['w_type_more'];
			$upd->w_size = $post['w_size'];
			$upd->w_depth = $post['w_depth'];
			$upd->w_width = $post['w_width'];
			
			$upd->w_wounddescription = $post['w_wounddescription']; //ISPC-2465
			$upd->w_localisation = $post['human'];
			
			$upd->w_treatment_goals = implode(',', $post['w_treatment_goals']);
			$upd->w_change_day = $post['w_change_day'];
			$upd->w_change_week = $post['w_change_week'];
			$upd->w_treatment_other = $post['w_treatment_other'];
			$upd->w_wet = implode(',', $post['w_wet']);
			
			$upd->w_clean = implode(',', $post['w_clean']);
			$upd->w_clean_more = $post['w_clean_more'];
			
			$upd->w_disinfection = implode(',', $post['w_disinfection']);
			$upd->w_disinfection_gel = $post['w_disinfection_gel'];
			$upd->w_disinfection_more = $post['w_disinfection_more'];
			
			$upd->w_dressings = implode(',', $post['w_dressings']);
			$upd->w_dressings_product = $post['w_dressings_product'];
			$upd->w_dressings_comment = $post['w_dressings_comment'];
			
			$upd->w_dressings_second = implode(',', $post['w_dressings_second']);
			$upd->w_dressings_second_more = $post['w_dressings_second_more'];
			$upd->w_dressings_second_product = $post['w_dressings_second_product'];
			$upd->w_dressings_second_comment = $post['w_dressings_second_comment'];
			
			$upd->w_surrounding_skin_protect = implode(',', $post['w_surrounding_skin_protect']);
			$upd->w_surrounding_skin_protect_more = $post['w_surrounding_skin_protect_more'];
			$upd->w_surrounding_skin_protect_product = $post['w_surrounding_skin_protect_product'];
			$upd->w_surrounding_skin_protect_comment = $post['w_surrounding_skin_protect_comment'];
			
			$upd->w_odor = implode(',', $post['w_odor']);
			$upd->w_odor_more = $post['w_odor_more'];
			$upd->w_exudation_therapy = implode(',', $post['w_exudation_therapy']);
			$upd->w_exudation_therapy_more = $post['w_exudation_therapy_more'];
			$upd->save();
			
			
			$tab_name = "wound_documentation";
			$comment = 'Wunddokumentation '.$post['w_name'].' wurde editiert';
			
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $formid;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $formid;
			$cust->save();
			
			
			
		}
		public function update_isclosed($formid, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
				
			$upd = Doctrine::getTable('WoundDocumentation')->find($formid);
			$upd->w_isclosed = $post['isclosed'];
			$upd->save();
		}

		
		public function create_form_wound_localization($options =  array() , $elementsBelongTo = null)
		{
		    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		     
		    $this->mapValidateFunction($__fnName , "create_form_isValid");
		    
		    $this->mapSaveFunction($__fnName , "save_form_wound");
		     
		    
		    //wound type
		    $subform = new Zend_Form_SubForm();
		    $subform->removeDecorator('DtDdWrapper');
		    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
		    $subform->setLegend($this->translate('Wound Localization'));
		    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
		     
		    $this->__setElementsBelongTo($subform, $elementsBelongTo);
		    
		    
		    $subform->addElement('textarea', 'previous_w_localisation', array(
		        'value'        => $options['w_localisation'],
		        'label'        => null,
		        'required'     => false,
		        'decorators'   => array(
		            'ViewHelper',
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'class'=>'previous_w_localisation')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
		        ),
		        'style'       => 'display:none',
		        'class'       => 'old_human_canvas_holder',
		    ));
		    
		    $subform->addElement('note', 'human_canvas_holder', array(
		        'value'        => null,
		        'label'        => null,
		        'required'     => false,
		        'decorators'   => array(
		            'ViewHelper',
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'class'=>'human_canvas_holder')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
		        ),
		    ));
		    //TODO where do i save this?
		    /*
		    $subform->addElement('text', 'w_size', array(
		        'value'        => $options['w_size'],
		        'label'        => $this->translate('wound size'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            array('Label', array('tag' => 'td', 'tagClass'=>'tdwidth20percent')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
		        ),
		        'size' => 8,
		    ));
		    $subform->addElement('text', 'w_depth', array(
		        'value'        => $options['w_depth'],
		        'label'        => $this->translate('wound depth'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            array('Label', array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
		        ),
		        'size' => 8,
		    ));
		    $subform->addElement('text', 'w_width', array(
		        'value'        => $options['w_width'],
		        'label'        => $this->translate('wound width'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            array('Label', array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
		        ),
		        'size' => 8,
		    ));
		    */
		    $el = $this->createElement('button', 'canvas_reset', array(
		    		'type'         => 'button',
		    		//'value'        => 'save',
		    		// 	        'content'      => $this->translate('submit'),
		    		'label'        => $this->translate('canvas_reset'),
		    		'class'=> 'canvas_reset',
		    		// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
		    		//'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
		    		'decorators'   => array(
		            'ViewHelper',
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2,)),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
		        ),
		    ));
		    // 	    dd($el->getAttrib('content'));
		    $subform->addElement($el, 'reset');
		    
		    
		    return $this->filter_by_block_name($subform, $__fnName);
		}
		
		
		
		public function create_form_wound_type($options =  array() , $elementsBelongTo = null)
		{		    
		    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		     
		    $this->mapValidateFunction($__fnName , "create_form_isValid");
		    
		    $this->mapSaveFunction($__fnName , "save_form_wound");
		     
		    
		    //wound type
		    $subform = new Zend_Form_SubForm();
		    $subform->removeDecorator('DtDdWrapper');
		    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
		    $subform->setLegend($this->translate('Wundart'));
		    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
		     
		    $this->__setElementsBelongTo($subform, $elementsBelongTo);
		    
		    /*
		     * $ins->w_name = $post['w_name'];
		     $ins->w_type = implode(',', $post['w_type']);
		     $ins->w_type_degree = $post['w_type_degree'];
		     $ins->w_type_more = $post['w_type_more'];

		     */
		    
		    $subform->addElement('hidden', 'id', array(
		        'value'        => (int)$options['id'],
		        'label'        => null,
		        'decorators'   => array(
		            'ViewHelper',
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		        ),
		    ));
		    
		    $el= $this->createElement('checkbox', 'w_type', array(
		        'checkedValue'    => '1',
		        'uncheckedValue'  => '',
		        'id'              => '_page_4-Wound_Type-w_type_1',
// 		        'value'           => in_array('1', $options['w_type']) ? 1 : null,
		        'checked'         => in_array('1', $options['w_type']) ? true : false,
		    
		        'label'        => $this->translate('ulceration wound'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'validators'   => array('Int'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Label', array(
		                'placement'=> 'APPEND'
		            )),
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
		        ),
		        'isArray' => true,
		    ));
		    $subform->addElement($el, 'w_type_1');

		    
		    
		    $el= $this->createElement('checkbox', 'w_type', array(
		        'checkedValue'    => '2',
		        'uncheckedValue'  => '',
		        'checked'         => in_array('2', $options['w_type']) ? true : false,
// 		        'checked'         => 'checked',
		        'id' => '_page_4-Wound_Type-w_type_2',
		        'label'        => $this->translate('wound grade'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'validators'   => array('Int'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Label', array(
		                'placement'=> 'APPEND'
		            )),
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
		        ),
		        'isArray' => true,
		    ));
		    $subform->addElement($el, 'w_type_2');
		    
		    
		    $subform->addElement('text', 'w_type_degree', array(
		        'value'        => $options['w_type_degree'],
		        'label'        => $this->translate('NPUAP'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Label', array(
		                'placement'=> 'APPEND'
		            )),
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            // 		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
		        ),
		        'size' => 8,
		    ));
		    
		    
		    $el= $this->createElement('checkbox', 'w_type', array(
		        'checkedValue'    => '3',
		        'uncheckedValue'  => '',
// 		        'value'           => in_array('3', $options['w_type']) ? 3 : null,
		        'checked'         => in_array('3', $options['w_type']) ? true : false,
		        'id' => '_page_4-Wound_Type-w_type3',
		        'label'        => $this->translate('other_form_type'),
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'validators'   => array('Int'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Label', array(
		                'placement'=> 'APPEND'
		            )),
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
		        ),
		        'isArray' => true,
		    ));
		    $subform->addElement($el, 'w_type_3');
		    
		    
		    $subform->addElement('text', 'w_type_more', array(
		        'value'        => $options['w_type_more'],
		        'label'        => null,
		        'required'     => false,
		        'filters'      => array('StringTrim'),
		        'decorators'   => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
		        ),
		        'size' => 8,
		    ));
		    
		    return $this->filter_by_block_name($subform, $__fnName);
		}
	
		
		
    
	public function save_form_wound($ipid = '', $data = array()) 
	{
	    
	    if (empty($ipid)) {
	        return;
	    }
	    
	    if (is_array($data['w_type'])) {
	        $data['w_type'] = implode(',', $data['w_type']);
	    }
	    
	    $entity = new WoundDocumentation();
	    return $entity->findOrCreateOneByIpidAndId($ipid, (int)$data['id'], $data);
	    
	}
	
}


?>