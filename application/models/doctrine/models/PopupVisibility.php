<?php

	Doctrine_Manager::getInstance()->bindComponent('PopupVisibility', 'MDAT');

	class PopupVisibility extends BasePopupVisibility {

		public function getUserPopupSettings($userid, $clientid = '0')
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PopupVisibility')
				->where('userid = ?', $userid)
				->andWhere('clientid = ?', $clientid);
			$droparray = $drop->fetchArray();

			$pop_settings = array();
			foreach($droparray as $k_drop => $v_drop)
			{
				$pop_settings[$v_drop['popup']] = $v_drop;
			}
			return $pop_settings;
		}

		public function clearUserPopupSettings($userid, $clientid = '0', $type)
		{
			$drop = Doctrine_Query::create()
				->delete('*')
				->from('PopupVisibility')
				->where('userid = ?', $userid)
				->andWhere('clientid = ?', $clientid)
				->andWhere('popup =?', $type);
			$drop->execute();
		}

	}

?>