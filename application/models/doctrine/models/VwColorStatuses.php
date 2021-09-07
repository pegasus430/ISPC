<?php

	Doctrine_Manager::getInstance()->bindComponent('VwColorStatuses', 'SYSDAT');

	class VwColorStatuses extends BaseVwColorStatuses {

		public function get_color_statuses($vw_id = false, $clientid = false,$now_active = false, $status = false)
		{
		    if($vw_id){
    			if(is_array($vw_id))
    			{
    				$vw_ids = $vw_id;
    			}
    			else
    			{
    				$vw_ids = array($vw_id);
    			}
		    }
		    
		    if($status){
    			if(is_array($status))
    			{
    				$statuses = $status;
    			}
    			else
    			{
    				$statuses = array($status);
    			}
		    }
		   
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VwColorStatuses')
				->where('isdelete = 0');
			
			if($vw_id){
				$drop->whereIn('vw_id', $vw_ids);
			}
			
			if($clientid)
			{
				$drop->andWhere('clientid = "' . $clientid . '"');
			}
			
			if($now_active){
			    $drop->andWhere(' (DATE("' . date('Y-m-d') . '") BETWEEN `start_date` AND `end_date`)  OR  ( DATE (start_date) <= DATE("' . date('Y-m-d', time()) . '") AND end_date = "0000-00-00 00:00:00")  ');
			}
			
			if($status)
			{
			    $drop->whereIn('status', $statuses);
			}
			
			$drop->orderBy('start_date DESC');
			$droparray = $drop->fetchArray();


			
			//insert old statuses
			/* $fdoc1 = Doctrine_Query::create();
			$fdoc1->select('*');
			$fdoc1->from('Voluntaryworkers');
			$fdoc1->whereIn('id', $vw_ids);
			if($clientid)
			{
			    $fdoc1->andWhere('clientid = "' . $clientid . '"');
			}
			$fdoc1->andWhere("isdelete = 0  ");
			$fdoc1->andWhere("indrop = 0 ");
			$old_status_arr = $fdoc1->fetchArray();
 
			if($old_status_arr){
    			foreach($old_status_arr as $key => $os){
    			    $statuses[$os['id']][0]['status'] = $os['status_color'];
    			    if($os['create_date']!= "0000-00-00 00:00:00"){
    			        $statuses[$os['id']][0]['start_date'] = date('d.m.Y',strtotime($os['create_date']));
    			    }
   			        $statuses[$os['id']][0]['end_date'] = "";
    			}
			} */
			

			
			$statuses = array();
			if($droparray)
			{
				$incr = 0;
				foreach($droparray as $k_voluntary => $v_voluntary)
				{
					$statuses[$v_voluntary['vw_id']][$incr]['status'] = $v_voluntary['status'];
					if($v_voluntary['start_date']!= "0000-00-00 00:00:00"){
    					$statuses[$v_voluntary['vw_id']][$incr]['start_date'] = date('d.m.Y',strtotime($v_voluntary['start_date']));   
					}

				    if($v_voluntary['end_date']!= "0000-00-00 00:00:00"){
    					$statuses[$v_voluntary['vw_id']][$incr]['end_date'] = date('d.m.Y',strtotime($v_voluntary['end_date']));    					
					}
					$incr++;
				}
			}

			if(count($statuses) != '0')
			{
				return $statuses;
			}
			else
			{
				return false;
			}
		}

		
		public function get_vw_ids_color_statuses_filter($vw_id = false, $clientid = false, $now_active = false, $status = false)
		{
		    if($vw_id){
    			if(is_array($vw_id))
    			{
    				$vw_ids = $vw_id;
    			}
    			else
    			{
    				$vw_ids = array($vw_id);
    			}
		    }
		    
		    if($status){
    			if(is_array($status))
    			{
    				$color_statuses = $status;
    			}
    			else
    			{
    				$color_statuses = array($status);
    			}
		    }

			$drop = Doctrine_Query::create()
				->select('*')
				->from('VwColorStatuses')
				->where('isdelete = 0');
			
			if($vw_id){
				$drop->andWhereIn('vw_id', $vw_ids);
			}
			
			if($clientid)
			{
				$drop->andWhere('clientid = "' . $clientid . '"');
			}
			$vw_color_statuses = $drop->fetchArray();
			
			if($now_active){
			    $drop->andWhere(' (DATE("' . date('Y-m-d') . '") BETWEEN `start_date` AND `end_date`)  OR  ( DATE (start_date) <= DATE("' . date('Y-m-d', time()) . '") AND end_date = "0000-00-00 00:00:00")  ');
			}
			
			if($status)
			{
			    $drop->andWhereIn('status', $color_statuses);
			}
			
			$drop->orderBy('start_date DESC');
			$droparray = $drop->fetchArray();
			
			
			$have_vw_color_statuses[] = "9999999999";
			foreach($vw_color_statuses as $k=>$vwcl){
			    $have_vw_color_statuses[] = $vwcl['vw_id'];
			}
		 
			
			$voluntary = array();
			$statuses = array();
			if($droparray)
			{
			    $incr = 0;
			    foreach($droparray as $k_voluntary => $v_voluntary)
			    {
// 			        $statuses[$v_voluntary['vw_id']][$incr]['status'] = $v_voluntary['status'];
// 			        if($v_voluntary['start_date']!= "0000-00-00 00:00:00"){
// 			            $statuses[$v_voluntary['vw_id']][$incr]['start_date'] = date('d.m.Y',strtotime($v_voluntary['start_date']));
// 			        }
			
// 			        if($v_voluntary['end_date']!= "0000-00-00 00:00:00"){
// 			            $statuses[$v_voluntary['vw_id']][$incr]['end_date'] = date('d.m.Y',strtotime($v_voluntary['end_date']));
// 			        }
			        $statuses[$v_voluntary['vw_id']][] = $v_voluntary;
			        $incr++;
			        
    			    $voluntary['filter_ids'][] = $v_voluntary['vw_id'];
			    }
			}
 
			//insert old statuses
			$fdoc1 = Doctrine_Query::create();
			$fdoc1->select('id,status_color,create_date,change_date');
			$fdoc1->from('Voluntaryworkers');
			$fdoc1->where("isdelete = 0");
			$fdoc1->andWhere("indrop = 0 ");
			
			if($clientid)
			{
			    $fdoc1->andWhere('clientid = "' . $clientid . '"');
			}

			if($status)
			{
			    $fdoc1->andWhereIn('status_color', $color_statuses);
			}

			$fdoc1->andWhereNotIn('id', $have_vw_color_statuses);
			$old_status_arr = $fdoc1->fetchArray();
			
			if($old_status_arr){
    			foreach($old_status_arr as $key => $os){
    			    $statuses[$os['id']][0]['status'] = $os['status_color'];
    			    if($os['create_date']!= "0000-00-00 00:00:00"){
    			        $statuses[$os['id']][0]['start_date'] = date('d.m.Y',strtotime($os['create_date']));
    			    }
   			        $statuses[$os['id']][0]['end_date'] = "";
   			        $voluntary['filter_ids'][] = $os['id'];
    			}
			} 
            
			$voluntary['statuses'] = $statuses;

			return $voluntary;
		}
		
		
		
		
		
		
		 public function reactivate_status(){
		     
		     // get client ids
		     $sql ='SELECT vc.* FROM voluntaryworkers_color_statuses vc 
                    INNER JOIN (SELECT vw_id, MAX( start_date) AS max_start_date,isdelete FROM voluntaryworkers_color_statuses WHERE isdelete = 0 GROUP BY vw_id) gvc  ON vc.vw_id = gvc.vw_id  AND vc.start_date = gvc.max_start_date
                    AND vc.isdelete = 0
                    AND vc.end_date != "0000-00-00 00:00:00"
                    AND vc.status != "g"';
		     
		     $resulted_vws = Doctrine_Manager::getInstance()
		     ->getConnection('SYSDAT')
		     ->getDbh()
		     ->query($sql)
		     ->fetchAll(PDO::FETCH_ASSOC);
		     
             if(!empty($resulted_vws)){
    		     foreach($resulted_vws as $k=>$vcs){
    		         
    		         $old_end_date = $vcs['end_date'];
    		         $new_start_date = date("Y-m-d H:i:s",strtotime("+1 day",strtotime($old_end_date)));
    		         
    		         $vw_statuses_data_array[] = array(
    		             'vw_id' => $vcs['vw_id'],
    		             'clientid' => $vcs['clientid'],
    		             'status' => "g",
    		             'start_date' => $new_start_date,
    		         );
    		     }
    		     
    		     if(!empty($vw_statuses_data_array)){
        		     $collection = new Doctrine_Collection('VwColorStatuses');
        		     $collection->fromArray($vw_statuses_data_array);
        		     $collection->save();
    		     }
             }
		     
		 }
		 

		 /**
		  * ISPC-2440  Lore 04.09.2019
		  * @param unknown $clientid
		  * @return array|Doctrine_Collection
		  */
		 public function get_color_statuses_change($clientid)
		 {
		     $daysago = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))).'' ;
		     $today = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))).' 23:59:59' ;   // changed on 18.12.2019
		     
		     $drop = Doctrine_Query::create()
		     //->select('DISTINCT(vw_id) as vw_id')
		     ->select('*')
		     ->from('VwColorStatuses')
		     ->where('isdelete = 0')
		     ->andWhere('clientid = "' . $clientid . '"')
		     ->andWhere('start_date BETWEEN "' . $daysago. '"  AND  "'. $today.'" ')       // changed on 18.12.2019
		     ->groupBy('vw_id')
		     ->orderBy('start_date  DESC');
		     $droparray = $drop->fetchArray();
		    
  	         return $droparray;

		 }
		 
	}

?>