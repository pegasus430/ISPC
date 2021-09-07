<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientSuppliers', 'SYSDAT');

	class PatientSuppliers extends BasePatientSuppliers {

		public function getPatientSuppliers($ipid, $supplier_id = false)
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
				$q = "PatientSuppliers.supplier_id = " . $supplier_id . " and";
			}
			else
			{
				$q = "";
			}
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Suppliers')
				->leftJoin('PatientSuppliers')
				->where("" . $q . " PatientSuppliers.supplier_id = Suppliers.id and PatientSuppliers.ipid='" . $ipid . "' and PatientSuppliers.isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatientLastSuppliers($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientSuppliers')
				->where("ipid='" . $ipid . "' and isdelete = 0")
				->orderBy('id desc')
				->limit(1);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

//		public function get_patients_suppliers($ipids)
//		{
//			$drop = Doctrine_Query::create()
//				->select('s.*, ps.*')
//				->from('PatientSuppliers ps')
//				->leftJoin('ps.Suppliers s')
//				->where("ps.supplier_id = s.id")
//				->andWhere('ps.isdelete = "0"')
//				->andWhereIn('ps.ipid', $ipids);
//			$droparray = $drop->fetchArray();
//
//			return $droparray;
//		}

		/*
		public function get_patients_suppliers($ipids)
		{
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientSuppliers')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = "0"');
			$suppliers_res = $drop->fetchArray();

			foreach($suppliers_res as $k_supp => $v_supp)
			{
				$suppliers_ids[] = $v_supp['supplier_id'];
			}

			if($suppliers_ids)
			{
				$supp_master = Doctrine_Query::create()
					->select('*')
					->from('Suppliers')
					->whereIn('id', $suppliers_ids);
				$supp_master_res = $supp_master->fetchArray();

				foreach($supp_master_res as $k_msupp => $v_msupp)
				{
					$master_supp_data[$v_msupp['id']] = $v_msupp;
				}

				foreach($suppliers_res as $k_supp_res => $v_supp_res)
				{
					if(!empty($master_supp_data[$v_supp_res['supplier_id']]))
					{
						$used_supplier_data = $master_supp_data[$v_supp_res['supplier_id']];
						$used_supplier_data['patient_pharmacy_id'] = $v_supp_res['id'];
						$used_supplier_data['ipid'] = $v_supp_res['ipid'];
						$used_supplier_data['supplier_comment'] = $v_supp_res['supplier_comment'];


						$patients_supplier_data[$v_supp_res['ipid']][] = $used_supplier_data;
					}
				}

				return $patients_supplier_data;
			}
			else
			{
				return false;
			}
		}
		*/
		
		
		
		/**
		 * @cla fn rewrite on 26.06.2018
		 * .. keeping the original array structure
		 * .. introduced key PatientSuppliers that is all we need
		 *
		 * @param array(string) $ipids
		 * @return array|boolean
		 */
		public function get_patients_suppliers($ipids)
		{
		    if (empty($ipids) || ! is_array($ipids)){
		        return false; // fail-safe
		    }
		    
		    $result = array();
		    
		    $suppliers_res = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientSuppliers ps')
		    ->leftJoin('ps.Suppliers s')
		    ->whereIn('ipid', $ipids)
		    ->andWhere('isdelete = "0"')
		    ->fetchArray();
		
		    
		    
		    
		    if ( ! empty($suppliers_res))
		    foreach ($suppliers_res as $k_supp => $v_supp) {
		        
		        if( ! empty($v_supp['Suppliers'])) {
		            
    		        $used_supplier_data = $v_supp['Suppliers'];
    		        
    		        $used_supplier_data['patient_pharmacy_id'] = $v_supp['id'];
    		        $used_supplier_data['ipid'] = $v_supp['ipid'];
    		        $used_supplier_data['supplier_comment'] = $v_supp['supplier_comment'];
    		        
    		        $used_supplier_data['PatientSuppliers'] = $v_supp;
    		        
    		        $result[$v_supp['ipid']][] = $used_supplier_data;
		        }
		        
		    }
		
		    return empty($result) ? false : $result;
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$pfleges = $this->getPatientSuppliers($ipid);
			if($pfleges)
			{
				foreach($pfleges as $k_pfl => $v_pfl)
				{
					$pfl = new Suppliers();
					$master_pfl = $pfl->clone_record($v_pfl['id'], $target_client);

					if($master_pfl)
					{
						$pfl_cl = new PatientSuppliers();
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

		public function get_patient_suppliers($ipids, $fetch_master_data = false)
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
				->from('PatientSuppliers')
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
						$patient_suppliers_details[$v_res['supplier_id']] = $v_res;
						$supplies_ids[] = $v_res['supplier_id'];
					}

					$suppliers = new Suppliers();
					$suppliers_details = $suppliers->get_suppliers($supplies_ids);

					foreach($suppliers_details as $k_suppliers => $v_suppliers)
					{
						$patient_suppliers_details[$v_suppliers['id']]['master'] = $v_suppliers;
					}

					if($patient_suppliers_details)
					{
						return $patient_suppliers_details;
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
	        if (isset($k['Suppliers'])){
	            $k ['nice_name']  = trim($k['Suppliers']['last_name']);
	            $k ['nice_name'] .= trim($k['Suppliers']['first_name']) != "" ? (", " . trim($k['Suppliers']['first_name'])) : "";
	        } else {
	            $k ['nice_name']  = trim($k['last_name']);
	            $k ['nice_name'] .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}

}

?>