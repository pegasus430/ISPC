<?php

	Doctrine_Manager::getInstance()->bindComponent('SymptomatologyValues', 'SYSDAT');

	class SymptomatologyValues extends BaseSymptomatologyValues {

		public function getSymptpomatologyValues($setid, $multi_arr = true)
		{
			$set = Doctrine_Query::create()
				->select('*, ss.name as set_name, sv.value as sym_description')
				->from('SymptomatologyValues sv')
				->leftJoin('sv.SymptomatologySets ss')
				->where('isdelete = 0');
			if(is_array($setid))
			{
				$set->andWhereIn('sv.set', $setid);
			}
			else
			{
				$set->andWhere('sv.set = "' . $setid . '"');
			}
			$set->orderBy('id');
			$values = $set->fetchArray();

			if(sizeof($values) > 0)
			{
				if($multi_arr)
				{
					foreach($values as $kset => $vset)
					{
						$valarr[$vset['id']] = $vset;
						$valarr[$vset['id']]['sym_description'] = utf8_encode($vset['sym_description']);
					}
					return $valarr;
				}
				else
				{
					foreach($values as $kset => $vset)
					{
						$valarr[$vset['id']] = $vset['id'];
					}
					return $valarr;
				}
			}
		}

		public function getSymptpomatologyValueData($symptomid)
		{
			$set = Doctrine_Query::create()
				->select('*, sv.value as sym_description')
				->from('SymptomatologyValues sv')
				->where('id="' . $symptomid . '"')
				->andWhere('isdelete = 0')
				->orderBy('id');
			$value = $set->fetchArray();

			if($value)
			{
				return $value;
			}
		}

		
		
		public static function getDefaults ($setids = array()) {

		    if ( empty($setids)) {
		        return;
		    }
		    
		    if( ! is_array($setids)) {
		        $setids = array($setids);
		    }
		    
		    return Doctrine_Query::create()
		    ->select('*,
		        CONVERT(CONVERT(value USING utf8) USING latin1) as value')
		    ->from('SymptomatologyValues INDEXBY id')
		    ->whereIn('set', $setids)
		    ->andWhere('isdelete = 0')
		    ->orderBy('id')
		    ->fetchArray();		    
		}
		
	}

?>