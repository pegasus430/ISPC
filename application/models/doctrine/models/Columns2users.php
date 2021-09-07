<?php

	Doctrine_Manager::getInstance()->bindComponent('Columns2users', 'SYSDAT');

	class Columns2users extends BaseColumns2users {

		public function getDefaultColumns($returnType = false, $tab)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.tab as tab, cl.columnName as cname, cl.id as colid, ct.user_selectable as selectable")
				->from('Columns2users cu')
				->leftJoin("cu.Columns2tabs ct ON ct.id = cu.c2t_id")
				->leftJoin("ct.Columnslist cl ON ct.column = cl.id")
				->where('cu.user_id =0')
				->andWhere('cl.columnName != "movetoarchive" ')//TODO-3644 Ancuta 02.12.2020
				->andWhere('cl.columnName != "movefromarchivtodischarge" ')//TODO-3644 02.12.2020
				->andWhere('ct.tab = "' . $tab . '"');
			$columnsDefault = $drop->fetchArray();

			foreach($columnsDefault as $keyCol => $valCol)
			{
				if($returnType == "tabs")
				{
					$tabsColumnsDefault[$valCol['tab']][$valCol['colid']] = $valCol;
				}
				else
				{
					$tabsColumnsDefault[$valCol['colid']] = $valCol;
				}
			}

			return $columnsDefault;
		}
        /**
         * ISPC-2479 Ancuta 01.11.2020 
         * Added new para, all_data 
         * @param unknown $userid
         * @param unknown $tab
         * @param boolean $all_data
         * @return array|Doctrine_Collection|unknown
         */
		public function getUserColumns($userid, $tab,$all_data = false)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.tab as tab, cl.columnName as cname, cl.id as colid, ct.user_selectable as selectable")
				->from('Columns2users cu')
				->leftJoin("cu.Columns2tabs ct ON ct.id = cu.c2t_id")
				->leftJoin("ct.Columnslist cl ON ct.column = cl.id")
				->where('cu.user_id ="' . $userid . '"')
				->andWhere('ct.tab = "' . $tab . '"')
				->andWhere('cl.columnName != "movetoarchive" ')//TODO-3644 02.12.2020
				->andWhere('cl.columnName != "movefromarchivtodischarge" ')//TODO-3644 02.12.2020
			    ->orderBy('cu.c2t_id ASC');
			$userColumns = $drop->fetchArray();

			if($all_data){
			    
			    return $userColumns;
    			
			} else{
			    
    			foreach($userColumns as $keyCol => $valCol)
    			{
    				$userColumnsFinal[] = $valCol['colid'];
    			}
    			array_unique($userColumnsFinal);
			}
			
			return $userColumnsFinal;
		}

	}

?>