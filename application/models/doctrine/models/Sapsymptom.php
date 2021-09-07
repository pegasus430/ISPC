<?php

	Doctrine_Manager::getInstance()->bindComponent('Sapsymptom', 'MDAT');

	class Sapsymptom extends BaseSapsymptom {

		function getentrycount($date, $ipid,$received_course = false)
		{
// 			$get_shared_from_patient_course = PatientCourse::getCourseDataReceived(array($ipid));
// 			if(!empty($get_shared_from_patient_course)){
// 				$received_course = array();
// 				foreach($get_shared_from_patient_course as $k=>$rc){
// 					$received_course[$rc['ipid']][] = $rc['id'];
// 				} 
// 			}
			
			
			
			$sp = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where("ipid ='" . $ipid . "' and create_date between '" . $date . " 00:00:00' and '" . $date . " 23:59:59'")
				->andWhere('isdelete = 0');
				if(!empty($received_course)){
					$sp->andWhereNotIn('patient_course_id',$received_course);
				}
			$spexec = $sp->execute();
			//echo $sp->getSqlQuery();
			$sparr = $spexec->toArray();
			return $sparr;
		}

		function getEntryInRangeCount($dateStart, $dateEnd, $ipid)
		{
			
			$get_shared_from_patient_course = PatientCourse::getCourseDataReceived(array($ipid));
			if(!empty($get_shared_from_patient_course)){
				$received_course = array();
				foreach($get_shared_from_patient_course as $k=>$rc){
					$received_course[$rc['ipid']][] = $rc['id'];
				}
			}
				
			
			$sp = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where("ipid ='" . $ipid . "' and create_date between '" . $dateStart . " 00:00:00' and '" . $dateEnd . " 23:59:59'")
				->andWhere('isdelete = 0');
				if(!empty($received_course[$ipid])){
					$sp->andWhereNotIn('patient_course_id',$received_course[$ipid]);
				}
			$spexec = $sp->execute();
			if($_REQUEST['dbg'] == "1")
			{
				print_r("\n\n");
				print_r($sp->getSqlQuery());
				print_r("\n\n");
			}
			$sparr = $spexec->toArray();
			return $sparr;
		}

		function get_patient_sapvsymptom($ipid)
		{
			
			$get_shared_from_patient_course = PatientCourse::getCourseDataReceived(array($ipid));
			if(!empty($get_shared_from_patient_course)){
				$received_course = array();
				foreach($get_shared_from_patient_course as $k=>$rc){
					$received_course[$rc['ipid']][] = $rc['id'];
				}
			}
				
			
			$sp = Doctrine_Query::create()
				->select('*')
				->from('Sapsymptom')
				->where("ipid ='" . $ipid . "'")
				->andWhere('isdelete = 0');
				if(!empty($received_course[$ipid])){
					$sp->andWhereNotIn('patient_course_id',$received_course[$ipid]);
				}
			$sparr = $sp->fetchArray();
			
			return $sparr;
		}

	}

?>