<?php

Doctrine_Manager::getInstance()->bindComponent('PatientSupplies', 'SYSDAT');

class PatientSupplies extends BasePatientSupplies 
{

		public function getPatientSupplies($ipid, $supplier_id = false)
		{
			$sql = "*, id as m_supplier_id";
			$sql .=",supplier as m_supplier";
			$sql .=",supplier_comment as supplier_comment";
			$sql .=",street1 as m_supplier_street";
			$sql .=",zip as m_supplier_zip";
			$sql .=",city as m_supplier_city";
			$sql .=",phone as m_supplier_phone";
			$sql .=",fax as m_supplier_fax";

			if($supplier_id)
			{
				$q = "PatientSupplies.supplier_id = " . $supplier_id . " and";
			}
			else
			{
				$q = "";
			}
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Supplies')
				->leftJoin('PatientSupplies')
				->where("" . $q . " PatientSupplies.supplier_id = Supplies.id and PatientSupplies.ipid='" . $ipid . "' and PatientSupplies.isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatientLastSupplies($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientSupplies')
				->where("ipid='" . $ipid . "' and isdelete = 0")
				->orderBy('id desc')
				->limit(1);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function get_patients_supplies($ipids)
		{
			$drop = Doctrine_Query::create()
				->select('s.*, ps.*')
				->from('PatientSupplies ps')
				->leftJoin('ps.Supplies s')
				->where("ps.supplier_id = s.id")
				->andWhere('ps.isdelete = "0"')
				->andWhereIn('ps.ipid', $ipids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$pfleges = $this->getPatientSupplies($ipid);
			if($pfleges)
			{
				foreach($pfleges as $k_pfl => $v_pfl)
				{
					$pfl = new Supplies();
					$master_pfl = $pfl->clone_record($v_pfl['id'], $target_client);

					if($master_pfl)
					{
						$pfl_cl = new PatientSupplies();
						//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
						$pc_listener = $pfl_cl->getListener()->get('IntenseConnectionListener');
						$pc_listener->setOption('disabled', true);
						//--
						$pfl_cl->ipid = $target_ipid;
						$pfl_cl->supplier_id = $master_pfl;
						$pfl_cl->supplier_comment = $v_pfl['supplier_comment'];
						$pfl_cl->save();
						//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
					}
				}

				return $pfl->id;
			}
		}

		public function get_patient_supplies($ipids, $fetch_master_data = false)
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
				->from('PatientSupplies')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$q_res = $Q->fetchArray();

			if($q_res)
			{
				if($fetch_master_data)
				{
					$supplies_ids[] = '999999999999';
					foreach($q_res as $k_res => $v_res)
					{
						$patient_supplies_details[$v_res['supplier_id']] = $v_res;
						$supplies_ids[] = $v_res['supplier_id'];
					}

					$suppliers = new Supplies();
					$supplies_details = $suppliers->get_supplies($supplies_ids);

					foreach($supplies_details as $k_supplies => $v_supplies)
					{
						$patient_supplies_details[$v_supplies['id']]['master'] = $v_supplies;
					}

					if($patient_supplies_details)
					{
						return $patient_supplies_details;
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
		
		
		public function getPatientSuppliesPrettylist(){
		    
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
	
	        if (isset($k['Supplies'])){
	            $k ['nice_name']  = trim($k['Supplies']['title']) != "" ? trim($k['Supplies']['title']) . " " : "";
	            $k ['nice_name']  .= trim($k['Supplies']['last_name']);
	            $k ['nice_name']  .= trim($k['Supplies']['first_name']) != "" ? (", " . trim($k['Supplies']['first_name'])) : "";
	        } else {
	            $k ['nice_name']  = trim($k['title']) != "" ? trim($k['title']) . " " : "";
	            $k ['nice_name']  .= trim($k['last_name']);
	            $k ['nice_name']  .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}
}

?>