<?php

	require_once("Pms/Form.php");

	class Application_Form_RosterUsersOrder extends Pms_Form {

		public function validate($post)
		{
			
		}

		public function insert_data($post_data, $clientid, $userid)
		{
			if($post_data)
			{
				$this->clear_data($clientid, $userid);

				foreach($post_data as $k_groupid => $v_users_arr)
				{
					foreach($v_users_arr as $k_user_order => $v_user_id)
					{
						$insert_sort_order[] = array(
							'userid' => $userid, //sort is per user
							'clientid' => $clientid, //sort is per user per client
							'user_sort' => $v_user_id,
							'group_sort' => $k_groupid,
							'sort_order' => $k_user_order,
						);
					}
				}

				$collection = new Doctrine_Collection('RosterUsersOrder');
				$collection->fromArray($insert_sort_order);
				$collection->save();

				return true;
			}
		}

		private function clear_data($clientid, $userid)
		{
			//uses isdelete
			$update = Doctrine_Query::create()
				->update("RosterUsersOrder")
				->set('isdelete', '1')
//				->where('userid ="' . $userid . '"')
				->andWhere('isdelete = "0"')
				->andWhere('clientid = "'.$clientid.'"');
			$update->execute();

			if($update_invoice)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

	}

?>