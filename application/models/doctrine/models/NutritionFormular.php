<?php

Doctrine_Manager::getInstance()->bindComponent('NutritionFormular', 'MDAT');

class NutritionFormular extends BaseNutritionFormular {
	
	public static function get_by_id ($id, $ipid = null) 
	{	
		$row = Doctrine_Query::create()
		->select('*')
		->from('NutritionFormular')
		->where('id=?', $id)
		->andWhere('ipid = ?', $ipid)
		->andWhere('isdelete = 0')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

		if (!empty($row['formular_values'])) {
			$row['formular_values'] = json_decode($row['formular_values'], true);
		}
		
		return $row;
	}

	public static function get_by_ipid ($ipid = null) 
	{
		$row = Doctrine_Query::create()
		->select('*')
		->from('NutritionFormular')
		->Where('ipid = ?', $ipid)
		->andWhere('isdelete = 0')
		->orderBy('id DESC')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	
		if (!empty($row['formular_values'])) {
			$row['formular_values'] = json_decode($row['formular_values'], true);
		}
	
		return $row;
	}
	
	public static function get_multiple_by_ipid ($ipid = array() , $params = null ) 
	{
		$colums2fetch = "*";
		
		if (empty($ipid)) {
			return; //nothing to do
		}
		
		if (!empty($params['colums2fetch'])){
			$colums2fetch =  $params['colums2fetch'];
		}
			
		$rows = Doctrine_Query::create()
		->select($colums2fetch)
		->from('NutritionFormular')
		->WhereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->orderBy('id DESC')
		->fetchArray();
	
		foreach ($rows as $k=>$row) {
			if (!empty($row['formular_values'])) {
				$rows[$k]['formular_values'] = json_decode($row['formular_values'], true);
			}	
		}
		
	
		return $rows;
	}
	
	
		
}

?>