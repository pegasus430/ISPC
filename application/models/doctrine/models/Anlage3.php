<?php

Doctrine_Manager::getInstance()->bindComponent('Anlage3', 'MDAT');

class Anlage3 extends BaseAnlage3 {

	public function get_anlage3_details($id,$ipid){
			
		$drop = Doctrine_Query::create()
		->select('*')
		->from('Anlage3')
		->where("id='" . $id . "'")
		->andWhere("ipid='" . $ipid . "'");
		$droparray = $drop->fetchArray();
			
		return $droparray;
	}

}

?>