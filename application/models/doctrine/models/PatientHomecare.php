<?php

Doctrine_Manager::getInstance()->bindComponent('PatientHomecare', 'SYSDAT');

class PatientHomecare extends BasePatientHomecare 
{

		public function getPatientHomecare($ipid, $homeid = false)
		{
			$sql = "*, id as home_id";
			$sql .=",homecare as ho_homecare";
			$sql .=",home_comment as ho_com";
			$sql .=",phone_practice as ho_phone_practice";
			$sql .=",phone_emergency as phone_emergency";
			//$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			//$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as ho_fax";

			if($homeid)
			{
				$q = "ph.homeid = " . $homeid . " and";
			}
			else
			{
				$q = "";
			}

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Homecare h ')
				->leftJoin('PatientHomecare ph')
				->where("" . $q . " ph.homeid = h.id and ph.ipid='" . $ipid . "' and ph.isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		public function get_homecare_patients($homeid)
		{
			$find_home = Doctrine_Query::create()
				->select('*')
				->from('PatientHomecare')
				->where('homeid = "'.$homeid.'"')
				->andWhere('isdelete = "0"');
			$find_home_res = $find_home->fetchArray();
			
			if($find_home_res)
			{
				return $find_home_res;
			}
			else
			{
				return false;
			}
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			//get patient Homecare
			$home = $this->getPatientHomecare($ipid);
			if($home)
			{
				foreach($home as $k_home => $v_home)
				{
					$homecare = new Homecare();
					$master_home = $homecare->clone_record($v_home['id'], $target_client);

					if($master_home)
					{
						$home_cl = new PatientHomecare();
						//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
						$pc_listener = $home_cl->getListener()->get('IntenseConnectionListener');
						$pc_listener->setOption('disabled', true);
						//--
						$home_cl->ipid = $target_ipid;
						$home_cl->homeid = $master_home;
						$home_cl->home_comment = $v_home['ho_com'];
						$home_cl->save();
						//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
					}
				}

				return $home_cl->id;
			}
		}
		
		public function get_patient_homecares($ipids, $fetch_master_data = false)
		{
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			if(count($ipids_arr) == 0)
			{
				$ipids_arr[] = '9999999999';
			}

			$Q = Doctrine_Query::create()
				->select('*')
				->from('PatientHomecare')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('create_date DESC');
			$q_res = $Q->fetchArray();

			if($q_res)
			{
				if($fetch_master_data)
				{
					$homecare_ids[] = '999999999999';
					foreach($q_res as $k_res => $v_res)
					{
						$patient_homecare_details[$v_res['homeid']] = $v_res;
						$homecare_ids[] = $v_res['homeid'];
					}

					$homecare = new Homecare();
					$homecare_details = $homecare->get_homecares($homecare_ids);

					foreach($homecare_details as $k_homecare => $v_homecare)
					{
						$patient_homecare_details[$v_homecare['id']]['master'] = $v_homecare;
					}

					if($patient_homecare_details)
					{
						return $patient_homecare_details;
					}
				}
				else
				{
					return $q_res;
				}
			}
			else
			{
				return false;
			}
		}

	public static function beautifyName( &$usrarray )
	{
	    //mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
	    if ( empty($usrarray) || ! is_array($usrarray)) {
	        return;
	    }
	
	
	    foreach ( $usrarray as &$k )
	    {
	
	        if ( ! is_array($k) || isset($k['nice_name'])) {
	            continue; // varaible allready exists, use another name for the variable
	        }
	        if (isset($k['Homecare'])){
	            $k ['nice_name']  = trim($k['Homecare']['last_name']);
	            $k ['nice_name'] .= trim($k['Homecare']['first_name']) != "" ? (", " . trim($k['Homecare']['first_name'])) : "";
	        } else {
	            $k ['nice_name']  = trim($k['last_name']);
	            $k ['nice_name'] .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}
	
}

?>