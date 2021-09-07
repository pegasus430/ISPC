<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientSapvPrices', 'SYSDAT');

	class ClientSapvPrices extends BaseClientSapvPrices {

		public function getClientPrices($client)
		{
			$price = Doctrine_Query::create()
				->select("*")
				->from('ClientSapvPrices')
				->Where("client='" . $client . "'")
				->orderBy('sapv_type ASC');
			$prices = $price->fetchArray();

			if($prices)
			{
				foreach($prices as $k_price => $v_price)
				{
					$prices_array[$v_price['sapv_type']] = $v_price;
				}

				return $prices_array;
			}
			else
			{
				return false;
			}
		}

	}

?>