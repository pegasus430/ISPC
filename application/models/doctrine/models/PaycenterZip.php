<?php

	Doctrine_Manager::getInstance()->bindComponent('PaycenterZip', 'SYSDAT');

	class PaycenterZip extends BasePaycenterZip {

		public function get_paycenterzip($zip)
		{
			$paycenter_zips_q = Doctrine_Query::create()
				->select("*")
				->from('PaycenterZip')
				->where("TRIM(zip) LIKE '%" . trim(rtrim($zip)) . "%'")
				->andWhere('isdelete = 0');
			$paycenter_zips_res = $paycenter_zips_q->fetchArray();

			if($paycenter_zips_res)
			{
				$paycenters[] = '999999999999';
				foreach($paycenter_zips_res as $k_paycenter_zip => $v_paycenter_zip)
				{
					$paycenters[] = $v_paycenter_zip['paycenter'];
				}

				if(!empty($paycenters))
				{
					return $paycenters;
				}
				else
				{
					return false;
				}
			}
		}

	}

?>