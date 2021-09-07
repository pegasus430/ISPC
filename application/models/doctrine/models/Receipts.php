<?php

	Doctrine_Manager::getInstance()->bindComponent('Receipts', 'SYSDAT');

	class Receipts extends BaseReceipts {

		public function get_receipt($receipt)
		{
			$receipts = Doctrine_Query::create()
				->select("*")
// 				->select("*,
// 						ri.type,
// 						ri.dbf_id,
// 						ri.medication,
// 						ri.custom_line,
// 						ri.pzn"
// 						)
				->from('Receipts r')
				->where("id= ? ", $receipt)
				->andWhere('isdelete = "0"')
// 				->innerJoin("r.ReceiptItems ri")
// 				->addWhere("ri.isdelete = 0")
				->limit(1);
			$receiptsarray = $receipts->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			return $receiptsarray;
		}

		public function get_multiple_receipts($receipts = false, $client = false)
		{
			if($receipts && $client)
			{
				$receipts[] = '99999999999999';
				$receipts = Doctrine_Query::create()
					->select("*")
					->from('Receipts')
					->whereIn("id", $receipts)
					->andWhere("client = '" . $client . "'")
					->andWhere('isdelete = "0"');
				$receipts_res = $receipts->fetchArray();
				if($receipts_res)
				{
					foreach($receipts_res as $k_receipt => $v_receipt)
					{
						$receipts_data[$v_receipt['id']] = $v_receipt;
					}

					return $receipts_data;
				}
				else
				{
					return false;
				}
			}
		}

		public function get_multiple_receipts_creators($receipts = false, $client = false)
		{
			if($receipts && $client)
			{
				$loaded_receipts = self::get_multiple_receipts($receipts, $client);
				$loaded_receipts = array_values($loaded_receipts);

				$receipt_creators[] = '9999999999999';
				if($loaded_receipts)
				{
					foreach($loaded_receipts as $k_receipt => $v_receipt)
					{
						$receipt_creators[$v_receipt['id']] = $v_receipt['create_user'];
					}

					return $receipt_creators;
				}
				else
				{
					//no receipts loaded
					return false;
				}
			}
		}
		
		public function remove_receipt_assigned_users($receipt = false, $client = false, $users_type = false)
		{
			$receipts = new Receipts();
			$users_type_src = array('print', 'fax');
			
			if($receipt && $client && $users_type)
			{
				if(in_array($users_type, $users_type_src))
				{
					$remove_assigned_users = $receipts->{'remove_assigned_'.$users_type.'_users'}($receipt, $client);
				}
			}
		}
		
		//used in remove_receipt_assigned_users
		public function remove_assigned_print_users($receipt = false, $client = false)
		{
			if($receipt && $client)
			{
				$q = Doctrine_Query::create()
					->update('PrintUsersAssigned')
					->set('isdelete', "1")
					->where('receipt = "'.$receipt.'"')
					->andWhere('client = "'.$client.'"')
					->andWhere('isdelete = "0"');
				$q->execute();
			}
		}
		
		//used in remove_receipt_assigned_users
		public function remove_assigned_fax_users($receipt = false, $client = false)
		{
			if($receipt && $client)
			{
				$q = Doctrine_Query::create()
					->update('FaxUsersAssigned')
					->set('isdelete', "1")
					->where('receipt = "'.$receipt.'"')
					->andWhere('client = "'.$client.'"')
					->andWhere('isdelete = "0"');
				$q->execute();
			}
		}
		
		public function get_multiple_receipt_print_assign_creators($receipts = false, $client = false)
		{
			if($receipts && $client)
			{
				$receipts[] = '99999999999999';
				$receipts = Doctrine_Query::create()
					->select("*")
					->from('PrintUsersAssigned')
					->whereIn("receipt", $receipts)
					->andWhere("client = '" . $client . "'")
					->andWhere('isdelete = "0"');
				$receipts_assigned_usr_res = $receipts->fetchArray();
				if($receipts_assigned_usr_res)
				{
					foreach($receipts_assigned_usr_res as $k_receipt => $v_receipt)
					{
						$receipts_assigned_creators[$v_receipt['receipt']][$v_receipt['user']] = $v_receipt['create_user'];
					}

					return $receipts_assigned_creators;
				}
				else
				{
					return false;
				}
			}
		}
		
		public function get_multiple_receipt_fax_assign_creators($receipts = false, $client = false)
		{
			if($receipts && $client)
			{
				$receipts[] = '99999999999999';
				$receipts = Doctrine_Query::create()
					->select("*")
					->from('FaxUsersAssigned')
					->whereIn("receipt", $receipts)
					->andWhere("client = '" . $client . "'")
					->andWhere('isdelete = "0"');
				$receipts_assigned_usr_res = $receipts->fetchArray();
				if($receipts_assigned_usr_res)
				{
					foreach($receipts_assigned_usr_res as $k_receipt => $v_receipt)
					{
						$receipts_assigned_creators[$v_receipt['receipt']][$v_receipt['user']] = $v_receipt['create_user'];
					}

					return $receipts_assigned_creators;
				}
				else
				{
					return false;
				}
			}
		}
	}

?>