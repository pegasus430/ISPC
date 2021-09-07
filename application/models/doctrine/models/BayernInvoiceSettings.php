<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernInvoiceSettings', 'SYSDAT');

	class BayernInvoiceSettings extends BaseBayernInvoiceSettings {

		public function get_invoice_settings($start, $end, $clientid)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start . '") <= `end` ')
				->andWhere('DATE("' . $end . '") >= `start` ')
				->orderBy('start ASC')
				->limit('1');
			$res = $query->fetchArray();


			if($res)
			{
				$list = $res[0]['id'];

				$q = Doctrine_Query::create()
					->select('*')
					->from('BayernInvoiceSettings')
					->where('listid = "' . $list . '"')
					->andWhere('clientid = "' . $clientid . '"');
				$q_res = $q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$results[$v_res['option_name']] = $v_res['value'];
					}
				}
				else
				{
					//default values
					$results['max_days_amount'] = '30';
				}

				return $results;
			}
			else
			{
				return false;
			}
		}

		public function get_list_invoice_settings($list, $clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('BayernInvoiceSettings')
				->where('listid = "' . $list . '"')
				->andWhere('clientid = "' . $clientid . '"');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$results[$v_res['option_name']] = $v_res['value'];
				}
			}
			else
			{
				//default values
				$results['max_days_amount'] = '30';
			}

			return $results;
		}

	}

?>