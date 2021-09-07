<?php
/**
 * 
 * @author claudiu
 * 
 * 16.11.2017
 *
 */
class Application_Form_PatientACP extends Pms_Form
{
	
    protected $_model = 'PatientACP';
    
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmPatientACP";  //define the name if you want to piggyback some triggers
		
	protected $_translate_lang_array = 'acp_box_lang';
	
	protected $_block_name_allowed_inputs =  array(
	
	    "MamboAssessment" => [
	
	        'create_form_acp' => [
	            //this are removed
	            '__removed' => [
	                'care_orders',
	                'emergencyplan',
	            ],
	            //only this are allowed
	            '__allowed' => []
	        ],
	        
	        '_tab_care_orders' => [
	            //this are removed
	            '__removed' => [],
	            //only this are allowed
	            '__allowed' => []
	        ],
	        '_tab_healthcare_proxy' => [
	            //this are removed
	            '__removed' => [],
	            //only this are allowed
	            '__allowed' => []
	        ],
	        '_tab_living_will' => [
	            //this are removed
	            '__removed' => [],
	            //only this are allowed
	            '__allowed' => []
	        ],
	    ],
	);
	
	protected $_block_feedback_options = [
	    "MamboAssessment" => [
	        'create_form_acp' => [
	            "todo",
	            "feedback",
// 	            "benefit_plan",
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
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_acp( $options = array(), $elementsBelongTo = null)
	{
	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

// 	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");

	    $this->mapSaveFunction(__FUNCTION__ , "save_form_acp");
	     
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'div' , 'class' => 'acp_accordion accordion_c'));
	    $subform->setLegend($this->translate('box_title'));
	    $subform->setAttrib("class", "label_same_size inlineEdit {$__fnName}");
	    
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		

		if ( empty($options['contact_persons_arr']) 
		    && ($array_values = array_values($options['contact_persons_arr'])) && empty($array_values) 
		    && ! empty($this->_patientMasterData['ContactPersonMaster'])) 
		{
		    
		    ContactPersonMaster::beautifyName($this->_patientMasterData['ContactPersonMaster']);
		    $options['contact_persons_arr'] = array_column($this->_patientMasterData['ContactPersonMaster'], 'nice_name', 'id');
		}
		
		$subform->addSubform( $this->_tab_living_will($options) , 'living_will');
		$subform->addSubform( $this->_tab_care_orders($options) , 'care_orders');
		$subform->addSubform( $this->_tab_healthcare_proxy($options) , 'healthcare_proxy');
		$subform->addSubform( $this->_tab_emergencyplan($options) , 'emergencyplan');
		
		return $this->filter_by_block_name($subform, $__fnName);
		
	}
	
	//ISPC-2671 Lore 07.09.2020
	public function create_form_block_patient_acp( $options = array(), $elementsBelongTo = null)
	{

	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
 	    
 	    $this->mapSaveFunction($__fnName , "save_form_acp");

 	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->removeDecorator('DtDdWrapper');
	   $subform->addDecorator('HtmlTag', array('tag' => 'div' , 'class' => 'XXX'));
	    $subform->setLegend('patient_acp');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    if ( empty($options['contact_persons_arr'])
	        //&& ($array_values = array_values($options['contact_persons_arr'])) && empty($array_values)
	        && ! empty($this->_patientMasterData['ContactPersonMaster']))
	    {
	        
	        ContactPersonMaster::beautifyName($this->_patientMasterData['ContactPersonMaster']);
	        $options['contact_persons_arr'] = array_column($this->_patientMasterData['ContactPersonMaster'], 'nice_name', 'id');
	    }
	    $subform->addSubform( $this->_tab_pluss($options) , 'plus');       //Lore 15.09.2020
	    
	    $subform->addSubform( $this->_tab_living_will($options) , 'living_will');
	    $subform->addElement('note', 'br_living_will', array(
	        'value'        => '<br><br/>',
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	        ),
	    ));

	    $subform->addSubform( $this->_tab_care_orders($options) , 'care_orders');
	    $subform->addElement('note', 'br_care_orders', array(
	        'value'        => '<br><br/>',
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	        ),
	    ));
      
	    $subform->addSubform( $this->_tab_healthcare_proxy($options) , 'healthcare_proxy');
	    $subform->addElement('note', 'br_healthcare_proxy', array(
	        'value'        => '<br><br/>',
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	        ),
	    ));
	    $subform->addSubform( $this->_tab_emergencyplan($options) , 'emergencyplan');
	    $subform->addElement('note', 'br_emergencyplan', array(
	        'value'        => '<br><br/>',
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	        ),
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	}

	/**
	 * ACP-Betreuungsverfügung
	 * 
	 * @param unknown $options
	 * @return Zend_Form
	 */
	private function _tab_care_orders($options =  array()) 
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

	    if ( ! isset($options['contact_persons_arr'][0])) {
	        $options['contact_persons_arr'] = is_array($options['contact_persons_arr']) ? $options['contact_persons_arr'] : [];
	        $options['contact_persons_arr'] = array(0 => '') + $options['contact_persons_arr'];
	    }
	    
	    $box_division = 'care_orders';
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate($box_division));
	    $subform->setAttrib("class", "label_same_size {$__fnName} ");
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' ,'class'=>' acp_table' ));
	     
	     
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options[$box_division]['id'] ? $options[$box_division]['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	    
	        ),
	    ));
	    $subform->addElement('hidden', 'division_tab', array(
	        'value'        => $box_division,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	        ),
	    ));
	    
	    $subform->addElement('radio',  'active', array(
	        'value'        => isset($options[$box_division]['active']) && ! empty($options[$box_division]['active']) ? $options[$box_division]['active'] : "",	         
	        'label'        => $this->translate('Status'),
	        'required'     => false,
	        'multiOptions' => PatientAcp::getDefaultRadios(),
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'living_will_radio',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',	                
	            )),
	        ),
	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide();\$('.show_hide td label', \$(this).parents('table')).find('input[type=checkbox]').removeAttr('checked');}", // ISPC-2565,Elena,26.02.2021
	         
	    ));

	    $display = $options[$box_division]['active'] == 'yes' ? '' : 'display:none;';
	    if ( ! empty($options['contact_persons_arr']) && count($options['contact_persons_arr']) > 1) {
	        if($options['formular_type'] == 'pdf'){
               // ISPC-2565,Elena,26.02.2021
	            $val = '';
	            foreach($options['contact_persons_arr'] as $key => $contact){
	                if(in_array($key, $options[$box_division]['contactperson_master_id'] )){
	                    $val .= $contact . '<br>';
                    }
                }
        	    $subform->addElement('note',  'contactperson_master_id', array(
        	        'value'        => $val,// $options['contact_persons_arr'][$options[$box_division]['contactperson_master_id']],//ISPC-2565,Elena,26.02.2021
        	        'label'        => $this->translate('contact person in charge'),
        	        'required'     => false,
        	        'filters'      => array('StringTrim'),
        	        'validators'   => array('Int'),
        	        'decorators' => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        	            array('Label', array('tag' => 'td')),
        	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
        	    
        	        ),
        	    ));
	        } else{

                //ISPC-2565,Elena,26.02.2021
	            //$display = '';
                $contactpersonArray = [];
                //print_r($options[$box_division]);
                if(!empty($options[$box_division]['contacts'])){
                    $contactpersonArray = array_keys($options[$box_division]['contacts']);
                }

                $arr_for_multicheckbox = [];
                foreach($options['contact_persons_arr'] as $key => $val){
                    if($key !== 0){
                        $arr_for_multicheckbox[$key] = $val;
                    }
                }


                $subform->addElement('multiCheckbox',  'contactperson_master_id', array(//ISPC-2565,Elena,26.02.2021
        	        'value'        => $contactpersonArray,//$options[$box_division]['contactperson_master_id'],//ISPC-2565,Elena,26.02.2021
        	        'label'        => $this->translate('contact person in charge'),
        	        'required'     => false,
        	        'multiOptions' => $arr_for_multicheckbox,//$options['contact_persons_arr'],//ISPC-2565,Elena,26.02.2021
        	        'filters'      => array('StringTrim'),
        	        'validators'   => array('Int'),
        	        'decorators' => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        	            array('Label', array('tag' => 'td')),
        	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
        	    
        	        ),
        	    ));
	        }
	    }
	    
	    $subform->addElement('text',  'comments', array(
	        'value'        => $options[$box_division]['comments'],
	        'label'        => $this->translate('where deposited'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
	             
	        ),
	    ));
	    $subform->addElement('text',  'file_date', array(
	        'value'        => null,
	        'label'        => $this->translate('from when'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'date inlineEdit_not_onFocusout',
	        'data-box'     => $box_division, // Maria:: Migration ISPC to CISPC 08.08.2020
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
	        ),
	        'onSelect' => " if(this.value != '') {\$('.hide_uploader', \$(this).parents('table')).show()} else {\$('.hide_uploader', \$(this).parents('table')).hide()}",      //ISPC-2671 Lore 07.09.2020
	        
	    ));
	    
	    $subform->addElement('note',  'qq_file_uploader_label', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => $this->translate('Upload new template'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'qq_file_uploader_label')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
	        ),
	    ));
	    
	    $subform->addElement('note',  'qq_file_uploader', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => '<noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript>',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 2,
	                'class' => 'qq_file_uploader_placeholder',
	                'data-parent' => 'table',
	                'data-tabname' => $box_division,
	            )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
	        ),
	    ));
	 
	    
	    if ( ! empty($options[$box_division]['files'])) {
	        /*list all files*/
	        $pat_enc_id =  ! empty($this->_patientMasterData['id_encrypted']) ? $this->_patientMasterData['id_encrypted'] : 0;
	        
	        $cnt = 0;
	        foreach ($options[$box_division]['files'] as $file) {
	             
// 	            $filename = $cnt++ ? $this->translate('old version') : str_replace('ACP-Betreuungsverfügung-', '', $file['title']) ;
	            $filename = $cnt++ ? $this->translate('old version') : $file['title'];
	            $filename = '<a href="stats/patientfileupload?doc_id='. $file['id'] . '&id=' . $pat_enc_id . '">' . $filename . "</a>";
                
	            $filedate= date("d.m.Y", strtotime($file['file_date']));
	                 
                $text .= <<<EOT
	                    <div class="fileitem">
	                    <div class="input">
	                    <span>{$filename}</span>
	                    <span class="filesize">{$filedate}</span>
	           </div>
	        </div>
EOT;
	                     
	        }
			$text = '<div class="fileupload">' . $text . '</div>';
			
			$subform->addElement('note',  'files_label', array(
			    'label'        => null,
			    'required'     => false,
			    'value'        => $this->translate('file download'),
			    'decorators' => array(
			        'ViewHelper',
			        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'files_label')),
			        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
			    ),
			));
			
	        $subform->addElement('note',  'files', array(
	            'label'        => null,
	            'required'     => false,
	            'value'        => $text,
	            'escape'       => false,
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 2)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide dontPrint', 'style' => $display)),
	            ),
	        ));
	    
	    }
	    												
	    
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	    
	    
	}
	
	
	/**
	 * ACP-Vorsorgevollmacht
	 * 
	 * @param unknown $options
	 * @return Zend_Form
	 */
	private function _tab_healthcare_proxy($options =  array()) 
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

	    if ( ! isset($options['contact_persons_arr'][0])) {
	        $options['contact_persons_arr'] = is_array($options['contact_persons_arr']) ? $options['contact_persons_arr'] : [];
	        $options['contact_persons_arr'] = array(0 => '') + $options['contact_persons_arr'];
	    }

	    $box_division = 'healthcare_proxy';
	    //print_r( $options[$box_division]);

	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate($box_division));
	    $subform->setAttrib("class", "label_same_size {$__fnName} ");
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' ,'class'=>' acp_table'));
	
	
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options[$box_division]['id'] ? $options[$box_division]['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	             
	        ),
	    ));
	    $subform->addElement('hidden', 'division_tab', array(
	        'value'        => $box_division,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	        ),
	    ));

	    $radio_value = isset($options[$box_division]['active']) && ! empty($options[$box_division]['active']) ? $options[$box_division]['active'] : "";
        //echo "radio " .$radio_value;

	    
	    $subform->addElement('radio',  'active', array(
	        'value'        => isset($options[$box_division]['active']) && ! empty($options[$box_division]['active']) ? $options[$box_division]['active'] : "",	         
	        'label'        => $this->translate('Status'),
	        'required'     => false,
	        'multiOptions' => PatientAcp::getDefaultRadios(),
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'living_will_radio',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide();\$('.show_hide td label', \$(this).parents('table')).find('input[type=checkbox]').removeAttr('checked');}",// ISPC-2565,Elena,26.02.2021
	         
	    ));
	     
	    $display = $options[$box_division]['active'] == 'yes' ? '' : 'display:none;';
	    if ( ! empty($options['contact_persons_arr']) && count($options['contact_persons_arr']) > 1) {
            //ISPC-2565,Elena,26.02.2021
	        $val = '';
            foreach($options['contact_persons_arr'] as $key => $contact){
                if(in_array($key, $options[$box_division]['contactperson_master_id'] )){
                    $val .= $contact . '<br>';
                }
            }
	        
	        if($options['formular_type'] == 'pdf'){
        	    $subform->addElement('note',  'contactperson_master_id', array(
        	        'value'        => $val,// $options['contact_persons_arr'][$options[$box_division]['contactperson_master_id']],//ISPC-2565,Elena,26.02.2021
        	        'label'        => $this->translate('contact person in charge'),
        	        'required'     => false,
        	        'filters'      => array('StringTrim'),
        	        'validators'   => array('Int'),
        	        'decorators' => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        	            array('Label', array('tag' => 'td')),
        	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide', 'style' => $display)),
        	             
        	        ),
        	    ));
	        } else{
                //ISPC-2565,Elena,06.04.2021
                $contactpersonArray = [];
                if(!empty($options[$box_division]['contacts'])){
                    $contactpersonArray = array_keys($options[$box_division]['contacts']);
                }

                $arr_for_multicheckbox = [];
                foreach($options['contact_persons_arr'] as $key => $val){
                    if($key !== 0){
                        $arr_for_multicheckbox[$key] = $val;
                    }
                }



        	    $subform->addElement('multiCheckbox',  'contactperson_master_id', array(//ISPC-2565,Elena,26.02.2021
        	        'value'        => $contactpersonArray,// //$options[$box_division]['contactperson_master_id'],//ISPC-2565,Elena,26.02.2021
        	        'label'        => $this->translate('contact person in charge') ,
        	        'required'     => false,
        	        'multiOptions' => $arr_for_multicheckbox,//$options['contact_persons_arr'],//ISPC-2565,Elena,26.02.2021
        	        'filters'      => array('StringTrim'),
        	        'validators'   => array('Int'),
        	        'decorators' => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        	            array('Label', array('tag' => 'td')),
        	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide', 'style' => $display)),
        	             
        	        ),
        	    ));
	        }
	    }
	     
	    $subform->addElement('text',  'comments', array(
	        'value'        => $options[$box_division]['comments'],
	        'label'        => $this->translate('where deposited'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide', 'style' => $display)),
	
	        ),
	    ));
	    $subform->addElement('text',  'file_date', array(
	        'value'        => null,
	        'label'        => $this->translate('from when'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'date inlineEdit_not_onFocusout',
	        'data-box'     => $box_division,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide', 'style' => $display)),
	        ),
	        'onSelect' => " if(this.value != '') {\$('.hide_uploader', \$(this).parents('table')).show()} else {\$('.hide_uploader', \$(this).parents('table')).hide()}",      //ISPC-2671 Lore 07.09.2020
	        
	    ));
	    
	    $subform->addElement('note',  'qq_file_uploader_label', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => $this->translate('Upload new template'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'qq_file_uploader_label')),
	           array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
	        ),
	    ));
	     
	    $subform->addElement('note',  'qq_file_uploader', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => '<noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript>',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 2,
	                'class' => 'qq_file_uploader_placeholder',
	                'data-parent' => 'table',
	                'data-tabname' => $box_division,
	            )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
	        ),
	    ));
	
	    if ( ! empty($options[$box_division]['files'])) {
	        /*list all files*/
	        $pat_enc_id =  ! empty($this->_patientMasterData['id_encrypted']) ? $this->_patientMasterData['id_encrypted'] : 0;
	        
	        foreach ($options[$box_division]['files'] as $file) {
	             
// 	            $filename = $cnt++ ? $this->translate('old version') : str_replace('ACP-Vorsorgevollmacht-', '', $file['title']) ;
	            $filename = $cnt++ ? $this->translate('old version') : $file['title'];
	            $filename = '<a href="stats/patientfileupload?doc_id='. $file['id'] . '&id=' . $pat_enc_id . '">' . $filename . "</a>";
	            
                $filedate= date("d.m.Y", strtotime($file['file_date']));
	                 
                $text .= <<<EOT
	                    <div class="fileitem">
	                    <div class="input">
	                    <span>{$filename}</span>
	                    <span class="filesize">{$filedate}</span>
	           </div>
	        </div>
EOT;
	                     
	        }
			$text = '<div class="fileupload">' . $text . '</div>';
	    		
	        			
	        $subform->addElement('note',  'files_label', array(
	            'label'        => null,
	            'required'     => false,
	            'value'        => $this->translate('file download'),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'files_label')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
	            ),
	        ));
	        
	        $subform->addElement('note',  'files', array(
	            'label'        => null,
	            'required'     => false,
	            'value'        => $text,
	            'escape'       => false,
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide dontPrint', 'style' => $display)),
	            ),
	        ));
	         
	    }
	    
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	     
	     
	}
	
/**
 * Lore 15.09.2020
 * @param array $options
 * @return Zend_Form
 */
	private function _tab_pluss($options =  array())
	{
	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	        
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->setAttrib("class", "label_same_size {$__fnName} ");
	    $subform->clearDecorators();
	    
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','class'=>' acp_table'));
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
	    
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	    
	    
	}
	
	
	/**
	 * ACP-Patientenverfügung
	 * 
	 * @param unknown $options
	 * @return Zend_Form
	 */
	private function _tab_living_will($options =  array()) 
	{
	     
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $box_division = 'living_will';
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate($box_division) );
	    $subform->setAttrib("class", "label_same_size {$__fnName} ");
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','class'=>' acp_table'));
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options[$box_division]['id'] ? $options[$box_division]['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
// 	    if (! empty($options[$box_division])) dd($options[$box_division]);
	
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options[$box_division]['id'] ? $options[$box_division]['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	             
	        ),
	    ));
	    $subform->addElement('hidden', 'division_tab', array(
	        'value'        => $box_division,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	        ),
	    ));
	     
	    $subform->addElement('radio',  'active', array(
	        'value'        => isset($options[$box_division]['active']) && ! empty($options[$box_division]['active']) ? $options[$box_division]['active'] : "",	         
	        'label'        => $this->translate('Status'),
	        'required'     => false,
	        'multiOptions' => PatientAcp::getDefaultRadios(),
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'living_will_radio',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('table')).show()} else {\$('.show_hide', \$(this).parents('table')).hide()}",
	         
	    ));
	    
	    $display = $options[$box_division]['active'] == 'yes' ? '' : 'display:none;';
	     
	    $subform->addElement('text',  'comments', array(
	        'value'        => $options[$box_division]['comments'],
	        'label'        => $this->translate('where deposited'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
	
	        ),
	    ));
	    $subform->addElement('text',  'file_date', array(
	        'value'        => null,
	        'label'        => $this->translate('from when'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'date inlineEdit_not_onFocusout',
	        'data-box'     => $box_division,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
	        ),
// 	        'onSelect' => "alert(this.value);  if(this.value == '') {\$('.uploader_tr', \$(this).parents('table')).addClass('hide_uploader')} else {\$('.uploader_tr', \$(this).parents('table')).removeClass('hide_uploader')}",
// 	        'onBlur' => "alert(this.value);  if(this.value == '') {\$('.uploader_tr', \$(this).parents('table')).addClass('hide_uploader')} else {\$('.uploader_tr', \$(this).parents('table')).removeClass('hide_uploader')}",
// 	        'onSelect' => "alert(this.value);  if(this.value != '') {\$('.uploader_tr', \$(this).parents('table')).show()} else {\$('.uploader_tr', \$(this).parents('table')).hide()}",
// 	        'onChange' => "alert(this.value);  if(this.value != '') {\$('.uploader_tr', \$(this).parents('table')).show()} else {\$('.uploader_tr', \$(this).parents('table')).hide()}",
	        'onSelect' => " if(this.value != '') {\$('.hide_uploader', \$(this).parents('table')).show()} else {\$('.hide_uploader', \$(this).parents('table')).hide()}",      //ISPC-2671 Lore 07.09.2020
//             'onChange' => 'date_condition(this)'
	    ));
	     
	    
	    
	    $subform->addElement('note',  'qq_file_uploader_label', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => $this->translate('Upload new template'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'qq_file_uploader_label')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
	        ),
	    ));
	    
	    
	    $subform->addElement('note',  'qq_file_uploader', array(
	        'label'        => null,
	        'required'     => false,
	        'value'        => '<noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript>',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan' => 2,
	                'class' => 'qq_file_uploader_placeholder',
	                'data-parent' => 'table',
	                'data-tabname' => $box_division,
	            )),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader file', 'style' => $display)),
	        ),
	    ));
	    
	    if ( ! empty($options[$box_division]['files'])) {
	        /*list all files*/
	        $pat_enc_id =  ! empty($this->_patientMasterData['id_encrypted']) ? $this->_patientMasterData['id_encrypted'] : 0;
	         
	        foreach ($options[$box_division]['files'] as $file) {
	    
// 	            $filename = $cnt++ ? $this->translate('old version') : str_replace('ACP-Patientenverfügung-', '', $file['title']) ;
	            $filename = $cnt++ ? $this->translate('old version') : $file['title'] ;
	            $filename = '<a href="stats/patientfileupload?doc_id='. $file['id'] . '&id=' . $pat_enc_id . '">' . $filename . "</a>";
	             
	            $filedate= date("d.m.Y", strtotime($file['file_date']));
	    
	            $text .= <<<EOT
	                    <div class="fileitem">
	                    <div class="input">
	                    <span>{$filename}</span>
	                    <span class="filesize">{$filedate}</span>
	           </div>
	        </div>
EOT;
	    
	        }
	        $text = '<div class="fileupload">' . $text . '</div>';
	         
	        $subform->addElement('note',  'files_label', array(
	            'label'        => null,
	            'required'     => false,
	            'value'        => $this->translate('file download'),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'files_label')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
	            ),
	        ));
	        
	        $subform->addElement('note',  'files', array(
	            'label'        => null,
	            'required'     => false,
	            'value'        => $text,
	            'escape'       => false,
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td',  'colspan'=>2 )),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide dontPrint', 'style' => $display)),
	            ),
	        ));
	         
	    } 
	    	     
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	     
	     
	}
	
	/**
	 * ACP-NotfallPlan - ISPC - 2129
	 *
	 * @param unknown $options
	 * @return Zend_Form
	 */
	private function _tab_emergencyplan($options =  array())
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		$box_division = 'emergencyplan';
	
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->setLegend($this->translate($box_division) );
		//$subform->setAttrib("class", "emergencyplan ");	
		$subform->setAttrib("class", "label_same_size {$__fnName} ");
		$subform->addDecorator('HtmlTag', array('tag' => 'table','class'=>' acp_table'));
		
		$subform->addElement('hidden', 'id', array(
	        'value'        => $options[$box_division]['id'] ? $options[$box_division]['id'] : 0 ,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	             
	        ),
	    ));
	    $subform->addElement('hidden', 'division_tab', array(
	        'value'        => $box_division,
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'active', array(
	    		'value'        => 'yes',
	    		'required'     => true,
	    		'filters'      => array('StringTrim'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2')),
	    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	    		),
	    ));
	    
	    //$display = $options[$box_division]['active'] == 'yes' ? '' : 'display:none;';
	    $display = 'yes';

	    $subform->addElement('text',  'file_date', array(
	        'value'        => null,
	        'label'        => $this->translate('from when'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'date inlineEdit_not_onFocusout',
	        'data-box'     => $box_division,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
	        ),
	        'onSelect' => " if(this.value != '') {\$('.hide_uploader', \$(this).parents('table')).show()} else {\$('.hide_uploader', \$(this).parents('table')).hide()}",      //ISPC-2671 Lore 07.09.2020
	        
	    ));
		 
		if ( ! empty($options[$box_division]['files'])) {
			/*list all files*/
			$pat_enc_id =  ! empty($this->_patientMasterData['id_encrypted']) ? $this->_patientMasterData['id_encrypted'] : 0;
			$text = '';
			$t = 0;
			foreach ($options[$box_division]['files'] as $file) {
				$filename = '<a href="stats/patientfileupload?doc_id='. $file['id'] . '&id=' . $pat_enc_id . '">' . $file['title'] . "</a>";
				$filedate= date("d.m.Y", strtotime($file['file_date']));
				
				if($file['active_version'] == '1')
				{
					$text .= <<<EOT
	                    <div class="fileitem  active_file">
	                    <div class="input">
	                    <span class="filename">{$filename}</span>
	                    <span>{$filedate}</span>
					
	    
EOT;
				}
				else 
				{
				$text .= <<<EOT
	                    <div class="fileitem">
	                    <div class="input">
	                    <span class="filename">{$filename}</span>
	                    <span>{$filedate}</span>
	           
	        
EOT;
				}
				$text = '<div class="fileupload">' . $text;
				$fileelem = $subform->createElement('note',  'files', array(
						'label'        => null,
						'required'     => false,
						'value'        => $text,
						'escape'       => false,
						'decorators' => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class' => 'show_hide', 'style' => $display)),
						),
				));
				$subform->addElement($fileelem,'file_arr_'.$t++);		
				
				
				
				$setact = $subform->createElement('Checkbox', 'active_version', array(
						'isArray'	   => true,
						'value' => $file['active_version'],
						'required'     => false,
						'onchange' => 'if(this.checked)	{$(\'input[name="patientDetails[PatientAcp][emergencyplan][active_version][]"]\').not(this).prop("checked", false); $("#isnotupload").val("1"); $(\'input[name="patientDetails[PatientAcp][emergencyplan][active_version][]"]\').not(this).closest("td").prev("td").find(\'input[type="hidden"]\').removeAttr("disabled");  $(this).closest("td").prev("td").find(\'input[type="hidden"]\').attr("disabled", "disabled"); } else	{$(this).closest("td").prev("td").find(\'input[type="hidden"]\').removeAttr("disabled"); }',
						'decorators' => array(
								'ViewHelper',
								array('Label', array('placement' => 'APPEND')),
								array(array('ispan' => 'HtmlTag'), array('tag'=>'span', 'class'=>'filecheck')),
								array(array('idiv' => 'HtmlTag'), array('tag'=>'div', 'class'=>'input', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('mdiv' => 'HtmlTag'), array('tag'=>'div', 'class'=>'fileitem', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('ediv' => 'HtmlTag'), array('tag'=>'div', 'class'=>'fileupload', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
						),
				
				));
				$subform->addElement($setact,'act_arr_'.$t++);
				
				$fid = $subform->createElement('hidden', 'file_id', array(
						'isArray'	   => true,
						'value'        => $file['id'],
						'required'     => true,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
						),
				));
				$subform->addElement($fid,'fid_arr_'.$t++);
		
				$setacth = $subform->createElement('hidden', 'active_version', array(
						'isArray'	   => true,
						'value' => '0',
						'required'     => false,
						'decorators' => array(
								'ViewHelper',
								array('Label', array('placement' => 'APPEND')),
								array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'colspan'=>'2', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class' => 'show_hide', 'style' => $display)),
						),
		
				));
				$subform->addElement($setacth,'act_arr_h'.$t++);
				
				$text = '';
			}
		}
		
		
		
		
		$subform->addElement('note',  'qq_file_uploader_label', array(
				'label'        => null,
				'required'     => false,
				'value'        => $this->translate('Upload new template'),
				'decorators' => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2', 'class' => 'qq_file_uploader_label')),
				        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
				),
		));
		
		$subform->addElement('note',  'qq_file_uploader', array(
				'label'        => null,
				'required'     => false,
				'value'        => '<noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript>',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2',
								'class' => 'qq_file_uploader_placeholder',
						        'data-parent' => 'table',
								'data-tabname' => $box_division,
						)),
				        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint uploader_tr_'.$box_division.' hide_uploader', 'style' => $display)),
				),
		));
		
		$subform->addElement('hidden', 'isactive', array(
				'value'        => '0',
				'required'     => true,
				'filters'      => array('StringTrim'),
				'id' => 'isactive',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2')),
				    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint', 'style' => 'display:none' )),
				),
		));
		
		$subform->addElement('hidden', 'isnotupload', array(
				'value'        => '0',
				'required'     => true,
				'filters'      => array('StringTrim'),
				'id' => 'isnotupload',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2')),
				    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'dontPrint', 'style' => 'display:none' )),
				),
		));
			
		return $this->filter_by_block_name($subform, __FUNCTION__);
	
	
	}
	
	public function save_form_acp($ipid =  null , $data = array())
	{
	    if (empty($ipid) || ! is_array($data)) {
	        return;
	    }
// 	    $controller = Zend_Controller_Front::getInstance();
	    
	    $contactperson_master_id = $data['contactperson_master_id'];
	    
	    if ($data['active'] != 'yes') {
	        $data['contactperson_master_id'] = null;
	    }
	    
	    $entity = new PatientAcp();
	    $result = $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);

	    
	    $this->_save_box_History($ipid, $result, 'active', 'grow6', $data['division_tab']);
	    $this->_save_box_History($ipid, $result, 'contactperson_master_id', 'grow6', $data['division_tab']);
	    $this->_save_box_History($ipid, $result, 'comments', 'grow6', $data['division_tab']);
	    
	    // update contact person master cnt_hatversorgungsvollmacht & cnt_legalguardian
	    if ($data['division_tab'] == "healthcare_proxy" || $data['division_tab'] == "care_orders")
	    {
	        $cnt_field = $data['division_tab'] == "healthcare_proxy" ? "cnt_hatversorgungsvollmacht" : "cnt_legalguardian";
	         
	        if ($contactperson_master_id > 0) {
	             
	            if($data['active'] == 'yes'){
	                //disable all others
	                Doctrine_Query::create()
	                ->update('ContactPersonMaster')
	                ->set($cnt_field, 0)
	                ->where("ipid= ? ", $ipid)
	                ->execute();
	            }
	             
	            $q = Doctrine_Query::create()
	            ->update('ContactPersonMaster')
	            ->set($cnt_field, ($data['active'] == 'yes' ? 1 : 0))
	            ->where("id = ? ", $contactperson_master_id)
	            ->andWhere("ipid = ?", $ipid)
	            ->execute();
	        } else {
	            //no contactperson is selected in this tab
	            $getLastModified = $result->getLastModified(true);
	            if (isset($getLastModified['contactperson_master_id']) && $getLastModified['contactperson_master_id'] > 0) {
	                $q = Doctrine_Query::create()
	                ->update('ContactPersonMaster')
	                ->set($cnt_field, 0)
	                ->where("id = ? ", $getLastModified['contactperson_master_id'])
	                ->andWhere("ipid = ?", $ipid)
	                ->execute();
	            }
	        }
	    }
	    
	   
        //now process the files save
        if (is_array($data['qquuid'])) {
            
            $action =  $data['division_tab'];
            $tabname = "acp_file_". $action;
            $recordid = '';
            $file_date = $data['file_date'];                
            $title_prefix = ''; // a title prefix if formed from translating the tabname
            if ( ! empty($tabname) ) {
                $tabname_tr = $this->translate($tabname);
                $title_prefix = (! empty($tabname_tr) && is_string($tabname_tr)) ? $tabname_tr : '';
            }
            
            $filesave_options = array(
                'ipid'		=> $ipid,
                'qquuid'	=> array(),
                'remove_after_save'	=> true, //remove files from local hdd after success save on ftp
                'options'	=> array(
                    'file_details' => array(),
                    'extra options go here' => array(), // this is for demo
                    'extra options2 go here' => "",// this is for demo
                ),
            );
            $filesave_options['qquuid'] = $data['qquuid'];
            $filesave_options['options']['file_details'] = array(
                'tabname'		=> $tabname,
                'recordid'		=> $recordid,
                'title_prefix'	=> $title_prefix,
                'file_date'		=> $file_date,
            );
            
            $result_filesave = $this->qqSaveFiles( $action,  $filesave_options);
            

        }
	
        return $result;
	}
	
	
	/**
	 * changed to return [Doctrine_Record] , with all the division tabs
	 * @param string $ipid
	 * @param unknown $data
	 * @return void|multitype:Doctrine_Record
	 */
	public function save_form_acp_all_tabs($ipid =  null , $data = array())
	{
	    if (empty($ipid) || ! is_array($data)) {
	        return;
	    }

	    $entity = new PatientAcp();
	    
	    $return = [];

	    foreach ($data as $division_tab) {
	        
	        if ( ! is_array($division_tab) || ! isset($division_tab['division_tab'])) {
	            continue;
	        }
	        
	        if (empty($division_tab['active'])) {
	            $division_tab['active'] =  null;
	        }
	        
	        $contactperson_master_id = $division_tab['contactperson_master_id'];
	        if ($division_tab['active'] != 'yes') {
	            $division_tab['contactperson_master_id'] = 0;
	        }
            //ISPC-2565,Elena,26.02.2021
	        $changedData = [];
	        if( $division_tab['division_tab'] == "healthcare_proxy" || $division_tab['division_tab'] == "care_orders"){
	            /*
	             * earlier, we had only one person with this option.
	             * That's why, we had only one record for patient, with the person (contactperson_master_id) AND activity status
	             * ('nicht bekannt', 'nicht vorhanden', 'ist vorhanden', 'nicht gewollt')
	             * Now, we make multiple persons possible
	             * it means, we need more records for person/option.
	             * i decide to keep a record with contactperson_master_id 0 and activity status, it'll be the record specially for activity_status of option for patient.
	             * the files are saved with recordid of this record too
	             * the records with contactperson_master_id > 0 are for persons and their activity status (ISPC-2565,Elena,26.02.2021)
	             */

	            $arrSaved = $data['savedAcp'][0][$division_tab['division_tab']];
                $contactperson_master_id = null;
                $isActive = $arrSaved['active'];
                $division_tab['data_found'] = false;
                $savedContactIds = array_keys($arrSaved['contacts']);
                $dataContactIds = $data[$division_tab['division_tab']]['contactperson_master_id'];

                //is this record new?
                foreach($dataContactIds as  $cdata){
                    if(!in_array($cdata, $savedContactIds)){
                        $contactperson_master_id = $cdata;
                        $isActive = true;
                        $division_tab['data_found'] = true;
                        $changedData[] = [
                            'contactperson_master_id' => $cdata,
                            'active' => 'yes'
                        ];
                    }
                }
                //is this record deleted?
                foreach($savedContactIds as  $cdata){
                    if(!in_array($cdata, $dataContactIds)){
                        $contactperson_master_id = $cdata;
                        $isActive = false;
                        $division_tab['data_found'] = true;
                        $changedData[] = [
                            'contactperson_master_id' => $cdata,
                            'active' => 'no'
                        ];
                    }
                }
                //is active changed from 'yes'?
                //if active is no more 'yes', deactivate persons
                /*

                if(($arrSaved['active'] == 'yes') && ($division_tab['division_tab']['active'] != 'yes')){
                    foreach($savedContactIds as  $cdata) {

                        $contactperson_master_id = $cdata;
                        //$isActive = false;
                        $division_tab['data_found'] = true;
                        $changedData[] = [
                            'contactperson_master_id' => $cdata,
                            'active' => 'no'
                        ];
                    }


                }*/
                $division_tab['contactperson_master_id'] = $contactperson_master_id;
                //$division_tab['active']  = ($isActive) ? 'yes' : 'no';



            }
	        
	        //Ancuta 14.09.2020
	        if (!empty($division_tab['file_date'])) {
	           $division_tab['file_date'] =  date('Y-m-d',strtotime($division_tab['file_date']));
	        } else{
	           $division_tab['file_date'] =  date('Y-m-d',time());
	        }
	        //-- 

	        //bugfix 15.08.2018, in stammdaten this box's html is not replaced when ajax...
	        if (empty($division_tab['id'])) {
                //ISPC-2565,Elena,26.02.2021

                if( $division_tab['division_tab'] == "healthcare_proxy" || $division_tab['division_tab'] == "care_orders"){
                    if($division_tab['data_found']){
                        foreach($changedData as $changed){
                            //$division_tab['active'] = $changed['active'];
                            $div_tab_contact = $division_tab;
                            $div_tab_contact['active'] = $changed['active'];
                            $result = $entity->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $div_tab_contact['division_tab'], $changed['contactperson_master_id'], $div_tab_contact);
                            $multiply_result[] = $result;

                            $this->_save_box_History($ipid, $result, 'active', 'grow6', $division_tab['division_tab']);
                            $this->_save_box_History($ipid, $result, 'contactperson_master_id', 'grow6', $division_tab['division_tab'], $changed['contactperson_master_id']);
                            $this->_save_box_History($ipid, $result, 'comments', 'grow6', $division_tab['division_tab']);


                        }

                    }
                    if(isset($division_tab['active'] ) || isset($arrSaved['active']) ){
                        //if active  was set and is unset now, it's a change too and have to be saved
                        if(!isset($division_tab['active'])){
                           $division_tab['active'] = '';
                        }
                        if( !isset($arrSaved['active'])){
                            $arrSaved['active'] = '';
                        }
                        if($division_tab['active'] != $arrSaved['active'] || $division_tab['comments'] != $arrSaved['comments'] ){
                            $result = $entity->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab['division_tab'], 0, $division_tab);
                            $this->_save_box_History($ipid, $result, 'active', 'grow6', $division_tab['division_tab']);
                        }

                    }

                }else{
                    $result = $entity->findOrCreateOneByIpidAndDivisionTab($ipid, $division_tab['division_tab'], $division_tab);
                }//ISPC-2565,Elena,26.02.2021


	        } else {
    	        $result = $entity->findOrCreateOneByIpidAndId($ipid, $division_tab['id'], $division_tab);
	        }
	        
	        
	        
	        // update contact person master cnt_hatversorgungsvollmacht & cnt_legalguardian
	        if ($division_tab['division_tab'] == "healthcare_proxy" || $division_tab['division_tab'] == "care_orders")
	        {
	            $cnt_field = $division_tab['division_tab'] == "healthcare_proxy" ? "cnt_hatversorgungsvollmacht" : "cnt_legalguardian";
                //ISPC-2565,Elena,26.02.2021
                foreach($changedData as $changed){
                    $q = Doctrine_Query::create()
                        ->update('ContactPersonMaster')
                        ->set($cnt_field, ( ($changed['active'] == 'yes') ? 1 : 0))
                        ->where("id = ? ", $changed['contactperson_master_id'])
                        ->andWhere("ipid = ?", $ipid)
                        ->execute();

                }


	        }
            //ISPC-2565,Elena,26.02.2021
	        if(empty($multiply_result)){

                $this->_save_box_History($ipid, $result, 'active', 'grow6', $division_tab['division_tab']);
                $this->_save_box_History($ipid, $result, 'contactperson_master_id', 'grow6', $division_tab['division_tab']);
                $this->_save_box_History($ipid, $result, 'comments', 'grow6', $division_tab['division_tab']);

            }

	        
	        
	        
	        //now process the files save
	        if (isset($division_tab['qquuid'])) {
	        
	            $action =  $division_tab['division_tab'];
	            $tabname = "acp_file_". $action;
	            $recordid = $result->id;
                //ISPC-2565,Elena,26.02.2021
                if ($division_tab['division_tab'] == "healthcare_proxy" || $division_tab['division_tab'] == "care_orders")
                {
                    $res = $entity->findOrCreateOneByIpidAndDivisionTabAndContactPerson($ipid, $division_tab['division_tab'], 0, $division_tab);
                    $recordid = $res->id;

                }
	            $file_date = $division_tab['file_date'];
	            $title_prefix = ''; // a title prefix if formed from translating the tabname
	            if ( ! empty($tabname) ) {
	                $tabname_tr = $this->translate($tabname);
	                $title_prefix = (! empty($tabname_tr) && is_string($tabname_tr)) ? $tabname_tr : '';
	            }
	        
	            $filesave_options = array(
	                'ipid'		=> $ipid,
	                'qquuid'	=> array(),
	                'remove_after_save'	=> true, //remove files from local hdd after success save on ftp
	                'options'	=> array(
	                    'file_details' => array(),
	                    'extra options go here' => array(), // this is for demo
	                    'extra options2 go here' => "",// this is for demo
	                ),
	            );
	            $filesave_options['qquuid'] = is_array($division_tab['qquuid']) ? $division_tab['qquuid'] : [$division_tab['qquuid']];
	            
	        if($data[$action]['isactive'] != '1') //ISPC - 2129
            {
	            $filesave_options['options']['file_details'] = array(
	                'tabname'		=> $tabname,
	                'recordid'		=> $recordid,
	                'title_prefix'	=> $title_prefix,
	                'file_date'		=> $file_date,
	            );
            }
            else 
            {
            	$filesave_options['options']['file_details'] = array(
            			'tabname'		=> $tabname,
            			'recordid'		=> $recordid,
            			'title_prefix'	=> $title_prefix,
            			'file_date'		=> $file_date,
            			'isactive'      => $data[$action]['isactive']
            	);
            	
            }
	        
	            $result_filesave = $this->qqSaveFiles( $action,  $filesave_options);
	        
	        
	        }
	        else
	        {
	        	//ISPC - 2129
	        	if($division_tab['division_tab'] == 'emergencyplan')
	        	{
	        		$factv = new PatientFileVersion();
	        		$ractv = $factv->get_reset_active_version($ipid);
	        	
	        		foreach($division_tab['active_version'] as $kact=>$vact)
	        		{
	        			if($vact == '1')
	        			{
	        				$actfilevers = $division_tab['file_id'][$kact];
	        				$connact = $factv->getTable()->getConnection();
	        				$factt = new PatientFileVersionTable('', $connact);
	        				$act_data['file'] = $actfilevers;
	        				$act_data['active_version'] = '1';
	        				$actf = $factt->findOrCreateOneBy('file', $actfilevers, $act_data);
	        				break;
	        			}
	        		}
	        	}
	        }
	        
	        $return[$division_tab['division_tab']] = $result;
	        
	    }
	    
	     
	    return $return;
// 	    return $result;
	}
	
	
	

	private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $division_tab , $mandatory_field_value = null)//ISPC-2565,Elena,26.02.2021
	{
	
	    $newModifiedValues = $newEntity->getLastModified();
        //ISPC-2565,Elena,26.02.2021
        // if i don't delete record but mark its field active / not active, contactperson_master_id won't to be written to history (isn't new)
        //that's why workaround
	    if (!isset($newModifiedValues[$fieldname]) && !empty($mandatory_field_value)){
                $newModifiedValues[$fieldname] = $mandatory_field_value ;
        }
	    
	    if (isset($newModifiedValues[$fieldname])) {
	        
	        $new_values = $newModifiedValues[$fieldname];

	        $history = [
	            'ipid' => $ipid,
	            'clientid' => $this->logininfo->clientid,
	            'formid' => $formid,
	            'fieldname' => json_encode(array( "division_tab"=>$division_tab, 'fieldname'=>$fieldname)),
	            'fieldvalue' => $new_values,
	        ];
	        
	        $newH = new BoxHistory();
	        $newH->fromArray($history);
	        $newH->save();
	
	    }
	
	}
	
}

