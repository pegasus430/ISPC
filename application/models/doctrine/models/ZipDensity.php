<?php

	Doctrine_Manager::getInstance()->bindComponent('ZipDensity', 'SYSDAT');

	class ZipDensity extends BaseZipDensity {

		public function get_zip_density()
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('ZipDensity')
				->andWhere('inactive=0');
			$result_array = $query->fetchArray();

			if($result_array)
			{
			    foreach($result_array as $k=>$zd){
			        $result_data[$zd['zipcode']] = $zd['population_density']; 
			    }
			    
			    
				return $result_data;
			}
		}
	}

?>