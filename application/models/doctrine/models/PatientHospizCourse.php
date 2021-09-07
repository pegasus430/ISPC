<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientHospizCourse', 'MDAT');

	class PatientHospizCourse extends BasePatientHospizCourse {

		public function getHospizCourseData($pid)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($pid)
			{
				$ipid = Pms_CommonData::getIpid($pid);
				$epid = Pms_CommonData::getEpid($ipid);

				$patient = Doctrine_Query::create()
					->select("*,AES_DECRYPT(course_short,'" . Zend_Registry::get('salt') . "') as course_short,
				     AES_DECRYPT(course_long,'" . Zend_Registry::get('salt') . "') as course_long")
					->from('PatientHospizCourse')
					->where('ipid ="' . $ipid . '"')
					->orderBy('course_date ASC');
				$patientarray = $patient->fetchArray();

				if(!empty($patientarray))
				{
					return $patientarray;
				}
			}
		}

		public function getCourseDataByShortcut($ipid, $shrt)
		{
			$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
				              AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
				->from('PatientCourse')
				->where("ipid='" . $ipid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt($shrt)) . "'");

			$qp1 = $qpa1->execute();

			if($qp1)
			{
				$newarr1 = $qp1->toArray();
				for($i = 0; $i < count($newarr1); $i++)
				{
					$rem = explode("|", $newarr1[$i]['course_title']);
					$newarr1[$i]['course_title'] = $rem[0];
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
				->where("wrong !=1 and user_id='" . $userid . "' and course_type='" . addslashes(Pms_CommonData::aesEncrypt("L")) . "' " . $where);//Ancuta 08.04.2020 MYSQL GONE Bug fix [use wrong instead of wrongcomment]
			$qp1 = $qpa1->execute();

			if($qp1)
			{
				$newarr1 = $qp1->toArray();

				return $newarr1;
			}
		}

		function getipidfromclientid($clientid)
		{
			$lastipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where("clientid = " . $clientid);

			$lastipid->getSqlQuery();
			$lastipidexec = $lastipid->execute();
			$lastipidarray = $lastipidexec->toArray();

			$comma = ",";
			$newipidval = "'0'";

			foreach($lastipidarray as $key => $val)
			{
				$newipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			$actipid = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster')
				->where("ipid in (" . $newipidval . ") and isdelete=0 and isdischarged=0");
			$actipid->getSqlQuery();
			$actipidexec = $actipid->execute();
			$actipidarray = $actipidexec->toArray();

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

	}

?>