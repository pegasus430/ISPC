<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumns2tabs', 'SYSDAT');

	class MemberColumns2tabs extends BaseMemberColumns2tabs {

		public function getTabsColumns($tab = false)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('MemberColumns2tabs');
			
			if($tab !== false){
				$drop->where('tab = ?', $tab );
			}	
				
			$tabsColumns = $drop->fetchArray();

			
			foreach($tabsColumns as $keyCol => $valCol)
			{
				$tabsColumnsDefault[$valCol['tab']][$valCol['column']] = $valCol['id'];
			}

			return $tabsColumnsDefault;
		}

	}

?>