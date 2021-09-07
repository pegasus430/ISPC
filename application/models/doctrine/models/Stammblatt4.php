<?php
	Doctrine_Manager::getInstance()->bindComponent('Stammblatt4', 'MDAT');

	class Stammblatt4 extends BaseStammblatt4 {

		function get_stammblatt4_details($ipid){
			
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Stammblatt4')
			->where("ipid='" . $ipid . "'")
			->andWhere("isdelete = 0");
			$droparray = $drop->fetchArray();
			
			return $droparray;
		}
	}
?>