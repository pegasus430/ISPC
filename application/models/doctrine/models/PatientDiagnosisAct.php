<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDiagnosisAct', 'MDAT');

	class PatientDiagnosisAct extends BasePatientDiagnosisAct {

		public function get_active_act($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientDiagnosisAct')
				->where("ipid='" . $ipid . "'")
				->andWhere("isdelete = 0")
			    ->limit('1');
			$drop_arr = $drop->fetchArray();
			
			return $drop_arr ;
		}
		

		public function get_today_active_acts($ipids)
		{
		    if(is_array($ipids))
		    {
		        $act_ipids = $ipids;
		    }
		    else
		    {
		        $act_ipids = array($ipids);
		    }
		
		    $sapv = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientDiagnosisAct')
		    ->whereIn('ipid', $act_ipids)
		    ->andWhere('isdelete = 0') ;
		    $sapv_res = $sapv->fetchArray();
		
		    if($sapv_res)
		    {
		        return $sapv_res;
		    }
		    else
		    {
		        return false;
		    }
		}
	}
?>