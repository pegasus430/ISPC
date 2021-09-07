<?php

	Doctrine_Manager::getInstance()->bindComponent('Rpassessment', 'MDAT');

	class Rpassessment extends BaseRpassessment {

		public function get_patient_last_rpassessment($ipid, $isclosed=false)
		{
			$rp_assessment = Doctrine_Query::create()
				->select("*")
				->from('Rpassessment')
				->where("ipid='" . $ipid . "'");
				if(!$isclosed)
				{
					$rp_assessment->andWhere("isclosed = '0'");
				}
				$rp_assessment->orderBy('id DESC');
				$rp_assessment->limit('1');
			$rp_assessmentarray = $rp_assessment->fetchArray();

			if($rp_assessmentarray)
			{
				return $rp_assessmentarray[0];
			}
			else
			{
				return false;
			}
		}

		public function get_patient_all_rpassessment($ipid)
		{
			$rp_assessment = Doctrine_Query::create()
				->select("id, iscompleted, DATE(completed_date) as completed_date")
				->from('Rpassessment')
				->where("ipid='" . $ipid . "'");
			$rp_assessmentarray = $rp_assessment->fetchArray();

			return $rp_assessmentarray;
		}

		public function get_patient_completed_rpassessment($ipid, $period = false)
		{
			$rp_assessment = Doctrine_Query::create()
				->select("id, iscompleted, DATE(completed_date) as completed_date")
				->from('Rpassessment')
				->where("ipid LIKE '" . $ipid . "'")
				->andWhere('iscompleted = "1"');
				if($period)
				{
					$rp_assessment->andWhere('DATE(completed_date) BETWEEN DATE("'.$period['start'].'") AND DATE("'.$period['end'].'")');
				}
			$rp_assessmentarray = $rp_assessment->fetchArray();

			return $rp_assessmentarray;
		}
		
		public function get_patients_completed_rpassessment($ipids,$sapv_invoiced_period_days = false)
		{
			
			
			
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid_arr = $ipids;
				}
			}
			else
			{
				$ipid_arr = array($ipids);
			}
			if(!empty($ipid_arr)){
				
				$rp_assessment = Doctrine_Query::create()
					->select("ipid,id, iscompleted, DATE(completed_date) as completed_date")
					->from('Rpassessment')
					->whereIn("ipid",$ipids)
					->andWhere('iscompleted = "1"');
					$rp_assessmentarray = $rp_assessment->fetchArray();

					foreach($rp_assessmentarray as $k => $rp_data){
						if(in_array($rp_data['completed_date'],$sapv_invoiced_period_days[$rp_data['ipid']])){
							$rp_assessment_arr[$rp_data['ipid']][]  = $rp_data;
						}
					}
					
					if($rp_assessment_arr){
						return $rp_assessment_arr;
					} else {
						return false;
					}
			} 
			else
			{
				return false;
			}
		}

	}

?>