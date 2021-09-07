<?php

	require_once("Pms/Form.php");

	class Application_Form_ClientHospitalSettings extends Pms_Form {

		public function insert_data($clientid, $post)
		{
			if($clientid > '0' && !empty($post))
			{
				$this->clear_client_data($clientid);

				$settings = new ClientHospitalSettings();
				$settings->client = $clientid;

				if(strlen(trim($post['hospiz_adm'])) > '0')
				{
					$settings->hospiz_adm = $post['hospiz_adm'];
				}

				if(strlen(trim($post['hospiz_dis'])) > '0')
				{
					$settings->hospiz_dis = $post['hospiz_dis'];
				}

				if(strlen(trim($post['hospiz_day'])) > '0')
				{
					$settings->hospiz_day = $post['hospiz_day'];
				}

				if(strlen(trim($post['hosp_adm'])) > '0')
				{
					$settings->hosp_adm = $post['hosp_adm'];
				}

				if(strlen(trim($post['hosp_dis'])) > '0')
				{
					$settings->hosp_dis = $post['hosp_dis'];
				}

				if(strlen(trim($post['hosp_day'])) > '0')
				{
					$settings->hosp_day = $post['hosp_day'];
				}


				if(strlen(trim($post['hosp_dis_hospiz_adm'])) > '0')
				{
					$settings->hosp_dis_hospiz_adm = $post['hosp_dis_hospiz_adm'];
				}

				if(strlen(trim($post['hospiz_dis_hosp_adm'])) > '0')
				{
					$settings->hospiz_dis_hosp_adm = $post['hospiz_dis_hosp_adm'];
				}

				if(strlen(trim($post['hosp_pat_dis'])) > '0')
				{
					$settings->hosp_pat_dis = $post['hosp_pat_dis'];
				}
    			$settings->hosp_pat_dis_final = $post['hosp_pat_dis_final'];

				
				
				if(strlen(trim($post['hospiz_pat_dis'])) > '0')
				{
					$settings->hospiz_pat_dis = $post['hospiz_pat_dis'];
				}
				$settings->hospiz_pat_dis_final = $post['hospiz_pat_dis_final'];

				if(strlen(trim($post['hosp_pat_dead'])) > '0')
				{
					$settings->hosp_pat_dead = $post['hosp_pat_dead'];
				}
				$settings->hosp_pat_dead_final = $post['hosp_pat_dead_final'];

				if(strlen(trim($post['hospiz_pat_dead'])) > '0')
				{
					$settings->hospiz_pat_dead = $post['hospiz_pat_dead'];
				}
				$settings->hospiz_pat_dead_final = $post['hospiz_pat_dead_final'];

				if(strlen(trim($post['hosp_dis_hosp_adm'])) > '0')
				{
					$settings->hosp_dis_hosp_adm = $post['hosp_dis_hosp_adm'];
				}

				if(strlen(trim($post['hospiz_dis_hospiz_adm'])) > '0')
				{
					$settings->hospiz_dis_hospiz_adm = $post['hospiz_dis_hospiz_adm'];
				}
				
				if(strlen(trim($post['hospiz_first_day'])) > '0')
				{
					$settings->hospiz_first_day = $post['hospiz_first_day'];
				}

				if(strlen(trim($post['hosp_first_day'])) > '0')
				{
					$settings->hosp_first_day = $post['hosp_first_day'];
				}

				$settings->isdelete = '0';
				$settings->save();

				if($settings->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		private function clear_client_data($clientid)
		{
			$upd = Doctrine::getTable('ClientHospitalSettings')->findOneByClientAndIsdelete($clientid, '0');
			if($upd)
			{
				$upd->isdelete = '1';
				$upd->save();
			}
		}

	}

?>