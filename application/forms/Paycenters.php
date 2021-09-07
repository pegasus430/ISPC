<?php

	require_once("Pms/Form.php");

	class Application_Form_Paycenters extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['paycenter']))
			{
				$this->error_message['paycenter'] = $Tr->translate('paycenter_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function insert($post)
		{
			$inserted_id = false;

			$pcenter = new Paycenters();
			$pcenter->client = $post['client'];
			$pcenter->paycenter = $post['paycenter'];
			$pcenter->isdelete = "0";
			$pcenter->save();

			$inserted_id = $pcenter->id;

			if($inserted_id)
			{
				$zips_tmp = explode(",", $post['zips']);

				if(count($zips_tmp) > '0')
				{
					$zips = $zips_tmp;
				}

				self::insert_zips($inserted_id, $zips);

				return $inserted_id;
			}
			else
			{
				return false;
			}
		}

		public function update($post)
		{
			$fdoc = Doctrine::getTable('Paycenters')->find($post['pcid']);

			$fdoc->paycenter = trim(rtrim($post['paycenter']));
			$fdoc->isdelete = "0";
			$fdoc->save();

			self::delete_zips($post['pcid']);
			
			if(!empty($post['zips']))
			{
				$zips_tmp = explode(",", $post['zips']);

				if(count($zips_tmp) > '0')
				{
					$zips = $zips_tmp;
				}

				self::insert_zips($post['pcid'], $zips);
			}
			
			return $fdoc->id;
		}

		public function delete($post)
		{
			$fdoc = Doctrine::getTable('Paycenters')->find($post['pcid']);
			if($fdoc)
			{
				$fdoc->isdelete = "1";
				$fdoc->save();
				
				return $fdoc->id;
			}
		}

		public function insert_zips($paycenter = false, $zips = false)
		{
			if($paycenter && $zips)
			{
				foreach($zips as $k_zip => $v_zip)
				{
					$zips_data[] = array(
						'paycenter' => $paycenter,
						'zip' => trim(rtrim($v_zip))
					);
				}

				$collection = new Doctrine_Collection('PaycenterZip');
				$collection->fromArray($zips_data);
				$collection->save();
			}
		}
		
		
		public function delete_zips($paycenter = false)
		{
			if($paycenter)
			{
				$q = Doctrine_Query::create()
					->update('PaycenterZip')
					->set('isdelete', "1")
					->where('paycenter = "' . $paycenter . '"')
					->andWhere('isdelete = "0"');
				$q->execute();
			}
		}

	}

?>