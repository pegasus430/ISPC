<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientFileTags', 'MDAT');

	class PatientFileTags extends BasePatientFileTags {

		public function get_client_tags($clientid, $all_tags = false)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('PatientFileTags')
				->where('client = "' . $clientid . '"')
				->andWhere('isdelete = "0"')
				->orderBy('tag ASC');
			$res_result = $res->fetchArray();

			if($all_tags)
			{
				$all_clients_tags = PatientFileTags::get_allclients_tags();
			}

			if($res_result)
			{
				foreach($res_result as $k_res => $v_res)
				{
					$client_tags[$v_res['id']] = $v_res;
				}

				if($all_clients_tags)
				{
					foreach($all_clients_tags as $k_allctag => $v_all_c_tag)
					{
						$client_tags[$v_all_c_tag['id']] = $v_all_c_tag;
					}
				}

				return $client_tags;
			}
			else
			{
				if($all_clients_tags)
				{
					return $all_clients_tags;
				}
				else
				{
					return false;
				}
			}
		}

		public function get_allclients_tags($only_ids = false)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('PatientFileTags')
				->andWhere('client = "0"')
				->andWhere('isdelete = "0"')
				->orderBy('tag ASC');
			$res_result = $res->fetchArray();

			if($res_result)
			{
				if($only_ids)
				{
					foreach($res_result as $k_res => $v_res)
					{
						$client_tags[] = $v_res['id'];
					}
				}
				else
				{
					foreach($res_result as $k_res => $v_res)
					{
						$client_tags[$v_res['id']] = $v_res;
					}
				}

				return $client_tags;
			}
			else
			{
				return false;
			}
		}
		
		public function get_tabname_tagids($tabname = false, $only_ids = false)
		{
			if(strlen($tabname)>'0')
			{
				$res = Doctrine_Query::create()
					->select('*')
					->from('PatientFileTags')
					->andWhere('client = "0"')
					->andWhere('isdelete = "0"')
					->andWhere('tabname = "'.$tabname.'"')
					->orderBy('tag ASC');
				$res_result = $res->fetchArray();

				if($res_result)
				{
					if($only_ids)
					{
						foreach($res_result as $k_res => $v_res)
						{
							$client_tags[] = $v_res['id'];
						}
					}
					else
					{
						foreach($res_result as $k_res => $v_res)
						{
							$client_tags[$v_res['id']] = $v_res;
						}
					}

					return $client_tags;
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

	}

?>