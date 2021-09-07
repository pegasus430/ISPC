<?php

	Doctrine_Manager::getInstance()->bindComponent('Notifications', 'SYSDAT');

	class Notifications extends BaseNotifications {

		public function get_notification_settings($user_ids = false)
		{
			if(empty($user_ids)){
				return false;
			}

			$users = array();

			if(!is_array($user_ids))
			{
				$users[] = $user_ids;
			}
			else
			{
				$users = $user_ids;
			}
			$notifications = Doctrine_Query::create()
				->select("*")
				->from('Notifications')
				->whereIn("user_id", $users)
				->orderBy('user_id ASC');
			$notifications_settings = $notifications->fetchArray();
			
			$settings = array();
			if(count($notifications_settings) > '0')
			{
				foreach($notifications_settings as $k_notif => $v_notif)
				{
					$settings[$v_notif['user_id']] = $v_notif;
				}

				return $settings;
			}
			else
			{
				return false;
			}
		}

	}

?>
