<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientHospitalSettings', 'SYSDAT');

	class ClientHospitalSettings extends BaseClientHospitalSettings {

		public static function getClientSetting($clientid)
		{
			$client_set = Doctrine_Query::create()
				->select("*")
				->from('ClientHospitalSettings')
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
				$settings_data['hospiz_adm'] = 'tr';
				$settings_data['hospiz_day'] = 'hz';
				$settings_data['hospiz_dis'] = 'tr';

				$settings_data['hosp_adm'] = 'tr';
				$settings_data['hosp_day'] = 'hp';
				$settings_data['hosp_dis'] = 'tr';

				$settings_data['hosp_dis_hospiz_adm'] = 'hz';
				$settings_data['hospiz_dis_hosp_adm'] = 'hp';

				
				$settings_data['hosp_pat_dis'] = 'tr';
				$settings_data['hosp_pat_dis_final'] = '1';
				$settings_data['hospiz_pat_dis'] = 'tr';
				$settings_data['hospiz_pat_dis_final'] = '1';

				
				$settings_data['hosp_pat_dead'] = 'hp';
				$settings_data['hosp_pat_dead_final'] = '1';
				$settings_data['hospiz_pat_dead'] = 'hz';
				$settings_data['hospiz_pat_dead_final'] = '1';

				
				$settings_data['hosp_dis_hosp_adm'] = 'hp';
				$settings_data['hospiz_dis_hospiz_adm'] = 'hz';
				
				$settings_data['hospiz_first_day'] = 'hz';
				$settings_data['hosp_first_day'] = 'hp';

				return $settings_data;
			}

		}

	}

?>