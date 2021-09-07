<?php

Doctrine_Manager::getInstance()->bindComponent('PatientPhysiotherapist', 'SYSDAT');

class PatientPhysiotherapist extends BasePatientPhysiotherapist 
{

		public function getPatientPhysiotherapist($ipid, $phyid = false)
		{
			$sql = "*, id as pf_id";
			$sql .=",physiotherapist as phy_physiotherapist";
			$sql .=",physio_comment as ph_com";
			$sql .=",phone_practice as pf_phone_practice";
			$sql .=",phone_emergency as phone_emergency";
			//$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			//$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as pf_fax";

			if($phyid)
			{
				$q = "pp.physioid = " . $phyid . " and";
			}
			else
			{
				$q = "";
			}

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Physiotherapists p ')
				->leftJoin('PatientPhysiotherapist pp')
				->where("" . $q . " pp.physioid = p.id and pp.ipid='" . $ipid . "' and pp.isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		public function get_physiotherapist_patients($physioid)
		{
			$find_physio = Doctrine_Query::create()
				->select('*')
				->from('PatientPhysiotherapist')
				->where('physioid = "'.$physioid.'"')
				->andWhere('isdelete = "0"');
			$find_physio_res = $find_physio->fetchArray();
			
			if($find_physio_res)
			{
				return $find_physio_res;
			}
			else
			{
				return false;
			}
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			//get patient Physiotherapists
			$physio = $this->getPatientPhysiotherapist($ipid);
			if($physio)
			{
				foreach($physio as $k_phy => $v_phy)
				{
					$physiotherapist = new Physiotherapists();
					$master_phy = $physiotherapist->clone_record($v_phy['id'], $target_client);

					if($master_phy)
					{
						$phy_cl = new PatientPhysiotherapist();
						//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
						$pc_listener = $phy_cl->getListener()->get('IntenseConnectionListener');
						$pc_listener->setOption('disabled', true);
						//--
						$phy_cl->ipid = $target_ipid;
						$phy_cl->physioid = $master_phy;
						$phy_cl->physio_comment = $v_phy['physio_comment'];
						$phy_cl->save();
						//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
					}
				}

				return $phy_cl->id;
			}
		}
		
		public function get_patient_physiotherapists($ipids, $fetch_master_data = false)
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
				->from('PatientPhysiotherapist')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('create_date DESC');
			$q_res = $Q->fetchArray();

			if($q_res)
			{
				if($fetch_master_data)
				{
					$physio_ids[] = '999999999999';
					foreach($q_res as $k_res => $v_res)
					{
						$patient_physio_details[$v_res['physioid']] = $v_res;
						$physio_ids[] = $v_res['physioid'];
					}

					$physio = new Physiotherapists();
					$physio_details = $physio->get_physiotherapists($physio_ids);

					foreach($physio_details as $k_physio => $v_physio)
					{
						$patient_physio_details[$v_physio['id']]['master'] = $v_physio;
					}

					if($patient_physio_details)
					{
						return $patient_physio_details;
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
	        if (isset($k['Physiotherapists'])){
	            $k ['nice_name']  = trim($k['Physiotherapists']['title']) ? (trim($k['Physiotherapists']['title']) . " ") : "";
	            $k ['nice_name'] .= trim($k['Physiotherapists']['last_name']);
	            $k ['nice_name'] .= trim($k['Physiotherapists']['first_name']) != "" ? (", " . trim($k['Physiotherapists']['first_name'])) : "";
	        } else {
	            $k ['nice_name']  = trim($k['title']) ? (trim($k['title']) . " ") : "";
	            $k ['nice_name'] .= trim($k['last_name']);
	            $k ['nice_name'] .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}
}

?>