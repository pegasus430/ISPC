<?php
Doctrine_Manager::getInstance()->bindComponent('PatientNraapv', 'MDAT');

class PatientNraapv extends BasePatientNraapv
{

	public function get_patient_nraapv($ipid)
	{
		$sql = "*,AES_DECRYPT(patient_name,'" . Zend_Registry::get('salt') . "') as patient_name";
		$sql .=",AES_DECRYPT(patient_phone,'" . Zend_Registry::get('salt') . "') as patient_phone";
		$actions_sql = Doctrine_Query::create()
		->select($sql)
		->from('PatientNraapv')
		->where("ipid= '" . $ipid . "' ")
		->andWhere('isdelete = 0');
		$actionsarray = $actions_sql->fetchArray();

		if($actionsarray)
		{
			return $actionsarray;
		}
	}
}