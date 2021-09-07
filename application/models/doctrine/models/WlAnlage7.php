<?php
Doctrine_Manager::getInstance()->bindComponent('WlAnlage7', 'MDAT');

class WlAnlage7 extends BaseWlAnlage7 {

	function get_anlage7_data($ipid, $anlage7_form_id = false){

		$fdoc = Doctrine_Query::create()
		->select("*")
		->from('WlAnlage7')
		->where("ipid='" . $ipid . "'");
		if($anlage7_form_id){
			$fdoc->andWhere("id='" . $anlage7_form_id . "'");
		}
		$fdoc->andWhere('isdelete=0');
		$anlage_7_data_array = $fdoc->fetchArray();
		
		return $anlage_7_data_array[0];
	}
	
	function get_anlage7_data_multiple($ipids){
		
		$fdoc = Doctrine_Query::create()
		->select("*")
		->from('WlAnlage7')
		->whereIn("ipid",$ipids);
		$fdoc->andWhere('isdelete=0');
		$anlage_7_data_array = $fdoc->fetchArray();
		foreach($anlage_7_data_array as $k=>$ipdd){
			$anlage_7_data[$ipdd['ipid']] = $ipdd;
		}
		
		return $anlage_7_data;
	}
}
?>