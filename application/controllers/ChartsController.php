<?php
/**
 *
 * @author Ancuta
 * #ISPC-2512PatientCharts
 *
 * ! be aware ! i've setup the viewRenderer to use .phtml !
 *
 */


class ChartsController extends Pms_Controller_Action 
{
    private $_use_altered_timezone = (APPLICATION_ENV != 'production' ? true : false);
    
    public function init()
    {
        
//         date_default_timezone_set('UTC');
        
        //    ISPC-791 secrecy tracker
        $user_access = PatientPermissions::document_user_acces();
        
        //Check patient permissions on controller and action
        $patient_privileges = PatientPermissions::checkPermissionOnRun();
        
        if(!$patient_privileges)
        {
            $this->_redirect(APP_BASE . 'error/previlege');
        }
        
        
        
    	/* Initialize action controller here */
    	$this
    	->setActionsWithPatientinfoAndTabmenus([
    			/*
    			 * actions that have the patient header
    			*/
    			'overview',
    	])
    	->setActionsWithJsFile([
    			"overview",
    			"navigation",
    	]);
    	
    	//phtml is the default for zf1 ... but on bootstrap you manualy set html :(
    	$this->getHelper('viewRenderer')->setViewSuffix('phtml');

    	$usr = new User();
	    $allus = $usr->getUserByClientid($this->logininfo->clientid, '0', true, true);
	    foreach($allus as $kus =>$vus)
	    {
	    	$all_users[$vus['id']] = $vus;
	    }
	    $this->all_users = $all_users;
	    
	    //get client data
	    $this->client_details = Client::getClientDataByid($this->logininfo->clientid);
    	 
    }
    
    private function _chart_timestamp_datetime($str_date)
    {
        $timezone = new DateTimeZone('UTC');
        
        $format = 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat($format, $str_date, $timezone);
        
        $unix_timestamp = $date->getTimestamp();
        
        $js_date = $unix_timestamp * 1000;
        
        return $js_date;
    }
    
    
    private function _chart_timestamp_datetime_utc($str_date)
    {
        $timezone = new DateTimeZone('UTC');
        
        $format = 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat($format, $str_date, $timezone);
        
        $unix_timestamp = $date->getTimestamp();
        
        $js_date = $unix_timestamp * 1000;
        
        return $js_date;
    }
    
    private function _chart_timestamp($unix_timestamp, $hour, $minute, $second = 0)
    {
        //return js milliseconds date
        
        $year = date('Y', $unix_timestamp);
        $month = date('m', $unix_timestamp);
        $day = date('d', $unix_timestamp);
        
        $js_date = mktime($hour, $minute, $second, $month, $day, $year);
        
        
        $js_date = $js_date * 1000;
        
        return $js_date;
    }
    
    private function _chart_timestamp_old($unix_timestamp, $hour, $minute, $second = 0)
    {
        //return js milliseconds date
        
        $year = gmdate('Y', $unix_timestamp);
        $month = gmdate('m', $unix_timestamp);
        $day = gmdate('d', $unix_timestamp);
        
        $js_date = gmmktime($hour, $minute, $second, $month, $day, $year);
        
        
        $js_date = $js_date * 1000;
        
        return $js_date;
    }
    
    
    public function overviewAction() 
    {
    	$ipid = $this->ipid;
    	
     
    	$chart_blocks = array(
         'vital_signs',//DONE
         'awake_sleep_status',
         'symptomatology',//DONE
    	 'symptomatologyII',//DONE ISPC-2516 carmen 15.07.2020
         'organic_entries_exits',//DONE
//          'organic_entries_exits_bilancing',//DONE ISPC-2661
         'organic_entries_exits_bilancing_oe',//DONE ISPC-2661
         'medication_actual',//DONE
         'medication_isbedarfs',//DONE
         'medication_iscrisis',//DONE
         'medication_isivmed', //ISPC-2871,Elena,30.03.2021
         'medication_isschmerzpumpe',
         'medication_ispumpe', //ISPC-2871,Elena,12.04.2021
         'artificial_entires_exits',//DONE
         'positioning',//DONE
         //'positioning_individual',//DEPRECATED
         'ventilation_info',//DONE
         'suckoff_events',//DONE
         'custom_events',//DONE
         'vigilance_awareness_events', //ISPC-2683 Carmen 16.10.2020
		 'beatmung', //ISPC-2697
         'pcoc_phase'//IM-153 TODO-4163
    	);
    	
   	
    	//ISPC-2841 Lore 22.03.2021
    	// get saved client events
    	$client_events = Doctrine_Query::create()->select("*")
    	->from("ClientEvents")
    	->where("clientid = ?", intval($this->logininfo->clientid))
    	->andWhere('show_in_chart = 1')
    	->andWhere('isdelete = 0')
    	->fetchArray();
    	$show_in_chart = array_column($client_events, 'event_name');
    	
    	$show_in_chart_events = array();
    	foreach($show_in_chart as $key => $vals){
    	    if( $vals == 'medication_dosage_interaction' ){
    	        $show_in_chart_events[] = 'medication_actual';
    	        $show_in_chart_events[] = 'medication_isbedarfs';
    	        $show_in_chart_events[] = 'medication_iscrisis';
    	        $show_in_chart_events[] = 'medication_isschmerzpumpe';
    	        $show_in_chart_events[] = 'medication_ispumpe';
    	        $show_in_chart_events[] = 'medication_isivmed';
    	    } elseif ($vals == 'suckoff' || $vals == 'vigilance_awareness' ) {
    	        $show_in_chart_events[] = $vals.'_events';
        	} elseif ($vals == 'organic_entries_exits') {
        	    $show_in_chart_events[] = $vals;
        	    $show_in_chart_events[] = 'organic_entries_exits_bilancing_oe';
        	} elseif ($vals == 'artificial_entries_exits') {           //Lore 13.04.2021
        	    $show_in_chart_events[] = 'artificial_entires_exits';
        	} else {
        	    $show_in_chart_events[] = $vals;
        	}
    	}
    	$chart_blocks = $show_in_chart_events;
    	//dd($show_in_chart_events);
    	//.

        //ISPC-2871,Elena,12.04.2021
        $modules =  new Modules();
        $clientid = $this->logininfo->clientid;
        $pumpe_perfusor = $modules->checkModulePrivileges("251", $clientid);
        if(!$pumpe_perfusor){
            $chart_blocks = array_diff($chart_blocks, ['medication_ispumpe']);
        }


    	$this->view->chart_blocks  = $chart_blocks ;
    	
    	
    	//Set default chart
    	$chart_interval = 'week';
    	// Get saved data 
    	$data = array();
    	$data['user'] = $this->logininfo->userid;
    	$data['ipid'] = $ipid;
    	$saved_view_mode = $this->__get_user_view_mode($data);
    	if($saved_view_mode ){
    	    $chart_interval = $saved_view_mode;
    	}
    	
    	
    	//Set default period for overview
    	$current_date = date('Y-m-d H:i:s');
    	$current_date_utc = $this->_chart_timestamp_datetime($current_date);
    	
    	$period = array();
    	$period['start_date'] = $this->_chart_timestamp_datetime($current_date);
    	$period['start_date_ts'] = date('d.m.Y',$current_date_utc/1000);
    	
    	
    	if($chart_interval == 'oneday'){
    	   $period['end_date'] = $this->_chart_timestamp_datetime($current_date);
    	   $period['end_date_ts'] = date('d.m.Y',$current_date_utc/1000);
    	
    	}
    	//ISPC-2661 pct.10 Carmen 15.09.2020
    	elseif($chart_interval == '12hours'){
    		$period['start_date'] = strtotime('- 12 hours', $this->_chart_timestamp_datetime($current_date));
    		$period['start_date_ts'] = date('d.m.Y H:i:s',strtotime('- 12 hours', $current_date_utc/1000));
    		
    		$twelve_hours = date('Y-m-d H:i:s',strtotime('+ 12 hours', strtotime('- 12 hours',strtotime($current_date))));
    	    $end_date_utc = $this->_chart_timestamp_datetime($twelve_hours);
    	    $period['end_date'] = $end_date_utc;
    	    $period['end_date_ts'] = date('d.m.Y H:i:s',$end_date_utc/1000);
    	}
    	elseif($chart_interval == '4hours'){
    		$period['start_date'] = strtotime('- 4 hours', $this->_chart_timestamp_datetime($current_date));
    		$period['start_date_ts'] = date('d.m.Y H:i:s',strtotime('- 4 hours', $current_date_utc/1000));
    		
    		$twelve_hours = date('Y-m-d H:i:s',strtotime('+ 4 hours', strtotime('- 4 hours',strtotime($current_date))));
    	    $end_date_utc = $this->_chart_timestamp_datetime($twelve_hours);
    	    $period['end_date'] = $end_date_utc;
    	    $period['end_date_ts'] = date('d.m.Y H:i:s',$end_date_utc/1000);
    		 
    	}
    	//--
    	elseif($chart_interval == 'threedays'){
    	   
           //ISPC-2661 pct.9 Carmen 15.09.2020
    	   /* $three_days = date('Y-m-d H:i:s',strtotime('+2 day',strtotime($current_date)));
    	   $end_date_utc = $this->_chart_timestamp_datetime($three_days);
    	   $period['end_date'] = $end_date_utc;
    	   $period['end_date_ts'] = date('d.m.Y',$end_date_utc/1000); */
           
    	   $period['start_date'] = strtotime('-2 day', $this->_chart_timestamp_datetime($current_date));
    	   $period['start_date_ts'] = date('d.m.Y', strtotime('-2 day', $current_date_utc/1000));
    	  
    	   $three_days = date('Y-m-d H:i:s',strtotime('+2 day',strtotime('-2 day',strtotime($current_date))));
    	   $end_date_utc = $this->_chart_timestamp_datetime($three_days);
    	   $period['end_date'] = $end_date_utc;
    	   $period['end_date_ts'] = date('d.m.Y',$end_date_utc/1000);
    	   //--
    	
    	} elseif($chart_interval == 'week'){
    	    
    		//ISPC-2661 pct.9 Carmen 15.09.2020
    	    /* $week_days = date('Y-m-d H:i:s',strtotime('+6 day',strtotime($current_date)));
    	    $end_w_date_utc = $this->_chart_timestamp_datetime($week_days);
    	    
    	    $period['end_date'] = $end_w_date_utc;
    	    $period['end_date_ts'] = date('d.m.Y',$end_w_date_utc/1000); */
    		
    		$period['start_date'] = strtotime('-6 day', $this->_chart_timestamp_datetime($current_date));
    		$period['start_date_ts'] = date('d.m.Y', strtotime('-6 day', $current_date_utc/1000));
    		
    		$week_days = date('Y-m-d H:i:s',strtotime('+6 day',strtotime('-6 day',strtotime($current_date))));
    		$end_w_date_utc = $this->_chart_timestamp_datetime($week_days);
    			
    		$period['end_date'] = $end_w_date_utc;
    		$period['end_date_ts'] = date('d.m.Y',$end_w_date_utc/1000);
    		//--
    	}
    	
    	$this->view->chart_interval  = $chart_interval;
    	$this->view->period = $period;


    	if (1==2 && $this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
			$decid = Pms_Uuid::decrypt($_REQUEST['patid']);
			$ipid = Pms_CommonData::getIpId($decid);
			$clientid = $this->logininfo->clientid;

	    	switch($_REQUEST['action']) 
	    	{
	    		case 'show_form': 
	    			switch($_REQUEST['form'])
	    			{
	    				case 'dosage_interaction':
	    				    $form = new Application_Form_PatientDrugPlan();
	    					
	    				    $values = $_REQUEST;
	    					
	    				    $dosage_interaction_form = $form->create_dosage_interaction($values);
	    					$this->getResponse()->setBody($dosage_interaction_form)->sendResponse();
	    					
	    					exit;
	    					break;
	    					
	    				//ISPC-2516 Carmen 09.04.2020
	    				case 'awakesleepingadd':
	    					$afb = new Application_Form_FormBlockAwakeSleepingStatus();
	    					
	    					if($_REQUEST['recid'])
	    					{
	    						$values = FormBlockAwakeSleepingStatusTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
	    					}
	    					
	    					$awake_sleeping_form = $afb->create_form_block_awake_sleeping_status($values);
	    					$this->getResponse()->setBody($awake_sleeping_form)->sendResponse();
	    					
	    					exit;
	    					break;
	    				//--
	    				//ISPC-2522 Carmen 10.04.2020
	    				case 'positioningadd':
	    					$afb = new Application_Form_FormBlockPositioning();
	    						
	    					if($_REQUEST['recid'])
	    					{
	    						$values = FormBlockPositioningTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
	    					}
	    					
	    					$positioning_form = $afb->create_form_block_positioning($values);
	    					$this->getResponse()->setBody($positioning_form)->sendResponse();
	    							
	    					exit;
	    					break;
	    				//--
    					//ISPC-2523 Carmen 13.04.2020
    					case 'suckoffadd':
    						$afb = new Application_Form_FormBlockSuckoff();
    						
    						if($_REQUEST['recid'])
    						{
    							$values = FormBlockSuckoffTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    						}
    						
    						$suckoff_form = $afb->create_form_block_suckoff($values);
    						$this->getResponse()->setBody($suckoff_form)->sendResponse();
    							
    						exit;
    						break;
    					//--
    					//ISPC-2518+ISPC-2520 Carmen 14.04.2020
    					case 'organicentriesexitsadd':
    						//get the options box from the client list
    						$client_options = OrganicEntriesExitsListsTable::getInstance()->findAllOptions($clientid);
    						
    						$afb = new Application_Form_FormBlockOrganicEntriesExits(array(
    								"_client_options"		=> $client_options,
    						));
    						
    						if($_REQUEST['recid'])
    						{
    							$values = FormBlockOrganicEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    						}
    						
    						$oee_form = $afb->create_form_block_organic_entries_exits($values);
    						$this->getResponse()->setBody($oee_form)->sendResponse();
    								
    						exit;
    						break;
    					//--
    					//ISPC-2519 Carmen 15.04.2020
    					case 'customeventadd':
    						$afb = new Application_Form_FormBlockCustomEvent();
    						
    						if($_REQUEST['recid'])
    						{
    							$values = FormBlockCustomEventTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    						}
    						
    						$custev_form = $afb->create_form_block_custom_event($values);
    						$this->getResponse()->setBody($custev_form)->sendResponse();
    								
    						exit;
    						break;
    					//--
    					//ISPC-2508 Carmen 21.04.2020
    					case 'artificialentriesexitsadd':    							
    						//get the options box from the client list
    						$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
    							
    						$afb = new Application_Form_Stammdatenerweitert(array(
    								"_client_options"		=> $client_options,
    						));
    						
    						if($_REQUEST['recid'])
    						{
    							$values = PatientArtificialEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    						}
    						
	    					if($_REQUEST['artaction'] && $_REQUEST['artaction'] == 'remove')
                            {
                                $values['action'] = 'remove';
                                $values['subaction'] = 'remove';
                            }
                            elseif($_REQUEST['artaction'] && $_REQUEST['artaction'] == 'refresh')
                            {
                            	$values['action'] = 'remove';
                            	$values['subaction'] = 'refresh';
                            } 
    							
    						$artee_form = $afb->create_form_artificial_entries_exits($values);
    						$this->getResponse()->setBody($artee_form)->sendResponse();
    						
    						exit;
    						break;
    					//--
    						case 'symptomatologyadd':
    							$afb = new Application_Form_PatientSymptomatology(array(
    								'_block_name'           => 'Charts',
    							));
    							
    							$sympt_form = $afb->create_form_symptomatology($values);
    							$this->getResponse()->setBody($sympt_form)->sendResponse();
    						
    							exit;
    							break;
    							
    							//ISPC-2523 Carmen 13.04.2020
    						case 'contact_form':
    						    $contact_forms = new ContactForms();
    						    if($_REQUEST['recid'])
    						    {
    						      $contact_form_id  = $_REQUEST['recid'];
    						      $contact_form_details = $contact_forms->get_contact_form($contact_form_id);
//     						      $contact_form_details = array();
//     						      $contact_form_details = $contact_form_details_array[0];
    						      
    						      
    						      $user =  new User();
    						      $users_details = array();
    						      $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);
    						          
						          $form_types = new FormTypes();
						          $contact_form_types = $form_types->get_form_types($this->logininfo->clientid);
						          $contact_form_types_final = array();
						          foreach($contact_form_types as $k_form_type => $v_form_type)
						          {
						              $contact_form_types_final[$v_form_type['id']] = $v_form_type['name'];
						          }
    						      
						          $contact_form_details['form_type_name'] = $contact_form_types_final[$contact_form_details['form_type']];
						          $contact_form_details['start_date_time'] = date('H:i',strtotime($contact_form_details['start_date'])).' - '.date('H:i',strtotime($contact_form_details['end_date'])).' '.date('d.m.Y',strtotime($contact_form_details['start_date'])) ;
						          $contact_form_details['create_user_name'] = $users_details[$contact_form_details['create_user']];
						          
    						    }
//     						    dd($contact_form_details);
    						    $suckoff_form = '';
    						    $suckoff_form .= '<table class="cfc_info">'; 
    						    $suckoff_form .= '<tr>'; 
    						    $suckoff_form .= '<td class="cfc_label">'.$this->view->translate('contact_form_type').'</td>';
    						    $suckoff_form .= '<td>'.$contact_form_details['form_type_name'].'</td>';
    						    $suckoff_form .= '</tr>'; 
    						    $suckoff_form .= '<tr>'; 
    						    $suckoff_form .= '<td class="cfc_label">'.$this->view->translate('visit_date').'</td>';
    						    $suckoff_form .= '<td>'.$contact_form_details['start_date_time'].'</td>';
    						    $suckoff_form .= '</tr>'; 
    						    $suckoff_form .= '<tr>'; 
    						    $suckoff_form .= '<td class="cfc_label">'.$this->view->translate('contact_form_creator').'</td>';
    						    $suckoff_form .= '<td>'.$contact_form_details['create_user_name'].'</td>';
    						    $suckoff_form .= '</tr>'; 
    						    $suckoff_form .= '</table>';
    						    
//     						    dd($suckoff_form,$contact_form_details);
    						    $this->getResponse()->setBody($suckoff_form)->sendResponse();
    						    
    						    exit;
    						    break;
    						    //--
    							
    							
	    				default:
	    					exit;
	    					break;
	    				}
	    				break;
	    			
		    		case 'save_form':
		    			switch($_REQUEST['form'])
		    			{
		    					
	    					case 'dosage_interaction_save':
	    						
	    						$form = new Application_Form_PatientDrugPlan();
	    						$form->save_dosage_interaction($ipid, $_POST, $_REQUEST['subaction']);
	    						
	    						exit;
	    						break;
		    						
	    					//ISPC-2516 Carmen 09.04.2020
	    					case 'awakesleepingsave':
	    						
	    						switch($_REQUEST['subaction'])
	    						{
	    							case 'delete':
	    								$entity = FormBlockAwakeSleepingStatusTable::getInstance()->find($_POST['id']);
	    						
	    								if($entity)
	    								{
	    									$entity->delete();
	    								}
	    								break;
	    									
	    							default:
			    						$fas = new Application_Form_FormBlockAwakeSleepingStatus();
			    						$form = $fas->save_form_block_awake_sleeping_status($ipid, $_POST);
	    						}
	    							
	    						exit;
	    						break;
	    						//--
		    						
		
    						//ISPC-2522 Carmen 10.04.2020
    						case 'positioningsave':
    								
    							switch($_REQUEST['subaction'])
    							{
    								case 'delete':
    									$entity = FormBlockPositioningTable::getInstance()->find($_POST['id']);
    										
    									if($entity)
    									{
    										$entity->delete();
    									}
    									break;
    							
    								default:
		    							$fas = new Application_Form_FormBlockPositioning();
		    							$form = $fas->save_form_block_positioning($ipid, $_POST);
		    							break;
    							}
    								
    							exit;
    							break;
    						//--
		    						
    						//ISPC-2523 Carmen 13.04.2020
    						case 'suckoffsave':
    							
    							switch($_REQUEST['subaction'])
    							{
    								case 'delete':
    									$entity = FormBlockSuckoffTable::getInstance()->find($_POST['id']);
    							
    									if($entity)
    									{
    										$entity->delete();
    									}
    									break;
    										
    								default:
		    							$fas = new Application_Form_FormBlockSuckoff();
		    							$form = $fas->save_form_block_suckoff($ipid, $_POST);
		    							break;
    							}
    								
    							exit;
    							break;
    						//--
    						
    						//ISPC-2518+ISPC-2520 Carmen 14.04.2020
    						case 'organicentriesexitssave':
    							
    							switch($_REQUEST['subaction'])
    							{
    								case 'delete':
    									$entity = FormBlockOrganicEntriesExitsTable::getInstance()->find($_POST['id']);
    										
    									if($entity)
    									{
    										$entity->delete();
    									}
    									break;
    							
    								default:    							
		    							$fas = new Application_Form_FormBlockOrganicEntriesExits();
		    							$form = $fas->save_form_block_organic_entries_exits($ipid, $_POST);
		    							break;
    							}
    							
    							exit;
    							break;
    						//--
		    					
    						//ISPC-2519 Carmen 15.04.2020
    						case 'customeventsave':
    							
    							switch($_REQUEST['subaction'])
    							{
    								case 'delete':
    									$entity = FormBlockCustomEventTable::getInstance()->find($_POST['id']);
    							
    									if($entity)
    									{
    										$entity->delete();
    									}
    									break;
    										
    								default:
		    							$fas = new Application_Form_FormBlockCustomEvent();
		    							$form = $fas->save_form_block_custom_event($ipid, $_POST);
		    							break;
    							}
    							
    							exit;
    							break;
    						//--
    						
    						//ISPC-2508 Carmen 21.04.2020
    						case 'artificialentriesexitssave':
    							
    							/* switch($_REQUEST['subaction'])
    							{
    								case 'delete':
    									$entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
    										
    									if($entity)
    									{
    										$entity->delete();
    									}
    									break;
    								
    								case 'remove':
    									$entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
    									
    									if($entity)
    									{
    										$entity->isremove = 1;
    										$entity->remove_date = date('Y-m-d H:i:s', time());
    										$entity->save();
    									}
    									break;
    									
    								case 'refresh':
    									$entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
    										
    									if($entity)
    									{
    										//remove the entity and create a new one starting now
    										$entity->isremove = 1;
    										$entity->remove_date = date('Y-m-d H:i:s', time());
    										$entity->save();
    											
    										$data['id'] = null;
    										$data['ipid'] = $ipid;
    										$data['option_id'] = $entity->option_id;
    										$data['option_date'] = date('Y-m-d H:i:s', time());
    										$data['option_localization'] = $entity->option_localization;
    											
    										$newentity = PatientArtificialEntriesExitsTable::getInstance()->createIfNotExistsOneBy(array('id', 'ipid'), array($data['id'], $ipid), $data);
    										
    									}
    									break;
    									
    								default:
    									
    									$fas = new Application_Form_Stammdatenerweitert();
    									$form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
    									
    									break;
    							} */
    							//ISPC-508 Carmen 21.05.2020 new design
    							if($_REQUEST['artaction'] == 'delete')
    							{
    								$entity = PatientArtificialEntriesExitsTable::getInstance()->find($_POST['id']);
    								
    								if($entity)
    								{
    									$entity->delete();
    								}
    							}
    							else 
    							{
    								$_POST['action'] = $_REQUEST['artaction'];
    								//get the options box from the client list
    								$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
    								$fas = new Application_Form_Stammdatenerweitert(array(
    								"_client_options"		=> $client_options,
    								));
    								
    								if($_POST['action'] != 'refresh')
    								{
        								$fas->mapValidateFunction( 'create_form_artificial_entries_exits', 'create_form_isValid');
        								
        								$validated = $fas->triggerValidateFunction('create_form_artificial_entries_exits', array($ipid, $_POST));
        								
        								if(!is_string($validated))
        								{
        									$form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
        								}
        								else
        								{
        									echo $validated;
        								}
    								}
    								else
    								{
    									$form = $fas->save_form_artificial_entries_exits($ipid, $_POST);
    								}
    								
    							}
    							//--
    							exit;
    							break;
    						//--
    						case 'symptomatologysave':
    							$post = array();

    							foreach($_POST as $kr => $vr)
    							{
    								if(!is_array($vr))
    								{
    									$post[$kr] = $vr;
    								}
    								else
    								{
    									foreach($vr as $keyr => $valr)
    									{
    										$post[$keyr] = $valr;
    									}
    								}
    							}
    							
    							$fas = new Application_Form_PatientSymptomatology();
    							$form = $fas->save_form_symptomatology($ipid, $post,true);
    							exit;
    							break;
    							
		    				default:
		    					exit;
		    					break;
		    			}
	    				break;
	    				
		    		case 'load-chart':	
		    		    switch($_REQUEST['chart_name'])
		    		    {
		    		        case 'vital-_signs':
		    		            
		    		            
		    		            exit;
		    		            break;
		    		            //--
		    		    }
		    		default:
		    			exit;
		    			break;
	
	    	}
    	}
    }
    
    
    public function navigationAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        
        $start = strtotime($_REQUEST['start']);
        setlocale(LC_ALL, 'de_DE.UTF8');//TODO-3733 Ancuta 12.01.2021
        // $_REQUEST start  is d.m.Y format
        $chart_view_type = $_REQUEST['chart_view_type'];
        $navigation = array();
        
        $this->view->pid = $_REQUEST['id'];
        $this->view->chart_type = $chart_view_type;
        //         $navigation[$chart_view_type]['month_year'] =  date('F, Y',$start);
        $navigation[$chart_view_type]['month_year'] =    mb_convert_encoding(strftime('%B, %Y', $start) , 'utf8') ;
        
        // pateint details - get admission date - for go to admission date - button
		// ISPC-2512 8) date picker: can you add a "Admission" ("Aufnahmetag") as a 5th button BELOW 1,3,7 days to jump to admission day (of current FALL)?

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $patient_master = new PatientMaster();
        $patientinfo = $patient_master->getMasterData($decid,0);
        
        $navigation['current_admission_date'] = date('d.m.Y',strtotime($patientinfo['admission_date'])); 

        
        
      
        
        $data = array();
        $data['user'] = $this->logininfo->userid;
        $data['ipid'] = $patientinfo['ipid'];

        // always save
        $data['view_mode'] = $chart_view_type;
        $this->__save_user_view_mode($data);
        
        
        // check if user has view saved for patient
        $saved_view_mode = $this->__get_user_view_mode($data);
        if($saved_view_mode){
            $chart_view_type = $saved_view_mode;
        } 
        
        switch($chart_view_type)
        {
            case 'oneday':
                $navigation['oneday']['prev'] = strtotime('-1 day',$start);
                $navigation['oneday']['next'] = strtotime('+1 day',$start);
                $navigation['oneday']['days'][] = $start;
                
                break;
                
            case 'threedays':
            	
                $navigation['threedays']['prev'] = strtotime('-3 day',$start);
                $navigation['threedays']['next'] = strtotime('+3 day',$start);
                
                $navigation['threedays']['days'][] = $start;
                $navigation['threedays']['days'][] = strtotime('+1 day',$start);
                $navigation['threedays']['days'][] = strtotime('+2 day',$start);
                
                $navigation['threedays']['prev_dmy'] = date('d.m.Y H:i:s',strtotime('-3 day',$start));
                $navigation['threedays']['next_dmy'] = date('d.m.Y H:i:s',strtotime('+3 day',$start));
                $navigation['threedays']['days_dmy'][] = date('d.m.Y H:i:s',$start);
                $navigation['threedays']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+1 day',$start));
                $navigation['threedays']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+2 day',$start));
            	
                break;
                
            case 'week':
            	
                $navigation['week']['prev'] = strtotime('-7 day',$start);
                $navigation['week']['next'] = strtotime('+7 day',$start);
                
                $navigation['week']['days'][] = $start;
                $navigation['week']['days'][] = strtotime('+1 day',$start);
                $navigation['week']['days'][] = strtotime('+2 day',$start);
                $navigation['week']['days'][] = strtotime('+3 day',$start);
                $navigation['week']['days'][] = strtotime('+4 day',$start);
                $navigation['week']['days'][] = strtotime('+5 day',$start);
                $navigation['week']['days'][] = strtotime('+6 day',$start);
                
                
                $navigation['week']['prev_dmy'] = date('d.m.Y H:i:s',strtotime('-7 day',$start));
                $navigation['week']['next_dmy'] = date('d.m.Y H:i:s',strtotime('+7 day',$start));
                
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',$start);
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+1 day',$start));
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+2 day',$start));
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+3 day',$start));
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+4 day',$start));
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+5 day',$start));
                $navigation['week']['days_dmy'][] = date('d.m.Y H:i:s',strtotime('+6 day',$start));
            	
                break;
                
                case '12hours':
                	 
                	$navigation['12hours']['prev'] = strtotime('-12 hours',$start);
                	$navigation['12hours']['next'] = strtotime('+12 hours',$start);                
                	$navigation['12hours']['days'][] = $start;
                	
                	$navigation['12hours']['prev_dmy'] = date('d.m.Y H:i:s',strtotime('-12 hours',$start));
                	$navigation['12hours']['next_dmy'] = date('d.m.Y H:i:s',strtotime('+12 hours',$start));
                	$navigation['12hours']['days_dmy'][] = date('d.m.Y H:i:s',$start);
                	
                	break;
                	
                case '4hours':
                	
                	$navigation['4hours']['prev'] = strtotime('-4 hours',$start);
                	$navigation['4hours']['next'] = strtotime('+4 hours',$start);
                	$navigation['4hours']['days'][] = $start;
                		 
                	$navigation['4hours']['prev_dmy'] = date('d.m.Y H:i:s',strtotime('-4 hours',$start));
                	$navigation['4hours']['next_dmy'] = date('d.m.Y H:i:s',strtotime('+4 hours',$start));
                	$navigation['4hours']['days_dmy'][] = date('d.m.Y H:i:s',$start);
                		 
                	break;
        }
        //var_dump($navigation); exit;
        $this->view->navigation  = $navigation;
    }
    
    
    
    /**
     * ISPC-2518+ISPC-2520 add extrafields to organic block
     * @carmen 14.04.2020
     */
    
    public function createformblockorganicextrafieldsAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    	
    	$orgid = $_REQUEST['orgid'];
    	$recid = $_REQUEST['recid'];
    	
    	$extrafields = OrganicEntriesExitsExtrafieldsTable::getInstance()->findBy('organic_id', $orgid, Doctrine_Core::HYDRATE_ARRAY);
   
    	if($_REQUEST['recid'])
    	{
    		$values = FormBlockOrganicEntriesExitsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
    	}
    	
    	$af = new Application_Form_FormBlockOrganicEntriesExits();
    	$extraf = $af->create_form_block_organic_extrafields([
    			'_extrafields' => $extrafields,
    			'values' => $values,
    	], $parent_form);
    
    	$this->getResponse()->setBody($extraf)->sendResponse();
    
    	exit;
    }
    
    
    public function chartdataAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $chart_type = $_REQUEST['chart_type'];
        // default = week
        $chart_interval = isset($_REQUEST['chart_interval']) && !empty($_REQUEST['chart_interval']) && in_array($_REQUEST['chart_interval'],array('oneday','threedays','week', '12hours', '4hours'))? $_REQUEST['chart_interval'] : 'week'; //ISPC-2661 pct.10 Carmen 17.09.2020

        
        // !!!!!!!!!!!!!!!!
        // Create period 
        // !!!!!!!!!!!!!!!!
        
        if(empty($_REQUEST['start'])){
            $current_start_dateTime = date('Y-m-d 00:00:00');
        } else{
            $current_start_dateTime = date('Y-m-d',strtotime($_REQUEST['start'])).' 00:00:00';
        }
        //ISPC-2661 pct.10 Carmen 17.09.2020
        if($chart_interval == '12hours' || $chart_interval == '4hours')
        {
        	$current_start_dateTime = date('Y-m-d H:i:s',strtotime($_REQUEST['start']));
        }
        //--
        if(empty($_REQUEST['end'])){
            //week is set as default
            $current_end_dateTime =  date('Y-m-d 23:59:59',strtotime('+ 6 day',strtotime($current_start_dateTime)));
        } else{
            $current_end_dateTime = date('Y-m-d',strtotime($_REQUEST['end'])).' 23:59:59';
        }
        
        //ISPC-2661 pct.10 Carmen 17.09.2020
        if($chart_interval == '12hours')
        {
        	$current_end_dateTime = date('Y-m-d H:i:s',strtotime('+ 12 hours',strtotime($current_start_dateTime)));
        }
        
        if ($chart_interval == '4hours')
        {
        	$current_end_dateTime = date('Y-m-d H:i:s',strtotime('+ 4 hours',strtotime($current_start_dateTime)));
        }
        //--
       
        
        $period_start  = $current_start_dateTime;
        $period_end   = $current_end_dateTime;
        
//         $period_start_js = strtotime($current_start_dateTime)*1000;
//         $period_end_js   = strtotime($current_end_dateTime)*1000;
 
        $period_start_js = $this->_chart_timestamp_datetime_utc($current_start_dateTime);
        $period_end_js = $this->_chart_timestamp_datetime_utc($current_end_dateTime);
//         $period_start_js = $this->_chart_timestamp_datetime($current_start_dateTime);
//         $period_end_js = $this->_chart_timestamp_datetime($current_end_dateTime);
        
        $period = array();

        $period['start'] = $period_start;
        $period['end'] = $period_end;
        
        $period['start_js'] = $period_start_js;
        $period['end_js'] = $period_end_js;
        
        $period['js_min'] = $period_start_js;
        $period['js_min_explained'] = date('Y-m-d H:i:s',$period_start_js/1000);;
        $period['js_max'] = $period_end_js;
        $period['js_max_explained'] = date('Y-m-d H:i:s',$period_end_js/1000);;
        
        $period['start_strtotime'] = strtotime($period_start);
        $period['end_strtotime'] = strtotime($period_end);
        
        
       /*  dd($period);
        $start_date_timestamp = $period['start_js']/1000;
        $end_date_timestamp = $period['end_js']/1000;
        
        $start_year = gmdate('Y', $start_date_timestamp);
        $start_month = gmdate('m', $start_date_timestamp);
        $start_day = gmdate('d', $start_date_timestamp);
        
        $end_year = gmdate('Y', $end_date_timestamp);
        $end_month = gmdate('m', $end_date_timestamp);
        $end_day = gmdate('d', $end_date_timestamp);
        
        $start_js_ts = gmmktime(0, 0, 0, $start_month, $start_day, $start_year);
        $start_js_date = $start_js_ts * 1000;
        
        $end_js_ts = gmmktime(23, 59, 59, $end_month, $end_day, $end_year);
        $end_js_date = $end_js_ts * 1000;
        
 
        dd($start_year,$start_month,$start_day,
            date('d.m.Y H:i:s',$start_js_date/1000),
            $period,
            $_REQUEST); */
        
        
        switch($chart_type)
        {
            case 'time':
                $chart_data =  $this->fetch_time($ipid,$period);
                break;
            
            case 'vital_signs':
                //Vitalparameter (ISPC-2515)
                $chart_data =  $this->fetch_vital_signs($ipid,$period);
                break;
                
                
            case 'awake_sleep_status':
                //Verhaltensbeobachtung (ISPC-2516)
                $chart_data =  $this->fetch_awake_sleep_status($ipid,$period);
                break;
                
                
            case 'symptomatology':
                //Symptome (ISPC-2516)
                $chart_data =  $this->fetch_symptoms($ipid,$period);
                break;
             
            //ISPC-2516 Carmen 15.07.2020
            case 'symptomatologyII':
               	//Symptome II (ISPC-2516)
               	$chart_data =  $this->fetch_symptomsII($ipid,$period);
               	break;
            //--    
                
                
            case 'organic_entries_exits':
                //Ein- und Ausfuhr (ISPC-2518 + ISPC-2520)
                //they are displayed as single CHAR in the chart box.
                $chart_data =  $this->fetch_organic_entries_exits($ipid,$period);
                break;
                
            case 'organic_entries_exits_bilancing':
                //ISPC-2661
                $chart_data =  $this->fetch_organic_entries_exits_bilancing($ipid,$period);
                break;
                
            case 'organic_entries_exits_bilancing_oe':
                //ISPC-2661
                $chart_data =  $this->fetch_oe_bilancing($ipid,$period);
                break;
                
            case 'medication_actual':
                //Medikamente
                $chart_data =  $this->fetch_medication($ipid,$period,'actual');
                break;
                                
            case 'medication_isbedarfs':
                //Bedarfsmedikamente
                $chart_data =  $this->fetch_medication($ipid,$period,'isbedarfs');
                break;
                
                                
            case 'medication_iscrisis':
                //Bedarfsmedikamente
                $chart_data =  $this->fetch_medication($ipid,$period,'iscrisis');
                break;
                      
             case 'medication_isivmed'://ISPC-2871,Elena,30.03.2021
                //Bedarfsmedikamente
                $chart_data =  $this->fetch_medication($ipid,$period,'isivmed');
                break;

                
            case 'medication_isschmerzpumpe':
                //Bedarfsmedikamente
                $chart_data =  $this->fetch_medication_pumpe($ipid,$period,'isschmerzpumpe');
                break;
                
            case 'medication_ispumpe'://ISPC-2871,Elena,12.04.2021

                $chart_data =  $this->fetch_medication_pumpe_perfusor($ipid,$period,'ispumpe');
                break;

                
            case 'artificial_entires_exits':
                //Zu- und Abgänge (ISPC-2521)
                $chart_data =  $this->fetch_artificial_entires_exits($ipid,$period);
                break;
                
                
            case 'artificial_entires_exits_old':
                //Zu- und Abgänge (ISPC-2521)
                $chart_data =  $this->fetch_artificial_entires_exits_old($ipid,$period);
                break;
                
                
            case 'positioning':
                //Positionierung (ISPC-2522)
                $chart_data =  $this->fetch_positioning_events($ipid,$period);
                break;
                
            case 'positioning_individual':
                //Positionierung (ISPC-2522)
                $chart_data =  $this->fetch_positioning_events_individual($ipid,$period);
                break;
                
            case 'ventilation_info':
                //Beatmung und Weaning
                $chart_data =  $this->fetch_ventilation_info($ipid,$period);
                break;
                
                
            case 'suckoff_events':
                //Absaugen (ISPC-2523)
                $chart_data =  $this->fetch_suckoff_events($ipid,$period);
                break;
                
            case 'custom_events':
                //Ereignisse (ISPC-2519)
                $chart_data =  $this->fetch_custom_events($ipid,$period);
                break;
                
           //ISPC-2661 pct.13 Carmen 11.09.2020
           case 'custom_events_individual':
              	//Ereignisse (ISPC-2519)
               	$chart_data =  $this->fetch_custom_events_individual($ipid,$period);
               	break;
           //--
                
           //ISPC-2683 Carmen 16.10.2020
           case 'vigilance_awareness_events':
               	$chart_data =  $this->fetch_vigilance_awareness_events($ipid,$period);
               	break;
            case "beatmung":
                //ISPC-2697,elena,30.11.2020 Beatmung
                $chart_data =  $this->fetch_ventilation_data($ipid,$period);
                break;
            case "pcoc_phase":
                //IM-153 TODO-4163
                $chart_data =  $this->fetch_pcoc_phase($ipid,$period);
                break;
                                
            default:    
                break;
        }
        
         
        echo $chart_data;
        exit();
        
    }
 
    public function fetch_vital_signs($ipid = 0,$period = array()){
        
        if(empty($ipid)){
            return;
        }
        $all_vital_signs = FormBlockVitalSigns::get_patients_chart($ipid, $period);

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        // count days between - tick interval
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        $series_visible = array();
        $series_visible['puls']= 'yes';//Puls (pulse) values between 0-220 Unit: Schläge/min
        $series_visible['blood_pressure']= 'yes';//Blutdruck (bloodpressure) values between 0-300 unit: mmHg. for bloodpressure 2 values (systolic / diastolic) are added and both are connected with a vertical line
        $series_visible['temperature']= 'yes';//Temperatur (temperature) values between: 34-42 unit:°
        $series_visible['blood_sugar']= 'yes';//Blutzucker (bloodsugar) values between 0-400 unit: mg/dl
        $series_visible['respiratory_frequency']= 'yes';//Atemfrequenz (breathing rate) zwischen 0-100 unit: Atemzüge/min
        $series_visible['oxygen'] = 'yes'; //Sauerstoffsättigung (O2) values betwee 0-100 unit: %
        
        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,
            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',
            
            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => -2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );
        
        $chart_index = 0;
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
 
        $points_values = array();
        
        //[Puls] //Puls (pulse) values between 0-220 Unit: Schläge/min
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        

        $Vs_color="";
        $Vs_color= "red";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = '/min';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 0;
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 5;
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 13;
        $chart_data['yaxis'][$chart_index]['max'] = 220;
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'line',
            'lineWidth' => 2,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'visible' => ( isset($series_visible['puls']) && $series_visible['puls'] == 'no' ? false : true ),
            'name' => $this->view->translate('[Puls]'),
            'color' => 'red',
            'marker'=>array(
                'enabled'=> true
            ),
            'tooltip' => array(
                "pointFormat" => "<b>{series.name}: {point.y:,.f} Schläge/min</b><br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['puls'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['puls'])),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['puls'];
                }
                
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        

        
        //Blutdruck (bloodpressure) values between 0-300 unit: mmHg. for bloodpressure 2 values (systolic / diastolic) are added and both are connected with a vertical line
        $chart_index++;
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        
        $Vs_color="";
        $Vs_color= "blue";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = 'mmHg';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 0;
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 20;
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 13;
        $chart_data['yaxis'][$chart_index]['max'] = 240;
 
        $chart_data['series'][$chart_index] = array(
            'type' => 'errorbar',
            'lineWidth' => 2,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'pointPlacement' => 'on',
//             'whiskerLength' => count($displayed_days) != 1 ? '30%' : '15%',
            'whiskerLength' => 10,
            'whiskerWidth' => '2',
            'visible' => ( isset($series_visible['blood_pressure']) && $series_visible['blood_pressure'] == 'no' ? false : true ),
            'name' => $this->view->translate('[Non-invasive blood pressure]'),
            'showInLegend' => true,
            'linkedTo' => null,
            'color' => $Vs_color,
            'tooltip' => array(                                 //ISPC-2515 units Lore 21.05.2020
                'pointFormat' => '{series.name}<br/>'.
                $this->view->translate('systolic').': <b>{point.high:,.f} </b>mmHg<br/>'.        //ISPC-2517 pct.e1 Lore 21.05.2020
                $this->view->translate('diastolic').': <b>{point.low:,.f} </b>mmHg<br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>' //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['blood_pressure']['systolic'] != '0.00' || $w['blood_pressure']['diastolic']  != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'low' => floatval(str_ireplace(',', '.', $w['blood_pressure']['systolic'])),
                        'high' => floatval(str_ireplace(',', '.', $w['blood_pressure']['diastolic'])),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['blood_pressure']['systolic'] ;
                }
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        
        
        
        
        //[Body temperature]
        
        $chart_index++;
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        
        $Vs_color="";
        $Vs_color= "black";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = '°C';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 30;
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 1;
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 14; //ISPC-2661 pct.1 Carmen 10.09.2020
        $chart_data['yaxis'][$chart_index]['max'] = 43; //ISPC-2661 pct.1 Carmen 10.09.2020
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'line',
            'lineWidth' => 2,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'visible' => (isset($series_visible['temperature']) && $series_visible['temperature'] == 'no' ? false : true),
            'name' => $this->view->translate('[Body temperature]'),
            'color' => 'black',
            'tooltip' => array(                           //ISPC-2515 units Lore 21.05.2020
                "pointFormat" => "<b>{series.name}: {point.y:,.f} </b>°C<br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        
        
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['temperature'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['temperature'])),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['temperature'];
                }
                
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        
        
        //[Blood sugar]
        $chart_index++;
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        
        $Vs_color="";
        $Vs_color= "purple";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = 'mg/dl';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 0;
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 50;
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 17; //ISPC-2780 Carmen 18.01.2021
        $chart_data['yaxis'][$chart_index]['max'] = 800;            //ISPC-2780 Lore 04.01.2020
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'line',
            'lineWidth' => 2,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'name' => $this->view->translate('[Blood sugar]'),
            'visible' => (
                isset($series_visible['blood_sugar']) && $series_visible['blood_sugar'] == 'no' ? false : true
                ),
            'color' => 'purple',
            'tooltip' => array(                     //ISPC-2515 units Lore 21.05.2020
                "pointFormat" => "<b>{series.name}: {point.y:,.f} </b>mg/dl<br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        
        
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['blood_sugar'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['blood_sugar'])),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['blood_sugar'];
                }
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        
        
        
        
        //Atemfrequenz (breathing rate) zwischen 0-100 unit: Atemzüge/min
        $chart_index++;
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        
        $Vs_color="";
        $Vs_color= "grey";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = '/min';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 0;
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 10; //ISPC-2661 pct.1 Carmen 10.09.2020
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 16; //ISPC-2661 pct.1 Carmen 10.09.2020
        $chart_data['yaxis'][$chart_index]['max'] = 150; //ISPC-2661 pct.1 Carmen 10.09.2020
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'line',
            'lineWidth' => 2,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'name' => $this->view->translate('[respiratory_frequency]'),
            'visible' => (
                isset($series_visible['respiratory_frequency']) && $series_visible['respiratory_frequency'] == 'no' ? false : true
                ),
            'color' => 'grey',
            'tooltip' => array(             //ISPC-2515 units Lore 21.05.2020
                "pointFormat" => "<b>{series.name}: {point.y:,.f} </b>Atemzüge/min<br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        
        
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['respiratory_frequency'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['respiratory_frequency'])),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['respiratory_frequency'];
                }
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        
        
        //[Peripheral oxygen saturation]
        $chart_index++;
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        $chart_data['yaxis'][$chart_index]['type'] = 'scatter';
        
        
        $Vs_color="";
        $Vs_color= "orange";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
        $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
        $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
        
        
        $chart_data['yaxis'][$chart_index]['customTitle'] = '%';
        $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
        $chart_data['yaxis'][$chart_index]['min'] = 0; //ISPC-2661 pct.1 Carmen 10.09.2020
        $chart_data['yaxis'][$chart_index]['tickInterval'] = 5;
        $chart_data['yaxis'][$chart_index]['tickAmount'] = 21; //ISPC-2661 pct.1 Carmen 10.09.2020
        $chart_data['yaxis'][$chart_index]['max'] = 100; 
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'scatter',
            'lineWidth' => 0,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'visible' => ( isset($series_visible['oxygen']) && $series_visible['oxygen'] == 'no' ? false : true ),
            'name' => $this->view->translate('[Peripheral oxygen saturation]'),
            'color' => 'orange',
            'tooltip' => array(         //ISPC-2515 units Lore 21.05.2020
                "pointFormat" => "<b>{series.name}: {point.y:,.f} </b>%<br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            ),
            'marker' => array(
                'enabled'=> true,
                'symbol'=>'circle',
                'lineColor' => 'orange',
                'lineWidth' => 1,
                'radius' => 6,
            ),
            
        );
        
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['oxygen_saturation'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['oxygen_saturation'])),
                        'label'=>$this->view->translate('[Peripheral oxygen saturation]'),
                    	'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['oxygen_saturation'];
                }
                
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('vital_signs');
        
        $chart_data['hasData']  = 1;
        if(empty($points_values)){
            $chart_data['hasData']  = 0;
        }
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        return $rez;
    
    }
    

    public function fetch_awake_sleep_status($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
            
        }
        
        $now = date("Y-m-d H:i:s");
        
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }
        //
        
        
        $all_data = FormBlockAwakeSleepingStatus::get_patients_chart($ipid);// get all data in order to optain a continuas line

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
 
        // first we create intervals
        $interval_data = array();
        $i=0;
        foreach ($all_data as $k => $w) {
            
            // if both start and end are smaller then period start date do not add
            //ISPC-2661 pct.13 Carmen 10.09.2020
            /* if(strtotime($w['status_date']) < strtotime($period['start']) 
               && ( isset($all_data[$k+1]['status_date']) && strtotime($all_data[$k+1]['status_date']) < strtotime($period['start']) )
              ){
                continue;
            } */
        	/* if(strtotime($w['form_start_date']) < strtotime($period['start'])
        			&& ( isset($all_data[$k+1]['form_start_date']) && strtotime($all_data[$k+1]['form_start_date']) < strtotime($period['start']) )
        			){
        				continue;
        	} */
        	
        
            
           /*  if(
                Pms_CommonData::isintersected(strtotime($w['status_date']), strtotime($w['status_date']), strtotime($period['start']), strtotime($period['end']))
                || Pms_CommonData::isintersected(strtotime($w['status_date']), strtotime($w['status_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
                ) */
        	
        		/* if(
        			Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime($period['start']), strtotime($period['end']))
        			|| Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
        			)

            { */
                $status = $w['awake_sleep_status']!=null ? $w['awake_sleep_status'] : 'keine';
                $interval_data[$status][$i]['entry_id'] =  $w['id']; 
                $interval_data[$status][$i]['usershortname'] = $this->all_users[$w['create_user']]['shortname'];//ISPC-2661 pct.6 Carmen 11.09.2020
                /*if(strtotime($w['status_date']) > strtotime($period['start'])){
                    $interval_data[$status][$i]['start'] = $w['status_date'];
                } else{
                    $interval_data[$status][$i]['start'] = $period['start'];
                }
                
                if(isset($all_data[$k+1]['status_date'])){
                    $interval_data[$status][$i]['end'] = $all_data[$k+1]['status_date'];
                } else{
                    
                    $interval_data[$status][$i]['end'] = $now;// change to NOW 
                    $interval_data[$status][$i]['no_end'] = '1';
                } */
            	
            	//if(strtotime($w['form_start_date']) > strtotime($period['start'])){
            		$interval_data[$status][$i]['start'] = $w['form_start_date'];
            	/* } else{
            		$interval_data[$status][$i]['start'] = $period['start'];
            	} */
            	
            	if(isset($w['form_end_date']) && strtotime($w['form_end_date']) < strtotime($period['end']) && $w['form_end_date'] != '0000-00-00 00:00:00'){
            		$interval_data[$status][$i]['end'] = $w['form_end_date'];
            	} else{
            	
            		$interval_data[$status][$i]['end'] = date('Y-m-d H:i:s', strtotime('+ 5 minutes', strtotime($now)));// change to NOW //ISPC-2661 Carmen change to future
                	$interval_data[$status][$i]['no_end'] = '1';
                	if($w['isenduncertain'])
                	{
                		$interval_data[$status][$i]['uncertain_end'] = '1';
                	}
            	}
                $i++;
           // }
        }
        //--

        $bar_color = array();
        //ISPC-2661 Carmen 
        $bar_color['awake'][1]  = '#2BAE2F';
        $bar_color['awake'][2]  = 'lightgreen';
        $bar_color['awake'][3]  = 'white';
        $bar_color['sleeping'][1]  = '#408CFF';
        $bar_color['sleeping'][2]  = 'lightblue';
        $bar_color['sleeping'][3]  = 'white';
        $bar_color['keine'][1]  = 'grey';
        $bar_color['keine'][2]  = 'lightgrey';
        $bar_color['keine'][3]  = 'white';
        //--
        //http://jsfiddle.net/2fk4ry30/
        $series_data = array();
        $chart_index = 0;        
        $categories = array(); //ISPC-2661 Carmen
        foreach($interval_data as $type=>$status_periods){
        	//ISPC-2661 Carmen
        	$categories[] = $type;
        	$countperiods = count($status_periods);
        	//--
            $chart_data['series'][$chart_index]['name'] = $type;
            $chart_data['series'][$chart_index]['color'] = $bar_color[$type];
            $chart_data['series'][$chart_index]['borderRadius'] = '5';
            $chart_data['series'][$chart_index]['pointWidth'] = '10';
            $chart_data['series'][$chart_index]['pointPadding'] = '0';
            $chart_data['series'][$chart_index]['groupPadding'] = '0';
            $chart_data['series'][$chart_index]['grouping'] = false;
            $chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
            
            foreach($status_periods as $ks=>$speriod)
            {
            	//ISPC-2661 pct.13 Carmen 17.09.2020
            	if($speriod['uncertain_end'] != 1)
            	{
            	
            		if($endunc){
            			$chart_index++;
            			$chart_data['series'][$chart_index]['name'] = $type;
            			//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
            			//             $chart_data['series'][$chart_index]['color'] = null;
            			$chart_data['series'][$chart_index]['borderRadius'] = '5';
            			$chart_data['series'][$chart_index]['pointWidth'] = '10';
            			$chart_data['series'][$chart_index]['pointPadding'] = '0';
            			$chart_data['series'][$chart_index]['groupPadding'] = '0';
            			$chart_data['series'][$chart_index]['grouping'] = false;
            			$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
            			$endunc=false;
            		}
            		$chart_data['series'][$chart_index]['color'] = $bar_color[$type][1];
            	
            	
            	}
            	else
            	{
            		$chart_index++;
            		$chart_data['series'][$chart_index]['name'] = $type;
            		//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
            		//             $chart_data['series'][$chart_index]['color'] = null;
            		$chart_data['series'][$chart_index]['borderRadius'] = '5';
            		$chart_data['series'][$chart_index]['pointWidth'] = '10';
            		$chart_data['series'][$chart_index]['pointPadding'] = '0';
            		$chart_data['series'][$chart_index]['groupPadding'] = '0';
            		$chart_data['series'][$chart_index]['grouping'] = false;
            		$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
            		$chart_data['series'][$chart_index]['color']['linearGradient'] = [x1=>0, x2=>0, y1=>0, y2=>1 ];
            		$chart_data['series'][$chart_index]['color']['stops'] =
            		[[0, $bar_color[$type][3]], // start
            				[0.8, $bar_color[$type][2]], // middle
            				[1, $bar_color[$type][1]] // end
            		];
            		if($ks != ($countperiods-1))
            		{
            			$endunc = true;
            		}
            	
            	}
            	//--
                //if(Pms_CommonData::isintersected(strtotime($speriod['start']), strtotime($speriod['end']), strtotime($period['start']), strtotime($period['end'])))
                //{
                    $series_data[] = 1;
                    $chart_data['series'][$chart_index]['data'][] = array(
                        //'x' => 0,
                    	'uncertainend' => ($speriod['uncertain_end'] != 1) ? (($speriod['no_end'] != 1) ? 0 : 1) : 1, //ISPC-2661
                    	'usershortname' => $speriod['usershortname'], //ISPC-2661 pct.7 Carmen 11.09.2020
                        'low' => $this->_chart_timestamp_datetime($speriod['start']),
                        'low_st' => $speriod['start'],
                        'high' =>$this->_chart_timestamp_datetime($speriod['end']),
                        'high_st' =>$speriod['end'],
                        'entry_id'=> $speriod['entry_id'],
                        'marker' => array(
                            'enabled'=> true,
                            'symbol'=>'circle',
                            'lineColor' => '#000000',
                            'lineWidth' => 1,
                            'radius' => 8,
                        ),
                    );                    
               // }
                
            }
        	//ISPC-2661 Carmen
            if(!$endunc)
            {
            	$chart_index++;
            }       
        }
        
        //ISPC-2661 Carmen
        $chartmodif = $chart_data;
        foreach($chart_data['series'] as $kserie => $serie)
        {
        	foreach($serie['data'] as $kdatc => $datc)
        	{
        		if(in_array($serie['name'], $categories))
        		{
        			$keycat = array_search($serie['name'], $categories);
        			$chartmodif['series'][$kserie]['data'][$kdatc]['x'] = $keycat;
        		}
        	}
        }
        $chart_data = $chartmodif;      
	    $chart_data['categories'] = $categories;
	    //--
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('awake_sleep_status');
 
        $chart_data['chart_height'] = 130;
        if(empty($chart_data['series'])){
            $chart_data['chart_height'] = 50;
        }
        
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        
        $chart_data['hasData']  = '0';
        if(!empty($series_data)){
            $chart_data['hasData']  = '1';
        }
        $rez = json_encode($chart_data);
        
        return $rez;
    }
    
    
    
    public function fetch_symptoms($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        $symp = new Symptomatology();
        $all_data = $symp->get_patient_symptpomatology_period($this->logininfo->clientid,$ipid, $period);
        $ss = new SymptomatologyValues();
        $set_details = $ss->getSymptpomatologyValues('1');
        
        if(empty($ipid)){
            return;
        }
       
        $sympt_cat_view_chart_list = array();           //ISPC-2516 Lore 11.06.2020
        
        $patient_column = array();
        foreach($all_data as $k=>$sympt){
            $patient_column[$sympt['entry_date']][$sympt['symptomid']] =$sympt;
            //ISPC-2516 Lore 11.06.2020
            if(!empty($sympt['input_value'])){
                $sympt_cat_view_chart_list[] =  $sympt['symptomid'];
            }
            //.
        }
       
        $categories = array();
        
        $sp=1;
        foreach($set_details as $k=>$ss_Data){
            //ISPC-2516 Lore 11.06.2020
            if(in_array($k, $sympt_cat_view_chart_list)){
                $categories[$sp] = $ss_Data['sym_description'];
                $sp++;
            }
            //.
/*           $categories[$sp] = $ss_Data['sym_description'];
             $sp++; */
        }
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        $def_yaxis = array(
            'categories' => $categories,
            'reversed' => true,
            'showEmpty'=> false,
            'type' => 'scatter',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,
            
            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'line',
            'lineColor' => '#ccd6eb',
            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => -2,
                'padding' => 2,
                'style' => array(
                    'color' => '#444444',
                    'fontSize'=>'12px'
                )
            )
        );

        // SOME twisted logic
        $y2categoryes_mapping = array(
            '1' => '1',
            '2' => '2',
            '4' => '3',
            '5' => '4',
            '6' => '5',
            '7' => '6',
            '8' => '7',
            '9' => '8',
            '10' => '9',
            '11' => '10',
            '12' => '11',
            '13' => '12',
            '14' => '13',
            '15' => '14',
            '16' => '15',
            '17' => '16',
            '18' => '17',
            '60' => '18'
        );
        
        //ISPC-2516 Lore 11.06.2020
        $new_y2categoryes_mapping = array();
        foreach($y2categoryes_mapping as $keys=>$vals){
            if(in_array($keys, $sympt_cat_view_chart_list)){
                $new_y2categoryes_mapping[$keys] = count($new_y2categoryes_mapping)+1;
            }
        }
        if(!empty($new_y2categoryes_mapping)){
            $y2categoryes_mapping = $new_y2categoryes_mapping;
        }
        //.

        $char_index = 0;
        foreach($patient_column as $column_date => $entries){
            foreach($set_details as $symptom_id => $set_symptom){
                if(isset($entries[$symptom_id]['input_value'])){
                    
                    $info_text = "";
                    $info_text .= $entries[$symptom_id]['symptom_name'].' '.$entries[$symptom_id]['input_value'];
                    $info_text .= !empty($entries[$symptom_id]['custom_description']) ? '<br/>Kommentar: '.$entries[$symptom_id]['custom_description'] : '';
                    
                    $chart_series[ $char_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($entries[$symptom_id]['entry_date']),
                        'y' => floatval($y2categoryes_mapping[$symptom_id]),// stupid change !!! STUPID
                    	'usershortname' => $this->all_users[$entries[$symptom_id]['create_user']]['shortname'], //ISPC-2661 pct.2 Carmen 10.09.2020
                        'color' => '#'.$entries[$symptom_id]['symptom_value_color'],
                        'marker' => array(
                            'enabled'=> true,
                            'symbol'=>'circle',
                            'lineColor' => null,
                            'lineWidth' => 0,
                            'radius' => 10,
                        ),
                        'noTooltip'=>false,
                        'title'=>true,
                        'info'=>$info_text ,
                        'label'=>$entries[$symptom_id]['symptom_name'],
//                         'comment'=> !empty($entries[$symptom_id]['custom_description']) ? 'Kommentar: '.$entries[$symptom_id]['custom_description'] : '',
                        'value' => $entries[$symptom_id]['input_value'] 
                    );
                    $char_index++;
                }  
                else{
                    //ISPC-2516 Lore 11.06.2020
/*                     $chart_series[ $char_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($entries[$symptom_id]['entry_date']),
                        'y' => floatval($y2categoryes_mapping[$symptom_id]),// stupid change !!! STUPID
                        'color' => '#ffffff',
                        'noTooltip'=>true,
                        'marker' => array(
                            'enabled'=> false,
                            'symbol'=>'circle',
                            'lineColor' => null,
                            'lineWidth' => 0,
                            'radius' => 1,
                        ),
                    );
                    $char_index++; */
                }
            }
        }
        $chart_data['hasData']  = 1;
        //$chart_data['chart_height'] = 480; 
        //ISPC-2516 Lore 11.06.2020
        $chart_data['chart_height'] = 80;
        $chart_data['chart_height'] += (37*count($y2categoryes_mapping) > 480) ? 400 : (37*count($y2categoryes_mapping)) ;    
        //.
        
        if (empty($chart_series)) {
            $chart_data['hasData']  = 0;
            //nothing to show - but stil show chart block  with title
            $chart_data['chart_height'] = 50;
        }
        
        
        $chart_data['yaxis'] = $def_yaxis;
        $chart_data['series'] = $chart_series;
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title']  = $this->__getChartTitle('symptomatology');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        
        return $rez;
    }

    
    public function fetch_organic_entries_exits($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
      
        
        $all_data = FormBlockOrganicEntriesExits::get_patients_chart($ipid, $period);
  
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
//         $chart_series[0] = array(
//             'type' => 'scatter',
//             'name' => $this->view->translate('chart_block_organic_entries_exits'),
//         );
        
        $colors= array(
            'rot'=>'red',
            'blutig'=>'red',
            'grün'=>'green',
            'braun'=>'brown',
            'bräunlich'=>'brown',
            'grau'=>'gray',
            'gräulich'=>'gray',
            'weiß'=>'white',
            'gelb-grün'=>'yellow',
            'n. bekannt'=>'black',
            'weiß-schaumig'=>'#f3f3f3',
            
        );
        
        $y = 1;
        foreach ($all_data as $row) {
            $info_text = '';
            $info_text .= $this->view->translate('organicentriesexits').': ' .$row['organic_id_master_name'] . "<br/>";
            $info_text .= $this->view->translate('organic_amount').': ' .$row['organic_amount'] . "<br/>";
            if(!empty($row['organic_type_name'])){
                $info_text .= $this->view->translate('organic_type_label').': ' . nl2br($row['organic_type_name']) . "<br/>";
            }
            $color = '#2bae2f';
            if(!empty($row['organic_color_name'])){
                $info_text .= $this->view->translate('organic_color_label').': ' . nl2br($row['organic_color_name']) . "<br/>";
                $color = $colors[$row['organic_color_name']];
            }
            
            $radius = 8;
            $chart_series[ 0 ]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($row['organic_date']),
                'y' => $y,
                'color' => $color,
                'marker' => array(
                    'enabled'=> true,
                    'lineColor' => '#000000',
                    'lineWidth' => 1,
                    'radius' => $radius
                ),
                'title'=>true,
            	'entry_id'=>$row['id'] ,
                'name'=>$row['organic_id_master_shortcut'].' '.$row['organic_id_master_name'] ,
                'value'=> $row['organic_amount'],
                'info' => $info_text
            );
           $y =$y+1;
        }
        $chart_data['chart_height'] = 150;
        $chart_data['hasData']  = 1;
        if (empty($chart_series)) {
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['series'] = $chart_series;
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title']  = $this->__getChartTitle('organic_entries_exits');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        return $rez;
    }

    /**
     * ISPC-2661 Ancuta
     */
    public function fetch_organic_entries_exits_bilancing_cc($ipid = 0,$period = array()){
        
        if(empty($ipid)){
            return;
        }
        $all_vital_signs = FormBlockVitalSigns::get_patients_chart($ipid, $period);
//         $all_data = FormBlockOrganicEntriesExits::get_patients_chart($ipid, $period);
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        // count days between - tick interval
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        $series_visible = array();
        $series_visible['puls']= 'yes';//Puls (pulse) values between 0-220 Unit: Schläge/min
        $series_visible['blood_pressure']= 'yes';//Blutdruck (bloodpressure) values between 0-300 unit: mmHg. for bloodpressure 2 values (systolic / diastolic) are added and both are connected with a vertical line
        $series_visible['temperature']= 'yes';//Temperatur (temperature) values between: 34-42 unit:°
        $series_visible['blood_sugar']= 'yes';//Blutzucker (bloodsugar) values between 0-400 unit: mg/dl
        $series_visible['respiratory_frequency']= 'yes';//Atemfrequenz (breathing rate) zwischen 0-100 unit: Atemzüge/min
        $series_visible['oxygen'] = 'yes'; //Sauerstoffsättigung (O2) values betwee 0-100 unit: %
        
        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,
            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',
            
            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => -2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );
        
        $chart_index = 0;
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        $points_values = array();
        
        //[Puls] //Puls (pulse) values between 0-220 Unit: Schläge/min
        $chart_data['yaxis'][$chart_index] = $def_yaxis;
        
        
        $Vs_color="";
        $Vs_color= "green";
        $chart_data['yaxis'][$chart_index]['title'] = false;
        $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
        $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['lineColor'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['tickWidth'] = '1';
        $chart_data['yaxis'][$chart_index]['tickColor'] = $Vs_color;
//         $chart_data['yaxis'][$chart_index]['labels']['x'] = 12;
//         $chart_data['yaxis'][$chart_index]['labels']['align'] = 'left';
//         $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = $Vs_color;
        $chart_data['yaxis'][$chart_index]['offset'] = ($chart_index+1)* 38;
        
//         $chart_data['yaxis'][$chart_index]['customTitle'] = '/min';
//         $chart_data['yaxis'][$chart_index]['color'] = $Vs_color;
        
//         $chart_data['yaxis'][$chart_index]['min'] = 0;
//         $chart_data['yaxis'][$chart_index]['tickInterval'] = 5;
//         $chart_data['yaxis'][$chart_index]['tickAmount'] = 13;
//         $chart_data['yaxis'][$chart_index]['max'] = 220;
        
        $chart_data['series'][$chart_index] = array(
            'type' => 'line',
            'lineWidth' => 1,
            'yAxis' => $chart_index,
            //'pointStart' => $period['js_min'],
            'stickyTracking' => false,
            'visible' => ( isset($series_visible['puls']) && $series_visible['puls'] == 'no' ? false : true ),
            'name' => $this->view->translate('[Puls]'),
            'color' => 'green',
            'marker'=>array(
                'enabled'=> true
            ),
            'tooltip' => array(
                "pointFormat" => "<b>{series.name}: {point.y:,.f} Schläge/min</b><br/>{point.x:%d.%m.%Y %H:%M}<br /><b>{point.usershortname}</b>" //ISPC-2661 pct.1 Carmen 10.09.2020
            )
        );
        foreach ($all_vital_signs as $ipid => $w_data) {
            foreach ($w_data as $k => $w) {
                if($w['puls'] != '0.00'){
                    $chart_series[ $chart_index ]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['date']),
                        'y' => floatval(str_ireplace(',', '.', $w['puls'])),
                        'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.1 Carmen 10.09.2020
                    );
                    $points_values[] = $w['puls'];
                }
                
            }
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
        }
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('organic_entries_exits_bilancing');
        $chart_data['chart_height'] = 150;
        $chart_data['hasData']  = 1;
        if(empty($points_values)){
            $chart_data['hasData']  = 0;
        }
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        return $rez;
        
    }
    
    
    public function fetch_organic_entries_exits_bilancing($ipid = 0, $period = array()){
        if (empty($ipid)) {
            return;
        }
        
//         $all_data = FormBlockVentilation::get_patients_chart($ipid, $period);
        
        $all_data = FormBlockOrganicEntriesExits::get_patients_chart($ipid, $period);
        
        $items2sets = array();
        foreach($all_data as $k=>$oex){
            $items2sets[$oex['setid']]['items'][] = $oex;
        }
        foreach($items2sets as $set=>$sdata){
            usort($items2sets[$set]['items'], array(new Pms_Sorter('organic_date'), "_date_compare"));
        }
        $items2sets_chart = array();
        foreach($items2sets as $si=>$sdata){
            foreach($sdata['items'] as $k=>$ex){
                if($ex['item_type'] == 'exit'){
                    $items2sets_chart[$si]['exits'] = $items2sets_chart[$si]['exits']+ $ex['organic_amount'];
                }elseif($ex['item_type'] == 'entry'){
                    $items2sets_chart[$si]['entry'] = $items2sets_chart[$si]['entry'] + $ex['organic_amount'];
                }
            }
            $items2sets_chart[$si]['start'] = $sdata['items'][0]['organic_date'];
            $items2sets_chart[$si]['name'] = 'START: '.$sdata['items'][0]['organic_date'];
            $last[$si] = end($sdata['items']);
            $items2sets_chart[$si]['end'] = $last[$si]['organic_date'];
            
            $items2sets_chart[$si]['value'] = $items2sets_chart[$si]['entry'] - $items2sets_chart[$si]['exits'];
        }
//         dd($items2sets_chart);
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $series_visible = array();
        $series_visible['modus'] = 'yes';
        $series_visible['f_tot'] = 'yes';
        $series_visible['vt'] = 'yes';
        $series_visible['mv'] = 'yes';
        $series_visible['peep'] = 'yes';
        $series_visible['pip'] = 'yes';
        $series_visible['o2_l_min'] = 'yes';
        $series_visible['i_e'] = 'yes';
        
        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,
            
            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',
            
            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => - 2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );
        
        $chart_index = 0;
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'], // 'min' => $start_js_date,
            'max' => $period['js_max'] // 'max' => $end_js_date,
        );
        
        $all_point_values = array();
            
            $chart_data['yaxis'][$chart_index] = $def_yaxis;
            $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = '#000000';
            
            $chart_data['series'][$chart_index] = array(
                'type' => 'line',
                'lineWidth' => 2,
                'stickyTracking' => false,
                'name' => "Bilanzierung",
                'color' => null,
                'marker' => array(
                    'enabled' => true
                ),
                'tooltip' => array(
                    "pointFormat" => "<b>{series.name}: {point.y:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}"
                )
            );
            foreach ($items2sets_chart  as $k => $w) {
                if($w[value] != "0.00"){
                    
                    $chart_series[$chart_index]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['start']),
                        'y' => floatval(str_ireplace(',', '.', $w['value']))
                    );
                    $all_point_values[] = $w['value'];
                    $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
                }
            }
            $chart_index ++;
        
//             dd($chart_data);
            
        $chart_data['hasData']  = 1;
        $chart_data['chart_height'] = 150;
        if (empty($all_point_values)) {
            // nothing to show
            //return;
            $chart_data['hasData']  =0;
            $chart_data['chart_height'] = 50;
            unset($chart_data['series']);
        }
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp, $end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('Bilanzierung');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        return $rez;
    }
    
    
    
    
    
    
    private function _chart_square_empty($color)
    {
        $marker = array(
            'symbol' => 'square',
            'radius' => 8,
            'fillColor' => '#FFFFFF',
            'lineColor' =>  $color,
            'lineWidth' => 2
        );
        
        return $marker;
    }
    
    private function _chart_circle_empty($color)
    {
        $marker = array(
            'symbol' => 'circle',
            'radius' => 8,
            'fillColor' => '#FFFFFF',
            'lineColor' =>  $color,
            'lineWidth' => 2
        );
        
        return $marker;
    }
    
    private function _chart_square_full($color)
    {
        $marker = array(
            'symbol' => 'square',
            'radius' => 8,
            'lineWidth' => 2,
            'fillColor' => $color,
            'lineColor' => $color
        );
        
        return $marker;
    }
    
    /**
     * @param $color
     * @return array
     *
     * ISPC-2871,Elena,07.05.2021
     */
    private function _chart_triangle_full($color)
    {
        $marker = array(
            'symbol'=> 'url('.APP_BASE.'images/triangle.png)',
            'radius' => 8,
            'lineWidth' => 1,
            'fillColor' => $color,
            'lineColor' => $color,
            'enabled' => true
        );

        return $marker;
    }
    
    private function _chart_circle_full($color)
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'circle',
            'radius' => 5,
            'lineWidth' => 2,
            'fillColor' => $color,
            'lineColor' => $color
        );
        
        return $marker;
    }
    private function _chart_circle_full_red()
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/no.svg)',
            'color'=> '#2bae2f',
            'lineWidth' => 0,
        );
        
        return $marker;
    }
    
    private function _chart_circle_full_blue()
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/other_dosage.svg)',
            'color'=> '#2bae2f',
            'lineWidth' => 0,
        );
        
        return $marker;
    }
    
    private function _chart_circle_full_yellow()
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/reject.svg)',
            'color'=> '#2bae2f',
            'lineWidth' => 0,
        );
        
        return $marker;
    }
    
    private function _chart_circle_full_green()
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)',
            'lineWidth' => 0,
        );
        
        return $marker;
    }
    
    private function _chart_radio_full_dual_color($outline_color, $fill_color)
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'circle',
            'radius' => 8,
            'lineWidth' => 2,
            'fillColor' => $fill_color,
            'lineColor' => $outline_color
            
        );
        return $marker;
    }
    
    private function _chart_square_full_dual_color($outline_color, $fill_color)
    {
        $marker = array(
            'enabled'=> true,
            'symbol' => 'square',
            'radius' => 8,
            'lineWidth' => 2,
            'fillColor' => $fill_color,
            'lineColor' => $outline_color
            
        );
        return $marker;
    }
    
    
    private function _chart_marker_hide()
    {
        $marker = array(
            'enabled' => false,
            'states' => array(
                'hover' => array(
                    'enabled' => false
                )
            )
        );
        
        return $marker;
    }
    
    
    public function fetch_medication($ipid = 0, $period = array(), $type = 'actual'){
        if(empty($ipid)){
            return;
        }
        //ISPC-2903,Elena,26.04.2021
        $chartsettings_client =ClientConfig::getConfig($this->logininfo->clientid, 'chartsettings');
        $this->chartsettings = json_decode($chartsettings_client);

       
        //if($period['start_strtotime'] > strtotime(date('Y-m-d')))
        //ISPC-2871,Elena,30.03.2021
        /*
        if($period['start_strtotime'] > strtotime(date('Y-m-d H:i:s'))) //ISPC-2661 Carmen
        {
            $chart_data = array();
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            //ISPC-2871,Elena,30.03.2021
            $title_identification ='medication_'.$type;
            $chart_data['title']  = $this->__getChartTitle($title_identification);

            $rez = json_encode($chart_data);
            return $rez;
        }*/
        
        
        $drugplan = new PatientDrugPlan();
        //ISPC-2903,Elena,26.04.2021
        $medication_types = MedicationType::client_medication_types($this->logininfo->clientid);
        $medication_types_kv = [];
        $medication_types_kv[0] = '';
        foreach($medication_types as $mtype){
            $medication_types_kv[$mtype['id']] = $mtype['type'];
        }
;        //print_r($medication_types_kv);
        //ISPC-2871,Elena,30.03.2021
        //i need not only actual data but deleted in the time period too, because i need to see and edit events for these data .
        //i can find it in PatientDrugPlan.
        $all_data = $drugplan->get_chart_medication($ipid,$this->logininfo->clientid, false, $type, $period);
      
        $user =  new User();
        $users_details = array();
        $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);
        
        
        // get time scheduled details 
        $clientid = $this->logininfo->clientid;
        $modules =  new Modules();
        $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
        if($individual_medication_time_m){
            $individual_medication_time = 1;
        }else {
            $individual_medication_time = 0;
        }
        
        //get get saved data
        if($individual_medication_time == "0"){
            $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
        } else {
            $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
        }
   
        
        //ISPC-2797 Ancuta 17.02.2021
        $elsa_planned_medis = $modules->checkModulePrivileges("250", $clientid);
        $this->view->elsa_planned_medis = 0;
        
        $drugplan_ids2planned_actions = array();
        if($elsa_planned_medis){
            $this->view->elsa_planned_medis = 1;
            $drugplan_ids2planned_actions = PatientDrugplanPlanning::get_planned_drugs($ipid);
        }
        //--
        
        //get time scchedule options
        $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
        
        $time_blocks = array('all');
        $timed_scheduled_medications= array();
        $NOT_timed_scheduled_medications = array();
        foreach($client_med_options as $mtype=>$mtime_opt){
            if($mtime_opt['time_schedule'] == "1"){
                $time_blocks[]  = $mtype;
                $timed_scheduled_medications[]  = $mtype;
            } else {
                $NOT_timed_scheduled_medications[]  = $mtype;
            }
        }
        
        if($individual_medication_time == "0"){
            $timed_scheduled_medications = array("actual","isivmed"); // default
            $time_blocks  = array("actual","isivmed"); // default
        }
        
        foreach($timed_scheduled_medications  as $tk=>$tmed){
            if(in_array($tmed,$NOT_timed_scheduled_medications)){
                unset($timed_scheduled_medications[$tk]);
            }
        }
        
        $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
        
        $dosage_intervals = array();
        $interval_array = array();
        $dosage_settings = array();
        if($patient_time_scheme['patient']){
            foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
            {
                if($med_type != "new"){
                    $set = 0;
                    foreach($dos_data  as $int_id=>$int_data)
                    {
                        if(in_array($med_type,$patient_time_scheme['patient']['new'])){
                            
                            $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                            $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
                            
                            $dosage_settings[$med_type][$set] = $int_data;
                            $set++;
                            
                            $dosage_intervals[$med_type][$int_data] = $int_data;
                        }
                        else
                        {
                            $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                            $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
                            $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
                            
                            $dosage_settings[$med_type][$set] = $int_data;
                            $set++;
                            
                            $dosage_intervals[$med_type][$int_data] = $int_data;
                        }
                    }
                }
            }
        }
        else
        {
            foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
            {
                
                $inf=1;
                $setc= 0;
                foreach($mtimes as $int_id=>$int_data){
                    
                    $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
                    $interval_array['interval'][$med_type][$inf]['custom'] = '1';
                    $dosage_settings[$med_type][$setc] = $int_data;
                    $setc++;
                    $inf++;
                    
                    $dosage_intervals[$med_type][$int_data] = $int_data;
                }
            }
        }
        
        
        foreach($all_data[$ipid] as $drugplan_id=>$vm){
            $medication_type = $vm['category'];
            $all_data[$ipid][$drugplan_id]['old_dosage'] = $vm['dosage'];
            //ISPC-2903,Elena,26.04.2021
            $all_data[$ipid][$drugplan_id]['type_text'] = $medication_types_kv[$all_data[$ipid][$drugplan_id]['extra']['type']];
            if(!in_array($vm['category'],$timed_scheduled_medications))
            {
                $all_data[$ipid][$drugplan_id]['dosage']= $vm['dosage'];
            }
            else
            {
                // first get new dosage
                if(!empty($vm['drugplan_dosage']))
                {
                    $all_data[$ipid][$drugplan_id]['dosage']  = $vm['drugplan_dosage'];
                }
                else if(strlen($vm['dosage'])> 0 )
                {
                    
                    $old_dosage_arr[$vm['id']] = array();
                    $all_data[$ipid][$drugplan_id]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
                    
                    if(strpos($vm['dosage'],"-")){
                        $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
                        
                        if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
                            //  create array from old
                            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                            {
                                //TODO-3424 Ancuta 15.09.2020
//                                 $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x];
                                $all_data[$ipid][$drugplan_id]['dosage'][$x]['dosage'] = $old_dosage_arr[$vm['id']][$x];
                                $all_data[$ipid][$drugplan_id]['dosage'][$x]['dosage_time_interval'] = $dosage_settings[$medication_type][$x].':00';
                                // --                                
                                
                            }
                        }
                        else
                        {
                            $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
                            $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                            for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                            {
                                $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                            }
                        }
                    }
                    else
                    {
                        $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
                        $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                        
                        for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                        {
                            $all_data[$ipid][$drugplan_id]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                        }
                    }
                }
                else
                {
                    $all_data[$ipid][$drugplan_id]['dosage'] =  "";
                }
            }
        }
//         dd($all_data);

        //TODO-3384 Carmen 01.09.2020
        $saved_dosage_interacion_NoTime = array();
        
        $now = date("Y-m-d H:i:s",time());
        
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
        	$timezone  = +2;
        	$now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }
      
        foreach($all_data[$ipid] as $kdr => $vdr)
        {
        	$dosage_arr = array();
        	if((!empty($vdr['dosage'] && $vdr['main_old_dosage'] != "") || $vdr['dosage'] != ""))
        	{
        	    
        	    if( $elsa_planned_medis ){
        	        
        	        $date = $now;
        	        if(isset($drugplan_ids2planned_actions[$vdr['id']]) && $drugplan_ids2planned_actions[$vdr['id']]['action'] == 'add'){
        	            if(strtotime(date('Y-m-d')) >= strtotime($drugplan_ids2planned_actions[$vdr['id']]['action_date'])){

        	            }  else{
        	                $date = $drugplan_ids2planned_actions[$vdr['id']]['action_date'];
        	            }
        	        }
        	        if(isset($drugplan_ids2planned_actions[$vdr['id']]) && $drugplan_ids2planned_actions[$vdr['id']]['action'] == 'remove'){
        	            if(strtotime(date('Y-m-d')) <= strtotime($drugplan_ids2planned_actions[$vdr['id']]['action_date'])){
        	                
        	            } else{
        	                $date = $drugplan_ids2planned_actions[$vdr['id']]['action_date'];
        	            }
        	        }
        	        
        	        
        	           $saved_dosage_interacion_NoTime[$kdr][date('Y-m-d', strtotime($date))][date('H:i:s', strtotime($date))] = array(
    	                    'id' => '',
    	                    'ipid' => $ipid,
    	                    'drugplan_id' => $kdr,
        	               'dosage_date' => $date,
    	                    //'dosage' => implode('-', $vdr['dosage'])
    	                );
    	                
    	                if(is_array($vdr['dosage']))
    	                {
    	                    foreach($vdr['dosage'] as $kdos => $vdos)
    	                    {
    	                        $dosage_arr[] = $vdos['dosage'];
    	                    }
    	                    $dosage_str = implode('-', $dosage_arr);
    	                }
    	                else
    	                {
    	                    $dosage_str = $vdr['dosage'];
    	                }
    	                
    	                $saved_dosage_interacion_NoTime[$kdr][date('Y-m-d', strtotime($date))][date('H:i:s', strtotime($date))]['dosage'] = $dosage_str;
        	        
        	    }  
        	    else
        	    {
            		$saved_dosage_interacion_NoTime[$kdr][date('Y-m-d', strtotime($now))][date('H:i:s', strtotime($now))] = array(
            				'id' => '',
            				'ipid' => $ipid,
            				'drugplan_id' => $kdr,
            				'dosage_date' => $now,
            				//'dosage' => implode('-', $vdr['dosage'])
            		);
            		
            		if(is_array($vdr['dosage']))
            		{
            		foreach($vdr['dosage'] as $kdos => $vdos)
            		{
            			$dosage_arr[] = $vdos['dosage'];
            		}
            		$dosage_str = implode('-', $dosage_arr);
            		}
            		else 
            		{
            			$dosage_str = $vdr['dosage'];
            		}
            		
            		$saved_dosage_interacion_NoTime[$kdr][date('Y-m-d', strtotime($now))][date('H:i:s', strtotime($now))]['dosage'] = $dosage_str;
        	    }
        	}
        }     
        
        //--
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        // count days between
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        $chart_meds = array();
        $interval_meds = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        
        
        
        $saved_dosage_interacion_array = array();
        $saved_dosage_interacion_array = PatientDrugPlanDosageGivenTable::findAllByIpids(array($ipid));
        
        $saved_dosage_interacion = array();
        //TODO-3384 Carmen 01.09.2020
        //$saved_dosage_interacion_NoTime = array();
        //-- 
        foreach($saved_dosage_interacion_array as $k=>$dsg_interaction){
            $saved_dosage_interacion[$dsg_interaction['drugplan_id']][date('Y-m-d',strtotime($dsg_interaction['dosage_date']))][$dsg_interaction['dosage_time_interval']] = $dsg_interaction;
           	
            $saved_dosage_interacion_NoTime[$dsg_interaction['drugplan_id']][date('Y-m-d',strtotime($dsg_interaction['dosage_date']))][substr($dsg_interaction['dosage_date'], -8)] = $dsg_interaction;
        }
       	
        
        
        foreach ($all_data[$ipid] as $id => $row) {
            $interval_meds[ $id ]['start'] = $period['js_min'];
            $interval_meds[ $id ]['end'] = $period['js_max'];
            //ISPC-2903,Elena,26.04.2021
            //print_r($row);
            if($row['has_interval'] == 1){
                $start_visible = strtotime($row['administration_date']) * 1000;
            
                $end_visible = (strtotime($row['administration_date']) + 24*3600* $row['days_interval']) * 1000;
                if($period['js_min'] < $start_visible){
                    $interval_meds[ $id ]['start'] = $start_visible;
                }
                /*
                if($period['js_max'] > $end_visible){
                    $interval_meds[ $id ]['end'] = $end_visible;
                }*/

            }
            
            if( $elsa_planned_medis && isset($drugplan_ids2planned_actions[$id]) && !empty($drugplan_ids2planned_actions[$id]) ){
                // if add - then set the new start
                if($drugplan_ids2planned_actions[$id]['action'] == 'add'){
                    $interval_meds[ $id ]['start']  = $this->_chart_timestamp_datetime_utc($drugplan_ids2planned_actions[$id]['action_date']);;
                    $interval_meds[ $id ]['end'] = $period['js_max'];
                }
                // if remove  then set the new end
                if($drugplan_ids2planned_actions[$id]['action'] == 'remove'){
                    $interval_meds[ $id ]['start'] = $period['js_min'];
                    $interval_meds[ $id ]['end']  = $this->_chart_timestamp_datetime_utc($drugplan_ids2planned_actions[$id]['action_date']);;
                }
            } elseif( $elsa_planned_medis && (!isset($drugplan_ids2planned_actions[$id]) || empty($drugplan_ids2planned_actions[$id])) ){
                if($row['active_date'] != "0000-00-00 00:00:00"){
                    $interval_meds[ $id ]['start']  = $this->_chart_timestamp_datetime_utc($row['active_date']);
                }
                else
                {
                    $interval_meds[ $id ]['start']  =  $period['js_min'];
                }
            }
            $chart_meds[ $id ] = $row;
        }
        
        $js_idx = 0;
        foreach ($chart_meds as $row) {
            
            $color_js_idx = '#000000';
            //ISPC-2903,Elena,26.04.2021
            $mediname_to_show = $this->chartsettings->mediname_to_show;
            $mediname= $row['medication_name'];
            if($mediname_to_show == 'drug' && strlen(trim($row['drug'])) > 0){
                $mediname = $row['drug'];
            }
            
            $chart_data['categories'][$js_idx] = $mediname; //ISPC-2903,Elena,26.04.2021
            //ISPC-2826 Lore 18.02.2021
            $chart_data['categories'][$js_idx] .= !empty($row['escalation']) ? '<br/><b>Eskalation: '.$row['escalation'].'</b>' : '' ;
            //ISPC-2903,Elena,26.04.2021
            if(($type == 'isbedarfs' || $type == 'iscrisis') && !empty($row['type_text'])){
                $chart_data['categories'][$js_idx] .= !empty($row['type_text']) ? ';&nbsp;<b>Applikationsweg: </b>'.$row['type_text'] : '' ;
            }

            
            $chart_data['plotlines'][$js_idx] = array(
                'value' => $js_idx,
                'color' => 'white',
                'dashStyle' => 'Solid',
                'width' => 0,
                'is_acute' => false,
                'medid' => $row['id'],
                'zIndex' => 5
                
            );
            
            $chart_data['series'][$js_idx] = array(
                'type' => 'scatter',
                'lineWidth' => 0,
                'name' => $row['name'],
                'visible' => true,
                'zIndex' => 9,
                'color' => $color_js_idx
            );
            
            $chart_data['series_line'][$js_idx] = array(
                'type' => 'line',
                'linecap' => 'square',
                'cursor' => 'pointer',
                'lineWidth' => 0,
                'name' => false, // If this is set - label is listed at the end of the line 
                'visible' => true,
                'showInLegend' => false,
                'color' => $color_js_idx,
                'pointStart' => $interval_meds[ $row['id'] ]['start'],
                'pointInterval' => 3600000,
                'medid' => $row['id'],
                'zIndex' => 1
            );
            
            
            $chart_data['series_line'][$js_idx]['data'][] = array(
                'x' => $interval_meds[ $row['id'] ]['start'],
                'y' => $js_idx,
                'medtype' => null,
                'is_given' => false,
                'noTooltip' => true,
                'marker' => $this->_chart_marker_hide()
            );
            
            
            
            $chart_data['series_line'][$js_idx]['data'][] = array(
                'x' => $interval_meds[ $row['id'] ]['end'],
                'y' => $js_idx,
                'medtype' => null,
                'is_given' => false,
                'noTooltip' => true,
                'marker' => $this->_chart_marker_hide()
            );
            
            //for each day in selected interval create points in chart
                for ($curent_day = $start_date_timestamp; $curent_day <= $end_date_timestamp; $curent_day = strtotime('+1 day', $curent_day)) {
                    //ISPC-2903,Elena,26.04.2021
                    //for interval medis
                    if($row['has_interval'] == 1){
                       //echo 'has interval';
                       $start_interval = strtotime($row['administration_date']);
                       $days_interval =  $row['days_interval'];
                    
                       $modulo = (($curent_day - $start_interval)/(24 * 3600)) % $days_interval;
                       if($modulo !=0 ){
                           continue;
                       }

                    }

                    if($type == $row['category'] && in_array($type, $timed_scheduled_medications)){
                        
                        $dosage_minutes = 0;
                        $dosage_minutes = 0;
                        foreach($row['dosage'] as $k=>$dosage){
                            if(empty($dosage['dosage'])){
                                continue;
                            }
               
                            $dosage_hour = date('G',strtotime(date('Y-m-d').' '.$dosage['dosage_time_interval']));
                            $dosage_minutes = (int)date("i",strtotime(date('Y-m-d').' '.$dosage['dosage_time_interval']));
                            
                            $info_text = '<b>'.$mediname.'</b>';//ISPC-2903,Elena,26.04.2021
                            $info_text .= '<br/>'.$this->view->translate('dosage').': '.$dosage['dosage'];
                            $info_text .= '<br/>'.$this->view->translate('dosage_time_interval').' '.substr($dosage['dosage_time_interval'], 0, 5);
                            if(!empty($row['unit_name'])){
                                $info_text .= '<br/>'.$this->view->translate('medication_unit').': '.$row['unit_name'];
                            }
                            if(!empty($row['dosage_form_name'])){
                                $info_text .= '<br/>'.$this->view->translate('medication_dosage_form').': '.$row['dosage_form_name'];
                            }
                            //ISPC-2903,Elena,26.04.2021
                            if(!empty($row['type_text'])){
                                $info_text .= '<br/>'.$this->view->translate('medication_type').': '.$row['type_text'];
                            }
                            
                            
                            if( 
                                $this->_chart_timestamp($curent_day, $dosage_hour,$dosage_minutes) >= $interval_meds[ $row['id'] ]['start'] &&
                                $this->_chart_timestamp($curent_day, $dosage_hour, $dosage_minutes) <= $interval_meds[ $row['id'] ]['end']
                                ){  
                                    $current_is_given = false;
                                    
                                    $save_dosage_interaction = array();
                                    
                                    if(!empty($saved_dosage_interacion[$row['id']][date('Y-m-d', $this->_chart_timestamp($curent_day, 0, 0)/1000 )][$dosage['dosage_time_interval']])){
                                        $save_dosage_interaction = $saved_dosage_interacion[$row['id']][date('Y-m-d', $this->_chart_timestamp($curent_day, 0, 0)/1000 )][$dosage['dosage_time_interval']];
                                    }
                                    $dosage_different = false;//ISPC-2871,Elena,30.03.2021 - 07.05.2021
                                    
                                    switch ($save_dosage_interaction['dosage_status']){
                                        case 'given':
                                            $current_marker = $this->_chart_circle_full_green();
                                            break;
                                            
                                        case 'not_given':
                                            $current_marker = $this->_chart_circle_full_red();
                                            break;
                                            
                                        case 'given_different_dosage':
                                            $current_marker = $this->_chart_circle_full_blue();
                                            $dosage_different = true;//ISPC-2871,Elena,30.03.2021 - 07.05.2021
                                            break;
                                            
                                        case 'not_taken_by_patient':
                                            $current_marker = $this->_chart_circle_full_yellow();
                                            break;
                                        default: {
                                            //ISPC-2871,Elena,30.03.2021
                                            //events to show: not deleted (as usually) and deleted, BUT deleted later than time period begins
                                            if( (intval($row['isdelete']) == 0) || (strtotime($row['delete_date']) >= $this->_chart_timestamp($curent_day, $dosage_hour, $dosage_minutes)/1000) ){
                                                if($row['approved'] == 1){
                                            $current_marker = $this->_chart_circle_full('grey','grey');
                                                }else{
                                                    $current_marker = $this->_chart_triangle_full('grey','grey');
                                                }

                                            }else{
                                                $current_marker = $this->_chart_marker_hide();

                                            }

                                            break;
                                        }//ISPC-2871,Elena,30.03.2021

                                    }
                                    
                                    //ISPC-2769 Carmen 18.01.2021
                                    if($this->client_details[0]['show_medi_times_when_given'] == 1 && !empty($save_dosage_interaction) && $dosage['dosage_time_interval'] != substr($save_dosage_interaction['dosage_date'], 11))
                                    {
	                                    $dosage_given_realtime = substr($save_dosage_interaction['dosage_date'], 11);
	                                    
	                                    $dosage_hour = date('G',strtotime(date('Y-m-d').' '.$dosage_given_realtime));
	                                    $dosage_minutes = (int)date("i",strtotime(date('Y-m-d').' '.$dosage_given_realtime));
                                    }
	                                // --
                                    if(!empty($save_dosage_interaction)){ 
                                        $info_text .= '<div class="tooltip_info">';
                                        $info_text .= '<h2>'.$this->translate('Given information').'</h2>';
                                        $info_text .= '<span class="med_status"><b>'.$this->translate('dosage_status_label').':</b> '.$this->translate('dosage_status_'.$save_dosage_interaction['dosage_status']).'</span>';
                                        
                                        $info_text .= '<span><b>'.$this->translate('Documented by').':</b> '.$users_details[$save_dosage_interaction['create_user']].'</span>';
                                        
                                        if($save_dosage_interaction['dosage_status'] == 'not_given' ){
                                            $info_text .= '<span><b>'.$this->translate('not_given_reason').':</b> '.$save_dosage_interaction['not_given_reason'].'</span>';
                                        }
                                        //ISPC-2583 pct.8 Lore 22.05.2020
                                        if($save_dosage_interaction['dosage_status'] != 'not_taken_by_patient'){
                                            $info_text .= '<span><b>'.$this->translate('Given dosage').':</b> '.$save_dosage_interaction['dosage'].' '.$row['unit_name'].'</span>';
                                        }
                                        
                                        $info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($save_dosage_interaction['dosage_date'])).'</span>';
                                        
                                        $info_text .= '<span><b>'.$this->translate('dosage_given_time').':</b> '.date('H:i',strtotime($save_dosage_interaction['dosage_date'])).'</span>';
                                        
                                        if(!empty($save_dosage_interaction['documented_info'])){
                                            $info_text .= '<span><b>'.$this->translate('documented_info').'</b> '.$save_dosage_interaction['documented_info'].'</span>';
                                        }
                                        $info_text .= '</div>';
                                    }
                                    
                                    $chart_data['series'][$js_idx]['data'][] = array(
                                        'x' => $this->_chart_timestamp($curent_day, $dosage_hour, $dosage_minutes),
                                        'y' => $js_idx,
                                        'marker' => $current_marker,
                                        'time_schedule' => 1,
                                        'drugplan_id' => $row['id'],
                                        'medication_name' => $mediname,
                                        'dosage_unit'=>  $row['unit_name'],
                                        'approved' => $row['approved'],//ISPC-2871,Elena,11.05.2021
                                        
                                        'documented_dosage_interaction'=>array(
                                            'entry_id'=> $save_dosage_interaction['id'],
                                            'status'=> $save_dosage_interaction['dosage_status'],
                                            'dosage'=> !empty($save_dosage_interaction['dosage'])&& strlen($save_dosage_interaction['dosage']) > 0 ? $save_dosage_interaction['dosage']: $dosage['dosage'],
                                            'dosage_date'=>  date('Y-m-d H:i:s', $this->_chart_timestamp($curent_day, $dosage_hour, $dosage_minutes)/1000 ),
                                            'dosage_unit_name'=>  $row['unit_name'],
                                            'dosage_unit'=>  $row['unit_name'],
                                            'dosage_time_interval'=> !empty($save_dosage_interaction['dosage_time_interval'])? $save_dosage_interaction['dosage_time_interval']: $dosage['dosage_time_interval'],
                                            'documented_info'=> $save_dosage_interaction['documented_info'],
                                            'not_given_reason'=> $save_dosage_interaction['not_given_reason'],
                                        ),
                                        
                                        'is_given' => $current_is_given,
                                        'label' => (count($displayed_days) < 3 && $current_marker['enabled'] )? ($dosage_different && !(empty($save_dosage_interaction['dosage']))? '<span class="dl">'.$save_dosage_interaction['dosage'].$row['unit_name'].'</span>'  :'<span class="dl">'.$dosage['dosage'].$row['unit_name'].'</span>'):'',//ISPC-2871,Elena,30.03.2021 - 07.05.2021 //show label for actual data and deleted later than time period begins only
                                        'info' => $info_text
                                    );
                            }
                        }
     
                    }  elseif($type == $row['category'] && !in_array($type, $timed_scheduled_medications)){
                        
                    	/* if ($this->_chart_timestamp($curent_day, 8, 0) >= $interval_meds[ $row['id'] ]['start'] &&
                            $this->_chart_timestamp($curent_day, 8, 0) <= $interval_meds[ $row['id'] ]['end']
                            &&  $this->_chart_timestamp($curent_day, 0, 0)/1000 <= strtotime(date('Y-m-d'))
                            ) { */
                                $current_is_given = false;
                                $current_marker = $this->_chart_radio_full_dual_color('white',$color_js_idx);
                                
                               /*  $info_text = '<b>'.$row['medication_name'].'</b>';
                                $info_text .= '<br/>'.$this->view->translate('dosage').': '.$row['dosage']; */
                                
                                
                                $save_dosage_interaction = array();
                                //TODO-3384 Carmen 01.09.2020
                                if(!empty($saved_dosage_interacion_NoTime[$row['id']][date('Y-m-d', $curent_day)])){
                                    $save_dosage_interaction = $saved_dosage_interacion_NoTime[$row['id']][date('Y-m-d', $curent_day)];
                                }
                                //--
                               /*  switch ($save_dosage_interaction['dosage_status']){
                                	case 'given':
                                		$current_marker = $this->_chart_circle_full_green();
                                		break;
                                
                                	case 'not_given':
                                		$current_marker = $this->_chart_circle_full_red();
                                		break;
                                
                                	case 'given_different_dosage':
                                		$current_marker = $this->_chart_circle_full('blue');
                                		break;
                                
                                	case 'not_taken_by_patient':
                                		$current_marker = $this->_chart_circle_full('yellow');
                                		break;
                                	default:
                                		$current_marker = $this->_chart_circle_full('grey','grey');
                                		break;
                                } */
                                
                                if(!empty($save_dosage_interaction)){
                                	//TODO-3384 Carmen 01.09.2020
                                	/* $info_text .= '<div class="tooltip_info">';
                                	$info_text .= '<h2>'.$this->translate('Given information').'</h2>';
                                	//ISPC-2583 pct.8 Lore 22.05.2020
                                	$info_text .= '<span class="med_status"><b>'.$this->translate('dosage_status_label').':</b> '.$this->translate('dosage_status_'.$save_dosage_interaction['dosage_status']).'</span>';
                                	
                                	$info_text .= '<span><b>'.$this->translate('Documented by').':</b> '.$users_details[$save_dosage_interaction['create_user']].'</span>';
                                	
                                	if($save_dosage_interaction['dosage_status'] == 'not_given' ){
                                		$info_text .= '<span><b>'.$this->translate('not_given_reason').':</b> '.$save_dosage_interaction['not_given_reason'].'</span>';
                                	}
                                	//ISPC-2583 pct.8 Lore 22.05.2020
                                	if($save_dosage_interaction['dosage_status'] != 'not_taken_by_patient'){
                                		$info_text .= '<span><b>'.$this->translate('Given dosage').':</b> '.$save_dosage_interaction['dosage'].' '.$row['unit_name'].'</span>';
                                	}
                                	
                                	$info_text .= '<span><b>'.$this->translate('dosage_given_time').':</b> '.date('H:i',strtotime($save_dosage_interaction['dosage_date'])).'</span>';
                                	
                                	$info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($save_dosage_interaction['documented_date'])).'</span>';
                                	
                                	if(!empty($save_dosage_interaction['documented_info'])){
                                		$info_text .= '<span><b>'.$this->translate('documented_info').'</b> '.$save_dosage_interaction['documented_info'].'</span>';
                                	}
                                	$info_text .= '</div>';
                                	}
                                	
                                	$dosage = is_array($row['dosage']) ? $row['main_old_dosage'] : $row['dosage'];
                                	$chart_data['series'][$js_idx]['data'][] = array(
                                			'x' => $this->_chart_timestamp($curent_day, 8, 0),
                                			'y' => $js_idx,
                                			'time_schedule' => 0,
                                			'marker' => $current_marker,
                                			'drugplan_id' => $row['id'],
                                			'medication_name' => $row['medication_name'],
                                			'is_given' => $current_is_given,
                                			'dosage_unit'=>  $row['unit_name'],
                                	
                                			'documented_dosage_interaction'=>array(
                                					'entry_id'=> $save_dosage_interaction['id'],
                                					'status'=> $save_dosage_interaction['dosage_status'],
                                					'dosage'=> !empty($save_dosage_interaction['dosage'])&& strlen($save_dosage_interaction['dosage']) > 0 ? $save_dosage_interaction['dosage']: $row['dosage'],
                                					'dosage_date'=>  date('Y-m-d H:i:s', $this->_chart_timestamp($curent_day, 8, 0)/1000 ),
                                					'dosage_time_interval'=> !empty($save_dosage_interaction['dosage_time_interval'])? $save_dosage_interaction['dosage_time_interval']: $dosage['dosage_time_interval'],
                                					'documented_info'=> $save_dosage_interaction['documented_info'],
                                					'not_given_reason'=> $save_dosage_interaction['not_given_reason'],
                                			),
                                	
                                			'label' => count($displayed_days) < 3 ? '<span class="dl">'.$dosage .$row['unit_name'].'</span>':'',
                                			'info' => $info_text
                                	); */
                                	$dosage = is_array($row['dosage']) ? $row['main_old_dosage'] : $row['dosage'];
                                	foreach($save_dosage_interaction as $kh => $vh)
                                	{
                                		switch ($vh['dosage_status']){
                                			case 'given':
                                				$current_marker = $this->_chart_circle_full_green();
                                				break;
                                		
                                			case 'not_given':
                                				$current_marker = $this->_chart_circle_full_red();
                                				break;
                                		
                                			case 'given_different_dosage':
                                			    $current_marker = $this->_chart_circle_full_blue();
                                				//$current_marker = $this->_chart_circle_full('blue');
                                				break;
                                		
                                			case 'not_taken_by_patient':
                                			    $current_marker = $this->_chart_circle_full_yellow();
                                				//$current_marker = $this->_chart_circle_full('yellow');
                                				break;
                                			default:{
                                			    //ISPC-2871,Elena,30.03.2021
                                                //show marker for actual data (as usually) AND for deleted data IF they deleted later than time period begins
                                                if( (intval($row['isdelete']) == 0) || (strtotime($row['delete_date']) >= $this->_chart_timestamp($curent_day, $dosage_hour, $dosage_minutes)) ){
                                				$current_marker = $this->_chart_circle_full('grey','grey');
                                                }else{
                                                    $current_marker = $this->_chart_marker_hide();
                                                }

                                				break;
                                		}
                                		}
                                		$dosage_hour = date('G',strtotime(date('Y-m-d').' '.substr($vh['dosage_date'], -8)));
                                		$dosage_minutes = (int)date("i",strtotime(date('Y-m-d').' '.substr($vh['dosage_date'], -8)));
	                                	if($vh['dosage_status'])
	                                	{
                                		$info_text = '<b>'.$mediname.'</b>';
                                		//$info_text .= '<br/>'.$this->view->translate('dosage').': '.$row['dosage'];
                                		$info_text .= '<br/>'.$this->view->translate('dosage').': '.$dosage;
	                                    $info_text .= '<div class="tooltip_info">';
	                                    $info_text .= '<h2>'.$this->translate('Given information').'</h2>';
	                                    //ISPC-2583 pct.8 Lore 22.05.2020
	                                    $info_text .= '<span class="med_status"><b>'.$this->translate('dosage_status_label').':</b> '.$this->translate('dosage_status_'.$vh['dosage_status']).'</span>';
	                                    
	                                    $info_text .= '<span><b>'.$this->translate('Documented by').':</b> '.$users_details[$vh['create_user']].'</span>';
	                                    
	                                    if($save_dosage_interaction['dosage_status'] == 'not_given' ){
	                                        $info_text .= '<span><b>'.$this->translate('not_given_reason').':</b> '.$vh['not_given_reason'].'</span>';
	                                    }
	                                    //ISPC-2583 pct.8 Lore 22.05.2020
	                                    if($save_dosage_interaction['dosage_status'] != 'not_taken_by_patient'){
	                                        $info_text .= '<span><b>'.$this->translate('Given dosage').':</b> '.$vh['dosage'].' '.$row['unit_name'].'</span>';
	                                    }
	                                    
	                                    $info_text .= '<span><b>'.$this->translate('dosage_given_time').':</b> '.date('H:i',strtotime($vh['dosage_date'])).'</span>';
	                                    
	                                    $info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($vh['documented_date'])).'</span>';
	                                    
	                                    if(!empty($vh['documented_info'])){
	                                        $info_text .= '<span><b>'.$this->translate('documented_info').'</b> '.$vh['documented_info'].'</span>';
	                                    }
	                                    $info_text .= '</div>';
	                                
	                                	}
	                                	else 
	                                	{
	                                		$info_text = '<div class="tooltip_info" style="border:0px;">';
	                                		$info_text .= '<span><b>'.$this->translate('Given dosage').':</b> '.$vh['dosage'].' '.$row['unit_name'].'</span>';
	                                		$info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($vh['dosage_date'])).'</span>';                                		
	                                	}
	                                	//$dosage = is_array($row['dosage']) ? $row['main_old_dosage'] : $row['dosage'];
		                                $chart_data['series'][$js_idx]['data'][] = array(
		                                    //'x' => $this->_chart_timestamp($curent_day, 8, 0),
		                                	//'x' => $this->_chart_timestamp(strtotime(substr($vh['dosage_date'], 0, 10)), $dosage_hour, $dosage_minutes),
		                                    'x' => $this->_chart_timestamp_datetime($vh['dosage_date']),
		                                    'y' => $js_idx,
		                                    'time_schedule' => 0,
		                                    'marker' => $current_marker,
		                                    'drugplan_id' => $row['id'],
		                                    'medication_name' => $mediname,
		                                    'is_given' => $current_is_given,
		                                    'dosage_unit'=>  $row['unit_name'],
		                                   
		                                    'documented_dosage_interaction'=>array(                                    	
		                                        'entry_id'=> $vh['id'],
		                                        'status'=> $vh['dosage_status'],
		                                        'dosage'=> !empty($vh['dosage'])&& strlen($vh['dosage']) > 0 ? $vh['dosage']: $row['dosage'],
		                                        //'dosage_date'=>  date('Y-m-d H:i:s', $this->_chart_timestamp($curent_day, 8, 0)/1000 ),
		                                    	'dosage_date'=>  date('Y-m-d H:i:s', $this->_chart_timestamp(strtotime(substr($vh['dosage_date'], 0, 10)), $dosage_hour, $dosage_minutes)/1000 ),
		                                    	//--
		                                        'dosage_time_interval'=> !empty($vh['dosage_time_interval'])? $vh['dosage_time_interval']: $dosage['dosage_time_interval'],
		                                        'documented_info'=> $vh['documented_info'],
		                                        'not_given_reason'=> $vh['not_given_reason'],
		                                    ),
		                                    
		                                    'label' => count($displayed_days) < 3 ? '<span class="dl">'.$dosage .$row['unit_name'].'</span>':'',
		                                    'info' => $info_text
		                                );
                           // }
                                	}
                        //--
                    		}
                    
                	}
                }                
            $js_idx++;            
        }
        foreach ($chart_data['series_line'] as $extra) {
            $chart_data['series'][] = $extra;
        }
        
        unset($chart_data['series_line']);
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $title_identification ='medication_'.$type;
        $chart_data['title']  = $this->__getChartTitle($title_identification);

          
        $chart_data['chart_height'] = (count($chart_data['plotlines']) +1) * 50;
        
        $chart_data['hasData']  = 1;
        if(empty($chart_data['series'])){
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            $chart_data['plotlines']=array();
        }
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
//         dd($chart_data);
        $rez = json_encode($chart_data);
 
        
        
        return $rez;
    }
    
    
    public function fetch_medication_pumpe($ipid = 0, $period = array(), $type = 'isschmerzpumpe'){
        if(empty($ipid)){
            return;
        }
        
        $now = date("Y-m-d H:i:s");
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }

        $drugplan = new PatientDrugPlan();
        $all_data = $drugplan->get_chart_medication($ipid,$this->logininfo->clientid, false, $type);

        
        $cocktails_model = new PatientDrugPlanCocktails();
        $cocktails_arr = $cocktails_model->getCocktails($ipid);
        
        if(empty($cocktails_arr) ||  $period['start_strtotime'] > strtotime(date('Y-m-d')))
        {
            $chart_data = array();
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            $chart_data['title'] = $this->__getChartTitle('Pumpe'); //ISPC-2661 pct.11 Carmen 16.09.2020 
            $rez = json_encode($chart_data);
            return $rez;
        }
        
        
        $pumpe_infos = array();
        foreach($cocktails_arr as $k=>$p){
            $pumpe_infos[$p['id']] =  $p;
        }
        $alls_data = array();
        $cocktailids = array();
        foreach($all_data[$ipid] as $drugplan_id=>$vm){
            $alls_data[$ipid][$vm['cocktailid']]['pumpe'] = $pumpe_infos[$vm['cocktailid']];
            $alls_data[$ipid][$vm['cocktailid']]['drugs'][$drugplan_id] = $vm;
            $meds[] = $drugplan_id;
            $cocktailids[] = $vm['cocktailid'];
        }
        $cocktailids = array_unique($cocktailids);
        
        
        // get all history for this medis 
        $changes_medis= array();
        $changes_medis_q = Doctrine_Query::create()
        ->select('*')
        ->from('PatientDrugPlanHistory')
        ->where("ipid = ?", $ipid)
        ->andWhereIn("pd_cocktailid", $cocktailids)
        ->orderBy("id ASC");
        $changes_medis = $changes_medis_q->fetchArray();
        
        $changed_pumpe_medis = array();
        $changed_pumpe_medis_ids = array();
        //ISPC-2871,Elena,30.03.2021
        $pd_ids = [];
        $medication_master_ids = [];
        $changed_pumpe_medis_extended = [];
        foreach($changes_medis as  $ck=>$h_data){
            $changed_pumpe_medis[$h_data['pd_cocktailid']][] = $h_data['create_date'];
            $changed_pumpe_medis_ids[$h_data['pd_cocktailid']][] = $h_data['pd_id'];
            //ISPC-2871,Elena,30.03.2021
            $changed_pumpe_medis_extended[$h_data['pd_cocktailid']][] = ['date' => $h_data['create_date'],
                'name' => $h_data['pd_medication_name'],
                'bolus' => $h_data['pd_cocktail_bolus'],
                'max_bolus' => $h_data['pd_cocktail_max_bolus'],
                'medication_master_id' => $h_data['pd_medication_master_id'],
                'pd_id' => $h_data['pd_id'],
                'pd_cocktailid'     => $h_data['pd_cocktailid']
            ];
            if(!in_array($h_data['pd_medication_master_id'], $medication_master_ids)){
                $medication_master_ids[] = $h_data['pd_medication_master_id'];
        }
            if(!in_array($h_data['pd_id'], $pd_ids)){
                $pd_ids[] = $h_data['pd_id'];
            }
        
        }
        //ISPC-2871,Elena,30.03.2021
        $drugplan_data_q = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan pdp')
            ->where("ipid = ?", $ipid)
            ->andWhereIn("id", $pd_ids);
        $drugplan_data =   $drugplan_data_q->fetchArray();
        $drugplan_data_ordered = [];
        foreach($drugplan_data as $onedata){
            $drugplan_data_ordered[$onedata['id']] = $onedata;
        }


        
        
        // get all given info
        $saved_dosage_interacion_array = array();
        $saved_dosage_interacion_array = PatientDrugPlanDosageGivenTable::findAllByIpids(array($ipid));
        
        $saved_dosage_interacion = array();
        $saved_dosage_interacion_NoTime = array();
        foreach($saved_dosage_interacion_array as $k=>$dsg_interaction){
            if(!empty($dsg_interaction['cocktail_id'])){
                $saved_dosage_interacion[$dsg_interaction['cocktail_id']][ $dsg_interaction['dosage_date'] ]  = $dsg_interaction;
            }
        }
        
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        $user =  new User();
        $users_details = array();
        $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);
        
        
        
        $bar_color = array();
        $bar_color[0] = 'brown';
        $bar_color[1] = 'grey';
        $bar_color[2] = 'purple';
        $bar_color[3] = 'lime';
        $bar_color[4] = 'black';
        $bar_color[5] = 'blue';
        $bar_color[6] = 'yellow';
        $bar_color[7] = 'green';
        
        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,
            
            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',
            
            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => - 2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );
        
        $chart_index = 0;
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'], // 'min' => $start_js_date,
            'max' => $period['js_max'] // 'max' => $end_js_date,
        );
        $y_pumpe = 1;
        $categories = array();//ISPC-2871,Elena,30.03.2021
        foreach ( $alls_data[$ipid] as $pumpe_id => $p_data) {
            
            $chart_data['yaxis'][$chart_index] = $def_yaxis;
            $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = '#000000';
            
            $name =  $chart_index!=0 ? 'PUMPE '.$chart_index : "PUMPE";
            //ISPC-2871,Elena,30.03.2021
            
            $pumpe_infos_text = '<div class="tooltip_info" style="border:0px;">';
            $pumpe_infos_text .= '<h2>Pumpe</h2>';
            $pumpe_infos_text .= '<span>' . $p_data['pumpe']['description']. '</span>';
            $pumpe_infos_text .= '<span><b>Bolus:</b> ' . $p_data['pumpe']['bolus']. '</span>';
            $pumpe_infos_text .= '<span><b>max. Bolus:</b> ' . $p_data['pumpe']['max_bolus']. '</span>';
            $pumpe_infos_text .= '<span><b>Flussrate:</b> ' . $p_data['pumpe']['flussrate']. $p_data['pumpe']['flussrate_type'] . '</span>';
            $pumpe_infos_text .= '<span><b>Sperrzeit:</b> ' . $p_data['pumpe']['sperrzeit'] . '</span>';
            $pumpe_last_change_date = $p_data['pumpe']['change_date'];
            $pumpe_infos_text .= '<span><b>Medikamente:</b></span>';

            //ISPC-2871,Elena,30.03.2021
            $cats = [];
            //$name = $p_data['medication_name'];
            
            $chart_data['series'][$chart_index] = array(
                'type' => 'line',
                'lineWidth' => 2,
                'stickyTracking' => false,
                'name' => $name,
                'color' => null,
                'marker' => array(
                    'enabled' => false
                ) 
            );
            
            
            // Add start point 
            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($p_data['pumpe']['create_date']),
                'y' => $y_pumpe,
                'marker' => array(
                    'enabled' => false
                ),
            );
            $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
 
            
            
        $color_index = 0 ;
            //ISPC-2871,Elena,30.03.2021
        //print_r($p_data['drugs']);
            /* data structure
             * [volume] =>
                    [dosage_24h_manual] => 24
                    [unit_dosage] =>
                    [unit_dosage_24h] =>
                    [escalation] =>
                    [overall_dosage_h] =>
                    [overall_dosage_24h] =>
                    [overall_dosage_pump] =>
                    [drug_volume] =>
                    [unit2ml] =>
                    [concentration_per_drug] =>
                    [bolus_per_med] =>
             */
        foreach($p_data['drugs'] as $drugplan_id=>$drug){
            $cats[] = $drug['name'];//ISPC-2871,Elena,30.03.2021 //medis as categories
            $pumpe_infos_text .= '<span><b>Name: </b>' . $drug['name']. '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text .= '<span><b>Dosierung: </b>' . $drug['dosage']. $drug['extra']['unit_dosage'] .  '</span>';//ISPC-2871,Elena,30.03.2021
           // $pumpe_infos_text .= '<span><b>Bolus:</b> ' . $drug['extra']['bolus_per_med'] . '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text.= '<span><b>Volume:</b> ' . $drug['extra']['drug_volume'] . '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text.= '<span><b>Konzentration:</b> ' . $drug['extra']['concentration_per_drug'] . '</span>';//ISPC-2871,Elena,30.03.2021

            if(!in_array($drugplan_id,$changed_pumpe_medis_ids[$pumpe_id])){
                //ISPC-2871,Elena,30.03.2021
                $info_text = '<div class="tooltip_info" style="border:0px;">';
                $info_text .= '<h2>Information</h2>';
                $info_text .= '<span><b>Datum:</b> ' . date('d.m.Y', strtotime($drug['date']))  . '</span>' ;
                $info_text .= '<span>' . $drug['name'] . '</span>';
                //$info_text .= '<span>Datum: ' . date('d.m.Y', strtotime($drug['date']))  . '</span>' ;
                $info_text .= '<span><b>Bolus:</b> ' . $drug['extra']['bolus_per_med'] . '</span>';
                $info_text .= '<span><b>Volume:</b> ' . $drug['extra']['drug_volume'] . '</span>';
                $info_text .= '<span><b>Dosierung:</b> ' . $drug['extra']['overall_dosage_pump'] . '</span>';
                $info_text .= '<span><b>Konzentration:</b> ' . $drug['extra']['concentration_per_drug'] . '</span>';//ISPC-2871,Elena,30.03.2021

                $info_text .= '</div>';

                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($drug['date']),
                    'y' => $y_pumpe,
                    'color'=>'grey',
                    'marker' => array(
                            'enabled'=> true,//ISPC-2871,Elena,30.03.2021
                        'symbol'=>'square',
                        'lineColor' => 'grey',
                        'lineWidth' => 1,
                        'radius' => 5,//ISPC-2871,Elena,30.03.2021
                    ),
                   // 'noTooltip' => true,//ISPC-2871,Elena,30.03.2021
                    'info' => $info_text //ISPC-2871,Elena,30.03.2021
                );
                    $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];

                    $chart_series[$chart_index]['zones'][] = array(
                        'value'=> $this->_chart_timestamp_datetime($drug['date']),
                        'color'=>$bar_color[$color_index]
                    );
                    $chart_data['series'][$chart_index]['zoneAxis'] = 'x';
                    $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
                    $color_index ++;

                }
            }
            //ISPC-2871,Elena,30.03.2021
            //we can have more than 1 medi for pumpe, that's why we need formatting
            $cats_formatted = [];
            foreach($cats as $cat){
                $cats_formatted[] = '<span style="font-size:smaller;font-weight:normal;">' . $cat . '</span>';
            }
            //$chart_data['series'][$chart_index]['name'] = $name . '<br/>' . implode($cats_formatted, '<br/>');

            
            $chart_data['categories'][$y_pumpe] = implode($cats, "<br/>");
            
            //add point for every change 
            $color_index = 1;
            //ISPC-2871,Elena,30.03.2021
            foreach ($changed_pumpe_medis_extended[$pumpe_id] as $k => $change_data) {
                //ISPC-2871,Elena,30.03.2021
                $info_text = '<div class="tooltip_info" style="border:0px;">';
                $info_text .= '<h2 style="border:0px;">Information</h2>';
                $info_text .= '<span><b>Datum:</b> ' . date('d.m.Y', strtotime($change_data['date'])) . '</span>';
                $info_text .= '<span>' . $change_data['name'] . '</span>';
                $info_text .= '<span><b>Bolus:</b> ' . $change_data['bolus'] . '</span>';
                $info_text .= '<span><b>Max. Bolus:</b> ' . $change_data['max_bolus'] . '</span>';
                if(abs(strtotime($drugplan_data_ordered[$change_data['pd_id']]['delete_date']) - strtotime($change_data['date'])) < 5){
                    $info_text .= '<span>Entfernt</span>';
                }
                if(abs(strtotime($drugplan_data_ordered[$change_data['pd_id']]['create_date']) - strtotime($change_data['date'])) < 5){
                    $info_text .= '<span>Erstellt</span>';
                }
                $info_text .= '</div>';
                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($change_data['date']),//ISPC-2871,Elena,30.03.2021
                    'y' => $y_pumpe,
                    'color'=>'grey',
                    'marker' => array( //ISPC-2871,Elena,30.03.2021
                        'enabled'=> true,
                        'symbol'=>'square',
                        'lineColor' => 'grey',
                        'lineWidth' => 1,
                        'radius' => 5,//ISPC-2871,Elena,30.03.2021
                    ),
                    //ISPC-2871,Elena,30.03.2021
                   'info' => $info_text
                   // 'noTooltip' => true

                );
                $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
                
                $chart_series[$chart_index]['zones'][] = array(
                    'value'=> $this->_chart_timestamp_datetime($change_data['date']),//ISPC-2871,Elena,30.03.2021
                    'color'=>$bar_color[$color_index]
                );
                $chart_data['series'][$chart_index]['zoneAxis'] = 'x';
                $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
                $color_index ++;
            }
            
            
            
            
            //add point for every GIVEN
            foreach ($saved_dosage_interacion[$pumpe_id] as $k_dosage_Date => $given_values) {
                
                switch ($given_values['dosage_status']){
                    case 'given':
                        $current_marker = $this->_chart_circle_full_green();
                        break;
                        
                    case 'not_given':
                        $current_marker = $this->_chart_circle_full_red();
                        break;
                        
                    case 'given_different_dosage':
                        $current_marker = $this->_chart_circle_full('blue');
                        $current_marker = $this->_chart_circle_full_blue();
                        break;
                        
                    case 'not_taken_by_patient':
                        $current_marker = $this->_chart_circle_full_yellow();
                        break;
                    default:
                        $current_marker = $this->_chart_circle_full('grey','grey');
                        break;
                }
                $info_text="";
                $save_dosage_interaction= $given_values;
                if(!empty($save_dosage_interaction)){
                    $info_text .= '<div class="tooltip_info">';
                    $info_text .= '<h2>'.$this->translate('Given information').'</h2>';
                    $info_text .= '<span class="med_status"><b>'.$this->translate('dosage_status_label').':</b> '.$this->translate('dosage_status_'.$save_dosage_interaction['dosage_status']).'</span>';
                    
                    $info_text .= '<span><b>'.$this->translate('Documented by').':</b> '.$users_details[$save_dosage_interaction['create_user']].'</span>';
                    
                    if($save_dosage_interaction['dosage_status'] == 'not_given' ){
                        $info_text .= '<span><b>'.$this->translate('not_given_reason').':</b> '.$save_dosage_interaction['not_given_reason'].'</span>';
                    }
                    if($save_dosage_interaction['dosage_status'] != 'not_taken_by_patient'){
                        $info_text .= '<span><b>'.$this->translate('Given bolus').':</b> '.$save_dosage_interaction['dosage'].'</span>';
                    }
                    
                    $info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($save_dosage_interaction['dosage_date'])).'</span>';
                    
                    $info_text .= '<span><b>'.$this->translate('dosage_given_time').':</b> '.date('H:i',strtotime($save_dosage_interaction['dosage_date'])).'</span>';
                    
                    if(!empty($save_dosage_interaction['documented_info'])){
                        $info_text .= '<span><b>'.$this->translate('documented_info').'</b> '.$save_dosage_interaction['documented_info'].'</span>';
                    }
                    $info_text .= '</div>';
                }
                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($given_values['dosage_date']),
                    'y' => $y_pumpe,
                    'color'=>null,
                    'marker' => $current_marker,
                    'drugplan_id' => 0,
                    'medication_name' => $name,
                    'cocktail_id'=> $pumpe_id,
                    'dosage_unit' => '',
                    
                    'documented_dosage_interaction'=>array(
                        'cocktail_id'=> $pumpe_id,
                        'entry_id'=> $given_values['id'],
                        'status'=> $given_values['dosage_status'],
                        'dosage'=> !empty($given_values['dosage'])&& strlen($given_values['dosage']) > 0 ? $save_dosage_interaction['dosage']: '',
                        'dosage_date'=> $given_values['dosage_date'],
                        'dosage_time_interval'=> !empty($given_values['dosage_time_interval'])? $given_values['dosage_time_interval']: '',
                        'documented_info'=> $given_values['documented_info'],
                        'not_given_reason'=> $given_values['not_given_reason'],
                    ),
                    'info'=> $info_text,
                );
                $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
            }
            

            
            //ISPC-2871,Elena,30.03.2021
            //add point for last change
            $pumpe_infos_text_without_date = $pumpe_infos_text;
            $pumpe_infos_text .= '<span><b>Datum: </b>' . date('d.m.Y', strtotime($pumpe_last_change_date)). '</span>';
            $pumpe_infos_text .= '</div>';

            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($pumpe_last_change_date),
                'y' => $y_pumpe,
                'color'=>'grey',
                'marker' => array( //ISPC-2871,Elena,30.03.2021
                    'enabled'=> true,
                    'symbol'=>'square',
                    'lineColor' => 'grey',
                    'lineWidth' => 1,
                    'radius' => 5,//ISPC-2871,Elena,30.03.2021
                ),
                //'cocktail_id'=> 0,
               // 'cocktail_id'=> $pumpe_id,
               // 'medication_name' => $name,
               // 'dosage_unit' => '',
//
//                'documented_dosage_interaction'=>array(
//                    'status'=> '',
//                    'cocktail_id'=> $pumpe_id,
//                    'dosage'=> $p_data['pumpe']['bolus'],
//                    'dosage_time_interval'=> date('H:i:s',strtotime($now)),
//                ),
                'info' => $pumpe_infos_text
            );
            $pumpe_infos_text = $pumpe_infos_text_without_date . '</div>';
           //add point for NOW
           $current_marker = $this->_chart_circle_full('grey','grey');
            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($now),
                'y' => $y_pumpe,
                'color'=>'red',
                'marker' => $current_marker,
                'cocktail_id'=> 0,
                'pumpe_id'=> $pumpe_id,//ISPC-2871,Elena,12.04.2021
                'medication_name' => $name,
                'dosage_unit' => '',
                
                'documented_dosage_interaction'=>array(
                    'status'=> '',
                    'cocktail_id'=> $pumpe_id,
                    'dosage'=> $p_data['pumpe']['bolus'],
                    'dosage_time_interval'=> date('H:i:s',strtotime($now)),
                ),
                'info' => $pumpe_infos_text //ISPC-2871,Elena,30.03.2021
            );
            $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
 
            
            $chart_series[$chart_index]['zones'][] = array(
                'value'=> $this->_chart_timestamp_datetime($now),
            );
            
            $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
            
            $chart_index ++;
            $y_pumpe++;
        }
        
        $chart_data['hasData']  = 1;
        if (empty($cocktailids)) {
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            unset($chart_data['series']);
        } else{
            if(count($cocktailids) ==1 ){
                $chart_data['chart_height'] =  150;
            } else{
                $chart_data['chart_height'] = count($cocktailids) * 80;
            }
        }

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp, $end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('Pumpe');//ISPC-2871,Elena,30.03.2021
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);

        $rez = json_encode($chart_data);
        
        return $rez;
    }

    /**
     * @param int $ipid
     * @param array $period
     * @param string $type
     * @return false|string|void
     *
     * ISPC-2871,Elena,12.04.2021
     */
    public function fetch_medication_pumpe_perfusor($ipid = 0, $period = array(), $type = 'ispumpe'){
        if(empty($ipid)){
            return;
        }

        $now = date("Y-m-d H:i:s");
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }

        $drugplan = new PatientDrugPlan();
        $all_data = $drugplan->get_chart_medication($ipid,$this->logininfo->clientid, false, $type);

        $pump_data = new PatientDrugplanPumpe();
        $cocktails_arr = $pump_data->getCocktails($ipid);
        //$cocktails_model = new PatientDrugPlanCocktails();
        //$cocktails_arr = $cocktails_model->getCocktails($ipid);

        if(empty($cocktails_arr) ||  $period['start_strtotime'] > strtotime(date('Y-m-d')))
        {
            $chart_data = array();
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            $chart_data['title'] = $this->__getChartTitle('PumpePerfusor'); //ISPC-2661 pct.11 Carmen 16.09.2020
            $rez = json_encode($chart_data);
            return $rez;
        }


        $pumpe_infos = array();
        foreach($cocktails_arr as $k=>$p){
            $pumpe_infos[$p['id']] =  $p;
        }
        //print_r($pumpe_infos);
        $alls_data = array();
        $cocktailids = array();
//print_r($all_data[$ipid]);
        foreach($all_data[$ipid] as $drugplan_id=>$vm){

            $alls_data[$ipid][$vm['pumpe_id']]['pumpe'] = $pumpe_infos[$vm['pumpe_id']];
            $alls_data[$ipid][$vm['pumpe_id']]['drugs'][$drugplan_id] = $vm;
            $meds[] = $drugplan_id;
            $cocktailids[] = $vm['pumpe_id'];
        }
        $cocktailids = array_unique($cocktailids);
        //print_r($alls_data);
        //die();


        // get all history for this medis
        $changes_medis= array();
        $changes_medis_q = Doctrine_Query::create()
        ->select('*')
        ->from('PatientDrugPlanHistory')
        ->where("ipid = ?", $ipid)
        ->andWhereIn("pd_pumpe_id", $cocktailids)
        ->orderBy("id ASC");
        $changes_medis = $changes_medis_q->fetchArray();
//print_r($changes_medis);
        $changed_pumpe_medis = array();
        $changed_pumpe_medis_ids = array();
        //ISPC-2871,Elena,30.03.2021
        $pd_ids = [];
        $medication_master_ids = [];
        $changed_pumpe_medis_extended = [];
        foreach($changes_medis as  $ck=>$h_data){
            $changed_pumpe_medis[$h_data['pd_pumpe_id']][] = $h_data['create_date'];
            $changed_pumpe_medis_ids[$h_data['pd_pumpe_id']][] = $h_data['pd_id'];
            //ISPC-2871,Elena,30.03.2021
            $changed_pumpe_medis_extended[$h_data['pd_pumpe_id']][] = ['date' => $h_data['create_date'],
                'name' => $h_data['pd_medication_name'],
                'bolus' => $h_data['pd_cocktail_bolus'],
                'max_bolus' => $h_data['pd_cocktail_max_bolus'],
                'medication_master_id' => $h_data['pd_medication_master_id'],
                'pd_id' => $h_data['pd_id']
            ];
            if(!in_array($h_data['pd_medication_master_id'], $medication_master_ids)){
                $medication_master_ids[] = $h_data['pd_medication_master_id'];
            }
            if(!in_array($h_data['pd_id'], $pd_ids)){
                $pd_ids[] = $h_data['pd_id'];
            }

        }
        //ISPC-2871,Elena,30.03.2021
        $drugplan_data_q = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan pdp')
            ->where("ipid = ?", $ipid)
            ->andWhereIn("id", $pd_ids);
        $drugplan_data =   $drugplan_data_q->fetchArray();
        $drugplan_data_ordered = [];
        foreach($drugplan_data as $onedata){
            $drugplan_data_ordered[$onedata['id']] = $onedata;
        }

            
            
            
        // get all given info
        $saved_dosage_interacion_array = array();
        $saved_dosage_interacion_array = PatientDrugPlanDosageGivenTable::findAllByIpids(array($ipid));

        $saved_dosage_interacion = array();
        $saved_dosage_interacion_NoTime = array();
        foreach($saved_dosage_interacion_array as $k=>$dsg_interaction){
            if(!empty($dsg_interaction['pumpe_id'])){
                $saved_dosage_interacion[$dsg_interaction['pumpe_id']][ $dsg_interaction['dosage_date'] ]  = $dsg_interaction;
            }
        }


        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        $user =  new User();
        $users_details = array();
        $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);



        $bar_color = array();
        $bar_color[0] = 'brown';
        $bar_color[1] = 'grey';
        $bar_color[2] = 'purple';
        $bar_color[3] = 'lime';
        $bar_color[4] = 'black';
        $bar_color[5] = 'blue';
        $bar_color[6] = 'yellow';
        $bar_color[7] = 'green';

        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,

            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',

            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => - 2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );

        $chart_index = 0;

        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'], // 'min' => $start_js_date,
            'max' => $period['js_max'] // 'max' => $end_js_date,
        );
        $y_pumpe = 1;
        $categories = array();//ISPC-2871,Elena,30.03.2021
        foreach ( $alls_data[$ipid] as $pumpe_id => $p_data) {
            $pumpe_id_val = $p_data['pumpe']['id'];

            $chart_data['yaxis'][$chart_index] = $def_yaxis;
            $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = '#000000';

            $name =  $chart_index!=0 ? 'PUMPE '.$chart_index : "PUMPE";
            //ISPC-2871,Elena,30.03.2021

            $pumpe_infos_text = '<div class="tooltip_info" style="border:0px;">';
            $pumpe_infos_text .= '<h2>Pumpe</h2>';
            $pumpe_infos_text .= '<span>' . $p_data['pumpe']['description']. '</span>';
            $pumpe_infos_text .= '<span><b>Bolus:</b> ' . $p_data['pumpe']['bolus']. '</span>';
            $pumpe_infos_text .= '<span><b>max. Bolus:</b> ' . $p_data['pumpe']['max_bolus']. '</span>';
            //$pumpe_infos_text .= '<span><b>Flussrate:</b> ' . $p_data['pumpe']['flussrate']. $p_data['pumpe']['flussrate_type'] . '</span>';
            $pumpe_infos_text .= '<span><b>Laufrate:</b> ' . $p_data['pumpe']['run_rate']. '</span>';
            $pumpe_infos_text .= '<span><b>Sperrzeit:</b> ' . $p_data['pumpe']['sperrzeit'] . '</span>';
            $pumpe_last_change_date = $p_data['pumpe']['change_date'];
            $pumpe_infos_text .= '<span><b>Medikamente:</b></span>';

            //ISPC-2871,Elena,30.03.2021
            $cats = [];
            //$name = $p_data['medication_name'];

            $chart_data['series'][$chart_index] = array(
                'type' => 'line',
                'lineWidth' => 2,
                'stickyTracking' => false,
                'name' => $name,
                'color' => null,
                'marker' => array(
                    'enabled' => false
                )
            );


            // Add start point
            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($p_data['pumpe']['create_date']),
                'y' => $y_pumpe,
                'marker' => array(
                    'enabled' => false
                ),
            );
            $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];



        $color_index = 0 ;
            //ISPC-2871,Elena,30.03.2021
        //print_r($p_data['drugs']);
            /* data structure
             * [volume] =>
                    [dosage_24h_manual] => 24
                    [unit_dosage] =>
                    [unit_dosage_24h] =>
                    [escalation] =>
                    [overall_dosage_h] =>
                    [overall_dosage_24h] =>
                    [overall_dosage_pump] =>
                    [drug_volume] =>
                    [unit2ml] =>
                    [concentration_per_drug] =>
                    [bolus_per_med] =>
             */
        foreach($p_data['drugs'] as $drugplan_id=>$drug){
            $cats[] = $drug['name'];//ISPC-2871,Elena,30.03.2021 //medis as categories
            $pumpe_infos_text .= '<span><b>Name: </b>' . $drug['name']. '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text .= '<span><b>Dosierung: </b>' . $drug['dosage']. $drug['extra']['unit_dosage'] .  '</span>';//ISPC-2871,Elena,30.03.2021
            //$pumpe_infos_text .= '<span><b>Bolus:</b> ' . $drug['extra']['bolus_per_med'] . '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text.= '<span><b>Volume:</b> ' . $drug['extra']['drug_volume'] . '</span>';//ISPC-2871,Elena,30.03.2021
            $pumpe_infos_text.= '<span><b>Konzentration:</b> ' . $drug['extra']['concentration_per_drug'] . '</span>';//ISPC-2871,Elena,30.03.2021

            if(!in_array($drugplan_id,$changed_pumpe_medis_ids[$pumpe_id])){
                //ISPC-2871,Elena,30.03.2021
                $info_text = '<div class="tooltip_info" style="border:0px;">';
                $info_text .= '<h2>Information</h2>';
                $info_text .= '<span><b>Datum:</b> ' . date('d.m.Y', strtotime($drug['date']))  . '</span>' ;
                $info_text .= '<span>' . $drug['name'] . '</span>';
                //$info_text .= '<span>Datum: ' . date('d.m.Y', strtotime($drug['date']))  . '</span>' ;
                $info_text .= '<span><b>Bolus:</b> ' . $drug['extra']['bolus_per_med'] . '</span>';
                $info_text .= '<span><b>Volume:</b> ' . $drug['extra']['drug_volume'] . '</span>';
                $info_text .= '<span><b>Dosierung:</b> ' . $drug['extra']['overall_dosage_pump'] . '</span>';
                $info_text .= '<span><b>Konzentration:</b> ' . $drug['extra']['concentration_per_drug'] . '</span>';//ISPC-2871,Elena,30.03.2021

                $info_text .= '</div>';

                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($drug['date']),
                    'y' => $y_pumpe,
                    'color'=>'grey',
                    'marker' => array(
                            'enabled'=> true,//ISPC-2871,Elena,30.03.2021
                        'symbol'=>'square',
                        'lineColor' => 'grey',
                        'lineWidth' => 1,
                        'radius' => 5,//ISPC-2871,Elena,30.03.2021
                    ),
                   // 'noTooltip' => true,//ISPC-2871,Elena,30.03.2021
                    'info' => $info_text //ISPC-2871,Elena,30.03.2021
                );
                    $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];

                    $chart_series[$chart_index]['zones'][] = array(
                        'value'=> $this->_chart_timestamp_datetime($drug['date']),
                        'color'=>$bar_color[$color_index]
                    );
                    $chart_data['series'][$chart_index]['zoneAxis'] = 'x';
                    $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
                    $color_index ++;

                }
            }
            //ISPC-2871,Elena,30.03.2021
            //we can have more than 1 medi for pumpe, that's why we need formatting
            $cats_formatted = [];
            foreach($cats as $cat){
                $cats_formatted[] = '<span style="font-size:smaller;font-weight:normal;">' . $cat . '</span>';
            }
            //$chart_data['series'][$chart_index]['name'] = $name . '<br/>' . implode($cats_formatted, '<br/>');


            $chart_data['categories'][$y_pumpe] = implode($cats, "<br/>");

            //add point for every change
            $color_index = 1;
            //ISPC-2871,Elena,30.03.2021
            foreach ($changed_pumpe_medis_extended[$pumpe_id] as $k => $change_data) {
                //ISPC-2871,Elena,30.03.2021
                $info_text = '<div class="tooltip_info" style="border:0px;">';
                $info_text .= '<h2 style="border:0px;">Information</h2>';
                $info_text .= '<span><b>Datum:</b> ' . date('d.m.Y', strtotime($change_data['date'])) . '</span>';
                $info_text .= '<span>' . $change_data['name'] . '</span>';
                $info_text .= '<span><b>Bolus:</b> ' . $change_data['bolus'] . '</span>';
                $info_text .= '<span><b>Max. Bolus:</b> ' . $change_data['max_bolus'] . '</span>';
                if(abs(strtotime($drugplan_data_ordered[$change_data['pd_id']]['delete_date']) - strtotime($change_data['date'])) < 5){
                    $info_text .= '<span>Entfernt</span>';
                }
                if(abs(strtotime($drugplan_data_ordered[$change_data['pd_id']]['create_date']) - strtotime($change_data['date'])) < 5){
                    $info_text .= '<span>Erstellt</span>';
                }
                $info_text .= '</div>';
                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($change_data['date']),//ISPC-2871,Elena,30.03.2021
                    'y' => $y_pumpe,
                    'color'=>'grey',
                    'marker' => array( //ISPC-2871,Elena,30.03.2021
                        'enabled'=> true,
                        'symbol'=>'square',
                        'lineColor' => 'grey',
                        'lineWidth' => 1,
                        'radius' => 5,//ISPC-2871,Elena,30.03.2021
                    ),
                    //ISPC-2871,Elena,30.03.2021
                   'info' => $info_text
                   // 'noTooltip' => true

                );
                $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];

                $chart_series[$chart_index]['zones'][] = array(
                    'value'=> $this->_chart_timestamp_datetime($change_data['date']),//ISPC-2871,Elena,30.03.2021
                    'color'=>$bar_color[$color_index]
                );
                $chart_data['series'][$chart_index]['zoneAxis'] = 'x';
                $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
                $color_index ++;
            }




            //add point for every GIVEN
            foreach ($saved_dosage_interacion[$pumpe_id_val] as $k_dosage_Date => $given_values) {

                switch ($given_values['dosage_status']){
                    case 'given':
                        $current_marker = $this->_chart_circle_full_green();
                        break;

                    case 'not_given':
                        $current_marker = $this->_chart_circle_full_red();
                        break;

                    case 'given_different_dosage':
                        $current_marker = $this->_chart_circle_full('blue');
                        $current_marker = $this->_chart_circle_full_blue();
                        break;
            
                    case 'not_taken_by_patient':
                        $current_marker = $this->_chart_circle_full_yellow();
                        break;
                    default:
                        $current_marker = $this->_chart_circle_full('grey','grey');
                        break;
                }
                $info_text="";
                $save_dosage_interaction= $given_values;
                if(!empty($save_dosage_interaction)){
                    $info_text .= '<div class="tooltip_info">';
                    $info_text .= '<h2>'.$this->translate('Given information').'</h2>';
                    $info_text .= '<span class="med_status"><b>'.$this->translate('dosage_status_label').':</b> '.$this->translate('dosage_status_'.$save_dosage_interaction['dosage_status']).'</span>';

                    $info_text .= '<span><b>'.$this->translate('Documented by').':</b> '.$users_details[$save_dosage_interaction['create_user']].'</span>';

                    if($save_dosage_interaction['dosage_status'] == 'not_given' ){
                        $info_text .= '<span><b>'.$this->translate('not_given_reason').':</b> '.$save_dosage_interaction['not_given_reason'].'</span>';
                    }
                    if($save_dosage_interaction['dosage_status'] != 'not_taken_by_patient'){
                        $info_text .= '<span><b>'.$this->translate('Given bolus').':</b> '.$save_dosage_interaction['dosage'].'</span>';
                    }

                    $info_text .= '<span><b>'.$this->translate('documented_date').':</b> '.date('d.m.Y',strtotime($save_dosage_interaction['dosage_date'])).'</span>';

                    $info_text .= '<span><b>'.$this->translate('dosage_given_time').':</b> '.date('H:i',strtotime($save_dosage_interaction['dosage_date'])).'</span>';

                    if(!empty($save_dosage_interaction['documented_info'])){
                        $info_text .= '<span><b>'.$this->translate('documented_info').'</b> '.$save_dosage_interaction['documented_info'].'</span>';
                    }
                    $info_text .= '</div>';
                }
                $chart_series[$chart_index]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($given_values['dosage_date']),
                    'y' => $y_pumpe,
                    'color'=>null,
                    'marker' => $current_marker,
                    'drugplan_id' => 0,
                    'medication_name' => $name,
                    'cocktail_id'=> 0,//$pumpe_id,
                    'pumpe_id'=> $pumpe_id_val,
                    'dosage_unit' => '',

                    'documented_dosage_interaction'=>array(
                        'cocktail_id'=> 0,//$pumpe_id,
                        'pumpe_id'=> $pumpe_id_val,
                        'entry_id'=> $given_values['id'],
                        'status'=> $given_values['dosage_status'],
                        'dosage'=> !empty($given_values['dosage'])&& strlen($given_values['dosage']) > 0 ? $save_dosage_interaction['dosage']: '',
                        'dosage_date'=> $given_values['dosage_date'],
                        'dosage_time_interval'=> !empty($given_values['dosage_time_interval'])? $given_values['dosage_time_interval']: '',
                        'documented_info'=> $given_values['documented_info'],
                        'not_given_reason'=> $given_values['not_given_reason'],
                    ),
                    'info'=> $info_text,
                );
                $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
            }



            //ISPC-2871,Elena,30.03.2021
            //add point for last change
            $pumpe_infos_text_without_date = $pumpe_infos_text;
            $pumpe_infos_text .= '<span><b>Datum: </b>' . date('d.m.Y', strtotime($pumpe_last_change_date)). '</span>';
            $pumpe_infos_text .= '</div>';

            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($pumpe_last_change_date),
                'y' => $y_pumpe,
                'color'=>'grey',
                'marker' => array( //ISPC-2871,Elena,30.03.2021
                    'enabled'=> true,
                    'symbol'=>'square',
                    'lineColor' => 'grey',
                    'lineWidth' => 1,
                    'radius' => 5,//ISPC-2871,Elena,30.03.2021
                ),
                //'cocktail_id'=> 0,
               // 'cocktail_id'=> $pumpe_id,
               // 'medication_name' => $name,
               // 'dosage_unit' => '',
//
//                'documented_dosage_interaction'=>array(
//                    'status'=> '',
//                    'cocktail_id'=> $pumpe_id,
//                    'dosage'=> $p_data['pumpe']['bolus'],
//                    'dosage_time_interval'=> date('H:i:s',strtotime($now)),
//                ),
                'info' => $pumpe_infos_text
            );
            $pumpe_infos_text = $pumpe_infos_text_without_date . '</div>';
           //add point for NOW
           $current_marker = $this->_chart_circle_full('grey','grey');
            $chart_series[$chart_index]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($now),
                'y' => $y_pumpe,
                'color'=>'red',
                'marker' => $current_marker,
                'cocktail_id'=> 0,
                'pumpe_id'=> $pumpe_id_val,
                'medication_name' => $name,
                'dosage_unit' => '',
                
                'documented_dosage_interaction'=>array(
                    'status'=> '',
                    'pumpe_id'=> $pumpe_id_val,
                    'cocktail_id'=> 0,//$pumpe_id,
                    'dosage'=> $p_data['pumpe']['bolus'],
                    'dosage_time_interval'=> date('H:i:s',strtotime($now)),
                ),
                'info' => $pumpe_infos_text //ISPC-2871,Elena,30.03.2021
            );
            $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
 
            
            $chart_series[$chart_index]['zones'][] = array(
                'value'=> $this->_chart_timestamp_datetime($now),
            );
            
            $chart_data['series'][$chart_index]['zones'] = $chart_series[$chart_index]['zones'];
            
            $chart_index ++;
            $y_pumpe++;
        }
        
        $chart_data['hasData']  = 1;
        if (empty($cocktailids)) {
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            unset($chart_data['series']);
        } else{
            if(count($cocktailids) ==1 ){
                $chart_data['chart_height'] =  150;
            } else{
                $chart_data['chart_height'] = count($cocktailids) * 80;
            }
        }

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp, $end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('PumpePerfusor');//ISPC-2871,Elena,12.04.2021
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);

        $rez = json_encode($chart_data);
        
        return $rez;
    }
    
    
    public function fetch_artificial_entires_exits($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        
        
        $now = date("Y-m-d H:i:s");
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }
        
        if(  $period['start_strtotime'] > strtotime(date('Y-m-d')))
        {
            $chart_data = array();
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
            $rez = json_encode($chart_data);
            return $rez;
        }
        
        
        //artificial entries/exists
        $client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
       
        if($period)
        {
            $sql_period = ' (DATE(option_date) != "0000-00-00" AND option_date BETWEEN ? AND ? ) ';
            
            $sql_period_params = array( $period['start'], $period['end'] );
        }
        else
        {
            $sql_period = ' DATE(option_date) != "0000-00-00"  ';
        }
        
        $all_data = Doctrine_Query::create()
        ->select("*")
        ->from('PatientArtificialEntriesExits')
        ->whereIn('ipid', array($ipid))
//         ->andWhere( $sql_period , $sql_period_params)
        ->fetchArray();
        
        
        foreach($all_data as $k=>$p_art){
            $optkey = array_search($p_art['option_id'], array_column($client_options, 'id'));
            if($optkey !== false)
            {
                $all_data[$k]['master_artificial_name'] = $client_options[$optkey]['name'];
                $all_data[$k]['master_artificial_type'] = $client_options[$optkey]['type'];
            }
        }
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        

        $def_yaxis = array(
            'type' => 'line',
             
        );
        
        $chart_index = 0;
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        $y = 1;
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        foreach($all_data as $k=>$row){
            
 
            $info_text = '';
            //ISPC-2661 pct.4 Carmen 14.09.2020
            /* $info_text .= $this->view->translate('artificial_entry_name').': ' .$row['master_artificial_name'] . "<br/>";
            $info_text .= $this->view->translate('artificial_entry_type').': ' .$this->translate('artificial_'.$row['master_artificial_type']) . "<br/>"; */
            $info_text .= $this->translate('artificial_'.$row['master_artificial_type']) . ': ' . $row['master_artificial_name'] . "<br/>"; 
            //--
            
            $info_text .= $this->view->translate('chart_artificial_start_date').': ' . date('d.m.Y H:i',strtotime($row['option_date'])) . "<br/>";
            
            if(!empty($row['option_localization'])){
                $info_text .= $this->view->translate('artificial_entry_localization').': ' . nl2br($row['option_localization']) . "<br/>";
            }
            if($row['isremove'] == '1' && $row['remove_date'] != "0000-00-00 00:00:00" ){
                $info_text .= $this->view->translate('artificial_remove_date').': ' . date('d.m.Y H:i',strtotime($row['remove_date'])) . "<br/>";
                
                
                $tage = $patient_master->getDaysInBetween(date('Y-m-d',strtotime($row['option_date'])), date('Y-m-d',strtotime($row['remove_date'])));
                $info_text .= $this->view->translate('chart_artificial_entry_age').': ' .count($tage) . "<br/>";
                $info_text .= $this->view->translate('artificial_entry_removed on day').' ' .count($tage) . "<br/>";
            } else{
                
                $tage = $patient_master->getDaysInBetween(date('Y-m-d',strtotime($row['option_date'])), date('Y-m-d'));
                $info_text .= $this->view->translate('chart_artificial_entry_age').': ' .count($tage) . "<br/>";
                
            }
            //ISPC-2661 pct.4 Carmen 14.09.2020
            $info_text .= $this->all_users[$row['create_user']]['shortname'];
            //--
//    
            
            $chart_data['series'][$chart_index] = array(
                'type' => 'line',
                'lineWidth' => 1,
                'stickyTracking' => false,
                'name' => $row['master_artificial_name'],
                'color' => $row['master_artificial_type'] == 'entry' ? 'blue' : 'yellow', //ISPC-2661 pct.4 Carmen 14.09.2020
                'marker' => array(
                    'enabled'=> true,
                    'symbol'=>'circle',
                    'lineWidth' => 1,
                    'radius' => 8,
                ),
            );
            
            $chart_series[ $chart_index ]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($row['option_date']),
                'y' => $y ,
                'entry_id'=>$row['id'],
                'marker' => array(
                    'enabled'=> true,
                    'symbol'=>'circle',
                    'lineWidth' => 1,
                    'radius' => 8,
                ),
                'info'=>$info_text
            );
            
            if($row['isremove'] == '1' && $row['remove_date'] != "0000-00-00 00:00:00" ){
                $chart_series[ $chart_index ]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($row['remove_date']),
                    'y' => $y ,
                    'entry_id'=>$row['id'],
                    'marker'=>array(
                        'enabled'=> true,
                    	//'symbol'=> count($displayed_days) < 3 ? 'url('.APP_BASE.'images/square.png)' : null, //ISPC-2661 pct.4 Carmen 14.09.2020
                    	'symbol'=> 'url('.APP_BASE.'images/square.png)', //ISPC-2661 pct.4 Carmen 14.09.2020
                        'radius' => 8
                    ) ,
                    'info'=>$info_text
                );
            } else{
                $chart_series[ $chart_index ]['data'][] = array(
                    'x' => $this->_chart_timestamp_datetime($now),
                    'y' => $y ,
                    'entry_id'=>$row['id'],
                    'marker' => array(
                        'enabled'=> true,
                        //'symbol'=> count($displayed_days) < 3 ? 'url('.APP_BASE.'images/triangle.png)' : null, //ISPC-2661 pct.4 Carmen 14.09.2020
                    	'symbol'=> 'url('.APP_BASE.'images/triangle.png)', //ISPC-2661 pct.4 Carmen 14.09.2020
                        'lineWidth' => 1,
                        'radius' => 8,
                    ),
                    'info'=>$info_text
                );
                
            }
            
            $chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
            $chart_index++;
            $y = $y+1; //ISPC-2661 pct.4 Carmen 14.09.2020
            
        }
        
 
        $chart_data['chart_height'] = 150; //ISPC-2661 pct.4 Carmen 14.09.2020
        $chart_data['hasData']  = 1;
        if (empty( $chart_data['series'])) {
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('artificial_entires_exits');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        return $rez;
    }
    
    public function fetch_positioning_events_individual($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
       
        $all_data = FormBlockPositioning::get_patients_chart($ipid, $period);
        
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
//         $chart_series[0] = array(
//             'type' => 'scatter',
//             'name' => $this->view->translate('Positionierung'),
//         );
        
        $y =2;
        foreach ($all_data as $row) {
            $info_text = '';
            $info_text .= $this->view->translate('Positionierung').': ' .$row['positioning_type'] . " <br/>";
           // if(!empty($row['positioning_additional_info'])){
            if($row['positioning_additional_info'] != ''){          //ISPC-2522 Lore 15.05.2020
                $info_text .= $this->view->translate('Lagerung in').': ' . nl2br($row['positioning_additional_info']).'°' . "<br/>";
            }
            
            $radius = 8;
            $chart_series[ 0 ]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($row['positioning_date']),
                'y' => $y,
                'color' => null,
                'marker' => array(
                    'enabled'=> true,
                    'lineColor' => '#000000',
                    'lineWidth' => 1,
                    'radius' => $radius
                ),
                'title'=>true,
            	'entry_id'=>$row['id'] ,
                'label' => $row['positioning_type'],
                'info' => $info_text,
            );
            //             $y++;
        }
        
        $chart_data['chart_height'] = 150;
        if (empty($chart_series)) {
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['series'] = $chart_series;
        
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('positioning');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        
        return $rez;
    }
    
    public function fetch_positioning_events($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        
        $now = date("Y-m-d H:i:s");
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
        }
       
        $all_data = FormBlockPositioning::get_patients_chart($ipid);
       //print_r($all_data); exit;
        //ISPC-2662 Carmen 31.08.2020
        $pf = new Application_Form_FormBlockPositioning();
        $postioning_additional_info_mapping = $pf->getColumnMapping('positioning_additional_info');
       	//--
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        
        // first we create intervals
        $interval_data = array();
        $i=0;
        foreach ($all_data as $k => $w) {
            
            // if both start and end are smaller then period start date do not add
            //ISPC-2661 pct.13 Carmen 09.09.2020
            /* if(strtotime($w['positioning_date']) < strtotime($period['start'])
                && ( isset($all_data[$k+1]['positioning_date']) && strtotime($all_data[$k+1]['positioning_date']) < strtotime($period['start']) )
                ){
                    continue;
            } */
        	/* if(strtotime($w['form_start_date']) < strtotime($period['start'])
        			&& ( isset($all_data[$k+1]['form_start_date']) && strtotime($all_data[$k+1]['form_start_date']) < strtotime($period['start']) )
        			){ 
        	
        				continue;
        	}*/
            
            /* if(
                Pms_CommonData::isintersected(strtotime($w['positioning_date']), strtotime($w['positioning_date']), strtotime($period['start']), strtotime($period['end']))
                || Pms_CommonData::isintersected(strtotime($w['positioning_date']), strtotime($w['positioning_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
                ) */
        		/* if(
        		Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime($period['start']), strtotime($period['end']))
        		|| Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
        		)
            { */
                $status = $w['positioning_type']!=null ? $w['positioning_type'] : 'keine';
                $interval_data[$status][$i]['entry_id'] =  $w['id'];
                $interval_data[$status][$i]['usershortname'] = $this->all_users[$w['create_user']]['shortname'];//ISPC-2661 pct.7 Carmen 11.09.2020
                //ISPC-2662 Carmen 31.08.2020
                //$interval_data[$status][$i]['info'] =  !empty($w['positioning_additional_info']) ? $this->view->translate('Lagerung in').': ' . $w['positioning_additional_info'].'°' : "";
                if($w['positioning_additional_info_old'] != "")
                {
                	$w['positioning_additional_info']['storage'] = array_search($w['positioning_additional_info_old'].'°', $postioning_additional_info_mapping['storage']);
                }
                
                $interval_data[$status][$i]['info'] = "";
                if(!empty($w['positioning_additional_info']))
                {
                	$interval_data[$status][$i]['info'] = !empty($w['positioning_additional_info']['no_storage_free_text']) ? $w['positioning_additional_info']['no_storage_free_text']."\n" : "";
                	$interval_data[$status][$i]['info'] .= !empty($w['positioning_additional_info']['storage']) ? $this->view->translate('Lagerung in').': ' . $postioning_additional_info_mapping['storage'][$w['positioning_additional_info']['storage']]."<br />" : "";
                	$interval_data[$status][$i]['info'] .= !empty($w['positioning_additional_info']['storage_support'] && $w['positioning_additional_info']['storage_support'] != '3') ? $this->view->translate('Lagerungsunterstützung mit').': ' . $postioning_additional_info_mapping['storage_support'][$w['positioning_additional_info']['storage_support']]."<br />" : "";
                	$interval_data[$status][$i]['info'] .= !empty($w['positioning_additional_info']['storage_suport_free_text']) ? $this->view->translate('Lagerungsunterstützung mit').': ' . $w['positioning_additional_info']['storage_suport_free_text']."<br />" : "";
                }
               //--
                /* if(strtotime($w['positioning_date']) > strtotime($period['start'])){
                    $interval_data[$status][$i]['start'] = $w['positioning_date'];
                } else{
                    $interval_data[$status][$i]['start'] = $period['start'];
                }
                
                if(isset($all_data[$k+1]['positioning_date'])){
                    $interval_data[$status][$i]['end'] = $all_data[$k+1]['positioning_date'];
                } else{
                    
                    $interval_data[$status][$i]['end'] = $now;// change to NOW
                    $interval_data[$status][$i]['no_end'] = '1';
                } */
                
                //if(strtotime($w['form_start_date']) > strtotime($period['start'])){
                	$interval_data[$status][$i]['start'] = $w['form_start_date'];
                /*} else{
                	$interval_data[$status][$i]['start'] = $period['start'];
                }*/
                
                if(isset($w['form_end_date']) && strtotime($w['form_end_date']) < strtotime($period['end']) && $w['form_end_date'] != '0000-00-00 00:00:00'){
                	$interval_data[$status][$i]['end'] = $w['form_end_date'];
                } else{
                
                	$interval_data[$status][$i]['end'] = date('Y-m-d H:i:s', strtotime('+ 5 minutes', strtotime($now)));// change to NOW //ISPC-2661 Carmen change to future
                	$interval_data[$status][$i]['no_end'] = '1';
                	if($w['isenduncertain'])
                	{
                		$interval_data[$status][$i]['uncertain_end'] = '1';
                	}
                	
                }
                $i++;
            //}
        }
        //--
       //print_r($interval_data); exit;
        //ISPC-2662 Carmen 31.08.2020+ISPC-2661
        $bar_color = array();
        //$bar_color['Lagerung nicht möglich'] = 'yellow';
        $bar_color['Lagerung nicht möglich'][1] = 'yellow';
        $bar_color['Lagerung nicht möglich'][2] = 'lightyellow';
        $bar_color['Lagerung nicht möglich'][3] = 'white';
        //$bar_color['Pat positioniert sich eigenständig'] = 'white';
        $bar_color['Pat positioniert sich eigenständig'][1] = 'Maroon';
        $bar_color['Pat positioniert sich eigenständig'][2] = 'lightMaroon';
        $bar_color['Pat positioniert sich eigenständig'][3] = 'white';
        //$bar_color['rechts'] = 'brown';
        $bar_color['rechts'][1] = 'brown';
        $bar_color['rechts'][2] = 'lightbrown';
        $bar_color['rechts'][3] = 'white';
        //$bar_color['links'] = 'grey';
        $bar_color['links'][1] = 'grey';
        $bar_color['links'][2] = 'lightgrey';
        $bar_color['links'][3] = 'white';
        //$bar_color['Bauchlage'] = 'purple';
        $bar_color['Bauchlage'][1] = 'purple';
        $bar_color['Bauchlage'][2] = 'lightpurple';
        $bar_color['Bauchlage'][3] = 'white';
        //$bar_color['Rückenlage'] = 'lime';
        $bar_color['Rückenlage'][1] = 'lime';
        $bar_color['Rückenlage'][2] = 'lightgrey';
        $bar_color['Rückenlage'][3] = 'white';
        //$bar_color['Bettkante'] = 'black';
        $bar_color['Bettkante'][1] = 'black';
        $bar_color['Bettkante'][2] = 'darkgray';
        $bar_color['Bettkante'][3] = 'white';
        //$bar_color['Sitzend im Stuhl'] = 'blue';
        $bar_color['Sitzend im Stuhl'][1] = 'blue';
        $bar_color['Sitzend im Stuhl'][2] = 'lightblue';
        $bar_color['Sitzend im Stuhl'][3] = 'white';
        //$bar_color['Mobilisationsstuhl'] = 'yellow';
        //$bar_color['Stand'] = 'green';
        $bar_color['Stand'][1] = 'green';
        $bar_color['Stand'][2] = 'lightgreen';
        $bar_color['Stand'][3] = 'white';
        //$bar_color['Histatuch'] = 'orange';
        $bar_color['Histatuch'][1] = 'orange';
        $bar_color['Histatuch'][2] = 'lightorange';
        $bar_color['Histatuch'][3] = 'white';
        //$bar_color['Hängematte'] = 'pink';
        $bar_color['Hängematte'][1] = 'pink';
        $bar_color['Hängematte'][2] = 'lightpink';
        $bar_color['Hängematte'][3] = 'white';
        //--
        //ISPC-2661 pct.7 Carmen 11.09.2020
        $bar_color['Patient lagert sich selbst'][1] = 'olive';
        $bar_color['Patient lagert sich selbst'][2] = 'lightolive';
        $bar_color['Patient lagert sich selbst'][3] = 'white';
        //--
        //http://jsfiddle.net/2fk4ry30/
        $chart_index = 0;
        $categories = array();
        $xaxix = array();
        foreach($interval_data as $type=>$status_periods){
        	//ISPC-2661 Carmen 
        	$categories[] = $type;
        	$countperiods = count($status_periods);
        	//--
            $chart_data['series'][$chart_index]['name'] = $type;//?
            //$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
//             $chart_data['series'][$chart_index]['color'] = null;
            $chart_data['series'][$chart_index]['borderRadius'] = '5';
            $chart_data['series'][$chart_index]['pointWidth'] = '10';
            $chart_data['series'][$chart_index]['pointPadding'] = '0';
            $chart_data['series'][$chart_index]['groupPadding'] = '0';
            $chart_data['series'][$chart_index]['grouping'] = false;
            $chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
           
            foreach($status_periods as $ks=>$speriod)
            {
            	//ISPC-2661 pct.13 Carmen 17.09.2020
            	if($speriod['uncertain_end'] != 1)
            	{
            		
            		if($endunc){
            			$chart_index++;
            			$chart_data['series'][$chart_index]['name'] = $type;
            			//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
            			//             $chart_data['series'][$chart_index]['color'] = null;
            			$chart_data['series'][$chart_index]['borderRadius'] = '5';
            			$chart_data['series'][$chart_index]['pointWidth'] = '10';
            			$chart_data['series'][$chart_index]['pointPadding'] = '0';
            			$chart_data['series'][$chart_index]['groupPadding'] = '0';
            			$chart_data['series'][$chart_index]['grouping'] = false;
            			$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
            			$endunc=false;
            		}
            		$chart_data['series'][$chart_index]['color'] = $bar_color[$type][1];
            		
            		
            	}
            	else
            	{
            		$chart_index++;
            		$chart_data['series'][$chart_index]['name'] = $type;
            		//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
            		//             $chart_data['series'][$chart_index]['color'] = null;
            		$chart_data['series'][$chart_index]['borderRadius'] = '5';
            		$chart_data['series'][$chart_index]['pointWidth'] = '10';
            		$chart_data['series'][$chart_index]['pointPadding'] = '0';
            		$chart_data['series'][$chart_index]['groupPadding'] = '0';
            		$chart_data['series'][$chart_index]['grouping'] = false;
            		$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
            		$chart_data['series'][$chart_index]['color']['linearGradient'] = [x1=>0, x2=>0, y1=>0, y2=>1 ];
            		$chart_data['series'][$chart_index]['color']['stops'] =
            				[[0, $bar_color[$type][3]], // start
            				[0.8, $bar_color[$type][2]], // middle
            				[1, $bar_color[$type][1]] // end
            		];
            		if($ks != ($countperiods-1))
            		{
            		$endunc = true;
            		}
            		
            	}
            	//--
                //if(Pms_CommonData::isintersected(strtotime($speriod['start']), strtotime($speriod['end']), strtotime($period['start']), strtotime($period['end'])))
                //{
                    $chart_data['series'][$chart_index]['data'][] = array(
                        //'x' => 0,
                    	'uncertainend' => ($speriod['uncertain_end'] != 1) ? (($speriod['no_end'] != 1) ? 0 : 1) : 1, //ISPC-2661
                    	'usershortname' => $speriod['usershortname'], //ISPC-2661 pct.7 Carmen 11.09.2020
                        'low' => $this->_chart_timestamp_datetime($speriod['start']),
                        'low_st' => $speriod['start'],
                        'high' =>$this->_chart_timestamp_datetime($speriod['end']),
                        'high_st' =>$speriod['end'],
                        'label'=> $speriod['info'],
                        'entry_id'=> $speriod['entry_id'],
                        'marker' => array(
                            'enabled'=> true,
                            'symbol'=>'circle',
                            'lineColor' => '#000000',
                            'lineWidth' => 1,
                            'radius' => 8,
                        ),
                    );
              //  }
            }
            //ISPC-2661 Carmen
            if(!$endunc)
            {
            	$chart_index++;
            }          
        }
        
        //ISPC-2661 Carmen
       	$chartmodif = $chart_data;
        foreach($chart_data['series'] as $kserie => $serie)
        {	
        	foreach($serie['data'] as $kdatc => $datc)
        	{			
        		if(in_array($serie['name'], $categories))
        		{	
        			$keycat = array_search($serie['name'], $categories);	
	        		$chartmodif['series'][$kserie]['data'][$kdatc]['x'] = $keycat;	        		
        		}
        	}
        }        	
	    $chart_data = $chartmodif;
	    $chart_data['categories'] = $categories;
	    //-- 

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title'] = $this->__getChartTitle('positioning');
        
        
        $chart_data['chart_height'] = 140;
        $chart_data['hasData']  = 1;
        if(empty($chart_data['series'])){
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        return $rez;
    }
    
    public function fetch_suckoff_events($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        
        $all_data = FormBlockSuckoff::get_patients_chart($ipid, $period);
       
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];

        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
        $suction_color = array();
        $suction_color['blutig']='#ff0000';//red
        $suction_color['gräulich']='fbfd76';//green
        $suction_color['bräunlich']='fbfd76';//brown
        $suction_color['gelb-grün']='#fbfd76';//yellow-green
        $suction_color['weiß']='#ffffff';//white
        $suction_color['weiß-schaumig']='#fff000';// grey
        
//         $chart_series[0] = array(
//             'type' => 'scatter',
//             'name' => $this->view->translate('suckoff'),
//             'color' => "#000000"
//         );
 
        $y =2;
        foreach ($all_data as $row) {
            $info_text = '';
            $info_text .= $this->view->translate('suckoff_secretion').': ' .$row['suckoff_secretion'] . "ml <br/>";
            $info_text .= $this->view->translate('suckoff_color').': ' .  $row['suckoff_color']  . "<br/>";
            $info_text .= $this->view->translate('suckoff_consistency').': ' .$row['suckoff_consistency'] . "<br/>";
            if(!empty($row['suckoff_consistency_text'])){
                $info_text .= $this->view->translate('suckoff_consistency_text').': ' . nl2br($row['suckoff_consistency_text']) . "<br/>";
            }
            //ISPC-2523 Lore 14.05.2020
            if($row['suckoff_soothing'] == 1 ){
                $info_text .= $this->view->translate('suckoff_soothing'). "<br/>";
            }
            if($row['suckoff_possible'] == 1){
                $info_text .= $this->view->translate('suckoff_possible'). "<br/>";
            }
            //.
            
            $radius = 8;
            $color = $suction_color[ $row['suckoff_color']];
            $chart_series[ 0 ]['data'][] = array(
                'x' => $this->_chart_timestamp_datetime($row['suckoff_date']),
                'y' => $y,
            	'usershortname' => $this->all_users[$row['create_user']]['shortname'], //ISPC-2661 pct.3 Carmen 10.09.2020
                'color' => $color,
                'marker' => array(
                    'enabled'=> true,
                    'lineColor' => '#000000',
                    'lineWidth' => 1,
                    'radius' => $radius
                ),
                'title'=>true,
            	'entry_id'=>$row['id'] ,
                'info' => $info_text,
            );
//             $y++;
        }
        
        $chart_data['chart_height'] = 150;
        $chart_data['hasData']  = 1;
        if (empty($chart_series)) {
            //nothing to show
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['series'] = $chart_series;
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        
        $chart_data['title']  = $this->__getChartTitle('suckoff_events');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        
        return $rez;
    }
    
    //ISPC-2661 pct.13 Carmen 11.09.2020
    public function fetch_custom_events_individual($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        
        $all_data = FormBlockCustomEvent::get_patients_chart($ipid, $period);

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
        
//         $chart_series[0] = array(
//             'type' => 'scatter',
//             'name' => $this->view->translate('chart_block_custom_events'),
//         );
 
 
        
        $user =  new User();
        $users_details = array();
        $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true); //Carmen 11.09.2020 de modificat dupa ISPC-2261 pct.5 ??
        
        
        // Contact froms 
        $contactforms_array = array();
        $contactforms = new ContactForms();
        $contactforms_array = $contactforms->get_period_contact_forms($ipid, $period);
        if(!empty($contactforms_array)){
            
            $form_types = new FormTypes();
            $contact_form_types = $form_types->get_form_types($this->logininfo->clientid);
            $contact_form_types_final = array();
            foreach($contact_form_types as $k_form_type => $v_form_type)
            {
                $contact_form_types_final[$v_form_type['id']] = $v_form_type['name'];
            }
            $x = count($all_data)+1;
            foreach($contactforms_array as $cf_Date=>$cfs_arr){
                foreach($cfs_arr as $k=>$cfs){
                    
                $all_data[$x]['id'] = $cfs['id'];
                $all_data[$x]['ipid'] = $cfs['ipid'];
                $all_data[$x]['custom_event_name'] = $contact_form_types_final[$cfs['form_type']];
                //ISPC-2661 pct.13 Carmen 11.09.2020
                //$all_data[$x]['custom_event_date'] = $cfs['start_date'];
                $all_data[$x]['form_start_date'] = $cfs['start_date'];
                //--
                $all_data[$x]['custom_event_visit_date'] = date('H:i',strtotime($cfs['start_date'])).'-'.date('H:i',strtotime($cfs['end_date'])).' '.date('d.m.Y',strtotime($cfs['start_date'])) ;
                $all_data[$x]['is_cf'] = '1';
                $all_data[$x]['create_user_name'] = $users_details[$cfs['create_user']];
                $x++;
                }
            }
        
        
        }
        
//         dd($all_data,$contactforms_array);
        
        $y = 2;
        foreach ($all_data as $row) {
            $info_text = '';
            $info_text .= '<div class="custom_event_info">';
            $info_text .= $this->view->translate('name').': <b>' .$row['custom_event_name'] . "</b><br/>";
            if($row['is_cf'] == '1'){
                $info_text .= $this->view->translate('visit_date').': ' . $row['custom_event_visit_date'] . "<br/>";
                
                if(!empty($row['create_user_name'])){
                    $info_text .= $this->view->translate('contact_form_creator').': ' . $row['create_user_name'] . "<br/>";
                }
                
            } else{
                
                if(!empty($row['custom_event_description'])){
                	//ISPC-2661 pct.5 Carmen 11.09.2020
                    $info_text .= $this->view->translate('custom_event_description').': ' . nl2br($row['custom_event_description']);
                    //--
                }
            }
            $info_text .= '</div>';
            
            $color = '#2bae2f';
            $symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)';
            if($row['is_cf'] != '1'){
                $symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/other_dosage.svg)';
            }
            
            $chart_series[ 0 ]['data'][] = array(
            	//ISPC-2261 pct.13 Carmen 11.09.2020
                //'x' => $this->_chart_timestamp_datetime($row['custom_event_date']),
            	'x' => $this->_chart_timestamp_datetime($row['form_start_date']),
            	//--            		
                'y' => $y,
            	'usershortname' => $this->all_users[$row['create_user']]['shortname'], //ISPC-2661 pct.5 Carmen 11.09.2020
                'color' => $color,
                'marker' => array(
                     'enabled'=> true,
//                     'symbol' => 'url('.RES_FILE_PATH.'/images/patient_header/blue_star.png)',
//                     'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)',
                    'symbol' => $symbol,
                ),
                'title'=>true,
                'is_cf'=>$row['is_cf'] == '1' ? '1' : '0',
                'entry_id'=> $row['id'],
                'name'=>$row['custom_event_name'] ,
                'Htmllabel'=>'<span class="point_label custom_events" >'.$row['custom_event_name'].'</span>' ,
                'info' => $info_text
            );
        }
        $chart_data['chart_height'] = 150;
        $chart_data['hasData']  = 1;
        if (empty($chart_series)) {
            //nothing to show
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        
        $chart_data['series'] = $chart_series;
        
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('custom_events');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        
        return $rez;
    }
    
    public function fetch_custom_events($ipid = 0, $period = array()){
    	if(empty($ipid)){
    		return;
    
    	}
    
    	$now = date("Y-m-d H:i:s");
    
    	if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
    		$timezone  = +2;
    		$now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
    	}
    	//
    
    
    	$all_data = FormBlockCustomEvent::get_patients_chart($ipid);// get all data in order to optain a continuas line
    	
    	$start_date_timestamp = $period['start_strtotime'];
    	$end_date_timestamp = $period['end_strtotime'];
    
    	$chart_data = array(
    			'min' => $period['js_min'],// 'min' => $start_js_date,
    			'max' => $period['js_max'] //  'max' => $end_js_date,
    	);
    
    	// first we create intervals
    	$interval_data = array();
    	$i=0;
    	//ISPC-2661 Carmen
    	//$color = '#2bae2f';    	
    	$color[1] = 'blue';
    	$color[2] = 'lightblue';
    	$color[3] = 'white';
    	//--
    	$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)';
    	if($row['is_cf'] != '1'){
    		$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/other_dosage.svg)';
    	}
    	$chart_series = array();
    	foreach ($all_data as $k => $w) {
    
    		// if both start and end are smaller then period start date do not add
    		//ISPC-2661 pct.13 Carmen 10.09.2020
    		/* if(strtotime($w['status_date']) < strtotime($period['start'])
    		 && ( isset($all_data[$k+1]['status_date']) && strtotime($all_data[$k+1]['status_date']) < strtotime($period['start']) )
    		 ){
    		 continue;
    		 } */
    		/* if(strtotime($w['form_start_date']) < strtotime($period['start'])
    				&& ( isset($all_data[$k+1]['form_start_date']) && strtotime($all_data[$k+1]['form_start_date']) < strtotime($period['start']) )
    				){
    					continue;
    		} */
    		 
    
    
    		/*  if(
    		 Pms_CommonData::isintersected(strtotime($w['status_date']), strtotime($w['status_date']), strtotime($period['start']), strtotime($period['end']))
    		 || Pms_CommonData::isintersected(strtotime($w['status_date']), strtotime($w['status_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
    		 ) */
    		 
    		/* if(
    				Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime($period['start']), strtotime($period['end']))
    				|| Pms_CommonData::isintersected(strtotime($w['form_start_date']), strtotime($w['form_start_date']), strtotime("-1 day",strtotime($period['start'])), strtotime($period['end']))
    				)
    
    		{ */
    		if($w['onetimeevent'] != '1')
    		{
    			$status = $w['custom_event_name']!=null ? $w['custom_event_name'] : 'keine';
    			$interval_data[$status][$i]['entry_id'] =  $w['id'];
    			$interval_data[$status][$i]['usershortname'] =  $this->all_users[$w['create_user']]['shortname']; //ISPC-2661 pct.5 Carmen 11.09.2020
    			$info_text = '';
    			$info_text .= '<div class="custom_event_info">';
    			$info_text .= $this->view->translate('name').': <b>' .$status . "</b><br/>";
    			if($w['is_cf'] == '1'){
    				$info_text .= $this->view->translate('visit_date').': ' . $w['custom_event_visit_date'] . "<br/>";
    			
    				if(!empty($w['create_user_name'])){
    					$info_text .= $this->view->translate('contact_form_creator').': ' . $w['create_user_name'] . "<br/>";
    				}
    			
    			} else{
    			
    				if(!empty($w['custom_event_description'])){
    					//ISPC-2661 pct.5 Carmen 11.09.2020
    					$info_text .= $this->view->translate('custom_event_description').': ' . nl2br($w['custom_event_description']);
    					//--
    				}
    			}
    			$info_text .= '</div>';
    			$interval_data[$status][$i]['info'] = $info_text;
    			
    			/*if(strtotime($w['status_date']) > strtotime($period['start'])){
    			 $interval_data[$status][$i]['start'] = $w['status_date'];
    			 } else{
    			 $interval_data[$status][$i]['start'] = $period['start'];
    			 }
    
    			 if(isset($all_data[$k+1]['status_date'])){
    			 $interval_data[$status][$i]['end'] = $all_data[$k+1]['status_date'];
    			 } else{
    
    			 $interval_data[$status][$i]['end'] = $now;// change to NOW
    			 $interval_data[$status][$i]['no_end'] = '1';
    			 } */
    			 
    			//if(strtotime($w['form_start_date']) > strtotime($period['start'])){
    				$interval_data[$status][$i]['start'] = $w['form_start_date'];
    			/* } else{
    				$interval_data[$status][$i]['start'] = $period['start'];
    			} */
    			 
    			if(isset($w['form_end_date']) && strtotime($w['form_end_date']) < strtotime($period['end']) && $w['form_end_date'] != '0000-00-00 00:00:00'){
    				$interval_data[$status][$i]['end'] = $w['form_end_date'];
    			} else{
    				 
    				$interval_data[$status][$i]['end'] = date('Y-m-d H:i:s', strtotime('+ 5 minutes', strtotime($now)));// change to NOW //ISPC-2661 Carmen change to future
                	$interval_data[$status][$i]['no_end'] = '1';
                	if($w['isenduncertain'])
                	{
                		$interval_data[$status][$i]['uncertain_end'] = '1';
                	}
    			}
    			$i++;
    		//}
    	}
    	else 
    	{
    		$info_text = '';
    		$info_text .= '<div class="custom_event_info">';
    		$info_text .= $this->view->translate('name').': <b>' .$w['custom_event_name'] . "</b><br/>";
    		if($w['is_cf'] == '1'){
    			$info_text .= $this->view->translate('visit_date').': ' . $w['custom_event_visit_date'] . "<br/>";
    		
    			if(!empty($w['create_user_name'])){
    				$info_text .= $this->view->translate('contact_form_creator').': ' . $w['create_user_name'] . "<br/>";
    			}
    		
    		} else{
    		
    			if(!empty($w['custom_event_description'])){
    				//ISPC-2661 pct.5 Carmen 11.09.2020
    				$info_text .= $this->view->translate('custom_event_description').': ' . nl2br($w['custom_event_description']);
    				//--
    			}
    		}
    		$info_text .= '</div>';
    		
    		$color = '#2bae2f';
    		$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)';
    		if($w['is_cf'] != '1'){
    			$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/other_dosage.svg)';
    		}
    		
    		//$chart_series[ 0 ]['type'] ='scatter';
    		$chart_series[] = array(
    				//ISPC-2261 pct.13 Carmen 11.09.2020
    				//'x' => $this->_chart_timestamp_datetime($w['custom_event_date']),
    				'y' => $this->_chart_timestamp_datetime($w['form_start_date']),
    				//--
    				'x' => 0,
    				'usershortname' => $this->all_users[$w['create_user']]['shortname'], //ISPC-2661 pct.5 Carmen 11.09.2020
    				'color' => $color,
    				'marker' => array(
    						'enabled'=> true,
    						//                     'symbol' => 'url('.RES_FILE_PATH.'/images/patient_header/blue_star.png)',
    				//                     'symbol' => 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)',
    						'symbol' => $symbol,
    				),
    				'title'=>true,
    				'is_cf'=>$w['is_cf'] == '1' ? '1' : '0',
    				'entry_id'=> $w['id'],
    				'name'=>$w['custom_event_name'] ,
    				'Htmllabel'=>'<span class="point_label custom_events" >'.$w['custom_event_name'].'</span>' ,
    				'info' => $info_text
    		);
    		}
    	}
    	
    	$chart_data['series'][0]['type'] = 'scatter';
    	$chart_data['series'][0]['xAxis'] = 1;
    	$chart_data['series'][0]['data'] = $chart_series;
    	//--
    	
    	//http://jsfiddle.net/2fk4ry30/
    	$series_data = array();
    	$categories = array(); //ISPC-2661 Carmen 
    	$chart_index = 1;
    	$bar_color[1] = 'blue';
    	$bar_color[2] = 'lightblue';
    	$bar_color[3] = 'lightgrey';//ISPC-2871,Elena,30.03.2021
    	
    	foreach($interval_data as $type=>$status_periods){
    		//ISPC-2661 Carmen
    		$categories[] = $type;
    		$countperiods = count($status_periods);
    		//--
    		$chart_data['series'][$chart_index]['type'] = 'columnrange';
    		$chart_data['series'][$chart_index]['xAxis'] = 0;
    		$chart_data['series'][$chart_index]['name'] = $type;
    		//$chart_data['series'][$chart_index]['color'] = 'blue';
    		$chart_data['series'][$chart_index]['borderRadius'] = '5';
    		$chart_data['series'][$chart_index]['pointWidth'] = '10';
    		$chart_data['series'][$chart_index]['pointPadding'] = '0';
    		$chart_data['series'][$chart_index]['groupPadding'] = '0';
    		$chart_data['series'][$chart_index]['grouping'] = false;
    		$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
    
    		foreach($status_periods as $ks=>$speriod)
    		{
    			//ISPC-2661 pct.13 Carmen 17.09.2020
    			if($speriod['uncertain_end'] != 1)
    			{
    			
    				if($endunc){
    					$chart_index++;
    					$chart_data['series'][$chart_index]['type'] = 'columnrange';
    					$chart_data['series'][$chart_index]['xAxis'] = 0;
    					$chart_data['series'][$chart_index]['name'] = $type;
    					//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
    					//             $chart_data['series'][$chart_index]['color'] = null;
    					$chart_data['series'][$chart_index]['borderRadius'] = '5';
    					$chart_data['series'][$chart_index]['pointWidth'] = '10';
    					$chart_data['series'][$chart_index]['pointPadding'] = '0';
    					$chart_data['series'][$chart_index]['groupPadding'] = '0';
    					$chart_data['series'][$chart_index]['grouping'] = false;
    					$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
    					$endunc=false;
    				}
    				$chart_data['series'][$chart_index]['color'] = $bar_color[1];
    			
    			
    			}
    			else
    			{
    				$chart_index++;
    				$chart_data['series'][$chart_index]['type'] = 'columnrange';
    				$chart_data['series'][$chart_index]['xAxis'] = 0;
    				$chart_data['series'][$chart_index]['name'] = $type;
    				//$chart_data['series'][$chart_index]['color'] = $bar_color[$type]; ISPC-2661 pct.13 Carmen 17.09.2020
    				//             $chart_data['series'][$chart_index]['color'] = null;
    				$chart_data['series'][$chart_index]['borderRadius'] = '5';
    				$chart_data['series'][$chart_index]['pointWidth'] = '10';
    				$chart_data['series'][$chart_index]['pointPadding'] = '0';
    				$chart_data['series'][$chart_index]['groupPadding'] = '0';
    				$chart_data['series'][$chart_index]['grouping'] = false;
    				$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
    				$chart_data['series'][$chart_index]['color']['linearGradient'] = [x1=>0, x2=>0, y1=>0, y2=>1 ];
    				$chart_data['series'][$chart_index]['color']['stops'] =
    				[[0, $bar_color[3]], // start
    						[0.8, $bar_color[2]], // middle
    						[1, $bar_color[1]] // end
    				];
    				if($ks != ($countperiods-1))
    				{
    					$endunc = true;
    				}
    			
    			}
    			//--
    			//if(Pms_CommonData::isintersected(strtotime($speriod['start']), strtotime($speriod['end']), strtotime($period['start']), strtotime($period['end'])))
    			//{
    				$series_data[] = 1;
    				$chart_data['series'][$chart_index]['data'][] = array(
    						//'x' => 0,
    						'uncertainend' => ($speriod['uncertain_end'] != 1) ? (($speriod['no_end'] != 1) ? 0 : 1) : 1, //ISPC-2661
    						'usershortname' => $speriod['usershortname'], //ISPC-2661 pct.5 Carmen 11.09.2020
    						'low' => $this->_chart_timestamp_datetime($speriod['start']),
    						'low_st' => $speriod['start'],
    						'high' =>$this->_chart_timestamp_datetime($speriod['end']),
    						'high_st' =>$speriod['end'],
    						'label'=> $speriod['info'],
    						'entry_id'=> $speriod['entry_id'],
    						'marker' => array(
    								'enabled'=> true,
    								'symbol'=>'circle',
    								'lineColor' => '#000000',
    								'lineWidth' => 1,
    								'radius' => 8,
    						),
    				);
    			//}
    
    		}
    		//ISPC-2661 Carmen
            if(!$endunc)
            {
            	$chart_index++;
            } 
    	}
    	
    	//ISPC-2661 Carmen
    	if(!empty($chart_data['series']) && count($chart_data['series']) == 1 && empty($chart_data['series'][0]['data']))
    	{
    		unset($chart_data['series'][0]);
    	}
    	
    	$chartmodif = $chart_data['series'];
    	
    	foreach($chart_data['series'] as $kserie => $serie)
    	{
    		foreach($serie['data'] as $kdatc => $datc)
    		{
    			if(in_array($serie['name'], $categories))
    			{
    				$keycat = array_search($serie['name'], $categories);
    				$chartmodif[$kserie]['data'][$kdatc]['x'] = $keycat;
    			}
    		}
    	}
    	
    	$chart_data['series'] = $chartmodif;
    	$chart_data['categories'] = $categories;
    	//--
    	//print_R($chart_data); exit;
    	$chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
    	//print_r($chart_data); exit;
    	$chart_data['title'] = $this->__getChartTitle('custom_events');
    
    	$chart_data['chart_height'] = 130;
    	
    	if(empty($chart_data['series'])){
    		
    		$chart_data['hasData']  = 0;
    		$chart_data['chart_height'] = 50;
    	}    
    
    	$chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
    
    
    	$chart_data['hasData']  = '0';
    	if(!empty($series_data)){
    		$chart_data['hasData']  = '1';
    	}
    	$rez = json_encode($chart_data);
    
    	return $rez;
    }
    //--
    
    public function fetch_oe_bilancing($ipid = 0, $period = array())
    {
    	if(empty($ipid)){
    		return;
    
    	}
    
    	$now = date("Y-m-d H:i:s");
    
    	if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
    		$timezone  = +2;
    		$now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I")));
    	}
    	//
    
    	//$all_data_S = FormBlockOrganicEntriesExits::get_patients_chart($ipid, $period);
    	$all_data_S = FormBlockOrganicEntriesExits::get_patients_chart($ipid, false, true); //ISPC-2661 Carmen
    	

    	//ISPC-2661 Carmen 02.10.2020
    	$allsets = OrganicEntriesExitsSetsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
    	$allsets_details_arr = array();
        foreach ($allsets as $sdet) {
    		$allsets_details_arr[$sdet['id']] = $sdet;
    	}
    	//--
    	
    	$items2sets = array();
    	$sets = array();
    	foreach($all_data_S as $k=>$oex){
    	    $sets[] = $oex['setid'];
    	    $items2sets[$oex['setid']]['items'][] = $oex;
    	}
    	
    	
    	foreach($items2sets as $set=>$sdata){
    	    usort($items2sets[$set]['items'], array(new Pms_Sorter('organic_date'), "_date_compare"));
    	}
    	
    	$items2sets_chart = array();
    	foreach($items2sets as $si=>$sdata){
    		$last[$si] = end($sdata['items']);
    		if(strtotime($sdata['items'][0]['organic_date']) >= strtotime($period['start']) || (strtotime($last[$si]['organic_date']) >= strtotime($period['start']) && strtotime($last[$si]['organic_date']) <= strtotime($period['end']))) //ISPC-2661 Carmen
    		{
	    	    foreach($sdata['items'] as $k=>$ex){
	    	        if($ex['item_type'] == 'exit'){
	    	            $items2sets_chart[$si]['exits'] = $items2sets_chart[$si]['exits']+ $ex['organic_amount'];
	    	        }elseif($ex['item_type'] == 'entry'){
	    	            $items2sets_chart[$si]['entry'] = $items2sets_chart[$si]['entry'] + $ex['organic_amount'];
	    	        }
	    	    }
	    	    $items2sets_chart[$si]['start'] = $sdata['items'][0]['organic_date'];
	    	    $items2sets_chart[$si]['name'] = 'Bilanzierung#: '.date('d.m.Y',strtotime($sdata['items'][0]['organic_date']));
	    	    //$last[$si] = end($sdata['items']);
	    	    $items2sets_chart[$si]['end'] = $last[$si]['organic_date'];
	    	    
	    	    $items2sets_chart[$si]['value'] = $items2sets_chart[$si]['entry'] - $items2sets_chart[$si]['exits'];
	    	    
	    	    $items2sets_chart[$si]['entry_id'] =  $si ;
	    	    $items2sets_chart[$si]['endset'] =  $allsets_details_arr[$ex['setid']]['endset'] ;
	    	    $items2sets_chart[$si]['usershortname'] =  "" ;
	 
	    	    
	    	    
	    	    $info_text = '';
	    	    $info_text .= '<div class="custom_event_info">';
	//     	    $info_text .=  '  <b>Starte Bilanzierung:' .date('d.m.Y H:i',strtotime($sdata['items'][0]['organic_date'])).'-'.date('d.m.Y H:i',strtotime($last[$si]['organic_date'])). "</b><br/>";
	    	    $info_text .=  '  <b>Bilanzierung: '.$items2sets_chart[$si]['value'].'</b><br/>';
	    	    $info_text .= '</div>';
	    	    $items2sets_chart[$si]['info'] = $info_text;
	    	    
	    	    
	    	    $interval_Data[ $items2sets_chart[$si]['name']][] =$items2sets_chart[$si];
    		}
    	}
    	
    	$start_date_timestamp = $period['start_strtotime'];
    	$end_date_timestamp = $period['end_strtotime'];
    
    	$chart_data = array(
    			'min' => $period['js_min'],// 'min' => $start_js_date,
    			'max' => $period['js_max'] //  'max' => $end_js_date,
    	);
    
    	// first we create intervals
    	$interval_data = array();
    	$i=0;
    	//ISPC-2661 Carmen
    	//$color = '#2bae2f';    	
    	$color[1] = 'blue';
    	$color[2] = 'lightblue';
    	$color[3] = 'white';
    	//--
    	$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/ok.svg)';
    	if($row['is_cf'] != '1'){
    		$symbol = 'url('.RES_FILE_PATH.'/images/chart_icons/other_dosage.svg)';
    	}
//     	foreach ($all_data as $k => $w) {
    
  
//     			$status = $w['custom_event_name']!=null ? $w['custom_event_name'] : 'keine';
//     			$interval_data[$status][$i]['entry_id'] =  $w['id'];
//     			$interval_data[$status][$i]['usershortname'] =  $this->all_users[$w['create_user']]['shortname']; //ISPC-2661 pct.5 Carmen 11.09.2020
//     			$info_text = '';
//     			$info_text .= '<div class="custom_event_info">';
//     			$info_text .= $this->view->translate('name').': <b>' .$status . "</b><br/>";
//     			if($w['is_cf'] == '1'){
//     				$info_text .= $this->view->translate('visit_date').': ' . $w['custom_event_visit_date'] . "<br/>";
    			
//     				if(!empty($w['create_user_name'])){
//     					$info_text .= $this->view->translate('contact_form_creator').': ' . $w['create_user_name'] . "<br/>";
//     				}
    			
//     			} else{
    			
//     				if(!empty($w['custom_event_description'])){
//     					//ISPC-2661 pct.5 Carmen 11.09.2020
//     					$info_text .= $this->view->translate('custom_event_description').': ' . nl2br($w['custom_event_description']);
//     					//--
//     				}
//     			}
//     			$info_text .= '</div>';
//     			$interval_data[$status][$i]['info'] = $info_text;
// 				$interval_data[$status][$i]['start'] = $w['form_start_date'];
    			 
//     			if(isset($w['form_end_date']) && strtotime($w['form_end_date']) < strtotime($period['end']) && $w['form_end_date'] != '0000-00-00 00:00:00'){
//     				$interval_data[$status][$i]['end'] = $w['form_end_date'];
//     			} else{
    				 
//     			$interval_data[$status][$i]['end'] = date('Y-m-d H:i:s', strtotime('+ 3month', strtotime($now)));// change to NOW //ISPC-2661 Carmen change to future
//                 	$interval_data[$status][$i]['no_end'] = '1';
//                 	if($w['isenduncertain'])
//                 	{
//                 		$interval_data[$status][$i]['uncertain_end'] = '1';
//                 	}
//     			}
//     			$i++;
//     	}
   
//     	dd($interval_data,$interval_Data);
    	$series_data = array();
    	$categories = array(); //ISPC-2661 Carmen 
    	$chart_index = 0;
    	foreach($interval_Data as $type=>$status_periods){
    		//ISPC-2661 Carmen
    		$categories[] = $type;
    		//$countperiods = count($status_periods);
    		//--
    		/* $chart_data['series'][$chart_index]['name'] = $type;
    		$chart_data['series'][$chart_index]['color'] = 'blue';
    		$chart_data['series'][$chart_index]['borderRadius'] = '5';
    		$chart_data['series'][$chart_index]['pointWidth'] = '10';
    		$chart_data['series'][$chart_index]['pointPadding'] = '0';
    		$chart_data['series'][$chart_index]['groupPadding'] = '0';
    		$chart_data['series'][$chart_index]['grouping'] = false;
    		$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
     */
            foreach ($status_periods as $ks => $speriod) {
    			//ISPC-2661 pct.13 Carmen 17.09.2020
                if ($speriod['endset'] == 1) {/*
    			
    				if($endunc){
    					$chart_index++; */
    					$chart_data['series'][$chart_index]['name'] = $type;
    					$chart_data['series'][$chart_index]['borderRadius'] = '5';
    					$chart_data['series'][$chart_index]['pointWidth'] = '10';
    					$chart_data['series'][$chart_index]['pointPadding'] = '0';
    					$chart_data['series'][$chart_index]['groupPadding'] = '0';
    					$chart_data['series'][$chart_index]['grouping'] = false;
    					$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
    					$endunc=false;
    				//}
    				$chart_data['series'][$chart_index]['color'] = $color[1];
    			
    			
                } else {
    				//$chart_index++;
    				$chart_data['series'][$chart_index]['name'] = $type;
    				$chart_data['series'][$chart_index]['borderRadius'] = '5';
    				$chart_data['series'][$chart_index]['pointWidth'] = '10';
    				$chart_data['series'][$chart_index]['pointPadding'] = '0';
    				$chart_data['series'][$chart_index]['groupPadding'] = '0';
    				$chart_data['series'][$chart_index]['grouping'] = false;
    				$chart_data['series'][$chart_index]['pointStart'] =   $period['js_min'];
    				$chart_data['series'][$chart_index]['color']['linearGradient'] = [x1=>0, x2=>0, y1=>0, y2=>1 ];
    				$chart_data['series'][$chart_index]['color']['stops'] =
    				[[0, $color[3]], // start
    						[0.8, $color[2]], // middle
    						[1, $color[1]] // end
    				];
    				$speriod['end'] = date('Y-m-d H:i:s', strtotime('+ 3 minutes', strtotime($now)));// change to NOW //ISPC-2661 Carmen change to future
    			
    			}
 
    				$series_data[] = 1;
    				$chart_data['series'][$chart_index]['data'][] = array(
    						//'x' => 0,
    						'uncertainend' => ($speriod['endset'] != 1) ? 1 : 0, //ISPC-2661
    						'usershortname' => $speriod['usershortname'], //ISPC-2661 pct.5 Carmen 11.09.2020
    						'low' => $this->_chart_timestamp_datetime($speriod['start']),
    						'low_st' => $speriod['start'],
    						'high' =>$this->_chart_timestamp_datetime($speriod['end']),
    						'high_st' =>$speriod['end'],
    						'label'=> $speriod['info'],
    						'entry_id'=> $speriod['entry_id'],
    						'marker' => array(
    								'enabled'=> true,
    								'symbol'=>'circle',
    								'lineColor' => '#000000',
    								'lineWidth' => 1,
    								'radius' => 3,
    						),
    				);
    
    		}
    		/* //ISPC-2661 Carmen
            if(!$endunc)
            { */
            	$chart_index++;
            //} 
    	}
    	
    	//ISPC-2661 Carmen
    	$chartmodif = $chart_data;
        foreach ($chart_data['series'] as $kserie => $serie) {
            foreach ($serie['data'] as $kdatc => $datc) {
                if (in_array($serie['name'], $categories)) {
    				$keycat = array_search($serie['name'], $categories);
    				$chartmodif['series'][$kserie]['data'][$kdatc]['x'] = $keycat;
    			}
    		}
    	}
    	$chart_data = $chartmodif;
    	$chart_data['categories'] = $categories;
    	//--
    	
    	$chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
    	//print_r($chart_data); exit;
    	$chart_data['title'] = $this->__getChartTitle('organir_ex_bilancing');
    
    	$chart_data['chart_height'] = 130;
    	if(empty($chart_data['series'])){
    		$chart_data['chart_height'] = 50;
    	}
    
    
    	$chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
    
    
    	$chart_data['hasData']  = '0';
    	if(!empty($series_data)){
    		$chart_data['hasData']  = '1';
    	}
    	$rez = json_encode($chart_data);
    
    	return $rez;
    }

    //TODO-4163
    public function fetch_pcoc_phase($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }

        $all_data = FormBlockPcoc::get_patients_chart($ipid, $period);

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];

        $allitems=FormBlockPcoc::$items;

        if(count($all_data)){
            if(isset($all_data[0]['more']) && count($all_data[0]['more'])){
                foreach ($all_data[0]['more'] as $title=>$k){
                    $allitems['ipos'][$k]=['long'=>$title,'short' => $title, 'itemclass' => 'ipos'];
                }
            }
        }

        //'phase','akps','ipos','nps','pcpss','psysoz','barthel'
        $items=array_merge(
            $allitems['phase'],
            $allitems['akps'],
            ['space1'=>['short'=>'']],

            $allitems['ipos'],
            ['space2'=>['short'=>'']],

            $allitems['pcpss'],
            ['space4'=>['short'=>'']],

            $allitems['nps'],
            ['space3'=>['short'=>'']],

            $allitems['psysoz'],
            ['space5'=>['short'=>'']],

            $allitems['barthel']
        );


        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );

        $timezone = new DateTimeZone('UTC');

        $format = 'Y-m-d H:i:s';

        $y=-1;
        foreach ($items as $item_k=>$item) {
            $y++;
            $chart_data['categories'][]=$item['short'];
            $chart_series[$y]['data']=[];

            $chart_data['plotlines'][]=[
                'color'=> "white",
                'dashStyle'=>  "Solid",
                'is_acute'=>  false,
                'medid'=>  $y,
                'value'=>  0,
                'width'=>  0,
                'zIndex'=>  5
            ];

            if($item['short']==""){
                //$chart_data['plotlines'][]=[
                //    'color'=>'#DDDDFF',
                //    'width'=> 2,
                //    'value'=> $y+0.5,
                //    'dashStyle'=>  "Solid"
                //];
                //$chart_series[$y]['data'][] = array();
            }else {



                foreach ($all_data as $row) {
                    if (
                        !isset($row[$item_k]) //no data
                        || $row[$item_k] == 0)    //saved unfilled
                    {
                        //no data
                        continue;
                    }
                    if ($item['itemclass'] == "phase") {
                        $color = FormBlockPcoc::$itemclasses['phase'][$row['phase_phase']]['color'];
                        $phase_no = intval($row['phase_phase']);
                        if ($phase_no == 5) {
                            $phase_no = 'death';
                        }
                        $symbol = 'url(' . RES_FILE_PATH . '/images/chart_icons/status_' . $phase_no . '.svg)';
                        $textval = FormBlockPcoc::$itemclasses['phase'][$row['phase_phase']]['disp'];
                    }else{
                        $color = FormBlockPcoc::$itemclasses[$item['itemclass']][$row[$item_k]]['color'];
                        $textval = FormBlockPcoc::$itemclasses[$item['itemclass']][$row[$item_k]]['disp'];
                        $symbol = 'circle';
                    }

                    $marker = array(
                        'enabled' => true,
                        'symbol' => $symbol,
                    );

                    if (isset($row['icon']) && count($row['icon']) && $symbol == 'circle') {
                        if (isset($row['icon']['shape'])) {
                            $marker['symbol'] = $row['icon']['shape'];
                        }
                        if (isset($row['icon']['size'])) {
                            $marker['radius'] = $row['icon']['size'];
                        }
                        if (isset($row['icon']['color'])) {
                            $marker['lineWidth'] = 1;
                            $marker['lineColor'] = $row['icon']['color'];
                        }
                    }

                    $info_text = '';
                    $info_text .= '<div class="custom_event_info">';
                    $info_text .= $item['short'] . ': <b>' . $textval . "</b><br/>";
                    $info_text .= 'Erhoben: ' . date("d.m.Y H:i", strtotime($row['misc_date']));
                    $info_text .= '</div>';

                    $chart_series[$y]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($row['misc_date']),
                        'y' => $y,
                        'color' => $color,
                        'marker' => $marker,
                        'title' => true,
                        'is_cf' => $row['is_cf'] == '1' ? '1' : '0',
                        'entry_id' => $row['id'],
                        'name' => $row['custom_event_name'],
                        'Htmllabel' => '<span class="point_label custom_events" >' . $row['custom_event_name'] . '</span>',
                        'info' => $info_text
                    );
                }
            }

        }
        $chart_data['chart_height'] = 800;
        $chart_data['hasData']  = 1;
        if (empty($chart_series)) {
            //nothing to show
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }




        $chart_data['series'] = $chart_series;

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('pcoc_phase');



        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);

        $rez = json_encode($chart_data);


        return $rez;
    }
    //--


    /**
     * ISPC-2697, elena, 20.11.2020
     * @param int $ipid
     * @param array $period
     * @return false|string|void
     */
    public function fetch_ventilation_data($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }

        $all_data = Application_Form_FormBlockKeyValue::get_ventilation_chart($ipid, $period);

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];



        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );

        $timezone = new DateTimeZone('UTC');

        $format = 'Y-m-d H:i:s';
        $chart_index = 0;
        $y = 0;
        //ISPC-2904,Elena,30.04.2021 
        $categories = ['Beatmung' => ['markerColor' => '#cccccc', 'type' => 'line', 'lineWidth' => 0, 'plotlineColor' => 'white'], 'Sauerstoffgabe' => ['markerColor' => '#44bcd8', 'type' => 'columnrange', 'lineWidth' => 1, 'plotlineColor' => '#000']];

        $catCounter = 0;
        //ISPC-2904,Elena,30.04.2021
        $bar_color = array();
        $cats = [];
        //ISPC-2661 Carmen

        $bar_color[1]  = $categories['Sauerstoffgabe']['markerColor'];
        $bar_color[2]  = 'lightblue';
        $bar_color[3]  = 'lightgrey';
        $gradientColor =  ['linearGradient' => [x1 => 0, x2 => 0, y1 => 0, y2 => 1],
            'stops' => [[0, $bar_color[3]], // start
                [0.8, $bar_color[2]], // middle
                [1, $bar_color[1]]]];

        foreach($categories as $cat => $catvals){
            //$cats[] = $cat;//ISPC-2904,Elena,30.04.2021
            $chart_data['categories'][] = $cat;
            $chart_data['plotlines'][]=[
                'color'=> $catvals['plotlineColor'],//ISPC-2904,Elena,30.04.2021
                'dashStyle'=>  "Solid",
                'is_acute'=>  false,
                'medid'=>  $catCounter,
                'value'=>  0,
                'width'=>  0,
                'zIndex'=>  5
            ];
            $chart_data['series'][]= array(
                'type' => $catvals['type'],//ISPC-2904,Elena,30.04.2021
                'lineWidth' => $catvals['lineWidth'],//ISPC-2904,Elena,30.04.2021
                'showInLegend' => false,
                'stickyTracking' => false,
                'name' => $cat ,
                'color' => $catvals['markerColor'],
                'marker' => array(
                    'enabled'=> true,
                    'symbol'=>'circle',
                    'lineWidth' => 1,
                    'radius' => 8,
                ),
            );
            $catCounter ++;
        }

        foreach($all_data as $k=>$row){
            $info_text = '';
            $info_text .= '<div class="tooltip_info">';
            $saved_data = [];//ISPC-2904,Elena,30.04.2021
            $beatmung_data = $row['beatmung'];
            $group = 0;
            $beatmung_date = $row['datum'];
            $beatmung_time = $row['time'];
            $beatmung_date_bis = '' ;
            $beatmung_time_bis  = '';
            $oxygen_bis = '';//ISPC-2904,Elena,30.04.2021
            $beatmung_open_end = false;


            if(isset($beatmung_data['form'] ) &&  $beatmung_data['form'] == 'oxygen'){
                //ISPC-2836,Elena,23.02.2021
                //ISPC-2904,Elena,30.04.2021
                $saved_data['oxygen'] = $beatmung_data['oxygen'];
                $beatmung_date = $row['oxygen_date_from'];
                $beatmung_time = $row['oxygen_date_from'];
                $beatmung_date_bis = $row['oxygen_date_bis'];
                $beatmung_time = $row['oxygen_date_bis'];
                $beatmung_open_end = $row['oxygen_open_end'];
                if(!empty($row['beatmung']['oxygen_date_bis']) && !empty($row['beatmung']['oxygen_time_bis'])){
                    $oxygen_bis =  date_create_from_format('d.m.Y H:i', $row['beatmung']['oxygen_date_bis'] . ' ' .  $row['beatmung']['oxygen_time_bis'] );
                    $oxygen_bis = date_format($oxygen_bis,'Y-m-d H:i:s' );
                }

                $saved_data['oxygen_description'] = $beatmung_data['oxygen_description'];
                $info_text .= '<span><b>Sauerstoff, l:</b> ' . $beatmung_data['oxygen'] . '</span><br/>';
                //ISPC-2904,Elena,30.04.2021
                $info_text .= '<span><b>Kommentar:</b> ' . $beatmung_data['oxygen_description'] . '</span><br/>';
                //$chart_index = 1;
                $group = 1;
                $y = 1;
            }else{
                //$chart_index = 0;
                $y = 0;
                //ISPC-2904,Elena,30.04.2021
                $group = 0;
                $tableopen = false;
                foreach($beatmung_data as $b_k => $b_data ){

                    if($b_k == 'machine_opt'){
                        $entry = new Machine();
                        $machine = $entry->getTable()->find(intval($b_data));
                        //print_r($machine);
                        if(isset($machine->machine_name)){
                            //ISPC-2836,Elena,23.02.2021
                            $info_text .= '<span><b>Beatmungsmaschine:</b> ' . $machine->machine_name . '</span><br/>';
                        }

                    } elseif($b_k == 'form' || $b_k == 'oxygen' || $b_k == 'oxygen_description' ){
                        $info_text .= '';
                    }
                    elseif ($b_k == 'chosen'){

                        $entry = new Anordnung();
                        $anord = $entry->getTable()->find(intval($b_data));
                        //ISPC-2904,Elena,30.04.2021
                        $beatmung_params = json_decode($anord->parameters, true);
                        //ISPC-2836,Elena,23.02.2021
                        $info_text .= '<span><b>Anordnung:</b> ' . $anord->name . '</span><br/>';
                    }elseif(strpos($b_k, '_soll') > 1){
                        $info_text .= '';
                    }
                    elseif($b_k !== 'id' && $b_k !== 'machine_opt' && $b_k !== 'soll_data' && $b_k !== 'date' && $b_k !== 'time' && $b_k !== 'used_data'&& $b_k !== 'chosen' && $b_k !== 'oxygen_time_from' && $b_k !== 'oxygen_date_from' && $b_k !== 'oxygen_time_bis' && $b_k !== 'oxygen_date_bis' ){//ISPC-2904,Elena,30.04.2021
                        //ISPC-2836,Elena,23.02.2021
                        //ISPC-2904,Elena,30.04.2021
                        if(!$tableopen){
                            $info_text .= '<table><tr><th>Parameter</th><th>IST</th><th>SOLL</th></tr>';
                            $tableopen = true;
                        }
                        $saved_data[$b_k] = $b_data;


                        if(isset($beatmung_params[$b_k . '_alarm_higher']) &&  (isset($beatmung_params[$b_k . '_alarm_lower']))
                        ) {
                            $higher = $beatmung_params[$b_k . '_alarm_higher'];
                            $lower = $beatmung_params[$b_k . '_alarm_lower'];
                            if($higher < $lower){
                                $lower = $beatmung_params[$b_k . '_alarm_higher'];
                                $higher = $beatmung_params[$b_k . '_alarm_lower'];
                            }
                            if($b_data < $lower || $b_data > $higher){
                                $b_data = '<span style="color:red;">' . $b_data . '</span>';
                            }

                        }


                        $info_text .= '<tr><td>'. $b_k . '</td><td>' . $b_data . '</td>' ;
                        if(isset($beatmung_data[$b_k . '_soll'])){
                            //ISPC-2904,Elena,30.04.2021
                            $info_text .= '<td>' . $beatmung_data[$b_k . '_soll'] . '</td>';
                        }else{
                            $info_text .= '<td></td>';
                        }
                        $info_text .= '</tr>';//ISPC-2904,Elena,30.04.2021
                    }
                }
                //ISPC-2904,Elena,30.04.2021
                $info_text .= '</table>';
            }
            //ISPC-2836,Elena,23.02.2021
            $info_text .= '<span><b>Dokumentiert von:</b> ' . $row['username']  . '</span><br/>';
            $info_text .= '<span><b>Dokumentiert am: </b>' . date('d.m.Y H:i', strtotime($row['create_date'] )) . ' Uhr</span><br/>';


            $info_text .= '</div>';
            //ISPC-2904,Elena,30.04.2021
            if(isset($beatmung_data['form'] ) &&  $beatmung_data['form'] == 'oxygen'){
                $chartColor = $categories['Sauerstoffgabe']['color'];
                $high = null;

                if($beatmung_data['oxygen_open_end'] == 1 || empty($beatmung_data['oxygen_date_bis'])){
                    $chartColor = $gradientColor;
                    $high =  $period['js_max'];

                }elseif ($oxygen_bis !== ''){
                    $high =  $this->_chart_timestamp_datetime($oxygen_bis);
                }
//echo 'oxy';
                //ISPC-2904,Elena,30.04.2021
                $chart_series[ 1 ] ['data'][] = array(
                    'x' => 1,

                    'low' => $this->_chart_timestamp_datetime($row['datum']),
                    'low_st' => $row['datum'],
                    'high' => $high,
                    'high_st' => $oxygen_bis,
                    //'y' => $y ,
                'entry_id'=>$row['id'],
                'time_schedule' => 1,
                'marker' => array(
                    'enabled'=> true,
                    'symbol'=>'circle',
                    'lineWidth' => 1,
                    'radius' => 8,
                ),
                    'info'=> $info_text,
                    'color' => $chartColor,
                    //'saved_data' => json_encode($row),
                    'saved_data' => json_encode($saved_data)
                );

            }else{//ISPC-2904,Elena,30.04.2021

                $chart_series[ 0 ] ['data'][] = array(
                    'y' => $this->_chart_timestamp_datetime($row['datum']),
                    'x' => 0,//$this->_chart_timestamp_datetime($row['datum']) ,
                    'entry_id'=>$row['id'],
                    'time_schedule' => 1,
                    'marker' => array(
                        'enabled'=> true,
                        'symbol'=>'circle',
                        'lineWidth' => 1,
                        'radius' => 8,
                    ),
                    'info'=> $info_text,
                    //'saved_data' => json_encode($row),
                    'saved_data' =>  json_encode($saved_data)
            );

            }


            $chart_data['yaxis'][$chart_index]['title'] = false;
            $chart_data['yaxis'][$chart_index]['legend'] = false;
            $chart_data['yaxis'][$chart_index]['label'] = false;
            $chart_data['yaxis'][$chart_index]['tickPosition'] = 'inside';
            $chart_data['yaxis'][$chart_index]['lineWidth'] = '1';




            if(!empty( $chart_series[ 0]['data'])){
                $chart_data['series'][0]['data'] = $chart_series[ 0]['data'];
            }
            if(!empty( $chart_series[ 1]['data'])){
                $chart_data['series'][1]['data'] = $chart_series[ 1 ]['data'];
            }

            //$chart_data['series'][$chart_index]['data'] = $chart_series[ $chart_index ]['data'];
            $chart_index++;
            $y++;

        }
//ISPC-2904,Elena,30.04.2021
        $chart_data['series'][1]['borderRadius'] = '5';
        $chart_data['series'][1]['pointWidth'] = '10';
        $chart_data['series'][1]['pointPadding'] = '0';
        $chart_data['series'][1]['groupPadding'] = '0';


        $chart_data['chart_height'] = 150;
        $chart_data['hasData']  = 1;
        if (empty( $chart_data['series'][0]['data']) && empty( $chart_data['series'][1]['data'])) {
            $chart_data['hasData']  = 0;
            $chart_data['chart_height'] = 50;
        }
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('ventilation_info');

        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);

        $rez = json_encode($chart_data);

        return $rez;


    }

    
    public function fetch_time($ipid = 0, $period = array()){
        if(empty($ipid)){
            return;
        }
        
        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];
        
        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'],// 'min' => $start_js_date,
            'max' => $period['js_max'] //  'max' => $end_js_date,
        );
            
        $chart_series[ 0 ]['data'][] = array();
        $chart_data['chart_height'] = 40;
        $chart_data['series'] = $chart_series;
        
        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
        $chart_data['title']  = $this->__getChartTitle('time');
        
        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        
        
        return $rez;
    }

    
    
    public function fetch_ventilation_info($ipid = 0, $period = array()){
        if (empty($ipid)) {
            return;
        }

        $all_data = FormBlockVentilation::get_patients_chart($ipid, $period);

        $start_date_timestamp = $period['start_strtotime'];
        $end_date_timestamp = $period['end_strtotime'];

        $series_visible = array();
        $series_visible['modus'] = 'yes';
        $series_visible['f_tot'] = 'yes';
        $series_visible['vt'] = 'yes';
        $series_visible['mv'] = 'yes';
        $series_visible['peep'] = 'yes';
        $series_visible['pip'] = 'yes';
        $series_visible['o2_l_min'] = 'yes';
        $series_visible['i_e'] = 'yes';

        $def_yaxis = array(
            'type' => 'line',
            'gridLineWidth' => 1,
            "startOnTick" => true,
            'endOnTick' => true,

            "showFirstLabel" => true,
            "showLastLabel" => true,
            'gridLineDashStyle' => 'longdash',
            'lineColor' => '#ccd6eb',

            'lineWidth' => 1,
            "title" => array(
                "text" => null
            ),
            'labels' => array(
                'x' => - 2,
                'padding' => 2,
                'style' => array(
                    'color' => '#666666'
                )
            )
        );

        $chart_index = 0;

        $chart_series = array();
        $chart_data = array(
            'min' => $period['js_min'], // 'min' => $start_js_date,
            'max' => $period['js_max'] // 'max' => $end_js_date,
        );

        $all_point_values = array();
        foreach ($series_visible as $category => $visibility) {

            $chart_data['yaxis'][$chart_index] = $def_yaxis;
            $chart_data['yaxis'][$chart_index]['labels']['style']['color'] = '#000000';

            $chart_data['series'][$chart_index] = array(
                'type' => 'line',
                'lineWidth' => 2,
                'stickyTracking' => false,
                'name' => $this->view->translate('ventilation_' . $category),
                'color' => null,
                'marker' => array(
                    'enabled' => true
                ),
                'tooltip' => array(
                    "pointFormat" => "<b>{series.name}: {point.y:,.f}</b><br/>{point.x:%d.%m.%Y %H:%M}"
                )
            );
            foreach ($all_data as $k => $w) {
                if($w[$category] != "0.00"){
                    
                    $chart_series[$chart_index]['data'][] = array(
                        'x' => $this->_chart_timestamp_datetime($w['ventilation_info_date']),
                        'y' => floatval(str_ireplace(',', '.', $w[$category]))
                    );
                    $all_point_values[] = $w[$category];
                   $chart_data['series'][$chart_index]['data'] = $chart_series[$chart_index]['data'];
                }
            }
            $chart_index ++;
        }
        
        $chart_data['hasData']  = 1;
        if (empty($all_point_values)) {
            // nothing to show
            //return;
            $chart_data['hasData']  =0;
            $chart_data['chart_height'] = 50;
            unset($chart_data['series']);
        }

        $chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp, $end_date_timestamp);

        $chart_data['title'] = $this->__getChartTitle('ventilation_info');

        $chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
        
        $rez = json_encode($chart_data);
        return $rez;
    }
    
 
    private function __getChartTitle($chart_ident){
        
        $title_options  = array(
            'text'=>$this->view->translate('chart_block_'.$chart_ident),
            'align'=>'left',
            'verticalAlign'=>'top',
            'useHTML'=> true,
            'className'=>'vasile',
            'style' => array(
                'fontSize'=>'18px',
                'fontColor'=>'#111111',
                'fontWeight'=>'bold'
            )
        );
        
        return $title_options;
    }
    private function __getXplotlines($start_date_timestamp,$end_date_timestamp){
        
        $xplotlines = array();

//         $now =  strtotime(date('Y-m-d H:i:00'));
//         $now_datetime =  date('Y-m-d H:i:00');

        $now =   date('Y-m-d H:i:s', time());
        
        if($this->_use_altered_timezone){ // TIMEZONE- FOR DEV
            $timezone  = +2;  
            $now = gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I"))); 
        }
        $xplotlines[] = array(
            'color' => 'red',
            'style' => 'line',
            'width' => 1,
            'value' => $this->_chart_timestamp_datetime($now ),
            
//             'value' => $this->_chart_timestamp_datetime_utc(date('Y-m-d H:i:s', strtotime("now"))),
            
//             'value' => $this->_chart_timestamp_datetime(date('Y-m-d H:i:s', strtotime("now"))),
//             'value' => $this->_chart_timestamp(date('Y-m-d', strtotime("now")), date('g', strtotime("now")), 0),
//             'value' => $this->_chart_timestamp_datetime(date('Y-m-d H:i:s', strtotime("now"))),
//             'day' => date('d.m.Y',$now ),
//             'dayStr' => strtotime(date('d.m.Y H:i:s',$now )),
            'zIndex' => 3
        );

        for ($curent_day = $start_date_timestamp; $curent_day <= $end_date_timestamp; $curent_day = strtotime('+1 day', $curent_day)) {
            
            $xplotlines[] = array(
                'color' => '#DBDEE6',
                'style' => 'line',
                'width' => 1,
                'value' => $this->_chart_timestamp($curent_day, 0, 0),
//                 'day' => date('d.m.Y',$curent_day ),
//                 'dayStr' => strtotime(date('d.m.Y H:i:s',$curent_day )),
                'zIndex' => 3
            );
            for ($h = 0; $h< 24; $h++) {
                
                $xplotlines[] = array(
                    'color' => '#DBDEE6',
                    'style' => 'line',
                    'width' => 1,
                    'value' => $this->_chart_timestamp($curent_day, $h, 0),
                    'dayStrdd' => $start_date_timestamp,
                    'dayStrd' => $curent_day,
                    'dayStr' => date('d.m.Y H:i:s', ($this->_chart_timestamp($curent_day, $h, 0))/1000 ),
                    'zIndex' => 3
                );
            }
            
        }        


		// Plot lines for each hour - when one day view is selected
        // count days between - tick interval
        /* 
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        
        if(count($displayed_days) == 1){
            
            for ($h = 1; $h< 24; $h++) {
                
                $xplotlines[] = array(
                    'color' => '#DBDEE6',
                    'style' => 'line',
                    'width' => 1,
                    'value' => $this->_chart_timestamp($start_date_timestamp, $h, 0),
                    'dayStr' => date('d.m.Y H:i:s', ($this->_chart_timestamp($start_date_timestamp, $h, 0))/1000 ),
                    'zIndex' => 3
                );
            }
        }
         */
        
        //dd($xplotlines);
        return $xplotlines;
        
    }
    
    private function __getXtickInterval($start_date_timestamp,$end_date_timestamp){
        
        // count days between - tick interval
        $patient_master = new PatientMaster();
        $displayed_days = array();
        $displayed_days = $patient_master->getDaysInBetween(date('Y-m-d',$start_date_timestamp), date('Y-m-d',$end_date_timestamp));
        
        $XtickInterval = null;
        if(count($displayed_days) == 1 || count($displayed_days) == 2){ //ISPC-2661 pct.10 Carmen 17.09.2020
            $XtickInterval  = 3600 * 1000;
        }
        elseif(count($displayed_days) == 3){
            $XtickInterval = (3600 * 1000) * 6 ;
        } else{
            $XtickInterval = (3600 * 1000)* 12;
        }
        return $XtickInterval;
        
    }
   
    private  function __get_user_view_mode($data) {
        
        $saved_data = Doctrine_Query::create()
        ->select("*")
        ->from('UserChartsNavigation')
        ->where('ipid =?',$data['ipid'])
        ->andWhere('user =?',$data['user'])
        ->orderBy('create_date DESC')
        ->limit(1)
        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

        if(!empty($saved_data)){
            return $saved_data['view_mode'];
        } else{
            return false;
            
        }
        
    }
    private  function __save_user_view_mode($data) {
        
        if(empty($data)){
            return;
        }
        
        $save_navigation = UserChartsNavigationTable::getInstance()->findOrCreateOneBy(
            ['user', 'ipid'],
            [$data['user'], $data['ipid']],
            $data
            );
    }
    
    //ISPC-2516 Carmen 09.07.2020
    public function fetch_symptomsII($ipid = 0, $period = array()){
    	if(empty($ipid)){
    		return;
    	}

    	$client_symptoms_block = new FormBlockClientSymptoms();
    	$all_data = $client_symptoms_block->get_patient_symptpomatology_period($this->logininfo->clientid,$ipid, $period);    	
    	//print_r($all_data); exit;
    	$sympt_cat_view_chart_list = array();           //ISPC-2516 Lore 11.06.2020
    
    	$patient_column = array();
    	$categories = array();    	
    	$sp=1;
    	foreach($all_data as $k=>$sympt){
    		$patient_column[$sympt['entry_date']][$sympt['symptom_id']] =$sympt;
    		//ISPC-2516 Lore 11.06.2020
    		if(!empty($sympt['severity'])){
    			$sympt_cat_view_chart_list[] =  $sympt['symptom_id'];
    		}
    		if(array_search($sympt['symptom_name'], $categories) === false)
    		{
    			$categories[$sp] = $sympt['symptom_name'];
    			$sp++;
    		}
    		//.
    	}
    	
    	$start_date_timestamp = $period['start_strtotime'];
    	$end_date_timestamp = $period['end_strtotime'];
    	$chart_series = array();
    	$chart_data = array(
    			'min' => $period['js_min'],// 'min' => $start_js_date,
    			'max' => $period['js_max'] //  'max' => $end_js_date,
    	);
    
    	$def_yaxis = array(
    			'categories' => $categories,
    			'reversed' => true,
    			'showEmpty'=> false,
    			'type' => 'scatter',
    			'gridLineWidth' => 1,
    			"startOnTick" => true,
    			'endOnTick' => true,
    
    			"showFirstLabel" => true,
    			"showLastLabel" => true,
    			'gridLineDashStyle' => 'line',
    			'lineColor' => '#ccd6eb',
    			'lineWidth' => 1,
    			"title" => array(
    					"text" => null
    			),
    			'labels' => array(
    					'x' => -2,
    					'padding' => 2,
    					'style' => array(
    							'color' => '#444444',
    							'fontSize'=>'12px'
    					)
    			)
    	);

    	$char_index = 0;
    	foreach($patient_column as $column_date => $entries){
    		foreach($entries as $symptom_id => $set_symptom){
    			if(isset($entries[$symptom_id]['severity'])){
    
    				$info_text = "";
    				$info_text .= $entries[$symptom_id]['symptom_name'].' '.$entries[$symptom_id]['severity'];
    				$info_text .= !empty($entries[$symptom_id]['comment']) ? '<br/>Kommentar: '.$entries[$symptom_id]['comment'] : '';
    
    				$chart_series[ $char_index ]['data'][] = array(
    						'x' => $this->_chart_timestamp_datetime($entries[$symptom_id]['entry_date']),
    						'y' => floatval(array_search($entries[$symptom_id]['symptom_name'], $categories)),// stupid change !!! STUPID
    						'usershortname' => $this->all_users[$entries[$symptom_id]['create_user']]['shortname'], //ISPC-2661 pct.2 Carmen 10.09.2020
    						'color' => '#'.$entries[$symptom_id]['symptom_value_color'],
    						'marker' => array(
    								'enabled'=> true,
    								'symbol'=>'circle',
    								'lineColor' => null,
    								'lineWidth' => 0,
    								'radius' => 10,
    						),
    						'noTooltip'=>false,
    						'title'=>true,
    						'info'=>$info_text ,
    						'label'=>$entries[$symptom_id]['symptom_name'],
    						//                         'comment'=> !empty($entries[$symptom_id]['custom_description']) ? 'Kommentar: '.$entries[$symptom_id]['custom_description'] : '',
    						'value' => $entries[$symptom_id]['severity']
    				);
    				$char_index++;
    			}
    			else{
    				//ISPC-2516 Lore 11.06.2020
    				/*                     $chart_series[ $char_index ]['data'][] = array(
    				'x' => $this->_chart_timestamp_datetime($entries[$symptom_id]['entry_date']),
    				'y' => floatval($y2categoryes_mapping[$symptom_id]),// stupid change !!! STUPID
    				'color' => '#ffffff',
    				'noTooltip'=>true,
    				'marker' => array(
    				'enabled'=> false,
    				'symbol'=>'circle',
    				'lineColor' => null,
    				'lineWidth' => 0,
    				'radius' => 1,
    				),
    				);
    				$char_index++; */
    			}
    		}
    	}
    	$chart_data['hasData']  = 1;
    	//$chart_data['chart_height'] = 480;
    	//ISPC-2516 Lore 11.06.2020
    	$chart_data['chart_height'] = 80;
    	$chart_data['chart_height'] += (37*count($categories) > 480) ? 400 : (37*count($categories)) ;
    	//.
    
    	if (empty($chart_series)) {
    		$chart_data['hasData']  = 0;
    		//nothing to show - but stil show chart block  with title
    		$chart_data['chart_height'] = 50;
    	}
    
    
    	$chart_data['yaxis'] = $def_yaxis;
    	$chart_data['series'] = $chart_series;
    	$chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
    
    	$chart_data['title']  = $this->__getChartTitle('symptomatologyII');
    
    	$chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
    
    	$rez = json_encode($chart_data);
    
    
    	return $rez;
    }
    
    //ISPC-2661 pct.13 Carmen 08.09.2020
    public function createformadditionalrowAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    //ISPC-2661 Carmen
    	$af = new Application_Form_FormBlockAdditionalRow();
    	
    	switch ($parent_form) {
    		case 'custom_events':
    			$row = $af->create_form_additionalcustomevents_row(array(), $parent_form."[new_". uniqid(). "]");
    			break;
    		default:
    			$row = $af->create_form_additional_row(array(), $parent_form."[new_". uniqid(). "]");
    			break;    			
    	}
    	//$row = $af->create_form_additional_row(array(), $parent_form."[new_". uniqid(). "]");
    	//--
    	$row->clearDecorators()->addDecorators( array(
    			'FormElements',));
    
    	$this->getResponse()->setBody($row)->sendResponse();
    
    	exit;
    }
    //--
    
    //ISPC-2661 pct.13 Carmen 08.09.2020
    public function createformawakesleepingadditionalrowAction()
    {
    	if ( ! $this->getRequest()->isXmlHttpRequest()) {
    		throw new Exception('!isXmlHttpRequest', 0);
    	}
    	$this->_helper->layout->setLayout('layout_ajax');
    	$this->_helper->viewRenderer->setNoRender();
    
    	$parent_form = $this->getRequest()->getParam('parent_form');
    
    	$_block_name = $this->getRequest()->getParam('_block_name', null);
    
    	$af = new Application_Form_FormBlockAdditionalRow();
    	$row = $af->create_form_awake_sleeping_additional_row(array(), $parent_form."[new_". uniqid(). "]");
    	$row->clearDecorators()->addDecorators( array(
    			'FormElements',));
    
    	$this->getResponse()->setBody($row)->sendResponse();
    
    	exit;
    }
    //--
    
    //ISPC-2683 carmen 16.10.2020
    public function fetch_vigilance_awareness_events($ipid = 0, $period = array()){
    	if(empty($ipid)){
    		return;
    	}
    
    	$all_data = FormBlockLmuVisit::get_patients_chart($ipid, $period);
    	 
    
    	$start_date_timestamp = $period['start_strtotime'];
    	$end_date_timestamp = $period['end_strtotime'];
    
    	$chart_series = array();
    	$chart_data = array(
    			'min' => $period['js_min'],// 'min' => $start_js_date,
    			'max' => $period['js_max'] //  'max' => $end_js_date,
    	);
    
    	$bewusstsein_color = array();
    	$bewusstsein_color['wach']='#2bae2f';
    	$bewusstsein_color['somnolent']='#408cff';
    	$bewusstsein_color['soporös']='yellow';
    	$bewusstsein_color['komatös']='red';
    
    	//         $chart_series[0] = array(
    	//             'type' => 'scatter',
    	//             'name' => $this->view->translate('suckoff'),
    	//             'color' => "#000000"
    	//         );
    
    	$y =2;
    	foreach ($all_data as $row) {
    		$info_text = '';
    		$info_text .= $this->view->translate('awareness').': ' .$row['bewusstsein'] . "<br/>";
    		$info_text .= $this->view->translate('aw_orientation').': ';
    		if(!empty($row['ort'])){
    			$info_text .= $this->view->translate('Ort') . "|";
    		}
    		if(!empty($row['person'])){
    			$info_text .= $this->view->translate('Person') . "|";
    		}
    		if(!empty($row['situation'])){
    			$info_text .= $this->view->translate('Situation') . "|";
    		}
    		if(!empty($row['zeit'])){
    			$info_text .= $this->view->translate('Zeit') . "|";
    		}
    		if(!empty($row['keineorient'])){
    			$info_text .= $this->view->translate('Keineorient') . "<br/>";
    		}
    		
    		$radius = 8;
    		$color = $bewusstsein_color[ $row['bewusstsein']];
    		$chart_series[ 0 ]['data'][] = array(
    				'x' => $this->_chart_timestamp_datetime($row['vigilance_awareness_date']),
    				'y' => $y,
    				'usershortname' => $this->all_users[$row['create_user']]['shortname'], //ISPC-2661 pct.3 Carmen 10.09.2020
    				'color' => $color,
    				'marker' => array(
    						'enabled'=> true,
    						'lineColor' => '#000000',
    						'lineWidth' => 1,
    						'radius' => $radius
    				),
    				'title'=>true,
    				'entry_id'=>$row['id'] ,
    				'info' => $info_text,
    		);
    		//             $y++;
    	}
    
    	$chart_data['chart_height'] = 150;
    	$chart_data['hasData']  = 1;
    	if (empty($chart_series)) {
    		//nothing to show
    		$chart_data['hasData']  = 0;
    		$chart_data['chart_height'] = 50;
    	}
    
    	$chart_data['series'] = $chart_series;
    
    	$chart_data['xplotlines'] = $this->__getXplotlines($start_date_timestamp,$end_date_timestamp);
    
    	$chart_data['title']  = $this->__getChartTitle('vigilance_awareness_events');
    
    	$chart_data['XtickInterval']  = $this->__getXtickInterval($start_date_timestamp,$end_date_timestamp);
    
    	$rez = json_encode($chart_data);
    
    
    	return $rez;
    }
    //--
}
?>