<?php

	Doctrine_Manager::getInstance()->bindComponent('Physiotherapists', 'SYSDAT');

	class Physiotherapists extends BasePhysiotherapists {

		public function get_physiotherapist($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Physiotherapists')
				->where("id = ? ", $id);
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}

		public function getPhysiotherapists($ipid = false, $letter = false, $keyword = false, $arrayids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$phy = new PatientPhysiotherapist();
				$phyarray = $phy->getPatientPhysiotherapist($ipid);

				if(count($phyarray) > 0)
				{
					foreach($phyarray as $keypfl => $valuepfl)
					{
						$phyarry[$keypfl] = $valuepfl['id'];
					}
					$ids = implode(",", $phyarry);

					$ipid_sql .= " AND id IN (" . $ids . ")";
				}
				else
				{
					$ipid_sql .= " AND id IN (0)";
				}
			}
			else
			{
				$ipid_sql = " AND indrop=0 AND isdelete =0 AND valid_till = '0000-00-00'";
			}

			if($keyword != false)
			{
				$keyword_sql = " AND physiotherapist like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND physiotherapist like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Physiotherapists')
				->where("clientid='" . $clientid . "' AND (physiotherapist != '' or first_name != '' or last_name != '') " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($id, $target_client)
		{
		    $physio = $this->get_physiotherapist($id);//ISPC-2614 Ancuta 19.07.2020

			if($physio)
			{
				$fdoc = new Physiotherapists();
				$fdoc->clientid = $target_client;
				$fdoc->physiotherapist = $physio[0]['physiotherapist'];
				$fdoc->first_name = $physio[0]['first_name'];
				$fdoc->last_name = $physio[0]['last_name'];
				$fdoc->title = $physio[0]['title'];
				$fdoc->salutation = $physio[0]['salutation'];
				$fdoc->title_letter = $physio[0]['title_letter'];
				$fdoc->salutation_letter = $physio[0]['salutation_letter'];
				$fdoc->street1 = $physio[0]['street1'];
				$fdoc->street2 = $physio[0]['street2'];
				$fdoc->zip = $physio[0]['zip'];
				$fdoc->city = $physio[0]['city'];
				$fdoc->doctornumber = $physio[0]['doctornumber'];
				$fdoc->phone_practice = $physio[0]['phone_practice'];
				$fdoc->phone_emergency = $physio[0]['phone_emergency'];
				$fdoc->fax = $physio[0]['fax'];
				$fdoc->phone_private = $physio[0]['phone_private'];
				$fdoc->email = $physio[0]['email'];
				$fdoc->kv_no = $physio[0]['kv_no'];
				$fdoc->medical_speciality = $physio[0]['medical_speciality'];
				$fdoc->comments = $physio[0]['comments'];
				$fdoc->valid_from = $physio[0]['valid_from'];
				$fdoc->valid_till = $physio[0]['valid_till'];
				$fdoc->indrop = '1';
				$fdoc->isdelete = $physio[0]['isdelete'];
				$fdoc->logo = $physio[0]['logo'];
				$fdoc->ik_number = $physio[0]['ik_number'];
				$fdoc->save();

				if($fdoc)
				{
					return $fdoc->id;
				}
				else
				{
					return false;
				}
			}
		}

		public function get_physiotherapists($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Physiotherapists')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>