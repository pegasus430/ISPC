<?php
/**
 * 
 * @author claudiu
 *
 */
class MedicationClientBTMCorrection extends BaseMedicationClientBTMCorrection 
{

	public function get_by_correction_new_id ( $correction_new_id = array(), $clientid = 0 )
	{
		if ( empty($correction_new_id) || !is_array($correction_new_id) ) {
			return;
		}
		
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientBTMCorrection')
		->Where('clientid = ? ', $clientid)
		->andWhereIn('correction_new_id', $correction_new_id)
		->andWhere('isdelete = 0');
		
// 		Pms_DoctrineUtil::get_raw_sql($result);
							
		
		return $result->fetchArray();
	}
	
	public function get_by_correction_id ( $correction_id = array(), $clientid = 0 )
	{
		if ( empty($correction_id)  || !is_array($correction_id)) {
			return;
		}
	
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientBTMCorrection')
		->Where('clientid = ? ', $clientid)
		->andWhereIn('correction_id', $correction_id)
		->andWhere('isdelete = 0');
	
		// 		Pms_DoctrineUtil::get_raw_sql($result);
			
	
		return $result->fetchArray();
	}
	
	public function get_by_correction_table ( $correction_table = "" , $clientid = 0 )
	{
		if ( empty($correction_table) ) {
			return;
		}
	
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientBTMCorrection INDEXBY correction_id')
		->Where('clientid = ? ', $clientid)
		->andWhere('correction_table = ? ', $correction_table)
		->andWhere('isdelete = 0');
	
		// 		Pms_DoctrineUtil::get_raw_sql($result);
			
	
		return $result->fetchArray();
	}
	
	public function get_by_correction_table_correction_id ( $correction_table = "",  $correction_id = array(), $clientid = 0 )
	{
		if ( empty($correction_id)  || !is_array($correction_id)) {
			return;
		}
	
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientBTMCorrection')
		->Where('clientid = ? ', $clientid)
		->andWhere('correction_table = ?', $correction_table)
		->andWhereIn('correction_id', $correction_id)
		->andWhere('isdelete = 0');
	
		// 		Pms_DoctrineUtil::get_raw_sql($result);
			
	
		return $result->fetchArray();
	}

}
?>