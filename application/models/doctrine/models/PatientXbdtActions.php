<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientXbdtActions', 'MDAT');

	class PatientXbdtActions extends BasePatientXbdtActions {
	    
	    public function get_actions($ipid, $cond = null)
	    {
	    	//ISPC-2746 Carmen 07.12.2020
	    	if(!is_array($ipid))
	    	{
	    		$ipid = array($ipid);
	    	}
	    	//--
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientXbdtActions')
	        //ISPC-2746 Carmen 07.12.2020
	        //->where('ipid =  "'.$ipid.'"')
	        ->whereIn('ipid', $ipid)
	        //--
	        ->andWhere('isdelete = 0')
	        ->orderBy("ipid, action_date ASC"); //ISPC-2746 Carmen 07.12.2020
	        $q_res = $query->fetchArray();
	        
	        if($q_res )
	        {
	           return $q_res;
	        }
	        else
	        {
	           return false;    
	        }
	    }
	    
	    public function get_patient_actions($ipid, $edit_source = false)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientXbdtActions')
	        ->where('ipid =  "'.$ipid.'"')
	        ->andWhere('isdelete = 0');
	        if($edit_source){
	           $query->andWhere('edited_from = "'.$edit_source.'"');
	        }
	        $query->orderBy("action_date ASC");
	        $q_res = $query->fetchArray();
	        
	        if($q_res )
	        {
	           return $q_res;
	        }
	        else
	        {
	           return false;    
	        }
	    }
	    
	    
	    public function get_actions_filtered($cond = null)
	    {
	    	$query = Doctrine_Query::create()
	    	->select('pa.*, xa.*')
	    	->from('PatientXbdtActions pa')
	    	->leftJoin('pa.XbdtActions xa');
	    	if(!empty($cond['ipids'])) {
	    		$query->whereIn('pa.ipid', $cond['ipids']);
	    	}
	    	if(!empty($cond['client'])) {
	    		$query->andWhere('xa.clientid = "'.$cond['client'].'"');
	    		$query->andWhere('pa.clientid = "'.$cond['client'].'"');
	    	}
	    	
	    	if(!empty($cond['actions'])) {
	    		$query->andWhereIn('pa.id', $cond['actions']);
	    	}
	    	
	    	if(!empty($cond['start']) && !empty($cond['end'])) {
	    		$query->andWhere('pa.action_date >=  "'.$cond['start'].'"');
	    		$query->andWhere('pa.action_date <= "'.$cond['end'].'"');
	    	}
	    	
	    	if(!empty($cond['user'])) {
	    		$query->andWhere('pa.userid = "'.$cond['user'].'"');
	    	}
	    	
	    	if(!empty($cond['group'])) {
	    		$query->andWhere('xa.groupname = "'.$cond['group'].'"');
	    	}

	    	
	    	if(!empty($cond['file_id'])) {
	    		$query->andWhere('pa.file_id =?', $cond['file_id']);
	    	}
	    	elseif($cond['all']) {
	    		//$query->andWhere('pa.file_id = 0');
	    	}
	    	else
	    	{
	    		$query->andWhere('pa.file_id = 0');
	    	}
	    	
	    	$query->andWhere('pa.isdelete = 0')
	    			->orderBy("pa.action_date ASC");
	    	
	    	$q_res = $query->fetchArray();
	    	 
	    	if($q_res )
	    	{
	    		return $q_res;
	    	}
	    	else
	    	{
	    		return false;
	    	}
	    }
	    
	    //used only in ContactForms.php
	    public function get_actions_by_ipid_id($ipid, $id = array())
	    {
	    	$query = Doctrine_Query::create()
	    	->select('*')
	    	->from('PatientXbdtActions')
	    	->where('ipid =  ? ' ,$ipid)
	    	->andWhere('isdelete = 0');
	    	
	    	if ( ! empty($id) && is_array($id)) {
	    		
	    		$query->whereIn('id' , $id);
	    	}
	    	
	    	if(($q_res = $query->fetchArray()) && ! empty($q_res) )
	    	{
	    		return $q_res;
	    	}
	    	else
	    	{
	    		return false;
	    	}
	    }
	    
	    
	}

?>