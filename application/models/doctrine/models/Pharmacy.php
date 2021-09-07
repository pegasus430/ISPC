<?php

	Doctrine_Manager::getInstance()->bindComponent('Pharmacy', 'SYSDAT');

	class Pharmacy extends BasePharmacy {

// 		public function getPharmacy($id)
// 		{
// 			$drop = Doctrine_Query::create()
// 				->select('*')
// 				->from('Pharmacy')
// 				->where("id='" . $id . "'");
// 			$droparray = $drop->fetchArray();

// 			return $droparray;
// 		}
		
		
		public static function findPharmacyById($id = 0)
		{
		    if (empty($id)) {
		        return;
		    }
		    
		    $result = Doctrine_Query::create()
		    ->select('*')
		    ->from('Pharmacy')
		    ->where("id = ?", $id)
		    ->fetchArray(); //even though id is unique
		
		    return $result;
		}
		

		public function getPharmacys($ipid = false, $letter = false, $keyword = false, $arrayids = false, $useclient = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$patientpharmacy = new PatientPharmacy();
				$patpharmacy = $patientpharmacy->getPatientPharmacy($ipid);
				if(count($patpharmacy) > 0)
				{
					foreach($patpharmacy as $keyph => $valueph)
					{
						$pharry[$keyph] = $valueph['pharmacy_id'];
					}
					$ids = implode(",", $pharry);

					$ipid_sql .= " AND id IN (" . $ids . ")";
				}
				else
				{
					$ipid_sql .= " AND id IN (0)";
				}
			}
			else
			{
				$ipid_sql = " AND indrop=0 AND isdelete =0 AND valid_till = '0000-00-00'";
			}

			if($keyword != false)
			{
				$keyword_sql = " AND pharmacy like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND pharmacy like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			if($useclient)
			{
				$client_sql = 'clientid= "' . $clientid . '" AND ';
			}
			else
			{
				$client_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Pharmacy')
				->where($client_sql . " pharmacy != '' " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($id, $target_client)
		{
// 			$pharmacy = $this->getPharmacy($id);
			$pharmacy = $this->findPharmacyById($id);
			foreach($pharmacy as $k_pharmacy => $v_pharmacy)
			{
				$ph = new Pharmacy();
				$ph->clientid = $target_client;
				$ph->pharmacy = $v_pharmacy['pharmacy'];
				$ph->last_name = $v_pharmacy['last_name'];
				$ph->first_name = $v_pharmacy['first_name'];
				$ph->street1 = $v_pharmacy['street1'];
				$ph->street2 = $v_pharmacy['street2'];
				$ph->zip = $v_pharmacy['zip'];
				$ph->city = $v_pharmacy['city'];
				$ph->phone = $v_pharmacy['phone'];
				$ph->fax = $v_pharmacy['fax'];
				$ph->email = $v_pharmacy['email'];
				$ph->kv_no = $v_pharmacy['kv_no'];
				$ph->comments = $v_pharmacy['comments'];
				$ph->valid_from = $v_pharmacy['valid_from'];
				$ph->valid_till = $v_pharmacy['valid_till'];
				$ph->indrop = '1';
				$ph->save();

				return $ph->id;
			}
		}

	}

?>