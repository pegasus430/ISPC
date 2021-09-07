<?php

	Doctrine_Manager::getInstance()->bindComponent('Columns2tabs', 'SYSDAT');

	class Columns2tabs extends BaseColumns2tabs {

		public function getTabsColumns()
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('Columns2tabs');
			$tabsColumns = $drop->fetchArray();

			foreach($tabsColumns as $keyCol => $valCol)
			{
				$tabsColumnsDefault[$valCol['tab']][$valCol['column']] = $valCol['id'];
			}

			return $tabsColumnsDefault;
		}

	}

?>