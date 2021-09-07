<?php

	require_once("Pms/Form.php");

	class Application_Form_BtmNotifications extends Pms_Form {

		public function insert_btm_notifications_users($clientid, $post_data)
		{
			if($clientid)
			{
				$clear_perms = $this->clear_btm_notifications_users($clientid);

				foreach($post_data['btm_users'] as $key => $value)
				{
					$notifications_data[] = array(
						'clientid' => $clientid,
						'type' => 'tresor',
						'user' => $value,
						'isdelete' => '0',
					);
				}

				if(count($notifications_data) > 0)
				{
					$collection = new Doctrine_Collection('BtmNotifications');
					$collection->fromArray($notifications_data);
					$collection->save();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		public function clear_btm_notifications_users($clientid, $group=false)
		{
			$del_perms = Doctrine_Query::create()
				->update('BtmNotifications')
				->set('isdelete', '1')
				->where('clientid="' . $clientid . '"')
				->andWhere('isdelete="0"');
			$del_perms_exec = $del_perms->execute();
		}

	}

?>