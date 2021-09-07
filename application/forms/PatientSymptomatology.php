<?php

require_once("Pms/Form.php");

class Application_Form_PatientSymptomatology extends Pms_Form
{
	protected $_block_name_allowed_inputs =  array(
			 "Charts" => [
             'create_form_symptomatology' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [
                	'entry_date',
                ],
            ],
		]
	);
	
	protected $_setid = null;
	
	public function __construct($options = null)
	{
		if($options['_setid'])
		{
			 
			$this->_setid = $options['_setid'];
			unset($options['_setid']);
		}
		
		parent::__construct($options);
	
	}
	
	public function validate($post)
	{

	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$coursearr = array();

		if(empty($post['setid'])){
			$setid = 0;
		} else {
			$setid = $post['setid'];
		}
		$now_time = (!empty($post['edit_entry_date']) ? $post['edit_entry_date']  : date('Y-m-d H:i:s', time()));
		foreach ($post['input_value'] as $sym_id => $sym_val)
		{
			if($sym_val == ''){
				$sym_val = null;
			}
			$cust  =new Symptomatology();
			$cust->ipid = $post['ipid'];
			$cust->kvnoid = $post['kvnoid'];
			$cust->symptomid = $sym_id;
			$cust->input_value = $sym_val;
			$cust->setid = $setid;
			$cust->entry_date = $now_time;
			$cust->custom_description = $post['custom_description'][$sym_id];
			$cust->save();

			$coursearr['input_value'] = $sym_id;
			$coursearr['symptid'] = $sym_val;
			$coursearr['setid'] = $setid;
			$coursearr['iskvno'] = $sym_val['iskvno'];
			$finalcourse[] = $coursearr;
		}
	}


	public function EditData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		foreach ($post['edit_value'] as $sym_id => $new_val)
		{
			if($new_val == ''){
				$new_val = null;
			}
			$symval = Doctrine_Core::getTable('Symptomatology')->find($sym_id);
			if($symval->input_value != $new_val) {
				$symval->input_value = $new_val;
				$symval->save();
				$updatedates[] = $symval->entry_date;
			}
		}
		$updatedates = array_unique($updatedates);

		foreach($updatedates as $updated) {
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			$cust->user_id = $userid;
			$cust->course_title=Pms_CommonData::aesEncrypt('Symptomatik vom '.date('d.m.Y H:i',strtotime($updated)).' wurde editiert.') ;
			$cust->save();
		}
	}


	public function UpdateData($post)
	{

		/******* Delete Previous ids**********/
		$ipid = Pms_CommonData::getIpid($_GET['id']);
		$cust = Doctrine::getTable('PatientDiagnosis')->findBy("ipid",$ipid);
		$cust->delete();


		for($i=1;$i<=sizeof($post['diagnosis']);$i++)
		{

			if($post['hidd_diagnosis'][$i]>0)
			{
			 if($post['icd'][$i]>0)
			 {
			 	$tname = "diagnosis";
			 }
			 else
			 {
			 	$tname = "diagnosis_freetext";
			 }

			 $cust = new PatientDiagnosis();
			 $cust->ipid = $ipid;
			 $cust->tabname = $tname;
			 $cust->diagnosis_type_id = $post['dtype'][$i];
			 $cust->diagnosis_id = $post['hidd_diagnosis'][$i];
			 $cust->save();
			}
		}

		if(sizeof($post['newhidd_diagnosis'])>0)
		{

			for($i=0;$i<sizeof($post['newhidd_diagnosis']);$i++)
			{
				$cust = new PatientDiagnosis();
				$cust->ipid = $post['ipid'];
				$cust->tabname = $tname;
				$cust->diagnosis_type_id = $post['newdiagnosistype'][$i];
				$cust->diagnosis_id = $post['newhidd_diagnosis'][$i];
				$cust->save();
			}
		}


	}

	public function FetchDiagnosisType($post,$darr)
	{

	 $diagnosistypes = array();
	 for($i=0;$i<sizeof($darr);$i++)
	 {
	 	$sz = sizeof($post['dtype_'.$darr[$i]['id']]);

	 	for($j=1;$j<=$sz;$j++)
	  {

	  	if($post['dtype_'.$darr[$i]['id']][$j]==1)
	  	{
		   $diagnosistypes[$j][] = $darr[$i]['id'];
	  	}
	  }

	 }
	 return $diagnosistypes;
	}

	public function InsertDataFromVerlauf($ipid, $post)
	{
		unset($_SESSION['finalcourse']);
		unset($_SESSION['symids']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		//get sets
		$symperm = new SymptomatologyPermissions();
		$clientsymsets = $symperm->getClientSymptomatology($clientid);

		if($clientsymsets)
		{
			foreach ($clientsymsets as $k_cset => $v_cset)
			{
				$setsids[] = $v_cset['setid'];
			}

			//get sets data
			$patsymval = new SymptomatologyValues();
			$patsymvalarr = $patsymval->getSymptpomatologyValues($setsids);
		}
		else
		{
			$sm = new SymptomatologyMaster();
			$patsymvalarr = $sm->getSymptpomatology($clientid);


		}

		$coursearr = array ();
		$record_ids = array ();
		if (!empty($post['symptom']))
		{
			//map symptom with it's submited value
			foreach ($post['symptom'] as $k_psym => $v_psym)
			{
				$post_sym_data[$v_psym] = $post['sym_value'][$k_psym];
			}

			foreach ($patsymvalarr as $sym_id => $sym_data)
			{
				if ($post_sym_data[$sym_id] >= '0')
				{
					$submited_setsid[] = $sym_data['set']; //get only the sets of the symptoms with values
				}
			}

			$i=1;
			$now_time = date('Y-m-d H:i:s', time());
			foreach ($patsymvalarr as $sym_id => $sym_data)
			{
				if ($post_sym_data[$sym_id] >= '0')
				{
					$sym_value = $post_sym_data[$sym_id];
				}
				else
				{
					$sym_value = null;

				}

				$submited_setsid = array_unique($submited_setsid);
				if (in_array($sym_data['set'], $submited_setsid))
				{
					$cust = new Symptomatology();
					$cust->ipid = $ipid;
					$cust->symptomid = $sym_id;
					$cust->input_value = $sym_value;
					$cust->setid = $sym_data['set'];
					$cust->entry_date = $now_time;
					$cust->save();

					$coursearr['input_value'] = $sym_value;
					$coursearr['symptid'] = $sym_id;
					$coursearr['setid'] = $sym_data['set'];
					$finalcourse[] = $coursearr;
				}
			}
		}
	}

	public function InsertMultipleDataFromVerlauf($ipid, $post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		//get sets
		$symperm = new SymptomatologyPermissions();
		$clientsymsets = $symperm->getClientSymptomatology($clientid);

		if($clientsymsets)
		{
			foreach ($clientsymsets as $k_cset => $v_cset)
			{
				$setsids[] = $v_cset['setid'];
			}

			//get sets data
			$patsymval = new SymptomatologyValues();
			$patsymvalarr = $patsymval->getSymptpomatologyValues($setsids);
		}
		else
		{
			$sm = new SymptomatologyMaster();
			$patsymvalarr = $sm->getSymptpomatology($clientid);
		}

		$coursearr = array ();
		$record_ids = array ();
		if (!empty($post['symptom']))
		{
			//map symptom with it's submited value
			foreach ($post['symptom'] as $kk_psym => $vv_psym)
			{
				foreach($vv_psym as $k_psym => $v_psym)
				{
					$post_sym_data[$kk_psym][$v_psym] = $post['sym_value'][$kk_psym][$k_psym];
					$com_sym[$kk_psym][$v_psym] = $post['sym_coment'][$kk_psym][$k_psym];
				}
			}
			foreach($post_sym_data as $k_pat_sym => $v_pat_sym)
			{
				foreach ($patsymvalarr as $sym_id => $sym_data)
				{
					if(!empty($v_pat_sym[$sym_id]))
					{
						$submited_setsid[$k_pat_sym][] = $sym_data['set'];
					}
				}
			}

			$i=1;
			$finalcourses = array();
			$symids = array();

			$now_time = date('Y-m-d H:i:s', time());
			foreach($post_sym_data as $k_sym_pat=>$v_sym_pat)
			{
				foreach ($patsymvalarr as $sym_id => $sym_data)
				{
					if ($post_sym_data[$k_sym_pat][$sym_id] >= '0')
					{
						$sym_value = $post_sym_data[$k_sym_pat][$sym_id];
						$sym_comment = $com_sym[$k_sym_pat][$sym_id];
					}
					else
					{
						$sym_value = null;
						$sym_comment  = null;
					}

					if (in_array($sym_data['set'], $submited_setsid[$k_sym_pat]))
					{
						$cust = new Symptomatology();
						$cust->ipid = $ipid;
						$cust->symptomid = $sym_id;
						$cust->input_value = $sym_value;
						$cust->setid = $sym_data['set'];
						$cust->entry_date = $now_time;
						$cust->save();

						if($sym_value >= '0')
						{
							$coursearr['input_value'] = $sym_value;
							$coursearr['second_value'] = $sym_comment;
							$coursearr['symptid'] = $sym_id;
							$coursearr['setid'] = $sym_data['set'];

							$symids[] = $cust->id;

							$finalcourses[$k_sym_pat][] = $coursearr;
						}
					}
				}
			}

			if (sizeof($finalcourses) > 0)
			{
				foreach($finalcourses as $k_course => $v_course)
				{
					$input_array = serialize($v_course);
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = $now_time;
					$cust->course_type = Pms_CommonData::aesEncrypt("S");
					$cust->isserialized = 1;
					$cust->user_id = $userid;
					$cust->recorddata = serialize($symids);
					$cust->course_title = Pms_CommonData::aesEncrypt($input_array);
					$cust->done_date = $now_time;
					$cust->done_name = Pms_CommonData::aesEncrypt("sym_verlauf");
					$cust->save();

					if($cust->id)
					{
						// 1. get client shortcuts!
						$courses = new Courseshortcuts();
						$shortcut_id = $courses->getShortcutIdByLetter('S', $clientid);

						// 2. check if shortcut is shared
						$patient_share = new PatientsShare();
						$shared_data = $patient_share->check_shortcut($ipid, $shortcut_id);

						if($shared_data && $input_array)
						{
							foreach($shared_data[$shortcut_id] as $shared)
							{
								// 3. salve to other patients
								$cust = new PatientCourse();
								$cust->ipid = $shared; //target ipid
								$cust->course_date = $now_time;
								$cust->course_type = Pms_CommonData::aesEncrypt('S');
								$cust->course_title = Pms_CommonData::aesEncrypt($input_array);
								$cust->user_id = $userid;
								$cust->source_ipid = $ipid;
								$cust->isserialized = 1;
								$cust->done_date = $now_time;
								$cust->done_name = Pms_CommonData::aesEncrypt("sym_verlauf");
								$cust->save();
							}
						}
					}
				}
			}
		}
		unset( $_SESSION['symids']);
		unset($_SESSION['finalcourse']);
		unset($_SESSION['all_sym']);
	}
	
	
	private function _get_symptomatology_value_mapped($value, $symptomatology_scale = 0) 
	{
    	if($symptomatology_scale == 'a'){
    	    $none = array(0);
    	    $weak = array(1,2,3,4);
    	    $average = array(5,6,7);
    	    $strong = array(8,9,10);
    	    $symptom_mapping = array(
    	        "0"=>	'kein',
    	        "1"=>	'leicht',
    	        "2"=>	'leicht',
    	        "3"=>	'leicht',
    	        "4"=>	'leicht',
    	        "5"=>	'mittel',
    	        "6"=>	'mittel',
    	        "7"=>	'mittel',
    	        "8"=>	'schwer',
    	        "9"=>	'schwer',
    	        "10"=>	'schwer'
    	    );
    	} else{
    	    $symptom_mapping = array(
    	        "0"=>	'0',
    	        "1"=>	'1',
    	        "2"=>	'2',
    	        "3"=>	'3',
    	        "4"=>	'4',
    	        "5"=>	'5',
    	        "6"=>	'6',
    	        "7"=>	'7',
    	        "8"=>	'8',
    	        "9"=>	'9',
    	        "10"=>	'10'
    	    );
    	    	
    	}
    	return isset($symptom_mapping[$value]) ? $symptom_mapping[$value] : $value;
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
    
    
    //#ISPC-2512PatientCharts
	public function create_form_symptomatology ($values =  array() , $elementsBelongTo = null) 
	{
		
		foreach($this->_block_name_allowed_inputs as $blockn=>$blockval)
		{
			foreach($blockval as $fn => $removed_allowed)
			{
				if($fn == 'create_form_symptomatology')
				{
					foreach($removed_allowed as $kr => $vr)
					{
						if($kr == '__allowed' && !empty($vr))
						{
							$block_allowed_fields = array(
									$blockn => $vr
							);
						}
					}
				}
			}
		}
		 
		$allowed_fields_by_block = $block_allowed_fields[$this->_block_name];
		 
		if($allowed_fields_by_block)
		{
			$this->setDecorators(array(
					'FormElements',
					// 		    array('HtmlTag',array('tag' => 'table')),
					'Form'
			));
			
			$this->addElement('note', 'symptomatology_date_label', array(
					'value'        => $this->translate('symptomatology_date'),
					'required'     => false,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('rdiv' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true  )),
					),
					));
			
			$this->addElement('text', 'entry_date', array(
					//'label'        => self::translate('suckoff_date'),
					'value'        => ! empty($values['entry_date']) ? date('d.m.Y', strtotime($values['entry_date'])) : date('d.m.Y'),
					'required'     => true,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'class'        => 'date option_date',
					'decorators' =>   array(
							'ViewHelper',
							array('Errors'),
					),
		
					));
				  
					$entry_time = ! empty($values['entry_date']) ? date('H:i:s', strtotime($values['entry_date'])) : date("H:i");
					$this->addElement('text', 'entry_time', array(
							//'label'        => self::translate('clock:'),
							'value'        => $entry_time,
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
		}
	   
	    $subform = $this->subFormTable(array(
	        'columns' => array(    
    	        'Item',
//     	        'Letzter Wert',
    	        'Aktueller Wert',
    	        'Kommentar',
    	    ),
	       // 'class' => 'datatable',
	    ));
	    $subform->setLegend($this->translate('Symptome'));
	    $subform->setAttrib("class", "label_same_size_auto");
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    if (is_null($this->_client) ) {
	        $client =  new Client();
	        $this->_client = $client->findOneById($this->clientid);
	    }
	    
	    if($this->_setid)
	    {
	    	$setid = $this->_setid;
	    }
	    else 
	    {
	    	$setid = 1; //TODO add this to options
	    }
	    
	    $symptome_list = SymptomatologyValues::getDefaults($setid);
	    
	    if ( ! empty($values)) {
	        foreach ($values as $symp) {
	            
	            $symptome_list[$symp['symptomid']]['lastValue'] = $this->_get_symptomatology_value_mapped($symp['input_value'], $this->_client['symptomatology_scale']);
	            $symptome_list[$symp['symptomid']]['lastValueNumeric'] = $symp['input_value'];
	            $symptome_list[$symp['symptomid']]['custom_description'] = $symp['custom_description'];
	        }
	    }
	    
	    foreach ($symptome_list as $symp) {
	        
	        $row = $this->subFormTableRow();
	        
	        $row->addElement('hidden', 'symptomid', array(
	            'value'        => $symp['id'],
	            'required'     => false,
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style'=>"display:none"))
	            ),
            ));
	        $row->addElement('hidden', 'setid', array(
	            'value'        => $symp['set'],
	            'required'     => false,
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style'=>"display:none"))
	            ),
            ));
	        
	        $row->addElement('note', 'name', array(
	            'value'        => utf8_encode($symp['value']),
	            'required'     => false,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                //array('Label', array('tag' => 'td')),
	                //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
	            ),
	        ));
	        
// 	        $row->addElement('note', 'old_value', array(
// 	            'value'        => $symp['lastValue'],
// 	            'required'     => false,
// 	            'decorators'   => array(
// 	                'ViewHelper',
// 	                array('Errors'),
// 	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	                //array('Label', array('tag' => 'td')),
// 	                //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
// 	            ),
// 	        ));
	        
	        if ($this->_client['symptomatology_scale'] == 'a') { //use selectbox
	           
        	   	$row->addElement('select', 'input_value', array(
        	   	    'multiOptions' => $this->_get_symptomatology_scale(),
        	        'value'        => $symp['lastValueNumeric'], //"",
        	        'required'     => false,
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
        	   	
	        } else { //use textinput
	            
        	   	$row->addElement('text', 'input_value', array(
        	        'value'        => $symp['lastValueNumeric'],//'',
        	        //'label'        => utf8_encode($symp['value']),
        	        'required'     => false,
        	   	    'validators'   => array('Int'),
        	        'filters'      => array('StringTrim'),
        	        'decorators'   => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        	            //array('Label', array('tag' => 'td')),
        	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true  )),
        	        ),
        	   	    'size' => '10',
        	   	    'onkeydown' => 'return $.fn.assertKeydownNumber( event );',
        	   	    'onchange' => 'return $(this).validate0to10();',        	   	    
        		));
	        }
	        
	        
    	   	$row->addElement('text', 'custom_description', array(
    	   	    'value'        => $symp['custom_description'],
    	   	    'required'     => false,
    	   	    'filters'      => array('StringTrim'),
    	   	    'decorators'   => array(
    	   	        'ViewHelper',
    	   	        array('Errors'),
    	   	        array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	   	        //array('Label', array('tag' => 'td')),
    	   	        //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
    	   	    ),
    	   	));
    	   		
    	   	
    	   	$rows [] = $row;
    	   	
	    }
	    if($allowed_fields_by_block)
	    {
	    	$subform->addSubForms($rows);
	    	$this->addSubForm($subform, 'PatientSymptomatology');
	    	
	    	return $this;
	    }
	    else 
	    {
		    $subform->addSubForms($rows);
		    
		    
		    return $subform;
	    }
	}
	
	/**
	 * 
	 * @param string $ipid
	 * @param array $data
	 * @param boolean $add2course
	 * @return void|NULL|Doctrine_Collection
	 * @Ancuta added extra param - to save po patient course 20.05.2020
	 * ISPC-2516
	 * #ISPC-2512PatientCharts
	 */
	public function save_form_symptomatology($ipid =  '' , $data = array(), $add2course = false)
	{
	    //texts
	    if(empty($ipid) || empty($data)) {
	        return;
	    }

	    $now_time = ( ! empty($data['edit_entry_date']) ? $data['edit_entry_date']  : date('Y-m-d H:i:s', time()));

	    $save_data =  array();
	    $mustSave = false;
	   
	    if(array_key_exists('entry_date', $data))
	    {
	    	$now_time = date('Y-m-d H:i:s', strtotime($data['entry_date'] . ' ' . $data['entry_time']));
	    	unset($data['entry_date']);
	    	unset($data['entry_time']);
	    }
	    
	    $coursecomment = array();
	    $tocourse = array();
	    foreach ($data as $row) {
	        
	        if ( ! is_array($row) || ! isset($row['symptomid'])) {
	            continue;
	        }
	        
	        $save_data[] = array(
	            'ipid'         => $ipid,
	            'symptomid'    => $row['symptomid'],
	            'input_value'  => trim($row['input_value']) == '' ? null : $row['input_value'],
	            'setid'        => $row['setid'],
	            'entry_date'   => $now_time,
	            'custom_description'   => $row['custom_description'],
	        );
	        
	        
	      
	        if (trim($row['input_value']) != '' ) {
	            //one is here... we can save
	            $mustSave = true;
    	        
    	        if($add2course){
        	        $tocourse['input_value'] =  trim($row['input_value']) == '' ? null : $row['input_value'];
        	        $tocourse['second_value'] = $row['custom_description'];
        	        $tocourse['symptid'] = $row['symptomid'];
        	        $tocourse['setid'] = $row['setid'];
        	        $tocourse['iskvno'] = '0';
        	        $coursecomment[] = $tocourse;
    	        }
	        }
	        
	    }
	    
	    $collection = null;
	    
	    if ( $mustSave && ! empty($save_data)) {
	        $collection = new Doctrine_Collection('Symptomatology');
	        $collection->fromArray($save_data);
	        $collection->save();
	        
	        
	        if (! empty($coursecomment) && $add2course) {
	            $logininfo= new Zend_Session_Namespace('Login_Info');
	            $userid = $logininfo->userid;
	            
	            $cust = new PatientCourse();
	            $cust->ipid = $ipid;
	            $cust->course_date = date("Y-m-d H:i:s", time());
	            $cust->course_type = Pms_CommonData::aesEncrypt("S");
	            $cust->course_title = Pms_CommonData::aesEncrypt(serialize($coursecomment));
	            $cust->isserialized = 1;
	            $cust->user_id = $userid;
	            $cust->done_date = $now_time;
	            $cust->done_name = Pms_CommonData::aesEncrypt("SymptomatologyPlusButton");
	            $cust->tabname = Pms_CommonData::aesEncrypt("PatientSymptomatology");
	            
	            $cust->save();
	        }
	        
	    }
	    
	    return $collection;
	    
	}
}

?>