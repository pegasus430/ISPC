<?php
Doctrine_Manager::getInstance()->bindComponent('FormBlockLmuVisit', 'MDAT');
class FormBlockLmuVisit extends BaseFormBlockLmuVisit
{

	public function getPatientFormBlockLmuVisit ( $ipid, $contact_form_id, $allow_deleted = false)
	{

		$groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockLmuVisit')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('contact_form_id ="' . $contact_form_id . '"');
		if(!$allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		$groupsarray = $groups_sql->fetchArray();


		if ($groupsarray)
		{
			return $groupsarray;
		}
	}
	
	/**
	 * @author Ancuta duplicated by Carmen 
	 * ISPC-2515 ISPC-2512 ISPC-2683/16.10.2020
	 * @param unknown $ipids
	 * @param boolean $period
	 * @return void|array|Doctrine_Collection
	 */
	public static function get_patients_chart($ipids, $period = false)
	{
		if ( empty($ipids)) {
			return;
		}
	
		if( ! is_array($ipids))
		{
			$ipids = array($ipids);
		}
		else
		{
			$ipids = $ipids;
		}
	
	
		$cf = new ContactForms();
		$delcf = $cf->get_patients_deleted_contactforms($ipids);
	
		$delcform = array();
	
		foreach ($delcf as $key_ipid => $valcf)
		{
			foreach($valcf as $kdcf=>$vcfdel)
			{
				$delcform[] = $vcfdel;
			}
		}
	
	
		$sql_period_params = array();
	
		if($period)
		{
			$sql_period = ' (DATE(vigilance_awareness_date) != "0000-00-00" AND vigilance_awareness_date BETWEEN ? AND ? ) ';
	
			$sql_period_params = array( $period['start'], $period['end'] );
		}
		else
		{
			$sql_period = ' DATE(vigilance_awareness_date) != "0000-00-00"  ';
		}
	
		$patient = Doctrine_Query::create()
		->select('*')
		->from('FormBlockLmuVisit')
		->where('isdelete= "0" ')
		->andWhereIn('ipid', $ipids)
		->orderBy('vigilance_awareness_date ASC');
	
		if ( ! empty($delcform)) {
			$patient->andwhereNotIn("contact_form_id",$delcform);
		}
	
		if ( ! empty($sql_period)) {
			$patient->andWhere( $sql_period , $sql_period_params);
		}
	
		$patientlimit = $patient->fetchArray();
	
		return $patientlimit;
	}

}
?>
