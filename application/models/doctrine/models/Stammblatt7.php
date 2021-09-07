<?php
	Doctrine_Manager::getInstance()->bindComponent('Stammblatt7', 'MDAT');

	class Stammblatt7 extends BaseStammblatt7 {

		function get_stammblatt7_details($ipid){
			
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Stammblatt7')
			->where("ipid='" . $ipid . "'")
			->andWhere("isdelete = 0");
			$droparray = $drop->fetchArray();
			
			return $droparray;
		}
	}
?>