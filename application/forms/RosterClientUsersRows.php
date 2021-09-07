<?php

	require_once("Pms/Form.php");

	class Application_Form_RosterClientUsersRows extends Pms_Form {

		public function insert_data($post_data, $clientid, $userid = false)
		{
			if($post_data)
			{
				$this->clear_data($clientid, $userid);

				foreach($post_data as $k_userid => $rows_ammount)
				{
					$insert_users_rows[] = array(
						'clientid' => $clientid,
						'userid' => $userid, 
						'rows_user' => $k_userid,
						'amount' => $rows_ammount,
					);
				}

				$collection = new Doctrine_Collection('RosterClientUsersRows');
				$collection->fromArray($insert_users_rows);
				$collection->save();

				return true;
			}
		}

		private function clear_data($clientid, $userid)
		{
			//uses isdelete
			$update = Doctrine_Query::create()
				->update("RosterClientUsersRows")
				->set('isdelete', '1')
				->where('clientid = "'.$clientid.'"')
				->andWhere('isdelete = "0"');
//				->where('userid ="' . $userid . '"');
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