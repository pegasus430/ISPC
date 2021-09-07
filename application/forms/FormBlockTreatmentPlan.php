<?php

// require_once("Pms/Form.php");
/**
 *
 *
 * @created Nov 12, 2018: @author carmen
 * Behandlungsplan = FormBlockTreatmentPlan
 */

class Application_Form_FormBlockTreatmentPlan extends Pms_Form 
{
	protected $_model = 'FormBlockTreatmentPlan';
	
	//define the name and id, if you want to piggyback some triggers
	private $triggerformid = FormBlockTreatmentPlan::TRIGGER_FORMID;
	private $triggerformname = FormBlockTreatmentPlan::TRIGGER_FORMNAME;
	
	//define this if you grouped the translations into an array for this form
	protected $_translate_lang_array = FormBlockTreatmentPlan::LANGUAGE_ARRAY;
	
	
	protected $_block_name_allowed_inputs =  array();
	protected $_block_feedback_options =  array();
	
	public function create_form_formblocktreatmentplan( $options = array(), $elementsBelongTo = null )
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		
		$this->mapValidateFunction($__fnName , "create_form_isValid");
		
		$this->mapSaveFunction($__fnName , "save_form_treatmentplan");
		
		$this->clearDecorators();
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => '', 'style' => 'border: 0px; padding: 0px; font-size: 12px;'));
		//$this->addDecorator('Form');
		
	    if ( ! is_null($elementsBelongTo)) {
	        $this->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }    
	
	    
	    if($options['formular_type'] == 'pdf')
	    {
	    	$subform = new Zend_Form_SubForm();
	    	//$subform->removeDecorator('Fieldset');
	    	$subform->setDecorators( array(
	    			'FormElements',
	    			array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>'width: 100%;')),
	    			//'Fieldset',
	    	));
	    	
	    	$subform->addElement('note', 'label_blank', array(
	    			'value' => '&nbsp;',
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'style' => 'width: 28%; border: 1px solid #000; padding: 5px;'
	    					)),
	    					array(array('row' => 'HtmlTag'), array(
	    							'tag'      => 'tr',
	    							'openOnly' => true,
	    					)),
	    			),
	    	
	    	));
	    	
	    	$subform->addElement('note', 'label_goal', array(
	    			'value' => $this->translate('label_goal'),
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'style' => 'width: 35%; border: 1px solid #000; padding: 5px;'
	    					))
	    	
	    			),
	    	));
	    	
	    	$subform->addElement('note', 'label_plan', array(
	    			'value' => $this->translate('label_plan'),
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'style' => 'width: 35%; border: 1px solid #000; height: 20px; padding: 5px;'
	    					)),
	    					array(array('row' => 'HtmlTag'), array(
	    							'tag'      => 'tr',
	    							'closeOnly' => true,
	    					)),
	    			),
	    	
	    	));
	    	
	    	foreach ($options as $krow=>$row)
	    	{
		    	foreach($row['colprop']['values'] as $kcol=>$col)
		    	{
		    		$subform->addElement('note', 'label_'.$krow, array(
		    				'value' => $this->translate('label_'.$krow),
		    				'decorators' => array(
		    						'ViewHelper',
		    						array(array('data' => 'HtmlTag'), array(
		    								'tag' => 'td',
		    								'style' => 'width: 28%; border: 1px solid #000; height: 20px; padding: 5px;'
		    						)),
		    						array(array('row' => 'HtmlTag'), array(
		    								'tag'      => 'tr',
		    								'openOnly' => true,
		    						)),
		    				),
		    					
		    		));
		    		 
		    		 
		    		if($kcol == 1)
		    		{
		    			$subform->addElement('note', $krow.$kcol.'text', array(
		    					'value' => $row['value'][$kcol] ? nl2br($row['value'][$kcol]) : '&nbsp;',
		    					'decorators' => array(
		    							'ViewHelper',
		    							array(array('data' => 'HtmlTag'), array(
		    									'tag' => 'td',
		    									'style' => 'width: 35%; border: 1px solid #000; height: 20px;padding-left: 5px;'
		    							)),
		    							array(array('row' => 'HtmlTag'), array(
		    									'tag'      => 'tr',
		    									'closeOnly' => true,
		    							)),
		    					),
		    			));
		    		}
		    		else
		    		{
		    			$subform->addElement('note', $krow.$kcol.'text', array(
		    					'value' => $row['value'][$kcol] ? nl2br($row['value'][$kcol]) : '&nbsp;',
		    					'decorators' => array(
		    							'ViewHelper',
		    							array(array('data' => 'HtmlTag'), array(
		    									'tag' => 'td',
		    									'style' => 'width: 35%; border: 1px solid #000;padding-left: 5px;'
		    							))
		    	
		    					),
		    			));
		    		}
		    	}
	    	}
	    	$this->addSubForm($subform, 'tr_table');
	    }
	    else 
	    {	    	
	    	$this->addElement('note', 'label_blank', array(
	    			'value' => '&nbsp;',
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 28%; display: inline-block; vertical-align: top;')),
	    					array(array('ediv' => 'HtmlTag'), array(
	    							'tag' => 'div',
	    							'style' => $options['formular_type'] == 'pdf' ? 'width: 100%; border: 1px solid #000;' : 'width: 100%;',
	    							'openOnly' => true,
	    							'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	    					)
	    			),
	    	));
	    	$this->addElement('note', 'label_goal', array(
	    			'value' => $this->translate('label_goal'),
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 35%;  display: inline-block; border-left: 1px solid #000;' : 'width: 35%;  display: inline-block;')),
	    			),
	    	));
	    	$this->addElement('note', 'label_plan', array(
	    			'value' => ($options['formular_type'] == 'pdf' ? $this->translate('label_plan') : $this->translate('label_measure')),
	    			'decorators' => array(
	    					'ViewHelper',
	    					array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 35%;  display: inline-block; border-left: 1px solid #000;' : 'width: 35%;  display: inline-block;')),
	    					array(array('ediv' => 'HtmlTag'), array(
	    							'tag' => 'div',
	    							'style' => $options['formular_type'] == 'pdf' ? 'width: 100%; border: 1px solid #000;' : 'width: 100%;',
	    							'closeOnly' => true,
	    							'placement' => Zend_Form_Decorator_Abstract::APPEND),
	    					)
	    			),
	    	));
	    	
	    	foreach ($options as $krow=>$row)
	    	{
		    	if($krow == 'ipid' || $krow == 'contact_form_id')
		    	{
		    		$this->addElement('hidden', $krow, array(
		    				'value' => $row['value'],
		    				'readonly' => true,
		    				'decorators' => array(
		    						'ViewHelper',
		    						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
		    				),
		    		));
		    	}
		    	else 
		    	{
		    		
		    		$t = 0;
		    		$subform = new Zend_Form_SubForm();
		    		$subform->setElementsBelongTo("treatment_plan");
		    		$subform->clearDecorators()
		    		->setDecorators( array(
		    				'FormElements',
		    		));
		    		$elementsBelongTo = $subform->getElementsBelongTo();
		    		
		    		foreach($row['colprop']['values'] as $kcol=>$col)
		    		{	  
		    			$subform->addElement('note', 'label_'.$krow, array(
		    					'value' => $this->translate('label_'.$krow),
		    					'decorators' => array(
		    							'ViewHelper',
		    							array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 28%;  display: inline-block; vertical-align: top; border-left: 1px solid #000; border-rif: 1px solid #000;' : 'width: 28%;  display: inline-block; vertical-align: top;')),
		    							array(array('ediv' => 'HtmlTag'), array(
		    									'tag' => 'div',
		    									'style' => $options['formular_type'] == 'pdf' ? 'width: 100%; border: 1px solid #000; page-break-inside: avoid;' : 'width: 100%;',
		    									'openOnly' => true,
		    									'placement' => Zend_Form_Decorator_Abstract::PREPEND),
		    							)
		    					),
		    			));
		    			
		    			
		    			if($kcol == 1)
		    			{
		    				
		    				$empty = $subform->createElement('textarea', $krow.'[]', array(
		    						'isArray' => true,
		    						'value'        => $row['value'][$kcol],
		    						//'label'        => $this->translate($col),
		    						'required'     => false,
		    						'filters'      => array('StringTrim'),
		    						'id' => $elementsBelongTo.'-'.$krow.'-'.$kcol,
		    						'decorators' => array(
		    								'ViewHelper',
		    								//array('Label', array('tag' => 'div' , 'placement' => 'PREPEND', 'style' => 'width: 100%;')),
		    								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 35%;  display: inline-block; border-left: 1px solid #000;' : 'width: 35%;  display: inline-block;')),
		    								array(array('ediv' => 'HtmlTag'), array(
		    										'tag' => 'div',
		    										'style' => $options['formular_type'] == 'pdf' ? 'width: 100%; border: 1px solid #000;' : 'width: 100%;',
		    										'closeOnly' => true,
		    										'placement' => Zend_Form_Decorator_Abstract::APPEND),
		    								)
		    						),
		    						'style' => 'width: 95%;',
		    						'rows' => "2"
		    				));
		    			}
		    			else 
		    			{
				    		$empty = $subform->createElement('textarea', $krow.'[]', array(
				    				'isArray' => true,
				    				'value'        => $row['value'][$kcol],
				    				//'label'        => $this->translate($col),
				    				'required'     => false,
				    				'filters'      => array('StringTrim'),
				    				'id' => $elementsBelongTo.'-'.$krow.'-'.$kcol,
				    				'decorators' => array(
													'ViewHelper',
				    								//array('Label', array('tag' => 'div' , 'placement' => 'PREPEND', 'style' => 'width: 100%;')),
													array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 35%;  display: inline-block; border-left: 1px solid #000;' : 'width: 35%;  display: inline-block;')),
											),
									'style' => 'width: 95%;',
				    				'rows' => "2"
				    		));
		    			}
			    		$subform->addElement($empty, $row.'_arr_'.$t++);
		    		
		    		
		    		}
		    		$this->addSubForm($subform, $krow);
		    	}
	    	}
	    }
	//return $this;
	return $this->filter_by_block_name($this , __FUNCTION__);
	}
	
	
	
	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FormBlockTreatmentPlan')
			->set('isdelete', '1')
			->where("contact_form_id= ?", $contact_form_id)
			->andWhere('ipid = ?', $ipid);
			$result = $Q->execute();
	
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function InsertData($post, $allowed_blocks)
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		//var_dump($allowed_blocks); exit;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
	
		if (empty($post['ipid'])) {
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
		} else {
			$ipid = $post['ipid'];
		}
	
		$save_2_PC = false; //if we have insert or update on PatientCourse
	
		$treatment_plan_block = new FormBlockTreatmentPlan();
		//var_dump($post['old_contact_form_id']); exit;
		if ( ! empty($post['old_contact_form_id'])) {			
			$treatment_plan_old_data = $treatment_plan_block->getPatientFormBlockTreatmentPlan($ipid, $post['old_contact_form_id']);
	
			if ( ! in_array('treatment_plan', $allowed_blocks) ) {
				// override post data if no permissions on block
				// PatientCourse will NOT be inserted
				if ( ! empty($treatment_plan_old_data)) {
					$array_data = $treatment_plan_old_data[0];
					$clear_block_entryes = $this->clear_block_data($ipid, $post['old_contact_form_id']);
				}
			}
			else {
				//we have permissions and cf is being edited
				//write changes in PatientCourse if something was changed
				if ( ! empty($treatment_plan_old_data)) {
					$fields_block = $treatment_plan_block->getModified();
					
					if(!empty($fields_block))
					{
						//something was edited, we must insert into PC
						$save_2_PC = true;
					}
					//set the old block values as isdelete
					$clear_block_entryes = $this->clear_block_data($ipid, $post['old_contact_form_id']);
				}
				else {
					//nothing was edited last time, or this block was added after the form was created
					$save_2_PC = true;					 
				}				
				$array_data = $post['treatment_plan'];
				$array_data['contact_form_id'] = $post['contact_form_id'];
				$array_data['ipid'] = $ipid;
			}
		} else {
			//new cf, save
			$array_data = $post['treatment_plan'];
			$array_data['contact_form_id'] = $post['contact_form_id'];
			$array_data['ipid'] = $ipid;
			
			$save_2_PC = true;
		}
		
		if($array_data)
		{
			/* $coursecomment = 'test';
			
			$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
			
			if ($save_2_PC
					&& in_array('treatment_plan', $allowed_blocks)
					&& ! empty($coursecomment)
					&& ($pc_listener = $treatment_plan_block->getListener()->get('PostInsertWriteToPatientCourse')) )
			{
				$change_date = "";//removed from pc; ISPC-2071		
				$pc_listener->setOption('disabled', true);		
				$pc_listener->setOption('course_title', $coursecomment . $change_date);
				$pc_listener->setOption('done_date', $done_date);
				$pc_listener->setOption('user_id', $userid);
			} */
		
		//if($array_data)
		//{
			$conn = $treatment_plan_block->getTable()->getConnection();		
			$entity  = new FormBlockTreatmentPlanTable('',$conn);
			$record = $entity->createIfNotExistsOneBy('contact_form_id', $post['contact_form_id'], $array_data);
		}
				
	}
	
}

?>