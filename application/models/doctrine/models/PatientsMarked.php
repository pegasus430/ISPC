<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientsMarked', 'SYSDAT');

	class PatientsMarked extends BasePatientsMarked {

	    /**
	     * @param unknown $source
	     * @param boolean $status
	     * @param boolean $allow_only_intense
	     * @return string|Zend_View_Helper_Translate
	     * //ISPC-2614 Ancuta 15.07.2020 - added a new param $allow_only_intense
	     */
	    public function sent_requests_get($source, $status = false,$allow_only_intense = false)
		{
			$helper = new Zend_View_Helper_Translate();

			$statuses = array('p' => 'share_pending', 'a' => 'share_accept', 'c' => 'canceled');

			$sp = Doctrine_Query::create()
				->select('*')
				->from('PatientsMarked')
				->where('source = "' . $source . '"');
			if($status && array_key_exists(strtolower($status), $statuses))
			{
				$sp->andWhere('status = "' . $status . '"');
			}
		    //ISPC-2614 Ancuta 15.07.2020
			if($allow_only_intense){ 
			    $sp->andWhere('intense_system is NOT NULL');
			}
			//
			$sp->orderBy("id DESC");
			$s_patients = $sp->fetchArray();

			foreach($s_patients as $k_spatient => $v_spatient)
			{
				$shared_patients[$v_spatient['id']] = $v_spatient;
				$shared_patients[$v_spatient['id']]['status_l'] = $v_spatient['status'];
				$shared_patients[$v_spatient['id']]['status'] = $helper->translate($statuses[$v_spatient['status']]);
			}

			return $shared_patients;
		}

		/**
		 * @param unknown $target
		 * @param boolean $status
		 * @param boolean $allow_only_intense
		 * @return string|Zend_View_Helper_Translate
		 * //ISPC-2614 Ancuta 15.07.2020 - added a new param $allow_only_intense
		 */
		public function received_requests_get($target, $status = false,$allow_only_intense = false)
		{
			$helper = new Zend_View_Helper_Translate();

			$statuses = array('p' => 'share_pending', 'a' => 'share_accept', 'c' => 'canceled');
			$rp = Doctrine_Query::create()
				->select('*')
				->from('PatientsMarked')
				->where('target = "' . $target . '"')
				->andWhere('status != "c"');
			if($status && array_key_exists(strtolower($status), $statuses))
			{
				$rp->andWhere('status = "' . $status . '"');
			}
			//ISPC-2614 Ancuta 15.07.2020
			if($allow_only_intense){
			    $rp->andWhere('intense_system = 1');
			}
			//
			$rp->orderBy("id DESC");
			$r_patients = $rp->fetchArray();

			foreach($r_patients as $k_rpatient => $v_rpatient)
			{
				$received_patients[$v_rpatient['id']] = $v_rpatient;
				$received_patients[$v_rpatient['id']]['status_l'] = $v_rpatient['status'];
				$received_patients[$v_rpatient['id']]['status'] = $helper->translate($statuses[$v_rpatient['status']]);
			}

			if(isset($received_patients))
			{
				return $received_patients;
			}
		}

		public function change_status($sid, $status = 'c')
		{
			$cstatus = Doctrine_Query::create()
				->update("PatientsMarked")
				->set('status', '"' . $status . '"')
				->where('id = "' . $sid . '"');
			$cs = $cstatus->execute();

			if($cs)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function share_get($sid, $clientid = false)
		{
			$share = Doctrine_Query::create()
				->select('*')
				->from('PatientsMarked')
				->where('id = "' . $sid . '"');
			if($clientid)
			{
				$share = $share->andWhere('target = "' . $clientid . '"');
			}

			$res = $share->fetchArray();

			return $res;
		}

		public function allowed_target_shortcuts($source, $target, $source_shortcuts)
		{
			$allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');

			//get source shortcuts
			$courses = new Courseshortcuts();
			$courses_source = $courses->getClientShortcuts($source);

			foreach($courses_source as $course_s)
			{
				/* if(in_array($course_s['shortcut'], $allowed_shortcuts))
				{
					$courses_source_sorted[$course_s['shortcut']] = $course_s;
				} */
				$courses_source_sorted[$course_s['shortcut']] = $course_s;
				$courses_source_sh[] = $course_s['shortcut'];
			}

			ksort($courses_source_sorted);

			//get target shortcuts
			$courses_target = $courses->getClientShortcuts($target);

			foreach($courses_target as $course)
			{
				/* if(in_array($course['shortcut'], $allowed_shortcuts))
				{
					$courses_target_sorted[$course['shortcut']] = $course;
				} */
				if(in_array($course['shortcut'], $courses_source_sh))
				{
					$courses_target_sorted[$course['shortcut']] = $course;
				}
			}

			ksort($courses_target_sorted);

			$target_allowed['source_shortcuts'] = $courses_source_sorted;
			$target_allowed['target_shortcuts'] = $courses_target_sorted;

// 			print_r($target_allowed);
// 			 exit;
// 			print_r($source_shortcuts);
			if(!empty($source_shortcuts))
			{
				foreach($courses_source as $kc_source => $vc_source)
				{
					if(in_array($vc_source['shortcut_id'], $source_shortcuts))
					{
						$source_courses[] = $vc_source['shortcut'];
					}
				}

				if(!empty($source_courses))
				{
					foreach($courses_target_sorted as $kc_target => $vc_target)
					{
						if(in_array($vc_target['shortcut'], $source_courses))
						{
							$target_allowed['allowed_shortcuts'][$vc_target['shortcut_id']] = $vc_target['shortcut'];
						}
					}

					return $target_allowed;
				}
				else
				{
					return $target_allowed;
				}
			}
			else
			{
				return $target_allowed;
			}
		}

		public function share_update($sid, $post)
		{
			$share_u = Doctrine::getTable('PatientsMarked')->findOneById($sid);

			if($share_u)
			{
				$share_u->target = $post['target_client'];
				if(!empty($post['patientid']))
				{
					$share_u->ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($post['patientid']));
				}
				$share_u->copy = $post['allow_copy'];
				$share_u->copy_options = implode(',', $post['copy_options']);
				$share_u->copy_files = $post['copy_files'];
				$share_u->request = $post['request_share'];
				$share_u->shortcuts = implode(',', $post['shortcut']);
				$share_u->save();
			}

			if($share_u->id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

	}

?>