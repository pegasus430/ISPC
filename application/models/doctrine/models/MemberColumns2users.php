<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumns2users', 'SYSDAT');

	class MemberColumns2users extends BaseMemberColumns2users {

		public function getDefaultColumns($returnType = false, $tab)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.tab as tab, cl.columnName as cname, cl.id as colid, ct.user_selectable as selectable")
				->from('MemberColumns2users cu')
				->leftJoin("cu.MemberColumns2tabs ct ON ct.id = cu.c2t_id")
				->leftJoin("ct.MemberColumnslist cl ON ct.column = cl.id")
				->where('cu.user_id =0')
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

		public function getUserColumns($userid, $tab)
		{
			$drop = Doctrine_Query::create()
				->select("*, ct.tab as tab, cl.columnName as cname, cl.id as colid, ct.user_selectable as selectable")
				->from('MemberColumns2users cu')
				->leftJoin("cu.MemberColumns2tabs ct ON ct.id = cu.c2t_id")
				->leftJoin("ct.MemberColumnslist cl ON ct.column = cl.id")
				->where('cu.user_id ="' . $userid . '"')
				->andWhere('ct.tab = "' . $tab . '"');
			$userColumns = $drop->fetchArray();

			foreach($userColumns as $keyCol => $valCol)
			{
				$userColumnsFinal[] = $valCol['colid'];
			}
			array_unique($userColumnsFinal);
			
			return $userColumnsFinal;
		}

	}

?>