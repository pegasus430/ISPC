<?php

	Doctrine_Manager::getInstance()->bindComponent('Homecare', 'SYSDAT');

	class Homecare extends BaseHomecare {

		public function get_homecare($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Homecare')
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

		
		//claudiu 08.2017: changed the fn name from getHomecare() to getHomecare_suffix() because getColumnname is a DQL callback
		public function getHomecare_suffix($ipid = false, $letter = false, $keyword = false, $arrayids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$home = new PatientHomecare();
				$homearray = $home->getPatientHomecare($ipid);

				if(count($homearray) > 0)
				{
					foreach($homearray as $keyhome => $valuehome)
					{
						$homearry[$keyhome] = $valuehome['id'];
					}
					$ids = implode(",", $homearry);

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
				$keyword_sql = " AND homecare like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND homecare like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Homecare')
				->where("clientid='" . $clientid . "' AND (homecare != '' or first_name != '' or last_name != '') " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_record($id, $target_client)
		{
		    $home = $this->get_homecare($id);//ISPC-2614 Ancuta 19.07.2020 - change  from getHomecare_suffix to get_homecare

			if($home)
			{
				$fdoc = new Homecare();
				$fdoc->clientid = $target_client;
				$fdoc->homecare = $home[0]['homecare'];
				$fdoc->first_name = $home[0]['first_name'];
				$fdoc->last_name = $home[0]['last_name'];
				$fdoc->title = $home[0]['title'];
				$fdoc->salutation = $home[0]['salutation'];
				$fdoc->title_letter = $home[0]['title_letter'];
				$fdoc->salutation_letter = $home[0]['salutation_letter'];
				$fdoc->street1 = $home[0]['street1'];
				$fdoc->street2 = $home[0]['street2'];
				$fdoc->zip = $home[0]['zip'];
				$fdoc->city = $home[0]['city'];
				$fdoc->doctornumber = $home[0]['doctornumber'];
				$fdoc->phone_practice = $home[0]['phone_practice'];
				$fdoc->phone_emergency = $home[0]['phone_emergency'];
				$fdoc->fax = $home[0]['fax'];
				$fdoc->phone_private = $home[0]['phone_private'];
				$fdoc->email = $home[0]['email'];
				$fdoc->kv_no = $home[0]['kv_no'];
				$fdoc->medical_speciality = $home[0]['medical_speciality'];
				$fdoc->comments = $home[0]['comments'];
				$fdoc->valid_from = $home[0]['valid_from'];
				$fdoc->valid_till = $home[0]['valid_till'];
				$fdoc->indrop = '1';
				$fdoc->isdelete = $home[0]['isdelete'];
				$fdoc->logo = $home[0]['logo'];
				$fdoc->ik_number = $home[0]['ik_number'];
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

		public function get_homecares($ids)
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
				->from('Homecare')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>