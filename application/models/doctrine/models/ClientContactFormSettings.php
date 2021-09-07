<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientContactFormSettings', 'SYSDAT');

	class ClientContactFormSettings extends BaseClientContactFormSettings {

		public static function getClientContactFormSetting($clientid)
		{
			$client_set = Doctrine_Query::create()
				->select("*")
				->from('ClientContactFormSettings')
				->where('client="' . $clientid . '"')
				->andWhere('isdelete = "0"')
				->orderBy('id ASC')
				->limit('1');
			$settings_data = $client_set->fetchArray();

			if($settings_data)
			{
				return $settings_data[0];
			}
			else
			{
				//client has no data -> return defaults
				$settings_data['date'] = 'date';

				return $settings_data;
			}

		}

	}

?>