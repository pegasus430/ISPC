<?php
require_once("Pms/Form.php");

class Application_Form_FormBlockKeyValue extends Pms_Form
{
    // Maria:: Migration CISPC to ISPC 22.07.2020
	// Maria:: Migration ISPC to CISPC 08.08.2020	
	// Maria:: Migration CISPC to ISPC 20.08.2020
    const BLOCK_CAREPROCESS_CLINIC = 'FormBlockCareProcessClinic';//IM-4
    const BLOCK_TREATMENTPLAN_CLINIC = 'FormBlockTreatmentPlanClinic';//IM-26
    const BLOCK_JOBBACKGROUND_CLINIC = 'FormBlockJobBackgroundClinic';//IM-47
    const BLOCK_DISCHARGEPLANNING_CLINIC = 'FormBlockDischargePlanningClinic';//IM-48
    const BLOCK_SCREENINGDEPRESSION_CLINIC = 'FormBlockScreeningDepressionClinic';//IM-51
    const BLOCK_GENOGRAM = 'FormBlockGenogram';//IM-55
    const BLOCK_PSYCHOSOZIAL_STATUS = 'FormBlockPsychosocialStatus';//IM-62
    const BLOCK_TALKWITH = 'FormBlockTalkWith'; //IM-56
    const BLOCK_TALKCONTENT = 'FormBlockTalkContent'; //IM-46
    const BLOCK_MEDICATION_CLINIC = 'FormBlockMedicationClinic'; //IM-53
    const BLOCK_PALLIATIV_SUPPORT = 'FormBlockPalliativSupport'; //IM-65
    const BLOCK_PALLIATIV_ASSESSMENT = 'FormBlockPalliativAssessment';//IM-66
    const BLOCK_SOAP = 'FormBlockSOAP';//IM-87
    const BLOCK_DIAGNOSIS_CLINIC = 'FormBlockDiagnosisClinic'; //IM-91
    const BLOCK_SHIFT = 'FormBlockShift';//IM-92
    const BLOCK_CLINIC_MEASURE = 'FormBlockClinicMeasure';//IM-93
    const BLOCK_ACTUALPROBLEMS = 'FormBlockActualproblems'; //IM-105
    const BLOCK_REPORTRECIPIENT = 'FormBlockReportRecipient'; //IM-104
    const BLOCK_DOCUMENTATION = 'FormBlockDocumentation'; //IM-137
    const BLOCK_PFLEGEBA = 'FormBlockPflegeba'; //IM-137
    const BLOCK_IPOS = 'FormBlockIpos'; //IM-2476
    const BLOCK_KARNOFSKY = 'FormBlockIKarnofsky'; //IM-2476
    const BLOCK_COORDINATIONTIME = 'FormBlockCoordinationtime'; //ISPC-2626
    const BLOCK_FILEUPLOAD = 'FormBlockFileupload'; //ISPC-2628
    const BLOCK_LMU_PMBA2 = 'FormBlockLmuPmba2'; //ISPC-2631
	const BLOCK_BEATMUNG = 'FormBlockBeatmung'; //ISPC-2697, elena, 04.11.2020

    
    //ISPC-2663 Carmen 02.09.2020
    const BLOCK_TALKWITHSINGLESELECTION = 'FormBlockTalkWithSingleSelection';

	public function clear_block_data($ipid, $contact_form_id, $blockname )
	{
        if (!empty($contact_form_id)) {

			$Q = Doctrine_Query::create()
			->update('FormBlockKeyValue')
			->set('isdelete','1')
			->where("contact_form_id='" . $contact_form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"')
			->andwhere('block = ?', $blockname);
			$result = $Q->execute();
			
			return true;
        } else {
			return false;
		}
	}

	/**
	 * Maria:: Migration CISPC to ISPC 22.07.2020
	 * @param unknown $data
	 * @return array[]|number[]
	 */
    private function get_careprocessclinic_data($data)
    {
        $erg = array();
        $input_values = array();
        $checked_items = array();

        foreach (array_keys($data) as $keys) {
            if (strpos($keys, 'section') === 0) {
                foreach (array_keys($data[$keys]) as $key) {
                    if (isset($data[$keys][$key]['checked_items']))
                        $checked_items = $checked_items + $data[$keys][$key]['checked_items'];
                    if (isset($data[$keys][$key]['input_values']))
                        $input_values = $input_values + $data[$keys][$key]['input_values'];
                }
            }
        }
        $erg['checked_items'] = $checked_items;
        $erg['input_values'] = $input_values;

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @param unknown $professions_conf
     * @return unknown[]
     */
    private function get_treatmentplanclinic_data($options, $professions_conf)
    {
        $erg = array();
        foreach($professions_conf as $keyconf => $conf){

            if(isset($options['goal_'.$keyconf])){
                $erg[$keyconf]['goal'] =  $options['goal_'.$keyconf];
            }
            if(isset($options['plan_'.$keyconf])){
                $erg[$keyconf]['plan'] =  $options['plan_'.$keyconf];
            }
        }

        if(isset($options['treatmentplan_clinic_agree_with'])){
            $erg['agree_with'] =  $options['treatmentplan_clinic_agree_with'];
        }
        if(isset($options['treatmentplan_clinic_talk_supply_planning'])){
            $erg['talk_supply_planning'] =  $options['treatmentplan_clinic_talk_supply_planning'];
        }
        if(isset($options['treatmentplan_clinic_date'])){
            $erg['date'] =  $options['treatmentplan_clinic_date'];
        }

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @return unknown[]
     */
    private function get_jobbackgroundclinic_data($options)
    {
        $erg = array();
        foreach ($options['item'] as $option) {
            $erg[$option['key']] = $option['val'];
        }

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @return unknown[]
     */
    private function get_screeningdepressionclinic_data($options)
    {
        $erg = array();
        foreach ($options['item'] as $option) {
            $erg[$option['key']] = $option['value'];
        }

        $erg['FREETEXT'] = $options['FREETEXT'];

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @param unknown $client
     * @return array[]|unknown[]
     */
    private function get_talkingwith_data($options, $client)
    {

        $contactlist = $this->get_contact_list_with_id($client);
        $erg = array();
        $erg['item'] = array();
        foreach ($options['item'] as $option) {
            if ($option['select'] == 'NOSELECT')
                continue;
            $erg['item'][] = array('key' => $option['select'], 'value' => $contactlist[$option['select']]);
        }

        $erg['TALKFREETXT'] = $options['TALKFREETXT'];

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @param unknown $clientid
     * @param unknown $form_type_id
     * @return unknown|array|string[]|boolean[]|mixed[]
     */
    private function get_talkingcontent_data($options, $clientid, $form_type_id)
    {

        $configs = ClientConfig::getConfigTalkContent($clientid, $form_type_id);

        foreach ($configs as $key => $config) {
            if (!array_key_exists($key, $options))
                continue;
            $option = $options[$key];
            if(isset($option['checkbox_val']))
                $configs[$key]['checkbox_val'] = $option['checkbox_val'];
            if(isset($option['freetext_val']))
                $configs[$key]['freetext_val'] = $option['freetext_val'];
        }

        return $configs;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @return unknown[]
     */
    private function get_genogram_data($options)
    {
        $erg = array();

        $erg['name'] = $options['name'];
        $erg['role'] = $options['role'];
        $erg['phone'] = $options['phone'];
        $erg['mobile'] = $options['mobile'];
        $erg['comment'] = $options['comment'];

        return $erg;
    }

    /**
     * Maria:: Migration CISPC to ISPC 22.07.2020
     * @param unknown $options
     * @param unknown $blocks
     * @return unknown[]
     */
    private function get_psychosozial_status_data($options,  $blocks)
    {

        $erg = array();

        foreach($blocks as $block){
             $erg[$block] = $options[$block];
        }

        return $erg;
    }

	/**
	 * ISPC-2697, elena, 20.11.2020
	 *
	 * @param $ipid
	 * @param $period
	 * @return array
	 */
    public static function get_ventilation_chart($ipid, $period){
		//ISPC-2836,Elena,23.02.2021
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$aUsers = User::get_AllByClientid($clientid);

		$SQL = Doctrine_Query::create()
			->select('id,contact_form_id, create_date, v, create_user') //ISPC-2836,Elena,23.02.2021
			->from('FormBlockKeyValue')
			->where('ipid=?',$ipid)
			->andWhere('isdelete=0')
			->andWhere('block=?', 'FormBlockBeatmung')
			->orderBy('create_date DESC');



		$aRawBlock = $SQL->fetchArray();
		//print_r($aRawBlock);
		$block = [];
		//print_r($aRawBlock);
		$allblockdata = [];
		foreach($aRawBlock as $rawdata){
			$data = json_decode($rawdata['v'], true);
			$data['id'] = $rawdata['id'];
			//ISPC-2836,Elena,23.02.2021
			$data['create_date'] = $rawdata['create_date'];
			$data['username'] = $aUsers[$rawdata['create_user']]['nice_name'];

			if(!empty($data['beatmung']['oxygen_date_from'])){//ISPC-2904,Elena,30.04.2021
//echo $data['id'] . '<br>';
				if(!empty($data['beatmung']['oxygen_time_from'])){
					$datum_as_datetime = date_create_from_format('d.m.Y H:i', $data['beatmung']['oxygen_date_from'] . ' ' .  $data['beatmung']['oxygen_time_from'] );
				}else{
					$datum_as_datetime = date_create_from_format('d.m.Y H:i', $data['beatmung']['oxygen_date_from']  . ' 00:00' );
				}
				if($datum_as_datetime){
					$datum_as_timestamp = $datum_as_datetime->getTimestamp();
				}else{
					continue;
				}

				$data['datum'] = date_format($datum_as_datetime,'Y-m-d H:i:s' );
				$allblockdata[$datum_as_timestamp] = $data;

			}elseif($rawdata['contact_form_id'] > 0){
				$sqldate =  Doctrine_Query::create()
					->select('date')
					->from('ContactForms')
					->where('id=?',$rawdata['contact_form_id'])
					;
				$adt = $sqldate->fetchArray();
				$datum = $adt[0]['date'];
				//print_r($adt);
				$data['datum'] = $datum;

				//echo $datum;
				$allblockdata[strtotime($datum)] = $data;

			}elseif(!empty($data['beatmung']['date'])){//ISPC-2904,Elena,30.04.2021

				$datum_as_datetime = date_create_from_format('d.m.Y H:i', $data['beatmung']['date'] . ' ' .  $data['beatmung']['time'] );
				//echo 'from format';
				//print_r($datum_as_datetime);
				$datum_as_timestamp = $datum_as_datetime->getTimestamp();
				$data['datum'] = date_format($datum_as_datetime,'Y-m-d H:i:s' );
				$allblockdata[$datum_as_timestamp] = $data;

			}


		}
		//print_r($allblockdata);
		#print_r($period);
		krsort($allblockdata);
		if($period){
			foreach($allblockdata as $key => $blockdata){
				if(($key >= strtotime($period['start']) || !empty($blockdata['beatmung']['oxygen_open_end']))  && ( $key <= strtotime($period['end']) ) ){//ISPC-2904,Elena,30.04.2021
					$block[] = $blockdata;
				}
		}
		}else{
			$block = $allblockdata;
		}
//print_r($block);
		return $block;
	}




	public function InsertData($post,$allowed_blocks, $blockname)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$tw_block = new FormBlockKeyValue();
		
		//TODO-3219 Carmen 18.06.2020// Maria:: Migration ISPC to CISPC 08.08.2020	
		$save_2_PC[$blockname] = false; //if we have insert or update on PatientCourse
		$change_date = '';
		$coursecomment[$blockname] = array();
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
		//--
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id'], $blockname);
		if (strlen($post['old_contact_form_id']) > 0 && !in_array($blockname, $allowed_blocks))
		{

			$tw_old_data = $tw_block->getPatientFormBlockKeyValues($post['ipid'], $post['old_contact_form_id'], true, $blockname);

			if ($tw_old_data)
			{
					foreach ($post[$blockname] as $key=>$val)
						$post[$blockname][$key] = $tw_old_data[$key];
			}
		}
		//TODO-3219 Carmen 18.06.2020
		else if (strlen($post['old_contact_form_id']) > 0 && in_array($blockname, $allowed_blocks) && ($blockname == 'lmu_pmba_wishes' || $blockname == 'lmu_pmba_aufklaerung') && $this->_clientModules['231'])
		{
			
			$tw_old_data = $tw_block->getPatientFormBlockKeyValues($post['ipid'], $post['old_contact_form_id'], true, $blockname);
			$change_date = $post['contact_form_change_date'];
			
			//we have permissions and cf is being edited
			//write changes in PatientCourse is something was changed
			if ( ! empty($tw_old_data)) {
				foreach($post[$blockname] as $key => $val) 
				{
					if ($val != $tw_old_data[$key])
					{
						//something was edited, we must insert into PC
						$save_2_PC[$blockname] = true;
						$coursecomment[$blockname][$key] = $val; 
					}
				}
			}
			else {
				//nothing was edited last time, or this block was added after the form was created
				$save_2_PC[$blockname] = true;
				foreach($post[$blockname] as $key => $val)
				{
					$coursecomment[$blockname][$key] = $val;
				}
				$change_date = '';
				 
			}
		}
		else if ( empty($post['old_contact_form_id']) && in_array($blockname, $allowed_blocks) && ($blockname == 'lmu_pmba_wishes' || $blockname == 'lmu_pmba_aufklaerung') && $this->_clientModules['231']) {
			//new cf, save
			$save_2_PC[$blockname] = true;
			foreach($post[$blockname] as $key => $val)
			{
				$coursecomment[$blockname][$key] = $val;
			}
		}
		//--
	
		$lmu_pmba_psysoz_oldValues =  array();
		if ((int)($post['old_contact_form_id']) > 0 && $blockname == "lmu_pmba_psysoz")
		{
			$lmu_pmba_psysoz_oldValues = $tw_block->getPatientFormBlockKeyValues($post['ipid'], $post['old_contact_form_id'], true, $blockname);	
			$is_new_contactform =  false;		
		} else {
			$is_new_contactform =  true;
		}
		
		/* if($blockname=="lmu_pmba_anamnese"){
			//Diagnosis-Stuff
			
			//get Types in style: abb.=>id
			$dg = new DiagnosisType();
			$diagabbs = $dg->getClientDiagnosisTypes($clientid);

			foreach($post[$blockname]['diag_freetext'] as $rownumber=>$rowfreetext)
			{
				
				//delete diagnosis
				if (($post[$blockname]['diag_deleted'][$rownumber]==1 || $post[$blockname]['diag_changed'][$rownumber]==1 )&& $post[$blockname]['diag_pdid'][$rownumber]>0){
					$cust = Doctrine::getTable('PatientDiagnosis')->find($post[$blockname]['diag_pdid'][$rownumber]);
					if($cust){
                        $cust->delete();
                        $cust->save();
                        }
					}
				if (!$post[$blockname]['diag_pdid'][$rownumber] || $post[$blockname]['diag_changed'][$rownumber]==1)
				{
					if($post[$blockname]['diag_freetext'][$rownumber] || $post[$blockname]['diag_icd'][$rownumber])
						{
						$dgtext = new DiagnosisText();
						$dgtext->clientid = $this->clientid;
						$dgtext->icd_primary = 	$post[$blockname]['diag_icd'][$rownumber];
						$dgtext->free_name = 	$post[$blockname]['diag_freetext'][$rownumber];
						$dgtext->free_desc = "";
						$dgtext->save();			
						$diagtextid = $dgtext->id;
						
						$pdiag = new PatientDiagnosis();
						$pdiag->ipid = $post['ipid'];
						$pdiag->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
						$pdiag->diagnosis_type_id = $diagabbs[$post[$blockname]['diag_hdnd'][$rownumber]];
						$pdiag->diagnosis_id = $diagtextid;
						$pdiag->icd_id = "";
						$pdiag->save();
						
						$post[$blockname]['diag_pdid'][$rownumber]=$pdiag->id;
						$post[$blockname]['diag_dbid'][$rownumber]=$diagtextid;
						}
				}
			}
		} */
		
		$psyanamtext = "";
		$fam = array(
				"vater_age",
            	"vater_note", 
            	"vater_died", 
            	"vater_contacty", 
            	"vater_contactn", 
            	"mutter_age", 
            	"mutter_note",
            	"mutter_died",
            	"mutter_contacty", 
            	"mutter_contactn", 
            	"partner_age",
            	"partner_note",
            	"partner_died",
            	"partner_contacty",
            	"partner_contactn",
            	"geschwister_age",
            	"geschwister_note",
            	"geschwister_died",
            	"geschwister_contacty",
            	"geschwister_contactn",
            	"kinder_age",
            	"kinder_note",
            	"kinder_died",
            	"kinder_contacty",
            	"kinder_contactn");
		$status =  array(
				"wohnsituation_allein",
				"wohnsituation_angehoerige",
				"wohnsituation_Zuhause",
				"wohnsituation_Pflegeheim",
				"Patientenverfuegung_verfuegung",
				"Vorsorgevollmacht_vollmacht",
				"Vorsorgevollmacht_selectedname",
				"betreuung_betreuung",
				"betreuung_selectedname",
				"Pflegestufe_stufe",
				"Pflegestufe_hoeher",
				"Pflegestufe_neuantrag",
				"Hilfsmittelversorgung_freetext",
				"wunschsterbeort_val",
				"memopsysoz_freetext"
		);
		$leben = array(
				"migrationshintergrund",
				"migrationshintergrund_freetext",
				"migrationshintergrund",
				"dolmetscher",
				"dolmetscher_freetext",
				"religion",
				"religion_freetext"
		);
		
		foreach ($post[$blockname] as $key=>$val)
		{
			if (is_array($val))
			{				
				foreach($val as $arrval)
					{
					$cust = new FormBlockKeyValue();
					$cust->ipid = $post['ipid'];
					$cust->contact_form_id = $post['contact_form_id'];
					$cust->block = $blockname;
					$cust->k = $key;
					$cust->v = $arrval;
					
					//TODO-3219 Carmen 18.06.2020
					$pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse');
					$pc_listener->setOption('disabled', true);
					//--
					
					$cust->save();
					
					if(in_array($key, $fam)) {
						$famval[$key][] = $arrval;
					}
					else if(in_array($key, $status)) {
						$statusval[$key][] = $arrval;
					}
					else if(in_array($key, $leben)) {
						$lebenval[$key][] = $arrval;
					}
				}
			} 
			else
			{
				$cust = new FormBlockKeyValue();
				$cust->ipid = $post['ipid'];
				$cust->contact_form_id = $post['contact_form_id'];
				$cust->block = $blockname;
				$cust->k = $key;
				$cust->v = $val;
				//$cust->save(); //TODO-3219 Carmen 18.06.2020
				
				if(in_array($key, $fam)) {
					$famval[$key] = $val;
				}
				else if(in_array($key, $status)) {
					$statusval[$key] = $val;
				}
				else if(in_array($key, $leben)) {
					$lebenval[$key] = $val;
				}
				else if($key == 'beruf') {
					$beruf = $val;
				}
				//TODO-3219 Carmen 18.06.2020
				if ($save_2_PC[$blockname]
						&& in_array($blockname, $allowed_blocks)
						&& ! empty($coursecomment[$blockname])
						&& ($blockname == 'lmu_pmba_wishes' || $blockname == 'lmu_pmba_aufklaerung')
						&& $this->_clientModules['231']
						&& ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
				{
					$coursetitle = $this->translate($blockname) .": ";
					$commvals = "";
					foreach($coursecomment[$blockname] as $comval)
					{
						$coursetitle .= $comval;
						$commvals .= $comval;
					}
					
					if($commvals != "")
					{
						$change_date = "";//removed from pc; ISPC-2071
						$pc_listener->setOption('disabled', false);
						$pc_listener->setOption('course_title', $coursetitle . $change_date);
						$pc_listener->setOption('tabname', $blockname);
						$pc_listener->setOption('done_date', $done_date);
						$pc_listener->setOption('user_id', $userid);
					}
					else
					{
						$pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse');
						$pc_listener->setOption('disabled', true);
					}
				
				}
				else 
				{
					$pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse');
					$pc_listener->setOption('disabled', true);
				}
				$cust->save();
				//--
			}
		}
		//var_dump($statusval);
		switch ($blockname){
            /* case 'lmu_pmba_allergien':
                if($post[$blockname]['course']){
                    $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($post['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
                    $pc = new PatientCourse();
                    $pc->ipid = $post['ipid'];
                    $pc->course_date = $cust->course_date;
                    $pc->done_date = $cust->done_date;
                    $pc->course_type=Pms_CommonData::aesEncrypt("C");
                    $pc->course_title=Pms_CommonData::aesEncrypt($post[$blockname]['text']);
                    $pc->user_id = $userid;
                    $pc->save();
                }
                if($post[$blockname]['text']){
                    $aller_port['ipid'] = $post['ipid'];
                    $aller_port['allergies_comment'] = $post[$blockname]['text'];
                    $aller_port['allergies_comment'] = str_replace("\n","<br />",$aller_port['allergies_comment']);

                    $aller = new PatientDrugPlanAllergies();
                    $allergies = $aller->getPatientDrugPlanAllergiesbyIpid($post['ipid']);

                    $med_form_allergies = new Application_Form_PatientDrugPlanAllergies();

                    if (!empty($allergies))
                    {
                        $med_form_allergies->UpdateData($aller_port);
                    }
                    else
                    {
                        $med_form_allergies->InsertData($aller_port);
                    }
                }
                break;
			case 'lmu_contactform_sozatalk':
				$l=array('psysoz'=>'Psychosozial', 'orga'=>'Organisation der Weiterversorung', 'recht'=>'Sozialrechtliche Beratung');
				$coursetext="";
				foreach ($l as $key=>$val){
					$text=Pms_CommonData::splitPseudoMs($post[$blockname][$key]);
					if ($text) $coursetext = $coursetext . $val . ": " . $text . "<br>";
					}
                if ($post[$blockname]['text']) $coursetext=$coursetext . $post[$blockname]['text'] . "<br>";
				break;
			case 'lmu_contactform_atem':
				$l=array('themen'=>'Themen');
				$coursetext="";
				foreach ($l as $key=>$val){
					$text=implode(", ",$post[$blockname][$key]);
					if ($text) $coursetext = $coursetext . $val . ": " . $text . "<br>";
					}
				if($post[$blockname]['text']) {
					$coursetext = $coursetext . $post[$blockname]['text'] . "<br>";
					}
				break; */
/* 			case 'lmu_pmba_anamnese':
                    $lmu_tm = new WeeklyMeeting();
					$lmu_tm->week=0;
					$lmu_tm->ipid=$post['ipid'];
					$lmu_tm->date=date('Y-m-d 00:00:00');
					$probtext="";
					foreach (array('mainprobs1', 'mainprobs2', 'mainprobs3') as $val){
						$probval= $post[$blockname][$val];
						if ($probval){
							$probtext = $probtext . $probval . '$n';
							}
						}
					$lmu_tm->main_problems = Pms_CommonData::aesEncrypt($probtext);
					//add Massnahmen if exists
					if ($post['lmu_pmba_massnahmen']){
						$cat_list = array('medic', 'care', 'psy', 'social', 'spiritual', 'physio', 'breath'); 
	
						foreach($cat_list as $cat){
							$plan = $cat.'_plan';
							$goal = $cat.'_goal';
							$lmu_tm->$plan = Pms_CommonData::aesEncrypt($post['lmu_pmba_massnahmen'][$plan]);
							$lmu_tm->$goal = Pms_CommonData::aesEncrypt($post['lmu_pmba_massnahmen'][$goal]);
							}
						}

					$lmu_tm->save();

					
				break; */
			 case 'lmu_pmba_psysoz':
				if($post[$blockname]['Pflegestufe_stufe']){
    				// check existing values
				    $pms = new PatientMaintainanceStage();
				    $pat_pms = $pms->getLastpatientMaintainanceStage($post['ipid']);
				    
// 					$pflegestufen = array('Keine Pflegestufe'=>'keine', 'Pflegestufe 0'=>'0', 'Pflegestufe 1'=>'1', 'Pflegestufe 2'=>'2','Pflegestufe 3'=>'3','Pflegestufe 3+'=>'3+');
					
					$pflegestufen = $pms->get_MaintainanceStage_array();
					
// 					$new_pflegestufe=$pflegestufen[$post[$blockname]['Pflegestufe_stufe']];
					$new_pflegestufe = $post[$blockname]['Pflegestufe_stufe'] ;
					
					$Pflegestufe_was_changed = false;
					
					if( $new_pflegestufe != $pat_pms[0]['stage']  || empty($pat_pms[0]['stage'])
							||  $pat_pms[0]['erstantrag'] !=  (int)$post[$blockname]['Pflegestufe_neuantrag']
							||  $pat_pms[0]['horherstufung'] != (int)$post[$blockname]['Pflegestufe_hoeher']
					){
    					
						$informpost=array('ipid'=>$post['ipid'], 'chkval'=>$new_pflegestufe);
    					
    					$informpost['erstantrag'] = (int)$post[$blockname]['Pflegestufe_neuantrag'];
    					$informpost['horherstufung'] = (int)$post[$blockname]['Pflegestufe_hoeher'];
    					$informpost['id'] = $pat_pms[0]['id'];
    					
	       				$mainform =  new Application_Form_PatientMaintainanceStage();
			     		$mainform->InsertData($informpost);
			     		
			     		
			     		$Pflegestufe_was_changed = true;
			     		
			     		
					}
				}
					
				/* if($post[$blockname]['hausarzt_id']){
					$pm=Doctrine::getTable('PatientMaster')->findOneByIpid($post['ipid']);
					$pm->familydoc_id=$post[$blockname]['hausarzt_id'][0];
					$pm->save();
					} */
					
				$religion_was_changed =  false;
                if($post[$blockname]['religion']){
                    $custpm=Doctrine::getTable('PatientReligions')->findOneByIpid($post['ipid']);
                    if($custpm){
                    	if ( $custpm->religion != $post[$blockname]['religion'] ) {
                        	$custpm->religion=$post[$blockname]['religion'];
                        	$custpm->save();
                        	
                        	$religion_was_changed = true;
                    	}
                    } else {
                        $new_rel= new PatientReligions();
                        $new_rel->ipid = $post['ipid'];
                        $new_rel->religion = $post[$blockname]['religion'];
                        $new_rel->save();
                        
                        $religion_was_changed = true;
                    }
                }


                /* $pfldids=array();
				foreach (array('pflegedienst_id', 'palliativpflegedienst_id') as $pfld){
					if(is_array($post[$blockname][$pfld])){
                        foreach ($post[$blockname][$pfld] as $pfld_id){
                            $pfldids[]=$pfld_id;
                            if($pfld_id>0){
                                $pm=Doctrine::getTable('PatientPflegedienste')->findOneByIpidAndPflidAndIsdelete($post['ipid'], $pfld_id,0);
                                if (!$pm){
                                    $ppd=new PatientPflegedienste();
                                    $ppd->ipid=$post['ipid'];
                                    $ppd->pflid=$pfld_id;
                                    $ppd->save();
                                    }
                                }
                            }
						}
					}
                //delete other Pflegedienste
                PatientPflegedienste::keepOnly($post['ipid'], $pfldids); */



/*                 //Apotheken
                $apotheken_ids=array();
                    if(is_array($post[$blockname]['apotheke_id'])){
                        foreach ($post[$blockname]['apotheke_id'] as $apo_id){
                            $apotheken_ids[]=$apo_id;
                            if($apo_id>0){

                                $pm=Doctrine::getTable('PatientPharmacy')->findOneByIpidAndPharmacyIdAndIsdelete($post['ipid'], $apo_id,0);

                                if (!$pm){
                                    $ppd=new PatientPharmacy();
                                    $ppd->ipid=$post['ipid'];
                                    $ppd->pharmacy_id=$apo_id;
                                    $ppd->save();
                                }
                            }
                        }
                    }
                //delete other Apotheken
                PatientPharmacy::keepOnly($post['ipid'], $apotheken_ids); */



                //Kontaktperson
/*                 $names_to_contact=array();

                if(is_array($post[$blockname]['ansprechpartner_first_name'])){
                    foreach ($post[$blockname]['ansprechpartner_first_name'] as $cntrow=>$foo){

                        $cnt=array();
                        $cnt['first_name']=$post[$blockname]['ansprechpartner_first_name'][$cntrow];
                        $cnt['last_name']=$post[$blockname]['ansprechpartner_last_name'][$cntrow];
                        $fullname=$cnt['first_name'] . ' ' . $cnt['last_name'];
                        $cnt['street']=$post[$blockname]['ansprechpartner_street'][$cntrow];
                        $cnt['zip']=$post[$blockname]['ansprechpartner_zip'][$cntrow];
                        $cnt['city']=$post[$blockname]['ansprechpartner_city'][$cntrow];
                        $cnt['phone']=$post[$blockname]['ansprechpartner_phone'][$cntrow];
                        $cnt['phone2']=$post[$blockname]['ansprechpartner_phone2'][$cntrow];
                        if($post[$blockname]['Vorsorgevollmacht_selectedname']==$fullname){
                            $cnt['vollmacht']=1;
                        }
                        if($post[$blockname]['betreuung_selectedname']==$fullname){
                            $cnt['legalc']=1;
                        }
                        if($post[$blockname]['ansprechpartner_trauerfeier'][$cntrow]=="x"){
                            $cnt['notify_funeral']=1;
                        }
                        if($post[$blockname]['ansprechpartnerqs_selectedname']==$fullname){
                            $cnt['qualitycontrol']=1;
                        }
                        $cpm = new ContactPersonMaster();
                        $cpm->addPatientContact($post['ipid'], $cnt);

                        $names_to_contact[$fullname] = $cnt;
                    }
                } */

                
                $Patientenverfuegung_was_changed = false;
                
                
                if(in_array( $post[$blockname]['Patientenverfuegung_verfuegung'] , array("Vorhanden", "abgelehnt", "nicht vorhanden"))) {
                	
                	$custpm=Doctrine::getTable('PatientMaster')->findOneByIpid($post['ipid']);
                	if ($custpm) {
                		if($post[$blockname]['Patientenverfuegung_verfuegung'] == "Vorhanden" && $custpm->living_will != 1){
                			$custpm->living_will=1;
                			$custpm->save();
                			
                			$Patientenverfuegung_was_changed = true;
                		}
                		
                		if(	($post[$blockname]['Patientenverfuegung_verfuegung'] == "abgelehnt" || $post[$blockname]['Patientenverfuegung_verfuegung'] == "nicht vorhanden")
                            && $custpm->living_will != 0) {
                			$custpm->living_will=0;
                			$custpm->save();
                			
                			$Patientenverfuegung_was_changed = true;
                		}
                	} else {
                		// else patient does not exist :)
                	}
                }
                
                
//                 if($post[$blockname]['Patientenverfuegung_verfuegung'] == "Vorhanden"){
//                     $custpm=Doctrine::getTable('PatientMaster')->findOneByIpid($post['ipid']);
//                     $custpm->living_will=1;
//                     $custpm->save();
//                 }
                
//                 if($post[$blockname]['Patientenverfuegung_verfuegung'] == "abgelehnt" || $post[$blockname]['Patientenverfuegung_verfuegung'] == "nicht vorhanden"){
//                     $custpm=Doctrine::getTable('PatientMaster')->findOneByIpid($post['ipid']);
//                     $custpm->living_will=0;
//                     $custpm->save();
//                 }

                //Update SAPV-Stammblatt-Data
                /* $sapvdata=array();
                if($post[$blockname]['Vorsorgevollmacht_vollmacht'] == "Vorhanden"){
                    $sapvdata['vorsorgevollmacht']=1;
                }
                if($post[$blockname]['Vorsorgevollmacht_vollmacht'] == "geplant"){
                    $sapvdata['vorsorgevollmacht']=3;
                }
                if($post[$blockname]['Vorsorgevollmacht_vollmacht'] == "abgelehnt" || $post[$blockname]['Vorsorgevollmacht_vollmacht'] == "nicht vorhanden"){
                    $sapvdata['vorsorgevollmacht']=2;
                }
                if($post[$blockname]['Vorsorgevollmacht_selectedname'] != "Auswählen"){
                    $sapvdata['bevollmachtigter']=$post[$blockname]['Vorsorgevollmacht_selectedname'];
                    $sapvdata['bevollmachtigter_tel']=$names_to_contact[$post[$blockname]['Vorsorgevollmacht_selectedname']]['phone'];
                }
                if($post[$blockname]['betreuung_betreuung'] == "Vorhanden"){
                    $sapvdata['betreuung']=1;
                }
                if($post[$blockname]['betreuung_betreuung'] == "angeregt/geplant" || $post[$blockname]['betreuung_betreuung'] == "nicht erforderlich/bekannt"){
                    $sapvdata['betreuung']=0;
                }
                if($post[$blockname]['betreuung_betreuung'] == "abgelehnt" || $post[$blockname]['betreuung_betreuung'] == "nicht vorhanden"){
                    $sapvdata['betreuung']=2;
                }
                if($post[$blockname]['betreuung_selectedname'] != "Auswählen"){
                    $sapvdata['betreuer']=$post[$blockname]['betreuung_selectedname'];
                    $sapvdata['betreuer_tel']=$names_to_contact[$post[$blockname]['betreuung_selectedname']]['phone'];
                }
                if(strpos('..'.strtolower($post[$blockname]['religion']), "katholisch")>0){
                    $sapvdata['religion']=2;
                }
                if(strpos('..'.strtolower($post[$blockname]['religion']), "evangelisch")>0){
                    $sapvdata['religion']=1;
                }
                Stammblattsapv::updatePatiententry($post['ipid'], $sapvdata); */
                $rl = new PatientReligions();
                $religions = $rl->getReligionsNames(true);
                
                $psyanamtext = 'Psychosoziale Anamnese';
                
                $text_array = array();
                
                if(!empty($famval)) {
                	
                	/**
                	 * FATHER
                	 */
                	$label_row = "Vater: ";
                	$vater_from_post = "";
                    if ($famval['vater_age'] != '' || $famval['vater_note'] != '') {
                		$vater_age = '';
                		if ($famval['vater_age'] != '') {
                			$vater_age = " (" . $famval['vater_age'] .")";
                		}
                		$vater_note = '';
                		if ($famval['vater_note'] != '') {
                			$vater_note = " " . $famval['vater_note'];
                		}
                		$vater_died = '';
                		if ($famval['vater_died'] == '1') {
                			$vater_died = "; Tod: Ja";
                		}
                		$vater_contacty = '';
                		if ($famval['vater_contacty'] == '1') {
                			$vater_contacty = "; Kontakt: Ja";
                		}

                		$vater_from_post = sprintf( $label_row . "%s%s%s%s" , $vater_age, $vater_note, $vater_died, $vater_contacty);

                	}                 	
                	if ( $is_new_contactform && $vater_from_post != '') {
                		$text_array[] = $vater_from_post;
                    } elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                		//look if any  modified
                		if ( $famval['vater_age'] != $lmu_pmba_psysoz_oldValues['vater_age']
                				||
                				$famval['vater_note'] != $lmu_pmba_psysoz_oldValues['vater_note']
                				||
                				$famval['vater_died'] != $lmu_pmba_psysoz_oldValues['vater_died']
                				||	
                				$famval['vater_contacty'] != $lmu_pmba_psysoz_oldValues['vater_contacty']	
                		) {
                			$vater_from_old = "";
                            if ($lmu_pmba_psysoz_oldValues['vater_age'] != '' || $lmu_pmba_psysoz_oldValues['vater_note'] != '') {
                				$vater_age = '';
                				if ($lmu_pmba_psysoz_oldValues['vater_age'] != '') {
                					$vater_age = " (" . $lmu_pmba_psysoz_oldValues['vater_age'] .")";
                				}
                				$vater_note = '';
                				if ($lmu_pmba_psysoz_oldValues['vater_note'] != '') {
                					$vater_note = " " . $lmu_pmba_psysoz_oldValues['vater_note'];
                				}
                				$vater_died = '';
                				if ($lmu_pmba_psysoz_oldValues['vater_died'] == '1') {
                					$vater_died = "; Tod: Ja";
                				}
                				$vater_contacty = '';
                				if ($lmu_pmba_psysoz_oldValues['vater_contacty'] == '1') {
                					$vater_contacty = "; Kontakt: Ja";
                				}
                			
                				$vater_from_old = sprintf($label_row . "%s%s%s%s" , $vater_age, $vater_note, $vater_died, $vater_contacty) ;
                			
                			}
                			if ($vater_from_old != $vater_from_post && $vater_from_old!='') {
                				//$ _from_post remove $label_row;
                				$vater_from_post = trim( substr($vater_from_post, strlen($label_row) ));
                				$text_array[] = $vater_from_old . " -> " . $vater_from_post;
                            } elseif ($vater_from_post != '') {
                				$text_array[] = $vater_from_post;
                			}
                		}

                	}
                	
                	
                	/**
                	 * MOTHER
                	 */
                	$mutter_from_post = "";
                	$label_row = 'Mutter: ';
                    if ($famval['mutter_age'] != '' || $famval['mutter_note'] != '') {
                		$mutter_age = '';
                		if ($famval['mutter_age'] != '') {
                			$mutter_age = " (" . $famval['mutter_age'] .")";
                		}
                		$mutter_note = '';
                		if ($famval['mutter_note'] != '') {
                			$mutter_note = " " . $famval['mutter_note'];
                		}
                		$mutter_died = '';
                		if ($famval['mutter_died'] == '1') {
                			$mutter_died = "; Tod: Ja";
                		}
                		$mutter_contacty = '';
                		if ($famval['mutter_contacty'] == '1') {
                			$mutter_contacty = "; Kontakt: Ja";
                		}
                	
                		$mutter_from_post = sprintf($label_row . "%s%s%s%s" , $mutter_age, $mutter_note, $mutter_died, $mutter_contacty);
                	
                	}
                	if ( $is_new_contactform && $mutter_from_post != '') {
                		$text_array[] = $mutter_from_post;
                    } elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                		//look if any what fields have been modified
                		if ( $famval['mutter_age'] != $lmu_pmba_psysoz_oldValues['mutter_age']
                				||
                				$famval['mutter_note'] != $lmu_pmba_psysoz_oldValues['mutter_note']
                				||
                				$famval['mutter_died'] != $lmu_pmba_psysoz_oldValues['mutter_died']
                				||
                				$famval['mutter_contacty'] != $lmu_pmba_psysoz_oldValues['mutter_contacty']
                		) {
                			$mutter_from_old = "";
                            if ($lmu_pmba_psysoz_oldValues['mutter_age'] != '' || $lmu_pmba_psysoz_oldValues['mutter_note'] != '') {
                				$mutter_age = '';
                				if ($lmu_pmba_psysoz_oldValues['mutter_age'] != '') {
                					$mutter_age = " (" . $lmu_pmba_psysoz_oldValues['mutter_age'] .")";
                				}
                				$mutter_note = '';
                				if ($lmu_pmba_psysoz_oldValues['mutter_note'] != '') {
                					$mutter_note = " " . $lmu_pmba_psysoz_oldValues['mutter_note'];
                				}
                				$mutter_died = '';
                				if ($lmu_pmba_psysoz_oldValues['mutter_died'] == '1') {
                					$mutter_died = "; Tod: Ja";
                				}
                				$mutter_contacty = '';
                				if ($lmu_pmba_psysoz_oldValues['mutter_contacty'] == '1') {
                					$mutter_contacty = "; Kontakt: Ja";
                				}
                				 
                				$mutter_from_old = sprintf($label_row . "%s%s%s%s" , $mutter_age, $mutter_note, $mutter_died, $mutter_contacty) ;
                				 
                			}
                			if ($mutter_from_old != $mutter_from_post && $mutter_from_old !='') {
                				$mutter_from_post = trim( substr($mutter_from_post, strlen($label_row) ));
                				$text_array[] = $mutter_from_old . " -> " . $mutter_from_post;
                            } elseif ($mutter_from_post != '') {
                				$text_array[] = $mutter_from_post;
                			}
                		}
                	
                	}
                	
                	/**
                	 * PARTNER
                	 */
                	$partner_from_post = "";
                	$label_row = 'Partner: ';
                    if ($famval['partner_age'] != '' || $famval['partner_note'] != '') {
                		$partner_age = '';
                		if ($famval['partner_age'] != '') {
                			$partner_age = " (" . $famval['partner_age'] .")";
                		}
                		$partner_note = '';
                		if ($famval['partner_note'] != '') {
                			$partner_note = " " . $famval['partner_note'];
                		}
                		$partner_died = '';
                		if ($famval['partner_died'] == '1') {
                			$partner_died = "; Tod: Ja";
                		}
                		$partner_contacty = '';
                		if ($famval['partner_contacty'] == '1') {
                			$partner_contacty = "; Kontakt: Ja";
                		}
                	
                		$partner_from_post = sprintf($label_row . "%s%s%s%s" , $partner_age, $partner_note, $partner_died, $partner_contacty);
                	
                	}
                	if ( $is_new_contactform && $partner_from_post != '') {
                		$text_array[] = $partner_from_post;
                    } elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                		//look if any modified
                		if ( $famval['partner_age'] != $lmu_pmba_psysoz_oldValues['partner_age']
                				||
                				$famval['partner_note'] != $lmu_pmba_psysoz_oldValues['partner_note']
                				||
                				$famval['partner_died'] != $lmu_pmba_psysoz_oldValues['partner_died']
                				||
                				$famval['partner_contacty'] != $lmu_pmba_psysoz_oldValues['partner_contacty']
                		) {
                			$partner_from_old = "";
                            if ($lmu_pmba_psysoz_oldValues['partner_age'] != '' || $lmu_pmba_psysoz_oldValues['partner_note'] != '') {
                				$partner_age = '';
                				if ($lmu_pmba_psysoz_oldValues['partner_age'] != '') {
                					$partner_age = " (" . $lmu_pmba_psysoz_oldValues['partner_age'] .")";
                				}
                				$partner_note = '';
                				if ($lmu_pmba_psysoz_oldValues['partner_note'] != '') {
                					$partner_note = " " . $lmu_pmba_psysoz_oldValues['partner_note'];
                				}
                				$partner_died = '';
                				if ($lmu_pmba_psysoz_oldValues['partner_died'] == '1') {
                					$partner_died = "; Tod: Ja";
                				}
                				$partner_contacty = '';
                				if ($lmu_pmba_psysoz_oldValues['partner_contacty'] == '1') {
                					$partner_contacty = "; Kontakt: Ja";
                				}
                				 
                				$partner_from_old = sprintf($label_row . "%s%s%s%s" , $partner_age, $partner_note, $partner_died, $partner_contacty) ;
                				 
                			}
                			if ($partner_from_old != $partner_from_post && $partner_from_old != '') {
                				$partner_from_post = trim( substr($partner_from_post, strlen($label_row) ));
                				$text_array[] = $partner_from_old . " -> " . $partner_from_post;
                			} elseif ($partner_from_post != '') {
                				$text_array[] = $partner_from_post;
                			}
                		}
                	
                	}
                	
                	
                	/**
                	 * foreach GESCHWISTER - sister/brother
                	 */
                	foreach ($famval['geschwister_age'] as $geschwister_key => $geschwister_age_value) {
                		/**
                		 * ONE GESCHWISTER - sister/brother
                		 */
                		$geschwister_from_post = "";
                		$label_row = 'Geschwister: ';
                        if ($famval['geschwister_age'][$geschwister_key] != '' || $famval['geschwister_note'][$geschwister_key] != '') {
                			$geschwister_age = '';
                			if ($famval['geschwister_age'][$geschwister_key] != '') {
                				$geschwister_age = " (" . $famval['geschwister_age'][$geschwister_key] .")";
                			}
                			$geschwister_note = '';
                			if ($famval['geschwister_note'] != '') {
                				$geschwister_note = " " . $famval['geschwister_note'][$geschwister_key];
                			}
                			$geschwister_died = '';
                			if ($famval['geschwister_died'][$geschwister_key] == '1') {
                				$geschwister_died = "; Tod: Ja";
                			}
                			$geschwister_contacty = '';
                			if ($famval['geschwister_contacty'][$geschwister_key] == '1') {
                				$geschwister_contacty = "; Kontakt: Ja";
                			}
                		
                			$geschwister_from_post = sprintf($label_row . "%s%s%s%s" , $geschwister_age, $geschwister_note, $geschwister_died, $geschwister_contacty);
                			$geschwister_from_post = trim($geschwister_from_post);
                		
                		}
                		if ( $is_new_contactform && $geschwister_from_post != '') {
                			$text_array[] = $geschwister_from_post;
                        } elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			if ( $famval['geschwister_age'][$geschwister_key] != $lmu_pmba_psysoz_oldValues['geschwister_age'][$geschwister_key]
                					||
                					$famval['geschwister_note'][$geschwister_key] != $lmu_pmba_psysoz_oldValues['geschwister_note'][$geschwister_key]
                					||
                					$famval['geschwister_died'][$geschwister_key] != $lmu_pmba_psysoz_oldValues['geschwister_died'][$geschwister_key]
                					||
                					$famval['geschwister_contacty'][$geschwister_key] != $lmu_pmba_psysoz_oldValues['geschwister_contacty'][$geschwister_key]
                			) {
                				$geschwister_from_old = "";
                                if ($lmu_pmba_psysoz_oldValues['geschwister_age'][$geschwister_key] != '' || $lmu_pmba_psysoz_oldValues['geschwister_note'][$geschwister_key] != '') {
                					$geschwister_age = '';
                					if ($lmu_pmba_psysoz_oldValues['geschwister_age'][$geschwister_key] != '') {
                						$geschwister_age = " (" . $lmu_pmba_psysoz_oldValues['geschwister_age'][$geschwister_key] .")";
                					}
                					$geschwister_note = '';
                					if ($lmu_pmba_psysoz_oldValues['geschwister_note'][$geschwister_key] != '') {
                						$geschwister_note = " " . $lmu_pmba_psysoz_oldValues['geschwister_note'][$geschwister_key];
                					}
                					$geschwister_died = '';
                					if ($lmu_pmba_psysoz_oldValues['geschwister_died'][$geschwister_key] == '1') {
                						$geschwister_died = "; Tod: Ja";
                					}
                					$geschwister_contacty = '';
                					if ($lmu_pmba_psysoz_oldValues['geschwister_contacty'][$geschwister_key] == '1') {
                						$geschwister_contacty = "; Kontakt: Ja";
                					}
                					 
                					$geschwister_from_old = sprintf($label_row . "%s%s%s%s" , $geschwister_age, $geschwister_note, $geschwister_died, $geschwister_contacty) ;
                					$geschwister_from_old = trim($geschwister_from_old);
                					 
                				}
                				if ($geschwister_from_old != $geschwister_from_post && $geschwister_from_old != '') {
                					$geschwister_from_post = trim( substr($geschwister_from_post, strlen($label_row) ));
                					$text_array[] = $geschwister_from_old . " -> " . $geschwister_from_post;
                                } elseif ($geschwister_from_post != '') {
	                				$text_array[] = $geschwister_from_post;
	                			}
                			}
                		
                		}
                		
                	}
                	
                	
                	/**
                	 * foreach KINDER
                	 */
                	foreach ($famval['kinder_age'] as $kinder_key => $kinder_age_value) {
                		/**
                		 * ONE kinder
                		 */
                		$kinder_from_post = "";
                		$label_row = 'Kinder: ';
                        if ($famval['kinder_age'][$kinder_key] != '' || $famval['kinder_note'][$kinder_key] != '') {
                			$kinder_age = '';
                			if ($famval['kinder_age'][$kinder_key] != '') {
                				$kinder_age = " (" . $famval['kinder_age'][$kinder_key] .")";
                			}
                			$kinder_note = '';
                			if ($famval['kinder_note'] != '') {
                				$kinder_note = " " . $famval['kinder_note'][$kinder_key];
                			}
                			$kinder_died = '';
                			if ($famval['kinder_died'][$kinder_key] == '1') {
                				$kinder_died = "; Tod: Ja";
                			}
                			$kinder_contacty = '';
                			if ($famval['kinder_contacty'][$kinder_key] == '1') {
                				$kinder_contacty = "; Kontakt: Ja";
                			}
                	
                			$kinder_from_post = sprintf($label_row . "%s%s%s%s" , $kinder_age, $kinder_note, $kinder_died, $kinder_contacty);
                			$kinder_from_post = trim($kinder_from_post);
                	
                		}
                		if ( $is_new_contactform && $kinder_from_post != '') {
                			$text_array[] = $kinder_from_post;
                        } elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			if ( $famval['kinder_age'][$kinder_key] != $lmu_pmba_psysoz_oldValues['kinder_age'][$kinder_key]
                					||
                					$famval['kinder_note'][$kinder_key] != $lmu_pmba_psysoz_oldValues['kinder_note'][$kinder_key]
                					||
                					$famval['kinder_died'][$kinder_key] != $lmu_pmba_psysoz_oldValues['kinder_died'][$kinder_key]
                					||
                					$famval['kinder_contacty'][$kinder_key] != $lmu_pmba_psysoz_oldValues['kinder_contacty'][$kinder_key]
                			) {
                				$kinder_from_old = "";
                                if ($lmu_pmba_psysoz_oldValues['kinder_age'][$kinder_key] != '' || $lmu_pmba_psysoz_oldValues['kinder_note'][$kinder_key] != '') {
                					$kinder_age = '';
                					if ($lmu_pmba_psysoz_oldValues['kinder_age'][$kinder_key] != '') {
                						$kinder_age = " (" . $lmu_pmba_psysoz_oldValues['kinder_age'][$kinder_key] .")";
                					}
                					$kinder_note = '';
                					if ($lmu_pmba_psysoz_oldValues['kinder_note'][$kinder_key] != '') {
                						$kinder_note = " " . $lmu_pmba_psysoz_oldValues['kinder_note'][$kinder_key];
                					}
                					$kinder_died = '';
                					if ($lmu_pmba_psysoz_oldValues['kinder_died'][$kinder_key] == '1') {
                						$kinder_died = "; Tod: Ja";
                					}
                					$kinder_contacty = '';
                					if ($lmu_pmba_psysoz_oldValues['kinder_contacty'][$kinder_key] == '1') {
                						$kinder_contacty = "; Kontakt: Ja";
                					}
                	
                					$kinder_from_old = sprintf($label_row . "%s%s%s%s" , $kinder_age, $kinder_note, $kinder_died, $kinder_contacty) ;
                					$kinder_from_old = trim($kinder_from_old);
                	
                				}
                				if ($kinder_from_old != $kinder_from_post && $kinder_from_old != '') {
                					$kinder_from_post = trim( substr($kinder_from_post, strlen($label_row) ));
                					$text_array[] = $kinder_from_old . " -> " . $kinder_from_post;
                                } elseif ($kinder_from_post != '') {
	                				$text_array[] = $kinder_from_post;
	                			}
                			}
                	
                		}
                	
                	}
                	
                	
                	
                	
                }
                
                
//                 die(print_r($text_array));
                
                /*
                if(!empty($famval)) {
                
                	$rad = '';
                	$psyanamtext .= "\n<b>Familie und soziale Strukturen</b>";
                	$psyanamlength = strlen($psyanamtext);
                	$coursetextarr = array();
                	foreach ($famval as $kv=>$vv) {

                		$radkv = explode('_', $kv);
                		
                		//this IF if only for vater, mutter , partner
                		if(!is_array($vv)) {
                			
                			$sign_arrow_edited = "";
                			
	                		if ($rad != $radkv[0]) {
	                			$rad = $radkv[0];
	                			
	                			
	                			
	                			if($radkv[1] == 'age') {
	                				
	                				//add row label for edited ones
	                				$father_mather_parner_edit_row_label = "\n".ucfirst($rad) . ": ";
	                				
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv] != $vv) {
	                					//new value was inserted
	                					if($lmu_pmba_psysoz_oldValues[$kv] !='') {
	                						$sign_arrow_edited = " -> ";
	                					}
	                					$psyanamtext .= $father_mather_parner_edit_row_label .' ('. $lmu_pmba_psysoz_oldValues[$kv] . $sign_arrow_edited .$vv.') ';
	                					//destroy label, so is only one per row
	                					$father_mather_parner_edit_row_label = "";
	                				}
	                				elseif( empty($lmu_pmba_psysoz_oldValues) && $vv != '') {
	                					$psyanamtext .= "\n".ucfirst($rad).' ('.$vv.') ';
	                				}
	                			}
	                		}
	                		else {
	                			if($vv != '1' && $vv != '') {
	                				//this is the note = comment = Bemerkung for vater, mutter..
	                				// if comments is deleted it will not be inserted !
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv] != $vv) {
	                					//new value
	                					if($lmu_pmba_psysoz_oldValues[$kv] !='') {
	                						$sign_arrow_edited = " -> ";
	                					}
	                					$psyanamtext .= $father_mather_parner_edit_row_label .  $lmu_pmba_psysoz_oldValues[$kv] . $sign_arrow_edited .$vv.' ' ;
	                					$father_mather_parner_edit_row_label = "";
	                					
	                				} else if (empty($lmu_pmba_psysoz_oldValues)) {
	                					$psyanamtext .= $vv.' ' ;
	                				}
	                			}
	                			else {//die($kv ."-?" . $vv);
	                				
	                				
	                				if(! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv] != $vv)
	                				{
	                					//edited
//  (print_r($post));
//  die(print_r($lmu_pmba_psysoz_oldValues));
		                				if($radkv[1] == 'died' && $vv == '1') {
		                					$psyanamtext .= $father_mather_parner_edit_row_label . ';Tod: Ja';
		                					$father_mather_parner_edit_row_label = '';
		                				} elseif($radkv[1] == 'died') {
		                					$psyanamtext .= $father_mather_parner_edit_row_label . ';Tod: Ja->Nein';
		                					$father_mather_parner_edit_row_label = '';
		                				}
		                				
		                				
		                				if($radkv[1] == 'contacty' && $vv == '1') {
		                					$psyanamtext .= $father_mather_parner_edit_row_label . '; Kontakt: Ja';
		                					$father_mather_parner_edit_row_label = '';
		                				} elseif($radkv[1] == 'contacty') {
		                					$psyanamtext .= $father_mather_parner_edit_row_label . '; Kontakt: Ja->Nein';
		                					$father_mather_parner_edit_row_label = '';
		                				}

	                				
	                				}
	                				else {
	                					
	                					if($radkv[1] == 'died' && $vv == '1') {
	                						$psyanamtext .= "\n".ucfirst($rad) . ": ".';Tod: Ja +1';
	                					}
	                					if($radkv[1] == 'contacty' && $vv == '1') {
	                						$psyanamtext .= "\n".ucfirst($rad) . ": ".'; Kontakt: Ja +' . $lmu_pmba_psysoz_oldValues[$kv];
	                					}
	                				}
	                				
	                				
	                			}
	                			
	                		}
                		
                		}
                		//this ELSE if for geschwister and kinder
	                	else {
	                		if($rad != $radkv[0]) {
	                			$rad = $radkv[0];
	                		
	                			$psyanamtext .= implode(" ", $coursetextarr);
	
	                			$coursetextarr = array();
	                		}
	                		
	                		if($radkv[1] == 'age') {
	                			foreach($vv as $k=>$valg) {     

	                				
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv][$k] != $valg) {
	                					//modified value
	                					$coursetextarr[] = "\n".ucfirst($rad).' ('.$lmu_pmba_psysoz_oldValues[$kv][$k] . "->" .$valg.') ';
	                				} else if (empty($lmu_pmba_psysoz_oldValues)){
	                					if($valg != '') {
	                						$coursetextarr[] = "\n".ucfirst($rad).' ('.$valg.') ';
	                					}
	                				}
	                				               				              			
	                			}
	                			
	                		}
	                		
	                		$i=0;
	                		if($radkv[1] == 'note') { 
	                			foreach($vv as $k=>$valg) {
	                				
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv][$k] != $valg) {
	                					//modified value
	                					$coursetextarr[$i] .= $lmu_pmba_psysoz_oldValues[$kv][$k]. "->" .$valg.' ';
	                				} else if (empty($lmu_pmba_psysoz_oldValues)){
	                					if($valg != '') {
	                						$coursetextarr[$i] .= $valg.' ';
	                					}
	                				}
	                				$i++;
	                			}
	                		}
	                			
	                		$i = 0;
	                		if($radkv[1] == 'died') {
	                			foreach($vv as $k=>$valg) {
	                				
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv][$k] != $valg) {
	                					//modified value
	                					
	                					if($valg == '1') {
	                						$coursetextarr[$i] .= ';Tod: Nein->Ja';
	                					} else {
	                						$coursetextarr[$i] .= ';Tod: Ja->Nein';
	                					}

	                				} else if (empty($lmu_pmba_psysoz_oldValues)){
		                				if($valg == '1') {
		                					$coursetextarr[$i] .= ';Tod: Ja';
		                				}         
	                				}
              					
	                					$i++;
	                			}
	                		}
	                			
	                		$i=0;
	                		if($radkv[1] == 'contacty') {
	                			foreach($vv as $k=>$valg) {
	                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$kv][$k] != $valg) {
	                					if($valg == '1') {
	                						$coursetextarr[$i] .= '; Kontakt: Nein->Ja';
	                					} else {
	                						$coursetextarr[$i] .= '; Kontakt: Ja->Nein';
	                					}
	                				} else if (empty($lmu_pmba_psysoz_oldValues)){
		                				if($valg == '1') {
		                					$coursetextarr[$i] .= '; Kontakt: Ja';
		                				}
	                				} 
	                				$i++;
	                			}
	                		}
	                	}
                	}
                
                	
                		if(!empty($coursetextarr)) {
                		$psyanamtext .= implode(" ", $coursetextarr);
                		}
                		$psyanamfinallength = strlen($psyanamtext);
                		
                		if($psyanamlength == $psyanamfinallength) {
                			
                			$psyanamtext = 'Psychosoziale Anamnese';
                			
                		}
                	}

                	*/
                
                
                	$psyanamtext = '<b>Psychosoziale Anamnese</b>';
                
	                /**
	                 * Familie und soziale Strukturen
	                 */
                	if ( ! empty ($text_array)) {
                		$psyanamtext .= "\n<b>Familie und soziale Strukturen</b>";
                		$psyanamtext .= "\n" . implode("\n", $text_array);
                	}
                	
                	
                	
                	/**
                	 * Beruf/Interessen/Kontext
                	 */
                	if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues['beruf'] != $beruf) {
                		$psyanamtext .= "\n<b>Beruf/Interessen/Kontext</b>: ";
                		$psyanamtext .= $lmu_pmba_psysoz_oldValues['beruf'] . "->" . $beruf;
                	} elseif (empty($lmu_pmba_psysoz_oldValues)) {
                		if($beruf != '') {
                			$psyanamtext .= "\n<b>Beruf/Interessen/Kontext</b>: ";
                			$psyanamtext .= $beruf;
                		}
                	}
                	
                	
                	/* 
                	$psyanamtext_copy = $psyanamtext;
                	
                	$psyanamtext = "";
                	
                	 */
                	
                	/**
                	 * Psychosozialer Status
                	 */
                	$text_array = array();
                	
                	if(!empty($statusval)) {
//                 		die(print_r($post));
                		/**
                		 * Wohnsituation
                		 */
                		$Wohnsituation_from_post = "";
                		$label_row = 'Wohnsituation: ';
                		if ( $statusval['wohnsituation_allein'] == '1' 
                				|| $famval['wohnsituation_angehoerige'] == '1' 
                				|| $famval['wohnsituation_Zuhause'] == '1' 
                				|| $famval['wohnsituation_Pflegeheim'] == '1' 
                    	) {
                			$allein = '';
                			if ($statusval['wohnsituation_allein'] == '1') {
                				$allein = " allein;";
                			}
                			$angehoerige = '';
                			if ($statusval['wohnsituation_angehoerige'] == '1') {
                				$angehoerige = " mit Angehörigen;";
                			}
                			$Zuhause = '';
                			if ($statusval['wohnsituation_Zuhause'] == '1') {
                				$Zuhause = " Zuhause;";
                			}
                			$Pflegeheim = '';
                			if ($statusval['wohnsituation_Pflegeheim'] == '1') {
                				$Pflegeheim = " Pflegeheim;";
                			}
                			$Wohnsituation_from_post = sprintf($label_row . "%s%s%s%s" , $allein, $angehoerige, $Zuhause, $Pflegeheim);
                	
                		}
                		if ( $is_new_contactform && $Wohnsituation_from_post != '') {
                			$text_array[] = $Wohnsituation_from_post;
                    	} elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Wohnsituation_from_old = "";
                			if ( $statusval['wohnsituation_allein'] != $lmu_pmba_psysoz_oldValues['wohnsituation_allein']
                					||
                					$statusval['wohnsituation_angehoerige'] != $lmu_pmba_psysoz_oldValues['wohnsituation_angehoerige']
                					||
                					$statusval['wohnsituation_Zuhause'] != $lmu_pmba_psysoz_oldValues['wohnsituation_Zuhause']
                					||
                					$statusval['wohnsituation_Pflegeheim'] != $lmu_pmba_psysoz_oldValues['wohnsituation_Pflegeheim']
                			) {
                				$Wohnsituation_from_old = "";
                				                				
                				$allein = '';
                				if ($lmu_pmba_psysoz_oldValues['wohnsituation_allein'] == '1') {
                					$allein = " allein;";
                				}
                				$angehoerige = '';
                				if ($lmu_pmba_psysoz_oldValues['wohnsituation_angehoerige'] == '1') {
                					$angehoerige = " mit Angehörigen;";
                				}
                				$Zuhause = '';
                				if ($lmu_pmba_psysoz_oldValues['wohnsituation_Zuhause'] == '1') {
                					$Zuhause = " Zuhause;";
                				}
                				$Pflegeheim = '';
                				if ($lmu_pmba_psysoz_oldValues['wohnsituation_Pflegeheim'] == '1') {
                					$Pflegeheim = " Pflegeheim;";
                				}
                				$Wohnsituation_from_old = sprintf($label_row . "%s%s%s%s" , $allein, $angehoerige, $Zuhause, $Pflegeheim);
                				
                			}

                			if ($Wohnsituation_from_old != $Wohnsituation_from_post && $Wohnsituation_from_old != '' && $Wohnsituation_from_old != $label_row) {
                				$Wohnsituation_from_post = trim( substr($Wohnsituation_from_post, strlen($label_row) ));
                				$text_array[] = $Wohnsituation_from_old . " -> " . $Wohnsituation_from_post;
                        	} elseif ($Wohnsituation_from_post != '') {
                				$text_array[] = $Wohnsituation_from_post;
                			}
                	
                		}

                		
                		/**
                		 * next 3 Patientenverfügung, Vorsorgevollmacht, Betreuung  = are the 3 PatientAPC boxes, and are get/set in that table 
                		 * ISPC-2244
                		 */
                		
                		/**
                		 * Patientenverfügung Patientenverfuegung_verfuegung
                		 */
                		/*
                		 * ISPC-2244 removed next  
                		$Patientenverfuegung_from_post = "";
                		$label_row = 'Patientenverfügung: ';
                		if ( $Patientenverfuegung_was_changed && $statusval['Patientenverfuegung_verfuegung'] != 'nicht bekannt') {
                			$Patientenverfuegung_from_post = sprintf($label_row . "%s" , $statusval['Patientenverfuegung_verfuegung']);
                		}
                		if ( $is_new_contactform && $Patientenverfuegung_from_post != '') {
                			$text_array[] = $Patientenverfuegung_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Patientenverfuegung_from_old = '';
                			if ( $statusval['Patientenverfuegung_verfuegung'] != $lmu_pmba_psysoz_oldValues['Patientenverfuegung_verfuegung']) {
                				
                				$Patientenverfuegung_from_old = sprintf($label_row . "%s" , $lmu_pmba_psysoz_oldValues['Patientenverfuegung_verfuegung']);                				
                				
                				if ($Patientenverfuegung_from_old != $Patientenverfuegung_from_post && $Patientenverfuegung_from_old != '') {
                					$Patientenverfuegung_from_post = trim( substr($Patientenverfuegung_from_post, strlen($label_row) ));
                					$text_array[] = $Patientenverfuegung_from_old . " -> " . $Patientenverfuegung_from_post;
                				}
                				elseif ($Patientenverfuegung_from_post != '') {
                					$text_array[] = $Patientenverfuegung_from_post;
                				}
                				
                			}
                			
                			
                			
                		}
                		*/
                		
                		
                		
                		/**
                		 * Vorsorgevollmacht Vorsorgevollmacht_vollmacht
                		 */
                		/*
                		 * ISPC-2244 removed next
                		$Vorsorgevollmacht_from_post = "";
                		$label_row = 'Vorsorgevollmacht: ';
                		if ( $statusval['Vorsorgevollmacht_vollmacht'] != 'nicht bekannt'
                				||  $statusval['Vorsorgevollmacht_selectedname'] != 'Auswählen'
                		) {
                			$Vorsorgevollmacht_vollmacht = '';
                			if ( $statusval['Vorsorgevollmacht_vollmacht'] != 'nicht bekannt') {
                				$Vorsorgevollmacht_vollmacht =  $statusval['Vorsorgevollmacht_vollmacht'] . "; ";
                			}
                			$Vorsorgevollmacht_selectedname = '';
                			if ( $statusval['Vorsorgevollmacht_selectedname'] != 'Auswählen') {
                				$Vorsorgevollmacht_selectedname = $statusval['Vorsorgevollmacht_selectedname'];
                			}
                			
                			$Vorsorgevollmacht_from_post = sprintf($label_row . "%s%s" , $Vorsorgevollmacht_vollmacht, $Vorsorgevollmacht_selectedname);
                		}
                		if ( $is_new_contactform && $Vorsorgevollmacht_from_post != '') {
                			$text_array[] = $Vorsorgevollmacht_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Vorsorgevollmacht_from_old = '';
                			if ( $statusval['Vorsorgevollmacht_vollmacht'] != $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_vollmacht']
                					|| $statusval['Vorsorgevollmacht_selectedname'] != $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_selectedname']
                			) {
                				
	                			$Vorsorgevollmacht_vollmacht = '';
	                			if ( $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_vollmacht'] != 'nicht bekannt') {
	                				$Vorsorgevollmacht_vollmacht =  $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_vollmacht'] . "; ";
	                			}
	                			$Vorsorgevollmacht_selectedname = '';
	                			if ( $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_selectedname'] != 'Auswählen') {
	                				$Vorsorgevollmacht_selectedname = $lmu_pmba_psysoz_oldValues['Vorsorgevollmacht_selectedname'];
	                			}
                				$Vorsorgevollmacht_from_old = sprintf($label_row . "%s%s" , $Vorsorgevollmacht_vollmacht, $Vorsorgevollmacht_selectedname) ; 
                				
                				if ($Vorsorgevollmacht_from_old != $Vorsorgevollmacht_from_post && $Vorsorgevollmacht_from_old != '') {
                					$Vorsorgevollmacht_from_post = trim( substr($Vorsorgevollmacht_from_post, strlen($label_row) ));
                					$text_array[] = $Vorsorgevollmacht_from_old . " -> " . $Vorsorgevollmacht_from_post;
                				}
                				elseif ($Vorsorgevollmacht_from_post != '') {
                					$text_array[] = $Vorsorgevollmacht_from_post;
                				}
                				
                			}
                			
                			
                		}
                		*/
                		
                		
                		
                		
                		/**
                		 * Betreuung betreuung_betreuung betreuung_selectedname
                		 */
                		/*
                		$Betreuung_from_post = "";
                		$label_row = 'Betreuung: ';
                		if ( $statusval['betreuung_betreuung'] != 'nicht erforderlich/bekannt'
                				||  $statusval['betreuung_selectedname'] != 'Auswählen'
                		) {
                			$betreuung_betreuung = '';
                			if ( $statusval['betreuung_betreuung'] != 'nicht erforderlich/bekannt') {
                				$betreuung_betreuung =  $statusval['betreuung_betreuung'] . "; ";
                			}
                			$betreuung_selectedname = '';
                			if ( $statusval['betreuung_selectedname'] != 'Auswählen') {
                				$betreuung_selectedname = $statusval['betreuung_selectedname'];
                			}
                		
                			$Betreuung_from_post = sprintf($label_row . "%s%s" , $betreuung_betreuung, $betreuung_selectedname);
                		}
                		if ( $is_new_contactform && $Betreuung_from_post != '') {
                			$text_array[] = $Betreuung_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Betreuung_from_old = '';
                			if ( $statusval['betreuung_betreuung'] != $lmu_pmba_psysoz_oldValues['betreuung_betreuung']
                					|| $statusval['betreuung_selectedname'] != $lmu_pmba_psysoz_oldValues['betreuung_selectedname']
                			) {
                		
                				$betreuung_betreuung = '';
                				if ( $lmu_pmba_psysoz_oldValues['betreuung_betreuung'] != 'nicht erforderlich/bekannt') {
                					$betreuung_betreuung =  $lmu_pmba_psysoz_oldValues['betreuung_betreuung'] . "; ";
                				}
                				$betreuung_selectedname = '';
                				if ( $lmu_pmba_psysoz_oldValues['betreuung_selectedname'] != 'Auswählen') {
                					$betreuung_selectedname = $lmu_pmba_psysoz_oldValues['betreuung_selectedname'];
                				}
                				$Betreuung_from_old = sprintf($label_row . "%s%s" , $betreuung_betreuung, $betreuung_selectedname) ;
                		
                				if ($Betreuung_from_old != $Betreuung_from_post && $Betreuung_from_old != '') {
                					$Betreuung_from_post = trim( substr($Betreuung_from_post, strlen($label_row) ));
                					$text_array[] = $Betreuung_from_old . " -> " . $Betreuung_from_post;
                				}
                				elseif ($Betreuung_from_post != '') {
                					$text_array[] = $Betreuung_from_post;
                				}
                			}
                			
                		}
                		*/
                	
                		
                		
                		/**
                		 * Pflegegrade Pflegestufe_stufe Pflegestufe_hoeher Pflegestufe_neuantrag
                		 */
                		$Pflegegrade_from_post = "";
                		$label_row = 'Pflegegrade: ';
                		if ( $Pflegestufe_was_changed 
                				&& ($statusval['Pflegestufe_stufe'] != ''
                					||  $statusval['Pflegestufe_hoeher'] == '1'
                					||  $statusval['Pflegestufe_neuantrag'] == '1')
                		) {
                			$Pflegestufe_stufe = '';
                			if ( $statusval['Pflegestufe_stufe'] != '') {
                				$Pflegestufe_stufe =  $statusval['Pflegestufe_stufe'];
                			}
                			$Pflegestufe_hoeher = '';
                			if ( $statusval['Pflegestufe_hoeher'] == '1') {
                				$Pflegestufe_hoeher =  "; Höherstufung beantragt" ;
                			}
                			$Pflegestufe_neuantrag = '';
                			if ( $statusval['Pflegestufe_neuantrag'] == '1') {
                				$Pflegestufe_neuantrag = "; Erstantrag";
                			}
                		
                			$Pflegegrade_from_post = sprintf($label_row . "%s%s%s" , $Pflegestufe_stufe, $Pflegestufe_hoeher, $Pflegestufe_neuantrag);
                		}
                		if ( $is_new_contactform && $Pflegegrade_from_post != '') {
                			$text_array[] = $Pflegegrade_from_post;
	                    } elseif ($Pflegestufe_was_changed && !$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Pflegegrade_from_old = '';
                			if ( $statusval['Pflegestufe_stufe'] != $lmu_pmba_psysoz_oldValues['Pflegestufe_stufe']
                					|| $statusval['Pflegestufe_hoeher'] != $lmu_pmba_psysoz_oldValues['Pflegestufe_hoeher']
                					|| $statusval['Pflegestufe_neuantrag'] != $lmu_pmba_psysoz_oldValues['Pflegestufe_neuantrag']
                			) {
                		
	                			$Pflegestufe_stufe = '';
	                			if ( $lmu_pmba_psysoz_oldValues['Pflegestufe_stufe'] != '') {
	                				$Pflegestufe_stufe =  $lmu_pmba_psysoz_oldValues['Pflegestufe_stufe'];
	                			}
	                			
	                			$Pflegestufe_hoeher = '';
	                			if ( $lmu_pmba_psysoz_oldValues['Pflegestufe_hoeher'] == '1') {
	                				$Pflegestufe_hoeher =  "; Höherstufung beantragt";
	                			}
	                			
	                			$Pflegestufe_neuantrag = '';
	                			if ( $lmu_pmba_psysoz_oldValues['Pflegestufe_neuantrag'] == '1') {
	                				$Pflegestufe_neuantrag = "; Erstantrag";
	                			}
	                			
	                			$Pflegegrade_from_old = sprintf($label_row . "%s%s%s" , $Pflegestufe_stufe, $Pflegestufe_hoeher, $Pflegestufe_neuantrag);

	                			if ($Pflegegrade_from_old != $Pflegegrade_from_post && $Pflegegrade_from_old != '') {
	                				$Pflegegrade_from_post = trim( substr($Pflegegrade_from_post, strlen($label_row) ));
	                				$text_array[] = $Pflegegrade_from_old . " -> " . $Pflegegrade_from_post;
                            	} elseif ($Pflegegrade_from_post != '') {
	                				$text_array[] = $Pflegegrade_from_post;
	                			}
	                			
                			}
                			
                			
                		}
                		 
                	
                		
                		
                		/**
                		 * Hilfsmittelversorgung Hilfsmittelversorgung_freetext
                		 */
                		$Hilfsmittelversorgung_from_post = "";
                		$label_row = 'Hilfsmittelversorgung: ';
                		if ( $statusval['Hilfsmittelversorgung_freetext'] != '') {
                			$Hilfsmittelversorgung_from_post = sprintf($label_row . "%s" , $statusval['Hilfsmittelversorgung_freetext']);
                		}
                		if ( $is_new_contactform && $Hilfsmittelversorgung_from_post != '') {
                			$text_array[] = $Hilfsmittelversorgung_from_post;
                    	} elseif (!$is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Hilfsmittelversorgung_from_old = '';
                			if ( $statusval['Hilfsmittelversorgung_freetext'] != $lmu_pmba_psysoz_oldValues['Hilfsmittelversorgung_freetext']) {
                		
                				$Hilfsmittelversorgung_from_old = sprintf($label_row . "%s" , $lmu_pmba_psysoz_oldValues['Hilfsmittelversorgung_freetext']);
                			

                				if ($Hilfsmittelversorgung_from_old != $Hilfsmittelversorgung_from_post && $Hilfsmittelversorgung_from_old != '') {
                					$Hilfsmittelversorgung_from_post = trim( substr($Hilfsmittelversorgung_from_post, strlen($label_row) ));
                					$text_array[] = $Hilfsmittelversorgung_from_old . " -> " . $Hilfsmittelversorgung_from_post;
                            	} elseif ($Hilfsmittelversorgung_from_post != '') {
                					$text_array[] = $Hilfsmittelversorgung_from_post;
                				}
                			}
                			
                		}
                		
                	
                		
                		

                		/**
                		 * Wunschsterbeort wunschsterbeort_val
                		 */
                		$Wunschsterbeort_from_post = "";
                		$label_row = 'Wunschsterbeort: ';
                		if ( $statusval['wunschsterbeort_val'] != 'Ort auswählen') {
                			$Wunschsterbeort_from_post = sprintf($label_row . "%s" , $statusval['wunschsterbeort_val']);
                		}
                		if ( $is_new_contactform && $Wunschsterbeort_from_post != '') {
                			$text_array[] = $Wunschsterbeort_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Wunschsterbeort_from_old = '';
                			if ( $statusval['wunschsterbeort_val'] != $lmu_pmba_psysoz_oldValues['wunschsterbeort_val']) {
                		
                				$Wunschsterbeort_from_old = sprintf($label_row . "%s" , $lmu_pmba_psysoz_oldValues['wunschsterbeort_val']);

                				if ($Wunschsterbeort_from_old != $Wunschsterbeort_from_post && $Wunschsterbeort_from_old != '') {
                					$Wunschsterbeort_from_post = trim( substr($Wunschsterbeort_from_post, strlen($label_row) ));
                					$text_array[] = $Wunschsterbeort_from_old . " -> " . $Wunschsterbeort_from_post;
                				}
                				elseif ($Wunschsterbeort_from_post != '') {
                					$text_array[] = $Wunschsterbeort_from_post;
                				}
                			}

                		}
                		
                		
                		
                		/**
                		 *  Anmerkungen  memopsysoz_freetext
                		 */
                		$Anmerkungen_from_post = "";
                		$label_row = 'Anmerkungen: ';
                		if ( $statusval['memopsysoz_freetext'] != '') {
                			$Anmerkungen_from_post = sprintf($label_row . "%s" , $statusval['memopsysoz_freetext']);
                		}
                		if ( $is_new_contactform && $Anmerkungen_from_post != '') {
                			$text_array[] = $Anmerkungen_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Anmerkungen_from_old = '';
                			if ( $statusval['memopsysoz_freetext'] != $lmu_pmba_psysoz_oldValues['memopsysoz_freetext']) {
                		
                				$Anmerkungen_from_old = sprintf($label_row . "%s" , $lmu_pmba_psysoz_oldValues['memopsysoz_freetext']);

                				if ($Anmerkungen_from_old != $Anmerkungen_from_post && $Anmerkungen_from_old != '') {
                					$Anmerkungen_from_post = trim( substr($Anmerkungen_from_post, strlen($label_row) ));
                					$text_array[] = $Anmerkungen_from_old . " -> " . $Anmerkungen_from_post;
                				}
                				elseif ($Anmerkungen_from_post != '') {
                					$text_array[] = $Anmerkungen_from_post;
                				}                		
                			}

                		}
                		
                		
                		
                	
                	}
                	

                	/**
                	 * Psychosozialer Status
                	 */
                	if ( ! empty ($text_array)) {
                		$psyanamtext .= "\n<b>Psychosozialer Status</b>";
                		$psyanamtext .= "\n" . implode("\n", $text_array);
                	}
                	
                	
                	
                	/*
                	
                	$rad = '';
                	if(!empty($statusval)) {
//                 		$psyanamtext .= "\n<b>Psychosozialer Status</b>";
                		foreach($statusval as $keys=>$vals) {
                			
                			
                			if ( empty($lmu_pmba_psysoz_oldValues) 
                					&&
                					( 
                						("Patientenverfuegung_verfuegung"== $keys && "nicht vorhanden" == $vals)
	                					|| ("Vorsorgevollmacht_vollmacht"== $keys && "nicht bekannt" == $vals)
	                					|| ("Vorsorgevollmacht_selectedname"== $keys && "Auswählen" == $vals)
	                					|| ("betreuung_betreuung"== $keys && "nicht erforderlich/bekannt" == $vals)
	                					|| ("betreuung_selectedname"== $keys && "Auswählen" == $vals)
	                					|| ("Hilfsmittelversorgung_freetext"== $keys && "" == $vals)
	                					|| ("wunschsterbeort_val"== $keys && "Ort auswählen" == $vals)
	                					|| ("memopsysoz_freetext"== $keys && "" == $vals)
	                					|| ("Pflegestufe_stufe"== $keys && "" == $vals)
                					)
                			) {
                				//this is a new form, ignore empty values , $lmu_pmba_psysoz_oldValues should be empty only on new forms
                				continue;
                			}
                				
                			$radks = explode('_', $keys);
                			
                			if($radks[0] == 'wohnsituation'){
                				
                				
                				if($rad != $radks[0]) {
                					if($radks[1] == 'angehoerige') {
                						$psyanamtext .= "\n".ucfirst($radks[0]).": ".'mit Angehörigen';
                					}
                					else {
                					$psyanamtext .= "\n".ucfirst($radks[0]).": ".$radks[1];
                					}
                					$rad=$radks[0];
                				}
                				else {
                				$psyanamtext .= " ".$radks[1];
                				}
                				
                			}
                			else {
                				if($rad != $radks[0]) { 
                					
                					if($radks[0] == 'memopsysoz') {
                						

                						if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$keys] != $vals) {
                							//new value was inserted
                							$psyanamtext .= "\n".'Anmerkungen'.": " . $lmu_pmba_psysoz_oldValues[$keys] . "->" . $vals;
                						} else if ($lmu_pmba_psysoz_oldValues[$keys] == $vals) {
                							//same value               							 
                						}
                						else {
                							$psyanamtext .= "\n".'Anmerkungen'.": ".$vals;
                						}			
                						
                					}
                					else {
                						              						
                						if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$keys] != $vals) {
                							//new value was inserted
                							$psyanamtext .= "\n".ucfirst($radks[0]).": ".$lmu_pmba_psysoz_oldValues[$keys] . "->" . $vals ;
                						} else if ($lmu_pmba_psysoz_oldValues[$keys] == $vals) {
                							//same value
                						}
                						else {
                							$psyanamtext .= "\n".ucfirst($radks[0]).": ".$vals ;
                						}
                					}
                					$rad=$radks[0];
                				}
                				else {
                					if($vals != '1') {
                					
                						if ($vals != "Auswählen") {
                							$psyanamtext .= '; Kontaktperson: '.$vals;
                						}
                					}
                					else if($vals == '1') {
                						if($radks[1] == 'hoeher') {
                							$psyanamtext .= ' '.'Höherbeantragung';
                						}
                						else {
                							$psyanamtext .= ' '.'Neubeantragung';
                						}
                					}
                				}                				
                			}
                		}
                	}
                	
                	if ( $psyanamtext != "" ) {
                		
                		$psyanamtext = "\n<b>Psychosozialer Status</b>" . $psyanamtext;
                	}
                	
                	
                	$psyanamtext = $psyanamtext_copy . $psyanamtext;
                	*/
                	
                	
                	
                	/**
                	 * Lebenshintergrund
                	 */
                	$text_array = array();
					if(!empty($lebenval)) {
                		
                		/**
                		 * Migrationshintergrund migrationshintergrund migrationshintergrund_freetext
                		 */
                		$Migrationshintergrund_from_post = "";
                		$label_row = 'Migrationshintergrund: ';
                		if ( $lebenval['migrationshintergrund'] == '1' 
                				|| $lebenval['migrationshintergrund'] == '2' 
                				|| $lebenval['migrationshintergrund_freetext'] != ''
                		) {
                			
                			$migrationshintergrund = '';
                			$migrationshintergrund_freetext = '';
                			if ( $lebenval['migrationshintergrund'] == '1') {
                				
                				$migrationshintergrund =  'Ja';
                				
                				if ( $lebenval['migrationshintergrund_freetext'] != '') {
                					
                					$migrationshintergrund_freetext =  '; ' . $lebenval['migrationshintergrund_freetext'];
                				}
                				
                			} elseif ( $lebenval['migrationshintergrund'] == '2') {
                				
                				$migrationshintergrund =  'Nein';
                			}
                			
                			$Migrationshintergrund_from_post = sprintf($label_row . "%s%s" , $migrationshintergrund, $migrationshintergrund_freetext);
                		}
                		if ( $is_new_contactform && $Migrationshintergrund_from_post != '') {
                			$text_array[] = $Migrationshintergrund_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Migrationshintergrund_from_old = '';
                			if ( $lebenval['migrationshintergrund'] != $lmu_pmba_psysoz_oldValues['migrationshintergrund']
                					|| $lebenval['migrationshintergrund_freetext'] != $lmu_pmba_psysoz_oldValues['migrationshintergrund_freetext']
                			) {
                		
                				$migrationshintergrund = '';
                				$migrationshintergrund_freetext = '';
                				if ( $lmu_pmba_psysoz_oldValues['migrationshintergrund'] == '1') {
                				
                					$migrationshintergrund =  'Ja';
                				
                					if ( $lmu_pmba_psysoz_oldValues['migrationshintergrund_freetext'] != '') {
                						 
                						$migrationshintergrund_freetext =  '; ' . $lmu_pmba_psysoz_oldValues['migrationshintergrund_freetext'];
                					}
                				
                				} elseif ( $lmu_pmba_psysoz_oldValues['migrationshintergrund'] == '2') {
                				
                					$migrationshintergrund =  'Nein';
                				}
                				
                				$Migrationshintergrund_from_old = sprintf($label_row . "%s%s" , $migrationshintergrund, $migrationshintergrund_freetext);

                				if ($Migrationshintergrund_from_old != $Migrationshintergrund_from_post && $Migrationshintergrund_from_old != '') {
                					$Migrationshintergrund_from_post = trim( substr($Migrationshintergrund_from_post, strlen($label_row) ));
                					$text_array[] = $Migrationshintergrund_from_old . " -> " . $Migrationshintergrund_from_post;
                				}
                				elseif ($Migrationshintergrund_from_post != '') {
                					$text_array[] = $Migrationshintergrund_from_post;
                				}                			    
                			}

                		}
                		
                		
                		/**
                		 * Dolmetscher nötig dolmetscher dolmetscher_freetext
                		 */
                		$Dolmetscher_from_post = "";
                		$label_row = 'Dolmetscher nötig: ';
                		if ( $lebenval['dolmetscher'] == '1'
                				|| $lebenval['dolmetscher'] == '2'
                				|| $lebenval['dolmetscher_freetext'] != ''
                		) {
                			 
                			$dolmetscher = '';
                			$dolmetscher_freetext = '';
                			if ( $lebenval['dolmetscher'] == '1') {
                		
                				$dolmetscher =  'Ja';
                		
                				if ( $lebenval['dolmetscher_freetext'] != '') {
                					 
                					$dolmetscher_freetext =  '; ' . $lebenval['dolmetscher_freetext'];
                				}
                		
                			} elseif ( $lebenval['dolmetscher'] == '2') {
                		
                				$dolmetscher =  'Nein';
                			}
                			 
                			$Dolmetscher_from_post = sprintf($label_row . "%s%s" , $dolmetscher, $dolmetscher_freetext);
                		}
                		if ( $is_new_contactform && $Dolmetscher_from_post != '') {
                			$text_array[] = $Dolmetscher_from_post;
                		}
                		elseif( ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Dolmetscher_from_old = '';
                			if ( $lebenval['dolmetscher'] != $lmu_pmba_psysoz_oldValues['dolmetscher']
                					|| $lebenval['dolmetscher_freetext'] != $lmu_pmba_psysoz_oldValues['dolmetscher_freetext']
                			) {
                		
                				$dolmetscher = '';
                				$dolmetscher_freetext = '';
                				if ( $lmu_pmba_psysoz_oldValues['dolmetscher'] == '1') {
                		
                					$dolmetscher =  'Ja';
                		
                					if ( $lmu_pmba_psysoz_oldValues['dolmetscher_freetext'] != '') {
                						 
                						$dolmetscher_freetext =  '; ' . $lmu_pmba_psysoz_oldValues['dolmetscher_freetext'];
                					}
                		
                				} elseif ( $lmu_pmba_psysoz_oldValues['dolmetscher'] == '2') {
                		
                					$dolmetscher =  'Nein';
                				}
                		
                				$Dolmetscher_from_old = sprintf($label_row . "%s%s" , $dolmetscher, $dolmetscher_freetext);

                				if ($Dolmetscher_from_old != $Dolmetscher_from_post && $Dolmetscher_from_old != '') {
                					$Dolmetscher_from_post = trim( substr($Dolmetscher_from_post, strlen($label_row) ));
                					$text_array[] = $Dolmetscher_from_old . " -> " . $Dolmetscher_from_post;
                				}
                				elseif ($Dolmetscher_from_post != '') {
                					$text_array[] = $Dolmetscher_from_post;
                				}                				
                			}

                		}
                		
                		
                		
                		
                		
                		
                		/**
                		 * Religionszugehörigkeit religion religion_freetext
                		 */
                		$Religion_from_post = "";
                		$label_row = 'Religionszugehörigkeit: ';
                		if ( $religion_was_changed 
                				&& ( $lebenval['religion'] != '0'
                						|| $lebenval['religion_freetext'] != '')
                		) {
                		
                			$religion = '';
                			if ( $lebenval['religion'] != '0') {
                				$religion =  $religions[ $lebenval['religion'] ];
                			} 
                			
                			$religion_freetext = '';
                			if ( $lebenval['religion_freetext'] != '') {
                			
                				$religion_freetext =  '; ' . $lebenval['religion_freetext'];
                			}
                		
                			$Religion_from_post = sprintf($label_row . "%s%s" , $religion, $religion_freetext);
                		}
                		if ( $is_new_contactform && $Religion_from_post != '') {
                			$text_array[] = $Religion_from_post;
                		}
                		elseif( $religion_was_changed && ! $is_new_contactform && !empty($lmu_pmba_psysoz_oldValues)) {
                			//look if any  modified
                			$Religion_from_old = '';
                			if ( $lebenval['religion'] != $lmu_pmba_psysoz_oldValues['religion']
                					|| $lebenval['religion_freetext'] != $lmu_pmba_psysoz_oldValues['religion_freetext']
                			) {
                		
                				$religion = '';
                				if ( $lmu_pmba_psysoz_oldValues['religion'] != '0') {
                					$religion =  $religions[ $lmu_pmba_psysoz_oldValues['religion'] ];
                				}
                				
                				$religion_freetext = '';
                				if ( $lmu_pmba_psysoz_oldValues['religion_freetext'] != '') {
                					$religion_freetext =  '; ' . $lmu_pmba_psysoz_oldValues['religion_freetext'];
                				}
                		
                				$Religion_from_old = sprintf($label_row . "%s%s" , $religion, $religion_freetext);

                				if ($Religion_from_old != $Religion_from_post && $Religion_from_old != '') {
                					$Religion_from_post = trim( substr($Religion_from_post, strlen($label_row) ));
                					$text_array[] = $Religion_from_old . " -> " . $Religion_from_post;
                				}
                				elseif ($Religion_from_post != '') {
                					$text_array[] = $Religion_from_post;
                				}                				
                			}

                		}
                		
                	}
                	
                	/**
                	 * Lebenshintergrund
                	 */
                	if ( ! empty ($text_array)) {
                		$psyanamtext .= "\n<b>Lebenshintergrund</b>";
                		$psyanamtext .= "\n" . implode("\n", $text_array);
                	}
                	
                	
                	/*
                   	$coursetextarr = array();
                	$rad = '';
                	
                	if(!empty($lebenval)) {
                	
                		$psyanamtext .= "\n<b>Lebenshintergrund</b>";
                		$psyanamlength = strlen($psyanamtext);
                		foreach($lebenval as $keyl=>$vall) {
                			
                			if($keyl != 'religion' && $vall == '1'){
                				
                				if (empty($lmu_pmba_psysoz_oldValues) || $lmu_pmba_psysoz_oldValues[$keyl] != $vall) {
                					$coursetextarr[] = "\n".ucfirst($keyl).": Ja";
                				}
                				$rad=$keyl;
                			}
                			else if($keyl == $rad.'_freetext') {
                				if (empty($lmu_pmba_psysoz_oldValues) || $lmu_pmba_psysoz_oldValues[$keyl] != $vall) {
	                				$coursetextarr[] = $vall;
	                				$psyanamtext .= implode(' ', $coursetextarr);
	                				$coursetextarr = array();
                				}
                			}
                			else if($keyl != 'religion' && $vall == '2') {
                				if (empty($lmu_pmba_psysoz_oldValues) || $lmu_pmba_psysoz_oldValues[$keyl] != $vall) {
                					$psyanamtext .= "\n".ucfirst($keyl).": Nein";
                				}
                			
                			}
                			else if ($keyl == 'religion') {
                				
                				if ( ! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$keyl] != $vall) {
                					//new value was inserted
                					$coursetextarr[] = "\n".'Religionszugehörigkeit'.": " . $religions[$lmu_pmba_psysoz_oldValues[$keyl]]. "->" .$religions[$vall];
                					 
                				} else if (! empty($lmu_pmba_psysoz_oldValues) && $lmu_pmba_psysoz_oldValues[$keyl] == $vall) {
                					//same value
                				}
                				else {
                					$coursetextarr[] = "\n".'Religionszugehörigkeit'.": ".$religions[$vall];
                				}
                				
                				
                				if($vall != "0") {
//                 					$coursetextarr[] = "\n".'Religionszugehörigkeit'.": ".$religions[$vall];	
                					$rad=$keyl;
                				}
                			}
                			else if($keyl == $rad.'_freetext') {
                				if (empty($lmu_pmba_psysoz_oldValues) || $lmu_pmba_psysoz_oldValues[$keyl] != $vall) {
                					$coursetextarr[] = $vall;
                					$psyanamtext .= implode(' ', $coursetextarr);
                					$coursetextarr = array();
                				}
                			}                			
                		}
                		
                		$psyanamfinallength = strlen($psyanamtext);
                		if($psyanamlength == $psyanamfinallength) {
                			$psyanamtext = str_replace("<b>Lebenshintergrund</b>", "", $psyanamtext);
                		}
                	}
                	*/
                	
				break;


/*
            case "lmu_pflegephone":
                if($post[$blockname]['pfl_inhalt']) {
                    $text=Pms_CommonData::splitPseudoMs($post[$blockname]['pfl_inhalt']);
                    $coursetext = "Inhalt: " . $text . "<br>";
                }
                break;
            case "lmu_contactform_sozaphone":
            case "lmu_pflegetalk":
                if($post[$blockname]['text']) {
                    $coursetext = "Inhalt: " . $post[$blockname]['text'] . "<br>";
                }
                break;
            case "lmu_visite_summary":
                if($post[$blockname]['freetext']) {
                    $coursetext = "Inhalt: " . $post[$blockname]['freetext'] . "<br>";
                }
                break;
            case "schicht":
                if($post[$blockname]['schicht']) {
                    $schichtname="";
                    switch($post[$blockname]['schicht']){
                        case "F":
                            $schichtname="Frühschicht";
                            break;
                        case "S":
                            $schichtname="Spätschicht";
                            break;
                        case "N":
                            $schichtname="Nachtschicht";
                            break;
                    }
                    $coursetext = "<b>".$schichtname . "</b><br>";
                }
                break; */
			}
			

		/*
		 * @author claudiu
		 * this @var $coursetext is not defineed.. removing the next if 
		 */
			/*
		if ($coursetext){
				$cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($post['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
				$cust->recorddata=$cust->recorddata . addslashes($coursetext);
				$cust->save();
		}
		*/

			
		//ISPC-2244
		/* ------------------SAVE PatientACP from BLOCK Psychosoziale Anamnese -------------------*/
		    //save patientACP
	    if ( ! empty($post['PatientAcp'])) {
	        $af_pacp = new Application_Form_PatientACP();
	        $savedAcpEntity = $af_pacp->save_form_acp_all_tabs($post['ipid'], $post['PatientAcp']);
	        	
	        $acp_box_lang = $this->translate('acp_box_lang');
	        $getDefaultRadios = PatientACP::getDefaultRadios();
	        
	        $acp_pc_text = '';
	        $acp_was_changed = false;
	        
	        $ContactPersonMaster = [];
	        if (! empty($this->_patientMasterData) && ! empty($this->_patientMasterData['ContactPersonMaster'])) {
	            $ContactPersonMaster = array_column($this->_patientMasterData['ContactPersonMaster'], 'nice_name', 'id');
	        }
	        
	        foreach ($savedAcpEntity as $tabname => $row) {
	            	
	            if ($row instanceof PatientAcp) {
	                $newModifiedValues = $row->getLastModified();
	                
	                $acp_pc_text .= "\n" . $acp_box_lang[$row->division_tab]
                    . " : "
                    . (!empty($row->active) && isset($getDefaultRadios[$row->active]) ? $getDefaultRadios[$row->active] : $getDefaultRadios[""])
	                . (isset($ContactPersonMaster[$row->contactperson_master_id]) ? ", ". $ContactPersonMaster[$row->contactperson_master_id] : "")
	                ;
	                
	                
	                if ( ! empty($newModifiedValues) 
	                    && (isset($newModifiedValues['active']) || isset ($newModifiedValues['contactperson_master_id']))) 
	                {	                    
	                    $acp_was_changed = true;
	                }
	                
	                
	            }
	        }
	        
	        if ($acp_was_changed) {
	           $psyanamtext .= $acp_pc_text;
	        }
	    }
		
	    
		if($psyanamtext != "" && trim($psyanamtext) != "<b>Psychosoziale Anamnese</b>") {

			$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
			
			$cust_psy = new PatientCourse();
			$cust_psy->ipid = $post['ipid'];
			$cust_psy->course_date = date("Y-m-d H:i:s", time());
			$cust_psy->course_type = Pms_CommonData::aesEncrypt("K");
			$cust_psy->course_title = Pms_CommonData::aesEncrypt(addslashes($psyanamtext));
			$cust_psy->tabname = Pms_CommonData::aesEncrypt("lmu_pmba_psysoz");
			//$cust_psy->recordid = $cust->recordid; nu trebuie 
			$cust_psy->user_id = $userid;
			$cust_psy->done_date = $done_date;
			$cust_psy->done_name = Pms_CommonData::aesEncrypt("contact_form");
			$cust_psy->done_id =  $post['contact_form_id'];
			$cust_psy->save();
		}		
		
		if($blockname == "bericht_fbe"){
		    
		    //TODO-3843 Ancuta 11.02.2021
		    // Check if bock is allowed to add to verlauf  - recoreddata - to F
		    $block = $blockname;
		    if( ! empty($post['__formular']['blocks2recorddata'])  && array_key_exists($block,$post['__formular']['blocks2recorddata']) && $post['__formular']['blocks2recorddata'][$block]['allow'] == '1'){
		        
		        $record_color = (!empty($post['__formular']['blocks2recorddata'][$block]['color'])) ? $post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
		        
		        $coursetext ="";
		        $course_arr = array();
		        foreach($post['bericht_fbe'] as $label =>$value){
		            if(strlen($value) > 0 ){
		                $course_arr[]= "<b>".$label.":</b>".$value;
		            }
		        }
		        $coursetext = implode("<br/>",$course_arr);
		        if(strlen($coursetext) > 0 ){
					//TODO-4035 Nico 12.04.2021
					ContactForms::add_recorddata($coursetext, $blockname, $post['__formular']);

		        }
		    }
		    // --
		    
		}
		
	}

	//	Maria:: Migration CISPC to ISPC 22.07.2020
	
	public function create_form_treatmentplanclinic($options = array(), $elementsBelongTo = null, $decid)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $ipid = Pms_CommonData::getIpid($decid);
	    $encid = Pms_Uuid::encrypt($decid);
	    
	    $listsmodel = new SelectboxlistPlangoal();
	    $list_goalsandplans = $listsmodel->getListOrDefault('goalsandplans');
	    $professions_conf = Client::getClientconfig($clientid, 'lmutm_profsmap');
	    
	    // update an existing contactform => loaded old values by ContactFormId
	    if (isset($options['v']))
	        $stored_data = json_decode($options['v'], true);
	        if (isset($options['treatment_plan_clinic'])) // use the post ones, maybe this is just a print
	            $stored_data = $this->get_treatmentplanclinic_data($options, $professions_conf);
	            
	            $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	            
	            //create the pdf-Layout and return
	            if ($options['formular_type'] == 'pdf'){
	                
	                return $this->create_pdf_treatmentplanclinic($stored_data, $professions_conf);
	                
	            }
	            
	            $this->mapValidateFunction($__fnName, "create_form_isValid");
	            $this->mapSaveFunction($__fnName, "save_form_treatmentplanclinic");
	            $fn_options = $this->getFnOptions($__fnName);
	            //$this->addDecorator('SimpleContactformBlock', array('class' => $fn_options['class']));
	            //$this->setLegend('block_treatment_plan_clinic');
	            $this->add_javascript_treatmentplan();
	            $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	            $subform->setLegend('block_treatment_plan_clinic');
	            $subform->clearDecorators();
	            $subform->addDecorator('FormElements');
	            //print_r($fn_options);
	            $subform->addDecorator('Fieldset', array('class' => 'treatmentplanclinic ' . $fn_options['class'], 'legend' => '', 'style' => 'border: 0px; padding: 0px; font-size: 12px;'));
	            $subform->addDecorator('SimpleContactformBlock', $fn_options);
	            $this->addSubForm($subform, $elementsBelongTo);
	            
	            
	            $subform->addElement('note', 'header_blank', array(
	                'value' => '&nbsp;',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 28%; display: inline-block; vertical-align: top;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'openOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	                    )
	                ),
	            ));
	            
	            $subform->addElement('note', 'label_goal', array(
	                'value' => $this->translate('label_goal'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'id' => 'label_goal',
	                        'style' => 'width: 35%;  display: inline-block;')),
	                    
	                ),
	            ));
	            $subform->addElement('note', 'label_plan', array(
	                'value' => $this->translate('label_measure'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'id' => 'label_plan',
	                        'style' => 'width: 35%;  display: inline-block;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'closeOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::APPEND),
	                    )
	                ),
	            ));
	            
	            $subform->addElement('hidden', 'encid', array(
	                'value' => $encid,
	                'id' => 'encid',
	                'readonly' => true,
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
	                ),
	            ));
	            
	            $subform->addElement('hidden', 'treatment_plan_clinic', array(
	                'value' => 'treatment_plan_clinic',
	                'readonly' => true,
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
	                ),
	            ));
	            
	            $subform->addElement('hidden', 'message', array(
	                'value' => $this->translate('treatment_plan_clinic_generate_plan_message'),
	                'readonly' => true,
	                'id' => 'treatment_plan_success',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
	                ),
	                
	            ));
	            
	            $subform->addElement('hidden', 'list_goalsandplans', array(
	                'value' => json_encode($list_goalsandplans),
	                'readonly' => true,
	                'id' => 'list_goalsandplans',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
	                ),
	            ));
	            
	            foreach ($professions_conf as $confkey => $confvalue) {
	                
	                $subform->addElement('note', 'note' . $confkey, array(
	                    'value' => $confvalue,
	                    'decorators' => array(
	                        'ViewHelper',
	                        array(array('ltag' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'style' => 'width: 18%;  display: inline-block; vertical-align: top;')),
	                        array(array('ediv' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'class' => 'theme_row',
	                            'style' => 'width: 100%;',
	                            'openOnly' => true,
	                            'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	                        )
	                    ),
	                ));
	                
	                $subform->addElement('hidden', 'theme_key_' . $confkey, array(
	                    'value' => $confkey,
	                    'class' => 'theme_key',
	                    'readonly' => true,
	                    'decorators' => array(
	                        'ViewHelper',
	                    ),
	                ));
	                
	                $subform->addElement('button', 'btn_lookup_goal_' . $confkey, array(
	                    'label' => '',
	                    'class' => 'btnLookup',
	                    'value' => '',
	                    'style' => 'background:url(css/page-css/images/lookup-blue.svg) 50% 50% no-repeat;background-size:18px 18px;width:18px;height:18px;cursor:pointer;outline:none;border:0 !important;',
	                    'decorators' => array(
	                        'ViewHelper',
	                        array(array('ltag' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'style' => 'width: 10%;  display: inline-block; vertical-align: top;')),
	                        
	                    ),
	                ));
	                
	                $subform->addElement('textarea', 'goal_' . $confkey, array(
	                    'isArray' => true,
	                    'value' => $stored_data[$confkey]['goal'][0],
	                    'class' => 'txtGoal',
	                    'required' => false,
	                    'filters' => array('StringTrim'),
	                    'id' => 'goal_' . $confkey,
	                    'style' => 'width: 95%;',
	                    'rows' => '2',
	                    'decorators' => array(
	                        'ViewHelper',
	                        array(array('ltag' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'style' => 'width: 35%;  display: inline-block;')),
	                    ),
	                ));
	                
	                $subform->addElement('textarea', 'plan_' . $confkey, array(
	                    'isArray' => true,
	                    'value' => $stored_data[$confkey]['plan'][0],
	                    'class' => 'txtPlan',
	                    'required' => false,
	                    'filters' => array('StringTrim'),
	                    'style' => 'width: 95%;',
	                    'rows' => '2',
	                    'id' => 'plan_' . $confkey,
	                    'decorators' => array(
	                        'ViewHelper',
	                        array(array('ltag' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'style' => 'width: 35%;  display: inline-block;')),
	                        array(array('ediv' => 'HtmlTag'), array(
	                            'tag' => 'div',
	                            'style' => 'width: 100%;',
	                            'closeOnly' => true,
	                            'placement' => Zend_Form_Decorator_Abstract::APPEND),
	                        )
	                    ),
	                ));
	            }
	            
	            $subform->addElement('note', 'lbl_abst', array(
	                'value' => $this->translate('treatment_plan_clinic_agree_with'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 28%;  display: inline-block; vertical-align: top;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'openOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	                    )
	                ),
	            ));
	            $agree_with = (isset($stored_data['agree_with']) ? $stored_data['agree_with'] : '');
	            $subform->addElement('text', 'treatmentplan_clinic_agree_with', array(
	                'value' => $agree_with,
	                'required' => false,
	                'filters' => array('StringTrim'),
	                'style' => 'width: 95%;',
	                'id' => 'agree_with',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 35%;  display: inline-block;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'closeOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::APPEND),
	                    )
	                ),
	            ));
	            
	            $subform->addElement('note', 'lbl_verspl', array(
	                'value' => $this->translate('treatment_plan_clinic_talk_supply_planning_lbl'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 28%;  display: inline-block; vertical-align: top;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;margin-top:10px;',
	                        'openOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	                    )
	                ),
	            ));
	            
	            $checked = (isset($stored_data['talk_supply_planning']) ? $stored_data['talk_supply_planning'] : '0');
	            $subform->addElement('checkbox', 'treatmentplan_clinic_talk_supply_planning', array(
	                'checkedValue' => '1',
	                'uncheckedValue' => '0',
	                'label' => $this->translate('treatment_plan_clinic_talk_supply_planning'),
	                'required' => false,
	                'value' => $checked,
	                'filters' => array('StringTrim'),
	                'style' => 'width: 3%;',
	                'id' => 'talk_supply_planning',
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Label', array('placement' => 'IMPLICIT_APPEND')),
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 60%;  display: inline-block;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'closeOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::APPEND),
	                    )
	                ),
	            ));
	            
	            $subform->addElement('note', 'lbl_behanplspez', array(
	                'value' => '',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 28%;  display: inline-block; vertical-align: top;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;margin-top:10px;',
	                        'openOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
	                    )
	                ),
	            ));
	            
	            $date = (isset($stored_data['date']) ? $stored_data['date'] : date('d.m.Y'));
	            
	            $subform->addElement('text', 'treatmentplan_clinic_date', array(
	                'value' => $date,
	                'filters' => array('StringTrim'),
	                'class' => 'datepicker',
	                'options' => array('ignore' => TRUE),
	                'label' => '',
	                'id' => 'treatment_plan_date',
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Label', array('placement' => 'IMPLICIT_APPEND')),
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 30%;  display: inline-block;')),
	                ),
	            ));
	            
	            $subform->addElement('button', 'btn_new_plan', array(
	                'label' => $this->translate('treatment_plan_clinic_generate_plan'),
	                'id' => 'btn_treatmentplan_clinic',
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('ltag' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 30%;  display: inline-block;')),
	                    array(array('ediv' => 'HtmlTag'), array(
	                        'tag' => 'div',
	                        'style' => 'width: 100%;',
	                        'closeOnly' => true,
	                        'placement' => Zend_Form_Decorator_Abstract::APPEND),
	                    )
	                ),
	            ));
	            
	            return $this->filter_by_block_name($this, __FUNCTION__);
	}
	
    public function create_form_jobbackgroundclinic($options = array(), $elementsBelongTo = null, $decid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $ipid = Pms_CommonData::getIpid($decid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        if (isset($options['job_background_clinic'])) // use the post ones, maybe this is just a print
            $stored_data = $this->get_jobbackgroundclinic_data($options);

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_jobbackgroundclinic($stored_data);
        }

        $config_job = ClientConfig::getConfig($clientid, 'configjob');

        // if there is no configuration, use the default
        if (!$config_job)
            $config_job = ClientConfig::getDefaultConfig('configjob');

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_jobbackgroundlinic");

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('block_job_background_clinic');
        //$this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'job_background_clinic', array(
            'value' => 'job_background_clinic',
            'elementBelongsTo' => self::BLOCK_JOBBACKGROUND_CLINIC,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        foreach ($config_job['items'] as $confkey => $confvalue) {

            //[item][0][key]
            $subform->addElement('hidden', 'key_' . $confkey, array(
                'value' => $confvalue,
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_JOBBACKGROUND_CLINIC,
                'array_index' => $confkey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));

            $subform->addElement('note', 'label_' . $confkey, array(
                'value' => $confvalue,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '25%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('textarea', 'val_' . $confkey, array(
                'value' => $stored_data[$confvalue],
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_JOBBACKGROUND_CLINIC,
                'array_index' => $confkey,
                'rows' => 24,
                'cols' => 80,
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleTextfield',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '65%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));
        }

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_screeningdepressionclinic($options = array(), $elementsBelongTo = null, $decid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $ipid = Pms_CommonData::getIpid($decid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        if (isset($options['screening_for_depression_clinic'])) // use the post ones, maybe this is just a print
            $stored_data = $this->get_screeningdepressionclinic_data($options);

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_screeningdepressionclinic($stored_data);
        }

        $config_depression = ClientConfig::getConfig($clientid, 'configdepression');

        // if there is no configuration, use the default
        if (!$config_depression)
            $config_depression = ClientConfig::getDefaultConfig($clientid, 'configdepression');

        $selectoptions = array('NOSELECT' => $this->translate('screen_depression_clinic_no_select'),
            'YES' => $this->translate('screen_depression_clinic_yes'),
            'NO' => $this->translate('screen_depression_clinic_no'));


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_screeningfordepressionlinic");

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('screen_depression_clinic');
        //$this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'screening_for_depression_clinic', array(
            'value' => 'screening_for_depression_clinic',
            'elementBelongsTo' => self::BLOCK_SCREENINGDEPRESSION_CLINIC,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        foreach ($config_depression as $confkey => $confvalue) {

            $subform->addElement('hidden', 'key_' . $confkey, array(
                'value' => $confvalue,
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_SCREENINGDEPRESSION_CLINIC,
                'array_index' => $confkey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));

            $subform->addElement('note', 'label_' . $confkey, array(
                'value' => $confvalue,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '75%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('select', 'value_' . $confkey, array(
                'value' => $stored_data[$confvalue],
                'multiOptions' => $selectoptions, //key=>value'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_SCREENINGDEPRESSION_CLINIC, 'belongsTo' => '[item]',
                'array_index' => $confkey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleSelect',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

        }

        $subform->addElement('text', 'lbl_freetext' . $confkey, array(
            'value' => $this->translate('screen_depression_clinic_freetxt'),
            'readonly' => true,
            'style' => 'width: 14%;',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan' => 2,
                    'openOnly' => true,

                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,
                )),
            ),
        ));

        $subform->addElement('text', 'FREETEXT', array(
            'value' => $stored_data['FREETEXT'],
            'filters' => array('StringTrim'),
            'class' => 'freetext',
            'elementBelongsTo' => self::BLOCK_SCREENINGDEPRESSION_CLINIC,
            'array_index' => 'noindex',
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'closeOnly' => true,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_dischargeplanningclinic($options = array(), $elementsBelongTo = null, $decid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $ipid = Pms_CommonData::getIpid($decid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        if (isset($options['discharge_planning_clinic'])) // use the post ones, maybe this is just a print
            $stored_data = $options;

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_dischargeplanning($stored_data);
        }

        $listsmodel = new Selectboxlist();
        $supplieslist = $listsmodel->getListOrDefault('supplieslist'); //read the list of supplies
        $suppliesoption = array('NOSELECT' => $this->translate('discharge_planning_clinic_select_supply')) + array_combine(array_values($supplieslist), array_values($supplieslist));

        $placesofdeathlist = $listsmodel->getListOrDefault('placesofdeathlist'); //read the list of places of death
        $placesofdeathoption = array('NOSELECT' => $this->translate('discharge_planning_clinic_select_place')) + array_combine(array_values($placesofdeathlist), array_values($placesofdeathlist));


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_dischargeplanning_clinic");
        $this->add_javascript_dischargeplanningclinic();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setAttrib("class", "dischargeplanning_clinic");
        $subform->setLegend('discharge_planning_clinic');
        $this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'discharge_planning_clinic', array(
            'value' => 'discharge_planning_clinic',
            // 'elementBelongsTo' => self::BLOCK_DISCHARGEPLANNING_CLINIC,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                //'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));


        $subform->addElement('note', 'label_dis_date', array(
            'value' => $this->translate('discharge_planning_clinic_date'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                    'width' => '45%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,
                )),
            ),
        ));

        $subform->addElement('text', 'fbkvdisdate', array(
            'value' => $stored_data['fbkvdisdate'],
            'filters' => array('StringTrim'),
            'class' => 'fbkvdisdate',
            'id'=> 'FormBlockDischargePlanningClinic-fbkvdisdate',
            'autocomplete' => 'off',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'date',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,

                )),
            )));

        $subform->addElement('note', 'label_dis_place', array(
            'value' => $this->translate('discharge_planning_clinic_placesofdeath'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                    'width' => '45%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,
                )),
            ),
        ));

        $subform->addElement('select', 'fbkvdisplace', array(
            'value' => $stored_data['fbkvdisplace'],
            'multiOptions' => $placesofdeathoption, //key=>value
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));

        $subform->addElement('note', 'label_dis_suppl', array(
            'value' => $this->translate('discharge_planning_clinic_further_supply'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                    'width' => '45%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,
                )),
            ),
        ));

        $subform->addElement('select', 'fbkvdissuppl', array(
            'value' => $stored_data['fbkvdissuppl'],
            'multiOptions' => $suppliesoption, //key=>value
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    /**
     *
     * Create a form block for the care process clinic (IM-4).
     * @param array $options
     * @param null $elementsBelongTo
     * @param null $ipid
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function create_form_careprocessclinic($options = array(), $elementsBelongTo = null, $ipid = null)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;


        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type']))
            $stored_data = $this->get_careprocessclinic_data($options);
        // prefill a new contactform with previous values => loaded values by the last contactform of the user
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_CAREPROCESS_CLINIC);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }

        // read the client-config
        $sections = ClientConfig::getConfig($clientid, 'careprocesslist');

        if (!$sections) {
            //read the deault-Values, if there are no client-config
            $sections = Client::get_clinic_careprocess_config();
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf')
            return $this->create_pdf_careprocessclinic($stored_data, $sections);


        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "careprocessclinic");
        $this->add_javascript_careprocessclinic();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_care_process_clinic');

        //$subform->setAttrib("class", "label_same_size_auto");

        $this->__setElementsBelongTo($subform, $elementsBelongTo);

        //create id-fields for checked and input-values
        $idnames_section = null;
        $idnames_subsection = null;
        $idnames_subsection_id = null;
        $idnames_item = null;
        $item_id = null;

        //create a range field for activities
        $activity_range = array_merge(["", ""], range(5, 75, 5));
        $activity_range = array_combine(array_values($activity_range), array_values($activity_range));

        foreach ($sections as $key => $row) {

            //create a subform for the new section
            //TODO: add a customizable list here

            $subform_section = $this->create_subform('contact_careprocessclinic');
            $subform->addSubForm($subform_section, 'section_' . $key);


            $idnames_section = "cpc_" . substr(Pms_CommonData::str_safeascii($key, '', ''), 0, 5);

            //add section header
            $subform_section->addElement('note', $key, array(
                'value' => $key,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'h2',
                        'class' => 'groupHeader')),
                    array('HtmlTag', array('tag' => 'div', 'class' => 'contact_careprocessclinic')),
                ),
            ));

            foreach ($row as $row_key => $value) {
                // contact_careprocessclinic
                $subform_outer = $this->create_subform('contact_careprocessclinic_outer_section');
                $subform_outer->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'contact_careprocessclinic'));

                //create a subform for 'checked_items'
                $subform_checked_items = $this->create_subform('contact_careprocessclinic_inner_section');

                $this->__setElementsBelongTo($subform_checked_items, 'checked_items');
                $subform_outer->addSubForm($subform_checked_items, 'checked_items_' . $key . '_' . $row_key);

                //create a subform for 'input_values'
                $subform_input_values = $this->create_subform('contact_careprocessclinic_inner_section');
                $this->__setElementsBelongTo($subform_input_values, 'input_values');
                $subform_outer->addSubForm($subform_input_values, 'input_values_' . $key . '_' . $row_key);

                $subform_section->addSubForm($subform_outer, 'outer_section_' . $key . '_' . $row_key);

                //the theme of the new block - that has to b one item
                $themeIsChecked = false;
                foreach ($value['col_thema'] as $valueTheme) {
                    //"Koerp"
                    $idnames_subsection = substr(Pms_CommonData::str_safeascii($valueTheme, '', ''), 0, 10);
                    //"cpc_Koerp_Patient_be"
                    $idnames_subsection_id = $idnames_section . "_" . $idnames_subsection;
                    //"Patient_be"
                    $idnames_item = substr(Pms_CommonData::str_safeascii($valueTheme, '', ''), 0, 10);
                    //"theme_cpc_Koerp_Patient_be_Patient_be"
                    $item_id = "them_" . $idnames_subsection_id . "_" . $idnames_item;

                    array_key_exists($item_id, $stored_data['checked_items']) ? $themeIsChecked = $valueTheme : $themeIsChecked = false;

                    $css_class = 'section_problem_header';

                    $subform_checked_items->addElement('checkbox', $item_id, array(
                        'checkedValue' => $valueTheme,
                        'uncheckedValue' => '0',
                        'label' => $valueTheme,
                        'required' => false,
                        'value' => $themeIsChecked,
                        'disableHidden' => true,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => $css_class)),
                        ),
                        'class' => 'check_problem',
                    ));
                }
                //the list of the problems
                $display_style = $themeIsChecked ? 'display: block;' : 'display: none';

                foreach ($value['col_probleme'] as $valueProblem) {

                    $idnames_item = Pms_CommonData::str_safeascii($valueProblem, '', '');
                    $item_id = "prob_" . $idnames_subsection_id . "_" . $idnames_item;

                    $i = strpos($valueProblem, "###");
                    if ($i) {
                        $hasTextfield = true;
                        $css_class = 'section_problem_item one_four is_to_hide';
                        $label = substr($valueProblem, 0, $i);
                        $item_id = $item_id . '_free';
                    } else {
                        $hasTextfield = false;
                        $css_class = 'section_problem_item one_two is_to_hide';
                        $label = $valueProblem;
                    }

                    array_key_exists($item_id, $stored_data['checked_items']) ? $checked = $valueProblem : $checked = false;
                    $subform_checked_items->addElement('checkbox', $item_id, array(
                        'checkedValue' => $valueProblem,
                        'uncheckedValue' => '0',
                        'label' => $label,
                        'required' => false,
                        'value' => $checked,
                        'disableHidden' => true,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                        ),
                    ));

                    if ($hasTextfield) {
                        $item_id_textfield = $item_id . '_txt';
                        array_key_exists($item_id_textfield, $stored_data['checked_items']) ? $input_value_textfield = $stored_data['checked_items'][$item_id_textfield] : $input_value_textfield = '';

                        $subform_checked_items->addElement('text', $item_id_textfield, array(
                            'value' => $input_value_textfield,
                            'label' => '',
                            'required' => false,
                            'filters' => array('StringTrim'),
                            'decorators' => array(
                                'ViewHelper',
                                array('Label', array('placement' => 'IMPLICIT_APPEND')),
                                array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                            ),
                        ));
                    }
                }

                if (count($value['col_probleme']) % 2 != 0) {
                    $item_id = "prob_" . $idnames_subsection_id . "_" . $idnames_item . '_blank';
                    $subform_checked_items->addElement('note', $item_id, array(
                        'value' => '',
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => 'section_problem_item one_two is_to_hide', 'style' => $display_style)),
                        ),
                    ));
                }

                $display_style = $themeIsChecked ? 'display: block;' : 'display: none';

                foreach ($value['col_ressourcen'] as $keyResource => $valuResource) {
                    $idnames_item = Pms_CommonData::str_safeascii($valuResource, '', '');
                    $item_id = "reso_" . $idnames_subsection_id . "_" . $idnames_item;

                    $i = strpos($valuResource, "###");
                    if ($i) {
                        $hasTextfield = true;
                        $css_class = 'section_resource_item one_four is_to_hide';
                        $label = substr($valuResource, 0, $i);
                        $item_id = $item_id . '_free';
                    } else {
                        $hasTextfield = false;
                        $css_class = 'section_resource_item one_two is_to_hide';
                        $label = $valuResource;
                    }

                    array_key_exists($item_id, $stored_data['checked_items']) ? $checked = $valuResource : $checked = false;

                    $subform_checked_items->addElement('checkbox', $item_id, array(
                        'checkedValue' => $valuResource,
                        'uncheckedValue' => '0',
                        'label' => $label,
                        'required' => false,
                        'value' => $checked,
                        'disableHidden' => true,
                        'filters' => array('StringTrim',),
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                        ),

                    ));

                    if ($hasTextfield) {
                        $item_id_textfield = $item_id . '_txt';
                        array_key_exists($item_id_textfield, $stored_data['checked_items']) ? $input_value_textfield = $stored_data['checked_items'][$item_id_textfield] : $input_value_textfield = '';

                        $subform_checked_items->addElement('text', $item_id_textfield, array(
                            'value' => $input_value_textfield,
                            'label' => '',
                            'required' => false,
                            'filters' => array('StringTrim'),
                            'decorators' => array(
                                'ViewHelper',
                                array('Label', array('placement' => 'IMPLICIT_APPEND')),
                                array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                            ),
                        ));
                    }

                }

                if (count($value['col_ressourcen']) % 2 != 0) {
                    $item_id = "reso_" . $idnames_subsection_id . "_" . $idnames_item . '_blank';
                    $subform_checked_items->addElement('note', $item_id, array(
                        'value' => '',
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => 'section_resource_item one_two is_to_hide', 'style' => $display_style)),
                        ),
                    ));
                }

                foreach ($value['col_ziele'] as $keyZiel => $valueZiel) {

                    $idnames_item = Pms_CommonData::str_safeascii($valueZiel, '', '');
                    $item_id = "ziel_" . $idnames_subsection_id . "_" . $idnames_item;

                    $i = strpos($valueZiel, "###");
                    if ($i) {
                        $hasTextfield = true;
                        $css_class = 'section_target_item one_four is_to_hide';
                        $label = substr($valueZiel, 0, $i);
                        $item_id = $item_id . '_free';
                    } else {
                        $hasTextfield = false;
                        $css_class = 'section_target_item one_two is_to_hide';
                        $label = $valueZiel;
                    }


                    array_key_exists($item_id, $stored_data['checked_items']) ? $checked = $valueZiel : $checked = false;
                    $subform_checked_items->addElement('checkbox', $item_id, array(
                        'checkedValue' => $valueZiel,
                        'uncheckedValue' => '0',
                        'label' => $label,
                        'required' => false,
                        'value' => $checked,
                        'disableHidden' => true,
                        'filters' => array('StringTrim',),
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                        ),

                    ));


                    if ($hasTextfield) {
                        $item_id_textfield = $item_id . '_txt';
                        array_key_exists($item_id_textfield, $stored_data['checked_items']) ? $input_value_textfield = $stored_data['checked_items'][$item_id_textfield] : $input_value_textfield = '';

                        $subform_checked_items->addElement('text', $item_id_textfield, array(
                            'value' => $input_value_textfield,
                            'label' => '',
                            'required' => false,
                            'filters' => array('StringTrim'),
                            'decorators' => array(
                                'ViewHelper',
                                array('Label', array('placement' => 'IMPLICIT_APPEND')),
                                array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                            ),
                        ));
                    }
                }

                if (count($value['col_ziele']) % 2 != 0) {
                    $item_id = "ziel_" . $idnames_subsection_id . "_" . $idnames_item . '_blank';
                    $subform_checked_items->addElement('note', $item_id, array(
                        'value' => '',
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => 'section_target_item one_two is_to_hide', 'style' => $display_style)),
                        ),
                    ));
                }

                foreach ($value['col_massnahmen'] as $keyMassnahme => $valueMassnahme) {

                    $idnames_item = Pms_CommonData::str_safeascii($valueMassnahme, '', '');
                    $item_id = $item_id = "mass_" . $idnames_subsection_id . "_" . $idnames_item;

                    array_key_exists($item_id, $stored_data['input_values']) ? $input_value = $stored_data['input_values'][$item_id] : $input_value = '';
                    strpos($valueMassnahme, "FREETEXT") ? $isFreetext = true : $isFreetext = false;
                    $i = strpos($valueMassnahme, "###");
                    if ($i) {
                        $hasTextfield = true;
                        $css_class = 'section_activity_item one_third is_to_hide';
                        $label = substr($valueMassnahme, 0, $i);
                    } elseif ($isFreetext) {
                        $hasTextfield = false;
                        $css_class = 'section_activity_item one_ten is_to_hide';
                        $label = '';
                    } else {
                        $hasTextfield = false;
                        $css_class = 'section_activity_item is_to_hide';
                        $label = $valueMassnahme;
                    }

                    $subform_input_values->addElement('select', $item_id, array(
                        'value' => $input_value,
                        'multiOptions' => $activity_range,
                        'label' => $label,
                        'title' => $this->translate('care_process_clinic_times'),
                        'tooltip' => 'test',
                        'required' => false,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array('Label', array('placement' => 'IMPLICIT_APPEND')),
                            array('HtmlTag', array('tag' => 'div', 'class' => $css_class, 'style' => $display_style)),
                        ),
                    ));
                    $item_id_hiddenfield = $item_id . '_lbl';
                    $subform_input_values->addElement('hidden', $item_id_hiddenfield, array(
                        'label' => null,
                        'value' => $label,
                        'required' => false,
                        'readonly' => true,
                        'decorators' => array('ViewHelper'),
                    ));

                    if ($hasTextfield) {
                        $item_id_textfield = $item_id . '_txt';
                        array_key_exists($item_id_textfield, $stored_data['input_values']) ? $input_value_textfield = $stored_data['input_values'][$item_id_textfield] : $input_value_textfield = '';

                        $subform_input_values->addElement('text', $item_id_textfield, array(
                            'value' => $input_value_textfield,
                            'label' => '',
                            'required' => false,
                            'filters' => array('StringTrim'),
                            'decorators' => array(
                                'ViewHelper',
                                array('Label', array('placement' => 'IMPLICIT_APPEND')),
                                array('HtmlTag', array('tag' => 'div', 'class' => 'section_activity_item two_third is_to_hide', 'style' => $display_style)),
                            ),
                        ));
                    }

                    if ($isFreetext) {
                        $item_id_freetext = $item_id . '_txt';
                        array_key_exists($item_id_freetext, $stored_data['input_values']) ? $input_value_textfield = $stored_data['input_values'][$item_id_freetext] : $input_value_textfield = '';

                        $subform_input_values->addElement('text', $item_id_freetext, array(
                            'value' => $input_value_textfield,
                            'label' => '',
                            'required' => false,
                            'filters' => array('StringTrim'),
                            'decorators' => array(
                                'ViewHelper',
                                array('Label', array('placement' => 'IMPLICIT_APPEND')),
                                array('HtmlTag', array('tag' => 'div', 'class' => 'section_activity_item nine_ten is_to_hide', 'style' => $display_style)),
                            ),
                        ));
                    }

                }
            }
        }

        $subform->addElement('note', 'clear', array(
            'value' => '&nbsp',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'style' => 'clear: both;')),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);
    }

    /**
     * ISPC-2599 Basisassessment Pflege
     *
     * @param array $options
     * @param $ipid
     * @param bool $patientdata
     * @return Zend_Form_SubForm
     */
    public function create_form_pflegeba($options = array(), $ipid, $patientdata = false)
    {
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);
        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])){
            //$stored_data = $this->get_genogram_data($options);
           // $options['actual_problems'];
            $stored_data = $options;
        }
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_PFLEGEBA);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => pflegeba,
            'formblockname' => self::BLOCK_PFLEGEBA,
            'blocktitle' => "Basisassessment Pflege",
            'template' => 'form_block_pflegeba.html',
            'formular_type' => $pdf,
        );

        $data = array();

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['f_values'] = $stored_data['pflegeba'];
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }

        if (!$stored_data) {
            $stored_data = array();
        }

        $data['f_values'] = $stored_data['pflegeba'];


        $data['config'] = array('belongsto' => self::BLOCK_PFLEGEBA);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }


    /**
     * ISPC-2476 RE-Assessment Nordrhein (form ipos repeated min. twice, that's why have to be refactored) - elena
     *
     * @param array $options
     * @param $ipid
     * @param bool $patientdata
     * @return Zend_Form_SubForm
     */
    public function create_form_ipos($options = array(), $ipid, $patientdata = false, $ipos_add = false)
    {
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);
        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])){
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        }else if (isset($options['formular_type'])){

            $stored_data = $options;
        }
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_IPOS);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => 'IPOS',
            'formblockname' => self::BLOCK_IPOS,
            'blocktitle' => "IPOS",
            'template' => 'form_block_ipos.html',
            'formular_type' => $pdf,
        );

        $data = array();



        if (!$stored_data || count($stored_data) == 1) {
            $pdf = $stored_data['formular_type'];
            $stored_data = array();
            if($patientdata !== false){
                $stored_data = $patientdata;
                $stored_data['formular_type'] = $pdf;
            }

        }

        $data['f_values'] = $stored_data['ipos'];
        if(!empty($ipos_add)){
            $data['f_values']['patient_ipos_add_values'] = $ipos_add;
        }
/*
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {

            if(empty($stored_data['ipos'])){
                $data['f_values'] = $patientdata['ipos'];

                //print_r($data['f_values']);

            }else{
                $data['f_values'] = $stored_data['ipos'];
            }



            return $this->create_simple_auto_add_block($blockconfig, $data);
        }
        */

        $data['config'] = array('belongsto' => self::BLOCK_IPOS);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];
        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    /**
     * form block karnofsky
     * ISPC-2476 RE-Assessment Nordrhein (form karnofsky repeated min. twice, that's why have to be refactored)
     *
     * @param array $options
     * @param $ipid
     * @param bool $patientdata
     * @return Zend_Form_SubForm
     */
    public function create_form_karnofsky($options = array(), $ipid, $patientdata = false)
    {
        //echo($patientdata['karnofsky']['value']);
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);
        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])){
            //$stored_data = $this->get_genogram_data($options);
            // $options['actual_problems'];
            $stored_data = $options;
        }
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_KARNOFSKY);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }
        if (!$stored_data || !(isset( $stored_data['value']))) {
            $stored_data = array();
            $stored_data['options'] = Pms_CommonData::get_karnofsky();
            if($patientdata !== false){
                $stored_data['value'] = $patientdata['karnofsky']['value'];
            }

        }


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => 'Karnofsky',
            'formblockname' => self::BLOCK_KARNOFSKY,
            'blocktitle' => "Karnofsky",
            'template' => 'form_block_karnofsky.html',
            'formular_type' => $pdf,
        );

        $data = array();

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['options'] = Pms_CommonData::get_karnofsky();
            $data['f_values'] = $stored_data;
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }

        if (!$stored_data) {
            $stored_data = array();
            $stored_data['options'] = Pms_CommonData::get_karnofsky();
            if($patientdata !== false){
                $stored_data['value'] = $patientdata['karnofsky']['value'];
            }

        }

        $data['f_values'] = $stored_data;
        //print_r($data['f_values']);

        $data['config'] = array('belongsto' => self::BLOCK_KARNOFSKY);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }


    /**
     * ISPC-2628, contactform block fileupload, elena, 20.07.2020
     *
     * @param array $options
     * @param $ipid
     * @return Zend_Form_SubForm
     */
    public function create_form_fileupload($options = array(), $ipid){
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $user = new User();
        $userarray = $user->getUserDetails($userid);

        //var_dump($userarray); exit;


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])){
            //$lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_FILEUPLOAD);
            //$stored_data = json_decode($lastBlockValue['returnvalue'], true);
            $stored_data = $options;
            //$stored_data['formular_type'] = $options['formular_type'];
        }
        // prefill a new contactform with previous values => loaded values by the last contactform of the user
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_FILEUPLOAD);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }
        $stored_data['enc_id'] = $encid;
        $stored_data['pat_id'] = $decid;


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "fileupload",
            'blocktitle' => "Fileupload",
            'template' => 'form_block_fileupload.html',
            'formular_type' => $pdf,
        );


        $client_tags = PatientFileTags::get_client_tags($clientid);
        $blockconfig['client_tags'] = $client_tags;
        $restricted_tags = [];
        foreach($client_tags as $k_ctg => $v_ctg)
        {
            if($v_ctg['restricted'] == '1')
            {
                $restricted_tags[] = $v_ctg['tag'];
            }
        }

        $system_tags = PatientFileTags::get_allclients_tags();
        $blockconfig['system_tags'] = $system_tags;

        foreach($system_tags as $k_tg => $v_tg)
        {
            if($v_tg['restricted'] == '1')
            {
                $restricted_tags[] = $v_tg['tag'];
            }
        }

        $blockconfig['restricted_tags']= $restricted_tags;
        $blockconfig['restricted_tags_js'] = json_encode($restricted_tags);
        $blockconfig['patient_file_tag_rights'] = $userarray[0]['patient_file_tag_rights'];

        //ISPC - 2018
        $all_tags = array_merge($client_tags, $system_tags);
        foreach($all_tags as $k_tg => $v_tg)
        {
            $all_tags_val[] = $v_tg['tag'];
        }

        $blockconfig['all_tags'] = $all_tags_val;
        $blockconfig['all_tags_js'] = json_encode($all_tags_val);



        $data = array();

//print_r($stored_data);
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['f_values'] = $stored_data;
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }


        $data['f_values'] = $stored_data;

        $data['encid'] = Pms_Uuid::encrypt($decid);
        $data['config'] = array('belongsto' => self::BLOCK_FILEUPLOAD);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];
        //print_r($blockconfig);

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    public function create_form_lmu_pmba2($options = array(), $ipid){

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);
        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])){
            //$lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_FILEUPLOAD);
            //$stored_data = json_decode($lastBlockValue['returnvalue'], true);
            $stored_data = $options;
            //$stored_data['formular_type'] = $options['formular_type'];
        }
        // prefill a new contactform with previous values => loaded values by the last contactform of the user
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_FILEUPLOAD);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "lmu_pmba2",
            'blocktitle' => "lmu_pmba2_title",
            'template' => 'form_block_lmu_pmba2.html',
            'formular_type' => $pdf,
        );

        $data = array();

//print_r($stored_data);
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['f_values'] = $stored_data;
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }


        $data['f_values'] = $stored_data;

        //$data['encid'] = Pms_Uuid::encrypt($decid);
        $data['config'] = array('belongsto' => self::BLOCK_LMU_PMBA2);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }


	/**
	 * ISPC-2577, elena, 07.09.2020
	 *
	 * @param array $options
	 * @param $ipid
	 * @param $shortcodeblockdata
	 * @return Zend_Form_SubForm
	 */
    public function create_form_dynamic($options = array(), $ipid, $shortcodeblockdata){

		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn

		$blockname =  'block_shortcode_' . $shortcodeblockdata['id'];
		//$fn_options = $this->getFnOptions($__fnName);
		//rewrite for 'faked' function
		// the function doesn't exitst, but we need infos whether the block opened or closed only
		$fn_options = $this->getFnOptions('create_form_' . $blockname);

		// update an existing contactform => loaded old values by ContactFormId
		if (isset($options['v']))
			$stored_data = json_decode($options['v'], true);
		// use the post ones, maybe this is just a print
		else if (isset($options['formular_type'])){
			//$lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_FILEUPLOAD);
			//$stored_data = json_decode($lastBlockValue['returnvalue'], true);
			$stored_data = $options;
			//$stored_data['formular_type'] = $options['formular_type'];
		}
		// prefill a new contactform with previous values => loaded values by the last contactform of the user
		else if (isset($ipid)) {
			//TODO-3841,Elena,09.02.2021
			//don't prefill this block with old text
			//$lastBlockValue = $this->getLastBlockValues($ipid, $blockname);
			//$stored_data = json_decode($lastBlockValue['returnvalue'], true);
		}

		$pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

		$blockconfig = array(
			'blockname' => $blockname,
			'blocktitle' => $shortcodeblockdata['blockname'],
			'template' => 'form_block_dynamic.html',
			'formular_type' => $pdf,
		);

		$data = array();

//print_r($stored_data);
		//create the pdf-Layout and return
		if ($options['formular_type'] == 'pdf') {
			$data['f_values'] = $stored_data;

			$data['shortcut'] = $shortcodeblockdata['shortcut'];
			$data['title'] = $shortcodeblockdata['blockname'];
			$data['blockname'] = $blockname;

			return $this->create_simple_auto_add_block($blockconfig, $data);
		}


		$data['f_values'] = $stored_data;


		$data['shortcut'] = $shortcodeblockdata['shortcut'];
		$data['title'] = $shortcodeblockdata['blockname'];
		$data['blockname'] = $blockname;

		//print_r($data['shortcutsarr']);

		//$data['encid'] = Pms_Uuid::encrypt($decid);
		//$data['config'] = array('belongsto' => self::BLOCK_LMU_PMBA2);
		// IM-131 important, to keep block open if it is configured
		$data['expanded'] = $fn_options['expanded'];
		$data['opened'] = $fn_options['opened'];

		return $this->create_simple_auto_add_block($blockconfig, $data);


	}



	/**
	 * ISPC-2698, elena, 22.12.2020
	 *
	 * @param array $options
	 * @param $ipid
	 * @param $optsblockdata
	 * @return Zend_Form_SubForm
	 */
    public function create_client_options_form($options = array(), $ipid, $optsblockdata){

		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn

		$blockname =  'block_opt_' . $optsblockdata['id'];

		//rewrite for 'faked' function
		// the function doesn't exitst, but we need infos whether the block opened or closed only
		$fn_options = $this->getFnOptions('create_form_' . $blockname);

		// update an existing contactform => loaded old values by ContactFormId
		if (isset($options['v']))
			$stored_data = json_decode($options['v'], true);
		// use the post ones, maybe this is just a print
		else if (isset($options['formular_type'])){

			$stored_data = $options;
			//$stored_data['formular_type'] = $options['formular_type'];
		}
		// prefill a new contactform with previous values => loaded values by the last contactform of the user
		else if (isset($ipid)) {
			$lastBlockValue = $this->getLastBlockValues($ipid, $blockname);
			$stored_data = json_decode($lastBlockValue['returnvalue'], true);
		}

		$pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

		$blockconfig = array(
			'blockname' => $blockname,
			'blocktitle' => $optsblockdata['blockname'],
			'template' => 'form_block_client_options.html',
			'formular_type' => $pdf,
		);

		$data = array();

		//create the pdf-Layout and return
		if ($options['formular_type'] == 'pdf') {
			$data['f_values'] = $stored_data;

			$data['shortcut'] = $optsblockdata['shortcut'];
			$data['title'] = $optsblockdata['blockname'];
			$data['headline'] = $optsblockdata['headline'];
			$data['blockname'] = $blockname;
			$data['options'] = json_decode($optsblockdata['options'], true);

			return $this->create_simple_auto_add_block($blockconfig, $data);
		}


		$data['f_values'] = $stored_data;


		$data['shortcut'] = $optsblockdata['shortcut'];
		$data['title'] = $optsblockdata['blockname'];
		$data['blockname'] = $blockname;
		$data['headline'] = $optsblockdata['headline'];
		$data['options'] = json_decode($optsblockdata['options'], true);

		//print_r($data['shortcutsarr']);

		//$data['encid'] = Pms_Uuid::encrypt($decid);
		//$data['config'] = array('belongsto' => self::BLOCK_LMU_PMBA2);
		// IM-131 important, to keep block open if it is configured
		$data['expanded'] = $fn_options['expanded'];
		$data['opened'] = $fn_options['opened'];

		return $this->create_simple_auto_add_block($blockconfig, $data);


	}




    public function create_form_genogram($options = array(), $ipid, $patientdata = false)
    {

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type']))
            $stored_data = $this->get_genogram_data($options);
        // prefill a new contactform with previous values => loaded values by the last contactform of the user
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_GENOGRAM);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "genogram",
            'blocktitle' => "Genogramm",
            'template' => 'form_block_genogram.html',
            'formular_type' => $pdf,
        );

        $data = array();

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['f_values'] = $stored_data;
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }

        if (!$patientdata) {
            $patientmaster = new PatientMaster();
            $patientdata = $patientmaster->get_patientMasterData();
        }

        //die Kontaktperson-Arten
        $dgitems = array();
        $pat_details = new PatientDetails($ipid);
        foreach ($pat_details->categories['contactperson']['cols'] as $col) {
            if ($col['db'] == 'cnt_familydegree_id') {
                $dgitems = $col['items'];
            }
        }

        $ef = new ExtraForms();
        $allowedFormBoxes = $ef->get_client_forms($this->logininfo->clientid);
        $patientDetailsForm = new Application_Form_PatientDetails(
            array(
                '_patientMasterData' => $patientdata,
                '_block_name' => 'PatientDetails',
                '_clientForms' => $allowedFormBoxes,
                '_onlyThisModel' => 'ContactPersonMaster',

            ),
            $this->ipid
        );

        //Box-Kategorie
        $allCategories = $patientDetailsForm->getAllCategories();
        //PatientFormData
        $patientFormData = $patientDetailsForm->getPatientData($this->ipid);

        //this is the first genogram for this patient => create for each contact-person a genogram-block
        if (!$stored_data) {
            $stored_data = array();
            $stored_data['name']['A'][2][]="Patient";
            $stored_data['role']['A'][2][]="##Patient";

            foreach ($patientdata['ContactPersonMaster'] as $data) {
                $name = trim($data['cnt_first_name'] . " " . $data['cnt_last_name']);
                $stored_data['name']['B'][2][] = $name;
                $stored_data['role']['B'][2][] = $dgitems[$data['cnt_familydegree_id']];
                $stored_data['phone']['B'][2][] = $data['cnt_phone'];
                $stored_data['mobile']['B'][2][] = $data['cnt_mobile'];
            }
        }

        //if there is no contact-person, an empty box is shown, that doesn't works. St a marker to hide this box.

        if (count($patientFormData["ContactPersonMaster"]) == 1 && $patientFormData["ContactPersonMaster"][0]['editDialogHtml'] == null) {
            $data['contactperson_exist'] = false;
        } else {
            $data['contactperson_exist'] = true;
        }


        $data['f_values'] = $stored_data;
        //the Categorie for the Contactperson
        $data['contactperson_cat'] = $allCategories['ContactPersonMaster'];
        $data['contactperson_cat']['id'] = 'ContactPersonMaster';
        $data['data'] = $patientFormData;
        //the categories of contact-persons
        $data['contactperson_type'] = $dgitems;
        $data['encid'] = Pms_Uuid::encrypt($decid);
        $data['memos'] = PatientVersorger::getEntry($ipid, 'memo-patientdetails');
        $data['config'] = array('belongsto' => self::BLOCK_GENOGRAM);
        // IM-131 important, to keep block open if it is configured
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    public function create_form_psychosocial_status($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $stored_data = '';
        $actual_data = true;
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
            $actual_data = false;
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data = $options;
            unset($stored_data['data']);
        }
        // no prefill of a new contactform with previous values => loaded values by the actual PatientData
        /** else if (isset($ipid)) {
         * $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_PSYCHOSOZIAL_STATUS);
         * $stored_data = json_decode($lastBlockValue['returnvalue'], true);
         * $actual_data = false;
         * }*/


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "psychosocial_status",
            'blocktitle' => $this->translate('psychosocial_status'),
            'template' => 'form_block_psychosocial_status.html',
            'formular_type' => $pdf,
            'class' => $options['class']
        );

        $data = array();
        $data['actual_data'] = $actual_data;
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $data['f_values'] = $stored_data;
            $is_data_empty = true;
            foreach($stored_data as $entry){
                if(is_array($entry) && count($entry) > 1){ //Hotfix Nico 10.02.2021 ISPC-2822
                    $is_data_empty = false;
                }
            }
            $data['f_values']['is_data_empty'] = $is_data_empty;
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }


        $ef = new ExtraForms();
        $allowedFormBoxes = $ef->get_client_forms($this->logininfo->clientid);

        $versorger = new ClinicVersorger();
        $patientMasterData = $versorger->getPatientData_with_extra_data($decid);

        $patientDetailsForm = new Application_Form_PatientDetails(
            array(
                '_patientMasterData' => $patientMasterData,
                '_block_name' => 'PatientDetails',
                '_clientForms' => $allowedFormBoxes,

            ),
            $ipid
        );

         $boxes4block = Client::getClientconfig($clientid, 'boxes_psychosocial_status');

        //this is the first psychosocial status for this patient => read the patient-details for View
        if (!$stored_data) {
            $merge_cat = array_merge(array_values($boxes4block['left']), array_values($boxes4block['right']));
            $patdet = new PatientDetails($ipid);
            $stored_data = $patdet->patientdata_get_pretty($merge_cat);
        }

        //the data for the boxes

        //Box-Kategorie
        $allCategories = $patientDetailsForm->getAllCategories();
        //PatientFormData
        $patientFormData = $patientDetailsForm->getPatientData($ipid);


        $boxesPlacement = [];

        foreach ($boxes4block as $key => $catlist) {
            foreach ($catlist as $keycat => $cat) {

                if (!array_key_exists($keycat, $allCategories))
                    continue; // we want not all the blocks

                $catFromPatientDetails = $allCategories[$keycat];

                if (!isset($allowedFormBoxes[$catFromPatientDetails['extra_form_ID']])
                    || !$allowedFormBoxes[$catFromPatientDetails['extra_form_ID']]) {
                    //not allowed to this box (@dev you can go to /extraforms/formlist and assign a box to a client)
                    continue;
                }

                //all boxes have the placement 'left' by default, so we define an own placement
                //$boxesPlacement['left'] // $boxesPlacement['right']
                $boxesPlacement[$key][] = $keycat;
            }
        }

        //the Categorie for the Contactperson
        //$data['boxesOpened'] = ClientConfig::getConfigOrDefault($clientid, 'boxesOpened_psychosocial_status');
        $data['boxesOpened'] = Client::getClientconfig($clientid, 'boxesOpened_psychosocial_status');
        $data['boxesPlacement'] = $boxesPlacement;
        $data['data'] = $patientFormData;
        $data['f_values'] = $stored_data;
        $data['mappings'] = $allCategories;
        $data['encid'] = Pms_Uuid::encrypt($decid);
        $data['memos'] = PatientVersorger::getEntry($ipid, 'memo-patientdetails');
        $data['config'] = array('belongsto' => self::BLOCK_PSYCHOSOZIAL_STATUS);
        $data['class'] = $fn_options['class'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    /**
     * ISPC-2626 ISPC:: plz estimate - new contact form block
     * @param array $options
     * @param $ipid
     * @param $form_type_id
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function create_form_coordinationtime($options = array(),  $form_type_id, $ipid){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])){

            $stored_data = $options;
        }
        else if (isset($ipid)) {
            $lastBlockValue = $this->getLastBlockValues($ipid, self::BLOCK_COORDINATIONTIME);
            $stored_data = json_decode($lastBlockValue['returnvalue'], true);
        }
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_coordinationtime");
        $this->add_javascript_coordinationtime();


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('coordinationtime');
        //$this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'block_coordinationtime', array(
            'value' => 'block_coordinationtime',
            'elementBelongsTo' => self::BLOCK_COORDINATIONTIME,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));
        //$subform->addElement
        $subform->addElement('note', 'coordinationtime_label' , array(
            'value' => 'Koordinationszeit',
            //'belongsTo' => '[item]',
            'elementBelongsTo' => self::BLOCK_COORDINATIONTIME,
            'array_index' => noindex,
            'index_type' => 'array',
            //'class' => 'palassitem',
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',

                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: middle;',
                    'width' => '35%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,

                )),
            ),
        ));

        $subform->addElement('text', 'coordinationtime' , array(
            'value' => $stored_data['coordinationtime'],
            //'belongsTo' => '[item]',
            'elementBelongsTo' => self::BLOCK_COORDINATIONTIME,
            'array_index' => noindex,
            'index_type' => 'array',
            //'class' => 'palassitem',
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'vertical-align: top;',
                    'width' => '65%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,

                )),
            ),
        ));

        $subform->addElement('note', 'coordination_container', array(
            'value' => '', //$this->translate('block_report_recipient_nachrichtlich'),

            'decorators' => array(
                'SimpleTemplate',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;color:red;' , 'class' => 'coordination_container'))
            ),
        ));






        return $this->filter_by_block_name($subform, __FUNCTION__);



    }

    public function create_form_talkcontent($options = array(), $ipid, $form_type_id)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v']))
            $stored_data = json_decode($options['v'], true);
        // use the post ones, maybe this is just a print
        else if (isset($options['formular_type']))
            $stored_data = $this->get_talkingcontent_data($options['item'], $clientid, $form_type_id);

       //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_talkcontent($stored_data);
        }

        if (!$stored_data) {
            $stored_data = ClientConfig::getConfigTalkContent($clientid, $form_type_id);
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_talkcontent");

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('talkcontent');

        $subform->addElement('hidden', 'block_talkcontent', array(
            'elementBelongsTo' =>self::BLOCK_TALKCONTENT,
            'value' => 'block_talkcontent',
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        foreach ($stored_data as $key => $item) {

            if($item['is_headline']) {

                $subform->addElement('note', 'lbl_' . $key, array(
                    'value' => $item['label'],
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'class' => 'headline',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                        )),
                    ),
                ));
            }
            else{
                $subform->addElement('checkbox', 'checkbox_val_' . $key, array(
                    'elementBelongsTo' =>self::BLOCK_TALKCONTENT,
                    'belongsTo' => '[item]',
                    'checkedValue' => '1',
                    'uncheckedValue' => '0',
                    'label' => $item['label'],
                    'value' => $item['checkbox_val'],
                    'array_index' => $key,
                    'disableHidden' => false,
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        'SimpleCheckbox',
                        array('Label', array('placement' => 'IMPLICIT_APPEND')),
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'class' => 'noheadline',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                        )),
                    ),
                ));

                if($item['is_freetext']) {

                    $subform->addElement('text', 'freetext_val_'. $key, array(
                        'elementBelongsTo' =>self::BLOCK_TALKCONTENT,
                        'value' => $item['freetext_val'],
                        'class' => 'freetext',
                        'belongsTo' => '[item]',
                        'array_index' => $key,
                        'decorators' => array(
                            'ViewHelper',
                            'SimpleInput',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                            )),
                            array(array('row' => 'HtmlTag'), array(
                                'tag' => 'tr',
                            )),
                        ),
                    ));
                }
            }

        }



        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_talkwith($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data = $this->get_talkingwith_data($options, $clientid);
        }

        if (!$stored_data) {
            $stored_data['items'] = array();
            $stored_data['TALKFREETXT'] = '';
        }

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_talkwith($stored_data);
        }

        //build the multi-Option
        $contact = array('NOSELECT' => '') + $this->get_contact_list_with_id($clientid);

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_talkwith");
        $this->add_javascript_talkwith();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('talkwith');
        //$this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'block_talkwith', array(
            'value' => 'block_talkwith',
            'elementBelongsTo' => self::BLOCK_TALKWITH,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        $confkey = 0;


        foreach ($stored_data['item'] as $item) {

            $subform->addElement('note', 'label_' . $confkey, array(
                'value' => $this->translate('talkwith_contact'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '40%',
                        'class' => 'headline',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'class' => 'talkwithitemrow',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('select', 'select_' . $confkey, array(
                'value' => $item['key'],
                'multiOptions' => $contact, //key=>value
                'elementBelongsTo' => self::BLOCK_TALKWITH,
                'belongsTo' => '[item]',
                'array_index' => $confkey,
                'class' => 'talkwithitem',
                'decorators' => array(
                    'ViewHelper',
                    'SimpleSelect',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

            $confkey++;
        }


        //add en empty row
        $subform->addElement('note', 'label_' . $confkey, array(
            'value' => ($confkey == 0) ? $this->translate('talkwith_contact') : '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'width' => '25%',
                    'class' => 'lbl_talkwith',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class' => 'talkwithitemrow',
                    'openOnly' => true,
                )),
            ),
        ));

        $subform->addElement('select', 'select_' . $confkey, array(
            'value' => '',
            'multiOptions' => $contact, //key=>value
            'elementBelongsTo' => self::BLOCK_TALKWITH,
            'belongsTo' => '[item]',
            'array_index' => $confkey,
            'class' => 'talkwithitem',
            'decorators' => array(
                'ViewHelper',
                'SimpleSelect',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));


        $subform->addElement('note', 'lbl_freetext', array(
            'value' => $this->translate('talkwith_further_information'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'width' => '25%',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true,
                )),
            ),
        ));
        $subform->addElement('text', 'TALKFREETXT', array(
            'value' => $stored_data['TALKFREETXT'],
            'elementBelongsTo' => self::BLOCK_TALKWITH,
            'class' => 'freetext',
            'array_index' => 'noindex',
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_palliativ_support($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['item'] = $options['item'];
        }

        if (!$stored_data) {
            $stored_data['item'] = array();
            $stored_data['item'][] = array('key' =>'further', 'checkbox_val' => 0, 'label' => $this->translate('palliativ_support_further'));
            $stored_data['item'][] = array('key' => 'closed', 'checkbox_val' => 0, 'label' => $this->translate('palliativ_support_closed'));
        }

        //create the pdf-Layout and return
       if ($options['formular_type'] == 'pdf') {
           return $this->create_pdf_palliativ_support($stored_data);
        }

        //build the multi-Option

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_palliativ_support");

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('palliativsupport');

        $subform->addElement('hidden', 'block_palliativsupport', array(
            'value' => 'block_palliativsupport',
            'elementBelongsTo' => self::BLOCK_PALLIATIV_SUPPORT,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

         foreach ($stored_data['item'] as $key => $item) {

             $subform->addElement('hidden', 'label_'.$key, array(
                 'value' => $item['label'],
                 'belongsTo' => '[item]',
                 'elementBelongsTo' => self::BLOCK_PALLIATIV_SUPPORT,
                 'array_index' => $key,
                 'readonly' => true,
                 'decorators' => array(
                     'ViewHelper',
                     'SimpleInput',
                     array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                 ),
             ));

            $subform->addElement('checkbox', 'checkbox_val_'.$key, array(
                'elementBelongsTo' =>self::BLOCK_PALLIATIV_SUPPORT,
                'belongsTo' => '[item]',
                'checkedValue' => '1',
                'uncheckedValue' => '0',
                'label' => $item['label'],
                'value' => $item['checkbox_val'],
                'array_index' => $key,
                'disableHidden' => false,
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleCheckbox',
                    array('Label', array('placement' => 'IMPLICIT_APPEND')),
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'class' => 'noheadline',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

        }




        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_palliativ_assessment($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['item'] = $options['item'];
        }

        if (!$stored_data) {
            $stored_data['item'] = array();
            $stored_data['item']['empfehlung'][] = '';
            $stored_data['item']['freetext'] = '';
        }

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_palliativ_assessment($stored_data);
        }

        //build the multi-Option

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_palliativ_assessment");
        $this->add_javascript_palliative_assessment();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('palliativassessment');

        $subform->addElement('hidden', 'block_palliativassessment', array(
            'value' => 'block_palliativassessment',
            'elementBelongsTo' => self::BLOCK_PALLIATIV_ASSESSMENT,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

       $subform->addElement('textarea', 'freetext' , array(
                'value' => $stored_data['item']['freetext'],
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_PALLIATIV_ASSESSMENT,
                'array_index' => 'noindex',
                'rows' => 24,
                'cols' => 80,
                'class' => 'freetextrow',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleTextfield',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '95%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',

                    )),
                ),
            ));

        foreach ($stored_data['item']['empfehlung'] as $key => $item) {

            $subform->addElement('text', 'empfehlung_' . $key, array(
                'value' => $item,
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_PALLIATIV_ASSESSMENT,
                'array_index' => $key,
                'index_type' => 'array',
                'class' => 'palassitem',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '95%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'class' => 'palassrow',

                    )),
                ),
            ));

        }




        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_medicationclinic($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        $fn_options = $this->getFnOptions($__fnName);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['med_array'] = $options['med_array'];
            $stored_data['timescheme'] = $options['timescheme'];
        }


        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "medication_clinic",
            'blocktitle' => "Medikamente",
            'template' => 'form_block_mediselect.html',
            'formular_type' => $pdf,
        );

        $data = array();


        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {

            $blockconfig['template'] = 'form_block_mediselect_pdf.html';
            $data['med_array'] = $stored_data['med_array'];
            $data['timescheme'] = $stored_data['timescheme'];
            if(Client::getClientconfig($clientid, 'config_clinic_report')['legaltext']){
                $data['legaltext'] = $this->translate('medication_clinic_legaltext');
            }

            if(isset($options['extramargin']) && $options['extramargin']) {
                $data['extramargin']=true;
            }
            return $this->create_simple_auto_add_block($blockconfig, $data);
        }

        //this is the first version => read the patient-data
        if (!$stored_data) {
            $model = new PatientDrugPlan();
            $stored_data['med_array'] = $model->get_medic_exportdata($ipid, array(array('check', 1)), array(array('check', 1)));
            $stored_data['timescheme'] = PatientDrugPlan::getPatientsDosageIntervals($ipid);
        }

        $data['med_array'] = $stored_data['med_array'];
        $data['timescheme'] = $stored_data['timescheme'];
        $data['encid'] = Pms_Uuid::encrypt($decid);

        $data['config'] = array('belongsto' => self::BLOCK_MEDICATION_CLINIC);

        $data['opened'] = $fn_options['opened'];
        $data['expanded'] = $fn_options['expanded'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    public function create_form_clinic_soap($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['item'] = $options['item'];
        }

        if (!$stored_data) {
            $stored_data['item']['patsympt'] = '';
            $stored_data['item']['docdiag'] = '';
        }

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_clinic_soap($stored_data);
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_clinicsoap");

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('clinic_soap');
        //$this->__setElementsBelongTo($subform, $elementsBelongTo);

        $subform->addElement('hidden', 'block_clinicsoap', array(
            'value' => 'block_clinicsoap',
            'elementBelongsTo' => self::BLOCK_SOAP,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));
        foreach ($stored_data['item'] As $key =>$value) {
            $subform->addElement('note', 'note_'.$key, array(
                'value' => $this->translate('clinic_soap_'.$key),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '25%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('textarea', $key, array(
                'value' => $value,
                'belongsTo' => '[item]',
                'elementBelongsTo' => self::BLOCK_SOAP,
                'array_index' => 'noindex',
                'rows' => 24,
                'cols' => 80,
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleTextfield',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'vertical-align: top;',
                        'width' => '65%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));
        }

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_clinic_shift($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['shift'] = $options['shift'];
        }

        if (!$stored_data) {
            $cf_start_date = $options['cf_start_date'];
            $stored_data['shift']['shifttype'] = '';
            $stored_data['shift']['shiftextra'] = '';
            $stored_data['shift']['typeoption'] = array('F' => $this->translate('block_clinic_early_shift'), 'S' => $this->translate('block_clinic_late_shift'), 'N' => $this->translate('block_clinic_night_shift'));
            $stored_data['shift']['extraoption'] =array('yesterday' => $cf_start_date.$this->translate('block_clinic_end_of_night_shift'), 'today' => $cf_start_date.$this->translate('block_clinic_begin_of_night_shift'));
        }

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_clinic_shift($stored_data);
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_clinicsoap");
        $this->add_javascript_clinic_shift();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('clinic_shift');
       // $this->__setElementsBelongTo($subform, self::BLOCK_SHIFT);

        $subform->addElement('hidden', 'block_clinicshift', array(
            'value' => 'block_clinicshift',
            'elementBelongsTo' => self::BLOCK_SHIFT,
            'array_index' => 'noindex',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        foreach ($stored_data['shift']['extraoption'] as $okey => $oval){
            $subform->addElement('hidden', $okey, array(
                'value' => $oval,
                'elementBelongsTo' => self::BLOCK_SHIFT.'[shift][extraoption]',
                'array_index' => 'noindex',
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
        }

        foreach ($stored_data['shift']['typeoption'] as $okey => $oval){
            $subform->addElement('hidden', $okey, array(
                'value' => $oval,
                'elementBelongsTo' => self::BLOCK_SHIFT.'[shift][typeoption]',
                'array_index' => 'noindex',
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
        }

        $subform->addElement('radio', 'shifttype', array(
            'value' => $stored_data['shift']['shifttype'],
            'multiOptions' => $stored_data['shift']['typeoption'],
            'label'        => $this->translate('block_clinic_please_select'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'belongsTo' => self::BLOCK_SHIFT.'[shift]',
            'class' => 'rb_shifttype',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td' , 'style' => 'width:200px;',
                    )),
                array('Label', array('tag' => 'td', 'style' => 'width:200px;')),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    )),
            ),
        ));

        $subform->addElement('select', 'shiftextra', array(
            'value' => $stored_data['shift']['shiftextra'],
            'multiOptions' => $stored_data['shift']['extraoption'],
            'elementBelongsTo' => self::BLOCK_SHIFT. '[shift]',
            //'belongsTo' => '[shift]',
            'array_index' => 'noindex',
            'decorators' => array(
                'ViewHelper',
                'SimpleSelect',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan' => 2,
                    'style' => 'vertical-align: top; width:400px;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                )),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    public function create_form_clinic_measure($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['measures'] = $options['measures'];
        }

        if (!$stored_data) {
            $stored_data['measures'][] = array('caption' => 'Bitte Auswählen');
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "clinic_measure",
            'blocktitle' => "klinische Massnahmen",
            'template' => 'form_block_clinic_measure.html',
            'formular_type' => $pdf,
        );

        $data = array();

        $items_config = Client::getClientconfig($clientid, 'block_clinic_measure');

        $data['f_values'] = $stored_data;
        $data['items_config'] = $items_config;
        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    public function create_form_clinic_diagnosis($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $actual_data = true;
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
            $actual_data = false;
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['clinic_diagnosis'] = $options['clinic_diagnosis'];
            $stored_data['diagnosis_types'] = $options['diagnosis_types'];
        }

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_clinic_diagnosis($stored_data);
        }

        $client =  new Client();
        $client_data = $client->findOneById($clientid);

        $frm = new ExtraForms();
        $clientForms = $frm->get_client_forms($clientid);

        $modules =  new Modules();
        $clientModules = $modules->get_client_modules($clientid);

        if(!$stored_data){
            $entity = new PatientDiagnosis();
            $saved = $entity->getAllDiagnosis($ipid);
            $stored_data['clinic_diagnosis'] =  ! empty($saved[$ipid]) ? $saved[$ipid] : array();

            $abb = "'HD','ND'";
            if ($clientModules[81]) {
                //Show Hauptsymptomlast(HS) column in Patient diagnosis. HS diagnosis type must be created in Administration>Diagnosen Arten
                $abb .= ",'HS'";
            }
            if ($clientModules[1005]) {
                //Show Palliativfall-begruendende Diagnose (PBD) column in Patient diagnosis. PBD diagnosis type must be created in Administration>Diagnosen Arten
                $abb .= ",'PBD'";
            }
            $dt = new DiagnosisType();
            $stored_data['diagnosis_types'] = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
        }

        $af_pd = new Application_Form_PatientDiagnosis(array(
            '_block_name'           => 'clinic_diagnosis',
            '_clientForms'          => $clientForms,
            '_clientModules'        => $clientModules,
            '_client'               => $client_data,
        ));

        if(!$options['reloadDiagnosis']) {
            $this->add_javascript_clinic_diagnosis();
        }

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->removeDecorator('SimpleTable');
        //$subform->removeDecorator('SimpleContactformBlock');
        $subform->setAttrib('class', 'wlassessment_form_class');
        $subform->setLegend('clinic_diagnosis');

        $subformdata = $this->subFormContactformBlock();
        $subformdata->removeDecorator('SimpleContactformBlock');
        $subformdata->removeDecorator('SimpleTable');
        //dummy-element for creating the div
        $subformdata->addElement('hidden', 'dummy', array(
            'value' => '',
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                array(array('ltag' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'style' => 'width: 100%;',
                    'id' => 'subformdata',
                    'openOnly' => false,
                )),

            ),
        ));

        //add a note for the user, if this is an update of an existing formular
        $actual_data = false;
        if(!$actual_data) {
            $subformStatus = $this->subFormContactformBlock();
            $subformStatus->removeDecorator('SimpleTable');
            $subformStatus->removeDecorator('SimpleContactformBlock');

            $subformStatus->addElement('hidden', 'encid', array(
                'value' => $encid,
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;')),
                    array(array('ediv' => 'HtmlTag'), array(
                        'id' => 'fb_clinic_diagnosis_status_header',
                        'tag' => 'div',
                        'style' => 'background-color: #ffffcc;border:1px solid #ccc;margin:4px;padding:4px;',
                        'openOnly' => false,
                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
                    )
                ),
            ));
/*
            $subformStatus->addElement('note', 'clinic_measure_status_warning', array(
                'value' => $this->translate('clinic_measure_status_warning'),
                'array_index' => 'noindex',
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'style' => 'width: 95%; display: inline-block; vertical-align: top;'
                    )),
                ),
            ));

            $subformStatus->addElement('button', 'status_button', array(
                'label' => $this->translate('clinic_measure_status_button'),
                'class' => 'button',
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'openOnly' => true,
                    )),
                ),
            ));
            $subformStatus->addElement('note', 'clinic_measure_status_refresh', array(
                'value' => $this->translate('clinic_measure_status_refresh'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'closeOnly' => true,
                    )),
                    array(array('ediv' => 'HtmlTag'), array(
                        'closeOnly' => true,
                    )
                    ),
                )));*/
            $subform->addSubForm($subformStatus, 'diagnosis_status');
        }
        //add a note for the user, if this is an update of an existing formular
        if(!$actual_data) {
            $subformDiagnosisEdit = $this->subFormContactformBlock();
            $subformDiagnosisEdit->removeDecorator('SimpleTable');
            $subformDiagnosisEdit->removeDecorator('SimpleContactformBlock');

            $subformDiagnosisEdit->addElement('hidden', 'encid', array(
                'value' => $encid,
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;')),
                    array(array('ediv' => 'HtmlTag'), array(
                        'id' => 'fb_clinic_diagnosis_diagnosis_header',
                        'tag' => 'div',
                        'style' => 'background-color: #ffffcc;border:1px solid #ccc;margin:4px;padding:4px;',
                        'openOnly' => true,
                        'placement' => Zend_Form_Decorator_Abstract::PREPEND),
                    )
                ),
            ));

            $subformDiagnosisEdit->addElement('note', 'clinic_measure_diagnosis_warning', array(
                'value' => $this->translate('clinic_measure_diagnosis_warning'),
                'array_index' => 'noindex',
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'style' => 'width: 95%; display: inline-block; vertical-align: top;'
                    )),
                ),
            ));

            $subformDiagnosisEdit->addElement('button', 'diagnosis_button', array(
                'label' => $this->translate('clinic_measure_diagnosis_button'),
                'class' => 'button',
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'openOnly' => true,
                    )),
                ),
            ));
            $subformDiagnosisEdit->addElement('note', 'clinic_measure_diagnosis_refresh', array(
                'value' => $this->translate('clinic_measure_diagnosis_refresh'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'closeOnly' => true,
                    )),
                    array(array('ediv' => 'HtmlTag'), array(
                        'closeOnly' => true,
                    )
                    ),
                )));
            $subformDiagnosisEdit->addElement('note', 'clinic_measure_diagnosis_container', array(
                'value' => $this->translate('block_report_recipient_nachrichtlich'),

                'decorators' => array(
                    'SimpleTemplate',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;' , 'class' => 'diagnosis_container'))
                ),
            ));
            $subform->addSubForm($subformDiagnosisEdit, 'diagnosis_edit');
        }

        $subformDiagnosis = $this->subFormContactformBlock();
        $subformDiagnosis->removeDecorator('SimpleContactformBlock');
        $subformDiagnosis->removeDecorator('SimpleTable');
        //add the diagnosis-types to $POST
        foreach($stored_data['diagnosis_types'] as $key=>$type){
            $subformDiagnosis->addElement('hidden', 'id_'.$key, array(
                'value' => $type['id'],
                'belongsTo' => '[diagnosis_type]',
                'array_index' => $key,
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
            $subformDiagnosis->addElement('hidden', 'abbrevation_'.$key, array(
                'value' => $type['abbrevation'],
                'belongsTo' => '[diagnosis_type]',
                'array_index' => $key,
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
            $subformDiagnosis->addElement('hidden', 'description_'.$key, array(
                'value' => $type['description'],
                'belongsTo' => '[diagnosis_type]',
                'array_index' => $key,
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
        }

        $subformdata->addSubForm($subformDiagnosis, 'diagnosis_types');

        $patient_diagnosis_form = $af_pd->create_form_diagnosis($stored_data['clinic_diagnosis']);
        $patient_diagnosis_form->setAttrib("class", "wlassessment_form_class livesearchFormEvents");

        $subformdata->addSubForm($patient_diagnosis_form,'clinic_diagnosis');

        $subform->addSubForm($subformdata, 'clinic_diagnosis_data');
        $this->__setElementsBelongTo($subformdata, self::BLOCK_DIAGNOSIS_CLINIC);

        if($options['reloadDiagnosis']) {
           return $subformdata;
        }

       return $subform;

    }

    /**
     * IM-137, Contact Form Block "documentation", create form
     *
     * @param array $options
     * @param $ipid
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function create_form_documentation($options = array(), $ipid)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['documentation'] = $options['documentation'];
        }
        if(!$stored_data ){
            //print_r($actualProblemsFromIpos);
            $cf_start_date = $options['cf_start_date'];
            $stored_data['documentation'] = '';

        }
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_documentation($stored_data);
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_clinicsoap");
        //$this->add_javascript_report_recipient();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('block_documentation_legend');

        $subform->addElement('hidden', 'block_documentation', array(
            'value' => 'block_documentation',
            'elementBelongsTo' => self::BLOCK_DOCUMENTATION,
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));



        $subform->addElement('textarea', 'documentation',  array(
            'value' =>  $stored_data['documentation'],
            'elementBelongsTo' => self::BLOCK_DOCUMENTATION . '[documentation]' , // it works for SimpleTextfield
            //'belongsTo' => '[report_recipient][report_recipient_mainaddress]',
            //'label' => 'Empfänger',
            //'array_index' => 0,

            'decorators' => array(
                'ViewHelper',
                'SimpleTextfield',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'style' => 'width: 100%;'
                ))
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);



    }

    /**
     * @param array $options
     * @param $ipid
     * @return Zend_Form
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     */
    public function create_form_actual_problems($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        $fbi=new FormBlockIpos();
        $actualProblemsFromIpos =$fbi->getMostRecentMainprobs($ipid, 1);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['actual_problems'] = $options['actual_problems'];
        }
        if(!$stored_data && is_array($actualProblemsFromIpos)){
            //print_r($actualProblemsFromIpos);
            $cf_start_date = $options['cf_start_date'];
            $stored_data['actual_problems'][0]['problem'] = $actualProblemsFromIpos[0][0];
            $stored_data['actual_problems'][0]['check'] = 0;
            $stored_data['actual_problems'][1]['problem'] = $actualProblemsFromIpos[0][1];;
            $stored_data['actual_problems'][1]['check'] = 0;
            $stored_data['actual_problems'][2]['problem'] = $actualProblemsFromIpos[0][2];;
            $stored_data['actual_problems'][2]['check'] = 0;

        } else if(!$stored_data) {

            $cf_start_date = $options['cf_start_date'];
            $stored_data['actual_problems'][0]['problem'] = '';
            $stored_data['actual_problems'][0]['check'] = 0;
            $stored_data['actual_problems'][1]['problem'] = '';
            $stored_data['actual_problems'][1]['check'] = 0;
            $stored_data['actual_problems'][2]['problem'] = '';
            $stored_data['actual_problems'][2]['check'] = 0;

        }
        // IM-123: if all problem fields are empty
        $all_problem_fields_are_empty = true;
        for($problems_counter=0; $problems_counter<3 ;$problems_counter++){
            if(strlen(trim($stored_data['actual_problems'][$problems_counter]['problem'])) > 0 ){
                $all_problem_fields_are_empty = false;
            }
        }

        if($all_problem_fields_are_empty){
            for($problems_counter=0; $problems_counter<3 ;$problems_counter++){
                if( isset($actualProblemsFromIpos[0][$problems_counter]) && strlen(trim($actualProblemsFromIpos[0][$problems_counter])) > 0){
                    $stored_data['actual_problems'][$problems_counter]['problem'] =  $actualProblemsFromIpos[0][$problems_counter];
                    $stored_data['actual_problems'][$problems_counter]['check'] = 0;
                }
            }
        }



        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_actual_problems($stored_data);
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_clinicsoap");
        //$this->add_javascript_clinic_shift();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('actual_problems');
        // $this->__setElementsBelongTo($subform, self::BLOCK_SHIFT);

        $subform->addElement('hidden', 'block_actual_problems', array(
            'value' => 'block_actualproblems',
            'elementBelongsTo' => self::BLOCK_ACTUALPROBLEMS,
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));



        foreach($stored_data['actual_problems'] as $okey => $oval){

            $subform->addElement('checkbox',  'check_' .$okey  , array(
                'value' => $stored_data['actual_problems'][$okey]['check'],
                'elementBelongsTo' => self::BLOCK_ACTUALPROBLEMS,
                'belongsTo' => '[actual_problems]',
                'array_index' => $okey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleCheckbox',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => 'true',

                    )),
                ),
            ));
            $subform->addElement('text', "problem_" . $okey, array(
                'value' => $stored_data['actual_problems'][$okey]['problem'],
                'filters' => array('StringTrim'),
                'elementBelongsTo' => self::BLOCK_ACTUALPROBLEMS,
                'belongsTo' => '[actual_problems]',
                'array_index' => $okey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => 'true',

                    )),
                ),
            ));

        };



        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    /**
     * form Bericht Empfaenger
     *
     * @param array $options
     * @param $ipid
     * @return Zend_Form
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     */
    public function create_form_report_recipient($options = array(), $ipid)
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        $vers = new ClinicVersorger();
        $tmpt=$vers->getPatientData($ipid);
        $vcats=$vers->getAllCategories();
        $tmp=array();

        $pat_addr = PatientMaster::getPatientAddress($ipid);
        $tmp[]= array('address' => str_replace("\n",'; ',$pat_addr[0]), 'salutation_letter' => $pat_addr[1], 'caption'=>'PAtient');
        foreach($tmpt as $cat=>$centries){
            foreach($centries as $centry){
                if(count($centry)>0){
                    $line="";
                    foreach ($centry['address'] as $cent){
                        $line = $line . "; " . $cent[1];
                    }
                    if(isset($centry['data']['phone']) && strlen($centry['data']['fax'])>0){
                        $line = $line . "; Tel:" .$centry['data']['phone'];
                    }
                    if(isset($centry['data']['fax']) && strlen($centry['data']['fax'])>0){
                        $line = $line . "; Fax:" .$centry['data']['fax'];
                    }
                    if(strpos($line,"; ")===0){
                        $line=substr($line,2);
                    }
                    $tmp[]=array('address'=>$line, 'caption'=>$vcats[$cat]['label']);
                }
            }
        }

        $pm=new PatientMaster();
        $mdata=$pm->get_Masterdata_quick($ipid);
        $verb="unseren gemeinsamen Patienten, Herrn ";
        if($mdata['sex']=="F"){
            $verb="unsere gemeinsame Patientin, Frau ";
        }

        $date="???";
        $queryForLastAdmdateInStatusCase = Doctrine_Query::create()
            ->select("st.admdate")
            ->from("PatientCaseStatus st")
            ->Where("st.ipid=?", $ipid)
            ->orderBy('st.admdate DESC')
            ->limit(1); //new : Date from PatientStatusCase - Elena
        $case = $queryForLastAdmdateInStatusCase->fetchArray();
        //$case= (new PatientReadmission)->getPatientLastDischargedate($ipid); // old: Date from Readmissions - Elena
        //print_r($case);
        if($case && isset($case[0])){
            $date=$case[0]['admdate'];
            $date=date('d.m.Y', strtotime($date));
        }

        $patinfo=array('name'=>htmlspecialchars($mdata['name2']),'dob'=>$mdata['dob'], 'verb'=>$verb, 'date'=>$date);
        $anrede_text_template =  $this->translate('block_report_recipient_salutation_text');
        $anrede_text = str_replace(['#verb#', '#name#', '#dob#', '#date#'], [$verb, $patinfo['name'], $patinfo['dob'], $patinfo['date']], $anrede_text_template);

        $addrs =array('addresses'=>$tmp, 'patinfo'=>$patinfo);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['report_recipient'] = $options['report_recipient'];
        }
        if(!$stored_data) {

            $cf_start_date = $options['cf_start_date'];
            $addressCounter = 0;
            $stored_data['report_recipient']['report_recipient_mainaddress'] = '';
            $stored_data['report_recipient']['report_recipient_anrede'] = $anrede_text;
            foreach($addrs['addresses'] as $addr){
                $stored_data['report_recipient']['addrs'][$addressCounter]['address'] =   $addr['address'];
                $stored_data['report_recipient']['addrs'][$addressCounter]['check'] = 0;
                $stored_data['report_recipient']['addrs'][$addressCounter]['firstAddr'] = ($addressCounter == 0) ? 1 :0;
                if($addressCounter == 0){
                    $stored_data['report_recipient']['report_recipient_mainaddress']  =   $addr['address'];
                }
                $addressCounter++;
                
            }

        }
        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            return $this->create_pdf_report_recipient($stored_data);
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_clinicsoap");
        $this->add_javascript_report_recipient();

        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        //$subform->setAttrib("class", "datatable");
        $subform->setLegend('report_recipient');
        // $this->__setElementsBelongTo($subform, self::BLOCK_REPORTRECIPIENT);

        $subform->addElement('hidden', 'block_report_recipient', array(
            'value' => 'block_report_recipient',
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));


        $subform->addElement('note', 'block_report_recipient_sub1', array(
            'value' => $this->translate('block_report_recipient_recipient'),
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
            'decorators' => array(
                'SimpleTemplate',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));


        $subform->addElement('textarea', 'report_recipient_mainaddress',  array(
            'value' => str_replace(";", "\n", $stored_data['report_recipient']['report_recipient_mainaddress']),
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT . '[report_recipient][report_recipient_mainaddress]' , // it works for SimpleTextfield
            //'belongsTo' => '[report_recipient][report_recipient_mainaddress]',
            'label' => 'Empfänger',
            //'array_index' => 0,

            'decorators' => array(
                'ViewHelper',
                'SimpleTextfield',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'style' => 'width: 100%;'
                ))
            ),
        ));

        $subform->addElement('note', 'block_report_recipient_subheader', array(
            'value' => $this->translate('block_report_recipient_nachrichtlich'),
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,

            'decorators' => array(
                'SimpleTemplate',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;' , 'class' => 'subheader_nachrichtlich', 'data-label' => 'Nachrichtlich'))
            ),
        ));


        foreach($stored_data['report_recipient']['addrs'] as $okey => $oval){

            $subform->addElement('checkbox',  'check_' .$okey  , array(
                'value' => $stored_data['report_recipient']['addrs'][$okey]['check'],
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'belongsTo' => '[report_recipient][addrs]',
                'array_index' => $okey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleCheckbox',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'div', 'style' => 'display:inline-block;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'openOnly' => 'true',

                    )),
                ),
            ));
            $subform->addElement('text', "address_" . $okey, array(
                'value' => $stored_data['report_recipient']['addrs'][$okey]['address'],
                'filters' => array('StringTrim'),
				'style' =>  'width: 400px;',
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'belongsTo' => '[report_recipient][addrs]',
                'array_index' => $okey,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'div', 'style' => 'display:inline-block;'
                    )),

                ),
            ));

            $subform->addElement('button', "button_" . $okey, array(
                'label' => $this->translate('block_report_recipient_as_recipient'),
                'filters' => array('StringTrim'),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'belongsTo' => '[report_recipient][addrs]',
                'array_index' => $okey,
                'class' => 'btn_as_recipient',
                'decorators' => array(
                    'ViewHelper',
                    //'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'div', 'style' => 'display:inline-block;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'closeOnly' => 'true',

                    )),
                ),
            ));

        };

        $subform->addElement('note', 'block_report_recipient_salutation', array(
            'value' => $this->translate('block_report_recipient_salutation'),
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,

            'decorators' => array(

                'SimpleTemplate',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;' , 'class' => 'subheader_anrede', 'data-label' => 'Nachrichtlich'))
            ),
        ));

        $subform->addElement('textarea', 'report_recipient_anrede',  array(
            'value' => str_replace(";", "\n", $stored_data['report_recipient']['report_recipient_anrede']),
            'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT . '[report_recipient][report_recipient_anrede]' ,
            //'belongsTo' => '[report_recipient]',

            'decorators' => array(
                'ViewHelper',
                'SimpleTextfield',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'style' => 'width: 100%;'
                ))
            ),
        ));





        return $this->filter_by_block_name($subform, __FUNCTION__);

    }

    /**
	 * ISPC-2697, elena, 04.11.2020
	 *
	 * @param null $ipid
	 * @param array $options
	 * @return Zend_Form_SubForm
	 */
	public function create_form_ventilation($options = array(), $ipid){
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$decid = Pms_CommonData::getIdfromIpid($ipid);
		$encid = Pms_Uuid::encrypt($decid);
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		$fn_options = $this->getFnOptions($__fnName);
		$oMachine = new Machine();
		$machines =$oMachine->getClientMachinesForType($clientid, 'beatmung');
		$machines_ordered = [];
		foreach($machines as $machine){
			$machines_ordered[$machine['id']] = $machine;
		}
		//print_r($machines);
        $machine_chosen = false;
		$obj = new Anordnung();
        $aAnordnungen = Anordnung::getPatientBeatmungAnordnungen($ipid);
        $aAnord = [];
        foreach($aAnordnungen as $anord){
        	$anord['parameters'] = json_decode($anord['parameters'], true);
			$anord['timelinedata'] = json_decode($anord['timelinedata'], true);

			$anord['machine_name'] = $machines_ordered[$anord['machine']]['machine_name'];
			$aAnord[] = $anord;
		}
        //print_r($aAnordnungen);
       // print_r($aAnord);

		// update an existing contactform => loaded old values by ContactFormId
		if (isset($options['v'])) {
			$stored_data = json_decode($options['v'], true);
			//print_r($stored_data);
			$machine_chosen = true;
			$machine_opt = $stored_data['machine_opt'];
		} // use the post ones, maybe this is just a print
		else if (isset($options['formular_type'])) {
			$stored_data = [];
			$stored_data = $options;
			$machine_chosen = true;
			$machine_opt = $stored_data['machine_opt'];
		}

		$pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        //$machines = json_decode(ClientConfig::getConfig($clientid, 'beatmung'), true);

		$blockconfig = array(
			'blockname' => "beatmung",
			'blocktitle' => "Beatmung",
			'template' => 'form_block_ventilation.html',
			'formular_type' => $pdf,
		);

		$data = array();
		//ISPC-2904,Elena,30.04.2021
		if(isset($options['id'])){
			$data['id'] = $options['id'];
		}

		//$beatmung_config = json_decode(ClientConfig::getConfig($clientid, 'beatmung'));

		$data['f_values'] = $stored_data;
		//print_r($data['f_values']);
		//$data['beatmung_config'] = $beatmung_config;
		$data['machine_chosen'] = $machine_chosen;
        $data['machines'] = $machines;
        $data['anordnungen'] = $aAnord;
        if(isset($options['used_machine'])){
        	$data['used_machine'] = $options['used_machine'];
		}
        if(isset($options['with_datetime'])){
        	$data['with_datetime'] = $options['with_datetime'];
		}
        if(isset($stored_data['beatmung']['machine_opt'])){
			$data['machine'] = Doctrine::getTable('Machine')->find($stored_data['beatmung']['machine_opt']);

		}
		$data['config'] = array('belongsto' => self::BLOCK_BEATMUNG);
		$data['expanded'] = $fn_options['expanded'];
		$data['opened'] = $fn_options['opened'];

		return $this->create_simple_auto_add_block($blockconfig, $data);

	}

    /**
     * create the pdf-Layout for care-process-clini (IM-4)
     *
     * @param $data
     * @param $sections
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    private function create_pdf_careprocessclinic($data, $sections)
    {

        $subform = $this->subFormContactformBlock();
        $subform->setLegend('block_care_process_clinic');

        $themes = array();

        //collect the data for the output

        //"them_cpc_Koerp_Patient_be_Patient_be": "Patient benötigt vollständige Übernahme/Unterstützung bei der Körperpflege weil",
        //"prob_cpc_Koerp_Patient_be_Bewusstsei": "Bewusstseinsstörung",
        //"reso_cpc_Koerp_Patient_be_isst_selbs": "ißt selbständig",
        //"ziel_cpc_Koerp_Patient_be_Patient_fu": "Patient fühlt sich wohl",
        foreach ($data['checked_items'] as $key => $value) {
            $group_item = substr($key, 9, 16); //"Koerp_Patient_be"
            $art_item = substr($key, 0, 4); //them, prob, ziel, reso
            if ($art_item == 'them')
                $themes[$group_item][$art_item] = $value;
            elseif (strpos($key, "_txt"))
                continue;
            elseif (strpos($key, "_free")) {
                //"prob_cpc_Spezi_Hautbehand_Aseptische_free": "Aseptische Wunde, Wo? __",
                //"prob_cpc_Spezi_Hautbehand_Aseptische_free_txt": "Bein",
                $label = substr($value, 0, strpos($value, "###"));
                $label = $label . $data['checked_items'][$key . '_txt'];
                $themes[$group_item][$art_item][] = $label;
            } else
                $themes[$group_item][$art_item][] = $value;
        }

        foreach ($data['input_values'] as $key => $value) {
            $group_item = substr($key, 9, 16); //"Koerp_Patient_be"
            $art_item = substr($key, -4); //"xxxx", "_lbl', '_txt'
            if ($art_item != '_lbl' && $art_item != '_txt' && $value != '') {
                $label = '';
                if (isset($data['input_values'][$key . '_lbl']))
                    $label = $data['input_values'][$key . '_lbl'];
                if (isset($data['input_values'][$key . '_txt']))
                    $label = $label . $data['input_values'][$key . '_txt'];
                $themes[$group_item]['time'][] = $value;
                $themes[$group_item]['mass'][] = $label;
            }
        }

        foreach ($themes as $key => $value) {
            $maxSize = 0;
            $maxSize = count($value['prob']) > $maxSize ? count($value['prob']) : $maxSize;
            $maxSize = count($value['reso']) > $maxSize ? count($value['reso']) : $maxSize;
            $maxSize = count($value['ziel']) > $maxSize ? count($value['ziel']) : $maxSize;
            $maxSize = count($value['mass']) > $maxSize ? count($value['mass']) : $maxSize;
            $maxSize = count($value['time']) > $maxSize ? count($value['time']) : $maxSize;
            $themes[$key]['max_size'] = $maxSize;
        }
        if(!empty($themes)) {
            $subform->addElement('note', 'problem', array(
                'value' => $this->translate('care_process_clinic_problems'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'width' => '24%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'resource', array(
                'value' => $this->translate('care_process_clinic_resources'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'width' => '24%',
                    )),
                ),
            ));

            $subform->addElement('note', 'target', array(
                'value' => $this->translate('care_process_clinic_targets'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'width' => '24%',
                    )),
                ),
            ));

            $subform->addElement('note', 'action', array(
                'value' => $this->translate('care_process_clinic_actions'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'width' => '24%',
                    )),
                ),
            ));

            $subform->addElement('note', 'time', array(
                'value' => $this->translate('care_process_clinic_times'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'width' => '4%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

            foreach ($themes as $key => $value) {

                $name = 'them_' . $key;
                $subform->addElement('note', $name, array(
                    'value' => $value['them'],
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'th',
                            'colspan' => 5,
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                        )),
                    ),
                ));

                for ($i = 0; $i < $value['max_size']; $i++) {

                    $name = 'prob_' . $key . '_' . $i;
                    $value_prob = array_key_exists($i, $value['prob']) ? $value['prob'][$i] : '';

                    $subform->addElement('note', $name, array(
                        'value' => $value_prob,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'width' => '24%',
                            )),
                            array(array('row' => 'HtmlTag'), array(
                                'tag' => 'tr',
                                'openOnly' => true,
                            )),
                        ),
                    ));

                    $name = 'reso_' . $key . '_' . $i;
                    $value_resource = array_key_exists($i, $value['reso']) ? $value['reso'][$i] : '';

                    $subform->addElement('note', $name, array(
                        'value' => $value_resource,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'width' => '24%',
                            )),
                        ),
                    ));

                    $name = 'ziel_' . $key . '_' . $i;
                    $value_ziel = array_key_exists($i, $value['ziel']) ? $value['ziel'][$i] : '';

                    $subform->addElement('note', $name, array(
                        'value' => $value_ziel,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'width' => '24%',
                            )),
                        ),
                    ));

                    $name = 'mass_' . $key . '_' . $i;
                    $value_massnahme = array_key_exists($i, $value['mass']) ? $value['mass'][$i] : '';
                    $subform->addElement('note', $name, array(
                        'value' => $value_massnahme,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'width' => '24%',
                            )),
                        ),
                    ));

                    $name = 'time' . $key . '_' . $i;
                    $value_time = array_key_exists($i, $value['time']) ? $value['time'][$i] : '';

                    $subform->addElement('note', $name, array(
                        'value' => $value_time,
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'width' => '4%',
                                'align' => 'right',
                            )),
                            array(array('row' => 'HtmlTag'), array(
                                'tag' => 'tr',
                                'closeOnly' => true,
                            )),
                        ),
                    ));
                }
            }

        }



        return $this->filter_by_block_name($subform);
    }

    private function create_pdf_treatmentplanclinic($stored_data, $professions_conf, $patient = null)
    {
        $subform = null;


        if (isset($patient)) { //call from 'create_extra_treatment_plan_clinic'

            $subform = new Zend_Form_SubForm();
            $subform->setDecorators(array(
                'FormElements',
                array('table' => 'HtmlTag', array('tag' => 'table', 'class' => 'SimpleTable', 'cellpadding' => "3", 'cellspacing' => "0", "style" => 'width: 100%;')),
            ));

            $subform->addElement('note', 'label_header', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 2,
                        'style' => 'width: 80%; height: 150px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

            $subform->addElement('note', 'label_pat_name', array(
                'value' => $this->translate('name_vorname'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),

            ));

            $subform->addElement('note', 'pat_name', array(
                'value' => $patient['nice_name'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),

                ),
            ));

            $subform->addElement('note', 'label_pat_number', array(
                'value' => $this->translate('patientnumber'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),

            ));

            $subform->addElement('note', 'pat_number', array(
                'value' => $patient['epid'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),

                ),
            ));

            $subform->addElement('note', 'label_adress', array(
                'value' => $this->translate('address'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),

            ));

            $subform->addElement('note', 'pat_adress', array(
                'value' => $patient['nice_address'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),

                ),
            ));

            $subform->addElement('note', 'label_pat_birthd', array(
                'value' => $this->translate('shtbirthd'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),

            ));

            $subform->addElement('note', 'pat_birthd', array(
                'value' => $patient['birthd'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),

                ),
            ));

            $subform->addElement('note', 'label_margin', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 2,
                        'style' => 'width: 80%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));
            $subform->addElement('note', 'lbl_date', array(
                'value' => $this->translate('Behandlungsplan erstellt am'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'date', array(
                'value' => $stored_data['date'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'lbl_treatment_plan_clinic_agree_with', array(
                'value' => $this->translate('treatment_plan_clinic_agree_with'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 25%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'treatmentplan_clinic_agree_with', array(
                'value' => $stored_data['agree_with'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'label_margin1', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 2,
                        'style' => 'width: 80%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

            $talk_supplied = $stored_data['talk_supply_planning'] == '0' ? $this->translate('treatment_plan_clinic_talk_supply_planning_no') : $this->translate('treatment_plan_clinic_talk_supply_planning_yes');

            $subform->addElement('note', 'treatmentplan_clinic_talk_supply_planning', array(
                'value' => $this->translate('treatment_plan_clinic_talk_supply_planning') . $talk_supplied,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 2,
                        'style' => 'width: 80%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

            $subform->addElement('note', 'label_margin2', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 2,
                        'style' => 'width: 80%;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

        } else {
            $subform = $this->subFormContactformBlock();
            $subform->setLegend('block_treatment_plan_clinic');
        }
        $plan_exists = false;

        foreach ($professions_conf as $confkey => $confvalue){
            if($stored_data[$confkey]['goal'][0] || $stored_data[$confkey]['plan'][0]){
                $plan_exists = true;
            }
        }
        if($plan_exists){
            $subform->addElement('note', 'label_blank', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 28%; border: 1px solid #000;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
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
                        'style' => 'width: 35%; border: 1px solid #000; padding-left: 5px;'
                    ))

                ),
            ));

            $subform->addElement('note', 'label_plan', array(
                'value' => $this->translate('label_plan'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'style' => 'width: 35%; border: 1px solid #000; height: 20px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),

            ));


            foreach ($professions_conf as $confkey => $confvalue) {

                $subform->addElement('note', 'label_' . $confkey, array(
                    'value' => $confvalue,
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'width: 28%;  border: 1px solid #000; height: 20px;'
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'openOnly' => true,
                        )),
                    ),

                ));


                $subform->addElement('note', 'goal_' . $confkey, array(
                    'value' => $stored_data[$confkey]['goal'][0] ? nl2br($stored_data[$confkey]['goal'][0]) : '&nbsp;',
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'width: 35%; border: 1px solid #000;padding-left: 5px;'
                        ))

                    ),
                ));


                $subform->addElement('note', 'plan_' . $confkey, array(
                    'value' => $stored_data[$confkey]['plan'][0] ? nl2br($stored_data[$confkey]['plan'][0]) : '&nbsp;',
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'width: 35%;  border: 1px solid #000; height: 20px;padding-left: 5px;'
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    ),
                ));


            }

        }



        if (!isset($patient) && $plan_exists) { // the 'normal' call from the PateintformController

            $subform->addElement('note', 'empty_row', array(
                'value' => '&nbsp;',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 3,
                        'style' => 'border: none; height: 20px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

            $subform->addElement('note', 'date', array(
                'value' => $this->translate('treatment_plan_clinic_time_plan_generated') . ': ' . $stored_data['date'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 3,
                        'style' => 'border: none; height: 20px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

            $subform->addElement('note', 'treatmentplan_clinic_agree_with', array(
                'value' => $this->translate('treatment_plan_clinic_agree_with') . ': ' . $stored_data['agree_with'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 3,
                        'style' => 'border: none; height: 20px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));
            $talk_supplied = $stored_data['talk_supply_planning'] == '0' ? $this->translate('treatment_plan_clinic_talk_supply_planning_no') : $this->translate('treatment_plan_clinic_talk_supply_planning_yes');
            $subform->addElement('note', 'treatmentplan_clinic_talk_supply_planning', array(
                'value' => $this->translate('treatment_plan_clinic_talk_supply_planning') . $talk_supplied,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspan' => 3,
                        'style' => 'border: none; height: 20px;'
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));
        }


        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_jobbackgroundclinic($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('block_job_background_clinic');

        foreach ($stored_data as $confkey => $confvalue) {
            // IM-121 if confvalue empty, don't show
            if(strlen(trim($confvalue)) > 0) {
                $subform->addElement('note', 'label_' . $confkey, array(
                    'value' => $confkey,
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '25%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'nobr' => 'true',
                            'openOnly' => true,
                        )),
                    ),
                ));

                $subform->addElement('note', 'val_' . $confkey, array(
                    'value' => $confvalue,
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '65%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    ),
                ));

            }

        }

        return $this->filter_by_block_name($subform);

    }

    /**
     * @param $stored_data
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    private function create_pdf_documentation($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('block_documentation_legend');

        if(strlen(trim($stored_data['documentation'])) > 0){
            $subform->addElement('note', 'documentation', array(
                'value' =>  nl2br(trim( $stored_data['documentation'])),

                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'div',
                            'style' => 'width: 100%;'
                        ))


                ),
            ));
        }
        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_screeningdepressionclinic($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('screen_depression_clinic');

        $selectoptions = array('NOSELECT' => $this->translate('screen_depression_clinic_no_select'),
            'YES' => $this->translate('screen_depression_clinic_yes'),
            'NO' => $this->translate('screen_depression_clinic_no'));

        $count = 1;

        foreach ($stored_data as $confkey => $confvalue) {

            if ($confkey == 'FREETEXT' || empty($confvalue) || strtolower($selectoptions[$confvalue]) == 'keine angabe')
                continue;

            $subform->addElement('note', 'label_' . $count, array(
                'value' => $count . '. ' . $confkey,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '75%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'value_' . $count, array(
                'value' => $selectoptions[$confvalue],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '20%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

            $count++;

        }

        if ($stored_data['FREETEXT'] != '') {

            $subform->addElement('note', 'FREETEXT', array(
                'value' => $count . '. ' . $stored_data['FREETEXT'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'colspAN' => 2,
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));
        }

        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_talkcontent($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('talkcontent');


        $values = array();
        $new_key = 'empty';

        //sort the entries to the heading
        foreach ($stored_data as $value) {
            if ($value['is_headline']) {
                $new_key = $value['label'];
                $values[$new_key] = array();
            }
            if (!$value['is_headline'] && $value['checkbox_val'] == '1')
                $values[$new_key][] = $value['label'];
            if ($value['is_freetext'] && $value['freetext_val'] != '')
                $values[$new_key][] = $value['freetext_val'];
        }

        $count = 0;
        foreach ($values as $key => $value) {
            // IM-121 don't show if empty
            if (!empty($value)) {


                $subform->addElement('note', 'key_' . $count, array(
                    'value' => $key,
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'width' => '50%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'openOnly' => 'true',
                        )),
                    ),
                ));

                $subform->addElement('note', 'value_' . $count, array(
                    'value' => implode('<br>', $value),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'width' => '50%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => 'true',
                        )),
                    ),
                ));

            }


            $count++;
        }

        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_talkwith($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('talkwith');

        $values = '';
        $size = count($stored_data['item']);
        foreach ($stored_data['item'] as $count => $value) {
            $values .= trim($value['value']);
            if ($count < $size - 1 && strlen(trim($value['value'])) > 0)
                $values .= '<br>';
        }

        // IM-121 if $values empty, don't show
        if(strlen($values) > 0) {
            $subform->addElement('note', 'label_talkwith', array(
                'value' => $this->translate('talkwith_contact'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '20%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => 'true',
                    )),
                ),
            ));

            $subform->addElement('note', 'value_talkwith', array(
                'value' => $values,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '75%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => 'true',
                    )),
                ),
            ));

        }




        if (trim($stored_data['TALKFREETXT']) != '') {

            $subform->addElement('note', 'label_talkfreetxt', array(
                'value' => $this->translate('talkwith_further_information'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '20%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => 'true',
                    )),
                ),
            ));

            $subform->addElement('note', 'value_talkfreetxt', array(
                'value' => $stored_data['TALKFREETXT'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'width' => '75%',
                        'closeOnly' => 'true',
                    )),
                ),
            ));
        }

        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_clinic_soap($stored_data)
    {

        $subform = $this->subFormContactformBlock();
        $subform->setLegend('clinic_soap');

        $values = '';
        $size = count($stored_data['item']);
        foreach ($stored_data['item'] as $count => $value) {
            $values .= $value['value'];
            if ($count < $size - 1 && strlen(trim($value) >0 ))
                $values .= '<br>';
        }

        foreach ($stored_data['item'] As $key =>$value) {
            //IM-121 if value doesn't exist, don't show
            if(strlen(trim($value)) > 0){
                $subform->addElement('note', 'note_'.$key, array(
                    'value' => $this->translate('clinic_soap_'.$key),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '25%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'openOnly' => true,
                        )),
                    ),
                ));

                $subform->addElement('note', 'valzue_'.$key, array(
                    'value' => $value,
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '65%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    ),
                ));

            }

        }


        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_clinic_shift($stored_data)
    {

        $subform = $this->subFormContactformBlock();
        $subform->setLegend('clinic_shift');

        $subform->addElement('note', 'shift', array(
            'value' => $stored_data['shift']['typeoption'][$stored_data['shift']['shifttype']],
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
            ),
        ));

        if ($stored_data['shift']['shifttype'] == 'N') {

            $subform->addElement('note', 'shiftextra', array(
                'value' => $stored_data['shift']['extraoption'][$stored_data['shift']['shiftextra']],
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));
        }


        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_clinic_diagnosis($stored_data)
    {
        $diag_types = array();
        foreach($stored_data['diagnosis_types'] as $type){
            $diag_types[$type['id']] = array(
                'diag_short' => $type['abbrevation'],
                'diag_long' => $type['abbrevation'] .' ('. $type['description'].')',
            );
        }


        $subform = $this->subFormContactformBlock();
        $subform->setLegend('clinic_diagnosis');


        foreach ($stored_data['clinic_diagnosis'] as $key => $value) {
            // IM-132 show row if info in min. 1 cell of row not empty - elena
            if(strlen(trim($value['description'])) > 0 || strlen(trim($value['icd_primary'])) > 0 ){
                $subform->addElement('note', 'icd_primary_'.$key, array(
                    'value' => $value['icd_primary'],
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '10%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'openOnly' => true,
                        )),
                    ),
                ));

                $subform->addElement('note', 'description_'.$key, array(
                    'value' => $value['description'],
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '45%',
                        )),
                    ),
                ));

                $subform->addElement('note', 'diagnosis_type_id_'.$key, array(
                    'value' => $diag_types[$value['diagnosis_type_id']]['diag_long'],
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'vertical-align: top;',
                            'width' => '35%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    ),
                ));

            }

        }


        return $this->filter_by_block_name($subform);

    }

    /**
     * pdf for subform actual problems
     *
     * @param $stored_data
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    private function create_pdf_actual_problems($stored_data)
    {

        $subform = $this->subFormContactformBlock();
        $subform->setLegend('actual_problems');

        foreach($stored_data['actual_problems'] as $key => $problemGroup){

            if (intval($stored_data['actual_problems'][$key]['check'] == 1)){
                $subform->addElement('note', 'problem' . $key , array(
                    'value' => '&nbsp;- ' . $stored_data['actual_problems'][$key]['problem'],
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'div',
                            'style' => 'width: 100%;',
                        ))
                    ),
                ));

            }

        }

        return $this->filter_by_block_name($subform);

    }

    /**
     * pdf for subform report recipient
     *
     * @param $stored_data
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    private function create_pdf_report_recipient($stored_data)
    {

        $subform = $this->subFormContactformBlock();
        $subform->setLegend('report_recipient');
        if(!empty($stored_data['report_recipient']['report_recipient_mainaddress'])
            && strlen(trim($stored_data['report_recipient']['report_recipient_mainaddress'])) > 0){
            $subform->addElement('note', 'block_report_recipient_sub1', array(
                'value' => $this->translate('block_report_recipient_recipient'),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'decorators' => array(
                    'SimpleTemplate',
                    array(array('ltag' => 'HtmlTag'),
                        array('tag' => 'h4'),
                        array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));



            $subform->addElement('note', 'report_recipient_mainaddress',  array(
                'value' => str_replace("\n", "<br>", $stored_data['report_recipient']['report_recipient_mainaddress']),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'belongsTo' => '[report_recipient]',

                'decorators' => array(
                    'SimpleTemplate',
                    //'SimpleInput',
                    array(array('data' => 'HtmlTag'),
                        array(
                            'tag' => 'div',
                            'style' => 'width: 100%;'
                        ))
                ),
            ));

        }
        $moreAdresses = false;
        foreach($stored_data['report_recipient']['addrs'] as $key => $recipientGroup){
           if (intval($stored_data['report_recipient']['addrs'][$key]['check'] == 1)){
               $moreAdresses = true;
           }
        }

        if(!empty($stored_data['report_recipient']['addrs']) && $moreAdresses){

            $subform->addElement('note', 'block_report_recipient_sub3', array(
                'value' => $this->translate('block_report_recipient_nachrichtlich'),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'decorators' => array(
                    'SimpleTemplate',

                    array(array('ltag' => 'HtmlTag'),
                        array('tag' => 'h4'),
                        array('tag' => 'div', 'style' => 'width: 100%;'))
                ),
            ));

            foreach($stored_data['report_recipient']['addrs'] as $key => $recipientGroup){

                if (intval($stored_data['report_recipient']['addrs'][$key]['check'] == 1)){
                    $subform->addElement('note', 'problem' . $key , array(
                        'value' => '&nbsp;- ' . $stored_data['report_recipient']['addrs'][$key]['address'],
                        'filters' => array('StringTrim'),
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'div',
                                'style' => 'width: 100%;',
                            ))
                        ),
                    ));

                }

            }



        }

        if(strlen(trim($stored_data['report_recipient']['report_recipient_anrede'])) > 0 ){
            $subform->addElement('note', 'block_report_recipient_salutation', array(
                'value' => $this->translate('block_report_recipient_salutation'),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,

                'decorators' => array(

                    'SimpleTemplate',
                    array(array('ltag' => 'HtmlTag'),
                        array('tag' => 'h4'),

                        array(
                            'tag' => 'div', 'style' => 'width: 100%;' , 'class' => 'subheader_anrede')
                    )
                ),
            ));

            $subform->addElement('note', 'report_recipient_anrede',  array(
                'value' => str_replace("\n", "<br>", $stored_data['report_recipient']['report_recipient_anrede']),
                'elementBelongsTo' => self::BLOCK_REPORTRECIPIENT,
                'belongsTo' => '[report_recipient]',

                'decorators' => array(
                    'ViewHelper',
                    //'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'div',
                        'style' => 'width: 100%;'
                    ))
                ),
            ));


        }



        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_palliativ_support($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('palliativsupport');


     foreach ($stored_data['item'] as $key => $item) {
          //IM-121 if not checked, don't show
            if($item['checkbox_val'] == 1){
                $subform->addElement('note', 'val_'.$key, array(
                    'value' => $item['checkbox_val'] == 1 ? 'X' : '&nbsp;',
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'width' => '5%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'openOnly' => 'true',
                        )),
                    ),
                ));

                $subform->addElement('note', 'label_'.$key, array(
                    'value' => $item['label'],
                    'decorators' => array(
                        'ViewHelper',
                        'SimpleInput',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'width' => '90%',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => 'true',
                        )),
                    ),
                ));


            }

        }
        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_palliativ_assessment($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('palliativassessment');

        // IM-121 don't show texbox if it is empty
        if(!empty(trim($stored_data['item']['freetext']))){
            $subform->addElement('textarea', 'freetext' , array(
                'value' => $stored_data['item']['freetext'],
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',

                    )),
                ),
            ));

        }


        foreach ($stored_data['item']['empfehlung'] as $key => $item) {

            if(empty(trim($item) )) //the last row is empty => don't print
                continue;

            $subform->addElement('text', 'empfehlung_' . $key, array(
                'value' => $key + 1 . '. ' . $item,
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                ),
            ));

        }

        return $this->filter_by_block_name($subform);

    }

    private function create_pdf_dischargeplanning($stored_data)
    {
        $subform = $this->subFormContactformBlock();
        $subform->setLegend('discharge_planning_clinic');
        if(strlen(trim($stored_data['fbkvdisdate'])) >0){
            $subform->addElement('note', 'label_dis_date', array(
                'value' => $this->translate('discharge_planning_clinic_date'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '45%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'fbkvdisdate', array(
                'value' => $stored_data['fbkvdisdate'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,

                    )),
                )));

        }

        if($stored_data['fbkvdisplace'] != 'NOSELECT'){
            $subform->addElement('note', 'label_dis_place', array(
                'value' => $this->translate('discharge_planning_clinic_placesofdeath'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '45%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'fbkvdisplace', array(
                'value' => ($stored_data['fbkvdisplace'] != 'NOSELECT') ? $stored_data['fbkvdisplace'] : '',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

        }

        if($stored_data['fbkvdissuppl'] != 'NOSELECT'){
            $subform->addElement('note', 'label_dis_suppl', array(
                'value' => $this->translate('discharge_planning_clinic_further_supply'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'width' => '45%',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'openOnly' => true,
                    )),
                ),
            ));

            $subform->addElement('note', 'fbkvdissuppl', array(
                'value' => ($stored_data['fbkvdissuppl'] != 'NOSELECT') ? $stored_data['fbkvdissuppl'] : '',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                ),
            ));

        }


        return $this->filter_by_block_name($subform);

    }

    /**
     * Generates an single Treatmentplan for clinic without the
     * other contact-form-blocks for the basis-assessment
     */
    public function create_extra_treatmentplanclinic()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $listsmodel = new SelectboxlistPlangoal();
        $professions_conf = Client::getClientconfig($clientid, 'lmutm_profsmap');

        $decid = Pms_Uuid::decrypt($_GET['encid']);
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpidFromId($decid); //patientnumber

        $erg = array();
        $get_goals = $_GET['goals'];
        $get_plans = $_GET['plans'];

        foreach ($professions_conf as $keyconf => $conf) {

            foreach ($get_goals as $goal) {
                if ($goal['id'] == 'goal_' . $keyconf)
                    $erg[$keyconf]['goal'][0] = $goal['val'];
            }
            foreach ($get_plans as $plan) {
                if ($plan['id'] == 'plan_' . $keyconf)
                    $erg[$keyconf]['plan'][0] = $plan['val'];
            }

        }

        $erg['agree_with'] = $_GET['agree_with'];
        $erg['talk_supply_planning'] = $_GET['talk_supply_planning'];
        $erg['date'] = $_GET['date'];

        $patientmaster = new PatientMaster();
        $patientarr_arr = $patientmaster->getMasterData($decid, 2);
        $patient = array();
        $patient['nice_name'] = $patientarr_arr['nice_name'];
        $patient['epid'] = $patientarr_arr['epid'];
        $patient['nice_address'] = $patientarr_arr['nice_address'];
        $patient['birthd'] = $patientarr_arr['birthd'];

        $footer_text = $this->translate('[Page %s from %s]');
        $options = array(
            "orientation" => "P",
            "customheader" => "Behandlungsplan bei Aufnahme",
            "footer_type" => "1 of n",
            "footer_text" => $footer_text,
            "margins" => array(25, 10, 20)
        );

        $form = $this->create_pdf_treatmentplanclinic($erg, $professions_conf, $patient);
        $rend = $form->render();

        //generate and store the pdf
        $pdfid = Pms_PDFUtil::generate_pdf_to_patient_file($rend, 'behandlungsplan_bei_aufnahme', 'Behandlungsplan bei Aufnahme', $ipid, $options);

        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date('Y-m-d H:i:s');
        $cust->course_type = Pms_CommonData::aesEncrypt('K');
        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Behandlungsplan bei Aufnahme'));
        $cust->user_id = $logininfo->userid;
        //  $cust->done_date = strftime("%Y-%m-%d ", strtotime( $erg['date']));
        //$cust->done_name = "";
        $cust->tabname = Pms_CommonData::aesEncrypt("fileupload");
        $cust->recordid = $pdfid;
        $cust->save();
    }

    /**
     * save the form-data for care-process-clinic (IM-4)
     *
     * @param null $ipid
     * @param array $data
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     */
    public function save_form_careprocessclinic($ipid = null, $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }

        //create patientcourse
        // $this->__save_form_visitclasification_patient_course($ipid , $data);

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data['__formular']['old_contact_form_id'], self::BLOCK_CAREPROCESS_CLINIC);

        $insert_data = array();
        $input_values = array();
        $checked_items = array();

        foreach (array_keys($data) as $keys) {
            if (strpos($keys, 'section') === 0) {
                foreach (array_keys($data[$keys]) as $key) {
                    if (isset($data[$keys][$key]['checked_items']))
                        $checked_items = $checked_items + $data[$keys][$key]['checked_items'];
                    if (isset($data[$keys][$key]['input_values']))
                        $input_values = $input_values + $data[$keys][$key]['input_values'];
                }
            }
        }
        $insert_data['checked_items'] = $checked_items;
        $insert_data['input_values'] = $input_values;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_CAREPROCESS_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }

    public function save_form_treatmentplanclinic($ipid = null, $options = array())
    {
        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_TREATMENTPLAN_CLINIC);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $professions_conf = Client::getClientconfig($clientid, 'lmutm_profsmap');
        $insert_data = $this->get_treatmentplanclinic_data($options, $professions_conf);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_TREATMENTPLAN_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

    }

    public function save_form_jobbackgroundclinic($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_JOBBACKGROUND_CLINIC);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $insert_data = $this->get_jobbackgroundclinic_data($options);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_JOBBACKGROUND_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        $config_job = ClientConfig::getConfig($clientid, 'configjob');
        $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_JOBBACKGROUND_CLINIC];

        // create an extra item for the patient-course with the given token, if the contactformular is saved for the first time
        if ($config_job && $config_job['token'] == true && $is_extra_patient_course && $options['__formular']['old_contact_form_id'] == '') {

            $shortcut = Doctrine::getTable('Courseshortcuts')->findOneByShortcut_id($config_job['token']);
            $coursetype = ($shortcut) ? $shortcut['shortcut'] : 'K';

            foreach ($insert_data as $key => $value) {
                if ($value == '')
                    continue;

                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt($coursetype);
                $cust->course_title = Pms_CommonData::aesEncrypt($this->translate('patient_course_title_job_background_clinic') . ' | ' . substr($key, 0, 30) . ' | ' . substr($value, 0, 50));
                $cust->user_id = $logininfo->userid;
                $cust->save();
            }

        }
    }

    public function save_form_screeningdepressionclinic($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_SCREENINGDEPRESSION_CLINIC);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $insert_data = $this->get_screeningdepressionclinic_data($options);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_SCREENINGDEPRESSION_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }

    public function save_form_genogram($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_GENOGRAM);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $insert_data = $this->get_genogram_data($options);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_GENOGRAM;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }

    public function save_form_psychosocial_status($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_PSYCHOSOZIAL_STATUS);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $blockConfig = Client::getClientconfig($clientid, 'boxes_psychosocial_status');
        $blocks = array_merge(array_values($blockConfig['left']), array_values($blockConfig['right']));


        $insert_data = $this->get_psychosozial_status_data($options, $blocks);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_PSYCHOSOZIAL_STATUS;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }


    public function save_form_dischargeplaningclinic($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_DISCHARGEPLANNING_CLINIC);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data = array();
        $insert_data['fbkvdisdate'] = $options['fbkvdisdate'];
        $insert_data['fbkvdisplace'] = $options['fbkvdisplace'];
        $insert_data['fbkvdissuppl'] = $options['fbkvdissuppl'];


        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_DISCHARGEPLANNING_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }

    public function save_form_talkcontent($ipid = null, $data_post, $data_block, $form_type_id)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block) || empty($form_type_id)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_TALKCONTENT);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data = $this->get_talkingcontent_data($data_block['item'], $clientid, $form_type_id);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_TALKCONTENT;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_TALKCONTENT];
        $coursetext = $this->get_talkingcontent_coursetext($insert_data);
        if ($coursetext && $is_extra_patient_course) {
            $title = ($data_post['__formular']['old_contact_form_id'] == 0) ? FormBlockKeyValue::PATIENT_COURSE_TITLE_TALKCONTENT_CREATE : FormBlockKeyValue::PATIENT_COURSE_TITLE_TALKCONTENT_UPDATE;
            //generate the PatientCourse-Entry
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockKeyValue::PATIENT_COURSE_TAB_TALKCONTENT);
            $cust->course_type = Pms_CommonData::aesEncrypt("K");
            $cust->course_title = Pms_CommonData::aesEncrypt($title);
            $cust->recorddata = $coursetext;
            $cust->user_id = $logininfo->userid;
            $cust->save();
        }
        
        
        
        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        $block = 'talkcontent';
        if( ! empty($data_post['__formular']['blocks2recorddata'])  && array_key_exists($block,$data_post['__formular']['blocks2recorddata']) && $data_post['__formular']['blocks2recorddata'][$block]['allow'] == '1'){

            $record_color = (!empty($data_post['__formular']['blocks2recorddata'][$block]['color'])) ? $data_post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
            
            
            $coursetext_rcd = '<br/><div class="rcd_'.self::BLOCK_TALKCONTENT.' pc_record_data" style="color:'.$record_color.'!important">';
            $coursetext_rcd .= $coursetext;
            $coursetext_rcd .= '</div>';
            
            
            $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data_post['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
            $cust->recorddata=$cust->recorddata . $coursetext_rcd;
            $cust->save();
        }
        // --
        
        
    }

    public function save_form_clinic_soap($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_SOAP);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['item'] = $data_block['item'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_SOAP;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_SOAP];
        $coursetext = $this->get_clinicsoap_coursetext($insert_data);
        // create an extra item for the patient-course
        if ($coursetext && $is_extra_patient_course) {
            $title = ($data_post['__formular']['old_contact_form_id'] == 0) ? FormBlockKeyValue::PATIENT_COURSE_TITLE_CLINIC_SOAP_CREATE : FormBlockKeyValue::PATIENT_COURSE_TITLE_CLINIC_SOAP_UPDATE;
            //generate the PatientCourse-Entry
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockKeyValue::PATIENT_COURSE_TAB_CLINIC_SOAP);
            $cust->course_type = Pms_CommonData::aesEncrypt("K");
            $cust->course_title = Pms_CommonData::aesEncrypt($title);
            $cust->recorddata = $coursetext;
            $cust->user_id = $logininfo->userid;
            $cust->save();
        }
    }

    public function save_form_clinic_shift($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_SHIFT);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['shift'] = $data_block['shift'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_SHIFT;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_SHIFT];
        $coursetext = $insert_data['shift']['typeoption'][$insert_data['shift']['shifttype']];
        // create an extra item for the patient-course
        if ($coursetext && $is_extra_patient_course) {

            //generate the PatientCourse-Entry
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockKeyValue::PATIENT_COURSE_TAB_CLINIC_SHIFT);
            $cust->course_type = Pms_CommonData::aesEncrypt("K");
            $cust->recorddata = $coursetext;
            $cust->user_id = $logininfo->userid;
            $cust->save();
        }
        
        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        $block = 'clinic_shift';
        if( strlen($coursetext) > 0  && ! empty($data_post['__formular']['blocks2recorddata'])  && array_key_exists($block ,$data_post['__formular']['blocks2recorddata']) && $data_post['__formular']['blocks2recorddata'][$block]['allow'] == '1'){
            
            $record_color = (!empty($data_post['__formular']['blocks2recorddata'][$block]['color'])) ? $data_post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
            $coursetext_rcd = '<br/><div class="rcd_'.FormBlockKeyValue::PATIENT_COURSE_TAB_CLINIC_SHIFT.' pc_record_data" style="color:'.$record_color.'!important">';
            $coursetext_rcd .= "<b>".$this->translate('block_'.$block).":</b> ".$coursetext;
            $coursetext_rcd .= '</div>';
            
            
            $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data_post['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
            $cust->recorddata=$cust->recorddata . $coursetext_rcd;
            $cust->save();
        }
        // --
    }

    public function save_form_clinic_measure($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_CLINIC_MEASURE);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data = $data_block;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_CLINIC_MEASURE;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_CLINIC_MEASURE];
        $items_config = Client::getClientconfig($clientid, 'block_clinic_measure');
        $coursetext = $this->get_clinic_measure_coursetext($insert_data, $items_config);
        // create an extra item for the patient-course
        if ($coursetext && $is_extra_patient_course) {

            //generate the PatientCourse-Entry
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockKeyValue::PATIENT_COURSE_TAB_CLINIC_MEASURE);
            $cust->course_type = Pms_CommonData::aesEncrypt("K");
            $cust->recorddata = $coursetext;
            $cust->user_id = $logininfo->userid;
            $cust->save();
        }
    }

    public function save_form_clinic_diagnosis($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //first save the diagnosis
        //$af = new Application_Form_PatientDiagnosis();
        //$af->save_form_diagnosis($ipid, $data_block['clinic_diagnosis']);
        //print_r($data_block['clinic_diagnosis']);

        //the read the diagnosis from db => we need the IDs
        $entity = new PatientDiagnosis();
        $saved = $entity->getAllDiagnosis($ipid);
        $saved_PatientDiagnosis =  ! empty($saved[$ipid]) ? $saved[$ipid] : array();

        //add the diagnosis
        $insert_data['clinic_diagnosis'] = $saved_PatientDiagnosis;

        //add the diagosis_types
        $insert_data['diagnosis_types'] = $data_block['diagnosis_types'];

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_DIAGNOSIS_CLINIC);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_DIAGNOSIS_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

    }

    /**
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Session_Exception
     */
    public function save_form_actual_problems($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_SHIFT);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['actual_problems'] = $data_block['actual_problems'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_ACTUALPROBLEMS;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

    }

    
    /**
     * ISPC-2697, elena, 05.11.2020 !!!
     *
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Exception
     */
    public function save_form_ventilation($ipid = null, $data_post, $data_block){
        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }
        
        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_BEATMUNG);
        
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        $insert_data['beatmung'] = $data_block['beatmung'];
        
        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
		//ISPC-2904,Elena,30.04.2021
		$cust->contact_form_id = 0;
		//ISPC-2904,Elena,30.04.2021
		$edit_form_id = 0;
		if(isset($data_block['beatmung']['id'])){
			$cust->id = $data_block['beatmung']['id'];
			//ISPC-2904,Elena,30.04.2021
			$edit_form_id = $cust->id;
			$cust =  Doctrine::getTable('FormBlockKeyValue')->find($cust->id);
		}
		if(isset($data_post['__formular']['contact_form_id'])){
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
		}
		//ISPC-2904,Elena,30.04.2021
		if(intval($insert_data['beatmung']['form']) == 'oxygen'){
			$end_date = $insert_data['beatmung']['oxygen_date_from'];
			$end_time = $insert_data['beatmung']['oxygen_time_from'];
			$mbkv = new FormBlockKeyValue();
			$mbkv->cancelLastOxygenEvents($ipid, $end_date, $end_time, $edit_form_id);
		}

        $cust->block = self::BLOCK_BEATMUNG;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
		//ISPC-2904,Elena,30.04.2021
		if($cust->id > 0){
			$cust->replace();
		}else{
        	$cust->save();
		}
        
        
    }
    
	/**
	 *
	 * ISPC-2577, elena, 04.09.2020
	 *
	 * @param null $ipid
	 * @param $data_post
	 * @param $data_block
	 * @throws Exception
	 */
    public function save_form_dynamic($ipid = null, $data_post, $data_block)
    {


    	if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

		$blockname = $data_block['blockname'];

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], $blockname);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data[$blockname] = $data_block[$blockname];


        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = $blockname;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
		//TODO-3656,elena,03.12.2020
        $blocktext  = $insert_data[$blockname]['text'];


        $blocktext_not_empty = strlen(trim($blocktext)) > 0;

        if($blocktext_not_empty){
        	$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt($insert_data[$blockname]['shortcut']);
			$cust->course_title = Pms_CommonData::aesEncrypt($insert_data[$blockname]['text']);
			$cust->user_id = $logininfo->userid;
			$cust->save();

    	}


    }


	/**
	 * ISPC-2698, elena, 22.12.2020
	 * @param null $ipid
	 * @param $data_post
	 * @param $data_block
	 * @throws Exception
	 */
    public function save_client_options_form($ipid = null, $data_post, $data_block){

		if (empty($ipid) || empty($data_post) || empty($data_block)) {
			return;
		}

		$blockname = $data_block['blockname'];

		//set the old block values as isdelete
		$this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], $blockname);


		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$insert_data[$blockname] = $data_block[$blockname];


		$cust = new FormBlockKeyValue();
		$cust->ipid = $ipid;
		$cust->contact_form_id = $data_post['__formular']['contact_form_id'];
		$cust->block = $blockname;
		$cust->k = 'returnvalue';
		$cust->v = json_encode($insert_data);
		$cust->save();

		$blocktext  = $insert_data[$blockname]['option'];
		$shortcut = $insert_data[$blockname]['shortcut'];


		$blocktext_not_empty = !(empty($blocktext));

		if($blocktext_not_empty && strlen($shortcut) > 0){
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
			$shortcut_text = $data_block['headline'] . ': ' . implode(', ', $blocktext);
			$cust->course_title = Pms_CommonData::aesEncrypt($shortcut_text);
			$cust->user_id = $logininfo->userid;
			$cust->save();
		}



	}


    /**
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Session_Exception
     */
    public function save_form_report_recipient($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_REPORTRECIPIENT);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['report_recipient'] = $data_block['report_recipient'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_REPORTRECIPIENT;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

    }

    /**
     * IM-137, Contact form block Documentation, save
     *
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Exception
     */
    public function save_form_documentation($ipid = null, $data_post, $data_block){
        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }


        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_DOCUMENTATION);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['documentation'] = $data_block['documentation'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_DOCUMENTATION;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        $block = 'documentation';
        if( strlen($insert_data['documentation']) > 0 && ! empty($data_post['__formular']['blocks2recorddata']) && array_key_exists($block,$data_post['__formular']['blocks2recorddata']) && $data_post['__formular']['blocks2recorddata'][$block]['allow'] == '1'){
            
            $record_color = (!empty($data_post['__formular']['blocks2recorddata'][$block]['color'])) ? $data_post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
            $coursetext = '<br/><div class="rcd_'.self::BLOCK_DOCUMENTATION.' pc_record_data" style="color:'.$record_color.'!important">';
            $coursetext .= "<b>DOCUMENTATION:</b> ".$insert_data['documentation'];
            $coursetext .= '</div>';
    
            $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data_post['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
            $cust->recorddata=$cust->recorddata . $coursetext;
            $cust->save();
        }
        // --
    }


    /**
     * ISPC-2628 Fileupload
     *
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Exception
     */
    public function save_form_fileupload($ipid = null, $data_post, $data_block){

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_FILEUPLOAD);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['fileupload'] = $data_block;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_FILEUPLOAD;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();


    }


    /**
     * ISPC-2631 Körperliche Untersuchung II, elena, 28.07.2020
     * // Maria:: Migration CISPC to ISPC 08.08.2020	
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Exception
     */
    public function save_form_lmu_pmba2($ipid = null, $data_post, $data_block){

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_LMU_PMBA2);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['lmu_pmba2'] = $data_block;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_LMU_PMBA2;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();


    }


/**
	 * saves contactformblock talkback
	 *
	 * @param null $ipid
	 * @param $data_post
	 * @param $data_block
	 * @throws Exception
	 *
	 * //ISPC-2868,Elena,18.03.2021
	 *
	 */
    public function save_form_talkback($ipid = null, $data_post, $data_block){

		if (empty($ipid) || empty($data_post) || empty($data_block)) {
			return;
		}

		//set the old block values as isdelete
		$this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_LMU_PMBA2);


		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$insert_data['talkback'] = $data_block;

		$cust = new FormBlockKeyValue();
		$cust->ipid = $ipid;
		$cust->contact_form_id = $data_post['__formular']['contact_form_id'];
		$cust->block = 'talkback';
		$cust->k = 'returnvalue';
		$cust->v = json_encode($insert_data);
		$cust->save();

		$block = 'talkback';
		//writes in patientcourse, if permitted, with the color that is chosen in settings for this block in this contactform
        if( ! empty($data_post['__formular']['blocks2recorddata'])  && isset($block,$data_post['__formular']['blocks2recorddata']['talkback']) && ($data_post['__formular']['blocks2recorddata'][$block]['allow'] == '1')) {

            if (!empty($insert_data['talkback'])) {

                $contact_pers_str = "";
                $contact_pers_str = "<br/> Rücksprache mit: " . $insert_data['talkback']['optionvalue'];
                $contact_text = "<br/> Memo: " . $insert_data['talkback']['freetext'];
            }

            if (!empty($insert_data['talkback']['optionvalue']) && (strlen($insert_data['talkback']['freetext']) > 0)) {

                $record_color = (!empty($data_post['__formular']['blocks2recorddata'][$block]['color'])) ? $data_post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
                $coursetext_rcd = '<br/><div class="rcd_talkback pc_record_data" style="color:' . $record_color . ' !important;">';
                $coursetext_rcd .= "<b>Rücksprache:</b> " . $contact_pers_str . $contact_text;
                $coursetext_rcd .= '</div>';

                $cust = Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data_post['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
                $cust->recorddata = $cust->recorddata . $coursetext_rcd;
                $cust->save();
            }
        }

	}

    /**
     * ISPC-2599 Save Basisassessment Pflege
     *
     * @param null $ipid
     * @param $data_post
     * @param $data_block
     * @throws Doctrine_Connection_Exception
     * @throws Doctrine_Record_Exception
     * @throws Zend_Session_Exception
     */
    public function save_form_pflegeba($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_PFLEGEBA);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $insert_data['pflegeba'] = $data_block['pflegeba'];

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_PFLEGEBA;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

    }

    public function save_form_talkwith($ipid = null, $options = array())
    {

        if (empty($ipid) || empty($options)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_TALKWITH);


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $insert_data = $this->get_talkingwith_data($options, $clientid);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $options['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_TALKWITH;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
 

        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        $block = 'talkwith';
        if( ! empty($options['__formular']['blocks2recorddata'])  && array_key_exists($block,$options['__formular']['blocks2recorddata']) && $options['__formular']['blocks2recorddata'][$block]['allow'] == '1'){
            
            if(!empty($insert_data))
            {
                $contact_pers = array();
                foreach($insert_data['item'] as $k => $item_data)
                {
                    $contact_pers[] =$item_data['value'];
                }
                $contact_pers_str = "";
                $contact_pers_str = "<br/> Kontakt mit :".implode(", ",$contact_pers);
                $contact_text =  "<br/> weitere Angaben:". $insert_data['TALKFREETXT'];
            }
            
            if(!empty($contact_pers) && strlen($insert_data['TALKFREETXT']) > 0  ){

                $record_color = (!empty($options['__formular']['blocks2recorddata'][$block]['color'])) ? $options['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
                $coursetext_rcd = '<br/><div class="rcd_'.self::BLOCK_TALKWITH.' pc_record_data" style="color:'.$record_color.'!important">';
                $coursetext_rcd .= "<b>".$this->translate('block_'.$block).":</b> ".$contact_pers_str.$contact_text;
                $coursetext_rcd .= '</div>';

                $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($options['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
                $cust->recorddata=$cust->recorddata . $coursetext_rcd;
                $cust->save();
            }
        }
        // --
        
    }

    public function save_form_palliativ_support($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block) ) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_PALLIATIV_SUPPORT);

        $insert_data = array();
        $insert_data['item'] = $data_block['item'];

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_PALLIATIV_SUPPORT;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();
    }

    public function save_form_palliativ_assessment($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block) ) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_PALLIATIV_ASSESSMENT);

        $insert_data = array();
        $insert_data['item'] = $data_block['item'];

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_PALLIATIV_ASSESSMENT;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($insert_data);
        $cust->save();

        // create an extra item for the patient-course with the given token, if the contactformular is saved for the first time
        if ($data_post['__formular']['old_contact_form_id'] == '') {

            $is_extra_patient_course = Client::getClientconfig($clientid, 'extra_patient_course_clinic')[self::BLOCK_PALLIATIV_ASSESSMENT];
            $coursetext = $this->get_palliativassessment_coursetext($insert_data);
            if ($coursetext && $is_extra_patient_course) {
                $title = FormBlockKeyValue::PATIENT_COURSE_TITLE_PALLIATIV_ASSESSMENT_CREATE;
                //generate the PatientCourse-Entry
                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockKeyValue::PATIENT_COURSE_TAB_PALLIATIV_ASSESSMENT);
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt($title);
                $cust->recorddata = $coursetext;
                $cust->user_id = $logininfo->userid;
                $cust->save();
            }
        }

    }

    public function save_form_medicationclinic($ipid = null, $data_post, $data_block)
    {

        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }

        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_MEDICATION_CLINIC);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_MEDICATION_CLINIC;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($data_block);
        $cust->save();

    }

    public function save_form_coordinationtime($ipid = null, $data_post, $data_block){
        if (empty($ipid) || empty($data_post) || empty($data_block)) {
            return;
        }
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;


        //set the old block values as isdelete
        $this->clear_block_data($ipid, $data_post['__formular']['old_contact_form_id'], self::BLOCK_COORDINATIONTIME);

        $cust = new FormBlockKeyValue();
        $cust->ipid = $ipid;
        $cust->contact_form_id = $data_post['__formular']['contact_form_id'];
        $cust->block = self::BLOCK_COORDINATIONTIME;
        $cust->k = 'returnvalue';
        $cust->v = json_encode($data_block);
        $cust->save();
        $p_cust = new PatientCourse();
        $p_cust->ipid = $ipid;
        $p_cust->course_date = date("Y-m-d H:i:s", time());
        $p_cust->tabname = Pms_CommonData::aesEncrypt(self::BLOCK_COORDINATIONTIME);
        $p_cust->course_type = Pms_CommonData::aesEncrypt("K");
        $p_cust->course_title = Pms_CommonData::aesEncrypt("Koordinationszeit");
        $p_cust->recorddata = "Koordinationszeit beträgt " . $data_block['coordinationtime'] . "Min.";
        $p_cust->user_id = $logininfo->userid;
        $p_cust->save();
        
        
        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        $block = 'coordinationtime';
        if( ! empty($data_post['__formular']['blocks2recorddata'])  && array_key_exists($block,$data_post['__formular']['blocks2recorddata']) && $data_post['__formular']['blocks2recorddata'][$block]['allow'] == '1'){
            $coursetext = "Koordinationszeit beträgt " . $data_block['coordinationtime'] . "Min.";;
            $record_color = (!empty($data_post['__formular']['blocks2recorddata'][$block]['color'])) ? $data_post['__formular']['blocks2recorddata'][$block]['color'] : "#000000";
            
            $coursetext_rcd = '<br/><div class="rcd_'.self::BLOCK_COORDINATIONTIME.' pc_record_data" style="color:'.$record_color.'!important">';
            $coursetext_rcd .= "<b>".$this->translate('block_'.$block).":</b> ".$coursetext;
            $coursetext_rcd .= '</div>';
            
            
            $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data_post['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
            $cust->recorddata=$cust->recorddata . $coursetext_rcd;
            $cust->save();
        }
        // --
        

    }

    public function getLastBlockValues($ipid, $blockname)
    {
        $returnarray = array();
        $q1 = Doctrine_Query::create()
            ->select("c.id, c.ipid, c.contact_form_id")
            ->from("FormBlockKeyValue c")
            ->Where("c.ipid=?", $ipid)
            ->andWhere('c.block=?', $blockname)
            ->andWhere('c.isdelete = 0')
            ->groupBy('c.contact_form_id')
            ->orderBy('c.contact_form_id DESC')
            ->limit(1);
        $a1 = $q1->fetchArray();
        if (count($a1 > 0)) {
            $q2 = Doctrine_Query::create()
                ->select("*")
                ->from("FormBlockKeyValue c")
                ->Where("c.ipid=?", $ipid)
                ->andWhere('c.block=?', $blockname)
                ->andWhere('c.contact_form_id=?', $a1[0]['contact_form_id']);
            $groupsarray = $q2->fetchArray();

            if ($groupsarray) {
                $cfid = $groupsarray[0]['contact_form_id'];

                foreach ($groupsarray as $elem) {
                    if ($elem['contact_form_id'] == $cfid) {
                        if (isset($returnarray[$elem['k']])) {
                            if (is_array($returnarray[$elem['k']])) {
                                $returnarray[$elem['k']][] = $elem['v'];
                            } else {
                                $returnarray[$elem['k']] = array($returnarray[$elem['k']], $elem['v']);
                            }
                        } else {
                            $returnarray[$elem['k']] = $elem['v'];
                        }
                    }
                }


            }

        }


        return $returnarray;
    }



    private function create_subform($classname)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->removeDecorator('Fieldset');
        $subform->removeDecorator('HtmlTag');
        // $subform->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => $classname));

        return $subform;
    }

    private function add_javascript_treatmentplan()
    {
        $new_line = "'\\n'";

        echo "<script>      

         $(document).ready(function () {   
             
            var dialog_goalselectbox = $('<div id=\"dialog_goalselectbox\"> <style>#dialog_goalselectbox li{border:1px solid #aed0ea;background-color:#deedf7; padding:4px;margin-bottom:4px;border-radius:3px;}#dialog_goalselectbox li:hover{border:1px solid #c0cba8;background-color:#ccefaa;}</style><ul><li></li></ul><div/>').appendTo('body');      
            //$('body').append('<div id=\"dialog_goalselectbox\" ><ul ><li></li></ul ><div/>');
            var dialog_planselectbox = $('<div id=\"dialog_planselectbox\"> <style>#dialog_planselectbox li{border:1px solid #aed0ea;background-color:#deedf7; padding:4px;margin-bottom:4px;border-radius:3px;}#dialog_planselectbox li:hover{border:1px solid #c0cba8;background-color:#ccefaa;}</style><ul><li></li></ul><div/>').appendTo('body');      
            //$('body').append('<div id=\"dialog_planselectbox\" ><ul ><li></li></ul ><div/>');
                 
            dialog_goalselectbox.dialog({
                autoOpen: false,
                title: $('#label_goal').text(),
                resizable: false,
                width:300,
                height:300,
                });
            
            dialog_planselectbox.dialog({
                autoOpen: false,
                title: $('#label_plan').text(),
                resizable: false,
                width:300,
                height:300,
                });
           
             
            $('#block-block_treatment_plan_clinic input.datepicker').datepicker({
             			dateFormat: 'dd.mm.yy',
             			changeMonth: true,
		                changeYear: true,
		                nextText: '',
		                prevText: ''
	            });         
       
           
        $('#btn_treatmentplan_clinic').on('click',function(event){
		    event.preventDefault();		
		    
		    var data_array = [];
		    data_array.encid = $('#encid').val();
		    data_array.agree_with = $('#agree_with').val();
		    data_array.talk_supply_planning = $('#talk_supply_planning').is(':checked') ? '1' : '0';
		    data_array.date = $('#treatment_plan_date').val();
		    data_array.theme_key = $('theme_key').val();	
		    data_array.goals = [];
		    data_array.plans = [];	  
		  
		    var goals = $('.txtGoal');
		    for(var i = 0; i < goals.length; i++){
                var goal = goals[i];
                data_array.goals[i] = {id: goal.id, val: $(goal).val() };
            }
		    
		    var plans = $('.txtPlan');
		    for(var i = 0; i < plans.length; i++){
                var plan = plans[i];
                data_array.plans[i] = {id: plan.id, val: $(plan).val() };                
            }
		    
        alert($('#treatment_plan_success').val());
		
		$.ajax({
		  	url:'ajax/createformextratreatmentplanclinic',
		    type: 'GET',
			data:{				
				'encid' : data_array.encid,
		  	    'agree_with' : data_array.agree_with,
				'talk_supply_planning' : data_array.talk_supply_planning,
				'date' : data_array.date,
				'goals' :  data_array.goals,
				'plans' :  data_array.plans
			}
		  });
	        
        });
        
        $('.btnLookup').on('click',function(event){
              var clicked_button = this;
              var list_goalsandplans=JSON.parse($('#list_goalsandplans').val());
              var selectlist=new Array();
              var prof_category = $(this).closest('.theme_row').find('.theme_key').val();
              var textfield = $(clicked_button).closest('.theme_row').find('.txtGoal');
              var oldtext;
              event.preventDefault();
              dialog_goalselectbox.dialog('open');
              $('#dialog_goalselectbox ul').empty();
              for(var propertyName in list_goalsandplans) {
                 //if(list_goalsandplans[propertyName]['category']==prof_category && !oldtext.includes(propertyName)){
                 if(list_goalsandplans[propertyName]['category']==prof_category){
                   
                    selectlist.push(propertyName);
                    var newel_goal=$(\"<li data-toggle='on'>\" +propertyName+ \"</li>\");                    
                                   
                    newel_goal.click(function(){
                        oldtext = textfield.val();

                       var newtext = $(this).text();
                       if (oldtext.length > 0 && !(oldtext[oldtext.length - 1] == $new_line)) {
                        oldtext = oldtext + $new_line;
                       } 
                       textfield.val(oldtext+newtext);
                       $(this).hide('slow');
                       dialog_planselectbox.dialog('open');
                        $('#dialog_planselectbox ul').empty();
                        for (var plan in list_goalsandplans[newtext]['plan']) {
                            if(list_goalsandplans[newtext]['category']==prof_category) {
                                var text = list_goalsandplans[newtext]['plan'][plan];
                                var newel_plan = $(\"<li data-toggle='on'>\" + text + \"</li>\");
                                $('#dialog_planselectbox ul').append(newel_plan);
                                dialog_entry_click(newel_plan, clicked_button, '.txtPlan');                                
                            }
                        }
                });  
                    $('#dialog_goalselectbox ul').append(newel_goal);     
                    
                }
              }       
        });
        
        });
                    
         function dialog_entry_click(list_entry, clicked_button, button_class){
              list_entry.click(function () {                                   
                                
                  var newtext = $(this).text();
                  var textfield = $(clicked_button).closest('.theme_row').find(button_class);
                  var oldtext =textfield.val();
                  if (oldtext.length > 0 && !(oldtext[oldtext.length - 1] == $new_line)) {
                    oldtext = oldtext + $new_line;
                   }
                   if($(this).data('toggle') == 'on' ) {                      
                       textfield.val(oldtext+newtext);
                       $(this).css('background-color', 'grey');
                       $(this).data('toggle', 'off');
                    }
                    else{                      
                       oldtext = oldtext.replace(newtext+$new_line, '');
                       textfield.val(oldtext);
                       $(this).css('background-color', '#deedf7');                                       
                       $(this).data('toggle', 'on');
                   }
            });
             
         }
	
	
        </script>";
    }

    private function add_javascript_careprocessclinic()
    {

        echo "<script> 

        $(document).ready(function () {
            
            $(this).find('.is_to_hide').hide();
            init_problem();
            
            $('form').submit(function (event) {
                            
                $('.check_problem').each(function () {                                     
                    this.checked = isProblemSelected(this);
                 });
                return true;
            });
             
         });        
                        
        $(document).on('change', '.check_problem', function () {
            if (this.checked == false) { 
                $(this).parents('.contact_careprocessclinic').find('.is_to_hide').hide();
            } else { 
                $(this).parents('.contact_careprocessclinic').find('.is_to_hide').show();
            };
        })
        
        $(document).on('change', '.section_problem_item, .section_resource_item, .section_target_item, .section_activity_item', function () {
            
            var header = $(this).parents('.contact_careprocessclinic').find('.section_problem_header');
            var check_problem = $(this).parents('.contact_careprocessclinic').find('.check_problem');
            if (isProblemSelected(check_problem) == true) { 
                header.addClass('selected');
            } else { 
                 header.removeClass('selected');
            };
        })
        
        function isProblemSelected(checkbox){
            var isChecked = false;
            $(checkbox).parents('.contact_careprocessclinic').find('.section_problem_item, .section_resource_item, .section_target_item').each(function (){
                var chbox = $(this).find('input');
                if(chbox.attr('checked')){
                   isChecked = true;
                 }
             });  
                
             $(checkbox).parents('.contact_careprocessclinic').find('.section_activity_item').each(function (){                      
                 if($(this).find('select option:selected').text() != ''){
                     isChecked = true;
                  }
             });
                     
              return isChecked;
        }
        
        function init_problem(){
         
            $('.check_problem').each(function () {
                this.checked = false;               
                var header = $(this).parents('.contact_careprocessclinic').find('.section_problem_header');
                if (isProblemSelected(this) == true) { 
                    header.addClass('selected');
                } else { 
                    header.removeClass('selected');
                };
             });
        }
        
        
        
         
        </script>";
    }

    private function add_javascript_dischargeplanningclinic()
    {

        echo "<script> 

        $(document).ready(function () {
            
             $('#FormBlockDischargePlanningClinic-fbkvdisdate').datepicker({
                dateFormat: 'dd.mm.yy',
                showOn: 'focus', 
            });
                
         });    
         
        </script>";
    }

    private function add_javascript_talkwith()
    {
        echo "<script> 

        $(document).ready(function () {

            re_label();
         });

        $(document).on('change', '.talkwithitem', function () {

        var last = $(this).closest('.talkwithitemrow');

        //only clone the row,if the last input-field is changed
        if ($(last).index() == $('.talkwithitemrow').size() - 1) {


            if ($(last).find('.talkwithitem').val() != '') {

                var newel = last.clone();  //clone the last row
                $(newel).find('.talkwithitem').val(''); //delete value of new row
                $(newel).find('.lbl_talkwith').text(''); //delete label of new row

                last.after(newel);

                //numerate the entries
                renumerate('.talkwithitemrow');

                newel.find('.talkwithitem').focus();

            }
        }
        
        //remove the row,if not the last input is changed and the value is ''
        if ($(last).index() != $('.talkwithitemrow').size() - 1) {


            if ($(last).find('.talkwithitem').val() == 'NOSELECT') {

                last.remove()

                //numerate the entries
                renumerate('.talkwithitemrow');
                
                //set the label of first row
                re_label();
                
            }
        }

    })
    
        function renumerate(aClass)    {
        //numerate the entries
        var rowcount = 0;
        $(aClass).each(function () {
            $(this).find('input, textarea, select').each(function () {
                var name = $(this).attr('name');
                name = name.replace(/\[[\d+]\]/, \"[\" + rowcount + \"]\");
                $(this).attr('name', name);
            });
            rowcount++;
        });
    }        
    
        function re_label(){
            $('.lbl_talkwith').text('');
            $('.lbl_talkwith:first').text(translate('talkwith_contact'));
           // $('.lbl_talkwith:not(:first)').text('');
            
        }
        </script>";

    }

    private function add_javascript_clinic_shift()
    {
        echo "<script> 

        $(document).ready(function () {
            
           show_hide_shiftextra();        
            
         });
        
        $(document).on('change', '.rb_shifttype', function () {
            show_hide_shiftextra();
        });
        
         
        function show_hide_shiftextra(){
           
            var s=$('.rb_shifttype:checked').val();
             if(s=='N'){
                $('#shiftextra').show();
             }else{
                $('#shiftextra').hide();
                }            
         }    
        
        </script>";

    }

    private function add_javascript_palliative_assessment()
    {
        echo "<script> 

        $(document).on('change', '.palassitem', function () {
       
          var last = $(this).closest('.palassrow');        

        //only clone the row,if the last input-field is changed
        //first palassrow has index 1, because it's the 2rd element in the group if tbody
        if ($(last).index() == $('.palassrow').size()) {


            if ($(last).find('.palassitem').val() != '') {

                var newel = last.clone();  //clone the last row
                $(newel).find('.palassitem').val(''); //delete value of new row

                last.after(newel);

                //numerate the entries
                renumerate('.palassrow');

                newel.find('.palassitem').focus();

            }
       }
        
        //remove the row,if not the last input is changed and the value is ''
        if ($(last).index() != $('.palassrow').size()) {


            if ($(last).find('.palassitem').val() == '') {

                last.remove()

                //numerate the entries
                renumerate('.palassrow');
            }
        }

    })
    
        function renumerate(aClass)    {
        //numerate the entries
        var rowcount = 0;
        $(aClass).each(function () {
            $(this).find('input, textarea, select').each(function () {
                var name = $(this).attr('name');
                name = name.replace(/\[[\d+]\]/, \"[\" + rowcount + \"]\");
                $(this).attr('name', name);
            });
            rowcount++;
        });
    }        
    
        </script>";

    }

    /**
     * JS for report recipient
     * set address as main if button 'als Empfänger' clicked
     */
    private function add_javascript_report_recipient()
    {
        echo "<script> 

        $(document).ready(function () {
            $('.btn_as_recipient').on('click', function(e){
                var button_id = (e.target.id);
                console.log(button_id);
                var textfield_id = '#' + button_id.replace('button', 'address');
                console.log(textfield_id);
                var textfield = $(textfield_id);
                console.log(textfield);
                var textfield_value = textfield.val();
                console.log(textfield_value);
                console.log($('#report_recipient_mainaddress'));
                textfield_value = textfield_value.replace(/;/g, '\\n');
                
                $('#report_recipient_mainaddress').val(textfield_value);
               
            
            })
        });
            
 
        
        </script>";

    }


    private function add_javascript_clinic_diagnosis()
    {
        $clinic_measure_diagnosis_send_to_form = $this->translate('clinic_measure_diagnosis_send_to_form');
        $clinic_measure_diagnosis_save_and_send_to_form = $this->translate('clinic_measure_diagnosis_save_and_send_to_form');
        $alert_if_diagnosis_type_not_filled = $this->translate('diagnose_cat_fill');

        echo "<script> 
        
        $(document).ready(function () {   
            
            PatientDiagnosis_addnew_clinic = function(elmId){
                var _target = $(elmId);
                //var _target = $('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis');
                var parent_form = 'FormBlockDiagnosisClinic[clinic_diagnosis]';
                console.log(elmId)
	           
	            $.get(appbase + 'ajax/createformdiagnosisrow?parent_form='+parent_form, function(result) {
	                //console.log(result);
		            var newFieldset =  $(result).insertBefore($(_target).parents('tr'));		            
		            if(elmId === '#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis_popup'){
		                console.log('add new in popup');
		                $('.diagnosis_container fieldset .livesearchFormEvents').livesearchDiacgnosisIcd({livesearch_id : 'livesearch_admission_diagnosis_popup'});
		            } else if (elmId === '#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis'){
		                $('.livesearchFormEvents').livesearchDiacgnosisIcd();
		            }
		
	                });
                
            }
            
            isDiagnosysTypeChosen = function(){
                    var diagnosisCorrectFilled = true;
					$('#diagnosis_form_dialog #fieldset-clinic_diagnosis table tr.icd_holder_row').each(function(){
						countChecked = 0;
						textfieldsFilled = false;
						$(this).find('input:text').each(function(){
							if($(this).val() !== ''){
								textfieldsFilled = true;
							}
						})
						if(textfieldsFilled){
							$(this).find('input:radio:checked').each(function(){
								console.log($(this).val());
								countChecked++;

							})
							//console.log(countChecked);
							if(diagnosisCorrectFilled && countChecked == 0){
								
								diagnosisCorrectFilled = false;
								
							}

						}

					});
					
					return diagnosisCorrectFilled;
                
            }

           
             $('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis').prop('onclick', null); 
             $('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis').live('click', function(e){
                 e.preventDefault();
                 PatientDiagnosis_addnew_clinic('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis')
             });  
             
             
             
             loadActualDiagnosisInFormBlock = function(){
                 $.ajax({                                
                          url: appbase + 'patientnew/clinicdiagnosisreport?id=' + $('#diagnosis_status-encid').val() + '&clinic=1',
                         // dataType: 'html',                          
                          success: function( data ){
                             var newFieldContent = ''; 
                             var html = $.parseHTML( data );
                            
                             $.each( html, function( i, el1 ) {
                               var elId = ($(el1).attr('id'));
                              
                               
                               if(elId == 'fieldset-clinic_diagnosis'){
                                   //console.log('found', el1);
                                   
                                   newFieldContent = $(el1).html();
                               }
                               });
                         
                          //console.log('newFieldContent', newFieldContent)   ;
                          
                           $('#fieldset-clinic_diagnosis').html(newFieldContent).promise().done(function(){
                               console.log('subformdata2 ready');                            
                               
                              $('#fieldset-clinic_diagnosis .livesearchFormEvents').livesearchDiacgnosisIcd();
                           });
                            
                          }
                        });
             }
             

	    $('#fb_clinic_diagnosis_status_header').hide(); 
        $('.diagnosis_container').hide();
        
        $('#diagnosis_edit-diagnosis_button').live('click', function(){
            //console.log('click');
            $('#livesearch_admission_diagnosis_popup').remove();
            $.ajax({ 
                         url: appbase + 'patientnew/clinicdiagnosisreport?id=' + $('#diagnosis_status-encid').val() + '&clinic=1',
                         // dataType: 'html',                          
                          success: function( data ){
                             var newFieldContent = '';
                             data = data.replace('FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis', 'FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis_popup');
                             
                             data = '<form id=\"diagnosis_form_dialog\">' + data + '</form>';
                             var html = $.parseHTML( data );
                             //console.log(html);
                             
                             $('.diagnosis_container').html(html).promise().done(function(){
                                 console.log('filled');
                                 $('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis_popup').prop('onclick', null);
                                 
                                  $('.diagnosis_container fieldset .livesearchFormEvents').livesearchDiacgnosisIcd({livesearch_id : 'livesearch_admission_diagnosis_popup'});
                                  $('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis_popup').live('click', function(e){
                                     e.preventDefault();
                                     PatientDiagnosis_addnew_clinic('#FormBlockDiagnosisClinic-clinic_diagnosis-addnew_diagnosis_popup')
                                 });  
                                                      
                                                      
                                                      
                             });
                             var formChanged = false;
                             var buttonText = '".  $clinic_measure_diagnosis_send_to_form ."';
                             
                             var fieldset = $('.diagnosis_container fieldset');
                                fieldset.live('change', function() {
                                   // console.log('fieldset changed');
                                   formChanged = true;
                                   buttonText =  '".  $clinic_measure_diagnosis_save_and_send_to_form ."';
                                   $('#diagnosis_container_button span').html(buttonText);
                                });
                             
                             $('.diagnosis_container').dialog({
                             modal: true,
                             height: 'auto',
                             width: 'auto',
      
                              buttons: [
                                  {
                                     id : 'diagnosis_container_button',
                                     text : buttonText,
                                     click : function() {
                                         var url = '" . APP_BASE . "ajax/savediagnosis';
			                            console.log('ins formular');
			                            var reqFormData = $('#diagnosis_form_dialog').serialize();
			                            //add id
			                            reqFormData =  reqFormData + '&id=" . $_REQUEST['id'] . "';
			                            diagnosisTypesFilled = isDiagnosysTypeChosen();
			                            //console.log('diagnosis type filled', diagnosisTypesFilled );
			                            
			                            //console.log('data', reqFormData);
			                            if(formChanged && diagnosisTypesFilled ){
			                                xhr = $.ajax({
                                            url: url,
                                            type: 'POST',
                                            //dataType: 'json',
                                            
                                            data: reqFormData,
                                            
                                            success: function(response) {
                                                console.log(response);
                                                loadActualDiagnosisInFormBlock();
                                            },
                                            error: function(err)
                                            {
                                                console.log(err);
                                            }
                                        });
			                            }else if(!formChanged && diagnosisTypesFilled){
			                                console.log('form not changed');
			                                loadActualDiagnosisInFormBlock();
			                            }else{
			                                jAlert('" . $alert_if_diagnosis_type_not_filled . "');
			                            }

                                        
                                                                     
                                  if( diagnosisTypesFilled  ){
                                      $( this ).dialog( 'close' );
                                  }                           
                                  
                                }
                                }
                              ]
                                
                              
                             });
 
                         
                        }
               });  
        
       }); 
             
            $('.livesearchFormEvents').livesearchDiacgnosisIcd();
        
        })
       
        </script>";

    }

    private function add_javascript_coordinationtime(){
        $new_line = "'\\n'";

        echo "<script>      

         $(document).ready(function () {   
             function isCoordinationstimeNumeric(value) {
                    if (value != null && !value.toString().match(/^[0-9\.,]*$/)) return false;
                    return true;
                }
               
                
                $('#coordinationtime').live('blur', function(){
                    
                    if(!isCoordinationstimeNumeric($('#coordinationtime').val())){
                        //console.log('not numeric');
                        var val = parseInt($('#coordinationtime').val());
                        console.log(val);
                        if(!val){
                           
                            val = 0;
                        }
                        $('#coordinationtime').val(val);
                       
                        
                    }
                })
                
                
                
                
          });
         </script>";


    }

    // IM-25 Versorger
    public function create_simple_template_form($blockname, $htmltemplate, $f_values = array(), $options=array()){
        $pdf=false;
        if($options['formular_type']=="pdf"){
            $pdf=true;
        }
        //print_r($options);
        $newview = new Zend_View();
        $newview->pdf=$pdf;
        $newview->blockname=$blockname;
        $newview->f_values=$f_values;
        $newview->options=$options;
        $newview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
        $html = $newview->render($htmltemplate);

        //$options=array();
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators();
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $subform->setDecorators(array('FormElements' , array('SimpleTemplate', $options), ) );
        $subform->setElementDecorators(array(
            'ViewHelper',
            array('Errors'),
        ));
        $subform->addDecorator('SimpleContactformBlock', $options);
        $subform->setLegend('block_'.$blockname);

        $subform->addElement('note', 'block_'.$blockname, array(
            'value'         => $html,
            'decorators'    => array(
                                'SimpleTemplate',
                            ),
        ));
        return $subform;

    }

    public function create_simple_auto_add_block($blockconfig, $data = array())
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $pdf = false;
        if ($blockconfig['formular_type'] == "pdf") {
            $pdf = true;
        }
        $newview = new Zend_View();
        $newview->pdf = $pdf;

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }
        // necessary for Baseassesment Pflege, does nothing with another form blocks
        $newview->blockconfig = $blockconfig;
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $html = $newview->render($blockconfig['template']);
        $options = array();
        $blockoptions = array();

        if(isset($data['class'])){
            $blockoptions['class'] = $data['class'];
        }
        if(isset($data['opened'])){
            $blockoptions['opened'] = $data['opened'];
        }
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators();
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $subform->setDecorators(array('FormElements', array('SimpleTemplate', $options),));
        $subform->setElementDecorators(array(
            'ViewHelper',
            array('Errors'),
        ));
        if($pdf){
            $subform->addDecorator('SimpleContactformBlockPdf');
        }else {
            $subform->addDecorator('SimpleContactformBlock', $blockoptions);
        }
        if(isset($blockconfig['blocktitle'])){
			$subform->setLegend($blockconfig['blocktitle']);
		}else{
        	$subform->setLegend($blockconfig['blockname']);
		}

        $subform->setAttrib('class', 'expanded');

        $subform->addElement('note', 'block_' . $blockconfig['blockname'], array(
            'value' => $html,
            'decorators' => array(
                'SimpleTemplate',
            ),
        ));
        return $subform;

    }

    private function get_contact_list_with_id($clientid){
        //get the contact-list and generate keys
        $contact = array();
        foreach (ClientConfig::getConfigOrDefault($clientid, 'configtalkwith') as $item) {
            $contact[substr(Pms_CommonData::str_safeascii($item, '', ''), 0, 10)] = $item;
        };

        return $contact;
    }

    private function get_talkingcontent_coursetext($stored_data)
    {

        $values = array();

        //sort the entries to the heading
        foreach ($stored_data as $value) {
            if ($value['is_headline'])
                continue;
            if (!$value['is_headline'] && $value['checkbox_val'] == '1')
                $values[] = $value['label'];
            if ($value['is_freetext'] && $value['freetext_val'] != '')
                $values[] = $value['freetext_val'];
        }

        if(count($values) == 0)
            return false;

        $coursetext="<b>Inhalte:</b> ".implode(', ', $values)."<br>";

        return $coursetext;

    }

    private function get_clinicsoap_coursetext($stored_data)
    {

        $coursetext = false;
        $values = 0;

        foreach ($stored_data['item'] as $key => $value) {
            if(trim($value) == ''){
                continue;
            }
            $coursetext .= "<b>" . $this->translate('clinic_soap_' . $key) . ": </b>" . str_replace("\n", "<br>", $value) . "<br>";
        }
       // if ($values == 0)
         //   return false;

        return $coursetext;

    }

    private function get_clinic_measure_coursetext($stored_data, $conf){

        $coursetext = false;

        foreach ($stored_data['measures'] as $val){

            if ($val['caption']=="Bitte Auswählen"){
                continue;
            }

            $coursetext .= "<b>".$val['caption']."</b>";

            if(is_array($val['subitem'])){
                $coursetext .= " (" . implode(", ", $val['subitem']) .")";
            }
            if(is_array($val['subtext'])){
                foreach ($val['subtext'] as $fk=>$field) {
                    $subfield = (isset($conf[$val['caption']]['subtextfield'][$fk])) ? $conf[$val['caption']]['subtextfield'][$fk] :'';
                    $coursetext .= "<br>" . $subfield . ": " . $field;
                }
            }
            $coursetext .= "<br>";
            if($val['freetext']){
                $coursetext .= htmlspecialchars($val['freetext']);
                $coursetext .= "<br>";
            }
        }

        return $coursetext;
    }

    private function get_palliativassessment_coursetext($insert_data){
        $coursetext ='';
        $course_empf= '';

        if(strlen($insert_data['item']['freetext'])>0) {
            $coursetext = "<b>Beurteilung:</b>" . str_replace("\n", "<br>",trim($insert_data['item']['freetext']))."<br>";
        }

        foreach($insert_data['item']['empfehlung'] as $empf){
            if(strlen($empf)>0){
                if(strlen($course_empf)>0){
                    $course_empf.="<br>";
                }
                $course_empf.=$empf;
            }
        }

        if(strlen($course_empf)>0){
            $coursetext.= "<b>Empfehlung:</b>" . $course_empf . "<br>";
        }
        return $coursetext;
    }

    private function get_clinic_measure_config()
    {
        $items_config = array(
            array('name'=>'Bitte Auswählen'),
            array('name'=>'Sonographie',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Abdomen', 'Niere', 'Blase', 'Pleura', 'Weichteile', 'Sonstiges')),
                )
            ),
            array('name'=>'Punktion',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('diagnostisch', 'therapeutisch', 'sonstige')),
                    array('name'=>'Ort', 'entries'=>array('Pleura', 'Aszites', 'Blase')),
                )
            ),
            array('name'=>'Transfusion',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Erythrozytenkonzentrat', 'Thrombozytenkonzentrat', 'Gerinnungsfaktoren')),
                ),
                'subtextfield'=>array('Anzahl')
            ),
            array('name'=>'Aufklärung für Externe Prozeduren/Intervention/Diagnostik'),
            array('name'=>'Externe Prozeduren/Intervention',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Port-Anlage', 'PEG-Anlage (Ernährung)', 'PEG-Anlage (Ablauf)', 'Pleuradrainage', 'Aszitesdrainage', 'Operation/Intervention (s.u.)')),
                )
            ),
            array('name'=>'Externe Diagnostik/Intervention',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Besondere Bildgebung (Sonographie/Röntgen/CT/MRT/PET', 'Endoskopie (Gastroskopie, ERCP, Coloskopie)', 'Kardiale Diagnostik/Intervention', 'Lungenfunktionstestung')),
                )
            ),
            array('name'=>'Leichenschau'),
            array('name'=>'Konsiliarische Vorstellung',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Psychiatrie', 'Dermatologie', 'HNO', 'Onkologie', 'Strahlentherapie')),
                )
            ),
        );

        return $items_config;
    }


    public static function get_simpleblocks_config(){
        $conf=array();
        $conf['bericht_fbe']=array(
            'blockname'=>"bericht_fbe",
            'template'=>'form_block_bericht_fbe.html',
        );
        $conf['anforderer']=array(
            'blockname'=>"anforderer",
            'template'=>'form_block_anforderer.html'
        );
        $conf['pcpss']=array(
            'blockname'=>"pcpss",
            'template'=>'form_block_pcpss.html',
        );
		$conf['visite_summary']=array(
			'blockname'=>"visite_summary",
			'template'=>'form_block_visite_summary.html',
			'extrapatientdata'=>function($ipid){//ISPC-2887,Elena,06.05.2021
				$fbkv=new FormBlockKeyValue();
				$data=$fbkv->getLastBlockValues($ipid, 'assessment_basis');
				return $data;
			}
		);

		//ISPC-2886 Nico 15.04.2021 Start
		$conf['assessment_basis']=array(
			'blockname'=>"assessment_basis",
			'template'=>'form_block_assessment_basis.html',
			'onsave'=>function($post_data_Arr, $blockname){
				$html=$post_data_Arr[$blockname]['freetext'];
				$html=trim($html);
				if(strlen($html)) {
					$html="<b>Assessment Basis:</b><br>" . Pms_CommonData::bb_to_html($html);
					ContactForms::add_recorddata($html, $blockname, $post_data_Arr['__formular']);
				}
			}
		);
		//ISPC-2886 Nico 15.04.2021 End
        return $conf;

    }
    
    //ISPC-2663 Carmen 02.09.2020
    public function create_form_talkwithsingleselection($options = array(), $ipid)
    {
    
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    
    	$decid = Pms_CommonData::getIdfromIpid($ipid);
    	$encid = Pms_Uuid::encrypt($decid);
    
    	// update an existing contactform => loaded old values by ContactFormId
    	if (isset($options['v'])) {
    		$stored_data = json_decode($options['v'], true);
    	} // use the post ones, maybe this is just a print
    	else if (isset($options['formular_type'])) {
    		$stored_data = $this->get_talkingwithsingleselection_data($options, $clientid);
    	}
    
    	if (!$stored_data) {
    		$stored_data['items'] = array();
    		$stored_data['TALKSINGLEFREETXT'] = '';
    	}
   
    	//create the pdf-Layout and return
    	if ($options['formular_type'] == 'pdf') {
    		return $this->create_pdf_talkwithsingleselection($stored_data);
    	}
    
    	//build the multi-Option
    	$contact = array('NOSELECT' => '') + $this->get_contact_list_withsingle_id($clientid);
    
    	$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    
    	$this->mapValidateFunction($__fnName, "create_form_isValid");
    	$this->mapSaveFunction($__fnName, "save_form_talkwithsingleselection");
    	//$this->add_javascript_talkwith();
    
    	$subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
    	//$subform->setAttrib("class", "datatable");
    	$subform->setLegend('talkwithsingleselection');
    	//$this->__setElementsBelongTo($subform, $elementsBelongTo);
    
    	$subform->addElement('hidden', 'block_talkwithsingleselection', array(
    			'value' => 'block_talkwithsingleselection',
    			'elementBelongsTo' => self::BLOCK_TALKWITHSINGLESELECTION,
    			'array_index' => 'noindex',
    			'readonly' => true,
    			'decorators' => array(
    					'ViewHelper',
    					'SimpleInput',
    					array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'))
    			),
    	));
    
    	$confkey = 0;
    	$subform->addElement('note', 'label_' . $confkey, array(
    			'value' => $this->translate('talkwithsingleselection_contact'),
    			'decorators' => array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td',
    							'width' => '25%',
    							'class' => 'lbl_talkwithsingleselection',
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag' => 'tr',
    							'class' => 'talkwithsingleselectionitemrow',
    							'openOnly' => true,
    					)),
    			),
    	));
    
    	$subform->addElement('select', 'singleselect_' . $confkey, array(
    			'value' => $stored_data['item'][0]['key'],
    			'multiOptions' => $contact, //key=>value
    			'elementBelongsTo' => self::BLOCK_TALKWITHSINGLESELECTION,
    			'belongsTo' => '[item]',
    			'array_index' => $confkey,
    			'class' => 'talkwithsingleselectionitem',
    			'decorators' => array(
    					'ViewHelper',
    					'SimpleSelect',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td',
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag' => 'tr',
    							'closeOnly' => true,
    					)),
    			),
    	));
    
    
    	$subform->addElement('note', 'lbl_freetext', array(
    			'value' => $this->translate('talkwithsingleselection_further_information'),
    			'decorators' => array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td',
    							'width' => '25%',
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag' => 'tr',
    							'openOnly' => true,
    					)),
    			),
    	));
    	$subform->addElement('text', 'TALKSINGLEFREETXT', array(
    			'value' => $stored_data['TALKSINGLEFREETXT'],
    			'elementBelongsTo' => self::BLOCK_TALKWITHSINGLESELECTION,
    			'class' => 'freetext',
    			'array_index' => 'noindex',
    			'decorators' => array(
    					'ViewHelper',
    					'SimpleInput',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td',
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag' => 'tr',
    							'closeOnly' => true,
    					)),
    			),
    	));
    
    	return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    
    //ISPC-2663 Carmen 02.09.2020
    private function get_talkingwithsingleselection_data($options, $client)
    {
    
    	$contactlist = $this->get_contact_list_withsingle_id($client);
    	$erg = array();
    	$erg['item'] = array();
    	foreach ($options['item'] as $option) {
    		if ($option['select'] == 'NOSELECT')
    			continue;
    			$erg['item'][] = array('key' => $option['singleselect'], 'value' => $contactlist[$option['singleselect']]);
    	}
    
    	$erg['TALKSINGLEFREETXT'] = $options['TALKSINGLEFREETXT'];
    
    	return $erg;
    }
    
    private function get_contact_list_withsingle_id($clientid){
    	//get the contact-list and generate keys
    	$contact = array();
    	foreach (ClientConfig::getConfigOrDefault($clientid, 'configtalkwithsingleselection') as $item) {
    		$contact[substr(Pms_CommonData::str_safeascii($item, '', ''), 0, 10)] = $item;
    	};
    
    	return $contact;
    }
    
    private function create_pdf_talkwithsingleselection($stored_data)
    {
    	$subform = $this->subFormContactformBlock();
    	$subform->setLegend('talkwithsingleselection');
    
    	$values = '';
    	$size = count($stored_data['item']);
    	foreach ($stored_data['item'] as $count => $value) {
    		$values .= trim($value['value']);
    		if ($count < $size - 1 && strlen(trim($value['value'])) > 0)
    			$values .= '<br>';
    	}
    
    	// IM-121 if $values empty, don't show
    	if(strlen($values) > 0) {
    		$subform->addElement('note', 'label_talkwith_single', array(
    				'value' => $this->translate('talkwith_contact'),
    				'decorators' => array(
    						'ViewHelper',
    						array(array('data' => 'HtmlTag'), array(
    								'tag' => 'td',
    								'width' => '20%',
    						)),
    						array(array('row' => 'HtmlTag'), array(
    								'tag' => 'tr',
    								'openOnly' => 'true',
    						)),
    				),
    		));
    
    		$subform->addElement('note', 'value_talkwith_single', array(
    				'value' => $values,
    				'decorators' => array(
    						'ViewHelper',
    						array(array('data' => 'HtmlTag'), array(
    								'tag' => 'td',
    								'width' => '75%',
    						)),
    						array(array('row' => 'HtmlTag'), array(
    								'tag' => 'tr',
    								'closeOnly' => 'true',
    						)),
    				),
    		));
    
    	}
    
    
    
    
    	if (trim($stored_data['TALKSINGLEFREETXT']) != '') {
    
    		$subform->addElement('note', 'label_talksinglefreetxt', array(
    				'value' => $this->translate('talkwith_further_information'),
    				'decorators' => array(
    						'ViewHelper',
    						array(array('data' => 'HtmlTag'), array(
    								'tag' => 'td',
    								'width' => '20%',
    						)),
    						array(array('row' => 'HtmlTag'), array(
    								'tag' => 'tr',
    								'openOnly' => 'true',
    						)),
    				),
    		));
    
    		$subform->addElement('note', 'value_talksinglefreetxt', array(
    				'value' => $stored_data['TALKSINGLEFREETXT'],
    				'decorators' => array(
    						'ViewHelper',
    						array(array('data' => 'HtmlTag'), array(
    								'tag' => 'td',
    						)),
    						array(array('row' => 'HtmlTag'), array(
    								'tag' => 'tr',
    								'width' => '75%',
    								'closeOnly' => 'true',
    						)),
    				),
    		));
    	}
    
    	return $this->filter_by_block_name($subform);
    
    }
    
    public function save_form_talkwithsingleselection($ipid = null, $options = array())
    {
    
    	if (empty($ipid) || empty($options)) {
    		return;
    	}
    
    	//set the old block values as isdelete
    	$this->clear_block_data($ipid, $options['__formular']['old_contact_form_id'], self::BLOCK_TALKWITHSINGLESELECTION);
    
    
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	$insert_data = $this->get_talkingwithsingleselection_data($options, $clientid);
    
    	$cust = new FormBlockKeyValue();
    	$cust->ipid = $ipid;
    	$cust->contact_form_id = $options['__formular']['contact_form_id'];
    	$cust->block = self::BLOCK_TALKWITHSINGLESELECTION;
    	$cust->k = 'returnvalue';
    	$cust->v = json_encode($insert_data);
    	$cust->save();
    }
    //--
}

?>
