<?php
Doctrine_Manager::getInstance()->bindComponent('Anlage2', 'MDAT');

class Anlage2 extends BaseAnlage2 {

  public function get_anlage2_details($id,$ipid){
			
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Anlage2')
			->where("id='" . $id . "'")
			->andWhere("ipid='" . $ipid . "'");
			//echo $drop->getSqlQuery();exit;
			$droparray = $drop->fetchArray();
			
			return $droparray;
		}

}

?>