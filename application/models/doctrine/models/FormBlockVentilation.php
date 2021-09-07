<?php
	
class FormBlockVentilation extends BaseFormBlockVentilation {
		

	/**
	 * functio will return one single item
	 * 
	 * @param string $ipid
	 * @param integer $contact_form_id
	 * @param bool $allow_deleted
	 * @return Ambigous <multitype:unknown , multitype:, Doctrine_Collection>
	 */
	public function get_by_ipid_and_formularid($ipid, $contact_form_id, $allow_deleted = false)
	{
		
				
		$user_array = array();

		$groups_sql = Doctrine_Query::create()
			->select('*')
			->from('FormBlockVentilation')
			->Where("contact_form_id = ?" , $contact_form_id )
			->andWhere("ipid = ? " , $ipid );	
					
		if( ! $allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		
		
		if($groupsarray = $groups_sql->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY))
		{
			$user_array = $groupsarray;				
		}
		return $user_array;
	}//end get_by_ipid_and_formularid

	
	
	
	
	public static function get_multiple_by_ipid ($ipid = array() , $params = null ) 
	{
		$colums2fetch = "*";
		$result =  array();
		
		if (empty($ipid)) {
			return $result;
		}
		
		if (!empty($params['colums2fetch'])){
			$colums2fetch =  $params['colums2fetch'];
		}
			
		
		$cf = new ContactForms();
		$delcf = $cf->get_deleted_contactforms_by_ipid($ipid);
// 		if (!empty($delcf)) {			
// 			$delcf = array_column($delcf, 'recordid');
// 		}
		

		$q = Doctrine_Query::create()
		->select($colums2fetch)
		->from('FormBlockVentilation')
		->WhereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->orderBy('id DESC');
		
		if (!empty($delcf) && is_array($delcf)) {			
			$q ->andwhereNotIn("contact_form_id", $delcf);
// 			die(print_r($delcf));
		}
		
		
		$rows = $q->fetchArray();
	
		foreach ($rows as $k=>$row) {
			
			if ( ! isset($result [$row['ipid']]) 
				&& 
				(
					 trim($row['modus']) != "" 
					|| trim($row['f_tot']) != ""
					|| trim($row['vt']) != ""
					|| trim($row['mv']) != ""
					|| trim($row['peep']) != ""
					|| trim($row['pip']) != ""
					|| trim($row['o2_l_min']) != ""
					|| trim($row['i_e']) != ""
					|| trim($row['i_e']) != ""
					|| trim($row['freetext']) != ""
				)
			) {
				
				$result [$row['ipid']] = $row ;

			}	
		}
		
	
		return $result;
	}
	
	
	
	
	/**
	 * @author Ancuta 
	 * ISPC-2515 ISPC-2512  #ISPC-2512PatientCharts
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
	    
	    $contact_forms_in_period = $cf->get_multiple_contact_form_period($ipids,$period,$delcform);
	    $period_cf_ids  = array();
	    $cf_date = array();
	    if(!empty($contact_forms_in_period)){
	        foreach($contact_forms_in_period as $ipid=>$cfdata){
	            foreach($cfdata as $k=>$cfs){
	                $period_cf_ids[] = $cfs['id'];
	                $cf_date[$cfs['id']] = $cfs['billable_date'];
	                
	            }
	        }
	    }
	    if(empty($period_cf_ids)){
	        return;
	    }
	    
	    $patient_vent = array();
	    $patient_vent = Doctrine_Query::create()
	    ->select('*')
	    ->from('FormBlockVentilation')
	    ->where('isdelete= "0" ')
	    ->andWhereIn('ipid', $ipids)
	    ->andwhereIn("contact_form_id",$period_cf_ids)
	    ->fetchArray();
	    
	    foreach($patient_vent as $k=>$vent_data){
	        $patient_vent[$k]['ventilation_info_date'] = $cf_date[$vent_data['contact_form_id']];
	    }
	    
	    return $patient_vent;
	}
	
	
	
}	
	
?>