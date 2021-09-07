<?php

	Doctrine_Manager::getInstance()->bindComponent('BtmNotifications', 'SYSDAT');

	class BtmNotifications extends BaseBtmNotifications {

		public function get_btm_notification_users($client, $type = false)
		{
			$bt = Doctrine_Query::create()
				->select('*')
				->from('BtmNotifications')
				->where('clientid= ?', $client )
				->andWhere('isdelete = "0"');
			if($type)
			{
				$bt->andWhere('type = ? ' , $type );
			}

			$btarray = $bt->fetchArray();

			return $btarray;
		}

	}

?>