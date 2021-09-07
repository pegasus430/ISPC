<?php

	require_once("Pms/Form.php");

	class Application_Form_DtaLocations extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$val = new Pms_Validation();

			$error = 0;
			if(!$val->isstring($post['name']))
			{
				$this->error_message['name'] = $Tr->translate('name_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function insert($clientid, $post)
		{
			$fdoc = new DtaLocations();
			$fdoc->client = $clientid;
			$fdoc->name = $post['name'];
			$fdoc->save();

			$ins_id = $fdoc->id;

			if($ins_id)
			{
				foreach($post['type'] as $k_loc => $v_location)
				{
					$fdoc = new Locations2dta();
					$fdoc->location = $v_location;
					$fdoc->dta = $ins_id;
					$fdoc->save();
				}
			}
			return $fdoc;
		}

		public function update($location, $post)
		{
			if($location > '0')
			{
				$fdoc = Doctrine::getTable('DtaLocations')->find($location);
				if($fdoc)
				{
					$fdoc->name = $post['name'];
					$fdoc->save();
					
					if($this->delete_table_links($location)) //dta_location_id
					{
						foreach($post['type'] as $k_loc => $v_location)
						{
							$fdocx = new Locations2dta();
							$fdocx->location = $v_location;
							$fdocx->dta = $location;
							$fdocx->save();
						}
					}

					return $fdoc;
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

		private function delete_table_links($dta = false)
		{
			if($dta)
			{
				$del = Doctrine_Query::create()
					->delete('Locations2dta')
					->where('dta = "' . $dta . '"');
				$del_res = $del->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function delete($client = false, $location = false)
		{
			if($client && $location)
			{
				$del = Doctrine_Query::create()
					->update('DtaLocations')
					->set('isdelete', "1")
					->where('id = "' . $location . '"')
					->andWhere('client = "' . $client . '"');
				$del_res = $del->execute();

				if($del_res)
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

	}

?>