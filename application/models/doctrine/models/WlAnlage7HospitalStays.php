<?php
Doctrine_Manager::getInstance()->bindComponent('WlAnlage7HospitalStays', 'MDAT');

class WlAnlage7HospitalStays extends BaseWlAnlage7HospitalStays {

	function get_anlage7_hospital_stays($ipid, $anlage7_form_id = false){
	
		$fdoc = Doctrine_Query::create()
		->select("*")
		->from('WlAnlage7HospitalStays')
		->where("ipid='" . $ipid . "'");
		if($anlage7_form_id){
			$fdoc->andWhere("anlage7_form_id='" . $anlage7_form_id . "'");
		}
		$fdoc->andWhere('isdelete=0');
		$anlage_7_data_array = $fdoc->fetchArray();
		
		foreach($anlage_7_data_array as $k=>$values){
			$hospital_stays[$k]['period']=$values['period'];
			$hospital_stays[$k]['reason']=$values['reason'];
		}
		
		return $hospital_stays;
	}
}
?>