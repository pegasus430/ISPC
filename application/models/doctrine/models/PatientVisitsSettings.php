<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientVisitsSettings', 'IDAT');

	class PatientVisitsSettings extends BasePatientVisitsSettings {

		public function getPatientVisitsSettings($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientVisitsSettings');
			if(is_array($ipid))
			{
				$drop->whereIn('ipid', $ipid);
			}
			else
			{
				$drop->where("ipid='" . $ipid . "'");
			}
			$loc = $drop->fetchArray();

			
			if($loc)
			{
				return $loc;
			}
			else
			{
				return false;
			}
		}
	
		/*
		 * ispc-1533
		 * this function if all f* up ... re-write when time 
		 */
		public static function getPatientVisitsSettingsV2( $ipid , $dayofweek = false , $ipis_array = false)
		{
			
			if (empty($ipid)){
				return;
			}
			
			if(!is_array($ipid))
			{
				$ipid = array($ipid);
			}
			
			
			
			$get = Doctrine_Query::create()
			->select("visit_day, visit_hour, visits_per_day, visitor_type, visitor_id, ipid, visit_duration")
			->from('PatientVisitsSettings')
			->whereIn('ipid', $ipid)
			->andWhere('isdeleted = ?', 0);
			
			if( (int)$dayofweek > 0 ){
				$get->andWhere("visit_day = ?" , (int)$dayofweek);
			}

			$get = $get->fetchArray();
			
			
			/* 
			array( visitor_id = array( visitor_type = array( day = value ) ) )	
			*/
			$duration = self::visitDuration($ipid);
				
			foreach ($get as &$row) {
			    if (isset($duration[$row['ipid']])) {
			        $row['visit_duration'] = $duration[$row['ipid']]['visit_duration'];
			    }
			}
			
			$final = array();
			foreach ($get as $k => $v){
				if ($dayofweek === false && $ipis_array == false){
					$final[ $v['visitor_id'] ] [ $v['visitor_type'] ] [ $v['visit_day'] ] = $v['visits_per_day'];
					if ($v['visitor_id'] ==0 && $v['visit_duration'] > 0) {
					   $final['visit_duration'] = $v['visit_duration'];
					}
				}else{		
					$final [$v['ipid']] [ $v['visitor_id'] ] [ $v['visitor_type'] ] [ $v['visit_day'] ] = $v['visits_per_day'];
					if ($v['visitor_id'] ==0 &&  $v['visit_duration'] > 0) {
					   $final [$v['ipid']] ['visit_duration'] = $v['visit_duration'];
					}
				}
			}
			
			//this visit duration is the same for all
			//this if is from 1(one) patient->stamdaten->tourenplanung = box43
//			if ( isset($get[0]) && $dayofweek === false ){
// 				$final ['visit_duration'] =  $get[0]["visit_duration"];
//			}
			return $final;
		}
		

		/*
		 * ispc-1533 
		 */
		public static function getPatientVisitsSettingsV3( $ipid , $dayofweek = false , $ipis_array = false){
			
			if(!is_array($ipid))
			{
				$ipid = array($ipid);
			}
			
			$get = Doctrine_Query::create()
			->select("visit_day, visit_hour, visits_per_day, visitor_type, visitor_id, ipid, visit_duration")
			->from('PatientVisitsSettings')
			->whereIn('ipid', $ipid)
			->andWhere('isdeleted = ?', 0)
			->andWhere('visits_per_day <> ?', 0);
			
			if( (int)$dayofweek > 0 ){
				$get->andWhere("visit_day = ?" , (int)$dayofweek);
			}

			$get = $get->fetchArray();
		
			
			$duration = self::visitDuration($ipid);
			
			foreach ($get as &$row) {
			    if (isset($duration[$row['ipid']])) {
			        $row['visit_duration'] = $duration[$row['ipid']]['visit_duration'];
			    }
			}
			
			
			return $get;
		}
		
		/**
		 * @cla on 04.04.2019 - changed to a inner join
		 * 
		 * @param array(strings)|string $ipids
		 * @return array
		 */
		public static function visitDuration($ipids = array())
		{
		    if (empty($ipids)) {
		        return;
		    }
		    if ( ! is_array($ipids)) {
		        $ipids = array($ipids);
		    }
		    /*
		    $getIds = Doctrine_Query::create()
		    ->select("MAX(id)")
		    ->from('PatientVisitsSettings t2')
		    ->whereIn('ipid', $ipids)
		    ->andWhere('visitor_id = 0')
		    ->andWhere('isdeleted = 0')
		    ->groupBy('ipid')
		    ;
		    
		    $get = Doctrine_Query::create()
		    ->select("id, ipid, visit_duration")
		    ->from('PatientVisitsSettings t1 INDEXBY ipid')
		    ->where('id IN ('.$getIds->getDql().')', $ipids)
		    ->andWhereIn('ipid', $ipids)
		    ->andWhere('visitor_id = 0')
		    ->andWhere('isdeleted = 0')
		    ->fetchArray()
		    ;
		    */		
		    
		    $placeholder_ipids = str_repeat ('?, ',  count ($ipids) - 1) . '?';
		    
		    //version LEFT JOIN
		    /*
		    $querystr = "SELECT id, ipid, visit_duration FROM 
		    (
    	        (SELECT MAX(id) as max_id from patient_visits_settings WHERE ipid IN ({$placeholder_ipids}) AND visitor_id = 0 and isdeleted = 0 GROUP BY ipid) AS t1
    	        LEFT JOIN patient_visits_settings AS t2 ON t1.max_id = t2.id 
		    )";
		    */

		    //version INNER JOIN
		    $querystr = "SELECT t1.id, t1.ipid, t1.visit_duration FROM patient_visits_settings t1
		    INNER JOIN
		      (SELECT MAX(id) AS max_id FROM patient_visits_settings WHERE ipid IN ({$placeholder_ipids}) AND visitor_id = 0 and isdeleted = 0 GROUP BY ipid) t2
		    ON t1.id = t2.max_id
		    ";
		    
		    
		     
		    $conn = Doctrine_Core::getTable('PatientVisitsSettings')->getConnection();
		    $query = $conn->prepare($querystr);
		    $query->execute(array_values($ipids));
		    $get = $query->fetchAll(PDO::FETCH_ASSOC);
		    if ($get) {
		        $get = array_column($get, null, 'ipid');
		    }
		    return $get;
		}
		
		/*
		 * ispc-1533
		 * get the patients from that cleintid that have settings for this date 
		 */
		public function get_Patients_with_VisitsSettings_of_client($clientid = false, $date = false, $visitor_type = false , $ipid = false){		
			
			$get = Doctrine_Query::create()
				->select("id, ipid, clientid, visits_per_day, visit_duration, visit_day, visit_hour, visitor_type, visitor_id")
				->from('PatientVisitsSettings vs')
				->Where('vs.isdeleted = ?', 0);
					
			if ($clientid !==false && !empty($clientid)){
				
				if (!is_array($clientid)) $clientid =  array($clientid);
				$get->andWhereIn('clientid', $clientid);
			
			}
			
			if ($ipid !==false){
				$get->andWhere('vs.ipid = ?', $ipid);
			}
			
			$get->leftJoin("vs.PatientMaster pm");
			$get->andWhere('pm.isdischarged = ?', 0);
			$get->andWhere('pm.isdelete = ?', 0);
			$get->andWhere('pm.isstandbydelete = ?', 0);

			
			if( $date !== false ){
				
				$day_of_the_week = date("N",  strtotime($date)); //1 to 7
				$get->andWhere("visit_day = ?" , (int)$day_of_the_week);
			
			}
			if( $visitor_type !== false ){
				if (is_array($visitor_type)){
					$get->andWhereIn("visitor_type" , $visitor_type);
				}
				else{
					$get->andWhere("visitor_type = ?" , $visitor_type);
				}
					
			}
			
				
			
			$get = $get->fetchArray();
			return $get;
		
		}
		/*
		 * ispc-1533
		 */
		public function setPatientVisitsSettings($ipid, $values = false , $update = false){
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if ($values['isdeleted'] == "1"){
				//just isdeleted old user
			
				$isdeleted = Doctrine_Query::create()
				->update('PatientVisitsSettings')
				->set('isdeleted', '1')
				->set('visits_per_day', '0')			
				->where('ipid = ? ' , $ipid);
				if( !empty($values['visitor_type']) ){
					$isdeleted ->andWhere('visitor_type = ?',  $values['visitor_type']);
				}
				if( !empty($values['visitor_id']) ){
					$isdeleted ->andWhere('visitor_id = ?',  $values['visitor_id']);
				}
				$isdeleted ->execute();
				
				return true;
			}
			
			if (empty ($values['visit'])){ 
				return false;
			}
			
			foreach ( $values['visit'] as $k => $v ){
				$patient = Doctrine::getTable('PatientVisitsSettings')->findByIpidAndVisit_dayAndVisitor_typeAndVisitor_id( $ipid, $k, $values['visitor_type'], $values['visitor_id']);
				$patient = $patient{0};
				if (!empty($patient->id)){
					//update
					$patient->isdeleted = 0;
					$patient->visits_per_day = (int)$v;
					$patient->clientid = $clientid;
					$patient->save();					
				}else{
					//insert new day
					$insert = new PatientVisitsSettings();
					$insert->visit_day = (int)$k;
					$insert->visits_per_day = (int)$v;
					$insert->visitor_type = $values['visitor_type'];
					$insert->visitor_id = $values['visitor_id'];
					$insert->clientid = $clientid;
					$insert->ipid = $ipid;
					$insert->isdeleted = 0 ;
					$insert->save();
				}
			}

			return true;
		}
		
	/**
	 * taken from PatientControll -> patientdetailsAction
	 * 
	 * used in PatientMaster->getMasterData_extradata()
	 * 
	 * @return multitype:number unknown Ambigous <number, boolean> string Ambigous <multitype:, unknown>
	 */
	public function fetch_PatientVisitsSettings( $ipid = '' )
	{
// 	    $ipid = $this->ipid;
// 	    $logininfo = $this->logininfo;
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    $pdata = [];
	
	    $pat_visits_settings = $this->getPatientVisitsSettingsV2($ipid);
	    $disabled_users = array();
	    if ( count( $pat_visits_settings ) > 0 ){
	         
	         
	        if ($pat_visits_settings['visit_duration'] == "" || $pat_visits_settings['visit_duration'] == "0"){
	            $getClientDataByid = Client :: getClientDataByid($logininfo->clientid);
	            $pat_visits_settings['visit_duration'] = (int)$getClientDataByid[0] ['tagesplanung_default_visit_time'] ;
	        }
	         
	        $pdata['visit_duration'] = $pat_visits_settings['visit_duration'];
	         
	        //get also the user that can visit
	        $visiting_users = User::get_all_visiting_users_and_groups( $logininfo->clientid, false, false );
	        $visiting_users_array = $visiting_users['user_details'];
	         
	        foreach($visiting_users_array as $user){
	            if (	($user['makes_visits'] == "0")
	                ||
	                (	( $user['isactive'] == "1" )
	                    &&
	                    ( strtotime($user['isactive_date']) <= strtotime(date("Y-m-d") ))
	                )
	            )
	            {
	                //this doctor was marked as cannot make visit, but he allready has assigned ones
	                $disabled_users[] = $user['id'];
	            }
	        }
	         
	        $normal_group= array();
	        foreach($visiting_users['grups'] as $k=>$v){
	            $normal_group = ($normal_group+$v + array($k=>$k));
	        }
	         
	        foreach($pat_visits_settings as $user => $type){
	            reset($type);
	            $type = key($type);
	             
	            if (	($visiting_users_array[ $user ] ['makes_visits'] == "0")
	                ||
	                (	( $visiting_users_array[ $user ] ['isactive'] == "1" )
	                    &&
	                    ( strtotime($visiting_users_array[ $user ] ['isactive_date']) <= strtotime(date("Y-m-d") ))
	                )
	            )
	            {
	                //this doctor was marked as cannot make visit, but he allready has assigned ones
	                $pat_visits_settings[$user]['is_disabled'] = true;
	            }
	             
	            if ($type == "pseudogrups"){
	                if (empty($visiting_users['pseudogrups'][$user])){
	                    unset( $pat_visits_settings[$user] );
	                }
	            }else{
	                if (empty($normal_group[$user])){
	                    unset( $pat_visits_settings[$user] );
	                }
	            }
	             
	        }
	        if (empty($disabled_users)) $disabled_users = array( 0 => "9999999");
	        //print_r($pat_visits_settings);
	        //print_r($visiting_users);	die();
	        $pdata['pat_visits_settings'] = $pat_visits_settings;
	        $pdata['pat_visits_settings_visiting_users'] = $visiting_users;
	        $pdata['pat_visits_settings_disabled_users'] = json_encode($disabled_users);
	         
	    }else{
	        //get default visit time of this client
	        $getClientDataByid = Client :: getClientDataByid($logininfo->clientid);
	        $pdata['visit_duration'] = (int)$getClientDataByid[0] ['tagesplanung_default_visit_time'] ;
	        $pdata['pat_visits_settings_disabled_users'] = json_encode( array( 0 => "9999999") );
	    }
	     
	    return $pdata;
	     
	}
	

}

?>