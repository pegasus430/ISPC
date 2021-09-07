<?php

	require_once("Pms/Form.php");

	class Application_Form_Voluntaryworkers extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
    		// get associated clients of current clientid START 
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
    		if($connected_client){
    		    $clientid = $connected_client;
    		} else{
    		    $clientid = $logininfo->clientid;
    		}
		    
		    
			$fdoc = new Voluntaryworkers();
			$fdoc->clientid = $clientid;
			$fdoc->hospice_association = $post['hospice_association'];
			
			$fdoc->inactive = $post['inactive'];
			
			if(count($post['status']) == 0 && count($post['primary_status']) == 0)
			{
				$fdoc->status = 'n'; //set to nein to look in statuses table
			}
			else if(count($post['primary_status']) != '0')
			{
				$fdoc->status = $post['primary_status'];
			}

			$fdoc->status_color = $post['status_color'];
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			if(strlen($post['birthdate'])>0){
			    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
			} else{
			    $fdoc->birthdate = "0000-00-00";
			}
			$fdoc->street = $post['street'];
			$fdoc->zip = $post['zip'];
			$fdoc->indrop = $post['indrop'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->mobile = $post['mobile'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];

			$fdoc->children = $post['children'];
			$fdoc->profession = $post['profession'];
			$fdoc->appellation = $post['appellation'];
			// Maria:: Migration ISPC to CISPC 08.08.2020
			//ISPC-2618 Carmen 30.07.2020
			$fdoc->linguistic_proficiency = $post['linguistic_proficiency'];
			//--
			$fdoc->edication_hobbies = $post['edication_hobbies'];
// 			$fdoc->working_week_days = $post['working_week_days'];
// 			$fdoc->working_hours = $post['working_hours'];
			$fdoc->has_car = $post['has_car'];
			$fdoc->special_skils = $post['special_skils'];
			//ISPC-2618 Carmen 30.07.2020
			$fdoc->limitations_uncertainties = $post['limitations_uncertainties'];
			$fdoc->measles_vaccination = $post['measles_vaccination'];
			$fdoc->received_certificate = $post['received_certificate'];
			if(strlen($post['received_certificate_date'])){
				$fdoc->received_certificate_date = date('Y-m-d 00:00:00',strtotime($post['received_certificate_date']));
			} else{
				$fdoc->received_certificate_date = "0000-00-00 00:00:00";
			}
			$fdoc->course_management = $post['course_management'];
			$fdoc->conversation_leader = $post['conversation_leader'];
			//--
			$fdoc->img_path = $post['img_path'];
			
			$fdoc->gc_certificate = $post['gc_certificate'];
			if(strlen($post['gc_certificate_date'])){
			    $fdoc->gc_certificate_date = date('Y-m-d 00:00:00',strtotime($post['gc_certificate_date']));
			} else{
			    $fdoc->gc_certificate_date = "0000-00-00 00:00:00";
			}
			
			//ISPC-2618 Carmen 28.07.2020
			$fdoc->gc_entry = $post['gc_entry'];
			$fdoc->gc_checked_by = $post['gc_checked_by'];
				
			$fdoc->confidentiality = $post['confidentiality'];
			$fdoc->health_aptitude = $post['health_aptitude'];
			$fdoc->activity_agreement = $post['activity_agreement'];
			$fdoc->photo_permission = $post['photo_permission'];
			//--
			
			if(strlen($post['engagement_date'])){
			    $fdoc->engagement_date = date('Y-m-d 00:00:00',strtotime($post['engagement_date']));
			} else{
			    $fdoc->engagement_date = "0000-00-00 00:00:00";
			}
				
			//ISPC-1977
			$fdoc->comments_availability = $post['comments_availability'];
			
			//ISPC-2401 10) add a VW with the new TAB "Ausbildung Ehrenamtliche" selected. 
			If ($post['referal_tab'] == "ineducation"){
			    $fdoc->ineducation = '1';
			}elseif ($post['referal_tab'] == "isarchived" ){
			    $fdoc->isarchived = '1';
			}
			
			//ISPC-2618 Carmen 30.07.2020
			$fdoc->bank_name = $post['bank_name'];
			$fdoc->iban = $post['iban'];
			$fdoc->bic = $post['bic'];
			$fdoc->account_holder = $post['account_holder'];
			//--
			
			$fdoc->save();

			if($fdoc)
			{
				$inserted_id = $fdoc->id;
				$new_vw_id = $fdoc->id;
				
			}

			/* ################## STATUS ######################################## */
			if(!empty($post['status']) && $inserted_id)
			{
				foreach($post['status'] as $k_status => $v_status)
				{
					$vw_statuses_data_array[] = array(
						'vw_id' => $inserted_id,
						'clientid' => $clientid,
						'status' => $v_status,
					);
				}

				$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
				$collection->fromArray($vw_statuses_data_array);
				$collection->save();
			}
			
			/* ################## AVAILABILITY #################################### */
			if(!empty($post['availability'])){
			
			    foreach($post['availability'] as $week_id => $hours_data){
			    	//ispc 1739 p.19
			    	$allday = 0;
			    	if(isset($post['availability'][$week_id]['allday']) && $post['availability'][$week_id]['allday']== "1"){
			    		$hours_data['from'] = "00:00:00";
			    		$hours_data['till'] = "00:00:00";
			    		$allday = 1;
			    	}
			        if(strlen($hours_data['from']) > 0 && strlen($hours_data['till'])){
			
			            if(strlen($hours_data['from']) > 0)
			            {
			                $hours_data['from'] = date('H:i:s', strtotime($hours_data['from']));
			            }
			
			            if(strlen($hours_data['till']) > 0)
			            {
			                $hours_data['till'] = date('H:i:s', strtotime($hours_data['till']));
			            }
			             
			            $working_data_array[] = array(
			                'vw_id' => $inserted_id,
			                'clientid' => $clientid,
			                'week_day' => $week_id,
			                'start_time' =>  $hours_data['from'],
			                'end_time' =>  $hours_data['till'],
			            	'allday' => $allday
			            );
			        }
			    }
			    $collection_w = new Doctrine_Collection('VwAvailability');
			    $collection_w->fromArray($working_data_array);
			    $collection_w->save();
			}
			
			/* ################## ACTIVITIES #################################### */
			if(!empty($post['activity']) && $inserted_id)
			{
				foreach($post['activity'] as $k => $a_values)
				{

					if(!empty($a_values['activity']))
					{
						if(strlen($a_values['date']) > 0)
						{
							$a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
						}
						else
						{
							$a_values['date'] = date('Y-m-d H:i:s');
						}

						$vw_activities_data_array[] = array(
							'vw_id' => $inserted_id,
							'clientid' => $clientid,
							'date' => $a_values['date'],
							'activity' => $a_values['activity'],
						    'team_event' => $a_values['team_event'],
						    'team_event_id' => $a_values['team_event_id'],
						    'team_event_type' => $a_values['team_event_type'],
							'comment' => $a_values['comment'],
						);
					}
				}
				$collection = new Doctrine_Collection('VoluntaryworkersActivities');
				$collection->fromArray($vw_activities_data_array);
				$collection->save();
			}

			/* ################## PATIENTS ASSOCIATION ################################### */
			
			// patient details
			$ipidq = Doctrine_Query::create()
			->select("epid,ipid")
			->from('EpidIpidMapping')
			->where('clientid = "' . $logininfo->clientid . '"');
			$ipids_array = $ipidq->fetchArray();
			
			foreach($ipids_array as $pe_val){
			    $ipid2epid[$pe_val['epid']] = $pe_val['ipid'];
			}
			
			
			foreach($post['patient_vw'] as $vv_w_id => $data_vw){
			     
			    if(strlen($data_vw['start']) > 0  ){
			        $vw_start_date[$vv_w_id] = date('Y-m-d H:i:s',strtotime($data_vw['start']));
			    }
			     
			    if(strlen($data_vw['end']) > 0  ){
			        $vw_end_date[$vv_w_id] = date('Y-m-d H:i:s',strtotime($data_vw['end']));
			    }
			    
			    // insert new - for patient associations 
			    $post['vw_parent_id'] = $inserted_id;
			    $p_vw_id = $this->insert_from_details($post,$clientid); //  add to insert to details all data
			    			
			    if($p_vw_id){
			        $pfl_cl = new PatientVoluntaryworkers();
			        $pfl_cl->ipid = $ipid2epid[$data_vw['patient_epid']];
			        $pfl_cl->vwid = $p_vw_id;
			        $pfl_cl->start_date = $vw_start_date[$vv_w_id];
			        $pfl_cl->end_date = $vw_end_date[$vv_w_id] ;
			        $pfl_cl->save();
			    }
			     
			}
			
			/* ################## PATIENTS HOSPIZ VISITS ################################# */
			if(!empty($post['simple'])){
			    foreach($post['simple'] as $sv_row_id => $sv_row_data){
			        if(strlen($sv_row_data['hospizvizit_date']) > 0)
			        {
			            $sv_row_data['date'] = date('Y-m-d H:i:s', strtotime($sv_row_data['hospizvizit_date']));
			        }
			        else
			        {
			            $sv_row_data['date'] = date('Y-m-d H:i:s');
			        }
			         
			        $hospiz_v_single_array[] = array(
			            'type' => $sv_row_data['type'],
			            'ipid' => $ipid2epid[$sv_row_data['patient_epid']],
			            'vw_id' => $inserted_id,
			            'hospizvizit_date' => $sv_row_data['date'],
			            'besuchsdauer' => $sv_row_data['besuchsdauer'],
			            'fahrtkilometer' => $sv_row_data['fahrtkilometer'],
			            'fahrtzeit' => $sv_row_data['fahrtzeit'],
			            'grund' => $sv_row_data['grund'],
			            'nightshift' => $sv_row_data['nightshift']
			        );
			    }
			    $collection_a = new Doctrine_Collection('PatientHospizvizits');
			    $collection_a->fromArray($hospiz_v_single_array);
			    $collection_a->save();
			}
			
			if(!empty($post['bulk'])){
			    foreach($post['bulk'] as $bv_row_id => $bv_row_data){
			
			        $bv_row_data['date'] = $bv_row_data['hospizvizit_date']."-01-01 00:00:00";;
			         
			        $hospiz_v_bulk_array[] = array(
			            'type' => $bv_row_data['type'],
			            'ipid' => $ipid2epid[$bv_row_data['patient_epid']],
			            'vw_id' => $inserted_id,
			            'hospizvizit_date' => $bv_row_data['date'],
			            'besuchsdauer' => $bv_row_data['besuchsdauer'],
			            'fahrtkilometer' => $bv_row_data['fahrtkilometer'],
			            'fahrtzeit' => $bv_row_data['fahrtzeit'],
			            'grund' => $bv_row_data['grund'],
			            'amount' => $bv_row_data['amount'],
			            'nightshift' => $bv_row_data['nightshift']
			        );
			    }
			    $collection_b = new Doctrine_Collection('PatientHospizvizits');
			    $collection_b->fromArray($hospiz_v_bulk_array);
			    $collection_b->save();
			}
			
			
			/* ################## VW WORK DATA ########################################### */

			if(!empty($post['work_simple'])){
			    foreach($post['work_simple'] as $sw_row_id => $sw_row_data){
			        if(strlen($sw_row_data['work_date']) > 0)
			        {
			            $sw_row_data['date'] = date('Y-m-d 00-00-00', strtotime($sw_row_data['work_date']));
			        }
			        else
			        {
			            $sw_row_data['date'] = date('Y-m-d H:i:s');
			        }
			         
			        $work_single_array[] = array(
			            'type' => $sw_row_data['type'],
			            'vw_id' => $inserted_id,
			            'work_date' => $sw_row_data['date'],
			        	'comments' => $sw_row_data['comments'],
			            'besuchsdauer' => $sw_row_data['besuchsdauer'],
			            'fahrtkilometer' => $sw_row_data['fahrtkilometer'],
			            'fahrtzeit' => $sw_row_data['fahrtzeit'],
			            'grund' => $sw_row_data['grund'],
			            'nightshift' => $sw_row_data['nightshift']
			        );
			    }
			    $collection_sw = new Doctrine_Collection('VwWorkdata');
			    $collection_sw->fromArray($work_single_array);
			    $collection_sw->save();
			}
			
			if(!empty($post['work_bulk'])){
			    foreach($post['work_bulk'] as $bw_row_id => $bw_row_data){
			
// 			        $bw_row_data['date'] = $bw_row_data['work_date']."-01-01 00:00:00";

			        if(strlen($bw_row_data['work_date']) > 0)
			        {
			            $bw_row_data['date'] = date('Y-m-d 00:00:00', strtotime($bw_row_data['work_date']));
			        }
			        else
			        {
			            $bw_row_data['date'] = date('Y-m-d 00:00:00');
			        }
			        
			        $work_bulk_array[] = array(
			            'type' => $bw_row_data['type'],
			            'vw_id' => $inserted_id,
			            'work_date' => $bw_row_data['date'],
			        	'comments' => $sw_row_data['comments'],
			            'besuchsdauer' => $bw_row_data['besuchsdauer'],
			            'fahrtkilometer' => $bw_row_data['fahrtkilometer'],
			            'fahrtzeit' => $bw_row_data['fahrtzeit'],
			            'grund' => $bw_row_data['grund'],
			            'amount' => $bw_row_data['amount'],
			            'nightshift' => $bw_row_data['nightshift']
			        );
			    }
			    $collection_bw = new Doctrine_Collection('VwWorkdata');
			    $collection_bw->fromArray($work_bulk_array);
			    $collection_bw->save();
			}
			
			
			
			
			
			/* ################## COLOR STATUSES #################################### */
			if(!empty($post['color_status'])){
			
			    foreach($post['color_status'] as $row_id => $cstatus_data){
			
		            if(strlen($cstatus_data['start_date']) > 0)
		            {
		                $cstatus_data['start_date'] = date('Y-m-d H:i:s', strtotime($cstatus_data['start_date']));
		            }
		
		            if(strlen($cstatus_data['end_date']) > 0)
		            {
		                $cstatus_data['end_date'] = date('Y-m-d H:i:s', strtotime($cstatus_data['end_date']));
		            }
		            	
		            $c_status_data_array[] = array(
		                'clientid' => $clientid,
		                'vw_id' => $inserted_id,
		                'status' => $cstatus_data['status'],
		                'start_date' =>  $cstatus_data['start_date'],
		                'end_date' =>  $cstatus_data['end_date']
		            );
			    }
			    $collection_cst = new Doctrine_Collection('VwColorStatuses');
			    $collection_cst->fromArray($c_status_data_array);
			    $collection_cst->save();
			}				
			
			//ISPC-2618 Carmen 31.07.2020
			if( ! empty($post['co_koordinator'])) {
			
				$vw_cok_arr = array(
						"clientid" => $post['clientid'],
						"vw_id" => $inserted_id,
						"vw_id_koordinator" => $post['co_koordinator'],
				);
			
				$vw_cok_obj = new VoluntaryworkersCoKoordinator();
				$vw_cok_obj->set_new_record($vw_cok_arr);
			
			}
			//--
			
			return $inserted_id;
		}
		
		public function InsertDataClient($post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
		
		
		    $fdoc = new Voluntaryworkers();
		    $fdoc->clientid = $clientid;
		    $fdoc->hospice_association = $post['hospice_association'];
		    	
		    $fdoc->inactive = $post['inactive'];
		    	
		    if(count($post['status']) == 0 && count($post['primary_status']) == 0)
		    {
		        $fdoc->status = 'n'; //set to nein to look in statuses table
		    }
		    else if(count($post['primary_status']) != '0')
		    {
		        $fdoc->status = $post['primary_status'];
		    }
		
		    $fdoc->status_color = $post['status_color'];
		    $fdoc->last_name = $post['last_name'];
		    $fdoc->first_name = $post['first_name'];
		    if(strlen($post['birthdate'])>0){
    		    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
		    } else{
    		    $fdoc->birthdate = "0000-00-00";
		    }
		    $fdoc->salutation = $post['salutation'];
		    $fdoc->street = $post['street'];
		    $fdoc->zip = $post['zip'];
		    $fdoc->indrop = $post['indrop'];
		    $fdoc->city = $post['city'];
		    $fdoc->phone = $post['phone'];
		    $fdoc->mobile = $post['mobile'];
		    $fdoc->email = $post['email'];
		    $fdoc->comments = $post['comments'];
		
		    $fdoc->children = $post['children'];
		    $fdoc->profession = $post['profession'];
		    $fdoc->appellation = $post['appellation'];
		    $fdoc->edication_hobbies = $post['edication_hobbies'];
		    $fdoc->working_week_days = $post['working_week_days'];
		    $fdoc->working_hours = $post['working_hours'];
		    $fdoc->has_car = $post['has_car'];
		    $fdoc->special_skils = $post['special_skils'];
		
		    //ISPC-1977
		    $fdoc->comments_availability = $post['comments_availability'];
		    
		    $fdoc->save();
		
		    if($fdoc)
		    {
		        $inserted_id = $fdoc->id;
		    }
		    	
		    if(!empty($_SESSION['filename']))
		    {
		        $this->move_uploaded_icon($inserted_id);
		    }
		
		    if(!empty($post['status']) && $inserted_id)
		    {
		        foreach($post['status'] as $k_status => $v_status)
		        {
		            $vw_statuses_data_array[] = array(
		                'vw_id' => $inserted_id,
		                'clientid' => $clientid,
		                'status' => $v_status,
		            );
		        }
		
		        $collection = new Doctrine_Collection('VoluntaryworkersStatuses');
		        $collection->fromArray($vw_statuses_data_array);
		        $collection->save();
		    }
		
		
		    if(!empty($post['activity']) && $inserted_id)
		    {
		        foreach($post['activity'] as $k => $a_values)
		        {
		
		            if(!empty($a_values['activity']))
		            {
		                if(strlen($a_values['date']) > 0)
		                {
		                    $a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
		                }
		                else
		                {
		                    $a_values['date'] = date('Y-m-d H:i:s');
		                }
		
		                $vw_activities_data_array[] = array(
		                    'vw_id' => $inserted_id,
		                    'clientid' => $clientid,
		                    'date' => $a_values['date'],
		                    'activity' => $a_values['activity'],
		                    'team_event' => $a_values['team_event'],
		                    'team_event_id' => $a_values['team_event_id'],
		                    'team_event_type' => $a_values['team_event_type'],
		                    'comment' => $a_values['comment'],
		                );
		            }
		        }
		        $collection = new Doctrine_Collection('VoluntaryworkersActivities');
		        $collection->fromArray($vw_activities_data_array);
		        $collection->save();
		    }
		    // 			return $fdoc;
		    return $inserted_id;
		}
		
		public function UpdateDataClient($post)
		{
		    if(!empty($_SESSION['filename']))
		    {
		        $this->move_uploaded_icon($post['did']);
		    }
		
		    $fdoc = Doctrine::getTable('Voluntaryworkers')->find($post['did']);
		
		    if($post['clientid'] > 0)
		    {
		        $fdoc->clientid = $post['clientid'];
		    }
		    if(count($post['status']) == 0 && count($post['primary_status']) == 0)
		    {
		        $fdoc->status = 'n';
		    }
		    else if(count($post['primary_status']) != '0')
		    {
		        $fdoc->status = $post['primary_status'];
		    }
		    $fdoc->inactive = $post['inactive'];
		    $fdoc->hospice_association = $post['hospice_association'];
		
		    $fdoc->status_color = $post['status_color'];
		    $fdoc->last_name = $post['last_name'];
		    $fdoc->first_name = $post['first_name'];
		    $fdoc->salutation = $post['salutation'];
		    $fdoc->street = $post['street'];
		    $fdoc->zip = $post['zip'];
		    $fdoc->city = $post['city'];
		    $fdoc->phone = $post['phone'];
		    $fdoc->mobile = $post['mobile'];
		    $fdoc->email = $post['email'];
		    $fdoc->comments = $post['comments'];
		
		
		    $fdoc->children = $post['children'];
		    $fdoc->profession = $post['profession'];
		    $fdoc->appellation = $post['appellation'];
		    $fdoc->edication_hobbies = $post['edication_hobbies'];
		    $fdoc->working_week_days = $post['working_week_days'];
		    $fdoc->working_hours = $post['working_hours'];
		    $fdoc->has_car = $post['has_car'];
		    $fdoc->special_skils = $post['special_skils'];
		
		    //ISPC-1977
		    $fdoc->comments_availability = $post['comments_availability'];
		
		    $fdoc->save();
		
		    if($fdoc)
		    {
		        $this->reset_voluntary_statuses($post['did'], $post['clientid']);
		
		        if(!empty($post['status']) && strlen($post['did']) > 0)
		        {
		            foreach($post['status'] as $k_status => $v_status)
		            {
		                $vw_statuses_data_array[] = array(
		                    'vw_id' => $post['did'],
		                    'clientid' => $post['clientid'],
		                    'status' => $v_status,
		                );
		            }
		
		            $collection = new Doctrine_Collection('VoluntaryworkersStatuses');
		            $collection->fromArray($vw_statuses_data_array);
		            $collection->save();
		        }
		        $this->reset_voluntary_activities($post['did'], $post['clientid']);
		        if(!empty($post['activity']) && strlen($post['did']) > 0)
		        {
		            foreach($post['activity'] as $k => $a_values)
		            {
		                if(!empty($a_values['activity']))
		                {
		                    if(strlen($a_values['date']) > 0)
		                    {
		                        $a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
		                    }
		                    else
		                    {
		                        $a_values['date'] = date('Y-m-d H:i:s');
		                    }
		
		                    $vw_activities_data_array[] = array(
		                        'vw_id' => $post['did'],
		                        'clientid' => $post['clientid'],
		                        'date' => $a_values['date'],
		                        'activity' => $a_values['activity'],
		                        'team_event' => $a_values['team_event'],
		                        'team_event_id' => $a_values['team_event_id'],
		                        'team_event_type' => $a_values['team_event_type'],
		                        'comment' => $a_values['comment'],
		                    );
		                }
		            }
		
		            $collection_a = new Doctrine_Collection('VoluntaryworkersActivities');
		            $collection_a->fromArray($vw_activities_data_array);
		            $collection_a->save();
		        }
		    }
		}
		
		public function InsertFromTabData($post)
		{
    		// get associated clients of current clientid START 
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
    		if($connected_client){
    		    $clientid = $connected_client;
    		} else{
    		    $clientid = $logininfo->clientid;
    		}
			
			
			if(!empty($_REQUEST['vwid']))
			{
				if($post['hidd_vwid'])
				{ //edit if it has vwid hidden
					$fdoc = Doctrine::getTable('Voluntaryworkers')->find($_REQUEST['vwid']);
					$fdoc->parent_id = $post['vw_parent_id'];
					$fdoc->hospice_association = $post['hospice_association'];
					$fdoc->status = $post['status'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					if(strlen($post['birthdate'])>0){
					    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
					} else{
					    $fdoc->birthdate = "0000-00-00";
					}
					$fdoc->salutation = $post['salutation'];
					$fdoc->street = $post['street'];
					$fdoc->zip = $post['zip'];
					$fdoc->city = $post['city'];
					$fdoc->phone = $post['phone'];
					$fdoc->mobile = $post['mobile'];
					$fdoc->email = $post['email'];
					$fdoc->comments = $post['comments'];

					$fdoc->children = $post['children'];
					$fdoc->profession = $post['profession'];
					$fdoc->appellation = $post['appellation'];
					$fdoc->edication_hobbies = $post['edication_hobbies'];
					$fdoc->working_week_days = $post['working_week_days'];
					$fdoc->working_hours = $post['working_hours'];
					$fdoc->has_car = $post['has_car'];
					$fdoc->special_skils = $post['special_skils'];

					//ISPC-1977
					$fdoc->comments_availability = $post['comments_availability'];
					
					$fdoc->save();


					if($post['hidd_vwid'] != $_REQUEST['vwid'])
					{
						$voluntary_workers_statuses = new VoluntaryworkersStatuses();
						$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($post['hidd_vwid'], $clientid);

						$statuses = $worker_statuses[$post['hidd_vwid']];
						$old_secondary_statuses = $this->retain_voluntary_statuses($post['hidd_vwid'], $clientid);
						
						if(!empty($statuses) && $post['hidd_vwid'])
						{
							foreach($statuses as $k_status => $v_status)
							{
								if($v_status == 'e') continue;
								$oldstatus = '';
								foreach($old_secondary_statuses as $koldst=>$oldst)
								{
										
									if($v_status == $oldst['status'])
									{
										$oldstatus = $oldst['status_old'];
										break;
									}
								}
								$vw_statuses_data_array[] = array(
									'vw_id' => $_REQUEST['vwid'],
									'clientid' => $clientid,
									'status' => $v_status,
									'status_old' => $oldstatus
								);
							}

							$this->reset_voluntary_statuses($_REQUEST['vwid'], $clientid);

							$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
							$collection->fromArray($vw_statuses_data_array);
							$collection->save();
						}
					}
				}
				else
				{
					//new one (no source)
					$fdoc = new Voluntaryworkers();
					$fdoc->parent_id = $post['vw_parent_id'];
					$fdoc->hospice_association = $post['hospice_association'];
					$fdoc->status = $post['status'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					if(strlen($post['birthdate'])>0){
					    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
					} else{
					    $fdoc->birthdate = "0000-00-00";
					}
					$fdoc->salutation = $post['salutation'];
					$fdoc->street = $post['street'];
					$fdoc->city = $post['city'];
					$fdoc->zip = $post['zip'];
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->phone = $post['phone'];
					$fdoc->mobile = $post['mobile'];
					$fdoc->email = $post['email'];
					$fdoc->comments = $post['comments'];


					$fdoc->children = $post['children'];
					$fdoc->profession = $post['profession'];
					$fdoc->appellation = $post['appellation'];
					$fdoc->edication_hobbies = $post['edication_hobbies'];
					$fdoc->working_week_days = $post['working_week_days'];
					$fdoc->working_hours = $post['working_hours'];
					$fdoc->has_car = $post['has_car'];
					$fdoc->special_skils = $post['special_skils'];

					//ISPC-1977
					$fdoc->comments_availability = $post['comments_availability'];
					
					$fdoc->save();
				}
			}
			else
			{
			    
				//custom new one (with source)
				$fdoc = new Voluntaryworkers();
				$fdoc->parent_id = $post['vw_parent_id'];
				$fdoc->hospice_association = $post['hospice_association'];
				$fdoc->status = $post['status'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				if(strlen($post['birthdate'])>0){
				    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
				} else{
				    $fdoc->birthdate = "0000-00-00";
				}
				$fdoc->salutation = $post['salutation'];
				$fdoc->street = $post['street'];
				$fdoc->city = $post['city'];
				$fdoc->zip = $post['zip'];
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->phone = $post['phone'];
				$fdoc->mobile = $post['mobile'];
				$fdoc->email = $post['email'];
				$fdoc->comments = $post['comments'];


				$fdoc->children = $post['children'];
				$fdoc->profession = $post['profession'];
				$fdoc->appellation = $post['appellation'];
				$fdoc->edication_hobbies = $post['edication_hobbies'];
				$fdoc->working_week_days = $post['working_week_days'];
				$fdoc->working_hours = $post['working_hours'];
				$fdoc->has_car = $post['has_car'];
				$fdoc->special_skils = $post['special_skils'];

				//ISPC-1977
				$fdoc->comments_availability = $post['comments_availability'];
				
				$fdoc->save();

				$inserted_id = $fdoc->id;

				//add statuses from
				if($post['hidd_vwid'])
				{
					$voluntary_workers_statuses = new VoluntaryworkersStatuses();
					$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($post['hidd_vwid'], $clientid);
					
					$statuses = $worker_statuses[$post['hidd_vwid']];
					$old_secondary_statuses = $this->retain_voluntary_statuses($post['hidd_vwid'], $clientid);

					if(!empty($statuses) && $post['hidd_vwid'])
					{
						foreach($statuses as $k_status => $v_status)
						{
							if ($v_status == 'e') continue;
							$oldstatus = '';
							foreach($old_secondary_statuses as $koldst=>$oldst)
							{
							
								if($v_status == $oldst['status'])
								{
									$oldstatus = $oldst['status_old'];
									break;
								}
							}
							$vw_statuses_data_array[] = array(
								'vw_id' => $inserted_id,
								'clientid' => $clientid,
								'status' => $v_status,
								'status_old' => $oldstatus
							);
						}

						$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
						$collection->fromArray($vw_statuses_data_array);
						$collection->save();
					}
				}
			}


			return $fdoc;
		}
		public function insert_from_details($post,$client = false)
		{
		    if($client){
		        $clientid = $client;
		    } else{
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
		    
			$fdoc = new Voluntaryworkers();
			$fdoc->clientid = $clientid;
			$fdoc->hospice_association = $post['hospice_association'];
				
			$fdoc->inactive = $post['inactive'];
				
			if(count($post['status']) == 0 && count($post['primary_status']) == 0)
			{
			    $fdoc->status = 'n'; //set to nein to look in statuses table
			}
			else if(count($post['primary_status']) != '0')
			{
			    $fdoc->status = $post['primary_status'];
			}
			
			$fdoc->parent_id = $post['vw_parent_id'];
			$fdoc->status_color = $post['status_color'];
			$fdoc->last_name = $post['last_name'];
			if(strlen($post['birthdate'])>0){
			    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
			} else{
			    $fdoc->birthdate = "0000-00-00";
			}
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street = $post['street'];
			$fdoc->zip = $post['zip'];
			$fdoc->indrop = 1;
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->mobile = $post['mobile'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];
			
			$fdoc->children = $post['children'];
			$fdoc->profession = $post['profession'];
			$fdoc->appellation = $post['appellation'];
			$fdoc->edication_hobbies = $post['edication_hobbies'];
			$fdoc->working_week_days = $post['working_week_days'];
			$fdoc->working_hours = $post['working_hours'];
			$fdoc->has_car = $post['has_car'];
			$fdoc->special_skils = $post['special_skils'];
			
			$fdoc->gc_certificate = $post['gc_certificate'];
			if(strlen($post['gc_certificate_date'])){
			    $fdoc->gc_certificate_date = date('Y-m-d 00:00:00',strtotime($post['gc_certificate_date']));
			} else{
			    $fdoc->gc_certificate_date = "0000-00-00 00:00:00";
			}
			
			if(strlen($post['engagement_date'])){
			    $fdoc->engagement_date = date('Y-m-d 00:00:00',strtotime($post['engagement_date']));
			} else{
			    $fdoc->engagement_date = "0000-00-00 00:00:00";
			}
			
			//ISPC-1977
			$fdoc->comments_availability = $post['comments_availability'];
			
			$fdoc->save();
			
			if($fdoc)
			{
			    $inserted_id = $fdoc->id;
			}
			
			if(!empty($post['status']) && $inserted_id)
			{
			    foreach($post['status'] as $k_status => $v_status)
			    {
			        $vw_statuses_data_array[] = array(
			            'vw_id' => $inserted_id,
			            'clientid' => $clientid,
			            'status' => $v_status,
			        );
			    }
			
			    $collection = new Doctrine_Collection('VoluntaryworkersStatuses');
			    $collection->fromArray($vw_statuses_data_array);
			    $collection->save();
			}
			
			
			if(!empty($post['activity']) && $inserted_id)
			{
			    foreach($post['activity'] as $k => $a_values)
			    {
			
			        if(!empty($a_values['activity']))
			        {
			            if(strlen($a_values['date']) > 0)
			            {
			                $a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
			            }
			            else
			            {
			                $a_values['date'] = date('Y-m-d H:i:s');
			            }
			
			            $vw_activities_data_array[] = array(
			                'vw_id' => $inserted_id,
			                'clientid' => $clientid,
			                'date' => $a_values['date'],
			                'activity' => $a_values['activity'],
			                'team_event' => $a_values['team_event'],
			                'team_event_id' => $a_values['team_event_id'],
			                'team_event_type' => $a_values['team_event_type'],
			                'comment' => $a_values['comment'],
			            );
			        }
			    }
			    $collection = new Doctrine_Collection('VoluntaryworkersActivities');
			    $collection->fromArray($vw_activities_data_array);
			    $collection->save();
			}
			
			
			
			return $inserted_id;
			
			 
		}

		public function UpdateData($post)
		{
			if(!empty($_SESSION['filename']))
			{
				$this->move_uploaded_icon($post['did']);
			}

			$fdoc = Doctrine::getTable('Voluntaryworkers')->find($post['did']);

			if($post['clientid'] > 0)
			{
				$fdoc->clientid = $post['clientid'];
			}
			if(count($post['status']) == 0 && count($post['primary_status']) == 0)
			{
				$fdoc->status = 'n';
			}
			else if(count($post['primary_status']) != '0')
			{
				$fdoc->status = $post['primary_status'];
			}
			$fdoc->inactive = $post['inactive'];
			$fdoc->hospice_association = $post['hospice_association'];

			$fdoc->status_color = $post['status_color'];
			$fdoc->last_name = $post['last_name'];
			if(strlen($post['birthdate'])>0){
			    $fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
			} else{
			    $fdoc->birthdate = "0000-00-00";
			}
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street = $post['street'];
			$fdoc->zip = $post['zip'];
			$fdoc->city = $post['city'];
			$fdoc->phone = $post['phone'];
			$fdoc->mobile = $post['mobile'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];


			$fdoc->children = $post['children'];
			$fdoc->profession = $post['profession'];
			$fdoc->appellation = $post['appellation'];
			$fdoc->edication_hobbies = $post['edication_hobbies'];
			$fdoc->working_week_days = $post['working_week_days'];
			$fdoc->working_hours = $post['working_hours'];
			$fdoc->has_car = $post['has_car'];
			$fdoc->special_skils = $post['special_skils'];

			//ISPC-1977
			$fdoc->comments_availability = $post['comments_availability'];

			$fdoc->save();

			if($fdoc)
			{
				$this->reset_voluntary_statuses($post['did'], $post['clientid']);

				if(!empty($post['status']) && strlen($post['did']) > 0)
				{
					foreach($post['status'] as $k_status => $v_status)
					{
						$vw_statuses_data_array[] = array(
							'vw_id' => $post['did'],
							'clientid' => $post['clientid'],
							'status' => $v_status,
						);
					}

					$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
					$collection->fromArray($vw_statuses_data_array);
					$collection->save();
				}
				$this->reset_voluntary_activities($post['did'], $post['clientid']);
				if(!empty($post['activity']) && strlen($post['did']) > 0)
				{
					foreach($post['activity'] as $k => $a_values)
					{
						if(!empty($a_values['activity']))
						{
							if(strlen($a_values['date']) > 0)
							{
								$a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
							}
							else
							{
								$a_values['date'] = date('Y-m-d H:i:s');
							}

							$vw_activities_data_array[] = array(
								'vw_id' => $post['did'],
								'clientid' => $post['clientid'],
								'date' => $a_values['date'],
								'activity' => $a_values['activity'],
							    'team_event' => $a_values['team_event'],
							    'team_event_id' => $a_values['team_event_id'],
							    'team_event_type' => $a_values['team_event_type'],
								'comment' => $a_values['comment'],
							);
						}
					}

					$collection_a = new Doctrine_Collection('VoluntaryworkersActivities');
					$collection_a->fromArray($vw_activities_data_array);
					$collection_a->save();
				}
			}
		}
		
		public function update_from_details($post, $filter = false)
		{
		   //print_r($post); exit;
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
			$fdoc = Doctrine::getTable('Voluntaryworkers')->find($post['did']);

			if($post['clientid'] > 0)
			{
				$fdoc->clientid = $post['clientid'];
			}
			if($filter)
			{
				/* ################# ACTIVITIES ######################### */
				/*$this->reset_voluntary_activities($post['did'],false);
				 if(!empty($post['activity']) && strlen($post['did']) > 0)
				 {
				 foreach($post['activity'] as $k => $a_values)
				 {
				 if(!empty($a_values['activity']))
				 {
				 if(strlen($a_values['date']) > 0)
				 {
				 $a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
				 }
				 else
				 {
				 $a_values['date'] = date('Y-m-d H:i:s');
				 }
				
				 $vw_activities_data_array[] = array(
				 'vw_id' => $post['did'],
				 'clientid' => $post['clientid'],
				 'date' => $a_values['date'],
				 'activity' => $a_values['activity'],
				 'team_event' => $a_values['team_event'],
				 'team_event_id' => $a_values['team_event_id'],
				 'team_event_type' => $a_values['team_event_type'],
				 'comment' => $a_values['comment'],
				 'duration' => $a_values['duration'],
				 'driving_time' => $a_values['driving_time']
				 );
				 }
				 }
				
				 $collection_a = new Doctrine_Collection('VoluntaryworkersActivities');
				 $collection_a->fromArray($vw_activities_data_array);
				 $collection_a->save();
				 }*/
				//$changedrows = json_decode($post['changedrows'], true);
				$changedrows = $post['changedrows'];
				//var_dump($changedrows); exit;
				if(!empty($changedrows) && strlen($post['did']) > 0)
				{
					foreach($changedrows as $kact=>$vact)
					{
						
// 						$updvwact = Doctrine::getTable('VoluntaryworkersActivities')->find($kact);
						$updvwact = Doctrine_Query::create()
						->select("*")
						->from('VoluntaryworkersActivities')
						->where('vw_id = ?',$post['vw_id'])
						->andWhere('id = ?',$kact)
						->limit(1)
						->fetchOne();
						
						if($updvwact && $updvwact->isdelete == '0')
						{
							if($vact['change'] == 'modified')
							{
								if(strlen($vact['date']) > 0)
								{
									$vact['date'] = date('Y-m-d H:i:s', strtotime($vact['date']));
								}
								unset($vact['change']);
									
								/*$updvwact->date = $vact['date'];
								 $updvwact->activity = $vact['activity'];
								 $updvwact->comment = $vact['comment'];
								 $updvwact->duration = $vact['duration'];
								 $updvwact->driving_time = $vact['driving_time'];*/
								foreach($vact as $kcolact=>$vcolact)
								{
									//var_dump($vcolact); exit;
									$updvwact->$kcolact = $vcolact;
								}
							}
							elseif($vact['change'] == 'deleted')
							{
								$updvwact->isdelete = '1';
							}
							$updvwact->save();
						}
						else
						{
							if(strlen($vact['date']) > 0)
							{
								$vact['date'] = date('Y-m-d H:i:s', strtotime($vact['date']));
							}
							else
							{
								$vact['date'] = date('Y-m-d H:i:s');
							}
							$insvwadct = new VoluntaryworkersActivities();
							unset($vact['change']);
							//var_dump($vact); exit;
							/*$updvwact->date = $vact['date'];
							$updvwact->activity = $vact['activity'];
							$updvwact->comment = $vact['comment'];
							$updvwact->duration = $vact['duration'];
							$updvwact->driving_time = $vact['driving_time'];*/
							$insvwadct->vw_id = $post['did'];
							$insvwadct->clientid = $post['clientid'];
							foreach($vact as $kcolact=>$vcolact)
							{
								$insvwadct->$kcolact = $vcolact;
							}
							$insvwadct->save();
						}
							
					}
				}
			}
			else 
				{
				if(count($post['status']) == 0 && count($post['primary_status']) == 0)
				{
					$fdoc->status = 'n';
				}
				else if(count($post['primary_status']) != '0')
				{
					$fdoc->status = $post['primary_status'];
				}
				$fdoc->inactive = $post['inactive'];
				$fdoc->hospice_association = $post['hospice_association'];
	
				$fdoc->status_color = $post['status_color'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->salutation = $post['salutation'];
				//ISPC-2884,Elena,14.04.2021
				$fdoc->gender = $post['gender'];
				if(strlen($post['birthdate'])){
	    			$fdoc->birthdate = date('Y-m-d',strtotime($post['birthdate']));
				} else{
	    			$fdoc->birthdate = "0000-00-00";
				}
				$fdoc->street = $post['street'];
				$fdoc->zip = $post['zip'];
				$fdoc->city = $post['city'];
				$fdoc->phone = $post['phone'];
				$fdoc->mobile = $post['mobile'];
				$fdoc->email = $post['email'];
				$fdoc->comments = $post['comments'];
	
	
				$fdoc->children = $post['children'];
				$fdoc->profession = $post['profession'];
				$fdoc->appellation = $post['appellation'];
				//ISPC-2618 Carmen 30.07.2020
				$fdoc->linguistic_proficiency = $post['linguistic_proficiency'];
				//--
				$fdoc->edication_hobbies = $post['edication_hobbies'];
	// 			$fdoc->working_week_days = $post['working_week_days'];
	// 			$fdoc->working_hours = $post['working_hours'];
				$fdoc->has_car = $post['has_car'];
				$fdoc->special_skils = $post['special_skils'];
				//ISPC-2618 Carmen 30.07.2020
				$fdoc->limitations_uncertainties = $post['limitations_uncertainties'];
				$fdoc->measles_vaccination = $post['measles_vaccination'];
				$fdoc->received_certificate = $post['received_certificate'];
				if(strlen($post['received_certificate_date'])){
					$fdoc->received_certificate_date = date('Y-m-d 00:00:00',strtotime($post['received_certificate_date']));
				} else{
					$fdoc->received_certificate_date = "0000-00-00 00:00:00";
				}
				$fdoc->course_management = $post['course_management'];
				$fdoc->conversation_leader = $post['conversation_leader'];
				//--
				$fdoc->img_deleted = $post['img_deleted'];
	
				$fdoc->gc_certificate = $post['gc_certificate'];
			    if(strlen($post['gc_certificate_date'])){
	    			$fdoc->gc_certificate_date = date('Y-m-d 00:00:00',strtotime($post['gc_certificate_date']));
				} else{
	    			$fdoc->gc_certificate_date = "0000-00-00 00:00:00";
				}
				
				//ISPC-2618 Carmen 28.07.2020
				$fdoc->gc_entry = $post['gc_entry'];
				$fdoc->gc_checked_by = $post['gc_checked_by'];
				
				$fdoc->confidentiality = $post['confidentiality'];
				$fdoc->health_aptitude = $post['health_aptitude'];
				$fdoc->activity_agreement = $post['activity_agreement'];
				$fdoc->photo_permission = $post['photo_permission'];
				//--
				
				if(strlen($post['engagement_date'])){
				    $fdoc->engagement_date = date('Y-m-d 00:00:00',strtotime($post['engagement_date']));
				} else{
				    $fdoc->engagement_date = "0000-00-00 00:00:00";
				}
				
				//ISPC-1977
				$fdoc->comments_availability = $post['comments_availability'];
				
				//ISPC-2618 Carmen 30.07.2020
				$fdoc->bank_name = $post['bank_name'];
				$fdoc->iban = $post['iban'];
				$fdoc->bic = $post['bic'];
				$fdoc->account_holder = $post['account_holder'];
				//--
				
	// print_r($fdoc); exit;
				$fdoc->save();
	
				if($fdoc)
				{
				    
				    /* ################# STATUS ######################### */
					$old_secondary_statuses = $this->retain_voluntary_statuses($post['did'], $post['clientid']);
					//var_dump($old_secondary_statuses); exit;
					$this->reset_voluntary_statuses($post['did'], $post['clientid']);
	
					if(!empty($post['status']) && strlen($post['did']) > 0)
					{
						foreach($post['status'] as $k_status => $v_status)
						{
							$oldstatus = '';
							foreach($old_secondary_statuses as $koldst=>$oldst)
							{
								
								if($v_status == $oldst['status'])
								{
									$oldstatus = $oldst['status_old'];
									break;
								}
							}
							
							$vw_statuses_data_array[] = array(
								'vw_id' => $post['did'],
								'clientid' => $post['clientid'],
								'status' => $v_status,
								'status_old' => $oldstatus
							);
						}
	
						$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
						$collection->fromArray($vw_statuses_data_array);
						$collection->save();
					}
					/* ################# AVAILABILITY NEW ISPC-2401 p9 ######################### */
					$this->reset_voluntary_availability_scheduled($post['did'],$post['clientid']);
					if(!empty($post['availability']))
					{
					    foreach($post['availability'] as $week_id => $hours_data)
					    {
					        $working_data_array[] = array(
					            'vw_id'     => $post['did'],
					            'clientid'  => $post['clientid'],
					            'week_day'  => $week_id,
					            'morning'   => $hours_data['morning']   == '1' ? "1" : "0",
					            'afternoon' => $hours_data['afternoon'] == '1' ? "1" : "0",
					            'evening'   => $hours_data['evening']   == '1' ? "1" : "0",
					            'night'     => $hours_data['night']     == '1' ? "1" : "0",
					            'allday'    => $hours_data['allday']    == '1' ? "1" : "0"
					        );
					    }
					    $collection_w = new Doctrine_Collection('VoluntaryworkersAvailabilitySchedule');
					    $collection_w->fromArray($working_data_array);
					    $collection_w->save();
					}
					
					
					
					/* ################# AVAILABILITY ######################### */
					/* $this->reset_voluntary_availability($post['did'],$post['clientid']);
					if(!empty($post['availability'])){
					
					    foreach($post['availability'] as $week_id => $hours_data){
							//ispc 1739 p.19
					    	$allday = 0;
					    	if(isset($post['availability'][$week_id]['allday']) && $post['availability'][$week_id]['allday']== "1"){
					    		$hours_data['from'] = "00:00:00";
					    		$hours_data['till'] = "00:00:00";
					    		$allday = 1;
					    	}
					        if(strlen($hours_data['from']) > 0 && strlen($hours_data['till'])){
					
					            if(strlen($hours_data['from']) > 0)
					            {
					                $hours_data['from'] = date('H:i:s', strtotime($hours_data['from']));
					            }
					
					            if(strlen($hours_data['till']) > 0)
					            {
					                $hours_data['till'] = date('H:i:s', strtotime($hours_data['till']));
					            }
					             
					            $working_data_array[] = array(
					                'vw_id' => $post['did'],
					                'clientid' => $post['clientid'],
					                'week_day' => $week_id,
					                'start_time' =>  $hours_data['from'],
					                'end_time' =>  $hours_data['till'],
					                'allday' => $allday
					            );
					        }
					    }
					    $collection_w = new Doctrine_Collection('VwAvailability');
					    $collection_w->fromArray($working_data_array);
					    $collection_w->save();
					} */
					/* ################# ACTIVITIES ######################### */
					/*$this->reset_voluntary_activities($post['did'],false);
					if(!empty($post['activity']) && strlen($post['did']) > 0)
					{
						foreach($post['activity'] as $k => $a_values)
						{
							if(!empty($a_values['activity']))
							{
								if(strlen($a_values['date']) > 0)
								{
									$a_values['date'] = date('Y-m-d H:i:s', strtotime($a_values['date']));
								}
								else
								{
									$a_values['date'] = date('Y-m-d H:i:s');
								}
	
								$vw_activities_data_array[] = array(
									'vw_id' => $post['did'],
									'clientid' => $post['clientid'],
									'date' => $a_values['date'],
									'activity' => $a_values['activity'],
									'team_event' => $a_values['team_event'],
									'team_event_id' => $a_values['team_event_id'],
									'team_event_type' => $a_values['team_event_type'],
									'comment' => $a_values['comment'],
									'duration' => $a_values['duration'],
									'driving_time' => $a_values['driving_time']
								);
							}
						}
	
						$collection_a = new Doctrine_Collection('VoluntaryworkersActivities');
						$collection_a->fromArray($vw_activities_data_array);
						$collection_a->save();
					}*/
					//ISPC - 2231 - p.3 - in the actions tab inside the VW it can happen that due to much work of the VW the lists of
					//actions are getting pretty long.
					$changedrows = json_decode($post['changedrows'], true);
					//var_dump($changedrows); exit;
					if(!empty($changedrows) && strlen($post['did']) > 0)
					{
						foreach($changedrows as $kact=>$vact)
						{						
						    $updvwact = Doctrine_Query::create()
						    ->select("*")
						    ->from('VoluntaryworkersActivities')
						    ->where('vw_id = ?',$post['vw_id'])
						    ->andWhere('id = ?',$kact)
						    ->limit(1)
						    ->fetchOne();
						    
// 							$updvwact = Doctrine::getTable('VoluntaryworkersActivities')->find($kact);
							if($updvwact && $updvwact->isdelete == '0')
							{
								if($vact['change'] == 'modified')
								{
									if(strlen($vact['date']) > 0)
									{
										$vact['date'] = date('Y-m-d H:i:s', strtotime($vact['date']));
									}
									unset($vact['change']);
									
									/*$updvwact->date = $vact['date'];
									$updvwact->activity = $vact['activity'];
									$updvwact->comment = $vact['comment'];
									$updvwact->duration = $vact['duration'];
									$updvwact->driving_time = $vact['driving_time'];*/
									foreach($vact as $kcolact=>$vcolact)
									{
										//var_dump($vcolact); exit;
										$updvwact->$kcolact = $vcolact;
									}
								}
								elseif($vact['change'] == 'deleted')
								{
									$updvwact->isdelete = '1';
								}
								$updvwact->save();
							}
							else 
							{
								if(strlen($vact['date']) > 0)
								{
									$vact['date'] = date('Y-m-d H:i:s', strtotime($vact['date']));
								}
								else
								{
									$vact['date'] = date('Y-m-d H:i:s');
								}
								$insvwadct = new VoluntaryworkersActivities();
								unset($vact['change']);
									//var_dump($vact); exit;
									/*$updvwact->date = $vact['date'];
									$updvwact->activity = $vact['activity'];
									$updvwact->comment = $vact['comment'];
									$updvwact->duration = $vact['duration'];
									$updvwact->driving_time = $vact['driving_time'];*/
									$insvwadct->vw_id = $post['did'];
									$insvwadct->clientid = $post['clientid'];
									foreach($vact as $kcolact=>$vcolact)
									{
										$insvwadct->$kcolact = $vcolact;
									}
									$insvwadct->save();
							}
							
						}
					}
	
					
					/* ################# PATIENTS ASSOCIATION ######################### */
					$ipidq = Doctrine_Query::create()
					->select("epid,ipid")
					->from('EpidIpidMapping')
					->where('clientid = "' . $logininfo->clientid . '"');
					$ipids_array = $ipidq->fetchArray();
						
					foreach($ipids_array as $pe_val){
					    $ipid2epid[$pe_val['epid']] = $pe_val['ipid'];
					}
					
					foreach($post['patient_vw'] as $vv_w_id => $data_vw){
					     
					    if(strlen($data_vw['start']) > 0  ){
					        $vw_start_date[$vv_w_id] = date('Y-m-d H:i:s',strtotime($data_vw['start']));
					    }
					    	
					    if(strlen($data_vw['end']) > 0  ){
					        $vw_end_date[$vv_w_id] = date('Y-m-d H:i:s',strtotime($data_vw['end']));
					    }
					
					    if($data_vw['custom'] == "0"){
					        // update existing
					
					        $q = Doctrine_Query::create()
					        ->update('PatientVoluntaryworkers')
					        ->set('start_date', '"' . $vw_start_date[$vv_w_id] . '"')
					        ->set('end_date', '"' . $vw_end_date[$vv_w_id] . '"')
					        ->set('change_date', '"' . date('Y-m-d H:i:s'). '"')
					        ->set('change_user',  $logininfo->userid)
					        ->where(' vwid="' . $data_vw['vwid'] . '" and ipid="' . $ipid2epid[$data_vw['patient_epid']] . '" ');
					        $q->execute();
					
					    } else{
					        // insert new
					        $post['vw_parent_id'] = $post['did'];
					        $docform = new Application_Form_Voluntaryworkers();
					        $docinfo = $docform->insert_from_details($post,$post['clientid']);
					        $post['hidd_vwid'] = $docinfo;
					        	
					        if($docinfo){
					            $pfl_cl = new PatientVoluntaryworkers();
					            $pfl_cl->ipid = $ipid2epid[$data_vw['patient_epid']];
					            $pfl_cl->vwid = $docinfo;
					            $pfl_cl->start_date = $vw_start_date[$vv_w_id];
					            $pfl_cl->end_date = $vw_end_date[$vv_w_id] ;
					            $pfl_cl->save();
					        }
					         
					    }
					}
					
					// delete associations to patients
					
					if(strlen($post['delete_voluntry_from_patients']) > 0 && $post['delete_voluntry_from_patients'] != "0"){
					    $delete_ids = explode(',',$post['delete_voluntry_from_patients']);
					
					    if(is_array($delete_ids)){
					
					        $q = Doctrine_Query::create()
					        ->update('PatientVoluntaryworkers')
					        ->set('isdelete', '1')
					        ->set('change_date', '"' . date('Y-m-d H:i:s'). '"')
					        ->set('change_user',  $logininfo->userid)
					        ->whereIn('id',$delete_ids);
					        $q->execute();
					    }
					}
					
					/* ################# PATIENTS VISITS - SIMPLE ######################### */
					if(!empty($post['simple'])){
					    foreach($post['simple'] as $sv_row_id => $sv_row_data){
					        if(strlen($sv_row_data['hospizvizit_date']) > 0)
					        {
					            $sv_row_data['date'] = date('Y-m-d H:i:s', strtotime($sv_row_data['hospizvizit_date']));
					        }
					        else
					        {
					            $sv_row_data['date'] = date('Y-m-d H:i:s');
					        }
					         
					        $hospiz_v_single_array[] = array(
					            'type' => $sv_row_data['type'],
					            'ipid' => $ipid2epid[$sv_row_data['patient_epid']],
					            'vw_id' => $post['did'],
					            'hospizvizit_date' => $sv_row_data['date'],
					            'besuchsdauer' => $sv_row_data['besuchsdauer'],
					            'fahrtkilometer' => $sv_row_data['fahrtkilometer'],
					            'fahrtzeit' => $sv_row_data['fahrtzeit'],
					            'grund' => $sv_row_data['grund'],
					            'nightshift' => $sv_row_data['nightshift']
					        );
					    }
					    $collection_sv = new Doctrine_Collection('PatientHospizvizits');
					    $collection_sv->fromArray($hospiz_v_single_array);
					    $collection_sv->save();
					}
					
					
					/* ################# PATIENTS VISITS - BULK ######################### */
					if(!empty($post['bulk'])){
					    foreach($post['bulk'] as $bv_row_id => $bv_row_data){
					
					        $bv_row_data['date'] = $bv_row_data['hospizvizit_date']."-01-01 00:00:00";;
					         
					        $hospiz_v_bulk_array[] = array(
					            'type' => $bv_row_data['type'],
					            'ipid' => $ipid2epid[$bv_row_data['patient_epid']],
					            'vw_id' => $post['did'],
					            'hospizvizit_date' => $bv_row_data['date'],
					            'besuchsdauer' => $bv_row_data['besuchsdauer'],
					            'fahrtkilometer' => $bv_row_data['fahrtkilometer'],
					            'fahrtzeit' => $bv_row_data['fahrtzeit'],
					            'grund' => $bv_row_data['grund'],
					            'amount' => $bv_row_data['amount'],
					            'nightshift' => $bv_row_data['nightshift']
					        );
					    }
					    $collection_b = new Doctrine_Collection('PatientHospizvizits');
					    $collection_b->fromArray($hospiz_v_bulk_array);
					    $collection_b->save();
					}
					
					/* ################# VW WORK - SIMPLE ######################### */
					if(!empty($post['work_simple'])){
					    foreach($post['work_simple'] as $sw_row_id => $sw_row_data){
					        if(strlen($sw_row_data['work_date']) > 0)
					        {
					            $sw_row_data['date'] = date('Y-m-d H:i:s', strtotime($sw_row_data['work_date']));
					        }
					        else
					        {
					            $sw_row_data['date'] = date('Y-m-d H:i:s');
					        }
					
					        $work_single_array[] = array(
					            'type' => $sw_row_data['type'],
					            'vw_id' => $post['did'],
					            'work_date' => $sw_row_data['date'],
					        	'comments' => $sw_row_data['comments'],
					            'besuchsdauer' => $sw_row_data['besuchsdauer'],
					            'fahrtkilometer' => $sw_row_data['fahrtkilometer'],
					            'fahrtzeit' => $sw_row_data['fahrtzeit'],
					            'grund' => $sw_row_data['grund'],
					            'nightshift' => $sw_row_data['nightshift']
					        );
					    }
					    $collection_sw = new Doctrine_Collection('VwWorkdata');
					    $collection_sw->fromArray($work_single_array);
					    $collection_sw->save();
					}
						
					/* ################# VW WORK - BULK ######################### */
					if(!empty($post['work_bulk'])){
					    foreach($post['work_bulk'] as $bw_row_id => $bw_row_data){
					        	
	// 				        $bw_row_data['date'] = $bw_row_data['work_date']."-01-01 00:00:00";
					        	
					        if(strlen($bw_row_data['work_date']) > 0)
					        {
					            $bw_row_data['date'] = date('Y-m-d 00:00:00', strtotime($bw_row_data['work_date']));
					        }
					        else
					        {
					            $bw_row_data['date'] = date('Y-m-d 00:00:00');
					        }
					        
					        $work_bulk_array[] = array(
					            'type' => $bw_row_data['type'],
					            'vw_id' => $post['did'],
					            'work_date' => $bw_row_data['date'],
					        	'comments' => $bw_row_data['comments'],
					            'besuchsdauer' => $bw_row_data['besuchsdauer'],
					            'fahrtkilometer' => $bw_row_data['fahrtkilometer'],
					            'fahrtzeit' => $bw_row_data['fahrtzeit'],
					            'grund' => $bw_row_data['grund'],
					            'amount' => $bw_row_data['amount'],
					            'nightshift' => $bw_row_data['nightshift']
					        );
					    }
					    $collection_bw = new Doctrine_Collection('VwWorkdata');
					    $collection_bw->fromArray($work_bulk_array);
					    $collection_bw->save();
					}
					
					
					
					/* ################## COLOR STATUSES #################################### */
					$this->reset_voluntary_color_statuses($post['did'],$post['clientid']);
					if(!empty($post['color_status'])){
					
					    foreach($post['color_status'] as $row_id => $cstatus_data){
					
				            if(strlen($cstatus_data['start_date']) > 0)
				            {
				                $cstatus_data['start_date'] = date('Y-m-d H:i:s', strtotime($cstatus_data['start_date']));
				            }
				
				            if(strlen($cstatus_data['end_date']) > 0)
				            {
				                $cstatus_data['end_date'] = date('Y-m-d H:i:s', strtotime($cstatus_data['end_date']));
				            }
				            	
				            $c_status_data_array[] = array(
				                'clientid' => $post['clientid'],
				                'vw_id' => $post['did'],
				                'status' => $cstatus_data['status'],
				                'start_date' =>  $cstatus_data['start_date'],
				                'end_date' =>  $cstatus_data['end_date']
				            );
					    }
					    $collection_cst = new Doctrine_Collection('VwColorStatuses');
					    $collection_cst->fromArray($c_status_data_array);
					    $collection_cst->save();
					}
					
					
					/* ################## Co-Koordinator ########################### */
					$res_result = $this->reset_voluntary_co_koordinator($post['did'], $post['co_koordinator'], $post['clientid']);
					
					if( ! empty($post['co_koordinator']) && $res_result) {
						//
						$vw_cok_arr = array(
							"clientid" => $post['clientid'],	
							"vw_id" => $post['did'],	
							"vw_id_koordinator" => $post['co_koordinator'],	
						);
						
						$vw_cok_obj = new VoluntaryworkersCoKoordinator();
						$vw_cok_obj->set_new_record($vw_cok_arr);
						
					}
				}
			}
		}

		public function UpdateAjaxData($post)
		{
			$fdoc = Doctrine::getTable('Voluntaryworkers')->findOneById($post['workerid']);

			$fdoc->hospice_association = $post[$post['workerid']]['hospice_association'];
			$fdoc->status = $post['primary_status'];
			$fdoc->salutation = $post[$post['workerid']]['salutation'];
			$fdoc->title = $post[$post['workerid']]['title'];
			$fdoc->last_name = $post[$post['workerid']]['last_name'];
			$fdoc->first_name = $post[$post['workerid']]['first_name'];
			$fdoc->birthdate = date('Y-m-d', strtotime($post[$post['workerid']]['birthd']));
			$fdoc->street = $post[$post['workerid']]['street'];
			$fdoc->zip = $post[$post['workerid']]['zip'];
			$fdoc->city = $post[$post['workerid']]['city'];
			$fdoc->phone = $post[$post['workerid']]['phone'];
			$fdoc->mobile = $post[$post['workerid']]['mobile'];
			$fdoc->email = $post[$post['workerid']]['email'];
			$fdoc->comments = $post[$post['workerid']]['comments'];


			$fdoc->children = $post[$post['workerid']]['children'];
			$fdoc->profession = $post[$post['workerid']]['profession'];
			$fdoc->appellation = $post[$post['workerid']]['appellation'];
			$fdoc->edication_hobbies = $post[$post['workerid']]['edication_hobbies'];
			$fdoc->working_week_days = $post[$post['workerid']]['working_week_days'];
			$fdoc->working_hours = $post[$post['workerid']]['working_hours'];
			$fdoc->has_car = $post[$post['workerid']]['has_car'];
			$fdoc->special_skils = $post[$post['workerid']]['special_skils'];

			//ISPC-1977
			$fdoc->comments_availability = $post[ $post['workerid'] ] ['comments_availability']; 
			
			$fdoc->save();

			return $fdoc;
		}

		public function reset_voluntary_statuses($vw_id, $client)
		{
			$reset = Doctrine_Query::create()
				->delete('*')
				->from('VoluntaryworkersStatuses')
				->where('clientid = "' . $client . '"')
				->andWhere('vw_id = "' . $vw_id . '"');
			$reset->execute();
		}
		
		public function retain_voluntary_statuses($vw_id, $client)
		{
			$retain = Doctrine_Query::create()
			->select('*')
			->from('VoluntaryworkersStatuses')
			->where('clientid = "' . $client . '"')
			->andWhere('vw_id = "' . $vw_id . '"');
			$old_statuses = $retain->fetchArray();
			if($old_statuses)
			{
				return $old_statuses;
			}
		}


		public function move_uploaded_icon($inserted_icon_id)
		{
		    // get associated clients of current clientid START
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		    if($connected_client){
		        $clientid = $connected_client;
		    } else{
		        $clientid = $logininfo->clientid;
		    }
		    	
		    if(!empty($_SESSION['filename']))
		    {
		//die($_SESSION['filename']);
		        $filename_arr = explode(".", $_SESSION['filename']);
		
		        $allowed_ext = array("jpg", "png", "gif", "jpeg");
		
		        if(in_array(strtolower($filename_arr[1]), $allowed_ext))
		        {
		
		            if(count($filename_arr >= '2'))
		            {
		                $filename_ext = $filename_arr[count($filename_arr) - 1];
		            }
		            else
		            {
		                $filename_ext = 'jpg';
		            }
		            //move icon file to desired destination /public/icons/clientid/pflege/icon_db_id.ext
		            $icon_upload_path = trim('icons_system/' . $_SESSION['filename']);
		            $icon_new_path = trim('icons_system/' . $clientid . '/voluntaryworkers/' . $inserted_icon_id . '.' . $filename_ext);
		
		            if ( ! file_exists('icons_system/' . $clientid )){
		            	mkdir('icons_system/' . $clientid );
		            }
		            if ( ! file_exists('icons_system/' . $clientid . '/voluntaryworkers/')){
		            	mkdir('icons_system/' . $clientid . '/voluntaryworkers/');
		            }
		         	
		            @unlink($icon_new_path);
		            copy($icon_upload_path, $icon_new_path);
		            unlink($icon_upload_path);
		
		            $update = Doctrine::getTable('Voluntaryworkers')->find($inserted_icon_id);
		            $update->img_path = $clientid . '/voluntaryworkers/' . $inserted_icon_id . '.' . $filename_ext;
		            $update->save();
// 		            echo trim($icon_upload_path) ."\n";
// 		            echo $logininfo->clientid ."\n";
// 		            die($update->img_path);
		            return $icon_new_path;
		        }
		    }
		}
		
		
		public function reset_voluntary_activities($vw_id, $client = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
			$q = Doctrine_Query::create()
				->update('VoluntaryworkersActivities')
				->set('isdelete', '1')
				->set('change_date', '"'.date("Y-m-d H:i:s").'"')
				->set('change_user', '"'.$logininfo->userid.'"');
			
				if($client){
    				$q->where(' clientid = "' . $client . '" AND vw_id = "' . $vw_id . '"');
				} else{
    				$q->where('vw_id = "' . $vw_id . '"');
				}
			$q->execute();
		}
		
		
		public function reset_voluntary_availability($vw_id, $client = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
			$q = Doctrine_Query::create()
				->update('VwAvailability')
				->set('isdelete', '1')
				->set('change_date', '"'.date("Y-m-d H:i:s").'"')
				->set('change_user', '"'.$logininfo->userid.'"');
			
				if($client){
    				$q->where(' clientid = "' . $client . '" AND vw_id = "' . $vw_id . '"');
				} else{
    				$q->where('vw_id = "' . $vw_id . '"');
				}
			$q->execute();
		}
		
		public function reset_voluntary_availability_scheduled($vw_id, $client = false)
		{ // Maria:: Migration ISPC to CISPC 08.08.2020
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
			$q = Doctrine_Query::create()
				->update('VoluntaryworkersAvailabilitySchedule ')
				->set('isdelete', '1')
				->set('change_date', '"'.date("Y-m-d H:i:s").'"')
				->set('change_user', '"'.$logininfo->userid.'"');
			
				if($client){
    				$q->where(' clientid = ?', $client);
    				$q->andWhere('vw_id = ?', $vw_id);
				} else{
    				$q->where('vw_id = ?',$vw_id);
				}
			$q->execute();
		}
		
		public function reset_voluntary_color_statuses($vw_id, $client = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
			$q = Doctrine_Query::create()
				->update('VwColorStatuses')
				->set('isdelete', '1')
				->set('change_date', '"'.date("Y-m-d H:i:s").'"')
				->set('change_user', '"'.$logininfo->userid.'"');
			
				if($client){
    				$q->where(' clientid = "' . $client . '" AND vw_id = "' . $vw_id . '"');
				} else{
    				$q->where('vw_id = "' . $vw_id . '"');
				}
			$q->execute();
		}
		
		public function UpdateVworkdata($post)
		{
		    if(strlen($post['work_date']) > 0)
		    {
		        $post['work_date'] = date('Y-m-d H:i:s', strtotime($post['work_date']));
		    }
		    
			$upd = Doctrine::getTable('VwWorkdata')->find($post['id']);
			$upd->work_date = $post['work_date'];
			$upd->grund = $post['grund'];
			$upd->comments = $post['comments'];
			$upd->besuchsdauer = $post['besuchsdauer'];
			$upd->fahrtkilometer = $post['fahrtkilometer'];
			$upd->fahrtzeit = $post['fahrtzeit'];
			$upd->nightshift = $post['nightshift'];
			$upd->amount = $post['amount'];
			$upd->save();
		}

		private function reset_voluntary_co_koordinator($vw_id = 0, $vw_id_koordinator=0, $client = 0) {
			
			$result =  true;
			
			$upd = Doctrine::getTable('VoluntaryworkersCoKoordinator')->findOneByClientidAndVwIdAndIsdelete( $client, $vw_id , 0);
			if ($upd instanceof VoluntaryworkersCoKoordinator) {
					
				if ($upd->vw_id_koordinator != $vw_id_koordinator) {
					//delete only if different coordinator is set
					$upd->delete();
					$result = true;
					
				} else {
					
					$result = false;
				}
				
			} 
			
			return $result;
		}
		
		//ISPC - 2114 - archive function for vw
		public function archive_unarchive_vws($vwsid, $clientid, $archive)
		{			
			if(count($vwsid) > 0)
			{
				if($archive)
				{
					$archive_vws = Doctrine_Query::create()
					->update("Voluntaryworkers")
					->set('isarchived', "1")
					->whereIn('id', $vwsid)
					->andWhere('clientid = ?', $clientid)
					->execute();
				}
				else 
				{
					$unarchive_vws = Doctrine_Query::create()
					->update("Voluntaryworkers")
					->set('isarchived', "0")
					->whereIn('id', $vwsid)
					->andWhere('clientid = ?', $clientid)
					->execute();
				}
			}
			
		}
		
		//ISPC-2401 - education function for vw // Maria:: Migration ISPC to CISPC 08.08.2020
		public function education_uneducation_vws($vwsid, $clientid, $education)
		{
		    if(count($vwsid) > 0)
		    {
		        if($education)
		        {
		            $education_vws = Doctrine_Query::create()
		            ->update("Voluntaryworkers")
		            ->set('ineducation', "1")
		            ->whereIn('id', $vwsid)
		            ->andWhere('clientid = ?', $clientid)
		            ->execute();
		        }
		        else
		        {
		            $uneducation_vws = Doctrine_Query::create()
		            ->update("Voluntaryworkers")
		            ->set('ineducation', "0")
		            ->whereIn('id', $vwsid)
		            ->andWhere('clientid = ?', $clientid)
		            ->execute();
		        }
		    }
		    
		}
	}

?> 