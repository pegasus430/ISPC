<?php

Doctrine_Manager::getInstance()->bindComponent('PatientReadmission', 'IDAT');

class PatientReadmission extends BasePatientReadmission 
{

		public function getPatientReadmission($ipid, $date_type)
		{
			if(is_array($ipid) && sizeof($ipid) > 0)
			{
				foreach($ipid as $ipid_single)
				{
					$ipid_str .= '"' . $ipid_single . '",';
				}
				$ipid_sql = 'ipid IN (' . substr($ipid_str, 0, -1) . ')';
			}
			else
			{
				$ipid_sql = "ipid='" . $ipid . "'";
			}

			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientReadmission');
			if(is_array($ipid) && sizeof($ipid) > 0)
			{
				$loc->whereIn('ipid', $ipid);
			}
			else
			{
				$loc->where("ipid='" . $ipid . "'");
			}
			$loc->andWhere("date_type='" . $date_type . "'")
				->orderBy("ipid, date ASC");
			$disarr = $loc->fetchArray();

			if($disarr)
			{
				return $disarr;
			}
		}

		public function getPatientReadmissionAll($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientReadmission')
				->where("ipid='" . $ipid . "'")
				->orderBy("date ASC");
			$disarr = $loc->fetchArray();

			if($disarr)
			{
				foreach($disarr as $k=>$readm){
					$readmission_details[$k] = $readm; 
					$readmission_details[$k]['status_period'] = 'active'; 
				} 
				
				return $readmission_details;
			}
		}

		public function get_patient_readmission_all($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientReadmission')
				->where("ipid='" . $ipid . "'")
				->orderBy("id, date ASC");
			$disarr = $loc->fetchArray();

			if($disarr)
			{
				foreach($disarr as $k=>$readm){
					$readmission_details[$k] = $readm; 
					$readmission_details[$k]['status_period'] = 'active'; 
				} 
				
				return $readmission_details;
			}
		}

		public function getPatientLastDischargedate($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*, discharge_date as date")
				->from('PatientDischarge')
				->where("ipid='" . $ipid . "'")
				->orderBy("date DESC")
				->limit(1);
			$disarr = $loc->fetchArray();

			if($disarr)
			{
				return $disarr;
			}
		}

		public function get_patient_previous_dischargedate($ipid)
		{
		    
		    $discharge_sql = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientDischarge')
		    ->where("ipid='" . $ipid . "'")
		    ->andWhere("isdelete = 0 ")
		    ->orderBy("discharge_date DESC")
		    ->limit(1);
		    $discharge_arr = $discharge_sql->fetchArray();
		    
		    if(sizeof($discharge_arr) > 0)
		    {
		        $readmision_dischargedate = $discharge_arr[0]['discharge_date'];
		    }
		    else
		    {
		        $readmision_dischargedate = date('Y-m-d H:i:s');
		    }
		    
		    $loc = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientReadmission')
		    ->where("ipid='" . $ipid . "' and date_type = '2' ")
		    ->andwhere('date < "' . $readmision_dischargedate . '"')
		    ->orderBy("date DESC")
		    ->limit(1);
		    $disarr = $loc->fetchArray();
		    
		    if($disarr)
		    {
		        $previous_discharge_date = date('Y-m-d H:i:s', strtotime($disarr[0]['date']));
		        return  $previous_discharge_date;
		    }

		}
		
		public function get_patient_admissions($ipids)
		{
			
			$ipids_arr[] = '999999999999';
			
			if(is_array($ipids) && count($ipids) >'0')
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr[] = $ipids;
			}
			
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientReadmission')
				->where('date_type = "1"')
				->andWhereIn("ipid", $ipids_arr)
				->orderBy("date DESC");
			$admissions = $loc->fetchArray();

			if($admissions)
			{
				foreach($admissions as $k_adm=>$v_adm)
				{
					$patients_admissions[$v_adm['ipid']][date('Y-m-d', strtotime($v_adm['date']))] = $v_adm['date'];
				}
					
				return $patients_admissions;
			}
		}
		
		
	
    /**
     * this was created for a single ipid
     * ! not to be used on intensive foreach
     * call findFallsOfIpids for multiple
     * 
     * returns ordered 
     * fall[0][admission] = row
     * fall[0][discharge] = row
     * fall[1]..
     * 
     * 
     * @param string $ipid
     * @param bool $multiple_ipids - will return groupped by ipid
     * @return multitype groupped falls
     */
	public static function findFallsOfIpid($ipid = '', $multiple_ipids = false)
	{
	    $result = array();
	    
	    if (empty($ipid)) {
	        return $result;
	    }
	    
	    $ipids = is_array($ipid) ? $ipid : array($ipid);
	    
	    $qr = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientReadmission')
	    ->whereIn("ipid", $ipids)
	    ->orderBy("date, id ASC")
	    ->fetchArray();
	   
	    $unordered_falls = array();
	    
	    //first group by ipid
	    foreach ($qr as $row) {
	        $unordered_falls[$row['ipid']][] = $row;   
	    } 
	    
	    
	    foreach ($unordered_falls as $ipid => $qr) {
	    
	        $falls = array();
	        
	        $x = 0;
	        $y = 0;
	         
	        
    	    foreach ($qr as $row) {
    	        
    	        if ($row['date_type'] == 1) {
    
    	            $falls [$y] ['admission'] = $row;
    	            $x++;
    	            
    	        } elseif ($row['date_type'] == 2) {
    	            
    	            $falls [$y] ['discharge'] = $row;
    	            
    	            if ( ! isset($falls [$y] ['admission']) 
    	                || $falls [$y] ['admission']['date'] > $falls [$y] ['discharge']['date']) 
    	            {
    	                //log error.. something went wrong.. you cannot be discharged before admission
    	                self::_log_error( __METHOD__ . " please check, something went wrong with calculating this ipid :  {$ipid}, it cannot be discharged before admission");
    	                 
    	            } else {
        	            $y++;
    	                
    	            }   
    	        }   
    	    }
    	    
    	    
    	    $result[$ipid] = $falls;
    	    
    	    if ($y > $x) {
    	        //what went wrong ?
    	        //log error
    	        self::_log_error( __METHOD__ . " please check, something went wrong with calculating this ipid : {$ipid}");
    	    }
	    }

	    if ($multiple_ipids !== true) {
	        $result = reset($result);
	    }
	    	    
	    return $result;
	}
	

	/**
	 * 
	 * @param array $ipids
	 */
	public static function findFallsOfIpids($ipids = array())
	{
	    return self::findFallsOfIpid($ipids, true);
	}
	

	/**
	 * find intervall that intersects $date, and return that 
	 * 
	 * @param string $ipid
	 * @param string $date
	 * @return unknown|Ambigous <multitype:, unknown, multitype>
	 */
	public static function findReadmissionFromDate($ipid = '', $date = "")
	{
	    if (empty($ipid)) {
	        return $result;
	    }
	    
	    $result = [];

	    $date = empty($date) ? strtotime("now") : strtotime($date);
	    
	    $findFallsOfIpid = self::findFallsOfIpid($ipid);
	    
	    foreach ($findFallsOfIpid as $fall) {
	        
	        $admissionDate = empty($fall['admission']['date']) ? 0 : strtotime($fall['admission']['date']);
	        $dischargeDate = empty($fall['discharge']['date']) ? 0 : strtotime($fall['discharge']['date']);
	        
	        if ( empty($dischargeDate) && $admissionDate <= $date) {
	            //current fall, is active,
    	        $result = $fall;
    	        break 1;
    	            	    
    	    } else if ($admissionDate <= $date && $date <= $dischargeDate) {
    	        $result = $fall;
    	        break 1;
    	    } else {
    	        //error_day_notinrange
    	    }
	    }
	    
	    return $result;
	    
	}
	

	//Maria:: Migration CISPC to ISPC 22.07.2020
    public static function getActiveIpids(){
        $logininfo= new Zend_Session_Namespace('Login_Info');

        $patient = Doctrine_Query::create()
            ->select('p.ipid')
            ->from('PatientMaster p')
            ->where('p.isdelete = 0')
            ->andWhere('p.isdelete = 0 AND p.isstandbydelete=0 AND p.isdischarged = 0 AND p.isstandby = 0 AND p.isarchived = 0');
        $patient->leftJoin("p.EpidIpidMapping e");
        $patient->andWhere('e.clientid = '.$logininfo->clientid);

        $patient_arr=$patient->fetchArray();

        $ipids=array();
        foreach($patient_arr as $pat){
            $ipids[]=$pat['ipid'];
        }

        return array_unique($ipids);
    }
	
}

?>