<?php

class Application_Form_PatientMaintainanceStage extends Pms_Form
{
    protected $_model = 'PatientMaintainanceStage';
    
//     protected $_block_name_allowed_inputs =  array(
//         "WlAssessment" => [
//             'create_form_maintenance_stage' => [
//                 'xxxxx',
//                 'first_name',
//                 'last_name',
//                 'birthd',
//                 'street1',
//                 'zip',
//                 'city',
//                 'phone',
    
//             ],
//         ],
//     );
    
    public function getVersorgerExtract() {
        return array(
            array( "label" => $this->translate('stage'), "cols" => array("__stage", "fromdate")),
            array( "label" => $this->translate('erstantrag'), "cols" => array("erstantrag", "e_fromdate")),
            array( "label" => $this->translate('horherstufung'), "cols" => array("horherstufung", "h_fromdate")),
            array( "label" => $this->translate('rejected_date'), "cols" => array("rejected_date", "rejected_date")),        //ISPC-2668 Lore 11.09.2020
            array( "label" => $this->translate('opposition_date'), "cols" => array("opposition_date", "opposition_date")),      //ISPC-2668 Lore 11.09.2020
        );
    }
    
    public function getVersorgerAddress()
    {
        return null;
    }
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_maintenance_stage_benefits' => [
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
    
    
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientMaintainanceStage';
    
    
	public function InsertData($post)
	{

		// get PatientMaintainanceStage  data, if empty- add new, else, change the actual one
		$pms =  new PatientMaintainanceStage();
		$current_pms = $pms->getLastpatientMaintainanceStage($post['ipid']);
		
		if(strlen($post['stage'])>0)
		{
		    $post['chkval'] = $post['stage'];
		}
		
		if( ! empty($current_pms)){
			$dg = Doctrine::getTable('PatientMaintainanceStage')->find($current_pms[0]['id']);
			$dg->stage = $post['chkval'];
			$dg->erstantrag = $post['erstantrag'];
			$dg->horherstufung = $post['horherstufung'];
			//ISPC-2668 Lore 11.09.2020
			$frm->rejected_date = isset($post['rejected_date']) ?  date("Y-m-d",strtotime($post['rejected_date'])) : "0000-00-00";
			$frm->opposition_date = isset($post['opposition_date']) ?  date("Y-m-d",strtotime($post['opposition_date'])) : "0000-00-00";
			//.
			$dg->save();
		} 
		else
		{ // no saved data ? ? ?? ?
			$frm = new PatientMaintainanceStage();
			$frm->ipid = $post['ipid'];
			$frm->stage = $post['chkval'];
			$frm->erstantrag = $post['erstantrag'];
			$frm->horherstufung = $post['horherstufung'];
			$frm->fromdate = date("Y-m-d",time());
			//ISPC-2668 Lore 11.09.2020
			$frm->rejected_date = isset($post['rejected_date']) ?  date("Y-m-d",strtotime($post['rejected_date'])) : "0000-00-00";
			$frm->opposition_date = isset($post['opposition_date']) ?  date("Y-m-d",strtotime($post['opposition_date'])) : "0000-00-00";
			//.
			$frm->save();
		}
		
		
		
		/* 
		if(strlen($post['stage'])>0)
		{
			$post['chkval'] = $post['stage'];
		}

		$ipid = Doctrine_Query::create()
		->select('*')
		->from('PatientMaintainanceStage')
		->where("ipid='".$post['ipid']."'")
		->limit(1)
		->orderBy('id desc');
		$epexe = $ipid->execute();

		if($epexe)
		{
			$maintainarr =  $epexe->toArray();
			if(count($maintainarr)>0)
			{
				$dg = Doctrine::getTable('PatientMaintainanceStage')->find($maintainarr[0]['id']);
				$dg->tilldate =  date("Y-m-d H:i:s",time());
				$dg->save();
			}
		}

		$frm = new PatientMaintainanceStage();
		$frm->ipid = $post['ipid'];
		$frm->stage = $post['chkval'];
		$frm->erstantrag = $post['erstantrag'];
		$frm->horherstufung = $post['horherstufung'];
		$frm->fromdate = date("Y-m-d",time());
		$frm->save();
		 */
	}
	
	public function InsertData_old($post)
	{

		if(strlen($post['stage'])>0)
		{
			$post['chkval'] = $post['stage'];
		}

		$ipid = Doctrine_Query::create()
		->select('*')
		->from('PatientMaintainanceStage')
		->where("ipid='".$post['ipid']."'")
		->limit(1)
		->orderBy('id desc');
		$epexe = $ipid->execute();

		if($epexe)
		{
			$maintainarr =  $epexe->toArray();
			if(count($maintainarr)>0)
			{
				$dg = Doctrine::getTable('PatientMaintainanceStage')->find($maintainarr[0]['id']);
				$dg->tilldate =  date("Y-m-d H:i:s",time());
				$dg->save();
			}
		}

		$frm = new PatientMaintainanceStage();
		$frm->ipid = $post['ipid'];
		$frm->stage = $post['chkval'];
		$frm->erstantrag = $post['erstantrag'];
		$frm->horherstufung = $post['horherstufung'];
		$frm->fromdate = date("Y-m-d",time());
		$frm->save();
		
	}


	public function UpdateData($post)
	{



	}

	
	

	public function create_form_maintenance_stage ($values =  array() , $elementsBelongTo = null)
	{
	    
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__ , "save_maintenancestage");
	    
	    
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table'));
		$subform->setLegend($this->translate('maintenancestage'));
		$subform->setAttrib("class", "label_same_size");
	
		
		if ( ! is_null($elementsBelongTo)) {
		    $subform->setOptions(array(
		        'elementsBelongTo' => $elementsBelongTo
		    ));
		} elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
		    $subform->setOptions(array(
		        'elementsBelongTo' => $elementsBelongTo
		    ));
		}
	
// 		if (! empty($values)) dd($values);
	
// 		$current_ms_arr = PatientMaintainanceStage::getLastpatientMaintainanceStage($this->_patientMasterData['ipid']);
// 		if( ! empty($current_ms_arr)){
// 			$current_ms = $current_ms_arr[0];
// 		}

		$stage_array  = PatientMaintainanceStage::get_MaintainanceStage_array();
		
		$current_ms =  $values;
		
		$subform->addElement('hidden', 'id', array(
				'value'        => $current_ms['id'] ? $current_ms['id'] : 0 ,
				'required'     => false,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
		
				),
		));
		
		$subform->addElement('select', 'stage', array(
				'value'        => $current_ms['stage'],
				'multiOptions' => $stage_array,
				'label'        => $this->translate('stage'),
				'required'     => false,
// 				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
				),
		));
		 
		
		
		$subform->addElement('text', 'fromdate', array(
		    'label'      => $this->translate('fromdate'),				// 	        'placeholder' => 'Search my date',
			'required'   => false,
		    'value'        => empty($current_ms['fromdate']) || $current_ms['fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['fromdate'])),
		    'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
		    //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	    
			'decorators' => array(
					'ViewHelper',
					array('Errors'),
					array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
					array('Label', array('tag' => 'td')),
					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
			),
		    'class' => 'date allow_future',
		    'data-altfield' => 'start_date',
		    'data-altformat' => 'yy-mm-dd',
		));
		
		
		
		
		
		
	
		
		$subform->addElement('checkbox', 'erstantrag', array(
		    'checkedValue'    => '1',
		    'uncheckedValue'  => '0',
			'value'        => $current_ms['erstantrag'],
			'label'        => $this->translate('erstantrag'),
			'decorators' => array(
					'ViewHelper',
					array('Errors'),
					array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
					array('Label', array('tag' => 'td')),
					array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
			),
		    'onChange' => 'if (this.checked) { $(".e_fromdate", $(this).parents("table")).show(); $("input[name$=\"\[e_fromdate\]\"]", $(this).parents("table")).val("");} else {$(".e_fromdate", $(this).parents("table")).hide(); $("input[name$=\"\[e_fromdate\]\"]", $(this).parents("table")).val("");}',
		));
		$display = $current_ms['erstantrag'] != 1 ? 'display:none' : null;
		$subform->addElement('text', 'e_fromdate', array(
		    
		    'value'        => empty($current_ms['e_fromdate']) || $current_ms['e_fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['e_fromdate'])),
		    'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
		    
			'label'        => null,
			'multiOptions' => array( 1 => ''),
			'decorators' => array(
					'ViewHelper',
					array('Errors'),
					array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'class' => 'e_fromdate', 'style' => $display)),
					array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
			),
		    'class' => 'date',
		    
		));
		
		
		
		$subform->addElement('checkbox', 'horherstufung', array(
		    'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
			'value'        => $current_ms['horherstufung'],
			'label'        => $this->translate('horherstufung'),
			'decorators' => array(
					'ViewHelper',
					array('Errors'),
					array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
					array('Label', array('tag' => 'td')),
					array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
			),
		    'onChange' => 'if (this.checked) { $(".h_fromdate", $(this).parents("table")).show(); $("input[name$=\"\[h_fromdate\]\"]", $(this).parents("table")).val("");} else {$(".h_fromdate", $(this).parents("table")).hide(); $("input[name$=\"\[h_fromdate\]\"]", $(this).parents("table")).val("");}',
		    
		));
		$display = $current_ms['horherstufung'] != 1 ? 'display:none' : null;
		$subform->addElement('text', 'h_fromdate', array(

		    'value'        => empty($current_ms['h_fromdate']) || $current_ms['h_fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['h_fromdate'])),
		    'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
		    
		    'label'        => null,
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'class' => 'h_fromdate', 'style' => $display)),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
		    ),
		    'class' => 'date',
		
		));
		
// 		
        //ISPC-2668 Lore 11.09.2020
		$subform->addElement('text', 'rejected_date', array(
		    'label'      => $this->translate('rejected_date'),				// 	        'placeholder' => 'Search my date',
		    'required'   => false,
		    'value'        => empty($current_ms['rejected_date']) || $current_ms['rejected_date'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['rejected_date'])),
		    'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
		    //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
		    
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
		        array('Label', array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    ),
		    'class' => 'date allow_future',
		    'data-altfield' => 'start_date',
		    'data-altformat' => 'yy-mm-dd',
		));
		
		$subform->addElement('text', 'opposition_date', array(
		    'label'      => $this->translate('opposition_date'),				// 	        'placeholder' => 'Search my date',
		    'required'   => false,
		    'value'        => empty($current_ms['opposition_date']) || $current_ms['opposition_date'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['opposition_date'])),
		    'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
		    //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
		    
		    'decorators' => array(
		        'ViewHelper',
		        array('Errors'),
		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
		        array('Label', array('tag' => 'td')),
		        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    ),
		    'class' => 'date allow_future',
		    'data-altfield' => 'start_date',
		    'data-altformat' => 'yy-mm-dd',
		));
		//.
	
		return $this->filter_by_block_name($subform,  __FUNCTION__);
	}
	
	//ISPC-2668 Lore 11.09.2020
	public function create_form_block_patient_ms ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_maintenancestage");
	    
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    
	    $subform->setLegend('block_patient_ms');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    	        
	    $stage_array  = PatientMaintainanceStage::get_MaintainanceStage_array();
	    $current_ms =  $values;
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $current_ms['id'] ? $current_ms['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
	    
	    if($values['formular_type'] == 'pdf'){
	        $subform->addElement('note', 'stage', array(
	            'value'        => $stage_array[$current_ms['stage']],
	            'label'        => $this->translate('stage'),
	            'required'     => false,
	            // 				'filters'      => array('StringTrim'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	        ));
	    } else{
	        
    	    $subform->addElement('select', 'stage', array(
    	        'value'        => $current_ms['stage'],
    	        'multiOptions' => $stage_array,
    	        'label'        => $this->translate('stage'),
    	        'required'     => false,
    	        // 				'filters'      => array('StringTrim'),
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
    	        ),
    	    ));
    	}
	    
	    
	    $subform->addElement('text', 'fromdate', array(
	        'label'      => $this->translate('fromdate'),				// 	        'placeholder' => 'Search my date',
	        'required'   => false,
	        'value'        => empty($current_ms['fromdate']) || $current_ms['fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['fromdate'])),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    
	    
	    
	    
	    
	    
	    
	    $subform->addElement('checkbox', 'erstantrag', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'value'        => $current_ms['erstantrag'],
	        'label'        => $this->translate('erstantrag'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
	        ),
	        'onChange' => 'if (this.checked) { $(".e_fromdate", $(this).parents("table")).show(); $("input[name$=\"\[e_fromdate\]\"]", $(this).parents("table")).val("");} else {$(".e_fromdate", $(this).parents("table")).hide(); $("input[name$=\"\[e_fromdate\]\"]", $(this).parents("table")).val("");}',
	    ));
	    $display = $current_ms['erstantrag'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'e_fromdate', array(
	        
	        'value'        => empty($current_ms['e_fromdate']) || $current_ms['e_fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['e_fromdate'])),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        
	        'label'        => null,
	        'multiOptions' => array( 1 => ''),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'class' => 'e_fromdate', 'style' => $display)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
	        ),
	        'class' => 'date',
	        
	    ));
	    
	    
	    
	    $subform->addElement('checkbox', 'horherstufung', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'value'        => $current_ms['horherstufung'],
	        'label'        => $this->translate('horherstufung'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),
	        'onChange' => 'if (this.checked) { $(".h_fromdate", $(this).parents("table")).show(); $("input[name$=\"\[h_fromdate\]\"]", $(this).parents("table")).val("");} else {$(".h_fromdate", $(this).parents("table")).hide(); $("input[name$=\"\[h_fromdate\]\"]", $(this).parents("table")).val("");}',
	        
	    ));
	    $display = $current_ms['horherstufung'] != 1 ? 'display:none' : null;
	    $subform->addElement('text', 'h_fromdate', array(
	        
	        'value'        => empty($current_ms['h_fromdate']) || $current_ms['h_fromdate'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['h_fromdate'])),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        
	        'label'        => null,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'class' => 'h_fromdate', 'style' => $display)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
	        ),
	        'class' => 'date',
	        
	    ));
	    
	    //
	    //ISPC-2668 Lore 11.09.2020
	    $subform->addElement('text', 'rejected_date', array(
	        'label'      => $this->translate('rejected_date'),				// 	        'placeholder' => 'Search my date',
	        'required'   => false,
	        'value'        => empty($current_ms['rejected_date']) || $current_ms['rejected_date'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['rejected_date'])),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    $subform->addElement('text', 'opposition_date', array(
	        'label'      => $this->translate('opposition_date'),				// 	        'placeholder' => 'Search my date',
	        'required'   => false,
	        'value'        => empty($current_ms['opposition_date']) || $current_ms['opposition_date'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['opposition_date'])),
	        'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-altfield' => 'start_date',
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    //.
	    
	    return $this->filter_by_block_name($subform,  __FUNCTION__);
	}
	
	
	public function save_maintenancestage($ipid= '', $data = array()){
		
		
		if(empty($ipid) || empty($data)) {
			return;
		}
		
		//dd($data);
		$data['ipid'] = $ipid;
		
		if ( ! empty($data['fromdate'])) {
			$data['fromdate'] = date("Y-m-d",strtotime($data['fromdate']));
		} else {
			$data['fromdate'] = '0000-00-00'; //date("Y-m-d");///ISPC-2245
		}
		
		if ( ! empty($data['tilldate'])) {
		    $data['tilldate'] = date("Y-m-d",strtotime($data['tilldate']));
		}
		
		if ( ! empty($data['date_of_decision'])) {
		    $data['date_of_decision'] = date("Y-m-d",strtotime($data['date_of_decision']));
		}
		
		if ($data['erstantrag'] == "1") {
	        $data['e_fromdate'] = ! empty($data['e_fromdate']) ? date("Y-m-d",strtotime($data['e_fromdate'])) : "0000-00-00";
		} else {
		    $data['e_fromdate'] = "0000-00-00";
		}
		
		if ($data['horherstufung'] == "1") {
	        $data['h_fromdate'] = ! empty($data['h_fromdate']) ? date("Y-m-d",strtotime($data['h_fromdate'])) : "0000-00-00";
		} else {
		    $data['h_fromdate'] = "0000-00-00";
		}
		
		//ISPC-2668 Lore 11.09.2020
		if ( ! empty($data['rejected_date'])) {
		    $data['rejected_date'] = date("Y-m-d",strtotime($data['rejected_date']));
		} else {
		    $data['rejected_date'] = '0000-00-00'; //date("Y-m-d");///ISPC-2245
		}
		if ( ! empty($data['opposition_date'])) {
		    $data['opposition_date'] = date("Y-m-d",strtotime($data['opposition_date']));
		} else {
		    $data['opposition_date'] = '0000-00-00'; //date("Y-m-d");///ISPC-2245
		}
		//.
		
		$entity = new PatientMaintainanceStage();
		
		return $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
		
// 		return $entity->findOrCreateOneBy('ipid', $ipid, $data);
		//return $entity->findOrCreateOneById($data['id'], $data);
	}
	
	
	public function getErstantragArray()
	{
	    return [
	        0 => $this->translate('no_radio'),
	        1 => $this->translate('yes_radio'),
	    ];
	}
	
	public function getHorherstufungArray()
	{
	    return [
	        0 => $this->translate('no_radio'),
	        1 => $this->translate('yes_radio'),
	    ];
	}
	
	

	/**
	 * @cla on 04.12.2018
	 * Leistungen der Pflegeversicherung SGB XI
	 * Benefits of long-term care insurance SGB XI
	 *
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_maintenance_stage_benefits ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_maintenancestage");
	     
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('maintenancestage'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);

	        $stage_array  = PatientMaintainanceStage::get_MaintainanceStage_array();
	
	        $current_ms =  $values;
	
	        $subform->addElement('hidden', 'id', array(
	            'value'        => $current_ms['id'] ? $current_ms['id'] : 0 ,
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	
	            ),
	        ));
	
	        $subform->addElement('select', 'stage', array(
	            'value'        => $current_ms['stage'],
	            'multiOptions' => $stage_array,
	            'label'        => $this->translate('stage'),
	            'required'     => false,
	            // 				'filters'      => array('StringTrim'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', "colspan" => 3)),
	                array('Label', array('tag' => 'td', 'tagClass' => 'print_column_first')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	        ));
	        	


	        

	        $radios = PatientMaintainanceStage::_mapping_columns('status');
	        $subform->addElement('radio',  'status', array(
	            'value'        => $current_ms['status'],
	            //'label'        => $this->translate('Status'),
	            'required'     => false,
	            'multiOptions' => $radios,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'class'        => 'living_will_radio',
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 4)),
// 	                /*array('Label', array('tag' => 'td')),*/
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            //'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
	        
	        ));
	        
	        $subform->addElement('text', 'date_of_decision', array(
	            'label'      => $this->translate('Date of decision'),				// 	        'placeholder' => 'Search my date',
	            'required'   => false,
	            'value'        => empty($current_ms['date_of_decision']) || $current_ms['date_of_decision'] == "0000-00-00" ? "" : date('d.m.Y', strtotime($current_ms['date_of_decision'])),
	            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	            //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),
	             
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'class' => 'date allow_future',
	            'data-altfield' => 'date_of_decision',
	            'data-altformat' => 'yy-mm-dd',
	        ));
	
	        
	        
	        
	        $subform->addElement('checkbox', 'nursing_care_visit_necessary', array(
	            'checkedValue'    => '1',
	            'uncheckedValue'  => '0',
	            'value'        => $current_ms['nursing_care_visit_necessary'],
	            'label'        => $this->translate('37er Nursing Care visit necessary'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
	            ),
	            'onChange' => 'if (this.checked) { $(".nursing_care_visit_necessary_freetext", $(this).parents("table")).show(); } else {$(".nursing_care_visit_necessary_freetext", $(this).parents("table")).hide();}',
	        ));
	        
	        $display = $current_ms['nursing_care_visit_necessary'] != 1 ? 'display:none' : null;
	        
	        $subform->addElement('text', 'implemented_by', array(
	        
	            'value'        => $current_ms['implemented_by'],
// 	            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),	        
	            'label'        => $this->translate("implemented by:"),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array('Label', array('tag' => 'span')),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan'=>2, 'class' => 'nursing_care_visit_necessary_freetext', 'style' => $display)),
	                //array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
	            ),
// 	            'class' => 'xxx',
	        
	        ));
	        $subform->addElement('text', 'required_on', array(
	        
	            'value'        => $current_ms['required_on'],
// 	            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
	        
	            'label'        => $this->translate("required on:"),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array('Label', array('tag' => 'span')),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true)),
	            ),
// 	            'class' => 'xxx',
	        
	        ));

	
	
	        return $this->filter_by_block_name($subform,  $__fnName);
		}
	
}

?>