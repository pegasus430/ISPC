<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersCourse', 'SYSDAT');

	class VoluntaryworkersCourse extends BaseVoluntaryworkersCourse {


		public function getCourseData($vw_id, $letter_arr, $chvals = 0, $start = false, $end = false, $sort_direction = 'ASC', $sort_field = 'course_date', $offset = '0', $limit = false, $page = false, $only_count = false, $first_limit = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			$whrlettr = 1;
			if(!empty($letter_arr))
			{
			    
    			if(!is_array($letter_arr)){
    			    $letter_arr = array($letter_arr);
    			}
			}

			if($vw_id)
			{
				// Client details
				$client_details = Pms_CommonData::getClientData($clientid);

				$comma = ",";
				$ipidval = "'0'";
				$cds = "'0'";

				foreach($privileges as $key => $val)
				{
					$cds .= $comma . "'" . $val['shortcutid'] . "'";
				}

				$cdarr = Pms_CommonData::get_voluntary_shortcuts();
				$privileges = $cdarr;

				$ct = "'0'";
				$comma = ",";

				foreach($cdarr as $key => $val)
				{
					$ct .= $comma . "'" . $val['shortcut'] . "'";
					$course_styles[$val['shortcut']] =  $val; 
				}

				if(!is_numeric($chvals)) //used for print course
				{
					$ct = $chvals;
				}
				$ctcond = "course_type in ('" . $ct . "')";

				if($start != false && $end != false)
				{
					$datecond = ' date(course_date) >= "' . date('Y-m-d', strtotime($start)) . '" AND date(course_date) <= "' . date('Y-m-d', strtotime($end)) . '"';
				}
				else
				{
					$datecond = '1';
				}

				if($only_count === false)
				{
					$patient = Doctrine_Query::create()
						->select("*")
						->from('VoluntaryworkersCourse')
						->where('vw_id ="' . $vw_id . '"');
						if(!empty($letter_arr)){
						    $patient->andWhereIn('course_type',$letter_arr);
						}
						$patient->andWhere(" course_type  in (" . $ct . ")")
						->andWhere($datecond)
    					->orderBy('DATE_FORMAT(' . $sort_field . ', "%Y-%m-%d %H:%i") ' . $sort_direction . ', wrong ASC, ' . $sort_field . ' ASC');
					if($limit)
					{
						$patient->limit($limit);
						$patient->offset($offset);
					}
					$patientarray = $patient->fetchArray();
					//print_r($patientarray);exit();
					foreach($patientarray as $key => $value)
					{
						$course_style[] = $value['course_type'];
					}

					$course_style[] = '999999'; //prevent fehler on empty array

// 					$course_styles = Courseshortcuts::getCourseMultipleDataByShortcut($course_style);

					$i = 0;
					foreach($patientarray as $key => $value)
					{

						$coursearr[0] = $course_styles[$value['course_type']];

						$cdate = date("Y-m-d H:i", strtotime($value[$sort_field]));
						$uid = $value['user_id'];
						$block[$key] = $value;
						$block[$key]['font_color'] = $coursearr[0]['font_color'];
						$block[$key]['isbold'] = $coursearr[0]['isbold'];
						$block[$key]['isitalic'] = $coursearr[0]['isitalic'];
						$block[$key]['isunderline'] = $coursearr[0]['isunderline'];


						//block keys
						$block_key = date("Y-m-d H:i", strtotime($value[$sort_field])) . '_' . $uid . '_' . $value['wrong'];
						$next_block_key = date("Y-m-d H:i", strtotime($patientarray[$key + 1][$sort_field])) . '_' . $patientarray[$key + 1]['user_id'] . '_' . $patientarray[$key + 1]['wrong'];

						//if ($cdate != date("Y-m-d H:i", strtotime($patientarray[$key + 1][$sort_field])) || $uid != $patientarray[$key + 1]['user_id'])
						if($block_key != $next_block_key)
						{
							$tempblocks['summary'] = $block;
							$tempblocks['date'] = $cdate;
							$tempblocks['user'] = $uid;
							$tempblocks['wrong'] = $value['wrong'];
							$tempblocks['source_vw_id'] = $value['source_vw_id'];
							$tempblocks['cid'] = $value['id'];
							$tempblocks['wrongcomment'] = $value['wrongcomment'];
							$tempblocks['date_dt'] = date('d.m.Y', strtotime($cdate));
							$tempblocks['date_hm'] = date('H:i', strtotime($cdate));
							$tempblocks['c_date_dt'] = date('d.m.Y', strtotime($value['course_date']));
							$tempblocks['c_date_hm'] = date('H:i', strtotime($value['course_date']));
							$tempblocks['full_course_date'] = date('d.m.Y H:i', strtotime($value['course_date']));
							$tempblocks['full_done_date'] = date('d.m.Y H:i', strtotime($value['done_date']));
							$allblocks[] = $tempblocks;
							$block = array();
							$tempblocks = array();
						}
					}
				}
				else
				{
					$patient = Doctrine_Query::create()
						->select("count(*)")
						->from('VoluntaryworkersCourse')
						->where('vw_id ="' . $vw_id . '"');
						if(!empty($letter_arr)){
						    $patient->andWhereIn('course_type',$letter_arr);
					    }
					   $patient->andWhere("course_type in (" . $ct . ")")
						->andWhere($datecond)
						->orderBy($sort_field . ' ' . $sort_direction );
					$allblocks = $patient->fetchArray();
				}

				return $allblocks;
			}
		}

		public function getCourseDataByShortcut($ipid, $shrt, $explodeCourseTitle = true, $hide_wrong = false)
		{

			$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('VoluntaryworkersCourse')
				->where("ipid='" . $ipid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");
				if($hide_wrong)
			{
				$qpa1->andWhere('wrong="0"');
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
				->from('VoluntaryworkersCourse')
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
				->from('VoluntaryworkersCourse')
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
				->from('VoluntaryworkersCourse')
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
				->from('VoluntaryworkersCourse')
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
					$pc = new VoluntaryworkersCourse();
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
					$pc->save();
				}
			}
		}

		public function get_patient_shortcuts_course($ipid, $shortcuts = false, $curent_period = false, $include_deleted = false)
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
				->from('VoluntaryworkersCourse')
				->where('ipid LIKE "' . $ipid . '"');
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

			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}

		public function get_deleted_contactforms($ipids = false, $ipids_key = true)
		{
			if($ipids)
			{
				$deleted_cf = Doctrine_Query::create()
					->select("*,AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('VoluntaryworkersCourse')
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
					->from('VoluntaryworkersCourse')
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
				->from('VoluntaryworkersCourse')
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
    				->from('VoluntaryworkersCourse')
    				->whereIn('ipid',$ipids)
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
				->from('VoluntaryworkersCourse')
				->whereIn("ipid", $ipids)
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
				->from('VoluntaryworkersCourse');

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

			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}
		
		public function get_multi_pat_shortcuts_course($ipids, $shortcuts = false, $curent_period = false, $include_deleted = false)
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
				->from('VoluntaryworkersCourse')
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

			$course_res = $course_data->fetchArray();

			if($course_res)
			{
				return $course_res;
			}
		}
				
		
	}

?>