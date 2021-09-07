<?php

	Doctrine_Manager::getInstance()->bindComponent('PpunIpid', 'IDAT');

	class PpunIpid extends BasePpunIpid {

		public function check_patient_ppun($ipid = false, $client = false)
		{
			if($ipid && $client)
			{
				//check if patient(s) are private and then if had ppun code already generated
				//check only, no hi aditional data neded
				$patient_private = PatientHealthInsurance::check_priv_patient($ipid, false);

				if($patient_private)
				{
					//if private check if ppun exists db
					$ppun_db_data = self::check_patient_ppun_db($ipid, $client);

					if($ppun_db_data !== false)
					{
						//return ppun data if exists in db
						return $ppun_db_data;
					}
					else
					{
						//call generate ppun if not exists in db
						$ppun_new_data = self::generate_patient_ppun($ipid, $client);

						//return generated data
						return $ppun_new_data;
					}
				}
				else
				{
					//if not private return false
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function check_patient_ppun_db($ipid = false, $client = false)
		{
			if($ipid && $client)
			{
				$doc = Doctrine_Query::create()
					->select('*')
					->from('PpunIpid')
					->where('ipid = ? ', $ipid)
					->andWhere('clientid = ?', $client)
					->andWhere('ppun <> 0')
					->limit('1');
				$doc_res = $doc->fetchArray();

				if($doc_res)
				{
					return $doc_res[0];
				}
				else
				{
					return false;
				}
			}
		}

		public function generate_patient_ppun($ipid = false, $client = false, $check_priv_patient = false, $save_generated_number = true)
		{
			$is_private = true;
			$post_data = array();

			if($ipid && $client)
			{
				//receck if patient is private -- no point (maybe if we call it from elsewhere[added $check_priv_patient to be used for such situations])
				if($check_priv_patient)
				{
					//check only, no hi aditional data neded
					$patient_private = PatientHealthInsurance::check_priv_patient($ipid);

					//!!! is_private should stay boolean, dont change check_priv_patient params(default is boolean response)
					$is_private = $patient_private;
				}

				if($is_private === true)
				{
					//get ppun client range::rturns ppun value
					$settings_ppun_data = self::get_ppun_client_settings($client);

					//get last generated ppun of client
					$existing_ppun_data = self::get_ppun_last_generated($client);

					//compare settings value with existing highest value
					if($existing_ppun_data === false)
					{
						$base_nr = $settings_ppun_data;
					}
					else if($existing_ppun_data >= $settings_ppun_data)
					{
						$base_nr = $existing_ppun_data;
						$base_nr++;
					}
					else
					{
						$base_nr = $settings_ppun_data;
						$base_nr++;
					}
					$post_data['ipid'] = $ipid;
					$post_data['ppun'] = $base_nr;
					$post_data['clientid'] = $client;

					if($save_generated_number){
					    
    					//insert ppun in db
    					$ppun_form = new Application_Form_PpunIpid();
//     					$ppun_form_data = $ppun_form->insert_ppun($post_data);
    					$ppun_form_data = $ppun_form->update_or_insert_ppun($post_data);
    
    					//then return it
    					return $ppun_form_data;
					} 
					else
					{
    					return $post_data;
					}
				}
				else
				{
					//if not private return false
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_ppun_client_settings($client = false)
		{
			//get client ppun range settings!
			if($client)
			{
				$client_data = Client::getClientDataByid($client);

				return $client_data[0]['ppun_start'];
			}
			else
			{
				return false;
			}
		}

		public function get_ppun_last_generated($client = false)
		{
			//get last generated ppun of client
			if($client)
			{
				$doc = Doctrine_Query::create()
					->select('*')
					->from('PpunIpid')
					->where('clientid = "' . $client . '"')
					->orderBy('ppun DESC')
					->limit('1');
				$doc_res = $doc->fetchArray();

				if($doc_res)
				{
					return $doc_res[0]['ppun'];
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