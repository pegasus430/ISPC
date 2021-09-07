<?php
// require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 23, 2018: @author claudiu, checked for ISPC-2071
 * Symptome II =  FormBlockClientSymptoms
 * 
 * changed: bypass Trigger() on PC	     
 * fixed: adding this block to a saved cf would not save to PC the first time
 * fixed: save cf without editing symp2, edit-cf and add symp2 would not save to PC 
 * fixed: savein will re-insert into PC all the old data even if not modified
 *  // Maria:: Migration ISPC to CISPC 08.08.2020	
 */
class Application_Form_FormBlockClientSymptoms extends Pms_Form
{
	protected $_symptom_groups = null;
	protected $_client_symptoms = null;
	protected $_all_sym_details = null;
	protected $_clsymmultiOptions = array();
	
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->_symptom_groups = ClientSymptomsGroups::get_client_symptoms_groups($this->logininfo->clientid);
		$this->_client_symptoms = ClientSymptoms::get_client_symptoms($this->logininfo->clientid,false, true);
		foreach($this->_client_symptoms as $group_id=>$syms){
			if($group_id == "0"){
				$grouped_syms[$group_id]['name'] =  $this->translate("no sym_group");
			}else{
				$grouped_syms[$group_id]['name'] = $this->_symptom_groups[$group_id]['groupname'];
			}
			$grouped_syms[$group_id]['symps'] = $syms;
		
			foreach($syms as $k=>$data){
				$this->_all_sym_details[$data['id']] = $data;
			}
		}
		//print_r($grouped_syms); exit;
		$this->_clsymmultiOptions[] = '';
		foreach($grouped_syms as $kg => $vg)
		{
			foreach($vg['symps'] as $ks => $vs)
			{
				$this->_clsymmultiOptions[$vg['name']][$vs['id']] = $vs['description'];
			}
		}
		

	}

	public function clear_block_data($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FormBlockClientSymptoms')
			->set('isdelete','1')
			->where("contact_form_id = ?", $contact_form_id)
			->andWhere('ipid = ?', $ipid);
			$Q->execute();

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
		$client_symptoms_block = new FormBlockClientSymptoms();

		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';		
		
		$client_sym_details_arr = ClientSymptoms::get_client_symptoms($clientid);
// 		foreach($client_sym_details_arr as $group_id=>$syms){
// 		    if($group_id == "0")
// 		    {
// 		        $grouped_syms[$group_id]['name'] =  $this->view->translate("no sym_group");
// 		    }
// 		    else
// 		    {
// 		        $grouped_syms[$group_id]['name'] = $s_groups[$group_id]['groupname'];
// 		    }
// 		    $grouped_syms[$group_id]['symps'] = $syms;
// 		}
		
		$severity = array(""=>"","0"=>"kein","4"=>"leicht","7"=>"mittel","10"=>"schwer");
		
		
		/*
		 * there are NO settings for ths block... why you had this? i removed
		 */
// 		$blocks_settings = new FormBlocksSettings();
// 		$block_ClientSymptoms_values = $blocks_settings->get_block($clientid,'ClientSymptoms');

		$records = array();
		$entry_array = array(); // ... this form inserts pc->course_title = serialized($entry_array)
		$course_str = "";
		$sorrowfully = "";
		$comment = "";
		$care_specifications = "";
		
		
		foreach ($post['clientsymptoms'] as $k=>$inserted_value) {
		    if (empty($inserted_value['symptom_id'])) {
		        unset($post['clientsymptoms'][$k]); // this are empty, this will NOT be saved
		    }
		}
		
		if ( ! empty($post['old_contact_form_id']))
		{
		    $change_date = $post['contact_form_change_date'];
		
		    $client_symptoms_old_data = $client_symptoms_block->getPatientFormBlockClientSymptoms($post['ipid'], $post['old_contact_form_id'], true);
		    		    
		    if ( ! in_array('clientsymptoms', $allowed_blocks)) {
		        // override post data if no permissions on block
		        // PatientCourse will NOT be inserted
		        if ( ! empty($client_symptoms_old_data)) {
		            
		            $records = array();
		            foreach ($client_symptoms_old_data as $ke => $action_values)
		            {
		            
		                $records[] = array(
		                    "ipid"                => $post['ipid'],
		                    "contact_form_id"     => $post['contact_form_id'],
		                    "symptom_id"          => $action_values['symptom_id'],
		                    "severity"            => $action_values['severity'],
		                    "sorrowfully"         => $action_values['sorrowfully'],
		                    "comment"             => $action_values['comment'],
		                    "care_specifications" => $action_values['care_specifications'],
		                    "isdelete"            => $action_values['isdelete']
		                );
		            }
		        }
		    }
		    else {
		        //we have permissions and cf is being edited
		        //write changes in PatientCourse is something was changed
		        if ( ! empty($client_symptoms_old_data)) {
		             
		            
		            if (count($client_symptoms_old_data) != count($post['clientsymptoms'])) {
		                //something changed
		                $save_2_PC = true;
		            } else {
		                
		                foreach ($post['clientsymptoms'] as $k=>$inserted_value) {
		                    
		                    if ( ! isset($client_symptoms_old_data[$k])) {
		                        // not same keys, something changed
		                        $save_2_PC = true;
		                        break;
		                        
		                    } elseif ($inserted_value['symptom_id'] != $client_symptoms_old_data[$k]['symptom_id']
	                            || $inserted_value['severity'] != $client_symptoms_old_data[$k]['severity']
	                            || $inserted_value['sorrowfully'] != $client_symptoms_old_data[$k]['sorrowfully']
	                            || $inserted_value['comment'] != $client_symptoms_old_data[$k]['comment']
	                            || $inserted_value['care_specifications'] != $client_symptoms_old_data[$k]['care_specifications'])
	                        {
	                            //compare each value to check if something changed, not same values
	                            $save_2_PC = true; 
	                            break;
	                        }
		                }
		            }
		            
		        }
		        else {
		            //nothing was edited last time, or this block was added after the form was created
		            $save_2_PC = true;
		            $change_date = '';
		             
		        }
		    }
		} else {
		    //new cf, save
		    $save_2_PC = true;
		}
		
		//create pc and block records
		foreach ($post['clientsymptoms'] as $k=>$inserted_value) {
		
		    if ( ! empty($inserted_value['symptom_id'])) {
		        
		        $records[] = array(
		            "ipid"                => $post['ipid'],
		            "contact_form_id"     => $post['contact_form_id'],
		            "symptom_id"          => $inserted_value['symptom_id'],
		            "severity"            => $inserted_value['severity'],
		            "sorrowfully"         => $inserted_value['sorrowfully'],
		            "comment"             => $inserted_value['comment'],
		            "care_specifications" => $inserted_value['care_specifications'],
		        );
		        
		        //create the pc $entry_array
		        if ($save_2_PC && in_array('clientsymptoms', $allowed_blocks)) {
		            
		            if (isset($inserted_value['sorrowfully']) && $inserted_value['sorrowfully'] == "1") {
		                $sorrowfully = "leidvoll";
		            } else {
		                $sorrowfully = "";
		            }
		            
		            if (strlen($inserted_value['comment']) > 0 ) {
		                $comment =  $inserted_value['comment'];
		            } else {
		                $comment = "";
		            }
		            
		            if (strlen($inserted_value['care_specifications']) > 0 ) {
		                $care_specifications =  $inserted_value['care_specifications'];
		            } else {
		                $care_specifications = "";
		            }
		            
		            $course_str .=  $client_sym_details_arr[$inserted_value['symptom_id']]['description']." | ".$severity[$inserted_value['severity']].$sorrowfully.$comment.$care_specifications."\n";
		            
		            $entry_array[] = array (
		                'description'         => $client_sym_details_arr[$inserted_value['symptom_id']]['description'],
		                'severity'            => $severity[$inserted_value['severity']],
		                'sorrowfully'         => $sorrowfully,
		                'comment'             => $comment,
		                'care_specifications' => $care_specifications
		            );
		            
		        }
		    }
		}
		
		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':'.date('s', $now)));


		//save the block
		$pc_recorddata =  array();
		
		if ( ! empty($records)) {
		
		    $collection = new Doctrine_Collection('FormBlockClientSymptoms');
		    $collection->fromArray($records);
		    $collection->save();
		    
		    $pc_recorddata = $collection->getPrimaryKeys();
		}
		
		//save into pc
		if ($save_2_PC && in_array('clientsymptoms', $allowed_blocks)) {
		    
		    if ( ! empty($entry_array)) {
		        
		        $cust = new PatientCourse();
		        
		        //skip Trigger()
		        $cust->triggerformid = null;
		        $cust->triggerformname = null;
		        
		        $cust->ipid = $post['ipid'];
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("S");
		        $cust->course_title = Pms_CommonData::aesEncrypt(serialize($entry_array));
		        // 		        $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
		        $cust->isserialized = 1;
		        
		        $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
		        
		        $cust->tabname = Pms_CommonData::aesEncrypt("cf_client_symptoms");
		        $cust->user_id = $userid;
		        $cust->done_date = $done_date;
		        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
		        $cust->done_id = $post['contact_form_id'];
		        $cust->save();
		        
		    } else {
	            //you unchecked all the options
	            //must remove from PC this option
	            //manualy remove and set $save_2_PC false
	            $save_2_PC =  false;
	             
	            if ( ! empty($post['old_contact_form_id'])) {
	               $pc_entity = new PatientCourse();
	               $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'cf_client_symptoms');
	            }
	        
		    }
		    
		    
		}
		

	}
	
	/**
	 * 
	 * ISPC-2516 @Carmen 10.07.2020 // Maria:: Migration ISPC to CISPC 08.08.2020	
	 */	
	public function create_form_symptomatology ($values =  array() , $elementsBelongTo = null)
	{
			$this->setDecorators(array(
					'FormElements',
					// 		    array('HtmlTag',array('tag' => 'table')),
					//'Form'
			));
				
			$this->addElement('note', 'symptomatology_date_label', array(
					'value'        => $this->translate('symptomatology_date'),
					'required'     => false,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('rdiv' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'class' => 'symptom_date'  )),
					),
			));
				
			$this->addElement('text', 'symptom_date', array(
					//'label'        => self::translate('suckoff_date'),
					'value'        => ! empty($values['symptom_date']) ? date('d.m.Y', strtotime($values['symptom_date'])) : date('d.m.Y'),
					'required'     => true,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'class'        => 'date option_date',
					'decorators' =>   array(
							'ViewHelper',
							array('Errors'),
					),
	
			));
	
			$symptom_time = ! empty($values['symptom_date']) ? date('H:i:s', strtotime($values['symptom_date'])) : date("H:i");
			$this->addElement('text', 'symptom_time', array(
					//'label'        => self::translate('clock:'),
					'value'        => $symptom_time,
					'required'     => true,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'class'        => 'time option_time',
					'decorators' =>   array(
							'ViewHelper',
							array('Errors'),
							array(array('rdiv' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true  )),
					),
			));
			
			$subform = $this->subFormTable(array(
					'columns' => array(
							'#',
							'Symptom',
							'Schweregrad',
							'leidvoll?',
							'Kommentar',
							'',
					),
					'id' => 'clientsymptomstable'
					// 'class' => 'datatable',
			));
			$subform->setLegend('Symptome II');
			$subform->setAttrib("class", "label_same_size_auto");
	
		$this->addSubForm($subform, 'cltable');
		
		$this->addElement('note', 'add_new_symptom', array(
				//'belongsTo' => 'set['.$krow.']',
				'value' => '<span class="ibutton add_symptom"><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="display: block; float: left; margin-right: 5px;"/>'.$this->translator->translate('addnewsymptom').'</span>',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
								'colspan' => '6',
						)),
						array(array('row' => 'HtmlTag'), array(
								'tag'      => 'tr',
						)),
				),
		));
		 
		
	
		return $this;
	}
	
	/**
	 *
	 * ISPC-2516 @Carmen 10.07.2020
	 */
	public function create_form_block_clientsymtoms_firstrow ($values =  array() , $elementsBelongTo = null)
	{
		$row = $this->subFormTableRow(array('id' => 'cl_sym'));
		
		if ( ! is_null($elementsBelongTo)) {
			$row->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$row->addElement('note', 'note_#', array(
				//'belongsTo' => 'set['.$krow.']',
				'value' => '#',
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
		));
		 
		$row->addElement('select', 'symptom_id', array(
				'multiOptions' => $this->_clsymmultiOptions,
				'value'        => $values['symptom_id'] != "" ? $values['symptom_id'] : "",
				'required'     => true,
				'validators'   => array('Int'),
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
		));
		
		$row->addElement('select', 'severity', array(
				'multiOptions' => self::_get_symptomatology_scale(),
				'value'        => $values['severity'] != "" ? $values['severity'] : "",
				'required'     => true,
				'validators'   => array('Int'),
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
		));
		
		$row->addElement('radio',  'sorrowfully', array(
				//'isArray'      => true,
				'multiOptions' => array('1' => 'Ja', '2'=>'Nein'),
				'value'        => $values['sorrowfully'] != '0' ? $values['sorrowfully'] : '0',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
				'separator' => ' ',
		));
		
		$row->addElement('textarea', 'comment', array(
				'required'     => false,
				'value'        => ! empty($values['comment']) ? nl2br($values['comment']) : null,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
					
		));
		$row->addElement('note', 'note_action', array(
				//'belongsTo' => 'set['.$krow.']',
				'value' => '<a href="javascript:void(0)" onclick="remove_sym_line($(this))"><img src="images/action_delete.png" border="0"></a>',
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
		));
		
		return $row;
	}
	
	/**
	 *
	 * ISPC-2516 @Carmen 10.07.2020
	 */
	public function create_form_block_clientsymtoms_secondrow ($values =  array() , $elementsBelongTo = null)
	{
		$row = $this->subFormTableRow(array('id' => 'care_cl_sym'));
		
		if ( ! is_null($elementsBelongTo)) {
			$row->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$row->addElement('note', 'note_care', array(
				//'belongsTo' => 'set['.$krow.']',
				'value' => '<b>Spez. Pflege / Therapien: </b><br />',
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('idiv' => 'HtmlTag'), array('tag' => 'div', 'style'=> 'padding: 2px; background-color: #f3f3f3; line-height: 200%;')),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 6, 'openOnly' => true)),
						//array('Label', array('tag' => 'td')),
						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
				),
				));
		
	
				$row->addElement('textarea', 'care_specifications', array(
						'required'     => false,
						'value'        => ! empty($values['care_specifications']) ? nl2br($values['care_specifications']) : null,
						'filters'      => array('StringTrim'),
						'validators'   => array('NotEmpty'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
								//array('Label', array('tag' => 'td')),
								//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
						),
							
				));
				
				return $row;
	}
	
	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param boolean $add2course
	 * @return void|NULL|Doctrine_Collection
	 * ISPC-2516 @Carmen 10.07.2020
	 */
	public function save_form_symptomatology($ipid =  '' , $data = array(), $add2course = false)
	{
		//texts
		if(empty($ipid) || empty($data)) {
			return;
		}
		
		$save_data = array();
		$coursecomment = array();
		$tocourse = array();
		$now_time = date('Y-m-d H:i:s', strtotime($data['symptom_date'] . ' ' . $data['symptom_time']));
		foreach($data['clientsymptoms'] as $krow => $row)
		{
			if (trim($row['severity']) !== '' ) {
				$save_data[] = array(
						'ipid'         => $ipid,
						'source' => 'charts',
						'symptom_id'    => $row['symptom_id'],
						'severity'  => trim($row['severity']) == '' ? null : $row['severity'],
						'sorrowfully' => $row['sorrowfully'],
						'symptom_date'   => $now_time,
						'comment'   => $row['comment'],
						'care_specifications' => $row['care_specifications'],
				);
				
				if($add2course){
					 if (isset($row['sorrowfully']) && $row['sorrowfully'] == "1") {
		                $sorrowfully = "leidvoll";
		            } else {
		                $sorrowfully = "";
		            }
		            
		            if (strlen($row['comment']) > 0 ) {
		                $comment =  $row['comment'];
		            } else {
		                $comment = "";
		            }
		            
		            if (strlen($row['care_specifications']) > 0 ) {
		                $care_specifications =  $row['care_specifications'];
		            } else {
		                $care_specifications = "";
		            }
		            
		            $tocourse[] = array (
		                'description'         => $this->_all_sym_details[$row['symptom_id']]['description'],
		                'severity'            => self::_get_symptomatology_scale()[$row['severity']],
		                'sorrowfully'         => $sorrowfully,
		                'comment'             => $comment,
		                'care_specifications' => $care_specifications
		            );
				}
				
			}
		}

		if (!empty($save_data)) {
			$collection = new Doctrine_Collection('FormBlockClientSymptoms');
			$collection->fromArray($save_data);
			$collection->save();
			
			$pc_recorddata = $collection->getPrimaryKeys();
			
			if (!empty($tocourse) && $add2course) {
				$cust = new PatientCourse();
				
				//skip Trigger()
				$cust->triggerformid = null;
				$cust->triggerformname = null;
				
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("S");
				$cust->course_title = Pms_CommonData::aesEncrypt(serialize($tocourse));
				$cust->isserialized = 1;
				
				$cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
				
				$cust->tabname = Pms_CommonData::aesEncrypt("cf_client_symptoms");
				$cust->user_id = $this->logininfo->userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("addmodal");
				$cust->save();
			}
			 
		}
		 
		return $collection;
		 
	}
	 
	
	private function _get_symptomatology_scale()
	{
		return array(
				''     => '',
				"0"     => 'kein',
				"4"     => 'leicht',
				"7"     => 'mittel',
				"10"    => 'schwer'
		);
	
	}
}

?>