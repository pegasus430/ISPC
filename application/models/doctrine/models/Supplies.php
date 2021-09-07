<?php

	Doctrine_Manager::getInstance()->bindComponent('Supplies', 'SYSDAT');

	class Supplies extends BaseSupplies {

		public function getSupplies($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Supplies')
				->where("id='" . $id . "'");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getSuppliess($ipid = false, $letter = false, $keyword = false, $arrayids = false, $useclient = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$patientsupplies = new PatientSupplies();
				$patsupplies = $patientsupplies->getPatientSupplies($ipid);
				if(count($patsupplies) > 0)
				{
					foreach($patsupplies as $keyph => $valueph)
					{
						$pharry[$keyph] = $valueph['supplier_id'];
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
				$keyword_sql = " AND supplier like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND supplier like '" . ($letter) . "%'";
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
				->from('Supplies')
				->where($client_sql . " supplier != '' " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($id, $target_client)
		{
			$supplies = $this->getSupplies($id);
			foreach($supplies as $k_supplies => $v_supplies)
			{
				$ph = new Supplies();
				$ph->clientid = $target_client;
				$ph->supplier = $v_supplies['supplier'];
				$ph->last_name = $v_supplies['last_name'];
				$ph->first_name = $v_supplies['first_name'];
				$ph->street1 = $v_supplies['street1'];
				$ph->street2 = $v_supplies['street2'];
				$ph->zip = $v_supplies['zip'];
				$ph->city = $v_supplies['city'];
				$ph->phone = $v_supplies['phone'];                  //ISPC-2614 Lore 28.07.2020
				$ph->salutation = $v_supplies['salutation'];        //ISPC-2614 Lore 28.07.2020
				$ph->fax = $v_supplies['fax'];
				$ph->email = $v_supplies['email'];
				$ph->comments = $v_supplies['comments'];
				$ph->indrop = '1';
				$ph->save();

				return $ph->id;
			}
		}

		public function get_supplies($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Supplies')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		public function checkRmemedySupplies($ipid,$supplier_id)
		{
			$remedy = Doctrine::getTable('PatientRemedies')->find($ipid);
			$remedy_array = $remedy->toArray();
			
			if($remedy_array['supplier']== $supplier_id)
			{
				return true;
			}else{
				return false;
			}
			
		}

	}

?>