<?php

	Doctrine_Manager::getInstance()->bindComponent('Paycenters', 'SYSDAT');

	class Paycenters extends BasePaycenters {

		public function get_paycenter($zip = false, $clientid = false)
		{
			if($zip)
			{
				$paycenters = PaycenterZip::get_paycenterzip($zip);

				if($paycenters)
				{
					$paycenter_q = Doctrine_Query::create()
						->select('*')
						->from('Paycenters')
						->where('isdelete = "0"')
						->andWhereIn('id', $paycenters)
						->andWhere('client = "' . $clientid . '"')
						->orderBy('id DESC')
						->limit('1');

					$paycenter_res = $paycenter_q->fetchArray();
					if($paycenter_res)
					{
						return $paycenter_res[0]['paycenter'];
					}
					else
					{
						return false;
					}
				}
			}
		}

	}

?>