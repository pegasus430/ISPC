<?php

	Doctrine_Manager::getInstance()->bindComponent('SymptomatologyZapvDetails', 'SYSDAT');

	class SymptomatologyZapvDetails extends BaseSymptomatologyZapvDetails {

		public function getSymptpomatologyZapvDetails($setid, $alias = false, $other_alias  = false, $multi_arr = true)
		{
			$set = Doctrine_Query::create()
				->select('*, sv.value as sym_description')
				->from('SymptomatologyValues sv')
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
				foreach($values as $ks => $vs)
				{
					$symp_v[$vs['id']] = $vs['details_select'];
					$symp_v_selects[] = $vs['details_select'];
				}

				if(sizeof($symp_v_selects) > 0)
				{
					$zapv_select = Doctrine_Query::create()
						->select('*')
						->from('SymptomatologyZapvDetails ')
						->where('isdelete = 0')
						->andWhereIn('select_id', $symp_v_selects);
					$zapv_select_values = $zapv_select->fetchArray();

					foreach($zapv_select_values as $zk => $zv)
					{
						$select_details[$zv['select_id']][$zv['id']] = utf8_encode($zv['item_name']);
					}
				}

				if($multi_arr)
				{
					foreach($values as $kset => $vset)
					{
						$valarr[$vset['id']] = $vset;
						if($alias && !empty($vset['alias']))
						{
							$valarr[$vset['id']]['sym_description'] = utf8_encode($vset['alias']);
						}
						elseif($other_alias && !empty($vset['other_alias']))
						{
							$valarr[$vset['id']]['sym_description'] = utf8_encode($vset['other_alias']);
						}
						else
						{
							$valarr[$vset['id']]['sym_description'] = utf8_encode($vset['sym_description']);
						}
						
						$valarr[$vset['id']]['zapv_select'] = $select_details[$vset['details_select']];
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

		public function getSymptpomatologyZapvDetailsData($selects_id)
		{
			$set = Doctrine_Query::create()
				->select('*')
				->from('SymptomatologyZapvDetails')
				->where('isdelete = 0');
			if(is_array($selects_id))
			{
				$set->andWhereIn('select_id', $selects_id);
			}
			else
			{
				$set->andWhere('select_id = "' . $selects_id . '"');
			}
			$value = $set->fetchArray();

			if($value)
			{
				foreach($value as $zk => $zv)
				{
					$details_array[$zv['select_id']][$zv['id']]['details_id'] = $zv['id'];
					$details_array[$zv['select_id']][$zv['id']]['details_description'] = utf8_encode($zv['item_name']);
				}

				return $details_array;
			}
		}

		public function getSymptpomatologyZapvItems()
		{
			$set = Doctrine_Query::create()
				->select('*')
				->from('SymptomatologyZapvDetails')
				->where('isdelete = 0');
			$value = $set->fetchArray();

			if($value)
			{
				foreach($value as $zk => $zv)
				{
					$details_array[$zv['id']] = utf8_encode($zv['item_name']);
				}

				return $details_array;
			}
		}

	}

?>