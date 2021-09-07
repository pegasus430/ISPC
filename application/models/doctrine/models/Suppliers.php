<?php

	Doctrine_Manager::getInstance()->bindComponent('Suppliers', 'SYSDAT');

	class Suppliers extends BaseSuppliers {

		public function getSuppliers($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Suppliers')
				->where("id='" . $id . "'");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getSupplierss($ipid = false, $letter = false, $keyword = false, $arrayids = false, $useclient = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$patientsuppliers = new PatientSuppliers();
				$patsuppliers = $patientsuppliers->getPatientSuppliers($ipid);
				if(count($patsuppliers) > 0)
				{
					foreach($patsuppliers as $keyph => $valueph)
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
				->from('Suppliers')
				->where($client_sql . " supplier != '' " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($id, $target_client)
		{
			$suppliers = $this->getSuppliers($id);
			foreach($suppliers as $k_suppliers => $v_suppliers)
			{
				$ph = new Suppliers();
				$ph->clientid = $target_client;
				$ph->supplier = $v_suppliers['supplier'];
				$ph->type = $v_suppliers['type'];//ISPC-2614 Ancuta 20.07.2020:: mising from clone
				$ph->last_name = $v_suppliers['last_name'];
				$ph->first_name = $v_suppliers['first_name'];
				$ph->street1 = $v_suppliers['street1'];
				$ph->street2 = $v_suppliers['street2'];
				$ph->zip = $v_suppliers['zip'];
				$ph->city = $v_suppliers['city'];
				$ph->phone = $v_suppliers['phone'];
				$ph->fax = $v_suppliers['fax'];
				$ph->email = $v_suppliers['email'];
				$ph->comments = $v_suppliers['comments'];
				$ph->indrop = '1';
				$ph->save();

				return $ph->id;
			}
		}

		public function get_suppliers($ids)
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
				->from('Suppliers')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>