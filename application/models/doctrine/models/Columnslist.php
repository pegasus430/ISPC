<?php

	Doctrine_Manager::getInstance()->bindComponent('Columnslist', 'SYSDAT');

	class Columnslist extends BaseColumnslist {

		public function getColumns($tab)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.id as ctid")
				->from('Columnslist cu')
				->leftJoin('cu.Columns2tabs ct ON cu.id = ct.column')
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
				->from('Columnslist')
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
