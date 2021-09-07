<?php
/**
 * 
 * @author ancuta
 * ISPC-2864 Ancuta 14.04.2021
 *
 */
class Application_Form_FormBlockProblems extends Pms_Form
{

    public function clear_block_data($ipid, $contact_form_id)
    {
        if (! empty($contact_form_id)) {
            
            $Q = Doctrine_Query::create()->update('FormBlockProblems')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $result = $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }
 
    
    
    
    
    public function create_form_block_patient_problems ($values =  array() , $elementsBelongTo = null)
    {
    	$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    
    	$this->mapValidateFunction($__fnName , "create_form_isValid");
    
    	$this->mapSaveFunction($__fnName , "save_form_block_patient_problems");
    
    
    	$subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
    	$subform->setLegend($this->translate('patient_problems'));
    	$subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
    	$subform->addDecorator('Form');
    	 
    	$this->__setElementsBelongTo($subform, $elementsBelongTo);
    	 
    	$subform->addElement('hidden', 'id', array(
    			'label'        => null,
    			'value'        => ! empty($values['id']) ? $values['id'] : '',
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
    							'class'    => 'dontPrint Hide',
    					)),
    			),
    	));
 
    	$subform->addElement('hidden', 'clientid', array(
    			'label'        => null,
    			'value'        => ! empty($values['clientid']) ? $values['clientid'] : '',
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
    							'class'    => 'dontPrint Hide',
    					)),
    			),
    	));
    	
    	// get client problems 
    	
    	// checked if  problemns are used
    	//select all problems of patient to check if they are used
    	$patient_problems = FormBlockProblemsTable::find_patient_problems(array($values['ipid']));
    	
    	$used_problems = array();
    	foreach($patient_problems as $k=>$pinf){
    	    $used_problems[] = $pinf['problem_id'];
    	    if(!empty($pinf['FormBlockProblemsSituations'])){
    	        foreach($pinf['FormBlockProblemsSituations'] as $k=>$pfssd){
    	            if($values['id'] == $pfssd['patient_problem_id'] && $pfssd['latest_version'] == '1'){
    	                $values[$pfssd['situation_type']] = $pfssd['situation_description'];
    	            }  
    	        }
    	    }
    	}
    	
    	$all_client_problems = Doctrine_Query::create()
    	->select('*')
    	->from('ClientProblemsList IndexBy id')
    	->where("clientid = ?", $values['clientid'])
    	->fetchArray();
    	$custom_ids = array();
    	foreach($all_client_problems as $pr_id=>$pinfo){
    	    if($pinfo['custom']=='1'){
    	        $custom_ids[]=$pinfo['id'];
    	    }
    	    $all_client_problems[$pinfo['id']] = $pinfo['problem_name'];
    	}
    	
    	
    	
    	//get client problems
    	$used_client_problems_array = array();
    	$not_used_client_problems_array = array();
    	if(!empty($used_problems)){
    	    $used_client_problems_array = Doctrine_Query::create()
        	->select('*')
        	->from('ClientProblemsList IndexBy id')
        	->where("clientid = ?", $values['clientid'])
        	->andWhereIn("id", $used_problems)
        	->orderBy('problem_name ASC')
        	->fetchArray();
        	
        	$not_used_client_problems_array = Doctrine_Query::create()
        	->select('*')
        	->from('ClientProblemsList IndexBy id')
        	->where("clientid = ?", $values['clientid'])
        	->andWhere("custom = 0")
        	->andWhereNotIn("id", $used_problems)
        	->orderBy('problem_name ASC')
        	->fetchArray();
    	}
    	else
    	{
        	$not_used_client_problems_array = Doctrine_Query::create()
        	->select('*')
        	->from('ClientProblemsList IndexBy id')
        	->where("clientid = ?", $values['clientid'])
        	->fetchArray();
    	}
    	
    	$client_pr2problem = array();
    	
    	$client_pr2problem[""] = self::translate('please select');
    	if(!empty($used_client_problems_array)){
    	    usort($used_client_problems_array, array(new Pms_Sorter('problem_name'), "_strnatcmp"));
    	    
    	    foreach($used_client_problems_array as $pr_id=>$pinfo){
    	        $client_pr2problem['Bestehende Probleme'][$pinfo['id']] = $pinfo['problem_name'];
    	        $all_client_pr2problem[$pinfo['id']] = $pinfo['problem_name'];
    	    }
    	}
    	
    	if(!empty($not_used_client_problems_array)){
    	    $client_pr2problem['Neues Problem']["custom"] = self::translate('custom**');
       	    
    	    usort($not_used_client_problems_array, array(new Pms_Sorter('problem_name'), "_strnatcmp"));
    	    
    	    foreach($not_used_client_problems_array as $pr_id=>$pinfo){
    	        if($pinfo['custom'] == 0){
        	        $client_pr2problem['Neues Problem'][$pinfo['id']] = $pinfo['problem_name'];
    	        }
    	        $all_client_pr2problem[$pinfo['id']] = $pinfo['problem_name'];
    	        
    	    }
    	}
    	else{
    	    $client_pr2problem['Neues Problem']["custom"] = self::translate('custom**');
    	    
    	}
    	
    	if(empty($values['id'])){
    	    
        	$subform->addElement('select', 'problem_id', array(
        			'label' 	   => self::translate('problem_name').":",
        	        'multiOptions' => $client_pr2problem,
        			'value'        => $values['problem_id'],
        			'required'     => true,
        			'filters'      => array('StringTrim'),
        			'decorators' =>   array(
        					'ViewHelper',
        					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
        					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        			),
        	       'onChange' => '
                        if ($(this).val() == "custom") { $(".custom_line").show(); } else {$(".custom_line").hide();}
                        preselect_latest_situations($(this).val());
                        ',
        	));
        	
        	$display = $positionind_type_index != 1 ? 'display:none' : null;
        	$subform->addElement('text', 'problem_name', array(
        	    'label'        => self::translate('custom problem name').":",
        	    'value'        => "",
        	    'required'     => true,
        	    'filters'      => array('StringTrim'),
        	    'validators'   => array('NotEmpty'),
        	    'class'        => ' ',
        	    'decorators' =>   array(
        	        'ViewHelper',
        	        array('Errors'),
        	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
        	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
        	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'Class'=>'custom_line', 'style' => $display )),
        	    ),
        	    
        	));
        	
        	
    	} else {
    	    //if custom allow - edit problem name  
 
    	    if(in_array($values['problem_id'],$custom_ids)){
    	        
    	        $subform->addElement('hidden', 'custom_problem_id', array(
    	            'label'        => null,
    	            'value'        => '1',
    	            'required'     => false,
    	            'readonly'     => true,
    	            'filters'      => array('StringTrim'),
    	        ));
    	        
    	        $subform->addElement('text', 'problem_name', array(
    	            'label'        => self::translate('custom problem name').":",
    	            'value'        => $all_client_pr2problem[$values['problem_id']],
    	            'required'     => true,
    	            'filters'      => array('StringTrim'),
    	            'validators'   => array('NotEmpty'),
    	            'class'        => ' ',
    	            'decorators' =>   array(
    	                'ViewHelper',
    	                array('Errors'),
    	                array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true   )),
    	            ),
    	        ));
    	        
    	    }
    	    else
    	    {
    	        
            	$subform->addElement('note', 'problem_name', array(
            	        'label' 	   => self::translate('problem_name').":",
            	        'value'        => $all_client_pr2problem[$values['problem_id']],
            			'filters'      => array('StringTrim'),
            			'decorators' =>   array(
            					'ViewHelper',
            					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
            					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            			),
            	));
    	    }
 
        	
        	$subform->addElement('hidden', 'problem_id', array(
        	    'label'        => null,
        	    'value'        => ! empty($values['problem_id']) ? $values['problem_id'] : '',
        	    'required'     => false,
        	    'readonly'     => true,
        	    'filters'      => array('StringTrim'),
        	));
        	
    	    
    	}
    	
    	
    	
    	$subform->addElement('text', 'documented_date', array(
    	    'label'        => self::translate('documented_date').":",
//     	    'value'        => ! empty($values['documented_date']) ? date('d.m.Y', strtotime($values['documented_date'])) : date('d.m.Y'),
    	    'value'        => ! empty($values['documented_date']) ? date('d.m.Y') : date('d.m.Y'),
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => 'date option_date',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    
    	));
    	
//     	$documented_date_time = ! empty($values['documented_date']) ? date('H:i:s', strtotime($values['documented_date'])) : date("H:i");
    	$documented_date_time = ! empty($values['documented_date']) ? date("H:i") : date("H:i");
    	$subform->addElement('text', 'documented_date_time', array(
    	    //'label'        => self::translate('clock:'),
    	    'value'        => $documented_date_time,
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => 'time option_time',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
    	        //array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	    ),
    	));
    	
    	
    	// get latest 
    	
        	
      $subform->addElement('textarea', 'current_situation', array(
    	    'label'        => self::translate('current_situation_problem').":",
          'value'        => ! empty($values['current_situation']) ? strip_tags($values['current_situation']) : "",
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => '',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    'cols' => 60,
    	    'rows' => 3,
    	));
    	$subform->addElement('textarea', 'hypothesis', array(
    	    'label'        => self::translate('hypothesis_problem').":",
    	    'value'        => ! empty($values['hypothesis']) ? strip_tags($values['hypothesis']) :"",
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => '',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    'cols' => 60,
    	    'rows' => 3,
    	));
    	$subform->addElement('textarea', 'measures', array(
    	    'label'        => self::translate('measures_problem').":",
    	    'value'        => ! empty($values['measures']) ? strip_tags($values['measures']) : "",
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => '',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    'cols' => 60,
    	    'rows' => 3,
    	));
 
    	
//     	$form_content_current_situation = $this->create_form_block_patient_problems_situation($values =  array() , $elementsBelongTo = null,'current_situation');
//     	$subform->addSubform($form_content_current_situation, 'FormBlockProblemsSituations1');
    	
//     	$form_content_hypothesis = $this->create_form_block_patient_problems_situation($values =  array() , $elementsBelongTo = null,'hypothesis');
//     	$subform->addSubform($form_content_hypothesis, 'FormBlockProblemsSituations2');
    	
//     	$form_content_measures = $this->create_form_block_patient_problems_situation($values =  array() , $elementsBelongTo = null,'measures');
//     	$subform->addSubform($form_content_measures, 'FormBlockProblemsSituations3');
    
    	
    	
    	return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    public function create_form_block_patient_problems_situation ($values =  array() , $elementsBelongTo = null,$situation_type  = "")
    {
    	$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    	$subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
    	 
    	$subform->addElement('hidden', 'id', array(
    			'label'        => null,
    			'value'        => ! empty($values['id']) ? $values['id'] : '',
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
    	));
 
    	$subform->addElement('textarea', $situation_type, array(
    	    'label'        => self::translate($situation_type).":",
    	    'value'        => ! empty($values[$situation_type]) ,
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => '',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    'cols' => 60,
    	    'rows' => 3,
    	));
    
    	return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    
    public function save_form_block_patient_problems ($ipid =  null , $data =  array())
    {
    	if (empty($ipid) || empty($data)) {
    		return;
    	}
    	
    	
    	if(empty($data['problem_id'])){
    	   return; 
    	}

    	if($data['problem_id'] == "custom" && !empty($data['clientid'])){
//     	    insert in client - as custom and use
            // check if custom name exist - if exist - use that - else insert new 
    	    $exiting_problem_name = Doctrine_Query::create()
    	    ->select('*')
    	    ->from('ClientProblemsList')
    	    ->where("clientid = ?", $data['clientid'])
    	    ->andWhere("problem_name=?",$data['problem_name'])
    	    ->orderBy('problem_name ASC')
    	    ->fetchArray();
    	    
    	    if(!empty($exiting_problem_name)){
    	        $data['problem_id'] = $exiting_problem_name['0']['id'];
    	    }
    	    else {
    	        $ins = new ClientProblemsList;
    	        $ins->clientid = $data['clientid'];
    	        $ins->problem_name =$data['problem_name'];
    	        $ins->custom='1';
    	        $ins-> save();
    	        
    	        $data['problem_id'] = $ins->id;
    	        
    	    }
    	}
    	
 
    	if(isset($data['custom_problem_id']) && $data['custom_problem_id'] == '1' &&  isset($data['problem_name']) ){
    	    // update  problem name in client 
    	    $q = Doctrine_Query::create()
    	    ->update('ClientProblemsList a')
    	    ->set('problem_name',"?",$data['problem_name'])
    	    ->where('clientid =?',$data['clientid'])
    	    ->andwhere('id =?',$data['problem_id']);
    	    $q->execute();
    	}
    	
    	// check if problem already existis 
    	$patient_exisitng_problem_q = Doctrine_Query::create()
    	->select('*')
    	->from('FormBlockProblems')
    	->where("ipid = ?", $ipid)
    	->andWhere("problem_id = ?", $data['problem_id'])
    	->andWhere("isdelete = 0")
    	->limit(1)
    	->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
    	
    	if($patient_exisitng_problem_q){
    	    $patient_exisitng_problem = $patient_exisitng_problem_q->toArray();
    	}
    	
    	if(empty($data['id']) &&  !empty($patient_exisitng_problem)){
    	    $new_update = 1;
    	    $data['id'] = $patient_exisitng_problem['id'];
    	}

    	
		if($data['documented_date_time'] != "")
		{
		    $documented_date_time = $data['documented_date_time'] . ":00";
		}
		else
		{
		    $documented_date_time = '00:00:00';
		}
		 
		if($data['documented_date'] != "")
		{
		    $data['documented_date'] = date('Y-m-d H:i:s', strtotime($data['documented_date'] . ' ' . $documented_date_time));
		}
		else
		{
			$data['documented_date'] = '0000-00-00 00:00:00';
		}

    	$data['ipid'] = $ipid;
    	$data['source'] = "charts";
    	
 
    	unset($data['documented_date_time']);
    	

    	$entity = FormBlockProblemsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    	$problem_info_arr = $entity->toArray();

    
    	// update all set previous to lates version 1
    	if(!empty($data['id'])){
        	    
        	$q = Doctrine_Query::create()
            	->update('FormBlockProblemsSituations a')
            	->set('latest_version',"0")
            	->where('patient_problem_id =?',$data['id'])
            	->andwhere('ipid =?',$ipid);
            	$q->execute();
    	}
    	
    	$s = 0;
    	$data_situation = array();
    	
    	// get latest version - increment and save // CHECK HOW PROBLEM IS SAVEDPLM pLM --- get all ids- for problem - search  all for this problem
    	$version_nr = 0 ;
    	$patient_exisitng_problem_version = Doctrine_Query::create()
    	->select('*')
    	->from('FormBlockProblemsSituations')
    	->where("ipid = ?", $ipid)
    	->andWhere("patient_problem_id = ?", $data['id'])
    	->andWhere("isdelete = 0")
    	->orderBy('version_nr DESC')
    	->limit(1)
    	->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
    	if($patient_exisitng_problem_version){
    	    $patient_exisitng_problem_version_arr = $patient_exisitng_problem_version->toArray();
    	    
    	    $old_version = $patient_exisitng_problem_version_arr['version_nr'];
    	}else{
    	    $old_version = 1;
    	}
    	
    	$version_nr = $old_version+1;
    	
    	$situation_array = array('current_situation','hypothesis','measures');
    	
    	foreach($situation_array as $situation_type){
    	    $data_situation['FormBlockProblemsSituations'][$s]['patient_problem_id'] = $problem_info_arr['id'];
    	    $data_situation['FormBlockProblemsSituations'][$s]['ipid'] = $ipid;
    	    $data_situation['FormBlockProblemsSituations'][$s]['situation_date'] = $data['documented_date'];
    	    $data_situation['FormBlockProblemsSituations'][$s]['situation_type'] = $situation_type;
    	    $data_situation['FormBlockProblemsSituations'][$s]['situation_description'] = $data[$situation_type];
    	    $data_situation['FormBlockProblemsSituations'][$s]['latest_version'] = '1';
    	    $data_situation['FormBlockProblemsSituations'][$s]['version_nr'] = $version_nr;
    	    $s++;
    	}
    	
    	
//     	if(!empty($data['current_situation'])){
//     	}
//    	    $s++;
//     	if(!empty($data['hypothesis'])){
//     	    $data_situation['FormBlockProblemsSituations'][$s]['patient_problem_id'] = $problem_info_arr['id'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['ipid'] = $ipid;
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_date'] = $data['documented_date'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_type'] = "hypothesis";
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_description'] = $data['hypothesis'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['latest_version'] = '1';
//     	    $data_situation['FormBlockProblemsSituations'][$s]['version_nr'] = $version_nr;
//     	}
//    	    $s++;
//     	if(!empty($data['measures'])){
//     	    $data_situation['FormBlockProblemsSituations'][$s]['patient_problem_id'] = $problem_info_arr['id'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['ipid'] = $ipid;
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_date'] = $data['documented_date'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_type'] = "measures";
//     	    $data_situation['FormBlockProblemsSituations'][$s]['situation_description'] = $data['measures'];
//     	    $data_situation['FormBlockProblemsSituations'][$s]['latest_version'] = '1';
//     	    $data_situation['FormBlockProblemsSituations'][$s]['version_nr'] = $version_nr;
//     	}
 
    	
    	if(!empty($data_situation['FormBlockProblemsSituations'])){
    	    $collection = new Doctrine_Collection('FormBlockProblemsSituations');
    	    $collection->fromArray($data_situation['FormBlockProblemsSituations']);
    	    $collection->save();
    	}
    	
    	 
    	return $entity;
    }
    
}

?>
