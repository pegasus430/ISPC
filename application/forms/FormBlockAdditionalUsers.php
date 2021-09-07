<?php

// require_once("Pms/Form.php");
/**
 *  
 *  
 * @update Jan 22, 2018: @author claudiu, checked for ISPC-2071
 * Beteiligte Mitarbeiter =  FormBlockAdditionalUsers
 
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 * fixed: this would insert PC each time you saved
 *
 */
class Application_Form_FormBlockAdditionalUsers extends Pms_Form
{
	protected $_model = 'FormBlockAdditionalUsers';
	
	protected $_cl_users = null;
	//define the name and id, if you want to piggyback some triggers
	/*private $triggerformid = FormBlockAdditionalUsers::TRIGGER_FORMID;
	private $triggerformname = FormBlockAdditionalUsers::TRIGGER_FORMNAME;
	
	//define this if you grouped the translations into an array for this form
	protected $_translate_lang_array = FormBlockAdditionalUsers::LANGUAGE_ARRAY;
	
	
	protected $_block_name_allowed_inputs =  array();
	protected $_block_feedback_options =  array();*/
	
	public function __construct($options = null)
	{
		parent::__construct($options);

		if (isset($options['_cl_users'])) {
			$this->_cl_users = $options['_cl_users'];
			unset($options['_cl_users']);
		}
		
	
	}
    
	public function clear_block_data($ipid = '', $contact_form_id = 0 )
	{
		if ( ! empty($contact_form_id))
		{
			Doctrine_Query::create()
			->update('FormBlockAdditionalUsers')
			->set('isdelete', '1')
			->where("contact_form_id = ?", $contact_form_id)
			->andWhere('ipid = ?', $ipid)
			->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post,$allowed_blocks)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		
		$records = array();
		
		$addt_users_block = new FormBlockAdditionalUsers();

		$save_2_PC =  false ;// 2 save or not 2 save into PatientCourse
		
		$selected_user_ids =  array();
		foreach($post['additional_users'] as $kuser => $vuser) {
		
		    if ($vuser['value'] > 0) {
		        $selected_user_ids [] = $kuser;
		    }
		}
		$allUserArray = User::getUsersNiceName($selected_user_ids, $logininfo->clientid);
		
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'].' '.$post['begin_date_h'].':'.$post['begin_date_m'].':'.date('s', time())));

		$aditional_users="";

		foreach ($post['additional_users'] as $kuser => $vuser) {
		    
			if ($post['additional_users'][$kuser]['value'] > 0) {
			    
			    $added_users[] = $kuser;
			    
				$records[] = array(
						"ipid" => $post['ipid'],
						"contact_form_id" => $post['contact_form_id'],
						"additional_user" => $kuser,
						"creator" => $post['additional_users'][$kuser]['creator']
				);
				$aditional_users .= $allUserArray[$kuser]['nice_name'] . '; ';
			}
		}

		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);

		$change_date = "";
		
		if ( ! empty($post['old_contact_form_id'])) {
		    
			$change_date = $post['contact_form_change_date'];
			
			$adt_users_old = $addt_users_block->getPatientFormBlockAdditionalUsers($post['ipid'], $post['old_contact_form_id'], true);
			
			// override post data if no permissions on additional_users block
			// PatientCourse will NOT be inserted
			if ( ! in_array('additional_users', $allowed_blocks)) {
			    
    			
    			if ($adt_users_old) {
    			    
					$records = array();
					foreach ($adt_users_old as $key => $u_value)
					{
						$records[] = array(
								"ipid" => $post['ipid'],
								"contact_form_id" => $post['contact_form_id'],
								"additional_user" => $u_value['additional_user'],
								"creator" => $u_value['creator']
						);
					}
    			}
			}
			else {
			    
    			if ( ! empty($adt_users_old)) {
    			    
    			    $old_additional_user_ids = array_column($adt_users_old, 'additional_user');
    			    $diff1 = array_diff($old_additional_user_ids, $selected_user_ids);
    			    $diff2 = array_diff($selected_user_ids, $old_additional_user_ids);
    			    
    			    if ( ! empty($diff1) || ! empty($diff2)) {
    			        //something was edited, we must insert into PC
    			        $save_2_PC = true;
    			    }
    			    
    			}
    			else {
    			    //nothing was edited last time, or this block was added after the form was created
    			    $save_2_PC = true;
    			    $change_date = '';
    			
    			}
			}
			
		} 
		else {
		    //new cf, save
		    $save_2_PC = true;
		}

		if ( ! empty($records)) {
        	$collection = new Doctrine_Collection('FormBlockAdditionalUsers');
        	$collection->fromArray($records);
        	$collection->save();
		}
		
		
		//TODO-4069 Ancuta 26.04.2021
		$modules = new Modules();
		$companion_time_tracking = 0;
		if($modules->checkModulePrivileges("254", $clientid))
		{
		    $save_2_PC = false;
		}
		//-- 
		

		if ($save_2_PC 
		    && in_array('additional_users', $allowed_blocks)
		    && ! empty($aditional_users))
		{
		    $change_date = "";//removed from pc; ISPC-2071 
			$course_save = new PatientCourse();
			
			$course_save->triggerformid = null; //bypass Trigger();
			
			$course_save->ipid = $post['ipid'];
			$course_save->course_date = date("Y-m-d H:i:s", time());
			$course_save->course_type = Pms_CommonData::aesEncrypt("K");
			$course_save->course_title = Pms_CommonData::aesEncrypt('Beteiligte Mitarbeiter: '.htmlspecialchars(addslashes($aditional_users)).$change_date);
			$course_save->user_id = $userid;
			$course_save->done_date = $done_date;
			$course_save->done_name = Pms_CommonData::aesEncrypt("contact_form");
			$course_save->done_id = $post['contact_form_id'];
			
			// ISPC-2071 - added tabname, this entry must be grouped/sorted
			$course_save->tabname = Pms_CommonData::aesEncrypt("FormBlockAdditionalUsers");
			
			$course_save->save();
				
		}
	}
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
				
	
		];
	
	
		$values = FormBlockAdditionalUsers::getInstance()->getEnumValues($fieldName);
	
		 
		$values = array_combine($values, array_map("self::translate", $values));
	
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName] + $values;
		}
	
		return $values;
	
	}
	
	public function create_form_formblockadditionalusers( $options = array(), $elementsBelongTo = null )
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
		$this->mapValidateFunction($__fnName , "create_form_isValid");
	
		$this->mapSaveFunction($__fnName , "save_form_additionalusers");
	
		$subform = $this->subFormContactformBlock();
	    $subform->setLegend('+  Beteiligte Mitarbeiter');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    //var_dump($this->_cl_users);
	    /*$subform->addElement('hidden', 'id', array(
	    		'label'        => null,
	    		'value'        => ! empty($options['id']) ? $options['id'] : '',
	    		'required'     => false,
	    		'readonly'     => true,
	    		'filters'      => array('StringTrim'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 2,
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag' => 'tr',
	    						'class'    => 'dontPrint',
	    				)),
	    				
	    		),
	    ));*/
	   
		$subform->addElement('note', 'label_check', array(
				'value' => '&nbsp;',
				'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'style' => 'width: 8%; border: 1px solid #ccc; background: #eee;',
	    						'class'    => "table_header",
	    						'colspan' => '2'
	    				)),
	    				 array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	            )),
	    		),
		));
		$subform->addElement('note', 'label_name', array(
				'value' => '&nbsp;',
				'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'style' => 'border: 1px solid #ccc; background: #eee;',
	    						'class'    => "table_header",
	    				)),
	    				 array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	    		),
		));
		$t = 0;
		
				foreach($this->_cl_users as $kr=>$vr)
				{
					//var_dump($kr.'_arr_'.$t++);
						$empty = $subform->createElement('checkbox', $kr.'[]', array(
								'isArray' => true,
								'value'      => ($vr['id'] == $options['additional_users']['value'][$kr][0]) ? $vr['id'] : '',
								'checkedValue' => $vr['id'],
								'uncheckedValue' => '0',
								'class' => 'selector_user',
								'decorators' => array(
					    				'ViewHelper',
					    				array(array('data' => 'HtmlTag'), array(
					    						'tag' => 'td',
					    				)),
					    				 array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	            )),
					    		),
						));
						$subform->addElement($empty, $kr.'_arr_'.$t++);
						//var_dump($kr.'_arr_'.$t++);
						$empty = $subform->createElement('hidden', $kr.'[]', array(
								'isArray' => true,
								'value'      => ($options['additional_users']['value'][$kr][1] == '1') ? '1' : '0',
								'decorators' => array(
										'ViewHelper',
										array(array('data' => 'HtmlTag'), array(
												'tag' => 'td',
										)),
										
								),
						));
						$subform->addElement($empty, $kr.'_arr_'.$t++);
						
						$subform->addElement('note', 'label_'.$kr, array(
								'value' => $vr['nice_name'],
								'decorators' => array(
					    				'ViewHelper',
					    				array(array('data' => 'HtmlTag'), array(
					    						'tag' => 'td',
					    				)),
					    				 array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	                'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	            )),
					    		),
						));
					}
	
		//return $this;
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
}

?>