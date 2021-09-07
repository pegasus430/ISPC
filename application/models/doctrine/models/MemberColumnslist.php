<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumnslist', 'SYSDAT');

	class MemberColumnslist extends BaseMemberColumnslist {
		//ispc 1739
		//we now use this table just for columns and user_table_settings for column order and visibility
		public function getColumns($tab)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.id as ctid")
				->from('MemberColumnslist cu')
				->leftJoin('cu.MemberColumns2tabs ct ON cu.id = ct.column')
				->where('tab = "' . $tab . '"')
				->orderBy('id ASC');
			$columns = $drop->fetchArray();

			foreach($columns as $kcol => $vcol)
			{
				$columnsfinal[$vcol['id']] = $vcol;
			}
			return $columnsfinal;
		}

		public function getAllColumns()
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('MemberColumnslist')
				->orderBy('id ASC');
			$columns = $drop->fetchArray();

			foreach($columns as $kcol => $vcol)
			{
				$columnsfinal[$vcol['id']] = $vcol;
			}
			return $columnsfinal;
		}
		
		public function get_specific_columns($column_ids)
		{
		    
			$drop = Doctrine_Query::create()
				->select("*")
				->from('MemberColumnslist')
				->where('id',$column_ids)
				->orderBy('id ASC');
			$columns = $drop->fetchArray();

			foreach($columns as $kcol => $vcol)
			{
				$columnsfinal[$vcol['id']] = $vcol;
			}
			return $columnsfinal;
		}

	}

?>
