<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientCourse', 'MDAT');

	class PatientCourse extends BasePatientCourse {

		public $triggerformid = 4;
		public $triggerformname = "frmpatientcourse";

		public function getCourseData($pid, $letter, $chvals = 0, $start = false, $end = false, $sort_direction = 'ASC', $sort_field = 'course_date', $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false , $filter_contactforms = null)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$whrlettr = 1;
			if(!is_numeric($letter))
			{
				$whrlettr = "AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = '" . $letter . "'";
			}

			if($pid)
			{
				$ipid = Pms_CommonData::getIpid($pid);
				$epid = Pms_CommonData::getEpid($ipid);
				$iskeyuser = PatientUsers::checkKeyUserPatient($logininfo->userid, $ipid);

				$previleges = new Modules();
				//moved in the ELSE @author claudiu
// 				$modulepriv_hs = $previleges->checkModulePrivileges("81", $logininfo->clientid);

				if($logininfo->usertype == "SA" || $logininfo->usertype == "CA" || $iskeyuser)
				{
					$privileges = Courseshortcuts::getClientShortcuts($logininfo->clientid);
				}
				else
				{
					$privileges = UserPatientShortcuts::getUserShortcuts($userid, $ipid);
				}

				// Client details
				$client_details = Pms_CommonData::getClientData($clientid);

				//  Symptomatology Values
				$set = Doctrine_Query::create()
					->select('*')
					->from('SymptomatologyValues sv')
					->where('isdelete = 0');
				$svalues = $set->fetchArray();

				foreach($svalues as $k => $s_val)
				{
					$symptom_values[$s_val['id']] = $s_val;
				}


				// Symptomatology Master details
				$symptom_master_values = SymptomatologyMaster::getSymptpomatology($clientid);

				// Service Entry values

				$set = Doctrine_Query::create()
					->select('*')
					->from('FormBlockServiceEntry se')
					->where('isdelete = 0');
				$servvalues = $set->fetchArray();

				foreach($servvalues as $k => $serv_val)
				{
					$service_values[$serv_val['id']] = $serv_val;
				}

				$comma = ",";
				$ipidval = "'0'";
				$cds = "'0'";

				foreach($privileges as $key => $val)
				{
					$cds .= $comma . "'" . $val['shortcutid'] . "'";
				}

				//moved in the ELSE below @author claudiu 19.01.2018
// 				$cd = new Courseshortcuts();
// 				$cdarr = $cd->getShortcuts($cds);

// 				$ct = "'0'";
// 				$comma = ",";

// 				foreach($cdarr as $key => $val)
// 				{
// 					if(!$modulepriv_hs)
// 					{
// 						if($val['shortcut'] != 'HS')
// 						{
// 							$ct .= $comma . "'" . $val['shortcut'] . "'";
// 						}
// 					}
// 					else
// 					{
// 						$ct .= $comma . "'" . $val['shortcut'] . "'";
// 					}
// 				}

				// $ct.= $comma . "'LE'" . $comma . "'LN'"; //  Why are this added here?  commented on 07.11.2016  

				if ( ! is_numeric($chvals)) //used for print course
				{
					$ct = $chvals;
					
				} else {
				    //the ELSE @author claudiu
				    $cd = new Courseshortcuts();
				    $cdarr = $cd->getShortcuts($cds);
				    
				    $course_shortcuts = array();
				    
				    if ( ! empty($cdarr) && is_array($cdarr)) {
				        				        
				        $course_shortcuts = array_column($cdarr, 'shortcut');
				        
				        $modulepriv_hs = $previleges->checkModulePrivileges("81", $logininfo->clientid);
				        
				        if ( ! $modulepriv_hs) {
				            //remove HS
				            if (($key = array_search('HS', $course_shortcuts)) !== false) {
				                unset($course_shortcuts[$key]);
				            }   
				        }				        
				    } 
				    
				    if (empty($course_shortcuts)) {
				        $ct = "'11111100010'"; //@author claudiu changed the $ct=0 in this default... so client maybe will not add this; course_type is varchar[255];
				    } else {
				        $ct = "'" . implode("', '" , $course_shortcuts) . "'";
				    }
				}
				
				
// 				$ctcond = "course_type IN ('" . $ct . "')"; 

				if($start != false && $end != false)
				{
					if($sort_field == 'done_date'){
    					$datecond = ' date(done_date) >= "' . date('Y-m-d', strtotime($start)) . '" AND date(done_date) <= "' . date('Y-m-d', strtotime($end)) . '"';
				    } else{
    					$datecond = ' date(course_date) >= "' . date('Y-m-d', strtotime($start)) . '" AND date(course_date) <= "' . date('Y-m-d', strtotime($end)) . '"';
				    }
				}
				else
				{
					$datecond = '1';
				}

				if($only_count === false)
				{
					$patient = Doctrine_Query::create()
						->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
						->from('PatientCourse pc')
						->where('ipid ="' . $ipid . '"')
						->andWhere($whrlettr)
						->andWhere('ishidden ="0"')
						->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') in (" . $ct . ")")
						->andWhere($datecond)
						->orderBy('DATE_FORMAT(' . $sort_field . ', "%Y-%m-%d %H:%i") ' . $sort_direction . ', wrong ASC, ' . $sort_field . ' ASC');
					
					//ISPC-2071
					//contact_forms that are re-edited have patient_courses that can be groupped by done_id
					//we exclude from the main search this done_id's of reEdited ones, and we will fetch them later
					if (  ! empty($filter_contactforms) && is_array($filter_contactforms)) {
					
					    if ( ! empty($filter_contactforms['exclude']) && is_array($filter_contactforms['exclude'])) {
					        $patient->andWhereNotIn("CONCAT_WS(' CONCAT_WS_SEPARATOR ', done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['exclude']);
					    }
					    if ( ! empty($filter_contactforms['include']) && is_array($filter_contactforms['include'])) {
					        $patient->andWhereIn("CONCAT_WS(' CONCAT_WS_SEPARATOR ', done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['include']); 	
					    }
					    
					    //TODO: change the filter to a WHERE (done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "')) IN((),...)
					    /*
					    if ( ! empty($filter_contactforms['excludeIN']) && is_array($filter_contactforms['excludeIN'])) {
					       $patient->andWhereNotIn("(done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['excludeIN']);
					    }
					    if ( ! empty($filter_contactforms['includeIN']) && is_array($filter_contactforms['includeIN'])) {
					        $patient->andWhereIn("(done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['includeIN']);
					    }
					    */
					    if ( ! empty($filter_contactforms['exclude_ids']) && is_array($filter_contactforms['exclude_ids'])) {
					        $patient->andWhereNotIn("id", $filter_contactforms['exclude_ids']);
					    }
					    
					    
					    $patient->leftJoin('pc.PatientCourseExtra pce')
					    ->andWhere("pce.id IS NULL OR pce.is_removed ='no'");
					    	
					}
					
					
					if($limit)
					{
						$patient->limit($limit);
						$patient->offset($offset);
					}

					$patientarray = $patient->fetchArray();

					$course_style =  array();
					foreach($patientarray as $key => $value)
					{
						$course_style[] = $value['course_type'];
					}
// 					$course_style[] = '999999'; //prevent fehler on empty array //@author claudiu removed flower

					$course_styles = Courseshortcuts::getCourseMultipleDataByShortcut($course_style);

					$i = 0;
					foreach($patientarray as $key => $value)
					{
                        
					    
					    //TODO-2802 ANCUTA 14.01.2020
					    if ($value['alien'] == '1' && $value['done_name'] == 'contact_form' && $value['done_id']){
					        $value['done_name'] = 'contact_form_alien';
					        $value['done_id'] = '0';
					    }
					    // -- 
						if($value['isserialized'] == 1 && $value['course_type'] == 'S')
						{
							if($value['tabname'] == "cf_client_symptoms"){
								$desrz_s2 = unserialize($value['course_title']);
// 								print_r($desrz); exit;
								$grid = new Pms_Grid($desrz_s2, 1, count($desrz_s2), "courseclientsymptolist.html");
								$grid->clientid = $clientid;
								$value['course_title'] = $grid->renderGrid();
							} 
							else
							{
								$desrz = unserialize($value['course_title']);
								$grid = new Pms_Grid($desrz, 1, count($desrz), "coursesymptolist.html");
								$grid->clientid = $clientid;
	
								$grid->client_data = $client_details[0];
	
								$grid->symptom_values = $symptom_values;
	
								$grid->symptom_master_values = $symptom_master_values;
	
								$value['course_title'] = $grid->renderGrid();
							}
						}
						elseif($value['isserialized'] == 1 && $value['course_type'] == 'PD')
						{

							$servdesrz = unserialize($value['course_title']);
							$grid = new Pms_Grid($servdesrz, 1, count($servdesrz), "courseservicelist.html");

							$grid->clientid = $clientid;

							$grid->service_values = $service_values;

							$value['course_title'] = $grid->renderGrid();
						}
						$coursearr[0] = $course_styles[$value['course_type']];

						$cdate = date("Y-m-d H:i", strtotime($value[$sort_field]));
						$uid = $value['user_id'];
						$block[$key] = $value;
						$block[$key]['font_color'] = $coursearr[0]['font_color'];
						$block[$key]['isbold'] = $coursearr[0]['isbold'];
						$block[$key]['isitalic'] = $coursearr[0]['isitalic'];
						$block[$key]['isunderline'] = $coursearr[0]['isunderline'];


						//ispc-2017 different approach of grouping the contactforms...
						if ($value['done_name'] == 'contact_form' && $value['done_id']) 
						{
						    
						    $block_key = $value['done_name'] . $value['done_id'];
						    
						    $allblocks[$block_key] = [
						        'summary'     => isset($allblocks[$block_key]) ? array_merge($allblocks[$block_key]['summary'], [$block[$key]]) : [$block[$key]],
						        'date'        => $cdate,
						        'user'        => $uid,
						        'wrong'       => $value['wrong'],
						        'source_ipid' => $value['source_ipid'],
						        'cid'         => $value['id'],
						        'wrongcomment'=> $value['wrongcomment'],
						        
						        //done_date must allways be the date you have inside the formular, set ... so if you set the date inside the formular without adding a patientcourse.. it will NOT work ok....
						        'date_dt'     => date('d.m.Y', strtotime($cdate)),
						        'date_hm'     => date('H:i', strtotime($cdate)),
						        
						        //course_date .. almost the same as create_date
						        'c_date_dt'   => date('d.m.Y', strtotime($value['course_date'])),
						        'c_date_hm'   => date('H:i', strtotime($value['course_date'])),
						        
						        '__is_contactform' => true,
						    ];
						    
						    unset($block[$key]);
						    
						} else {
    						//block keys
						    //TODO-3481 Ancuta 05.10.2020 - added to the clock key, source_ipid - as  they should be  listed separatly 
//     						$block_key = date("Y-m-d H:i", strtotime($value[$sort_field])) . '_' . $uid . '_' . $value['wrong'];
//     						$next_block_key = date("Y-m-d H:i", strtotime($patientarray[$key + 1][$sort_field])) . '_' . $patientarray[$key + 1]['user_id'] . '_' . $patientarray[$key + 1]['wrong'];						    
						    $block_key = date("Y-m-d H:i", strtotime($value[$sort_field])) . '_' . $uid . '_' . $value['wrong']. '_' . $value['source_ipid'];
    						$next_block_key = date("Y-m-d H:i", strtotime($patientarray[$key + 1][$sort_field])) . '_' . $patientarray[$key + 1]['user_id'] . '_' . $patientarray[$key + 1]['wrong']. '_' . $patientarray[$key + 1]['source_ipid'];
						    //-- 

    						
    						//ispc-2017 different approach of grouping the contactforms...
    						if ($value['done_name'] == 'contact_form' && $value['done_id']) {
    						    $block_key .= $value['done_name'] . $value['done_id'];
    						}
    						if (isset($patientarray[$key + 1]) && $patientarray[$key + 1]['done_name'] == 'contact_form' && $patientarray[$key + 1]['done_id']) {
    						    $next_block_key .= $patientarray[$key + 1]['done_name'] . $patientarray[$key + 1]['done_id'];
    						}
    						
    						
    						//if ($cdate != date("Y-m-d H:i", strtotime($patientarray[$key + 1][$sort_field])) || $uid != $patientarray[$key + 1]['user_id'])
    						if($block_key != $next_block_key)
    						{
    							$tempblocks['summary'] = $block;
    							$tempblocks['date'] = $cdate;
    							$tempblocks['user'] = $uid;
    							$tempblocks['wrong'] = $value['wrong'];
    							$tempblocks['source_ipid'] = $value['source_ipid'];
    							$tempblocks['cid'] = $value['id'];
    							$tempblocks['wrongcomment'] = $value['wrongcomment'];
    							$tempblocks['date_dt'] = date('d.m.Y', strtotime($cdate));
    							$tempblocks['date_hm'] = date('H:i', strtotime($cdate));
    							$tempblocks['c_date_dt'] = date('d.m.Y', strtotime($value['course_date']));
    							$tempblocks['c_date_hm'] = date('H:i', strtotime($value['course_date']));
    							$allblocks[] = $tempblocks;
    							$block = array();
    							$tempblocks = array();
    						}
						}
					}
				}
				else
				{
				    
				    
// 					$patient_course = Doctrine_Query::create()
					$patient_course = $this->getTable()->createQuery('pc')
					->select("count(*)")
					->where('pc.ipid = ?', $ipid)
					//->andWhere('ishidden = "0"') // this is on select.. so it should also be on count... for @cla this is a bug.. fix if you want
// 					->from('PatientCourse pc')
                    ;
						
					//ISPC-2071
					//contact_forms that are re-edited have patient_courses that can be groupped by done_id
					//we exclude from the main search this done_id's of reEdited ones, and we will fetch them later
					if (  ! empty($filter_contactforms) && is_array($filter_contactforms)) {

					    //rc 0.1
					    if ( ! empty($filter_contactforms['exclude']) && is_array($filter_contactforms['exclude'])) {
					        $patient_course->andWhereNotIn("CONCAT_WS(' CONCAT_WS_SEPARATOR ', done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['exclude']);
					    }
					    if ( ! empty($filter_contactforms['include']) && is_array($filter_contactforms['include'])) {
					        $patient_course->andWhereIn("CONCAT_WS(' CONCAT_WS_SEPARATOR ', done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['include']);
					    }
					    //TODO: change the filter to a WHERE (done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "')) IN((),...)
					    /*
					     if ( ! empty($filter_contactforms['excludeIN']) && is_array($filter_contactforms['excludeIN'])) {
					     $patient->andWhereNotIn("(done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['excludeIN']);
					     }
					     if ( ! empty($filter_contactforms['includeIN']) && is_array($filter_contactforms['includeIN'])) {
					     $patient->andWhereIn("(done_id, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "'))", $filter_contactforms['includeIN']);
					     }
					     */
					    
					    if ( ! empty($filter_contactforms['exclude_ids']) && is_array($filter_contactforms['exclude_ids'])) {
					        $patient_course->andWhereNotIn("id", $filter_contactforms['exclude_ids']);
					    }
					    
					    //v2 : instead on concat_ws use a join
// 					    add relation
//     				    $this->getTable()->hasOne('PatientCourse', array(
//     				        'local' => 'id',
//     				        'foreign' => 'id'
//     				    ));
// 					    $patient_course
// 					    ->leftJoin("pc.PatientCourse pc2 WITH (pc2.done_id IN (?) AND AES_DECRYPT(pc2.done_name,'" . Zend_Registry::get('salt') . "') = ?)", array(implode(', ', $filter['done_id']), $filter['done_name']) )
// 				        ->andWhere('pc2.id IS NULL ')
						
					    $patient_course->leftJoin('pc.PatientCourseExtra pce')
					    ->andWhere("pce.id IS NULL OR pce.is_removed ='no'");
					    
					    
					}	
						
					$patient_course->andWhere($whrlettr)
					->andWhere("AES_DECRYPT(pc.course_type,'" . Zend_Registry::get('salt') . "') IN (" . $ct . ")")
					->andWhere($datecond)
// 					->orderBy($sort_field . ' ' . $sort_direction) //removed by @author claudiu; no need for order on count
					;
					
					
					$allblocks = $patient_course->fetchArray(); 
				}

				return $allblocks;
			}
		}

		public function getCourseDataByShortcut($ipid, $shrt, $explodeCourseTitle = true, $hide_wrong = false,$exclude_shared = true)
		{

			$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('PatientCourse')
				->where("ipid='" . $ipid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");
			if($hide_wrong)
			{
				$qpa1->andWhere('wrong="0"');
			}

			if($exclude_shared){
				$qpa1->andWhere('source_ipid = ""');
			}
			$qp1 = $qpa1->execute();

			if($qp1)
			{
				$newarr1 = $qp1->toArray();
				for($i = 0; $i < count($newarr1); $i++)
				{
					if($explodeCourseTitle)
					{
						$rem = explode("|", $newarr1[$i]['course_title']);
						$newarr1[$i]['course_title'] = $rem[0];
					}
				}
				return $newarr1;
			}
		}

		public function getCourseDataForSpecialreport($userid, $where)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				              AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('PatientCourse')
				->where("wrong != 1 and user_id='" . $userid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt("L")) . "' " . $where);
			$qp1 = $qpa1->execute();

			if($qp1)
			{
				$newarr1 = $qp1->toArray();

				return $newarr1;
			}
		}

		function getipidfromclientid($clientid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_patients = PatientUsers::getUserPatients($logininfo->userid); //get user's patients by permission
			$lastipid = Doctrine_Query::create()
				->select('e.ipid')
				->from('EpidIpidMapping e')
				->where("e.clientid = " . $clientid);
			$newipidval = $lastipid->getDql();

			$actipid = Doctrine_Query::create()
				->select('p.ipid')
				->from('PatientMaster p')
				->where("p.ipid in (" . $newipidval . ") and p.ipid in (" . $user_patients['patients_str'] . ") and p.isdelete=0 and p.isdischarged=0")
				->andWhere("p.isstandbydelete = 0");
			$actipidarray = $actipid->fetchArray();

			$comma = ",";
			$actipidval = "'0'";
			foreach($actipidarray as $key => $val)
			{
				$actipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			return $actipidval;
		}

		public function getLastKrise($ipid)
		{


			$qur = Doctrine_Query::create()
				->select('*')
				->from('PatientCourse')
				->where("ipid in(" . $ipid . ") and course_date >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))) . " 00:00:00'  and  course_type='" . addslashes(Pms_CommonData::aesEncrypt("X")) . "'  ")
				->orderBy('course_date DESC')
				->groupBy(ipid);
			$qurexec = $qur->execute();
			if($qurexec)
			{
				$firstarray = $qurexec->toArray();
				$comma = ",";
				$firstids = array();
				$secondids = '0';
				foreach($firstarray as $key => $val)
				{
					if(!in_array($val['ipid'], $firstids))
					{
						array_push($firstids, $val['ipid']);
						$secondids .= $comma . "'" . $val['id'] . "'";
						$comma = ",";
					}
				}
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientCourse')
				->where("id in(" . $secondids . ") and course_date >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))) . " 00:00:00'  and  course_type='" . addslashes(Pms_CommonData::aesEncrypt("X")) . "'")
				->orderBy('course_date ASC');
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();

				return $droparray;
			}
		}

		public function getCourseByIpidAndShortcuts($ipid, $shortcuts, $explodeCourseTitle = false)
		{
			$q = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
				AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where("ipid='" . $ipid . "'")
				->andWhere('source_ipid = ""');

			if(is_array($shortcuts))
			{
				$shortcuts_arr[] = '999999999';
				foreach($shortcuts as $shortcut)
				{
					$shortcuts_arr[] = Pms_CommonData::aesEncrypt($shortcut);
				}

				$q->andWhereIn('course_type', $shortcuts_arr);
			}
			else
			{
				$q->andWhere("course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");
			}

			$coursearr = $q->fetchArray();

			if($coursearr)
			{
				return $coursearr;
			}
		}

		public function copy_verlauf_records($ipid, $target_ipid, $allowed_shortcuts)
		{

			$course_data = $this->getCourseByIpidAndShortcuts($ipid, $allowed_shortcuts);
			//print_r($course_data);
			foreach($course_data as $course)
			{
				if($course['recordid'] == '0' || ($course['tabname'] == 'patient_drugplan' && $course['recordid'] >= '0') || $course['tabname'] != 'kvno_assesment')
				{
					$pc = new PatientCourse();
					$pc->ipid = $target_ipid;
					$pc->user_id = $course['user_id'];
					$pc->course_date = $course['course_date'];
					$pc->course_type = Pms_CommonData::aesEncrypt($course['course_type']);
					$pc->tabname = Pms_CommonData::aesEncrypt($course['tabname']);
					$pc->course_title = Pms_CommonData::aesEncrypt($course['course_title']);
					$pc->recordid = '0';
					$pc->recorddata = $course['recorddata'];
					$pc->ishidden = $course['ishidden'];
					$pc->wrong = $course['wrong'];
					$pc->wrongcomment = $course['wrongcomment'];
					$pc->isstandby = $course['isstandby'];
					$pc->isserialized = $course['isserialized'];
					$pc->source_ipid = $ipid;
					$pc->done_date = $course['done_date'];
					$pc->done_name = $course['done_name'];
					$pc->done_id = $course['done_id'];
					/*
					 * TODO : This column never went5 into prodcution, and was moved to another table
					 * ... do the things..
					 */
// 					$pc->is_removed = $course['is_removed'];//ISPC-2071

					
					$pc->save();
				}
			}
		}

		public function get_patient_shortcuts_course($ipid, $shortcuts = false, $curent_period = false, $include_deleted = false, $course_id = false)
		{
			if($shortcuts && is_array($shortcuts))
			{
				foreach($shortcuts as $k_short => $v_short)
				{
					$sql_courses[] = 'course_type="' . addslashes(Pms_CommonData::aesEncrypt($v_short)) . '" ';
				}
			}

			$sql_courses_str = implode("OR ", $sql_courses);

			$course_data = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid =?', $ipid)
				->andWhere('source_ipid = ""');
			if($sql_courses)
			{
				$course_data->andWhere($sql_courses_str);
			}

			if($include_deleted === false)
			{
				$course_data->andWhere('wrong=0');
			}
			
			if($curent_period)
			{
				$course_data->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
			}

			if($course_id){
				if( ! is_array($course_id)){
					$course_id = array($course_id);
				}
			
				$course_data->andWhereIn('id',$course_id);
			}
			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}

		
		/**
		 * @deprecated, use ContactForms::get_deleted_contactforms_by_ipid
		 */
		public function get_deleted_contactforms($ipids = false, $ipids_key = true)
		{
			if($ipids)
			{
				$deleted_cf = Doctrine_Query::create()
					->select("id,ipid,recordid,AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('PatientCourse')
					->where('wrong=1')
					->andWhereIn("ipid", $ipids)
					->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
					->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'")
					->andWhere("aes_decrypt(course_title,'encrypt') NOT LIKE '%editiert%'");
				$deleted_cf_array = $deleted_cf->fetchArray();
				
				if($deleted_cf_array)
				{
					$deleted_cf_ids[] = '99999999999';
					foreach($deleted_cf_array as $k_dcf => $v_dcf)
					{
						if($ipids_key)
						{
							$deleted_cf_ids[$v_dcf['ipid']][] = $v_dcf['recordid'];
						}
						else
						{
							$deleted_cf_ids[] = $v_dcf['recordid'];
						}
					}



					return $deleted_cf_ids;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_course_details($course_ids = false, $tabname = false)
		{
			if($course_ids)
			{
				$course_ids[] = '9999999999';
				$query = Doctrine_Query::create()
					->select('*')
					->from('PatientCourse')
					->whereIn('id', $course_ids)
					->andWhere('wrong = "1"'); //it is allready made wrong
				if($tabname)
				{
					$query->andWhere('tabname = "' . $tabname . '"');
				}

				$query_res = $query->fetchArray();

				if($query_res)
				{
					return $query_res;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_deleted_visits($ipid)
		{
			$visits_form_course = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid ="' . $ipid . '"')
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("(AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'sakvno_doctor_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'wl_doctor_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'visit_koordination_form' OR 
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'bayern_doctorvisit')")
				->orderBy('course_date ASC');

			$visits_course = $visits_form_course->fetchArray();

			if($visits_course)
			{
				foreach($visits_course as $k_course => $v_course)
				{
					$deleted_ids[$v_course['tabname']][] = $v_course['recordid'];
				}

				return $deleted_ids;
			}
			else
			{
				return false;
			}
		}
		public function get_deleted_visits_multiple_patients($ipids = false, $ipids_key = true)
		{
			if($ipids)
			{
    			$visits_form_course = Doctrine_Query::create()
    				->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
    					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
    				->from('PatientCourse')
    				->whereIn('ipid',$ipids)
    				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
    				->andWhere("wrong = 1")
    				->andWhere("(AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'lvn_nurse_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'sakvno_doctor_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'sakvno_nurse_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'wl_doctor_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'wl_nurse_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'visit_koordination_form' OR 
    					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'bayern_doctorvisit')")
    				->orderBy('course_date ASC');
    
    			$visits_course = $visits_form_course->fetchArray();
    
    			if($visits_course)
    			{
    				foreach($visits_course as $k_course => $v_course)
    				{
    				    if($ipids_key)
    				    {
        					$deleted_ids[$v_course['ipid']][$v_course['tabname']][] = $v_course['recordid'];
    				    }
    				    else
    				    {
        					$deleted_ids[$v_course['tabname']][] = $v_course['recordid'];
    				    }
    				}
    
    				return $deleted_ids;
    			}
    			else
    			{
    				return false;
    			}
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_patient_course($ipids, $shrt, $explodeCourseTitle = true, $hide_wrong = false)
		{

			$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('PatientCourse')
				->whereIn("ipid", $ipids)
				->andWhere('source_ipid = ""')
				->andWhere("course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");
			if($hide_wrong)
			{
				$qpa1->andWhere('wrong="0"');
			}

			$newarr1 = $qpa1->fetchArray();

			if($newarr1)
			{
				foreach($newarr1 as $k_course => $v_course)
				{
					if($explodeCourseTitle)
					{
						$course_title_arr = explode("|", $v_course['course_title']);
						$v_course['course_title'] = $course_title_arr[0];

						$course_arr[$v_course['ipid']][] = $v_course;
					}
					else
					{
						$course_arr[$v_course['ipid']][] = $v_course;
					}
				}
				return $course_arr;
			}
		}

		public function get_sh_patient_shortcuts_course($ipid, $shortcuts = false, $curent_period = false, $include_deleted = false)
		{
			if($shortcuts && is_array($shortcuts))
			{
				foreach($shortcuts as $k_short => $v_short)
				{
					$sql_courses[] = 'course_type="' . addslashes(Pms_CommonData::aesEncrypt($v_short)) . '" ';
				}
			}

			$sql_courses_str = implode("OR ", $sql_courses);

			$course_data = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse');

			if(is_array($ipid))
			{
				$ipid[] = '9999999999999';
				$course_data->whereIn('ipid', $ipid);
			}
			else
			{
				$course_data->where('ipid LIKE "' . $ipid . '"');
			}

			if($sql_courses)
			{
				$course_data->andWhere($sql_courses_str);
			}

			if($include_deleted === false)
			{
				$course_data->andWhere('wrong=0');
			}

			if($curent_period)
			{
				$course_data->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
			}
			$course_data->andWhere('source_ipid = ""');
			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}
		
		public function get_shstatistik_patient_shortcuts_course($ipid, $shortcuts = false, $patients_periods = false, $curent_period = false, $include_deleted = false)
		{
			if($shortcuts && is_array($shortcuts))
			{
				foreach($shortcuts as $k_short => $v_short)
				{
// 					$sql_courses[] = 'course_type="' . addslashes(Pms_CommonData::aesEncrypt($v_short)) . '" ';
					$sql_courses[] = "course_type = AES_ENCRYPT('".$v_short."', '" . Zend_Registry::get('salt') . "') ";
				}
			}

			$sql_courses_str = implode("OR ", $sql_courses);

			if( ! empty($patients_periods)){

				foreach($patients_periods as $k_sapv_data => $v_sapv_data)
				{
					$sql_parts[] = '(ipid LIKE "' . $v_sapv_data['ipid'] . '" AND  DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($v_sapv_data['start'])) . '" AND "' . date('Y-m-d', strtotime($v_sapv_data['end'])) . '" )';
// 					$sql_parts[] = '(ipid LIKE "' . $v_sapv_data['ipid'] . '" AND   `done_date`  BETWEEN "' . date('Y-m-d', strtotime($v_sapv_data['start'])) . '" AND "' . date('Y-m-d', strtotime($v_sapv_data['end'])) . '" )';
				}
				
				$sql_in_pperiod = implode(" OR ", $sql_parts);
				
			}
				
			
			$course_data = Doctrine_Query::create()
				->select("id,ipid,done_date,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse');
			
			
			if(!empty($patients_periods) && strlen($sql_in_pperiod) > 0 ){
				$course_data->andWhere($sql_in_pperiod);
			} 
			else
			{
				if(is_array($ipid))
				{
					$ipid[] = '9999999999999';
					$course_data->whereIn('ipid', $ipid);
				}
				else
				{
					$course_data->where('ipid LIKE "' . $ipid . '"');
				}
			}

			if($sql_courses)
			{
				$course_data->andWhere($sql_courses_str);
			}
 
			if($include_deleted === false)
			{
				$course_data->andWhere('wrong=0');
			}

			if($curent_period)
			{
				$course_data->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"  ');
// 				$course_data->andWhere(' done_date BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"  ');
			}
			$course_data->andWhere('source_ipid = ""');
			
			$course_res = $course_data->fetchArray();
			
			if($course_res)
			{
				return $course_res;
			}
		}
		
		public function get_multi_pat_shortcuts_course($ipids, $shortcuts = false, $curent_period = false, $include_deleted = false, $exclude_shared = true)
		{
			

			if($shortcuts && is_array($shortcuts))
			{
				foreach($shortcuts as $k_short => $v_short)
				{
					$sql_courses[] = 'course_type="' . addslashes(Pms_CommonData::aesEncrypt($v_short)) . '" ';
				}
			}

			$sql_courses_str = implode("OR ", $sql_courses);

			$ipids[] = '99999999999999';
			$course_data = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->whereIn('ipid', $ipids);
			if($sql_courses)
			{
				$course_data->andWhere($sql_courses_str);
			}

			if($include_deleted === false)
			{
				$course_data->andWhere('wrong=0');
			}

			if($curent_period)
			{
				$course_data->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
			}

			if($exclude_shared)
			{
				$course_data->andWhere('source_ipid = ""');
			}
			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}
		
		public static function remove_course_links($in, $mode = null) {
			$dat = $in;
			$a = preg_match_all ( '/<a .+?>.+?<\/a>/im', $dat, $linkmatches );
		
			if ($a > 0) {
				foreach ( $linkmatches [0] as $mlink ) {
					preg_match ( '/<a .+?>(.+?)<\/a>/im', $mlink, $linkfree );
					$link = "";
					if (is_array ( $linkfree )) {
						$link = $linkfree [1];
					}
					$dat = str_replace ( $mlink, $link, $dat );
				}
			}
			return $dat;
		}
	
		
		//used only in ContactForms.php
		public function getCourseDetailsByIpidAndShortcut($ipids, $shortcuts)
		{
			if (empty($ipids)) {
				return;
			}elseif ( ! is_array($ipids)) {
				$ipids = array($ipids);
			}
			
			
			$q = Doctrine_Query::create()
			->select("*")
			->from('PatientCourse')
			->whereIn("ipid" , $ipids);
			
			if(is_array($shortcuts))
			{
				$shortcuts_arr =  array();
				foreach($shortcuts as $shortcut)
				{
					$shortcuts_arr[] = Pms_CommonData::aesEncrypt($shortcut);
				}
		
				$q->andWhereIn('course_type', $shortcuts_arr);
			}
			else
			{
				$q->andWhere("course_type = AES_ENCRYPT( ? , ?)", array($shortcuts, Zend_Registry::get('salt')));
			}		
			
			if($coursearr = $q->fetchArray())
			{
				return $coursearr;
			}
		}
	
		public function insertPatientCourse($data) 
		{			
			if (empty($data['ipid']) || empty($data['user_id']) ||  empty($data['course_type']) ) {
				return false;
			}
			
			$this->ipid = $data['ipid'];
			$this->course_date = empty($data['course_date']) ? date("Y-m-d H:i") : $data['course_date'];//date("Y-m-d H:i");
			$this->course_type = Pms_CommonData::aesEncrypt( $data['course_type'] );
			$this->course_title = Pms_CommonData::aesEncrypt( $data['course_title'] );
			$this->user_id =  $data['user_id'];		
			$this->save();
			return $this->id;
			
		}		
		
		/**
		 * be aware, the fn name may be misleading - this is how Doctrine works!
		 * this fn will insert new if there is no db-record object in our class...
		 * if you called second time, or you fetchOne, it will update!
		 * fn was intended for single record, not collection
		 * @param array $params
		 * @return boolean|number
		 * return $this->id | false if you don't have the mandatory_columns in the params
		 */
		public function set_new_record($params = array())
		{
				
			if (empty($params) || !is_array($params)) {
				return false;// something went wrong
			}
			if ( (empty($params['ipid']) || empty($params['user_id']) ||  empty($params['course_type'])) 
					&& ( empty($this->ipid) || empty($this->user_id) ||  empty($this->course_type) ) )
			{
				return false;
			}
				
			foreach ($params as $k => $v)
				if (isset($this->{$k})) {
						
					//next columns should be encrypted
					switch ($k) {
						case "course_type":
						case "course_title":
						case "tabname":
						case "done_name":
							$v = Pms_CommonData::aesEncrypt($v);
							break;
					}
					$this->{$k} = $v;
						
				}
			if (empty($params['course_date'])){
				$this->course_date = date("Y-m-d H:i");
			}
			$this->save();
			return $this->id;
		
		}

		
		public function getCourseDataReceived($ipids)
		{
			if (empty($ipids)) {
				return;
			}elseif ( ! is_array($ipids)) {
				$ipids = array($ipids);
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$qpa1 = Doctrine_Query::create()
			->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				              AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
			->from('PatientCourse')
			->where('source_ipid != "" ')
			->andWhereIn('ipid',$ipids);
			$qp1 = $qpa1->execute();
		
			if($qp1)
			{
				$newarr1 = $qp1->toArray();
		
				return $newarr1;
			}
		}
		
		
		
	/**
	 * created for ISPC-2071
	 * @author claudiu 22.01.2018
	 * 
	 * @since 16.10,2018
	 * fn was modified before we used in production
	 * field is_removed was moved into the newly created PatientCourseExtra
	 * 
	 * 
	 * @param string $ipid             - filter
	 * @param number $contact_form_id  - filter
	 * @param string $tabname          - filter
	 * @param string $is_removed       - set
	 * @param string $remove_childrens - filter
	 * @param string|array $course_types   - filter
	 * @return void|Doctrine_Collection
	 */
	public static function setIsRemovedByIpidAndContactFormAndTabname( $ipid = '', $contact_form_id = 0, $tabname = '', $is_removed = 'yes', $remove_childrens = true, $course_types = null) 
	{
	    if (empty($contact_form_id) || empty($ipid) || empty($tabname)) {
	        return; // nothing to update
	    }
	    
	    if ( ! is_array($contact_form_id)) {
	        $contact_form_id =  array($contact_form_id);
	    }
	    
	    if ($remove_childrens === true) {
	        //search all the parents_childrens of ths form, we will remove PC from them too
	        $cf_entity = new ContactForms();
	        $cf_traceHistory = $cf_entity->traceHistory($contact_form_id);
	        $cf_history = $cf_traceHistory['_trace'];
	        
	        if ( ! empty($cf_history)) {
	            $contact_form_id = array_shift(array_values($cf_history)); // first in array was the newes cf
	        }
	    }
	    
	    $is_removed = ($is_removed === 'yes'|| $is_removed === 1 || $is_removed === true) ? 'yes' : 'no';
	    
	    
	    
	    $query = Doctrine_Query::create()
	    ->select('id')
	    ->from('PatientCourse')
	    ->where('ipid = ?', $ipid)
	    ->andWhereIn('done_id', $contact_form_id)
// 	    ->andWhere('tabname = AES_ENCRYPT(?, ?) = ?', [$tabname, Zend_Registry::get('salt')])
// 	    ->andWhere('done_name = AES_ENCRYPT(?, ?) = ?', ['contact_form', Zend_Registry::get('salt')])
	    ->andWhere('AES_DECRYPT(tabname, ?) = ?', [Zend_Registry::get('salt'), $tabname])
	    ->andWhere('AES_DECRYPT(done_name, ?) = ?', [Zend_Registry::get('salt'), 'contact_form'])
	    ;

	    if ( ! is_null($course_types)) {
	        //filter also by course_type
	        if ( ! is_array($course_types)) {
	            $course_types =  array($course_types);
	        }
	        $query->andWhereIn('AES_DECRYPT(course_type, \''.Zend_Registry::get('salt').'\')', $course_types);
	    }
	    
	    $saved_pcs = $query->fetchArray();
	    
	    if (empty($saved_pcs)) {
	        return;//failsafe, premature evacuation
	    }

	    
	    foreach($saved_pcs as $pc_row) {
	        PatientCourseExtraTable::getInstance()->findOrCreateOneBy('patient_course_id', $pc_row['id'], ['is_removed' => $is_removed]);
	    }
	}
	
	
	/**
	 * @author claudiu on 01.03.2018 for ISPC-2161
	 * returns the one with MAX(done_date)
	 * 
	 * @param unknown $ipids
	 * @return void|multitype:unknown
	 */
	public static function get_last_XT( $ipids = array())
	{
	    if (empty($ipids)){
	        return;   
	    }
	    
	    if ( ! is_array($ipids)) {
	        $ipids = array($ipids);
	    }
	    
	    $result = array();
	    
	    $placeholders = str_repeat ('?, ',  count ($ipids) - 1) . '?';
	    
	    $querystr ="SELECT pc.* FROM patient_course pc
        INNER JOIN 
        (SELECT MAX(done_date) AS max_done_date, ipid FROM patient_course 
           WHERE ipid IN ({$placeholders}) AND AES_DECRYPT(course_type, '".Zend_Registry::get('salt'). "') = 'XT' AND wrong = 0
           GROUP BY ipid) pcc  
        ON pc.ipid = pcc.ipid  
        AND pc.done_date = pcc.max_done_date
        AND AES_DECRYPT(course_type, '".Zend_Registry::get('salt'). "') = 'XT'        
        AND pc.wrong = 0"
	    ;
	    
	    $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
	    $query = $conn->prepare($querystr);
	    $query->execute($ipids);
	    $res = $query->fetchAll(PDO::FETCH_ASSOC);
	    
	    foreach ($res as $row) {
	        $result[$row['ipid']] = $row;
	    }
	    
	    return $result;
	}

	
	
	/**
	 * @author Ancuta on 29.03.2018 for TODO-1466
	 * @param string $ipids
	 * @param unknown $shortcuts
	 * @param string $discharge_dates
	 * @param string $period
	 * @param string $details
	 * @return Ambigous <unknown, multitype:, Doctrine_Collection>
	 */
	public function get_patients_period_course_by_shortcuts($ipids = false, $shortcuts , $discharge_dates = false, $period = false,$details = false)
	{
	    $sql = "id, ipid, course_date, wrong, done_date, create_user, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type";
	    if($details)
	    {
    	    $sql = "id, ipid, course_date, wrong, done_date, create_user, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title";
	    }
	    
		$course = Doctrine_Query::create()
		->select($sql)
		->from('PatientCourse')
		->where("wrong = 0")
		->andWhere('source_ipid = ""')
		->orderBy('course_date ASC');
		if($ipids && $period)
		{
			foreach($ipids as $ipid)
			{
				$sql_w[] = ' ipid LIKE "' . $ipid . '" AND (DATE(`done_date`) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($period[$ipid]['start'])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime('-1 second', strtotime('+1 day', strtotime($period[$ipid]['end'])))) . '")) ';
			}
	
			$course->andWhere(implode("OR", $sql_w));
		}
		else
		{
			$course->andWhereIn('ipid', $ipids);
		}
	
	
		if(is_array($shortcuts))
		{
			$shortcuts_arr[] = '999999999';
			foreach($shortcuts as $shortcut)
			{
				$shortcuts_arr[] = Pms_CommonData::aesEncrypt($shortcut);
			}
				
			$course->andWhereIn('course_type', $shortcuts_arr);
		}
		else
		{
			$course->andWhere("course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");
		}
	
			
		$course_res = $course->fetchArray();
 
		foreach($course_res as $k_course => $v_course)
		{
			$course_date = date('Y-m-d', strtotime($v_course['done_date']));// TODO-2058 Added by Ancuta 30.01.2019
			
			if(strtotime($v_course['done_date']) <= strtotime($discharge_dates[$v_course['ipid']]))
			{
				$course_date = date('Y-m-d', strtotime($v_course['done_date']));
			}
			else if(strlen($discharge_dates[$v_course['ipid']]) == "0")
			{
				$course_date = date('Y-m-d', strtotime($v_course['done_date']));
			}
	
			
			if($details)
			{
				$days_course[$v_course['ipid']][$course_date][] = $v_course;
			}
			else
			{
				$days_course[$v_course['ipid']][$course_date][] = $v_course['course_type'];
			}
	
		}
	
		return $days_course;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	}
?>