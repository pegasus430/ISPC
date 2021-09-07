<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientPharmacy', 'SYSDAT');

	class PatientPharmacy extends BasePatientPharmacy {

		public function getPatientPharmacy($ipid, $pharmacy_id = false)
		{
			$sql = "*, p.*, p.id as ph_id, p.fax as fax, p.phone as phone, p.city as city, p.street1 as street1, p.zip as zip, p.salutation as salutation";
			$sql .=",p.pharmacy as apotheke";
			$sql .=",pp.pharmacy_comment as ph_com";

			if($pharmacy_id)
			{
				$q = "pp.pharmacy_id = " . $pharmacy_id . " and";
			}
			else
			{
				$q = "";
			}
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientPharmacy pp')
				->leftJoin('pp.Pharmacy p')
				->where("" . $q . " pp.pharmacy_id = p.id and pp.ipid='" . $ipid . "' and isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}
/*
		public function get_patients_pharmacy($ipids)
		{
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('PatientPharmacy')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = "0"');
			$pharmacy_res = $drop->fetchArray();

			foreach($pharmacy_res as $k_phar => $v_phar)
			{
				$pharmacy_ids[] = $v_phar['pharmacy_id'];
			}

			if($pharmacy_ids)
			{
				$phar_master = Doctrine_Query::create()
					->select('*')
					->from('Pharmacy')
					->whereIn('id', $pharmacy_ids);
				$phar_master_res = $phar_master->fetchArray();

				foreach($phar_master_res as $k_mphar => $v_mphar)
				{
					$master_phar_data[$v_mphar['id']] = $v_mphar;
				}

				foreach($pharmacy_res as $k_phar_res => $v_phar_res)
				{
					if(!empty($master_phar_data[$v_phar_res['pharmacy_id']]))
					{
						$used_pharmacy_data = $master_phar_data[$v_phar_res['pharmacy_id']];
						$used_pharmacy_data['patient_pharmacy_id'] = $v_phar_res['id'];
						$used_pharmacy_data['ipid'] = $v_phar_res['ipid'];
						$used_pharmacy_data['pharmacy_comment'] = $v_phar_res['pharmacy_comment'];


						$patients_pharmacy_data[$v_phar_res['ipid']][] = $used_pharmacy_data;
					}
				}

				return $patients_pharmacy_data;
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
		 * .. introduced key PatientPharmacy that is all we need
		 *  
		 * @param array(string) $ipids
		 * @return array|boolean
		 */
		public function get_patients_pharmacy($ipids =  array())
		{
		    if (empty($ipids) || ! is_array($ipids)) {
		        return false; //fails-safe with false...
		    }
		    
		    $result = array();
		    
		    $pharmacy_res = Doctrine_Query::create()
		    ->select("pp.*, ph.*")
		    ->from('PatientPharmacy pp')
		    ->leftJoin("pp.Pharmacy ph")
		    ->whereIn('ipid', $ipids)
		    ->andWhere('isdelete = "0"')
		    ->fetchArray();
		    
		    if ( ! empty($pharmacy_res))
		    foreach($pharmacy_res as $k_phar_res => $v_phar_res)
		    {
		        if( ! empty($v_phar_res['Pharmacy']))
		        {
		            $used_pharmacy_data = $v_phar_res['Pharmacy'];
		            $used_pharmacy_data['patient_pharmacy_id'] = $v_phar_res['id'];
		            $used_pharmacy_data['pharmacy_comment'] = $v_phar_res['pharmacy_comment'];
	                $used_pharmacy_data['ipid'] = $v_phar_res['ipid'];
		            
	                $used_pharmacy_data['PatientPharmacy'] = $v_phar_res;
	                
		    
		            $result[$v_phar_res['ipid']][] = $used_pharmacy_data;
		        }
		    }
		    
		    
		    return empty($result) ? false : $result; //return falseon no result..
		}
		
		
		
		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$patient_pharmacyes = $this->getPatientPharmacy($ipid);

			if($patient_pharmacyes)
			{
				$pharmacyes = new Pharmacy();

				foreach($patient_pharmacyes as $k_ppharmacy => $v_ppharmacy)
				{
					$pharmacy_id = $pharmacyes->clone_record($v_ppharmacy['pharmacy_id'], $target_client);
				
					$pph = new PatientPharmacy();
					//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone	
					$pc_listener = $pph->getListener()->get('IntenseConnectionListener');	
					$pc_listener->setOption('disabled', true);	
					//--
					$pph->ipid = $target_ipid;
					$pph->pharmacy_id = $pharmacy_id;
					$pph->pharmacy_comment = $v_ppharmacy['pharmacy_comment'];
					$pph->isdelete = '0';
					$pph->save();
					//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
					$pc_listener->setOption('disabled', false);
					//--
				}
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
	        
	        if (isset($k['Pharmacy'])){
	            $k ['nice_name']  = trim($k['Pharmacy']['last_name']);
	            $k ['nice_name'] .= trim($k['Pharmacy']['first_name']) != "" ? (", " . trim($k['Pharmacy']['first_name'])) : "";
	        } else {
	            $k ['nice_name']  = trim($k['last_name']);
	            $k ['nice_name'] .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}
}

?>