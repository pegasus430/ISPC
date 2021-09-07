<?php
	// Maria:: Migration ISPC to CISPC 08.08.2020
	class MemberController extends Pms_Controller_Action {

// 		protected $actions_with_js_file = array(
// 				'sendemail2membershistoy',
		
// 		);
		
//  		protected $logininfo;// = null; 
		
		public function init()
		{			
			/* Initialize action controller here */
			array_push($this->actions_with_js_file, "sendemail2membershistoy");
// 			setlocale(LC_ALL, 'de_DE.utf-8');

			// ISPC-2609 Ancuta 12.09.2020 :: TODO-3668 14.12.2020
			 $this->user_print_jobs = 1;
			//
			
// 			$logininfo = new Zend_Session_Namespace('Login_Info');
// 			$this->clientid = $logininfo->clientid;
// 			$this->userid = $logininfo->userid;
// 			$this->usertype = $logininfo->usertype;
// 			$this->filepass = $logininfo->filepass;
// 			if(!$logininfo->clientid)
// 			{
// 				//redir to select client error
// 				$this->_redirect(APP_BASE . "error/noclient");
// 				exit;
// 			}
			
// 			$this->logininfo = $logininfo;
			
// 			//call template_init for selected action
// 			$this_action = $this->getRequest()->getActionName();	
// 			if(in_array($this_action, self::$actions_with_js_file)) {
// 				$this->template_init();
// 			}
			
		}

		
// 		private function template_init()
// 		{
// 			setlocale(LC_ALL, 'de_DE.UTF-8');
				
// 			if ( (isset($_REQUEST['pdf_print_template']) && $_REQUEST['pdf_print_template']=="pdf_print_template")
// 					|| (isset($_REQUEST['bypass_template']) && $_REQUEST['bypass_template']== "1" )
// 			)
// 			{
// 				//pdf print template
// 				$this->_helper->viewRenderer->setNoRender(true);
		
// 			}
// 			elseif ( ! $this->getRequest()->isXmlHttpRequest()) {
// 				/* ------------- Include js file of this action --------------------- */
// 				$actionName = $this->getRequest()->getActionName();
// 				$controllerName = $this->getRequest()->getControllerName();
					
// 				//sanitize $js_file_name ?
// 				$actionName = Pms_CommonData::normalizeString($actionName);
// 				$controllerName = Pms_CommonData::normalizeString($controllerName);
		
// 				//this is only on pc... so remember to put the ipad version
// 				$pc_js_file =  PUBLIC_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
					
// 				//$js_filename is for http ipad/pc
// 				$js_filename = RES_FILE_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";
					
// 				if (file_exists( $pc_js_file )) {
// 					$this->view->headScript()->appendFile($js_filename . "?_".(int)filemtime($pc_js_file));
// 				}
		
// 			}
			
// 		}
		
			
		public function memberslistAction()
		{			
			$clientid = $this->clientid;
			$userid = $this->userid;
			$previleges = new Pms_Acl_Assertion();
			//$this->_helper->viewRenderer('memberslist.html_2016.09.26');
			
			$return = $previleges->checkPrevilege('member', $this->userid, 'canview');
			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

            if($_REQUEST['flg'] == "no_members_error")
            {
                $this->view->error_message = $this->view->translate("No members were selected");
            }
            elseif($_REQUEST['flg'] == "no_export_method"){
                $this->view->error_message = $this->view->translate("No export method");
            }
			
            elseif($_REQUEST['flg'] == "email_sent"){
                $this->view->error_message = $this->view->translate("Email was sent");
            }
            
            
            //ISPC-2609 Ancuta 28.08.2020 + Changes on  07.09.2020
            //get printjobs - active or completed - for client, user and invoice type
            $allowed_invoice_name =  "members_letter";
            $this->view->allowed_invoice = "members_letter";
            $invoice_user_printjobs = PrintJobsBulkTable::_find_user_print_jobs($clientid,$this->userid,$this->getRequest()->getControllerName());
            
            $print_html = '<div class="print_jobs_div">';
            $print_html .= "<h3> ".$this->translate('print_job_table_headline')."</h3>";
            $print_html .= '<span id="clear_user_jobs" class="clear_user_jobs" data-user="'.$this->userid.'"  data-invoice_type="'.$allowed_invoice_name .'" data-client="'.$clientid.'"> '.$this->translate('Clear_all_prints')."</span>";
            $table_html = $this->view->tabulate($invoice_user_printjobs,array("class"=>"datatable",'id'=>'print_jobs_table','escaped'=>false));
            $print_html .= $table_html;
            $print_html .= '</div>';
            if(count($invoice_user_printjobs) > 1 && empty($_REQUEST['print'])){
                echo $print_html;
            }
            
            $this->view->show_print_jobs = $this->user_print_jobs;
            
            //---
            
            
            if( ! $this->getRequest()->isPost() ||  !isset($_POST['data_for_back'])) { //ISPC-2606, elena, 19.10.2020
	            
            	
            	$registered_as_donors_ids = array();
            	$registered_as_members_ids = array();
            	if ($_REQUEST['tab'] == 'donors') {
            		$registered_as_donors = MemberReferalTab::get_donors($this->clientid);
            		$registered_as_donors_ids = array_column($registered_as_donors , 'memberid');
            		
            	} else {
            		$registered_as_members = MemberReferalTab::get_members($this->clientid);
            		$registered_as_members_ids = array_column($registered_as_members , 'memberid');
            	}
	            
	            // get all members that are donors and donation_details
	         /*    $donations_history = MemberDonations::get_donations_history($clientid);
	            
	            $this->view->donation_history = $donations_history;
	            
	            $donors_ids = array();
	            if ( ! empty($donations_history)){
	            	$donors_ids = array_unique(array_column($donations_history, 'member'));
	            } */
            	
// 	            foreach($donations_history as $donation_id =>$donation_data){
// 	                if(!in_array($donation_data['member'],$donors_ids)){
// 	                    $donors_ids[] = $donation_data['member'];
// 	                }
// 	            }
	            
	            
	            $member = Doctrine_Query::create()
	            ->select('id, isdelete, inactive, type')
	            ->from('Member')
	            ->where('isdelete = 0')
	            ->andWhere('clientid = ?' , $this->clientid);
	            $patienidt_array = $member->fetchArray();
	            
	            $member_count_array['total'] = 0;
	            $member_count_array['active'] = 0;
	            $member_count_array['inactive'] = 0;
	            $member_count_array['donors'] = 0;
	            $member_count_array['member_family'] = 0;
	            
	            foreach($patienidt_array as $k=>$mdata){
	                //ISPC-2401 pct.14
	                if($mdata['isdelete'] == 0 && in_array($mdata['id'], $registered_as_members_ids)){
	                    
	                    //$member_count_array['total'] += 1;
	                    
	                    if($mdata['inactive'] == 0 ){
	                        $member_count_array['total'] += 1;
	                        
	                        $member_count_array['active'] += 1;
	                    }
	                    
	                    if($mdata['inactive'] == 1 ){
	                        $member_count_array['inactive'] += 1;
	                    }
	                    
	                    if($mdata['type'] == 'family' ){
	                        $member_count_array['total'] += 1;
	                        $member_count_array['member_family'] += 1;
	                    }
	                    
	                }
	                if($mdata['isdelete'] == 0 && in_array($mdata['id'], $registered_as_donors_ids)){
	                        $member_count_array['donors'] += 1;        
	                }    
	            }
	 
	            $this->view->member_count = $member_count_array;

            }
			
            
			if($this->getRequest()->isPost() && !isset($_POST['data_for_back'])) //ISPC-2606, elena, 19.10.2020
			{
			    $a_post = $_POST;
			    $excluded_sortby = array(
			        'membership_type',
			        'last_invoice_date',
			        'voluntary_referance',
			        'status',
			        'last_donation',
			        'membership_end_reasonid',
			        'membership_start_date',
			        'payment_method_description',
			    );
			    
                // generate only if members are selected			    
			    if((!empty($a_post['members_ids']) && count($a_post['members_ids'])) > 0 || $a_post['generate'] == "upcomming_birthdays" || $a_post['generate'] == "allyear_birthdays"){

			        $modules = new Modules(); // ISPC-2527 Andrei 29.05.2020
                    $client_modules = $modules->get_client_modules();
                    $this->view->client_modules = $client_modules;
            
                    $allCols = new MemberColumnslist();
			        $system_columns = $allCols->getAllColumns();
			         if (strlen($a_post['sortby']) 
			             && ! in_array( $a_post['sortby'],$excluded_sortby)
			             && ! empty($system_columns[$a_post['sortby']]['columnName'])
			             && ! in_array( $system_columns[$a_post['sortby']]['columnName'], $excluded_sortby))
			         {
			             //TODO-1261
			             //TODO-1263
			             //$sortby = $a_post['sortby'];  
			             $sortby = 'CONVERT(CONVERT('.$system_columns[$a_post['sortby']]['columnName'].' USING BINARY) USING utf8)';
			             
			         } else{
			             $sortby = "first_name";  
			         }
			         if(strlen($a_post['sortdir'])){
			             $sortdir = $a_post['sortdir'];  
			         } else{
			             $sortdir = "ASC";  
			         }
			         // get selected members details 
			        if($a_post['generate'] == "upcomming_birthdays"){
			            $a_post['members_ids'] = false;
			            $upcomming_birthdays = true;
			        } 
			        if($a_post['generate'] == "allyear_birthdays"){
			            $a_post['members_ids'] = false;
			            $allyear_birthdays = true;
			        } 
			        if($a_post['generate'] == 'export_xlsx' && isset($client_modules[233]) && $client_modules[233] == 1 ) //ISPC-2527 Andrei 29.05.2020
			        {
			            $has_member_family = true;
			        }
			        //ISPC-2401 1)     // ISPC-2527 Andrei 29.05.2020 added has_member_family parameter
			        if($a_post['generate'] == "upcomming_birthdays"){
                        //ISPC-2803,Elena,22.02.2021
			            $member_array = Member::getMultipleMemberDetails($a_post['members_ids'],$sortby,$sortdir,$upcomming_birthdays,$_REQUEST['tab'],true);
			        } elseif($a_post['generate'] == "allyear_birthdays"){
                        //ISPC-2803,Elena,22.02.2021
			            $member_array =  Member::getMultipleMemberDetails_allyear($a_post['members_ids'],$sortby,$sortdir,$allyear_birthdays,$_REQUEST['tab'],true);
			        } 
			        else{
			            $member_array = Member::getMultipleMemberDetails($a_post['members_ids'],$sortby,$sortdir,$upcomming_birthdays,$_REQUEST['tab'],$has_member_family); 
			        }
			        
			        
			        $all_member_array = Member::getMembersDetails($a_post['members_ids']);
// 			        print_r($a_post); exit;
			        
		        
			        $ordered_columns = array();                                                          
			        //$colum_order_array = UserTableSettings::user_saved_settings($this->userid, $a_post['page'], false, false, true);
			        
			        //ISPC-2468  Lore 15.11.2019
			        $tab = !empty($a_post['tab']) ? (int)$a_post['tab'] : 0; 
			        $colum_order_array = UserTableSettings::user_saved_settings($this->userid, $a_post['page'], false, $tab, true);   
			        
			        $not_viewable_cols = array();
			       	foreach($colum_order_array as $k => $v){
			       		$column_id_order[$v['column_id']] = $v['column_order'];
			       		if ( $v['visible'] == 'no' ){
			       			$not_viewable_cols[] = $v['column_id'];
			       		}	
			       	}      

			        
			        // get columns details
			        //ISPC - 2116 - add columns street, zip and city(7, 8 si 9)
			        $birth_day_columns = array("2","3","4","7","8","9");

			        
			        foreach( $system_columns as $col_id => $col_data){
			        	
			        	// @TODO icon_row
			        	if ($col_data['columnName'] == 'icon_row') continue;
			        	
			        	// @TODO in case of error must redo next if
			        	if(!in_array($col_id, $not_viewable_cols) && $col_data['columnName']!= 'icon_row'){  
			        		if (isset($column_id_order[$col_id]) && $column_id_order[$col_id]>0){
			                	$columns['viewable'][$column_id_order[$col_id]] = $col_data;
			                }else{
			                	$columns['viewable'][$col_id] = $col_data;
			                }
			            }
			            
			            if(in_array($col_id,$birth_day_columns)){
			                $columns['upcomming_birthdays'][$col_id] = $col_data;
			            }
			            
			            //if(in_array($col_id,$all_cols)){
    		             $columns['all'][$col_id] = $col_data;
			            //}
			        }
			        
	                //$columns['viewable'][$col_id] = $col_data;
			        ksort($columns['viewable']);
// 	                die(print_r($a_post));
			        if($a_post['generate'] != "0" ){
			            switch($a_post['generate'])
			            {
			                 // export
			                case 'export_xlsx':
			                    if ( isset($client_modules[233]) && $client_modules[233] == 1 ) // ISPC-2527 Andrei 29.05.2020
			                    {
			                        $extra_columns = array ('mf_title', 'mf_salutation', 'mf_salutationletter', 'mf_firstname', 'mf_lastname', 'mf_birthd', 'mf_gender');
			                        foreach ($extra_columns as $name) {
			                            $columns['all'][] = array('columnName' => $name);
			                        }
			                    }
			                    //dd($member_array);
			                    $this->export_xlsx($columns['all'],$member_array);
			                    break;
			                    
			                case 'export_xlsx_special':
			                    $this->export_xlsx_special($all_member_array);
			                    break;
			                    
			                case 'export_csv':
			                    $this->export_csv($columns['all'],$member_array);
			                    break;
			                    
                            // print			                    
			                case 'print_list_all_columns':
			                    $this->export_html($columns['all'],$member_array,false);
			                    break;
			                    			                    
			                case 'print_list_viewable_columns':
			                    $this->export_html($columns['viewable'],$member_array,false);
			                    break;
			                    
			                case 'print_letters':
			                   
			                    //$this->export_letters($a_post);          //ISPC-1236 Lore 12.04.2021  ---- test pt dev
			                    
			                    //TODO-3668 Ancuta 14.12.2020
			                    //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
			                    if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
			                        
			                        $this->export_letters($a_post);
			                        
			                    } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
			                        
			                        $a_post['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
			                        $a_post['members_ids'] = array_unique($a_post['members_ids']);
			                        $print_job_data = array();
			                        $print_job_data['clientid'] = $this->clientid;
			                        $print_job_data['user'] = $this->userid;
			                        $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
			                        $print_job_data['output_type'] = 'pdf';
			                        $print_job_data['status'] = 'active';
			                        $print_job_data['template_id'] = $a_post['template_id'];
			                        $print_job_data['invoice_type'] = null;
			                        $print_job_data['print_params'] = serialize($a_post);
			                        $print_job_data['print_function'] = 'export_letters';
			                        $print_job_data['print_controller'] = $this->getRequest()->getControllerName();
			                        
			                        foreach($a_post['members_ids'] as $k=>$m_id){
			                            $print_job_data['PrintJobsItems'][] = array(
			                                'clientid'=>$print_job_data['clientid'],
			                                'user'=>$print_job_data['user'],
			                                'item_id'=>$m_id,
			                                'item_type'=>"member_letter",
			                                'invoice_type'=>null,
			                                'status'=>"new"
			                            );
			                        }
			                        
			                        $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
			                        $print_id = $PrintJobsBulk_obj->id;
			                        
			                        if($print_id){
			                            $this->__StartPrintJobs();
			                        }
			                    }
			                    
			                    
			                    break;
			                    
			                case 'print_labels_3424':
			                    $this->export_pdf($member_array, 'Avery105x48', "member_stickers105x48.html");
			                    break;
			                    
			                case 'print_labels_3422':
			                    $this->export_pdf($member_array, 'Avery70x35', "member_stickers70x35.html");
			                    break;
			                    
		                    case 'upcomming_birthdays':
		                        $this->export_html($columns['upcomming_birthdays'],$member_array,true);
		                        break;

		                    case 'allyear_birthdays':
		                        $this->export_html($columns['upcomming_birthdays'],$member_array,true);
		                        break;
			                    
			                default:
			                    
			                    break;
			            }			            
    			        $this->_redirect(APP_BASE . "member/memberslist");
			        } else{
			            
    			        $this->_redirect(APP_BASE . "member/memberslist?flg=no_export_method");
    			        
			        }
			    } else {
			        $this->_redirect(APP_BASE . "member/memberslist?flg=no_members_error");
			    }
			    
			}
			
			// send from standby to deleted standby
			if($_REQUEST['setisdelete'] == 1)
			{
				
			    $pt = Doctrine_Core::getTable('Member')->find($_GET['id']);
			    $pt->isdelete = 1;
			    $pt->save();
			    
			    $this->_helper->viewRenderer->setNoRender();
			    header("Content-type: application/json; charset=UTF-8");	
			    echo json_encode(array('id'=> (int)$_GET['id'], 'deleted'=>true));
			    exit;
			}
				
			$allCols = new MemberColumnslist();
			$this->view->allColumns = $allCols->getAllColumns();
			
			//ispc 1739
			//get client icons
			$icons_client = new IconsClient();
			$client_icons = $icons_client->get_client_icons($clientid, false, 'icons_member');
			foreach($client_icons as $k => $v)
			{
				$client_icons[$v['id']]['visible'] = '1';
			}
			$this->view->client_icons = $client_icons;
			
			$templates_data = MemberLetterTemplates::get_all_letter_templates($clientid);
			if($templates_data){
				foreach($templates_data as $k => $tpl){
					$templates[$tpl['id']] = $tpl;
				}
			}
			$this->view->letter_templates = $templates;
				
			
		}

		public function fetchonememberAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			$clientid = $this->clientid;
			
			
			if($this->getRequest()->isPost()){
				$member = Member::getMemberDetails((int)$_POST['id']);				
				$marr = array_values($member);
				$member = $marr[0];
				
				if ($member['clientid'] != $clientid) {
					//loghed in clietid is not the owner of this member
					exit;
				}
				
				
				if($member['birthd'] != "0000-00-00" && Zend_Date::isdate($member['birthd'], "YYYY-mm-dd")){
					$member['birthd'] = date("d.m.Y",strtotime($member['birthd']));
				}else{
					$member['birthd'] = "";
				}
				if(isset($member['vw_id']) && $member['vw_id'] >0){
					$vw = Voluntaryworkers::getClientsVoluntaryworkersSort($clientid,array($member['vw_id']));
					$vwarr = array_values($vw);
					$vw = $vwarr[0];
					if (isset($vw['last_name']))$member['vw_id'] = $vw['last_name'] . ", ". $vw['first_name'];
				}else{
					$member['vw_id'] = '';
				}
				if($member['type'] == 'family')
			   	{
			   		if($family_child = MemberFamily::getMemberFamilyDetails((int)$_POST['id'])){
			   			$family_child = array_values($family_child) ;
			   			$family_child = $family_child[0] ;
			   			if($family_child['birthd'] != "0000-00-00" && Zend_Date::isdate($family_child['birthd'], "YYYY-mm-dd")){
			   				$family_child['birthd'] = date('d.m.Y',strtotime($family_child['birthd']));
			   			} else{
			   				$family_child['birthd'] = "";
			   			}
			   		}
			   		if(count($family_child)>0){
			   			$member['family_child'] = $family_child;
			   		}
			   	}
			   	
			   	$client_memberships = Memberships::get_memberships($clientid);
			   	foreach($client_memberships as $km =>$vm){
			   		$memberships_data[$vm['id']]  = 	$vm;
			   		$memberships_data[$vm['id']]['membership'] =$vm['membership'];
			   	}
			   	 
			   	$membership_history_array = Member2Memberships::get_memberships_history($clientid,(int)$_POST['id']);
			   	if(!empty($membership_history_array)){
			   		$membership_history = "<table class='membership_history'>
			   				<thead>
			   				<td>".$this->view->translate('Membership')."</td>
			   				<td>".$this->view->translate('th_membership_price')."</td>
			   				<td>".$this->view->translate('start_date')."</td>
			   				<td>".$this->view->translate('end_date')."</td>
			   				</thead>
			   				<tbody>";
			   		foreach($membership_history_array as $k=>$md){
			   			$membership_history  .= "<tr><td>".$memberships_data[$md['membership']]['membership']
			   			. "</td><td>" . $md['membership_price']
			   			. "</td><td>" . date("d.m.Y", strtotime($md['start_date']));
			   			
			   			if ( $md['end_date'] != '' && $md['end_date']!="0000-00-00" && $md['end_date']!='0000-00-00 00:00:00'){
			   				$membership_history  .= "</td><td>" . date("d.m.Y", strtotime($md['end_date']));
			   			}else{
			   				$membership_history  .= "</td><td>&nbsp;";
			   			}
			   			 
			   			$membership_history  .= "</td></tr>";
			   			/*
			   			$membership_history[$md['member']]['membership'] = $memberships_data[$md['membership']]['membership'];
			   			$membership_history[$md['member']]['membership_price'] = $md['membership_price'];
			   			$membership_history[$md['member']]['start_date'] = $md['start_date'];
			   			$membership_history[$md['member']]['start_date'] = $md['end_date'];
			   			*/
			   		}
			   		$membership_history .="</tbody></table>";
			   	}
			   	
			 
			   	/*			   	
			   	
			   	print_r($memberships_data);
			   	print_r($membership_history_array);
			   	print_r($membership_history);
			   	*/
			   	$member['membership_history'] = $membership_history;
				$this->_helper->json($member);
			}
			exit;
		}
		
		public function fetchlistAction()
		{ 
		    $clientid = $this->clientid;
		    $userid = $this->userid;

		    $this->_helper->layout->setLayout('layout_ajax');

		    /* ----------------- set limits, pages, order, sort ----------------------- */
		    $standby_page = false;
		    
		    
		    
		    if(!empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']))
		    {
		        $custom_limit = $_REQUEST['limit'];
		    }
		    else
		    {
		        $custom_limit = 20;
		    }
		    
		    

		    $page_results_array =  UserPageResults :: get_page_result($userid,$clientid,"members");
		    
		    if(!empty($page_results_array ))
		    {
		        foreach($page_results_array as $k=>$res_data){
		            $custom_limit = $res_data['results'];
		        }
		    }
		    
		    
		    
		    $limit = $custom_limit ;
		    $this->view->limit = $limit;
		    
		    if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
		    {
		        $current_page = $_REQUEST['page'];
		    }
		    else
		    {
		        $current_page = 1;
		    }
		    
		    if($_REQUEST['sort'] == 'desc')
		    {
		        $sort = 'desc';
		    }
		    else
		    {
		        $sort = 'asc';
		    }
		    
		    /* --------------------  set default status -------------------------------- */
		    if(strlen($_REQUEST['f_status']) > 0)
		    {
		        $reqestedTab = $_REQUEST['f_status'];
		    }
		    else
		    {
		        $_REQUEST['f_status'] = "active";
		        $reqestedTab = "active";
		    }
 
		    /* ##################################################################################### */
		    //columns
		    $userCols = new MemberColumns2users();
		    
		    if(strlen($_REQUEST['f_status']) > 0)
		    {
		        $reqestedTab = $_REQUEST['f_status'];
		    }
		    else
		    {
		        $reqestedTab = "active";
		    }
		    
		    $tabs = array("active" => "1", "inactive" => "2", "donors" => "3");
		    
		    $tabColumns = new MemberColumnslist();
		    $tabCols = $tabColumns->getColumns($tabs[$reqestedTab]);
		    $tabsColumnsSource = new MemberColumns2tabs();
		    $allTabsColumns = $tabsColumnsSource->getTabsColumns();
	
		    foreach($tabCols as $kcol => $valcol)
		    {
		        $finalCol[] = $valcol['id'];
		    }
		    $this->view->tabColumns = implode(",", $finalCol);

		    
		    if(count($_REQUEST['columns']) != "0" && $_REQUEST['savecols'] == "1")
		    {
		        if(empty($_REQUEST['columns'])){
    		        $_REQUEST['columns'][] = "9999999";
		        }
		        $selectedColumns = implode(",", $_REQUEST['columns']);
		        $user_columns2tab = $_REQUEST['columns'];
		    }
		    else
		    {
		        //get selected cols from db!!!
		        $userSavedCols = $userCols->getUserColumns($this->userid, $tabs[$reqestedTab]);
		        if(count($userSavedCols) == "0")
		        { //get defaults if user has no saved data
		            $defaultCols = $userCols->getDefaultColumns(false, $tabs[$reqestedTab]);
		            	
		            foreach($defaultCols as $col)
		            {
		                $defCols[] = $col['colid'];
		            }
		            
		            if(empty($defCols)){
    		            $defCols[] = "9999999";
		            }
		            
		    
		            $selectedColumns = implode(",", $defCols);
		            $user_columns2tab = $defCols;
		          
		        }
		        else
		        {
		            if(empty($userSavedCols)){
    		            $userSavedCols[] = "9999999";
		            }
		            $selectedColumns = implode(",", $userSavedCols);
		            $user_columns2tab = $userSavedCols;
		        }
		    }
		    	
		    // !!! THIS ARRAY IS NOT USED IT IS ONLY FOR INFORMATION !!!
		    $overall_columns = array(
		        '1' => 'title',
		        '2' => 'lastname',
		        '3' => 'firstname',
		        '4' => 'dateofbirth',
		        '5' => 'salutation_letter',
		        '6' => 'gender',
		        '7' => 'street',
		        '8' => 'zip',
		        '9' => 'city',
		        ''    => 'country',
		        '10' => 'phone',
		        '11' => 'mobile',
		        '12' => 'email',
		        '13' => 'image',
		        '14' => 'membership_type',
		        '15' => 'last_invoice_date',
		        '16' => 'member_company',
		        '17' => 'voluntary_referance',
		        '18' => 'status',
		        '19' => 'profession',
		        '20' => 'last_donation',
		        
		        '24' => 'membership_end_reasonid',
		        '25' => 'bank_name',
		        '26' => 'iban',
		        '27' => 'payment_method_description',
		        '28' => 'membership_start_date',
		        
		    );
		    $this->view->selectedColumns = $selectedColumns;
		    
		    /* ##################################################################################### */
		    // COLUMNS DETAILS -  END
		    /* ##################################################################################### */
		    // get letter templates 
		    
		    $templates_data = MemberLetterTemplates::get_all_letter_templates($clientid);

		    if($templates_data){
    		    foreach($templates_data as $k => $tpl){
    		        $templates[$tpl['id']] = $tpl;
    		    }
		    }
		    $this->view->letter_templates = $templates;
		    
		    // get memberships
		    $client_memberships = Memberships::get_memberships($clientid);
		    $this->view->memberships = $client_memberships;
		    foreach($client_memberships as $km =>$vm){
		        $memberships_data[$vm['id']]  = 	$vm;	    
		        $memberships_data[$vm['id']]['membership'] =$vm['membership']; 	    
		    }
		    	
	        $membership_history_array = Member2Memberships::get_memberships_history($clientid,false,$current = true);
	        if(!empty($membership_history_array)){
	            foreach($membership_history_array as $k=>$md){
	                $membership_history[$md['member']]['membership'] = $memberships_data[$md['membership']]['membership'];
	            }
	        }
		    $this->view->membership_history = $membership_history;
		    
		    
		    
		    // get statuses
		    $mstatuses= MemberStatuses::get_client_member_statuses($clientid);
		    $this->view->statuses = $mstatuses;
	   

		    // ################################################
		    // get associated clients of current clientid START
		    // ###############################################
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		    if($connected_client){
		        $vw_clientid = $connected_client;
		    } else{
		        $vw_clientid = $logininfo->clientid;
		    }
		     
		    // ################################################
		    // get associated clients of current clientid END
		    // ###############################################
		     
		    $fdoc = new Voluntaryworkers();
// 		    $docarray = $fdoc->getClientsVoluntaryworkers($vw_clientid);
		    $docarray = $fdoc->getClientsVoluntaryworkersSort($vw_clientid,false,$sort);
		     
		    foreach($docarray as $k=>$vw){
		        $vw_details[$vw['id']] = $vw;
		        $vw_name_details[$vw['id']] = $vw['last_name'].', '.$vw['first_name'];
		        $vw_ids[] = $vw['id'];
		    }
		     
		     
		    // get all members that are donors and donation_details
		    $donations_history = MemberDonations::get_donations_history($clientid);
		    
		    $this->view->donation_history = $donations_history;
		    
		    $donors_ids_str .= '"0",';
		    $donors_ids = array();
		    foreach($donations_history as $donation_id =>$donation_data){
		        if(!in_array($donation_data['member'],$donors_ids)){
                    $donors_ids[] = $donation_data['member'];
    		        $donors_ids_str .= '"'.$donation_data['member'].'",';
		        }
		        $donation_details[$donation_data['member']][] = $donation_data;
		    }
		    
		    if(empty($donors_ids)){
		        $donors_ids[] = "9999999999";
		    }
		    
		    /* --------------------  build filters array based on form input ------------------ */
		    $filters = array();
		    
		    $filters_params = array();
		    $filters_params['member'] =  array();
		    
		    switch($_REQUEST['f_status'])
		    {
		    
		        case 'active':
		            $filters['member'] = 'isdelete = 0 AND inactive = 0';
		            break;

		        case 'inactive':
		            $filters['member'] = 'isdelete = 0 AND inactive = 1';
		            break;

		        case 'donors':
		            $filters['member'] = 'isdelete = 0 AND id IN (' . substr($donors_ids_str, 0, -1) . ') ';
		            break;
		            
		        default:
		            $filters['member'] = '1';
		            break;
		    }
		    
		    
		    if($_REQUEST['f_dob_start'] && $_REQUEST['f_dob_end'])
		    {
		        $filters['member'] .= ' AND (year(birthd) BETWEEN "' . $_REQUEST['f_dob_start'] . '" AND "' . $_REQUEST['f_dob_end'] . '")';
		    }

		   
		    //trim(mb_convert_case(mb_strtolower($member_data['first_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"))
		    if(!empty($_REQUEST['f_keyword']))
		    {
		    	//ISPC 1739
// 		    	$regexp = addslashes(trim($_REQUEST['f_keyword']));
// 		    	$regexp = trim(mb_convert_case(mb_strtolower($regexp, 'UTF-8'), MB_CASE_TITLE, "UTF-8"));
		    	//@claudiu 12.2017, changed Pms_CommonData::value_patternation
		    	$regexp = trim($_REQUEST['f_keyword']);
		    	Pms_CommonData::value_patternation($regexp);
		    	
// 		    	$filters['member'] .= ' AND ( lower(CONCAT(
		    	$filters['member'] .= ' AND ( CONCAT_WS(" ",
		    								member_company,
		    								title ,
											salutation_letter,
											salutation,
											first_name,
											last_name,
											birthd,
											phone,
											private_phone,
											mobile,
											email,
											website,
											fax,
											street1,
											street2,
											zip,
											city,
											country,
											profession,
											remarks,
											memos,
											comments ) REGEXP ?)';
		    	$filters_params['member'][] = $regexp;
		    	/*
		    	 * original query before ispc-1739
		        $filters['member'] .= ' AND (CONCAT(last_name,first_name,title,street1,zip,city,phone,email) LIKE "%' . addslashes($_REQUEST['f_keyword']) . '%")';
		   		*/
		    }
		    
		    /* --------- get initial ipids and apply patient master filters ---------------------------- */
		    $member = Doctrine_Query::create()
		    ->select('*')
		    ->from('Member')
		    ->where('isdelete = 0')
		    ->andWhere($filters['member'], $filters_params['member'])
		    ->andWhere('clientid = ?' , $this->clientid);
		    $patienidt_array = $member->fetchArray();
		    
		    if(empty($patienidt_array)){
    		    $patienidt_array[9999999] = "xx";
		    }
		    
		    
		    $member_filter_ipids[] = '999999999';
		    foreach($patienidt_array as $k_pat_idtarray => $v_pat_idtarray)
		    {
		        $member_filter_ipids[] = $v_pat_idtarray['id'];
		        $member_filter_details[$v_pat_idtarray['id']] = $v_pat_idtarray;
		    }
	 
		    $pat_ipidsarr = $member_filter_ipids;
		    
		    $pat_ipidsarr = array_values(array_unique($pat_ipidsarr));
		   
 
		    
		    foreach($patienidt_array as $k_pat_details => $v_pat_details)
		    {
		        if(in_array($v_pat_details['id'], $pat_ipidsarr))
		        {
		            $patienidtarray[] = $v_pat_details;
		        }
		    }
		    if(empty($patienidtarray)){
    		    $patienidtarray[9999999] = "xx";
		    }

		    
		    foreach($patienidtarray as $memberid)
		    {
		        $memberipidsarr[] = $memberid['id'];
		    }
		    
		    
		    foreach($patienidtarray as $memberid)
		    {
		        $memberipidsfinal[] = $memberid['id'];
		    }

		    $storno_invoices_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('MembersInvoices')
		    ->where('client = "' . $clientid . '"  ')
		    ->andwhereIn('member', $memberipidsfinal)
		    ->andWhere('storno = 1')
		    ->andWhere('isdelete = 0');
		    $storno_invoices_array = $storno_invoices_q->fetchArray();
		    
		    $storno_ids_str = '"XXXXXX",';
		    foreach($storno_invoices_array as $k => $st)
		    {
		        $storno_ids[] = $st['record_id'];
		    }
		    
		    if(empty($storno_ids))
		    {
		        $storno_ids[] = "XXXXXXX";
		    }
		    
		    
		    $invoices = Doctrine_Query::create()
		    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
		    ->from('MembersInvoices')
		    ->where("client='" . $clientid . "'" )
		    ->andwhereIn('member', $memberipidsfinal)
		    ->andwhereNotIn('id', $storno_ids)
		    ->andWhere('isdelete = 0')
		    ->andWhere('storno = 0')
		    ->orderBy('completed_date_sort ASC');
		    $invoices_array = $invoices->fetchArray();
            
            foreach($invoices_array as $k => $invoice){
		          $invoices_data[$invoice['member']]['invoice_date'][] = $invoice['completed_date_sort']; 
		    }

		    $no_patients = sizeof($memberipidsfinal);
		    $no_pages = ceil($no_patients / $limit);
		    
		    switch($_REQUEST['ord'])
		    {
		    
		        case 'title':
		            $orderby = 'title ' . $sort;
		            break;
		    
		        case 'member_company':
		            $orderby = 'member_company ' . $sort;
		            break;
		    
		        case 'first_name':
		            $orderby = 'first_name ' . $sort;
		            break;
		    
		        case 'last_name':
		            $orderby = 'last_name ' . $sort;
		            break;
		    
		        case 'birthd':
		            $orderby = 'birthd ' . $sort;
		            break;
		    
		        case 'salutation_letter':
		            $orderby = 'salutation_letter ' . $sort;
		            break;
		    
		        case 'gender':
		            $orderby = 'gender ' . $sort;
		            break;
		    
		        case 'phone':
		            $orderby = 'phone ' . $sort;
		            break;
		            
		        case 'mobile':
		            $orderby = 'mobile ' . $sort;
		            break;
		    
		        case 'email':
		            $orderby = 'email ' . $sort;
		            break;
		    
		        case 'street':
		            $orderby = 'street1 ' . $sort;
		            break;
		            
		        case 'zip':
		            $orderby = 'zip ' . $sort;
		            break;
		            
		        case 'city':
		            $orderby = 'city ' . $sort;
		            break;
		    
		            
		        default:
		            $orderby = 'last_name ' . $sort;
		            break;
		    }
		    
		    $member = Doctrine_Query::create()
		    ->select("*")
		    ->from('Member')
		    ->whereIn("id", $memberipidsfinal)
		    ->andWhere('clientid = ' . $this->clientid);
		    $member->orderby($orderby);
		    if($limit != "-1"){
	       	    $member->offset(($current_page - 1) * $limit);
    		    $member->limit($limit);
		    }
		    $memberlimit = $member->fetchArray();
		  // print_r( $memberlimit); exit;
	    
		    $last_donation = array();
		    foreach($memberlimit as $key => $member_item)
		    {
		        
		        if($member_item['gender'] == "1"){
		            $memberlimit[$key]['gender'] = "male";
		        } 
		        elseif($member_item['gender'] == "2"){
		            $memberlimit[$key]['gender'] = "female";
		        } 
		        elseif($member_item['gender'] == "3"){
		            $memberlimit[$key]['gender'] = "divers"; //ISPC-2442 @Lore   09.10.2019
		        } 
		        else{
		            $memberlimit[$key]['gender'] = "gender_not_documented";
		        }
		        
	            $memberlimit[$key]['street'] = $member_item['street1'];
	            
	            $memberlimit[$key]['membership_type'] = $membership_history[$member_item['id']]['membership'];

	            if(!empty($invoices_data[$member_item['id']]['invoice_date'])){
    	            $memberlimit[$key]['last_invoice_date'] = date('d.m.Y', strtotime(end($invoices_data[$member_item['id']]['invoice_date'])));
	            } else {
    	            $memberlimit[$key]['last_invoice_date'] = "";
	            }
		        if($member_item['vw_id']!="0"){
		        	
    	            $memberlimit[$key]['voluntary_referance'] = $vw_name_details[$member_item['vw_id']];
		        } else{
    	            $memberlimit[$key]['voluntary_referance'] = "";
		        }
		        
		        if($member_item['status']!="0"){
		        	
    	            $memberlimit[$key]['status'] = $mstatuses[$member_item['status']]['status'];
		        } else{
    	            $memberlimit[$key]['status'] = "";
		        }
		        
		        if(in_array('20', $user_columns2tab))
		        {
    		        if(!empty($donation_details[$member_item['id']])){
    		            $last_donation = end($donation_details[$member_item['id']]);
        	            $memberlimit[$key]['last_donation'] = $last_donation['donation_date'].' ('.$last_donation['amount'].' &euro;)';
        	            $memberlimit[$key]['last_donation_date'] = $last_donation['donation_date'];
    		        } else{
        	            $memberlimit[$key]['last_donation'] = "-";
        	            $memberlimit[$key]['last_donation_date'] ="";
    		        }
		        }
		    }
		    if($_REQUEST['sort'] == 'desc')
		    {
		    	$sort_n= SORT_DESC;
		    }
		    else
		    {
		    	$sort_n = SORT_ASC;
		    }
		 
		    //========= sorting columns===============
		    if($_REQUEST['ord'] == "last_invoice_date")
		    {
			    $memberlimit = $this->array_sort($memberlimit, 'last_invoice_date', $sort_n);
		    } 
		    elseif($_REQUEST['ord'] == "voluntary_referance")
		    {
			    $memberlimit = $this->array_sort($memberlimit, 'voluntary_referance', $sort_n);
		    } 
		    elseif($_REQUEST['ord'] == "status")
		    {
			  
			  $memberlimit = $this->array_sort($memberlimit, 'status', $sort_n);
		    }
		    elseif($_REQUEST['ord'] == "profession")
		    {
		    	$memberlimit = $this->array_sort($memberlimit, 'profession', $sort_n);
		    }
		    elseif($_REQUEST['ord'] == "membership_type")
		    {
		    	$memberlimit = $this->array_sort($memberlimit, 'membership_type', $sort_n);
		    }
		    elseif($_REQUEST['ord'] == "last_donation_date")
		    {
		    	$memberlimit = $this->array_sort($memberlimit, 'last_donation_date', $sort_n);
		    }
		    //=============================================
		   
        // print_r($memberlimit);
		  
		    
		    $this->view->standby_page = $standby_page;
		    $this->view->memberlist = $memberlimit;
		    $this->view->current_page = $current_page;
		    $this->view->no_pages = $no_pages;
		    $this->view->no_patients = $no_patients;
		    $this->view->orderby = $_REQUEST['ord'];
		    $this->view->sort = $_REQUEST['sort'];

		    if(count($_REQUEST['columns']) != "0" && $_REQUEST['savecols'] == "1")
		    { //save
    		    foreach($allTabsColumns[$tabs[$reqestedTab]] as $k => $cid)
    		    {
    		        $column_array[] = $cid;
    		    }
    		    if(!empty($column_array))
    		    {
    		        $drop = Doctrine_Query::create()
    		        ->delete("*")
    		        ->from('MemberColumns2users')
    		        ->where('user_id ="' . $this->userid . '"')
    		        ->andWhereIn("c2t_id", $column_array);
    		        $delete = $drop->execute();
    		    }
    		    
    		    foreach($_REQUEST['columns'] as $columnid)
    		    {
    		        if($allTabsColumns[$tabs[$reqestedTab]][$columnid] != "0")
    		        {
    		            $records[] = array(
    		                "c2t_id" => $allTabsColumns[$tabs[$reqestedTab]][$columnid],
    		                "user_id" => $this->userid
    		            );
    		        }
    		    }
    		    
    		    if(!empty($records))
    		    {
    		        $collection = new Doctrine_Collection('MemberColumns2users');
    		        $collection->fromArray($records);
    		        $collection->save();
    		    }
		    }
		}

		public function fetchlistdatatablesAction()
		{
			
			
			$clientid = $this->clientid;
			$userid = $this->userid;
		
			$this->_helper->layout->setLayout('layout_ajax');
			$this->_helper->viewRenderer->setNoRender(true);
			
			/* ----------------- set limits, pages, order, sort ----------------------- */
			$standby_page = false;
			
			$limit = $_REQUEST['length'];
			$offset = $_REQUEST['start'];
			$search_value = $_REQUEST['search']['value'];
			if(!empty($_REQUEST['order'][0]['column']) && (int)$_REQUEST['order'][0]['column'] > 0 ){
				$order_column = (int)$_REQUEST['order'][0]['column'];
			} else{
				$order_column = "2"; // last_name
			}
			$order_dir = htmlspecialchars($_REQUEST['order'][0]['dir']);
			if( $order_dir!='asc' && $order_dir!='desc'){
				$order_dir = 'asc';
			}
			$order_by_str = false;
			/*
			
			$page_results_array =  UserPageResults :: get_page_result($userid,$clientid,"members");
			if(!empty($page_results_array ))
			{
				foreach($page_results_array as $k=>$res_data){
					$custom_limit = $res_data['results'];
				}
			}
			$limit = $custom_limit ;
		
			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
			{
				$current_page = $_REQUEST['page'];
			}
			else
			{
				$current_page = 1;
			}
			*/
			
		
			/* --------------------  set default status -------------------------------- */
			/*
			if(strlen($_REQUEST['f_status']) > 0)
			{
				$reqestedTab = $_REQUEST['f_status'];
			}
			else
			{
				$_REQUEST['f_status'] = "active";
				$reqestedTab = "active";
			}
			*/
		
			/* ##################################################################################### */
			//columns
			/*
			$userCols = new MemberColumns2users();
		
			if(strlen($_REQUEST['f_status']) > 0)
			{
				$reqestedTab = $_REQUEST['f_status'];
			}
			else
			{
				$reqestedTab = "active";
			}
		
			$tabs = array("active" => "1", "inactive" => "2", "donors" => "3");
			*/
			//get all the columns
			$tabColumns = new MemberColumnslist();
			$tabCols = $tabColumns->getAllColumns();
			$tabCols = array_column($tabCols, "columnName");
			//set the index of our columns from 1 ... because we add [0] in the html the selectbox
			array_unshift($tabCols,"");
			unset($tabCols[0]);
			
			$manual_order_column = "";
			$manual_sortable_columns = array(
				'last_invoice_date',
				'voluntary_referance',
				'status',
				'profession',
				'membership_type',
				'last_donation',
			    'membership_end_reasonid',
			    
			    'payment_method_description',
			    'membership_start_date',
				'member_type', //ISPC-2795 Dragos 15.01.2021
			);
			$columns_array = $tabCols;
			/*
			if(!in_array($columns_array[$order_column], $manual_sortable_columns)){
				$order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
			}
			*/
			
			if ( isset($_POST['columns'][$order_column]['data']) 
					&& !in_array($_POST['columns'][$order_column]['data'], $manual_sortable_columns))
			{
				
			    //TODO-1261
			    $order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($_POST['columns'][$order_column]['data'])).' USING BINARY) USING utf8) '.$order_dir;
// 				$order_by_str = addslashes(htmlspecialchars($_POST['columns'][$order_column]['data']).' '.$order_dir.' ');
				
			}else{
				$manual_order_column = addslashes(htmlspecialchars($_POST['columns'][$order_column]['data']));
				//$columns_array[$order_column] = htmlspecialchars($_POST['columns'][$order_column]['data']);
			}
			
			/* ##################################################################################### */
			// COLUMNS DETAILS -  END
			/* ##################################################################################### */
						
			// get memberships
			$client_memberships = Memberships::get_memberships($clientid);
			$this->view->memberships = $client_memberships;
			foreach($client_memberships as $km =>$vm){
				$memberships_data[$vm['id']]  = 	$vm;
				$memberships_data[$vm['id']]['membership'] =$vm['membership'];
			}
			 
			
			if ($_REQUEST['tab'] == 'inactive') {
			    $membership_history_array = Member2Memberships::get_memberships_history($clientid, false, $current = false);
			} else {
			    $membership_history_array = Member2Memberships::get_memberships_history($clientid, false, $current = true);
			}						
			$membership_history = array();
			
			//ISPC-2152
			$ReasonOfMembershipEnd_list =  array();
			if ( ! empty($membership_history_array)) {
			    $ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid);
			}
			
			if ( ! empty($membership_history_array)) {
				foreach($membership_history_array as $k=>$md){
					$membership_history[$md['member']]['membership'] = $memberships_data[$md['membership']]['membership'];
					
					if ($_REQUEST['tab'] == 'inactive') {
    					$membership_history[$md['member']]['membership'] .= "<br/>" . date('d.m.Y', strtotime($md['start_date']));
    					if ( ! empty($md['end_date']) && $md['end_date'] != '0000-00-00 00:00:00') {
    					   $membership_history[$md['member']]['membership'] .= "-" . date('d.m.Y', strtotime($md['end_date']));
    					}
					}
					
					$membership_history[$md['member']]['membership_end_reasonid'] = $md['end_reasonid'] > 0 ? $ReasonOfMembershipEnd_list[$md['end_reasonid']]['description'] : '';
					
					$membership_history[$md['member']]['membership_start_date'] = date('d.m.Y', strtotime($md['start_date']));
				}
			}
			$this->view->membership_history = $membership_history;
		
			// get statuses
			$mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			$this->view->statuses = $mstatuses;
		
		
			// ################################################
			// get associated clients of current clientid START
			// ###############################################
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
			if($connected_client){
				$vw_clientid = $connected_client;
			} else{
				$vw_clientid = $logininfo->clientid;
			}
			 
			// ################################################
			// get associated clients of current clientid END
			// ###############################################
			 
			$fdoc = new Voluntaryworkers();
			// 		    $docarray = $fdoc->getClientsVoluntaryworkers($vw_clientid);
			$docarray = $fdoc->getClientsVoluntaryworkersSort($vw_clientid,false,$sort);
			 
			foreach($docarray as $k=>$vw){
				$vw_details[$vw['id']] = $vw;
				$vw_name_details[$vw['id']] = $vw['last_name'].', '.$vw['first_name'];
				$vw_ids[] = $vw['id'];
			}
			 
			 
			// get all members that are donors and donation_details
			$donations_history = MemberDonations::get_donations_history($clientid);
		
			$this->view->donation_history = $donations_history;
		
			$donors_ids_str .= '"0",';
			$donors_ids = array();
			foreach($donations_history as $donation_id =>$donation_data){
				if(!in_array($donation_data['member'],$donors_ids)){
					$donors_ids[] = $donation_data['member'];
					$donors_ids_str .= '"'.$donation_data['member'].'",';
				}
				$donation_details[$donation_data['member']][] = $donation_data;
			}
		
			if(empty($donors_ids)){
				$donors_ids[] = "9999999999";
			}
			
			
			$filter_by_icons = array();
			if(strlen($_REQUEST['icons_filter']) > 0 && $_REQUEST['icons_filter'] != "0"){
			
				$filter_icons = explode(',',$_REQUEST['icons_filter']);
				
				$member_icons = new MemberIcons();			
				$filter_by_icons = $member_icons->filter_icons(false, $filter_icons );
				$filter_by_icons = array_unique($filter_by_icons);	
							
				if(empty($filter_by_icons)) {
					
					$this->returnDatatablesEmptyAndExit();
				}
				
			} 
			
			
			/* --------------------  build filters array based on form input ------------------ */
			$filters = array();
		
			$member_id_array = array(
					'with_donations' => array(),
					'with_membership' => array(),
					'registered_as_donors' => array(),
					'registered_as_members' => array(),
			);
			$sql_txt =  array();
			
			switch($_REQUEST['tab'])
			{
		
				case 'active':
					//was added from member tab = registered_as_members
					//inactive = 0 is active
					//or has a donor has a membership
					
					
					$registered_as_members = MemberReferalTab::get_members($clientid);
					if ( ! empty($registered_as_members)) {
						$member_id_array['registered_as_members'] = array_column($registered_as_members, "memberid");
						$sql_txt['registered_as_members'] = '(id IN (' . implode(',', $member_id_array['registered_as_members']) . ') )';
					}
						
					if ( ! empty($membership_history_array)) {
						$member_id_array['with_membership'] = array_column($membership_history_array, "member");
						$sql_txt['with_membership'] = '(id IN (' . implode(',', $member_id_array['with_membership']) . ') )';
					}
										
					
					if ( ! empty($sql_txt) ) {
					
						$filters['member_status'] =
						$filters['member'] = 'isdelete = 0 AND inactive = 0 AND ( '. implode(" OR ", $sql_txt) ." ) ";
					
					}
					else {
						$filters['member_status'] =
						$filters['member'] = ' id IS NULL ';
					}
						
					
					break;
		
				case 'inactive':
					//was added from member tab = registered_as_members
					//inactive = 1 is NOTactive
					
					$registered_as_members = MemberReferalTab::get_members($clientid);
					if ( ! empty($registered_as_members)) {
						$member_id_array['registered_as_members'] = array_column($registered_as_members, "memberid");
						$sql_txt['registered_as_members'] = '(id IN (' . implode(',', $member_id_array['registered_as_members']) . ') )';
					}
					
						
					if ( ! empty($sql_txt) ) {
							
						$filters['member_status'] =
						$filters['member'] = 'isdelete = 0 AND inactive = 1 AND ( '. implode(" OR ", $sql_txt) ." ) ";
							
					}
					else {
						$filters['member_status'] =
						$filters['member'] = 'id IS NULL ';
					}
					
					
					break;
		
				case 'donors':
					
					//was added from spender tab = registered_as_donors
					//or in $donations_history + inactive=0
					//or in $membership_history_array + inactive=0					
					$registered_as_donors = MemberReferalTab::get_donors($clientid);
					if ( ! empty($registered_as_donors)) {
						$member_id_array['registered_as_donors'] = array_column($registered_as_donors, "memberid");
						$sql_txt['registered_as_donors'] = '(id IN (' . implode(',', $member_id_array['registered_as_donors']) . ') )';
					}
					
					if ( ! empty($donations_history)) {
						$member_id_array['with_donations'] = array_column($donations_history, "member");						
						$sql_txt['with_donations'] = '(inactive = 0 AND id IN (' . implode(',', $member_id_array['with_donations']) . ') )';
					}
					
					/*
					if ( ! empty($membership_history_array)) {
						$member_id_array['with_membership'] = array_column($membership_history_array, "member");
						$sql_txt['with_membership'] = '(inactive = 0 AND id IN (' . implode(',', $member_id_array['with_membership']) . ') )';
					}
					*/
									
					
					if ( ! empty($sql_txt) ) {
						
						$filters['member_status'] =
						$filters['member'] = 'isdelete = 0 AND ( '. implode(" OR ", $sql_txt) ." )";
												
					}
					else {
						$filters['member_status'] =
						$filters['member'] = 'id IS NULL ';
					}

					
					
					break;
		
				default:
					$filters['member_status'] = '';
					$filters['member'] = '1';
					break;
			}
		
			if(!empty($_REQUEST['f_dob_start']) && !empty($_REQUEST['f_dob_end']))
			{
				$filters['member'] .= ' AND (year(birthd) BETWEEN "' . $_REQUEST['f_dob_start'] . '" AND "' . $_REQUEST['f_dob_end'] . '")';
			}
		
			
			if (count($filter_by_icons) > 0){
				$filters['member'] .= " AND id IN (". implode(",", $filter_by_icons)." ) ";
			}
			
			
		
		
			/* --------- get initial ipids and apply patient master filters ---------------------------- */
			$member = Doctrine_Query::create()
// 			->select('*')
			->select('id')
			->from('Member')
			->where('isdelete = 0')
			->andWhere($filters['member'])
			->andWhere('clientid = ? ' , $this->clientid);
// 			->leftJoin("m.MemberReferalTab mrt")
// 			->andWhere('m.id = mrt.memberid');
				
			//->orderBy($order_by_str);
			//echo ($member->getSqlQuery());die();
			//echo $filters['member'];
			
			

			if ( ! empty($search_value))
			{
				//ISPC 1739
				//$regexp = addslashes(trim($search_value));
				//Pms_CommonData::value_patternation($regexp);
			
// 				$regexp = mb_strtolower(preg_quote(trim($search_value) , "'"), 'UTF-8') ;
				//@claudiu 12.2017, changed Pms_CommonData::value_patternation
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp, false, true);
				
				$filter_search_value_arr = array();
				$regexp_arr = array();
				foreach ($regexp as $word) {

				    $filter_search_value_arr [] = 'CONVERT(CAST(member_company as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(title as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(salutation_letter as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(salutation as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(first_name as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(last_name as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(birthd as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(phone as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(private_phone as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(mobile as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(email as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(website as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(fax as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(street1 as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(street2 as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(zip as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(city as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(country as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(profession as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(remarks as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(memos as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				    $filter_search_value_arr [] = 'CONVERT(CAST(comments as BINARY) USING utf8) LIKE CONVERT(CAST(? as BINARY) USING utf8)';
				    $regexp_arr[] = '%'. $word .'%';
				}
				
				
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp);
				$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \',
		    								member_company,
		    								title,
											salutation_letter,
											salutation,
											first_name,
											last_name,
		                                    first_name,
											birthd,
											phone,
											private_phone,
											mobile,
											email,
											website,
											fax,
											street1,
											street2,
											zip,
											city,
											country,
											profession,
											remarks,
											memos,
											comments ) USING utf8 ) REGEXP ?';
				$regexp_arr[] = $regexp;
				
				/*
				 * original query before ispc-1739
				 $filters['member'] .= ' AND (CONCAT(last_name,first_name,title,street1,zip,city,phone,email) LIKE "%' . addslashes($_REQUEST['f_keyword']) . '%")';
				 */
				$member->andWhere( '(' . implode( ' OR ', $filter_search_value_arr) .')' , $regexp_arr);
				
				//todo 765 - search also in member_family table
				$member_family = Doctrine_Query::create()
				->select('id, member_id')
				->from('MemberFamily')
				->where('isdelete = 0')
				->andWhere('merged_slave = 0')
				->andWhere('clientid = ?' , $this->clientid)
// 				->andWhere( $filter_search_value , $regexp)
				->andWhere( '(' . implode( ' OR ', $filter_search_value_arr) .')' , $regexp_arr)
				->fetchArray();
				
				if (count($member_family) > 0){
					
					$member_family_ids =  array_column($member_family, 'member_id');					
					$member->orWhere(' ('.$filters['member_status'].' AND clientid='.$this->clientid . ' AND id IN ('.implode(',',$member_family_ids).'))');
				}
				
			}
			
			
			
			
			$patienidt_array = $member->fetchArray();
		
			if(empty($patienidt_array)){
				
				$this->returnDatatablesEmptyAndExit();
			}
		
		
// 			$member_filter_ipids = array();
// 			foreach($patienidt_array as $k_pat_idtarray => $v_pat_idtarray)
// 			{
// 				$member_filter_ipids[] = $v_pat_idtarray['id'];
// 			}
		
// 			$pat_ipidsarr = $member_filter_ipids;
		
// 			$pat_ipidsarr = array_values(array_unique($pat_ipidsarr));
			 
		
		
// 			foreach($patienidt_array as $k_pat_details => $v_pat_details)
// 			{
// 				if(in_array($v_pat_details['id'], $pat_ipidsarr))
// 				{
// 					$patienidtarray[] = $v_pat_details;
// 				}
// 			}
// 			if(empty($patienidtarray)){
// 				$patienidtarray[9999999] = "xx";
// 			}
		
		
// 			foreach($patienidtarray as $memberid)
// 			{
// 				$memberipidsarr[] = $memberid['id'];
// 			}
		
// 			foreach($patienidtarray as $memberid)
// 			{
// 				$memberipidsfinal[] = $memberid['id'];
// 			}
			
			$memberipidsfinal =  array_column($patienidt_array, 'id');
			
			
		
			$storno_invoices_q = Doctrine_Query::create()
			->select("id, record_id")
			->from('MembersInvoices')
			->where('client = ? ' , $clientid )
			->andwhereIn('member', $memberipidsfinal)
			->andWhere('storno = 1')
			->andWhere('isdelete = 0');
			$storno_invoices_array = $storno_invoices_q->fetchArray();
		
			$storno_ids = array();
			if( ! empty($storno_invoices_array)) {
				$storno_ids  = array_column($storno_invoices_array, "record_id");
			}
			
			$invoices_data = array();
			if( ! empty($storno_ids)) {
				
				$invoices = Doctrine_Query::create()
// 				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->select("id, member, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->where("client= ? " , $clientid )
				->andwhereIn('member', $memberipidsfinal)
				->andwhereNotIn('id', $storno_ids)
				->andWhere('isdelete = 0')
				->andWhere('storno = 0')
				->orderBy('completed_date_sort ASC');
				$invoices_array = $invoices->fetchArray();
			
				foreach($invoices_array as $k => $invoice){
					$invoices_data[$invoice['member']]['invoice_date'][] = $invoice['completed_date_sort'];
				}
			}
		
			//$no_patients = sizeof($memberipidsfinal);
			//$no_pages = ceil($no_patients / $limit);
		
			
			//payment_method_id => payment_method_description
			$MemberPaymentMethod_list = MemberPaymentMethod::get_list($clientid  );
			
			
			//get colored icons
			$icons_client = new IconsClient();
			$client_icons = $icons_client->get_client_icons($clientid, false, 'icons_member');
			
			
			$member_icons = new MemberIcons();
			$member_icons_array = $member_icons->get_icons($memberipidsfinal);
			
			$member = Doctrine_Query::create()
			->select("*, mrt.referal_tab ")
			->from('Member m')
			->whereIn("id", $memberipidsfinal)
			->andWhere('clientid = ?' , $this->clientid)
			
			->leftJoin("m.MemberReferalTab mrt");
// 			->andWhere('m.id = mrt.memberid');
			
			
			if($order_by_str != false){
				$member->orderBy($order_by_str);
			}
			
			if($limit != "-1"){
				
				$member->limit($limit);
				$member->offset($offset);
				
			}

			$memberlimit = $member->fetchArray();

			
			 
			$last_donation = array();
			foreach($memberlimit as $key => $member_item)
			{
		
				if($member_item['gender'] == "1"){
					$memberlimit[$key]['gender'] = $this->view->translate("male");
				} 
				elseif($member_item['gender'] == "2"){
					$memberlimit[$key]['gender'] = $this->view->translate("female");
				} 
				elseif($member_item['gender'] == "3"){
				    $memberlimit[$key]['gender'] = $this->view->translate("divers"); //ISPC-2442 @Lore   09.10.2019
				} 
				else{
					$memberlimit[$key]['gender'] = $this->view->translate("gender_not_documented");
				}

				//ISPC-2795 Dragos 15.01.2021
				$memberlimit[$key]['member_type'] = $this->view->translate($member_item['type'].'_type');

				$memberlimit[$key]['street'] = $member_item['street1'];
				 
				$memberlimit[$key]['membership_type'] = $membership_history[$member_item['id']]['membership'];
				
				$memberlimit[$key]['membership_end_reasonid'] = $membership_history[$member_item['id']]['membership_end_reasonid'];//ISPC-2152
				
				$memberlimit[$key]['membership_start_date'] = $membership_history[$member_item['id']]['membership_start_date'];//ISPC-2152
				
				if(!empty($invoices_data[$member_item['id']]['invoice_date'])){
					$memberlimit[$key]['last_invoice_date'] = date('d.m.Y', strtotime(end($invoices_data[$member_item['id']]['invoice_date'])));
				} else {
					$memberlimit[$key]['last_invoice_date'] = "";
				}
				if($member_item['vw_id']!="0"){
					 
					$memberlimit[$key]['voluntary_referance'] = $vw_name_details[$member_item['vw_id']];
				} else{
					$memberlimit[$key]['voluntary_referance'] = "";
				}
		
				if($member_item['status']!="0"){
					 
					$memberlimit[$key]['status'] = $mstatuses[$member_item['status']]['status'];
				} else{
					$memberlimit[$key]['status'] = "";
				}
		
				
				if(!empty($donation_details[$member_item['id']])){
						$last_donation = end($donation_details[$member_item['id']]);
						$memberlimit[$key]['last_donation'] = $last_donation['donation_date'].' ('.$last_donation['amount'].' &euro;)';
						$memberlimit[$key]['last_donation_date'] = $last_donation['donation_date'];
				} else{
					$memberlimit[$key]['last_donation'] = "-";
					$memberlimit[$key]['last_donation_date'] ="";
				}
				
				$memberlimit[$key]['payment_method_description'] = $member_item['payment_method_id'] > 0 ? $MemberPaymentMethod_list[$member_item['payment_method_id']]['description'] : '';
				
				
				
			}
			
			if($order_dir == 'desc' )
			{
				$sort_n= SORT_DESC;
			}
			else
			{
				$sort_n = SORT_ASC;
			}
			
			
			//========= sorting columns===============
			
			switch ($manual_order_column){
				case "last_invoice_date":
				case "voluntary_referance":
				case "status":
				case "profession":
				case "membership_type":
				case "membership_end_reasonid":
				case "membership_start_date":
				case "last_donation":
				case "payment_method_description":
				case 'member_type': //ISPC-2795 Dragos 15.01.2021
					$memberlimit = $this->array_sort($memberlimit, $manual_order_column, $sort_n);
					break;
				
			}
			/*
			if($columns_array[$order_column] == 'last_invoice_date')
			{
				$memberlimit = $this->array_sort($memberlimit, 'last_invoice_date', $sort_n);
			}
			elseif($columns_array[$order_column] == 'voluntary_referance' )
			{
				$memberlimit = $this->array_sort($memberlimit, 'voluntary_referance', $sort_n);
			}
			elseif($columns_array[$order_column] == 'status' )
			{	
				$memberlimit = $this->array_sort($memberlimit, 'status', $sort_n);
			}
			elseif($columns_array[$order_column] == "profession")
			{
				$memberlimit = $this->array_sort($memberlimit, 'profession', $sort_n);
			}
			elseif($columns_array[$order_column] == "membership_type")
			{
				$memberlimit = $this->array_sort($memberlimit, 'membership_type', $sort_n);
			}
			elseif($columns_array[$order_column] ==  "last_donation")
			{
				$memberlimit = $this->array_sort($memberlimit, 'last_donation', $sort_n);
			}
			*/
			//=============================================
			 
			
			//print_r($memberlimit);
			$resulted_data = array();
			$row = 0;
			foreach($memberlimit as $m){
				
				$link = '<a href="'.APP_BASE .'member/editmember?id='.$m['id'].'&referal_tab='.$_POST['tab'].'"> %s </a>';
				foreach($columns_array as $val){
					$resulted_data[$row][$val] = sprintf($link, $m[$val]);
				}
				
				$referal_icon = "";
				if ($m['MemberReferalTab']['referal_tab'] == 'donors') {
					
					$referal_icon = "(S)";//&#x24C8"; //U+24C8
					
					
				} else{
					
					$referal_icon = "(M)";//&#x24C2"; //U+24C2
					
				} 
			
				
				$resulted_data[$row]['select_member'] = '<input type="checkbox" name="members_ids[]" class="members" value="'.$m['id'].'"/>' . $referal_icon ;
				
				$resulted_data[$row]['actions'] = '<a href="'.APP_BASE.'member/editmember?id='.$m["id"].'&referal_tab='.$_POST['tab'].'"  class="memberlist_link"><img src="'.RES_FILE_PATH.'/images/edit.png" border="0" /></a>'
						.'<a href="javascript:void(0);"  onclick="deleteconfirm(\''.$m['id'].'\')"  class="memberlist_link"><img src="'.RES_FILE_PATH.'/images/action_delete.png" border="0" id="delete_'.$m['id'].'" rel="'.$m['id'].'" class="confirm_button" /></a>';
				$resulted_data[$row]['actions'] = sprintf($link, $resulted_data[$row]['actions']);
				
				$resulted_data[$row]['voluntary_referance']	= sprintf($link, $m['voluntary_referance']);
				
				if($m['birthd'] == '0000-00-00' ||  $m['birthd']== ''){
					$resulted_data[$row]['birthd'] = '-';
				}else{
					$resulted_data[$row]['birthd']	= date('d.m.Y',strtotime($m['birthd']));
					$resulted_data[$row]['birthd'] = sprintf($link, $resulted_data[$row]['birthd']);
				}
				
				$resulted_data[$row]['image'] = '-';
				if (strlen($m['img_path']) > 0 ){
					$img_file = 'icons_system/'.trim($m['img_path']);
					$resulted_data[$row]['image'] = '<img src="'. $img_file .'" class="vw_img" style="width:40px;"/>';
					$resulted_data[$row]['image'] = sprintf($link, $resulted_data[$row]['img_path']);
				}
				
				$icon_row = '';
				//echo count($member_icons_array[$m['id']]) ."<\r>";
				if(isset($member_icons_array[$m['id']])){
					$icons = array_column($member_icons_array[$m['id']], 'icon_id');
					foreach ($icons as $icon)
					{
						if($client_icons[$icon]['image']){
							$icon_row .= '<span class="vw_list_icon" style="background:#'.$client_icons[$icon]['color'].'"><img src="'.APP_BASE.'icons_system/'.$client_icons[$icon]['image'].'"   title="'.$client_icons[$icon]['name'].'"    /></span> ';
						} else{
							$icon_row .= '<span class="vw_list_icon"  style="background:#'.$client_icons[$icon]['color'].'"><p></p></span> ';
						}
					}
				}
				$resulted_data[$row]['icon_row'] = sprintf($link, $icon_row);
				
				$link = '<a href="'.APP_BASE .'member/editmember?id='.$m['id'].'&tabs=3&referal_tab='.$_POST['tab'].'"> %s </a>';
				$resulted_data[$row]['last_donation'] = sprintf($link, $m['last_donation']);
				
				$link = '<a href="'.APP_BASE .'member/editmember?id='.$m['id'].'&tabs=1&referal_tab='.$_POST['tab'].'"> %s </a>';
				$resulted_data[$row]['last_invoice_date'] = sprintf($link, $m['last_invoice_date']);
				$resulted_data[$row]['membership_type'] = sprintf($link, $m['membership_type']);
				
				$resulted_data[$row]['membership_end_reasonid'] = ! empty($m['membership_end_reasonid']) ? sprintf($link, $m['membership_end_reasonid']) : '';//ISPC-2152
				
				$resulted_data[$row]['membership_start_date'] = ! empty($m['membership_start_date']) ? sprintf($link, $m['membership_start_date']) : '';//ISPC-2169
				
				
								
				$row++;
				
			}
			/*
			$this->view->memberlist = $memberlimit;
			$this->view->current_page = $current_page;
			$this->view->no_pages = $no_pages;
			$this->view->no_patients = $no_patients;
			$this->view->orderby = $_REQUEST['ord'];
			$this->view->sort = $_REQUEST['sort'];
			*/
			/*
			if(count($_REQUEST['columns']) != "0" && $_REQUEST['savecols'] == "1")
			{ //save
				foreach($allTabsColumns[$tabs[$reqestedTab]] as $k => $cid)
				{
					$column_array[] = $cid;
				}
				if(!empty($column_array))
				{
					$drop = Doctrine_Query::create()
					->delete("*")
					->from('MemberColumns2users')
					->where('user_id ="' . $this->userid . '"')
					->andWhereIn("c2t_id", $column_array);
					$delete = $drop->execute();
				}
		
				foreach($_REQUEST['columns'] as $columnid)
				{
					if($allTabsColumns[$tabs[$reqestedTab]][$columnid] != "0")
					{
						$records[] = array(
								"c2t_id" => $allTabsColumns[$tabs[$reqestedTab]][$columnid],
								"user_id" => $this->userid
						);
					}
				}
		
				if(!empty($records))
				{
					$collection = new Doctrine_Collection('MemberColumns2users');
					$collection->fromArray($records);
					$collection->save();
				}
			}
			*/
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = count($memberipidsfinal);//$full_count;
			$response['recordsFiltered'] = count($memberipidsfinal);//$full_count;//count($resulted_data); // ??
			$response['data'] = $resulted_data;
			
			header("Content-type: application/json; charset=UTF-8");
			
			echo json_encode($response);
			exit;
			
			
		}
		
		/*
		private function returnDatatablesEmptyAndExit()
		{
			
			
			$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
			$viewRenderer->setNoRender(true); // disable view rendering
			
			$response = array();
			$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = 0;
			$response['recordsFiltered'] = 0;//count($resulted_data); // ??
			$response['data'] = array();
				
			ob_end_clean();
			ob_start();

			$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
			$json->sendJson($response);
			
// 			header("Content-type: application/json; charset=UTF-8");
// 			echo json_encode($response);
			exit;
		}
		*/
		
		private function sortMultiArray($arr, $k, $sort) {
                $tmp = Array();
                foreach($arr as &$ma)  $tmp[] = &$ma[$k];
                $tmp = array_map('strtolower', $tmp);      // pt case-insensitive
                array_multisort($tmp, $sort, $arr);
                return $arr;
              }
		
		public function addmemberAction()
		{
			$clientid = $this->clientid;
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
//			$this->view->genders = Pms_CommonData::getGender();
			$this->view->genders = Pms_CommonData::getGenderMember();  //ISPC-2442 @Lore 09.10.2019
			
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('member', $this->userid, 'canadd');
			
			$this->view->action_page = "add";

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			
			if(isset($_GET['id']))
			{
				$get = "?id=" . $_GET['id'];
			}
			$this->view->act = "member/addmember" . $get;
 
			$this->_helper->layout->setLayout('layout');
			

			// get statuses
			$mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			$this->view->statuses = $mstatuses;
			

			// get memberships
			$client_memberships = Memberships::get_memberships($clientid);
			$this->view->memberships = $client_memberships;
				
			$member = new Member();
// 			$last_nr = $member->get_next_member_number($clientid);
			$last_nr = $member->get_next_mandate_reference($clientid);
// 			$this->view->mandate_reference = $last_nr;
			
			$highest_nr = $member->get_highest_member_number();
			$this->view->highest_nr = $highest_nr;
			
			
			if($this->getRequest()->isPost())
			{
				$a_post = $_POST;
				
			  	$member_form = new Application_Form_Member();
				if($member_form->validate($a_post))
				{
// 				    print_r($a_post);exit;
					$member_id = $member_form->InsertData($a_post);

					if($member_id){
					    //#########################################
					    // add  memberships
					    //#########################################
					    $membership_error = 0;
					    foreach($a_post['membership'] as $ms_id => $data_ms){
					        
                            if(strlen($data_ms['membership']) > 0  && $data_ms['membership'] != 0 ){
                                
    					        if(strlen($data_ms['start']) > 0  ){
    					            $ms_start_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['start']));
    					            $start_date[$ms_id] = date('Y-m-d',strtotime($data_ms['start']));
    					        }
    					    
    					        if(strlen($data_ms['end']) > 0  ){
    					            $ms_end_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['end']));
    					            $end_date[$ms_id] = date('Y-m-d',strtotime($data_ms['end']));
    					        }
    					         
    					        $membership_records[] = array(
    					            "clientid" =>   $clientid,
    					            "member" =>   $member_id,
    					            "membership" =>   $data_ms['membership'],
    					            "membership_price" =>   $data_ms['membership_price'],
    					            "start_date" =>   $ms_start_date[$ms_id],
    					            "end_date" =>   $ms_end_date[$ms_id],
    					            "end_reasonid" =>   $data_ms['end_reasonid'],
    					            "ms_id" =>  $ms_id,
    					        		
    					        );
                            }
					    }

					    $membership_order = $this->array_sort($membership_records, 'start_date', SORT_ASC);
				        
				        if(!empty($membership_order)){
                            // close previous
				            foreach($membership_order as $kmps => $mps_data){
				                if( strtotime($mps_data['end_date'])  < $membership_order[$kmps+1]['start_date'] && empty($mps_data['end_date'])){
				                    $membership_order[$kmps]['end_date'] = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($membership_order[$kmps+1]['start_date']))); 
				                }
				                
				                $pfl_cl = new Member2Memberships();
				                $pfl_cl->clientid = $clientid;
				                $pfl_cl->member = $member_id;
				                $pfl_cl->membership = $membership_order [ $kmps ] ['membership'];
				                $pfl_cl->membership_price = $membership_order [ $kmps ]['membership_price'];
				                $pfl_cl->start_date = $membership_order [ $kmps ] ['start_date'];
				                $pfl_cl->end_date = $membership_order [ $kmps ] ['end_date'];
				                $pfl_cl->end_reasonid = $membership_order [ $kmps ] ['end_reasonid'];
				                $pfl_cl->save();
				                 
				                $ms_id = $membership_order [ $kmps ] ['ms_id'];
				                switch ($a_post['sepa_howoften']){
				                	case "monthly":
				                		foreach($a_post['sepa_month_amount'] as $month=>$id){
				                			$a_post['sepa_month_amount'][$month][$pfl_cl->id] = $a_post['sepa_month_amount'][$month][$ms_id];
				                			unset($a_post['sepa_month_amount'][$month][$ms_id]);
				                		}
				                		break;
				                	case "quarterly":
				                		foreach($a_post['sepa_quarter_amount'] as $month=>$id){
				                			$a_post['sepa_quarter_amount'][$month][$pfl_cl->id] = $a_post['sepa_quarter_amount'][$month][$ms_id];
				                			unset($a_post['sepa_quarter_amount'][$month][$ms_id]);
				                		}
				                		break;
				                
				                }
				                
				            }
				            /*
				            $collection = new Doctrine_Collection('Member2Memberships');
				            $collection->fromArray($membership_order);
				            $collection->save();
				            */
				            
				        }
				        //ispc-1842 - membership sepa settings / how ofthen and when to pay
				        if ($a_post['sepa_is_active_input'] == "1") {
				        	MembersSepaSettings :: set_sepa_settings($clientid , $member_id, $a_post);
				        }else{
				        	MembersSepaSettings :: reset_settings($clientid , $member_id);
				        	
				        }
				        
				        $this->clear_image_details();
				        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
				        $get = "&id=" . $member_id;
				        
				        $this->_redirect(APP_BASE . 'member/editmember?flg=suc' . $get);
				        
					}
					else{
						//ispc 1739 full else duplicate member verify
						$member_form->assignErrorMessages();
						$this->retainValues($_POST);
					}
				}
				else
				{
					$member_form->assignErrorMessages();
			        $this->view->membership_history = $membership_records;
					$this->retainValues($_POST);
					

					$err = $member_form->getErrorMessages();
					$this->view->validate_err = $err;
				}
			}
			$this->view->clientid = $clientid;
			
			//ispc 1739 p.15
			// get reasonofmembershipend list => reason_inactive
			$r = array('0' => $this->view->translate('selectreason_end'));
			$ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid , 0);
			foreach($ReasonOfMembershipEnd_list as $k=>$v){
				$r[$v['id']] = $v['description'];
			
			}
			$this->view->reasonofmembershipend_array = $r;
			
			//ispc 1739 p.12
			// get payment_method from id
			$r = array('0' => $this->view->translate('please select'));
			$MemberPaymentMethod_list = MemberPaymentMethod::get_list($clientid , 0 );
			foreach($MemberPaymentMethod_list as $k=>$v){
				$r[$v['id']] = $v['description'];
					
			}
			$this->view->paymentmethod_array = $r;
			//ispc 1739
			//get client icons
			$icons_client = new IconsClient();
			$client_icons = $icons_client->get_client_icons($clientid, false, 'icons_member');
			foreach($client_icons as $k => $v)
			{
				$client_icons[$v['id']]['visible'] = '1';
			}
			$this->view->custom_icon_details = $client_icons;
			
			
			
		}
		
		public function editmemberAction()
		{
		    //ISPC-2606, elena, 19.10.2020
			if(isset($_POST['back_list']) && $_POST['back_list'] != ''){
			    setcookie('back_list',$_POST['back_list']);
			    $this->view->back_list = $_POST['back_list'];

            }elseif (isset($_COOKIE['back_list'])){
                $this->view->back_list = $_COOKIE['back_list'];
            }
		    $has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$this->view->action_page = "edit";
			//$this->view->genders = Pms_CommonData::getGender();
			$this->view->genders = Pms_CommonData::getGenderMember();  //ISPC-2442 @Lore 09.10.2019
			
			if(strlen($_GET['id']) > 0)
			{
				$member_id = intval($_GET['id']);
				$edit = "?id=" . $_GET['id'];
			}
 

			$this->view->act = "member/editmember" . $edit;
			$this->_helper->viewRenderer('addmember');
 
			$clientid = $this->clientid;
			
			if($member_id > 0)
			{

		
			   //$member = Doctrine::getTable('Member')->find($member_id);
// 			   $member = Doctrine::getTable('Member')->findByIdAndClientidAndIsdelete($member_id, $clientid, 0);
// 			   $memberarray = $member->toArray();
// 			   $memberarray = array_values($memberarray);
// 			   $memberarray = $memberarray[0];
			   
				$memberarray = Member::getMemberDetails( $member_id );
				$memberarray = $memberarray[$member_id];
			   
			   if(empty($memberarray) || $memberarray['clientid'] != $clientid)
			   {
			   	$this->_redirect(APP_BASE . "error/previlege");
			   	exit;
			   }
			   //save for the viewer ... remember not to overwrite them cause they are not in an array, or somehow protected
			   $this->retainValues($memberarray);
			   //ISPC 1739
			   $this->view->family_child = false;
			   if($memberarray['type'] == 'family')
			   {
			   	if($family_child = MemberFamily::getMemberFamilyDetails($memberarray['id'])){
			   		$family_child = array_values($family_child) ;
			   		$family_child = $family_child[0] ;
			   		if($family_child['birthd'] != "0000-00-00"){
			   			$family_child['birthd'] = date('d.m.Y',strtotime($family_child['birthd']));
			   		} else{
			   			$family_child['birthd'] = "";
			   		}
			   		$this->view->family_child = $family_child;
			   	}
			   }
			   
			  if($memberarray['birthd'] != "0000-00-00"){
			        $this->view->birthd = date('d.m.Y',strtotime($memberarray['birthd']));
			    } else{
			        $this->view->birthd = "";
			    }
			    
			    $clientid = $memberarray['clientid'];
			   
			    if($memberarray['vw_id'] != 0 ){
			        $vw_id = $memberarray['vw_id'];
			        $vw_model = new  Voluntaryworkers();
			        $vw_details = $vw_model->getVoluntaryworkers($vw_id);
			        if(!empty($vw_details)){
			            $this->view->voluntary_name = $vw_details['0']['last_name'].', '.$vw_details['0']['first_name'];
			        }
			    } else{
			        $this->view->voluntary_name = '';
			    }
			}
				
			
			
			if($_REQUEST['flg'] == "suc")
			{
// 			    $this->view->error_message = $this->view->translate("recordinsertsucessfully");
			    $this->view->validate_suc = array("0"=>$this->view->translate("recordinsertsucessfully"));
			}
			if($_REQUEST['file_flg'] == "err")
			{
// 				$this->view->error_message .= "<br>" .$this->view->translate("error") . " ". $this->view->translate("youuploadedaninvalidfile");
				$this->view->validate_err = array("0"=>$this->view->translate("error") . ' ' . $this->view->translate("youuploadedaninvalidfile"));
			}
			
			
	
			if($this->getRequest()->isPost() && isset($_POST['save_form'])) //ISPC-2606, elena, 19.10.2020
			{
								
				$client_form = new Application_Form_Member();
				if($_GET['id'] > 0)
				{
					if($client_form->validate($_POST))
					{
					    $a_post = $_POST;
					   
					    $a_post['did'] = $_GET['id'];
						$change = $client_form->UpdateData($a_post);
											
						//dd($a_post);
						
						//#########################################
						// add / edit / delete memberships
						//#########################################

						foreach($a_post['membership'] as $ms_id => $data_ms){
						    
                            if(strlen($data_ms['membership']) >  0 && $data_ms['membership'] != 0){
						         
    						    if(strlen($data_ms['start']) > 0  ){
    						        $ms_start_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['start']));
    						    }
    						    	
    						    if(strlen($data_ms['end']) > 0  ){
    						        $ms_end_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['end']));
    						        
    						        //ISPC-2228 Lore
    						        $mem_invoices = Doctrine_Query::create()
    						        ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
    						        ->from('MembersInvoices')
    						        ->where("member ='" . $_GET['id'] . "'")
    						        ->andWhere('isdelete = "0"')
    						        ->andWhere('isarchived = "0"')
    						        ->andWhere('storno = "0"')
    						        //TODO-4025 Ancuta 12.05.2021 - changed, to check invoice_start  AFTER membership end! then set as storno
    						        //->andWhere("invoice_end <= '" . $ms_end_date[$ms_id] . "'");
    						        ->andWhere("invoice_start > '" . $ms_end_date[$ms_id] . "'");
    						        //-- 
    						        
    						        $invoices_res = $mem_invoices->fetchArray();
    						        
    						        if($invoices_res)
    						        {
    						            foreach ($invoices_res as $key){
    						                
    						                $has_storno = Doctrine_Query::create()
    						                ->select("*")
    						                ->from('MembersInvoices')
    						                ->where("member ='" . $_GET['id'] . "'")
    						                ->andWhere('isdelete = "0"')
    						                ->andWhere('isarchived = "0"')
    						                ->andWhere('storno = "1"')
    						                ->andWhere("record_id = '" . $key['id'] . "'");
    						                
    						                $has_storno_res = $has_storno->fetchArray();
    						                
    						                if($has_storno_res){
    						                } else {
    						                    // generate storno
    						                    $ins_inv = new MembersInvoices();
    						                    $ins_inv->member = $key['member'] ;
    						                    $ins_inv->invoice_start = $key['invoice_start'];
    						                    $ins_inv->invoice_end = $key['invoice_end'];
    						                    $ins_inv->membership_start = $key['membership_start'];
    						                    $ins_inv->membership_end = $key['$membership_end'];
    						                    $ins_inv->membership_data = $key['membership_data'];
    						                    $ins_inv->invoiced_month = $key['invoiced_month'];
    						                    $ins_inv->client = $key['client'];
    						                    $ins_inv->prefix = $key['prefix'];
    						                    $ins_inv->invoice_number = $key['invoice_number'];
    						                    $ins_inv->invoice_total = $key['invoice_total'];
    						                    $ins_inv->recipient = $key['recipient'];
    						                    $ins_inv->status = $key['status'];
    						                    $ins_inv->record_id = $key['id'];
    						                    $ins_inv->storno = '1';
    						                    $ins_inv->storno_comment = $this->view->translate("mitgliedschaft_end").$data_ms['end'];
    						                    $ins_inv->save();
     						                }
    						            }
    						        } 
    						        //ISPC-2228.
    						    }
    						
    						    
    						    
    						    if($data_ms['custom'] == "0"){
    						        // update existing
    						        //ispc 1739
    						    	$fdoc = Doctrine::getTable('Member2Memberships')->find((int)$ms_id);
    						    	$fdoc->membership = $data_ms['membership'];
    						    	$fdoc->membership_price =$data_ms['price'];
    						    	$fdoc->start_date = $ms_start_date[$ms_id];
    						    	$fdoc->end_date = $ms_end_date[$ms_id];  
    						    	$fdoc->end_reasonid =$data_ms['end_reasonid'];
    						    	$fdoc->save();

    						    } else {
     
    					            $pfl_cl = new Member2Memberships();
    					            $pfl_cl->clientid = $clientid;
    					            $pfl_cl->member = $_GET['id'];
    					            $pfl_cl->membership = $data_ms['membership'];
    					            $pfl_cl->membership_price = $data_ms['price'];
    					            $pfl_cl->start_date = $ms_start_date[$ms_id];
    					            $pfl_cl->end_date = $ms_end_date[$ms_id] ;
    					            $pfl_cl->end_reasonid =$data_ms['end_reasonid'];
    					            $pfl_cl->save();	            
    					            
    					            switch ($a_post['sepa_howoften']){
    					            	case "monthly":
    					            		foreach($a_post['sepa_month_amount'] as $month=>$id){
    					            			$a_post['sepa_month_amount'][$month][$pfl_cl->id] = $a_post['sepa_month_amount'][$month][$ms_id];
    					            			unset($a_post['sepa_month_amount'][$month][$ms_id]);
    					            		}
    					            		break;
    					            	case "quarterly":
    					            		foreach($a_post['sepa_quarter_amount'] as $month=>$id){
    					            			$a_post['sepa_quarter_amount'][$month][$pfl_cl->id] = $a_post['sepa_quarter_amount'][$month][$ms_id];
    					            			unset($a_post['sepa_quarter_amount'][$month][$ms_id]);
    					            		}
    					            		break;
    					            	
    					            }
 
    						    }
						    }
						}
						
						// delete associations to patients
						if(strlen($a_post['delete_memberships']) > 0 ){
						    $delete_ids = explode(',',$a_post['delete_memberships']);
						
						    if(is_array($delete_ids)){
						
						        $q = Doctrine_Query::create()
						        ->update('Member2Memberships')
						        ->set('isdelete', '1')
						        ->set('change_date','?', date('Y-m-d H:i:s'))
						        ->set('change_user','?',  $this->userid)
						        ->whereIn('id',$delete_ids);
						        $q->execute();
						    }
						}
						
						//ispc-1842 - membership sepa settings / how ofthen and when to pay
						if ($a_post['sepa_is_active_input'] == "1") {
							MembersSepaSettings :: set_sepa_settings($clientid , $a_post['did'], $a_post);
						}else{
							MembersSepaSettings :: reset_settings($clientid , $a_post['did']);	
						}
						
						
						$file_flg = "";
						//ispc 1739 p.16 upload a single new version file
						if(!empty($_POST['add_file']) && $_POST['add_file'] == '1'){
							
							$file_flg = "suc";
							$result = $this->fileuploadAction(false , (int)$_GET['id']);
							//print_r($result);
							if ($result['success'] != true){
								$file_flg = "err";
								$this->view->error_message_file = $result['error'] ." File was NOT saved.<br>";
							}
						}
						
						$tabs = (!empty($_REQUEST['tabs'])) ? 'tabs='.(int)$_REQUEST['tabs'] : "";
						$referal_tab = (!empty($_REQUEST['referal_tab'])) ? 'referal_tab='.htmlspecialchars($_REQUEST['referal_tab']) : "";
						
						if($change == 'change')
						{
						    $this->clear_image_details();
							$this->view->error_message = $this->view->translate("recordupdatedsucessfully3");
							
							$this->_redirect(APP_BASE . 'member/editmember?id='.$_GET['id'].'&flg=suc&'.$tabs . "&file_flg=".$file_flg ."&".$referal_tab);
						}
						else
						{
							$this->view->error_message = $this->view->translate("recordupdatedsucessfully4");
							$this->clear_image_details();
							
							$this->_redirect(APP_BASE . 'member/editmember?id='.$_GET['id'].'&flg=suc&'.$tabs. "&file_flg=".$file_flg ."&".$referal_tab);
						}
					}
					else
					{
						$client_form->assignErrorMessages();
						$this->retainValues($_POST);
						
						$err = $client_form->getErrorMessages();
						$this->view->validate_err = $err;
						
						
						
						$tabs = (!empty($_REQUEST['tabs'])) ? (int)$_REQUEST['tabs'] : 0;
						$this->view->tabs_menu = $tabs;
						
					}
				}
			}


			// get client settings.
			$client_data_array = Client::getClientDataByid($this->clientid);
			$client_data = $client_data_array[0];
			
			$this->view->billing_method = $client_data['membership_billing_method'];
			
			// get statuses
			$mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			$this->view->statuses = $mstatuses;
				
			// get memberships
			$client_memberships = Memberships::get_memberships($clientid);
			$this->view->memberships = $client_memberships;
			
			
			if(strlen($_GET['id']) > 0){
			    $member_id = $_GET['id'];
			    
			    $last_inv_q= Doctrine_Query::create()
			    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			    ->from('MembersInvoices')
			    ->where("client= ?", $clientid)
			    ->andWhere('member = ?', $member_id)
			    ->andWhere('isdelete = "0"  ')
			    ->orderBy('completed_date_sort DESC')
			    ->limit("1");
			    $last_invoice_array = $last_inv_q->fetchArray();

			    if($last_invoice_array){
			        $last_invoice_date = date("d.m.Y",strtotime($last_invoice_array[0]['completed_date_sort']));
			    }
			    $this->view->last_invoice_date = $last_invoice_date;
			    
			    
			    
			    $invoices_q = Doctrine_Query::create()
			    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			    ->from('MembersInvoices')
			    ->where("client= ?", $clientid)
			    ->andWhere('member = ?', $member_id)
			    ->andWhere('isdelete = "0"  ');
			    $invoices_array = $invoices_q->fetchArray();
			    $this->view->invoices_array = $invoices_array;
			
			    foreach($invoices_array as $k => $invoice){
// 			        if($invoice['isdelete'] == '0'){
			            $invoice_data_s[$member_id][$invoice['id']]['start'] = $invoice['invoice_start'];
			            $invoice_data_s[$member_id][$invoice['id']]['end'] = $invoice['invoice_end'];
			
			            $invoice_data[$member_id][$invoice['membership_data']][$invoice['id']]['start'] = strtotime($invoice['invoice_start']);
			            $invoice_data[$member_id][$invoice['membership_data']][$invoice['id']]['end'] = strtotime($invoice['invoice_end']);
// 			        }
			    }
			
			    $membership_history_array = Member2Memberships::get_memberships_history($clientid,$_GET['id']);
			
			    $membership_history = null;
			    $invoice_period = null;
			    
			    if(!empty($membership_history_array)){

			        foreach($membership_history_array as $k=>$md){

			            //IMPORTANT 
			            // - CHANGE MEMBERSHIP  END DATE IF INACTIVE DATE IS SET 
			            if($memberarray['inactive'] == "1" && $memberarray['inactive_from'] !=  "0000-00-00"){
			               $inactive_date = date("Y-m-d H:i:s", strtotime($memberarray['inactive_from']));
			                if( $md['end_date'] != "0000-00-00 00:00:00"){
			                    if( strtotime($md['end_date'])  >  strtotime($inactive_date)){
			                        $membership_history_array[$k]['end_date'] =$inactive_date;
			                    }
			                } else {
 
			                    if( strtotime($md['start_date']) < strtotime($inactive_date) ){
    			                    $membership_history_array[$k]['end_date'] = $inactive_date; 
			                    } else {
                                    unset($membership_history_array[$k]);			                        
			                    }
			                }
			            }
			        }
			        foreach($membership_history_array as $k=>$md){

			            $membership_history[$md['id']] = $md;
			            if($client_data['membership_billing_method'] == "membership"){
                            // membership
			                if($md['start_date'] != "0000-00-00 00:00:00"){
			                    $membership_history[$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
			                } else{
			                    $membership_history[$md['id']]['start'] = "";
			                }
			                
			                	
			                if($md['end_date'] != "0000-00-00 00:00:00"){
			                    $membership_history[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
			                    $membership_history_cal[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
			                } else{
			                    $membership_history[$md['id']]['end'] = "";

			                    if(strtotime($md['start_date']) >= strtotime(date("d.m.Y",time()))){
    			                    $membership_history_cal[$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($md['start_date']))));
			                    } else{
    			                    $membership_history_cal[$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", time())));
			                    }
			                }
	 
			                // break membership in 12 months interval
		                    $membership_intervals[$md['id']] = Pms_CommonData::generateDateRangeArray(  $membership_history[$md['id']]['start'] ,$membership_history_cal[$md['id']]['end'],"+1 year");
		                    
		                    foreach($membership_intervals[$md['id']] as $k => $start_dates){
		                        if(count($membership_intervals[$md['id']]) >= 1 && $membership_history[$md['id']]['end'] == "") {
		                            
		                            if( date( 'Y',strtotime($start_dates)) <= date("Y",time()) ){
    		                            $msp_intervals[$md['id']][$k]['start'] = $start_dates;
    		                            if($membership_intervals[$md['id']][$k+1]){
    		                                $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
    		                            } else{
    		                                $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($membership_intervals[$md['id']][$k]))));
    		                            }
		                            }  
		                            
		                        } else{
		                            
		                            $msp_intervals[$md['id']][$k]['start'] = $start_dates;
		                            if($membership_intervals[$md['id']][$k+1]){
		                                $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
		                            } else{
                                        //   $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($start_dates))));
		                                $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime($membership_history[$md['id']]['end']));
		                            }
		                        }
		                    }
			
		                    $m=0;
		                    foreach($msp_intervals[$md['id']] as $int_k => $int_dates){
		                        $invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
		                        $invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
		
		                        $invoice_period[$md['id']][$m]['invoiced'] = 0;
		                        if($invoice_data[$member_id][$md['id']]){
			                        foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
			                            if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
			                                $invoice_period[$md['id']][$m]['invoiced'] += 1;
			                            } else
			                            {
			                                $invoice_period[$md['id']][$m]['invoiced'] += 0;
			                            }
			                        }
		                         }
		                        $m++;
		                    }
			            } else { // CALENDAR YEAR METHOD
			
                            // Membership interval
			                if($md['start_date'] != "0000-00-00 00:00:00"){
			                    $membership_history[$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
			                } else{
			                    $membership_history[$md['id']]['start'] = "";
			                }
			                	
			                if($md['end_date'] != "0000-00-00 00:00:00"){
			                    $membership_history[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
			                    $membership_history_cal[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
			                } else{
			                    $membership_history[$md['id']]['end'] = "";
			                    
			                    if( date( 'Y',strtotime($md['start_date'])) > date("Y",time()) ){
    			                    $membership_history_cal[$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date( 'Y',strtotime($md['start_date']))));;
			                    } else{
    			                    $membership_history_cal[$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date("Y",time())));;
			                    }
			                }

		                    // break membership period in calendar year intervals
		                    $start_year = date('Y',strtotime($membership_history[$md['id']]['start']));
		                    $end_year  = date('Y',strtotime($membership_history_cal[$md['id']]['end']));
		                    $i= 0;
		                    $interval[$md['id']] = array();
		
		                    for ($i = $start_year; $i <= $end_year; $i++ ){
		                        if($i ==  $start_year &&  $start_year != $end_year){
		                            $interval[$md['id']][$i]['start'] = $membership_history[$md['id']]['start'];
		                            $interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
		                        } else if($i ==  $end_year && $start_year  != $end_year ){
		                            $interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
		                            $interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['id']]['end'];
		                        } else if($start_year == $end_year ){
		                            $interval[$md['id']][$i]['start'] = $membership_history[$md['id']]['start'];
		                            $interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['id']]['end'];
		                        } else {
		                            $interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
		                            $interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
		                        }
		                    }
		
		                    $m=0;
		                    foreach($interval[$md['id']] as $int_k => $int_dates){
		                        $invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
		                        $invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
		
		                        $invoice_period[$md['id']][$m]['invoiced'] = 0;
		                        
		                        if($invoice_data[$member_id][$md['id']]){
    		                        foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
    		                            if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
    		                                $invoice_period[$md['id']][$m]['invoiced'] += 1;
    		                            }
    		                            else
    		                            {
    		                                $invoice_period[$md['id']][$m]['invoiced'] += 0;
    		                            }
    		                        }
		                        }
		                        $m++;
		                    }
			            }
			            
			            
			            
			            $p_list = new PriceList();
			             
			            if($md['end_date'] == "0000-00-00 00:00:00") {
			                if(strtotime( date('Y-m-d',strtotime($md['start_date']))) <= strtotime(date('Y-m-d'))){
			                    $md['end_date'] = date('Y-m-d H:i:s');
			                } else{
			                    $md['end_date'] = $md['start_date'];
			                }
			            }
			            
			            $master_price_list[$md['id']] = $p_list->get_client_list_period(date('Y-m-d', strtotime($md['start_date'])), date('Y-m-d', strtotime($md['end_date'])));
			             
			            $current_pricelist = $master_price_list[$md['id']][0];
			            if($current_pricelist)
			            {
			                $price_memberships_model = new PriceMemberships();
			                $price_memberships = $price_memberships_model->get_prices($current_pricelist['id'], $clientid);
			            }
			            
			            
			            if($md['membership_price'] != "0.00"){
    			            $membership_history[$md['id']]['price'] = $md['membership_price']; 
    			            
			            } else {
			                // get membership price from price list 
    			            $membership_history[$md['id']]['price'] = $price_memberships[$md['membership']]['price']; 
			            }
   			            $membership_history[$md['id']]['price_from_list'] = $price_memberships[$md['membership']]['price']; 
			            
			          
			        } 
			    }
			    
			    // get donations
			    $donations_history = MemberDonations::get_donations_history($clientid,$_GET['id']);
			    $this->view->donation_history = $donations_history;
			}
			$this->view->membership_history = $membership_history;
			$this->view->membership_invoice_period = $invoice_period;
			
			//ispc 1739 p.15
			// get reason of membershipend list => member_membership_end
			$r = array('0' => $this->view->translate('selectreason_end'));
			$ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid , 0 );
			foreach($ReasonOfMembershipEnd_list as $k=>$v){
				$r[$v['id']] = $v['description'];
			}
			$this->view->reasonofmembershipend_array = $r;
			
			//ispc 1739 p.12
			// get payment_method from id
			$r = array('0' => $this->view->translate('please select'));
			$MemberPaymentMethod_list = MemberPaymentMethod::get_list($clientid , 0 );
			foreach($MemberPaymentMethod_list as $k=>$v){
				$r[$v['id']] = $v['description'];	
			}
			$this->view->paymentmethod_array = $r;
			
			//ispc 1739 p.16
			//get files
			if(isset($_GET['id']) && (int)$_GET['id'] > 0){
				$member_files = MemberFiles::get_files($clientid, (int)$_GET['id']);
				$this->view->member_files = $member_files;
			}
			
			
			//ispc 1739
			//get history
			if(isset($_GET['id']) && (int)$_GET['id'] > 0){
				$type = $this->view->type;
				
				$params = array(
						"type" => $this->view->type,
						"genders" => $this->view->genders,
						"mstatuses" => $this->view->statuses,
						"memberships" => $this->view->memberships,
						"reasonofmembershipend" => $this->view->reasonofmembershipend_array,
						"payment_method" => $this->view->paymentmethod_array,
				);
	
				$member_history = Member::get_member_edit_history($clientid, (int)$_GET['id'] , $params);
				
				$this->view->member_history = $member_history['history'];
				$this->view->member_history_translate = $member_history['translate'];
				$this->view->member_history_user_names = $member_history['user_names'];
			}
			
			//ispc 1739
			//get member icons
			if(isset($_GET['id']) && (int)$_GET['id'] > 0){

				$member_icons = new MemberIcons();
				$icons_client = new IconsClient();
				$member_icons_array = $member_icons->get_icons((int)$_GET['id']);
				$client_icons = $icons_client->get_client_icons($clientid, false, 'icons_member');
				
				foreach($client_icons as $k => $v)
				{
					if(array_key_exists($v['id'], $member_icons_array[(int)$_GET['id']]))
					{
						$client_icons[$v['id']]['visible'] = '0';
					}
					else
					{
						$client_icons[$v['id']]['visible'] = '1';
					}
				}
				$this->view->custom_icon_details = $client_icons;
				
			}
			
			//ispc 1842			
			if(isset($_GET['id']) && (int)$_GET['id'] > 0){
				//get sepa settings
				if ($member_sepa_settings = MembersSepaSettings::get_member_settings( (int)$_GET['id'] , $clientid) ){
					$howoften = array_column($member_sepa_settings, 'howoften');
					$this->view->howoften = $howoften[0];
					$this->view->member_sepa_settings = $member_sepa_settings;
				}else{
					$this->view->howoften = "annually";
					$this->view->member_sepa_settings = false;
				}
				
				//print_R($this->view->howoften);die();
				//print_R($member_sepa_settings);die();
				
				//sepa_xml_files_array
				$sepa_xml_files_array = MembersSepaXml :: get_member_sepa_files( (int)$member_id, $clientid );
				if ( $sepa_xml_files_array !==false ){
					$this->view->sepa_xml_files_array = $sepa_xml_files_array;
				}
				
			}

			
						
		}
		
		public function mergememberAction(){
			$clientid = $this->clientid;
			
			if($this->getRequest()->isPost())
			{
				$member_form = new Application_Form_Member();
				$member_form->merge_member($_POST);
				$member_form->assignErrorMessages();
			}
			
			$client_member_array = Member::get_client_members($clientid);
			$members = array();
			$members[0] = $this->view->translate('please select');
			$types['person'] = $this->view->translate('person_type');
			$types['company'] = $this->view->translate('company_type');
			$types['family'] = $this->view->translate('family_type');
			foreach($client_member_array as $k => $v){
				
				$members[$types[$v['type']]][$v['id']] = $v['title'] . " " . $v['first_name'] . " " . $v['last_name']; 
			}
			
			
			
			$this->view->statuses = MemberStatuses::get_client_member_statuses($clientid);
			$this->view->genders = Pms_CommonData::getGender();
			$this->view->members = $members;
		}
		
		public function mergememberundoAction(){
			$clientid = $this->clientid;
			
			if($this->getRequest()->isPost())
			{
				$member_form = new Application_Form_Member();
				$member_form->merge_member_undo($_POST);
				$member_form->assignErrorMessages();
			}
				
			
			$client_member_array = Member::get_client_members($clientid, 0, false, 1);
			$member_array = array();
			$slaves =  array();
			foreach($client_member_array as $k => $v){
				$member_array[$v['id']] = $v;
				if($v['merged_slave'] != 0 && $v['isdelete'] == "1" ){
					$slaves[$v['merged_slave']][$v['id']] =  $v['title']. " " .$v['first_name'] . " " .$v['last_name']; 
				}
			}
		
			$members = array();
			$members[0] = $this->view->translate('please select');
			$types['person'] = $this->view->translate('person_type');
			$types['company'] = $this->view->translate('company_type');
			$types['family'] = $this->view->translate('family_type');
			
			foreach($member_array as $k => $v){
				
				if ($v['merged_parent'] == 1 && $v['isdelete '] == 0){
					$unmerge_possible = MemberHistory::verify_if_unmerge_is_possible($clientid, $v['id']);
					
					if ($unmerge_possible !== true){
						continue;
					}
					
					$members[$types[$v['type']]][$v['id']] = $v['title'] . " " . $v['first_name'] . " " . $v['last_name'] 
						. " => " . 
						implode(" + ", $slaves[$v['id']]);
				}
			}
			if (count($members)<2){
				$this->view->error_unmerge .=  " &raquo; " . $this->view->translate("Nothing left to unmerge");
			}
			$this->view->members = $members;
		}

		public function getmembershippriceAction(){
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    $clientid = $this->clientid;
   		    $p_list = new PriceList();
   		    $return['needed_data'] = '0'; 
   		    
		    if(!empty($_REQUEST['row_id'])){
		        
    		    parse_str($_REQUEST['form_data'], $form_data);
    		    
    		    $membership_details = $form_data['membership'][$_REQUEST['row_id']];
    		    
    		    if(strlen($membership_details['membership']) > 0  && strlen($membership_details['start']) > 0 ){
    		        
    		        $membership_details['start_date'] = date('Y-m-d',strtotime($membership_details['start']));
    		        
    		        if(empty($membership_details['end'])){
    		            
    		            if(strtotime( date('Y-m-d',strtotime($membership_details['start']))) <= strtotime(date('Y-m-d'))){
    		                
    		                $membership_details['end_date'] = date('Y-m-d');
    		                
    		            } else{
    		                
    		                $membership_details['end_date'] = date('Y-m-d',strtotime($membership_details['start']));
    		            }  
    		              		            
    		        } else {
    		            
    		             $membership_details['end_date'] = date('Y-m-d',strtotime($membership_details['end']));
    		        }
    		        
        		    $master_price_list = $p_list->get_client_list_period($membership_details['start_date'],  $membership_details['end_date'] );
        		    $current_pricelist = $master_price_list[0];
    
        		    if($current_pricelist)
        		    {
        		        $price_memberships_model = new PriceMemberships();
        		        $price_memberships = $price_memberships_model->get_prices($current_pricelist['id'], $clientid);
        		        $membership_price = $price_memberships[$membership_details['membership']]['price'];

        		        $return['membership_price'] = $membership_price;
        		    } else {
        		        $return['membership_price'] = 0;
        		    }
        		    
        		    $return['needed_data'] = '1';
    		    }

		    }

		    echo json_encode($return);
		    exit;
		}
		
		public function memberinvoicesAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			if(strlen($_GET['id']) > 0)
			{
				$member_id = $_GET['id'];
			}
			$clientid = $this->clientid;
			if(strlen($_GET['id']) > 0){
			    $member_id = $_GET['id'];
			    $invoices_q = Doctrine_Query::create()
			    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			    ->from('MembersInvoices')
			    ->where("client= ?", $clientid)
			    ->andWhere('member = ?', $member_id)
			    ->andWhere('isdelete = "0"  ');
			    $invoices_array = $invoices_q->fetchArray();
			    $this->view->invoices_array = $invoices_array;
			} 
			
		}
		
		public function memberdetailsAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
//			$this->view->genders = Pms_CommonData::getGender();
			$this->view->genders = Pms_CommonData::getGenderMember();  //ISPC-2442 @Lore 09.10.2019
			
			if(strlen($_GET['id']) > 0)
			{
				$cid = $_GET['id'];
				$edit = "?id=" . $_GET['id'] . "&cid=" . $_GET['cid'];
			}
			else
			{
				$cid = $this->userid;
			}

			$clientid = $this->clientid;
			// get client settings.
			$client_data_array = Client::getClientDataByid($this->clientid);
			$client_data = $client_data_array[0];
			
			$this->view->billing_method = $client_data['membership_billing_method'];
			
			
			// get memberships
			$client_memberships = Memberships::get_memberships($clientid);
			$this->view->memberships = $client_memberships;
			
    		if(strlen($_GET['id']) > 0){
    		    //all invoices for counting
    		    
    		    $member_id = $_GET['id'];
    		    $invoices_q = Doctrine_Query::create()
    		    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
    		    ->from('MembersInvoices')
    		    ->where("client= ?", $clientid)
    		    ->andWhere('member = ?', $member_id);
    		    $invoices_array = $invoices_q->fetchArray();
    		    $this->view->invoices_array = $invoices_array;
    		    
    		    foreach($invoices_array as $k => $invoice){
    		        if($invoice['isdelete'] == '0'){
        		        $invoice_data_s[$member_id][$invoice['id']]['start'] = $invoice['invoice_start']; 
        		        $invoice_data_s[$member_id][$invoice['id']]['end'] = $invoice['invoice_end']; 

        		        $invoice_data[$member_id][$invoice['membership_data']][$invoice['id']]['start'] = strtotime($invoice['invoice_start']); 
        		        $invoice_data[$member_id][$invoice['membership_data']][$invoice['id']]['end'] = strtotime($invoice['invoice_end']); 
    		        }
    		    }
    		  $membership_history_array = Member2Memberships::get_memberships_history($clientid,$_GET['id']);
    		  
    		  if(!empty($membership_history_array)){
    		      
    		      foreach($membership_history_array as $k=>$md){
    		          $membership_history[$md['id']] = $md;
    		          
    		          if($md['start_date'] != "0000-00-00 00:00:00"){
        		          $membership_history[$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
    		          } else{
        		          $membership_history[$md['id']]['start'] = "";
    		          }
    		          
    		          if($md['end_date'] != "0000-00-00 00:00:00"){
        		          $membership_history[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
        		          $membership_history_cal[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
    		          } else{
        		          $membership_history[$md['id']]['end'] = "";
        		          $membership_history_cal[$md['id']]['end'] = date("d.m.Y", time());
    		          }
    		          
    		          
    		          if($client_data['membership_billing_method'] == "membership"){
    		              
        		          if( strtotime($membership_history[$md['id']]['start']) <= strtotime(date("d.m.Y",time())) ) {
        		              // break membership in 12 months interval
        		              $membership_intervals[$md['id']] = Pms_CommonData::generateDateRangeArray(  $membership_history[$md['id']]['start'] ,$membership_history_cal[$md['id']]['end'],"+1 year");
        		              
        		              foreach($membership_intervals[$md['id']] as $k => $start_dates){
        		                  if(count($membership_intervals[$md['id']]) >= 1 && $membership_history[$md['id']]['end'] == "") {
               		                  $msp_intervals[$k]['start'] = $start_dates;
            		                  if($membership_intervals[$md['id']][$k+1]){
                		                  $msp_intervals[$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
            		                  } else{
                		                  $msp_intervals[$k]['end'] = date("d.m.Y", time());
            		                  }
        		                  } else{
               		                  $msp_intervals[$k]['start'] = $start_dates;
            		                  if($membership_intervals[$md['id']][$k+1]){
                		                  $msp_intervals[$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
            		                  } else{
                		                  $msp_intervals[$k]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($start_dates))));
            		                  }
        		                  }
        		              }
        		              
                              $m=0;  
        		              foreach($msp_intervals as $int_k => $int_dates){
            		              $invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
            		              $invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
            		              
                                  $invoice_period[$md['id']][$m]['invoiced'] = 0;
            		              foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
            		                  if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
            		                      $invoice_period[$md['id']][$m]['invoiced'] += 1;
            		                  } else
            		                  {
            		                      $invoice_period[$md['id']][$m]['invoiced'] += 0;
            		                  }
            		              }
            		              $m++;
        		              }
        		          }
    		              
    		          } else { // CALENDAR YEAR METHOD
    		              
        		          if( strtotime($membership_history[$md['id']]['start']) <= strtotime(date("d.m.Y",time())) ) {
        		              // break membership period in calendar year intervals
        		              $start_year = date('Y',strtotime($membership_history[$md['id']]['start']));
        		              $end_year  = date('Y',strtotime($membership_history_cal[$md['id']]['end']));
        		              $i= 0;
        		              $interval = array();
        		              
    		                  for ($i = $start_year; $i <= $end_year; $i++ ){
    		                      if($i ==  $start_year &&  $start_year != $end_year){
    		                          $interval[$i]['start'] = $membership_history[$md['id']]['start'];
    		                          $interval[$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
    		                      } else if($i ==  $end_year && $start_year  != $end_year ){
    		                          $interval[$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
       		                          $interval[$i]['end'] =  $membership_history_cal[$md['id']]['end'];
    		                      } else if($start_year == $end_year ){
    		                          $interval[$i]['start'] = $membership_history[$md['id']]['start'];
       		                          $interval[$i]['end'] =  $membership_history_cal[$md['id']]['end'];
    		                      } else {
    		                          $interval[$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
    		                          $interval[$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
    		                      }
    		                  }
        		                  
    		                  $m=0;
    		                  foreach($interval as $int_k => $int_dates){
    		                      $invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
    		                      $invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
    		                  
    		                      $invoice_period[$md['id']][$m]['invoiced'] = 0;
    		                      foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
    		                          if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
    		                              $invoice_period[$md['id']][$m]['invoiced'] += 1;
    		                          } 
    		                          else
    		                          {
    		                              $invoice_period[$md['id']][$m]['invoiced'] += 0;
    		                          }
    		                      }
    		                      $m++;
        		              }
        		           
                            }
    		          }
    		      }
    		  }
    		}
            $this->view->membership_history = $membership_history;
            $this->view->membership_invoice_period = $invoice_period;			    

			if($this->getRequest()->isPost())
			{
				$client_form = new Application_Form_Member();
				if($_GET['id'] > 0)
				{
					if($client_form->validate($_POST))
					{
					    $a_post = $_POST;
					    $a_post['did'] = $_GET['id'];
						$change = $client_form->UpdateData($a_post);
											

						//#########################################
						// add / edit / delete memberships
						//#########################################
						foreach($a_post['membership'] as $ms_id => $data_ms){
						     
						    if(strlen($data_ms['start']) > 0  ){
						        $ms_start_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['start']));
						    }
						    	
						    if(strlen($data_ms['end']) > 0  ){
						        $ms_end_date[$ms_id] = date('Y-m-d H:i:s',strtotime($data_ms['end']));
						    }
						
						    if($data_ms['custom'] == "0"){
						        // update existing
						        $q = Doctrine_Query::create()
						        ->update('Member2Memberships')
						        ->set('membership','?', $data_ms['membership'])
						        ->set('start_date','?', $ms_start_date[$ms_id])
						        ->set('end_date','?', $ms_end_date[$ms_id])
						        ->set('change_date','?', date('Y-m-d H:i:s'))
						        ->set('change_user','?',  $this->userid)
						        ->where('id= ?', $ms_id)
						        ->andWhere('member= ?', $_GET['id']);
						        $q->execute();
						
						    } else {
 
						            $pfl_cl = new Member2Memberships();
						            $pfl_cl->clientid = $clientid;
						            $pfl_cl->member = $_GET['id'];
						            $pfl_cl->membership = $data_ms['membership'];
						            $pfl_cl->start_date = $ms_start_date[$ms_id];
						            $pfl_cl->end_date = $ms_end_date[$ms_id] ;
						            $pfl_cl->save();
						    }
						}
						
						// delete associations to patients
						if(strlen($a_post['delete_memberships']) > 0 ){
						    $delete_ids = explode(',',$a_post['delete_memberships']);
						
						    if(is_array($delete_ids)){
						
						        $q = Doctrine_Query::create()
						        ->update('Member2Memberships')
						        ->set('isdelete', '1')
						        ->set('change_date','?', date('Y-m-d H:i:s'))
						        ->set('change_user','?',  $this->userid)
						        ->whereIn('id',$delete_ids);
						        $q->execute();
						    }
						}
						
						if($change == 'change')
						{
						    $this->clear_image_details();
							$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
							$this->_redirect(APP_BASE . 'member/memberslist');
						}
						else
						{
							$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
							if(isset($_GET['cid']))
							{
								$get = "&id=" . $_GET['cid'];
							}
							$this->clear_image_details();
							$this->_redirect(APP_BASE . 'member/memberslist?flg=suc' . $get);
						}
					}
					else
					{
						$client_form->assignErrorMessages();
						$this->retainValues($_POST);
					}
				}
 
			}

			if($cid > 0)
			{
				$member = Doctrine::getTable('Member')->find($cid);
				$this->retainValues($member->toArray());
				$memberarray = $member->toArray();
				$clientid = $memberarray['clientid'];
			}
		}

		public function deletememberAction()
		{
		    $previleges = new Pms_Acl_Assertion();
		    $return = $previleges->checkPrevilege('member', $this->userid, 'candelete');
		
		    if(!$return)
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		    }
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    if($this->getRequest()->isPost())
		    {
		        if(count($_POST['member_id']) < 1)
		        {
		            $this->view->error_message = $this->view->translate("selectatlestone");
		            $error = 1;
		        }
		
		        if($error == 0)
		        {
		            if($this->usertype == 'SA')
		            {
		                foreach($_POST['member_id'] as $key => $val)
		                {
		
		                    $mod = Doctrine::getTable('Member')->find($val);
		                    $mod->isdelete = 1;
		                    $mod->save();
		                }
		
		                $this->view->error_message = $this->view->translate('memberdeletedsuccessfully');
		            }
		            else
		            {
		                foreach($_POST['member_id'] as $key => $val)
		                {
		                    if($this->usertype == 'CA')
		                    {
		                        $where = "id=" . $val . " and clientid=" . $this->clientid;
		                    }
		                    else
		                    {
		                        $where = "id=" . $val . " and clientid=" . $this->clientid . " and membertype!='CA'";
		                    }
		
		                    $query = Doctrine_Query::create()
		                    ->select('*')
		                    ->from('Member')
		                    ->where($where);
		
		                    $previlege = $query->execute();
		                    if($previlege->toArray())
		                    {
		                        $mod = Doctrine::getTable('Member')->find($val);
		                        $mod->isdelete = 1;
		                        $mod->save();
		
		                        $this->view->error_message = $this->view->translate("memberdeletedsuccessfully");
		                    }
		                    else
		                    {
		                        $this->_redirect(APP_BASE . "error/previlege");
		                    }
		                }
		            }
		        }
		    }
		
		    $this->_helper->viewRenderer('listmember');
		}		
		
		public function membershiplistAction()
		{
		    $userid = $this->userid;
		    $clientid = $this->clientid;

		    if($this->getRequest()->isPost() && ! empty($_POST['next_auto_member_number']))
		    {
		    	$man_form = new Application_Form_MemberAutoNumber();
		    	if ($man_form->validate($_POST, $this->clientid)) {
		    		
		    		$man_form->insert($_POST, $this->clientid );
		    		
		    	} else {
		    		
		    		$man_form->assignErrorMessages();
		    	}
		    }
		    
		    $medp = new Memberships();
		    $client_memberships = $medp->get_memberships($clientid);
		    $this->view->memberships = $client_memberships;
		    
		    
		    $m_obj = new Member();
		    $highest_nr = $m_obj->get_highest_member_number();
		    $this->view->highest_nr = $highest_nr;
		   		    
		}
				
		public function addmembershipAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    $userid = $this->userid;
		    $clientid = $this->clientid;
		    	
		    	
		    if($this->getRequest()->isPost())
		    {
		        $a_post = $_POST;
		        $membership_form = new Application_Form_Memberships();
		
		        if($membership_form->validate($a_post))
		        {
		            $membership_form->InsertData($a_post);
		            $this->_redirect(APP_BASE . 'member/membershiplist?flg=suc');
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $membership_form->assignErrorMessages();
		        }
		    }
		}
		
		public function editmembershipAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    	
		    $userid = $this->userid;
		    $clientid = $this->clientid;

		    
		    $this->_helper->viewRenderer("addmembership");
		
		    if(!empty($_REQUEST['id']))
		    {
		        $medp = new Memberships();
		        $membership = $medp->membership_details($clientid, $_REQUEST['id']);
		
		        $this->view->membership = $membership;
		    }
		    
		    if($this->getRequest()->isPost())
		    {
		
		        $membership_form = new Application_Form_Memberships();
		
		        if($membership_form->validate($_POST))
		        {
		            if(!empty($_REQUEST['id']))
		            {
		                $membership_form->UpdateData($_POST, $_REQUEST['id']);
		            }
		            else
		            {
		                $membership_form->InsertData($_POST);
		            }
		            $this->_redirect(APP_BASE . 'member/membershiplist?flg=suc');
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $membership_form->assignErrorMessages();
		        }
		    }
		}
		
		public function deletemembershipAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    $this->_helper->viewRenderer('memberships');
		    	
		    $thrash = Doctrine::getTable('Memberships')->find($_REQUEST['id']);
		    $thrash->isdelete = 1;
		    $thrash->save();
		    
		    $this->_redirect(APP_BASE . 'member/membershiplist?flg=suc');
		    $this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		}
				
		private function export_xlsx($columns, $data,$upcoming_birthdays = false)
        {
            $this->xlsBOF();

			$c = 1;
			$this->xlsWriteLabel($line, 0, $this->view->translate('no'));
			foreach($columns as $column)
			{
			    if($column['columnName'] != "image"){
					$this->xlsWriteLabel($line, $c, utf8_decode($this->view->translate($column['columnName'])));
					$c++;
					$column_names[] = $column['columnName']; 
			    }
			}

			$line++;

			$xlsRow = $line;
			foreach($data as $member_id => $row)
			{
				$i++;
				$this->xlsWriteNumber($xlsRow, 0, "$i");
				$t = 1;
				
				
				//TODO-1056 >> associative arrays should NOT be treated as sequential
				foreach ($column_names as $column) {
					 
					$value = isset($row[$column]) ? $row[$column] : "";
				
				/*
				foreach($row as $field => $value)
				{
				   
				    if(in_array($field,$column_names)){
				 */
						$value = str_replace("<br />", "\n", $value);
						$value = str_replace("<hr/>", "\n", $value);
						$value = str_replace("&euro;", '', $value);
						if(is_numeric($value))
						{ //if numeric format as number
							if($field == "firstname" || $field == "lastname")
							{
								//weird stuff going if first name/last name or memo is numeric = true(ISPC-1243)
								$this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
							}
							else
							{
								$this->xlsWriteNumber($xlsRow, $t, $value);
							}
						}
						else
						{
							$this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
						}
						$t++;
				    //}
				}
				$xlsRow++;
			}

			$this->xlsEOF();

			$file = str_replace(" ", "_", $this->view->translate('Members'));
			$fileName = $file . ".xls";

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $fileName);
			exit;
        }
		private function export_xlsx_special($data)
        {
            $column_names = array('member_number','last_name','first_name','member_company');
            
            $this->xlsBOF();

			$c = 1;
			$this->xlsWriteLabel($line, 0, $this->view->translate('no'));
			$this->xlsWriteLabel($line, 1,$this->view->translate('member_number'));
			$this->xlsWriteLabel($line, 2,$this->view->translate('member_company'));
			$this->xlsWriteLabel($line, 3,$this->view->translate('first_name'));
			$this->xlsWriteLabel($line, 4,$this->view->translate('last_name'));
 
			$line++;

			$xlsRow = $line;
			foreach($data as $member_id => $row)
			{
				$i++;
				$this->xlsWriteNumber($xlsRow, 0, "$i");
				$t = 1;
				foreach($row as $field => $value)
				{
				    
				    if(in_array($field,$column_names)){
						$value = str_replace("<br />", "\n", $value);
						$value = str_replace("<hr/>", "\n", $value);
						$value = str_replace("&euro;", '', $value);
						if(is_numeric($value))
						{ //if numeric format as number
							if($field == "first_name" || $field == "last_name")
							{
								//weird stuff going if first name/last name or memo is numeric = true(ISPC-1243)
								$this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
							}
							else
							{
								$this->xlsWriteNumber($xlsRow, $t, $value);
							}
						}
						else
						{
							$this->xlsWriteLabel($xlsRow, $t, utf8_decode($value));
						}
						$t++;
				    }
				}
				$xlsRow++;
			}

			$this->xlsEOF();

			$file = str_replace(" ", "_", $this->view->translate('Members'));
			$fileName = $file . ".xls";

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $fileName);
			exit;
        }
        
        public function export_csv($columns, $data,$upcoming_birthdays = false)
        {
            $file = fopen('php://output', 'w');
            $filename = 'Members.csv';
            
            $csv_header_data = array();
            foreach ($columns as $column) {
                if ($column['columnName'] != "image") {
                    $csv_header_data[$column['columnName']] = $this->view->translate($column['columnName']);
                }
            }
            
            fputcsv($file, $csv_header_data);
            
            foreach ($data as $member => $row) {
            	
            	//TODO-1085
            	$row_values =  array();
            	foreach ($csv_header_data as $k => $column) {
            		$row_values[$k] =  isset($row[$k]) ? $row[$k] : "" ;
            		
            	}
            	
                fputcsv($file, $row_values);
                
            }
            
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-dType: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=" . $filename);
            exit();
        }
        
        private function export_html($columns, $data,$upcoming_birthdays = false)
        {
            $html = "";
            $html .='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr>';
            $html .= '<th width="1%">' . $this->view->translate('no') . '</th>';
            foreach($columns as $column)
            {
                if($column['columnName'] != "image"){
                    
                    if($upcoming_birthdays && $column['columnName'] == "birthd"){
                        $html .= '<th width="10%">' . $this->view->translate("birthdyears") . '</th>';
                    }  else{
                        $html .= '<th width="10%">' . $this->view->translate($column['columnName']) . '</th>';
                    }
                    
                    $columns_ids[] = $column['columnName'];
                }
            }
            $html .= '</tr>';            
            $rowcount = 1;
            foreach($data as $member_id => $row)
            {
                $html .='<tr class="row"><td valign="top">' . $rowcount . '</td>';
                
                //TODO-1056 >> associative arrays should NOT be treated as sequential
                foreach ($columns_ids as $column) {
                	
                	$value = isset($row[$column]) ? $row[$column] : "";
                	
                	$html.= '<td valign="top">' . $value . '</td>';
                	
                }
                /*
                foreach($row as $field => $value)
                {
                    if(in_array($field,$columns_ids)){
                        $html.= '<td valign="top">' . $value . '</td>';
                    }
                }
                */
                
                
                $html .='</tr>';
                $rowcount++;
            }
            $html.="</table>";
        
            $output = "printing";
            
            if($output == "screen")
            {
                $html = '<link href="' . APP_BASE . 'css/members.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;
                echo $html;
                exit;
            }
            elseif($output == "printing")
            {
                $html = '<link href="' . APP_BASE . 'css/members.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;
        
                echo $html;
                echo "<SCRIPT type='text/javascript'>";
                echo "window.print();";
                echo "</SCRIPT>";
                exit;
            }
        
        }
        
        
        /*
        private function set_last_uploaded_file( $action = "action_name", $filepath = false, $qquuid = null, $filename = null )
        {
        	$action =  func_get_arg(0);
        	
        	$this_controller = $this->getRequest()->getControllerName();
        	
        	//delete previous file
        	if ( ! empty ($this->logininfo->{$this_controller}->{$action}->fileupload [1])) {
        		
        		if ( is_file($this->logininfo->{$this_controller}->{$action}->fileupload [1])) {
        			unlink($this->logininfo->{$this_controller}->{$action}->fileupload [1]);
        		}
        	} 
        	
        	$this->logininfo->{$this_controller}->{$action}->fileupload = func_get_args();
        	
        }
        
        //optional extra validation by some id
        private function get_last_uploaded_file( $action = "action_name", $qquuid = false )
        {
        	$action =  func_get_arg(0);
        	
        	$result = false;

        	$this_controller = $this->getRequest()->getControllerName();
        	 
        	if ( ! empty($this->logininfo->{$this_controller}->{$action}->fileupload)        	
        		&& ( ! empty($this->logininfo->{$this_controller}->{$action}->fileupload [1]))
        		&& ( is_file($this->logininfo->{$this_controller}->{$action}->fileupload [1]))
        	) {
        		
        		if ( $qquuid === false
        			|| in_array($qquuid, $this->logininfo->{$this_controller}->{$action}->fileupload)
        		) {
        			$file = $this->logininfo->{$this_controller}->{$action}->fileupload ;	
        			$file[0] =  
        			$result = array(
        					"action" => $file[0],
        					"filepath" => $file[1],
        					"qquuid" => $file[2],
        					"filename" => $file[3],		
        			);
        			
        		}
        	} 
        	 
        	return $result;
        }
        
        
        
        //@todo: translate the errors if you don't validate in js
        private function upload_one_qq_file( $params = array(
        		"allowed_file_extensions" => array('pdf'),
        		"max-filesize" => 102400000,
        		"action" => "upload_one_qq_file"
        		
        ))
        {
        	//invalidate all other previous fileupload from session       	
        	if ( isset($params['action'])) {        		
        		self::set_last_uploaded_file( $params['action'] );        		
        	}
     	
        	$extension = explode(".", $_FILES['qqfile']['name']);        	
        	$extension = strtolower($extension[count($extension) -1]);
        	
        	
        	if ( isset($params['allowed_file_extensions']) && ! in_array($extension , $params['allowed_file_extensions'] ) ) 
        	{
        		$response = array("success"=>false, "error"=>"invalid file extension, only: ". implode(" , ",$params['allowed_file_extensions']));
        		return $response;
        	}
        	
        	if ( isset($params['max-filesize']) && filesize($_FILES['qqfile']['tmp_name']) > $params['max-filesize'] ) 
        	{
        		$response = array("success"=>false, "error"=>"max-filesize: ". $params['max-filesize'] . " yours:" . filesize($_FILES['qqfile']['tmp_name']));
        		return $response;
        	}
        	
        	$qqfilename = Pms_CommonData::filter_filename($_POST['qqfilename'], true);
        	$qquuid = $_POST['qquuid'];
        	        	
        	$filename = trim(time(). '.' . $extension);
        	
        	//create unique new folder in PDF_PATH
        	$temp_folder = (Pms_CommonData::uniqfolder_v2( PDF_PATH , "email_attachment_"));
        	$unique_folder = pathinfo($temp_folder, PATHINFO_BASENAME);
        	
        	//forced return ... something went wrong 
        	if( ! is_dir($temp_folder)) {
        		
        		$response = array("success"=>false, "error"=>"cannot creat temp folder, inform admin" , "qqfilename" => $qqfilename , "qquuid" => $qquuid);
        		return $response;
        	}
        		
        	
        	$full_path_filename = $temp_folder . "/". $filename;
        	
        	if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $full_path_filename))
        	{
        		$response = array('success' => true, "filename"=>$filename , "full_path_filename"=>$full_path_filename, "qqfilename" => $qqfilename, "qquuid" => $qquuid);

        		//remember to session our new file for later ftp upload
        		if ( isset($params['action'])) {
        			
        			self::set_last_uploaded_file( $params['action'] ,  $full_path_filename,  $qquuid, $qqfilename);        			
        		}
        		       		
        	} else {
        		$response = array("success"=>false, "error"=>"cannot move to our folder", "qqfilename" => $qqfilename, "qquuid" => $qquuid);
        	}
        	
        	return $response;
        }
        
        */
        
        
        
        public function sendemail2membersAction()
        {
            
            $clientid = $this->clientid;
            $userid = $this->userid;
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
            	
            
            //upload_file_attachment from ajax and exit
            if ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "upload_file_attachment")
            {
            	
            	$fileupload_result = $this->upload_qq_file( array(
            			"allowed_file_extensions" => array('pdf'),
            			"max-filesize" => 5 * 1000 * 1024,
            			"action" => "sendemail2members",
            	));
            	
            	$this->_helper->json->sendJson($fileupload_result);
            	exit;
            }
            
            // get client members data
            $client_member_array = Member::get_client_members($clientid);
            
            $emailValidator = new Zend_Validate_EmailAddress();
            
            foreach($client_member_array as $cm => $mv){
                $members_array[$mv['id']] = $mv;
                if(empty($mv['email'])  || strlen($mv['email']) == 0 || ! $emailValidator->isValid($mv['email'])){
                    $no_email_members_array[] = $mv['id'];
                }
            }
            // get curent user details
            $user_data_array = Pms_CommonData::getUserData($userid);
            $user_data = $user_data_array[0];
            
            if(strlen($user_data['emailid']) == 0 || empty($user_data['emailid'])){
//                 $this->_redirect(APP_BASE . "member/memberslist?flg=no_email");
//                 exit;
            }
            $email['sender'] = $user_data;

            
            if($this->getRequest()->isPost())
            {
                if($_POST['transfer2sendemail'] == "1" ){
                    if($_POST['members_ids']){
                     $members_ids = $_POST['members_ids'];
                    }
                } else {
                    $email_form = new Application_Form_MemberEmailsLog();
                    $post = $_POST;

                   
                    
                    $members_ids = explode(',',$post['email']['initial_members']);
                    $validation = $email_form->validate($post['email']);
                    
                    if($validation){
                    	
                    	$attachments = $this->get_last_uploaded_file("sendemail2members", $post['email']['attachment']);
                    	$post['email']['attachment'] = $attachments[$post['email']['attachment']];
                    	
  
                        $email_form->save2email_log($post['email']); 
                        
                        //invalidate all attachment in sendemail2members
                        $this->set_last_uploaded_file('sendemail2members');
                        
                        $this->_redirect(APP_BASE . "member/memberslist?flg=email_sent");
                        exit;
                        
                    } else{
                    	
                    	//invalidate all attachment in sendemail2members
                    	$this->set_last_uploaded_file('sendemail2members');
                    	
                        $email_form->assignErrorMessages();
                        $this->retainValues($_POST);
                    }
                }
            }
            
            foreach($members_ids as $k => $member_id){
                if(!in_array($member_id, $no_email_members_array)){
                    $member_data[$member_id] = $members_array[$member_id];
                    $email['recipients']['data'][$member_id] = $members_array[$member_id];
                    $email['recipients']['ids'][] = $member_id;
                } else {
                	$email['no_email_members_array'][$member_id] = $members_array[$member_id];
                }
            }
            
            Member::beautifyName($members_array);
            Member::beautifyName($email['no_email_members_array']);
            
            $email['client_members'] = $members_array;

            $this->view->email_data = $email;

            $tokens_obj = new Pms_Tokens('MemberEmail');
            $email_tokens = $tokens_obj->getTokens4Viewer();
            
            $this->view->email_tokens = $email_tokens['prefixed_array_viewer'];
            
            
        }
        
        public function sendemail2membershistoyAction()
        {
        	
        	$has_edit_permissions = Links::checkLinkActionsPermission();
        	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        	{
        		$this->redirect(APP_BASE . "error/previlege" , array("exit" => true));
        	}
        	 

        	//this IF = the dataTables ajax request
        	if ($this->getRequest()->isXmlHttpRequest() && $_POST['action'] == "fetch_emails_list")
        	{

        		//datatables settings
				$offset = (!empty($_REQUEST['start']))  ? (int)$_REQUEST['start']  : 0 ;
				$length = (!empty($_REQUEST['length'])) ? (int)$_REQUEST['length'] : 50 ;
// 				$filter_by_member = (!empty($_REQUEST['filter_by_member'])) ? (int)$_REQUEST['filter_by_member'] : 0 ;
				$filter_by_member = false;
        		
				if (trim($_REQUEST['sSearch']) != ''){
					$filtered_memberids = Member::search_memberids($_REQUEST['sSearch'] , $this->clientid);
					if ( empty($filtered_memberids) ) {
						
						$this->returnDatatablesEmptyAndExit(); // return NO RESULT 4 dataTables
					}
					$filter_by_member = true;
				}
				
				$filters = array();
				$filters['limit'] = $length;
				$filters['offset'] = $offset;
				
				
				//count emails 
				$email_log = new MemberEmailsLog();
				
				$emails_count_filteres = 
				$emails_count = $email_log->get_grouped_log_count( $this->clientid );
				
				if ( $filter_by_member ) {
					
					$filters['recipient'] =  $filtered_memberids;
					$emails_count_filteres = $email_log->get_grouped_log_filtered_count( $this->clientid , $filters);
				}
				
        		//get emails groupped as single rows
        		$emails = $email_log->get_grouped_log( $this->clientid , $filters);

        		
         
        		$search_by_batch = array();
    		    foreach($emails as $k=> $email_inf){
    		        if($email_inf['batch_id'] == $email_inf['my_group_by']){
    		            $search_by_batch[] = $email_inf['batch_id'];
    		        } 
    		    }
    		    
    		    $recipientrs2batch_id = array();
    		    $batch_log_arr = Doctrine_Query::create()
    		    ->select()
    		    ->from('MemberEmailsLog')
    		    ->where('clientid = ?', $this->clientid)
    		    ->fetchArray();
    		    
    		    $all_recipients = array();
    		    foreach($batch_log_arr as $kb => $blog){
    		        $all_recipients[] = $blog['recipient'];
    		        if(!empty($blog['batch_id'])){
        		        $recipientrs2batch_id[$blog['batch_id']][] = $blog['recipient'];
    		        }
    		    }
        		
    		    if($this->userid == "338CC"){
                    echo "<pre>";
                    print_r($all_recipients);
                    print_r($recipientrs2batch_id);
        		    exit;
    		    }
    		    
        		$results = array();
        		
        		if (! empty($emails)) {
        			
        			$users = array_column($emails, 'sender');
        			$user_id_arr = array_unique($users);
        			$user_names_array = User::getUsersNiceName($user_id_arr, $this->clientid);
        			 
        			if(!empty($all_recipients)){
        			    $members = $all_recipients;
        			} else{
            			$members = array_column($emails, 'recipients');
        			}
        			$members_id_arr = array_unique(explode(',', implode(',', $members)));
        			$members_name_array = Member::getMembersNiceName( $members_id_arr, $this->clientid );
        			
        			$attachment_id_arr = array_unique(array_column($emails, 'attachment_id'));
        			$attachment_id_arr = array_filter( $attachment_id_arr, 'strlen' );
        			$attachment_name_array = MemberFiles::get_files_by_id($attachment_id_arr);
        		      			
        			
        			$sendemail2membershistoy_lang = $this->view->translate('sendemail2membershistoy_lang');
        			$no_of_recipients_text = $sendemail2membershistoy_lang['no_of_recipients_text'];
        			
        			
        			foreach($emails  as $row) {
        				
        				if(!empty($recipientrs2batch_id[$row['batch_id']])){
        				    $members_id_arr = $recipientrs2batch_id[$row['batch_id']];
        				} else{
            				$members_id_arr = explode(',', $row['recipients']);
        				    
        				}
        				$email_recipients = array();
        				foreach ($members_id_arr as $member_id) {
        					$email_recipients[] = $members_name_array[$member_id]['nice_name'];
        				}
        				
        				
        				$data =  array(
        						"debugcolumn" 				=> "2",
        						
        						"entrydate" 				=> date("d.m.Y", strtotime($row['date'])),
        						"email_sent_by" 			=> $user_names_array[ $row['sender'] ] ['nice_name'],
        						
        						"email_subject" 			=> $row['title_plain'],
        						"email_content" 			=> $row['content_plain'],
        						
        						"email_attachment_id" 		=> $row['attachment_id'],
        						"email_attachment_filename" => $attachment_name_array[$row['attachment_id']]["file_showname"],
        						
        						"email_recipients"		 	=> $email_recipients,
        						"no_of_recipients"	=> sprintf($no_of_recipients_text , count($email_recipients)),
        						
        				);
        				
        				$results[] = $data;
        			}
        			
        			
        		}
        		
	        
	
	        	$response = array();
	        	$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	        	$response['recordsTotal'] = $emails_count;//count($results);//$full_count;
	        	
// 	        	$response['iTotalRecords'] = 0;
// 	        	$response['iTotalDisplayRecords'] = 0;
// 	        	$response['aaData'] = $results;
	        	
	        	$response['recordsFiltered'] = $emails_count_filteres;//count($results);
	        	$response['data'] = $results; //empty($results) ? array() : $results;
	        		
	        	
	        		
	        	ob_end_clean();	ob_start();
	        	
	        	$this->_helper->json->sendJson($response);
	        	
        	} else {
        		
        		//get filters 
        		//changed to search input, so not used, remais here for example
        		/*
        		$member_id_arr = Member::get_ids_by_clientid( $this->clientid );
        		$user_names_array = Member::getMembersNiceName( $member_id_arr , $this->clientid );

        		$user_names_filter = array();
        		$user_names_filter['0'] = 'Alle';
        		
        		foreach ( $user_names_array as $row ) {
        			$user_names_filter [ $row['id'] ] = $row['nice_name'] ;
        		}
        		
        		
        		$this->view->user_names_filter = $user_names_filter;
        		*/
        		$this->view->user_names_filter = array();

        	}
        
        	
        	
        }
        
        private function export_pdf($post, $pdfname, $filename)
        {
            
            $clientid = $this->clientid;
            $clientinfo = Pms_CommonData::getClientData($this->clientid);
            
            $post['members'] = $post;
            
            $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
            $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
    

            //defaults with header
            $pdf->setDefaults(true);
            $pdf->setImageScale(1.6);
            //reset margins
            $pdf->SetMargins(10, 5, 10);
            $pdf->setPrintFooter(false);
  
            
            if($pdfname == 'Avery105x48')
            {
                $bottom_margin = '3';
                $pdf->setDefaults(true, 'P', $bottom_margin);
                $pdf->SetMargins(0, 4.5, 0);
                $pdf->SetFont('', '', 9);
            }
            
            if($pdfname == 'Avery70x35')
            {
                $bottom_margin = '5';
                $pdf->setDefaults(true, 'P', $bottom_margin);
                $pdf->SetMargins(0, 8.5, 0);
                $pdf->SetFont('', '', 9);
            }

            switch($pdfname)
            {
            
                case 'Avery70x35':
//                     $background_type = '59';
                        $background_type = false;
                    break;
                case 'Avery105x48':
//                     $background_type = '60';
                        $background_type = false;
                    break;
                default:
                    $background_type = false;
                    break;
            }
            
            $pdf->HeaderText = false;
            
            if($background_type != false)
            {
                $bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
                if($bg_image !== false)
                {
                    $bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
                    if(is_file($bg_image_path))
                    {
                        $pdf->setBackgroundImage($bg_image_path);
                    }
                }
            }
            
            $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
            if($pdfname != 'anlage5' || $pdfname != 'Palliativ-Notfallbogen' || $pdfname != 'Stammblatt4' || $pdfname != 'therapyplan')
            {
                $html = preg_replace('/style=\"(.*)\"/i', '', $html);
            }
    
            $pdf->setHTML($html);
    

            ob_end_clean();
            ob_start();
            $pdf->toBrowser($pdfname . '.pdf', "d");
            exit;
        }

        public function listtemplatesAction()
        {
            
            $userid = $this->userid;
            $clientid = $this->clientid;
        
            if($_REQUEST['flg'])
            {
                if($_REQUEST['flg'] == 'err')
                {
                    $this->view->error_mesage = $this->view->translate('error');
                }
                else if($_REQUEST['flg'] == 'inv')
                {
                    $this->view->error_mesage = $this->view->translate('invalid_template');
                }
                else if($_REQUEST['flg'] == 'suc')
                {
                    $this->view->success_message = $this->view->translate('success');
                }
                else if($_REQUEST['flg'] == 'del_suc')
                {
                    $this->view->delete_message = $this->view->translate('deletedsuccessfully');
                }
            }
        }
        
        public function fetchtemplatelistAction()
        {
            
            $userid = $this->userid;
            $clientid = $this->clientid;
            $user_type = $this->usertype;
        
            $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
        
            $columnarray = array(
                "crd" => "create_date",
                "id" => "id",
                "ti" => "title",
                "ft" => "file_type"
            );
        
            $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
            $this->view->order = $orderarray[$_REQUEST['ord']];
            $this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
        
            $client_users_res = User::getUserByClientid($clientid, 0, true);
        
            foreach($client_users_res as $k_user => $v_user)
            {
                $client_users[$v_user['id']] = $v_user['first_name'] . ' ' . $v_user['last_name'];
            }
        
            $this->view->client_users = $client_users;
        
            if($clientid > 0)
            {
                $where = ' and clientid=' . $this->clientid;
            }
            else
            {
                $where = ' and clientid=0';
            }
        
            if($user_type == "CA" || $user_type == "SA")
            {
                $this->view->reveal_actions_col = '1';
            }
            else
            {
                $this->view->reveal_actions_col = '0';
            }
        
            $fdoc = Doctrine_Query::create()
            ->select('count(*)')
            ->from('MemberLetterTemplates')
            ->where("isdeleted = 0 " . $where)
            ->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
        
            //used in pagination of search results
            if(!empty($_REQUEST['val']))
            {
                $fdoc->andWhere("(title != '' OR file_type != '')");
                $fdoc->andWhere("(title like '%" . trim($_REQUEST['val']) . "%' OR file_type like '%" . trim($_REQUEST['val']) . "%')");
            }
            $fdocarray = $fdoc->fetchArray();
        
            $limit = 50;
            $fdoc->select('*');
                $fdoc->where("isdeleted = 0 " . $where . "");
            if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
            {
                $fdoc->andWhere("(title != '' or file_type != '')");
                $fdoc->andWhere("(title like '%" . trim($_REQUEST['val']) . "%' OR file_type like '%" . trim($_REQUEST['val']) . "%')");
            }
            $fdoc->limit($limit);
            $fdoc->offset($_REQUEST['pgno'] * $limit);
        
            $fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
        
        
            $this->view->{"style" . $_GET['pgno']} = "active";
            if(count($fdoclimit) > '0')
            {
                $grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "member_letter_templateslist.html");
                $this->view->templates_grid = $grid->renderGrid();
                $this->view->navigation = $grid->dotnavigation("member_letter_templatesnavigation.html", 5, $_REQUEST['pgno'], $limit);
            }
            else
            {
                //no items found
                $this->view->templates_grid = '<tr><td colspan="5" style="text-align:center;">' . $this->view->translate('noresultfound') . '</td></tr>';
                $this->view->navigation = '';
            }
        
            $response['msg'] = "Success";
            $response['error'] = "";
            $response['callBack'] = "callBack";
            $response['callBackParameters'] = array();
            $response['callBackParameters']['templateslist'] = $this->view->render('member/fetchtemplatelist.html');
        
            echo json_encode($response);
            exit;
        }
        
        public function gettemplateAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
        
            
            $clientid = $this->clientid;
        
            if($_REQUEST['tid'])
            {
                $template_id = trim(rtrim($_REQUEST['tid']));
                $template_data = MemberLetterTemplates::get_template($clientid, $template_id, '1');
                $file_check_path = 'member_letter_templates/' . $template_data['0']['file_path'];
        
                if($template_data && is_file($file_check_path))
                {
                    $this->_redirect(APP_BASE . 'member_letter_templates/' . $template_data['0']['file_path']);
                    exit;
                }
                else
                {
                    $this->_redirect(APP_BASE . "error/nofile");
                }
            }
        }
        
        public function templateuploadAction()
        {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
        
            
            $clientid = $this->clientid;
        
            if($_REQUEST['op'] == 'memberlettertemplate')
            {
                $this->resetuploadvars();
            }
        
            $extension = explode(".", $_FILES['qqfile']['name']);
        
            if($_REQUEST['op'] == 'memberlettertemplate')
            {
                $timestamp_filename = time() . "_file";
                $path = MEMBER_LETTER_TEMPLATE_PATH;
                $dir = $clientid;
        
                //create first directory in /public
                while(!is_dir($path))
                {
                    mkdir($path);
                    chmod($path, "0755");
                    if($i >= 50)
                    {
                        exit; //failsafe
                    }
                    $i++;
                }
        
                //create second client directory in first dir /public/first_dir/clientid
                while(!is_dir($path . '/' . $dir))
                {
                    mkdir($path . '/' . $dir);
                    chmod($path, "0755");
                    if($i >= 50)
                    {
                        exit; //failsafe
                    }
                    $i++;
                }
            }
        
        
            $folderpath = $dir;
        
            $filename = $path . "/" . $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
        
            //file name
            $_SESSION['template_filename'] = trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
        
            //file path
            $_SESSION['template_filepath'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
        
            //file extension
            $_SESSION['template_filetype'] = $extension[count($extension) - 1];
        
            if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename))
            {
                echo json_encode(array('success' => true));
                exit;
            }
        }

        public function addtemplateAction()
        {
            
            $userid = $this->userid;
            $clientid = $this->clientid;
        
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
            	
            	
            $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
        
            if($this->getRequest()->isPost())
            {
                $upload_form = new Application_Form_MemberLetterTemplate();
        
                $post = $_POST;
                $post['template_filename'] = $_SESSION['template_filename'];
                $post['template_filetype'] = $_SESSION['template_filetype'];
                $post['template_filepath'] = $_SESSION['template_filepath'];
        
                if($upload_form->validate($post))
                {
                    $upload_form->insert_template_data($post);
                    $this->_redirect(APP_BASE . 'member/listtemplates?flg=suc_add');
                }
                else
                {
                    $upload_form->assignErrorMessages();
                    $this->retainValues($_POST);
                }
        
                $this->resetuploadvars();
            }
        }
        
        public function edittemplateAction()
        {
            
            $userid = $this->userid;
            $clientid = $this->clientid;
            $upload_form = new Application_Form_MemberLetterTemplate();
            	
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
            	
            $this->view->default_recipient_values = Pms_CommonData::template_default_recipients();
        
            if($_REQUEST['tid'] > '0')
            {
                $template_id = trim(rtrim($_REQUEST['tid']));
        
                if($this->getRequest()->isPost())
                {
                    $post = $_POST;
                    $post['template_id'] = $template_id;
        
                    //used to cleanup in edit mode(file uploaded but check was deselected)
                    $post['template_filepath'] = $_SESSION['template_filepath'];
                    $post['template_filetype'] = $_SESSION['template_filetype'];
        
                    //reset upload vars(if any) if change template is not checked
                    if($post['change_file'] != '1')
                    {
                        $this->resetuploadvars();
                    }
        
                    if($upload_form->validate($post))
                    {
                        $upload_form->update_template_data($clientid, $post);
                        $this->_redirect(APP_BASE . 'member/listtemplates?flg=suc_edt');
                        exit;
                    }
                    else
                    {
                        $this->retainValues($post);
                    }
                    $this->resetuploadvars();
                }
        
        
                //load data
                $template_data = MemberLetterTemplates::get_template($clientid, $template_id);
                if($template_data)
                {
                    $this->retainValues($template_data[0]);
                }
                else
                {
                    $this->redirect(APP_BASE . 'member/listtemplates?flg=inv');
                    exit;
                }
            }
            else
            {
                $this->redirect(APP_BASE . 'member/listtemplates?flg=inv');
                exit;
            }
        }
        
        public function deletetemplateAction()
        {
            
            $userid = $this->userid;
            $clientid = $this->clientid;
        
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
            	
            $this->_helper->viewRenderer->setNoRender();
        
            $fdoc = Doctrine::getTable('MemberLetterTemplates')->findOneByIdAndClientid($_REQUEST['tid'], $clientid);
            if($fdoc)
            {
                $fdoc->isdeleted = 1;
                $fdoc->save();
        
                $this->redirect(APP_BASE . 'member/listtemplates?flg=del_suc');
                exit;
            }
            else
            {
                $this->redirect(APP_BASE . 'member/listtemplates?flg=del_err');
                exit;
            }
        }
        
        // actual function which is generating blank letter
        //TODO-3668 Ancuta 14.12.2020 - changed to public 
        public function export_letters($export_data)
        {
            //spl_autoload_register(array('AutoLoader', 'autoloadPdf')); // Alex+Ancuta- commented on 18.11.2019- New phpdocx 9.5 added
            //$clientid = $this->clientid;
            
            $clientid = isset($export_data['clientid']) && !empty($export_data['clientid']) ? $export_data['clientid'] :$this->clientid;
            $userid = isset($export_data['userid']) && !empty($export_data['userid']) ? $export_data['userid'] :$this->userid;


            if(strlen($export_data['sortby'])){
                $sortby = $export_data['sortby'];
            } else{
                $sortby = "first_name";
            }
            if(strlen($export_data['sortdir'])){
                $sortdir = $export_data['sortdir'];
            } else{
                $sortdir = "ASC";
            }
            
            $members_data = Member::get_all_members_details($export_data['members_ids'],$sortby,$sortdir,$clientid);//TODO-3719 Ancuta - 12.01.2021 added clientid
            if (count($members_data) > '0') {
                // batch temp folder
                if(!is_dir(PDFDOCX_PATH))
                {
                    while(!is_dir(PDFDOCX_PATH))
                    {
                        mkdir(PDFDOCX_PATH);
                        if($i >= 50)
                        {
                            exit; //failsafe
                        }
                        $i++;
                    }
                }
                
                if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
                {
                    while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
                    {
                        mkdir(PDFDOCX_PATH . '/' . $clientid);
                        if($i >= 50)
                        {
                            exit; //failsafe
                        }
                        $i++;
                    }
                }
                
                $path = PDFDOCX_PATH . '/' . $clientid;
                $i = 0;
                $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
                while(!is_dir($path . '/' . $dir))
                {
                    $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
                    mkdir($path . '/' . $dir);
                    if($i >= 50)
                    {
                        exit; //failsafe
                    }
                    $i++;
                }
                $batch_temp_folder = $dir;
                                
                // load template data
                if($export_data['template_id'] != "0"){
                    $template_data = MemberLetterTemplates::get_template($clientid,$export_data['template_id']);
                } else{
                    $template_data = MemberLetterTemplates::get_letter_template($clientid);
                }
                
               
                
                foreach ($members_data as $member_id => $member_data) {
                    // setup tokens
                    
                    // user token
                    $tokens_multi[$member_id]['member_firstname'] = (strlen($member_data['first_name']) > '0' ? html_entity_decode($member_data['first_name'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['member_surname'] = (strlen($member_data['last_name']) > '0' ? html_entity_decode($member_data['last_name'], ENT_QUOTES, 'utf-8') : '');

                    $tokens_multi[$member_id]['member_company'] = (strlen($member_data['member_company']) > '0' ? html_entity_decode($member_data['member_company'], ENT_QUOTES, 'utf-8') : '');
                    
                    //NEW  - 16.02.2016
                    $tokens_multi[$member_id]['member_salutation'] = (strlen($member_data['salutation']) > '0' ? html_entity_decode($member_data['salutation'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['member_title'] = (strlen($member_data['title']) > '0' ? html_entity_decode($member_data['title'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['member_geb_datum'] =  (strlen($member_data['birthd']) > '0' ? html_entity_decode($member_data['birthd'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['member_street'] = (strlen(trim(rtrim($member_data['street1']))) > '0' ? html_entity_decode(trim(rtrim($member_data['street1'])), ENT_QUOTES, 'utf-8') : '') ;
                    $tokens_multi[$member_id]['member_zip']= (strlen(trim(rtrim($member_data['zip']))) > '0' ? html_entity_decode(trim(rtrim($member_data['zip'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['member_city'] = (strlen(trim(rtrim($member_data['city']))) > '0' ? html_entity_decode(trim(rtrim($member_data['city'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['membership_start_date'] = (strlen(trim(rtrim($member_data['membership_start_date']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_start_date'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['membership_end_date'] = (strlen(trim(rtrim($member_data['membership_end_date']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_end_date'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['membership_type'] = (strlen(trim(rtrim($member_data['membership_type']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_type'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['membership_price'] = (strlen(trim(rtrim($member_data['membership_price']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_price'])), ENT_QUOTES, 'utf-8') : '');
                    
                    //ISPC-2152 30.01.2018 
                    $tokens_multi[$member_id]['mitgliedschaft_ende_grund'] = 
                    $tokens_multi[$member_id]['membership_end_reason'] = ! empty($member_data['membership_end_reasonid_description']) ? html_entity_decode(Pms_CommonData::mb_trim($member_data['membership_end_reasonid_description']), ENT_QUOTES, 'utf-8') : '';
                    
                    
                    //NEW  - 16.02.2016
                    $tokens_multi[$member_id]['mitglied_vorname'] = (strlen($member_data['first_name']) > '0' ? html_entity_decode($member_data['first_name'], ENT_QUOTES, 'utf-8') : '');;
                    $tokens_multi[$member_id]['mitglied_nachname'] = (strlen($member_data['last_name']) > '0' ? html_entity_decode($member_data['last_name'], ENT_QUOTES, 'utf-8') : '');;
                    
                    $tokens_multi[$member_id]['mitglied_anrede'] = (strlen($member_data['salutation']) > '0' ? html_entity_decode($member_data['salutation'], ENT_QUOTES, 'utf-8') : '');;
                    $tokens_multi[$member_id]['mitglied_titel'] = (strlen($member_data['title']) > '0' ? html_entity_decode($member_data['title'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitglied_geb_datum'] = (strlen($member_data['birthd']) > '0' ? html_entity_decode($member_data['birthd'], ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitglied_straße'] = (strlen(trim(rtrim($member_data['street1']))) > '0' ? html_entity_decode(trim(rtrim($member_data['street1'])), ENT_QUOTES, 'utf-8') : '') ;
                    $tokens_multi[$member_id]['mitglied_plz']= (strlen(trim(rtrim($member_data['zip']))) > '0' ? html_entity_decode(trim(rtrim($member_data['zip'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitglied_ort'] = (strlen(trim(rtrim($member_data['city']))) > '0' ? html_entity_decode(trim(rtrim($member_data['city'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitgliedschaft_start'] = (strlen(trim(rtrim($member_data['membership_start_date']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_start_date'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitgliedschaft_ende'] = (strlen(trim(rtrim($member_data['membership_end_date']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_end_date'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitgliedschaft'] = (strlen(trim(rtrim($member_data['membership_type']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_type'])), ENT_QUOTES, 'utf-8') : '');
                    $tokens_multi[$member_id]['mitgliedschaft_preis'] = (strlen(trim(rtrim($member_data['membership_price']))) > '0' ? html_entity_decode(trim(rtrim($member_data['membership_price'])), ENT_QUOTES, 'utf-8') : '');
                    
                    $tokens_multi[$member_id]['mandatsreferenznummer'] = (strlen(trim(rtrim($member_data['mandate_reference']))) > '0' ? html_entity_decode(trim(rtrim($member_data['mandate_reference'])), ENT_QUOTES, 'utf-8') : '');
                    
                    $tokens_multi[$member_id]['member_id'] = $member_id;
                    
                    //ISPC-1236 Lore 05.02.2020
                    $tokens_multi[$member_id]['membersbank'] = (strlen(trim(rtrim($member_data['bank_name']))) > '0' ? html_entity_decode(trim(rtrim($member_data['bank_name'])), ENT_QUOTES, 'utf-8') : '');
                    
    
					//ispc 1739
                    $briefanrede_mitglied = trim($member_data['salutation']);
                    if(trim($member_data['title']) != ''){
                    	$briefanrede_mitglied .= " " . trim($member_data['title']);
                    }
                    if(trim($member_data['first_name']) != ''){
                    	//$briefanrede_mitglied .= " " . trim(mb_convert_case(mb_strtolower($member_data['first_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));
                        //$briefanrede_mitglied .= " " . trim($member_data['first_name']);        //TODO-3847 Lore 10.02.2021   //TODO-3272 Lore 15.02.2021
                    }
                    if(trim($member_data['last_name']) != ''){
                    	//$briefanrede_mitglied .= " " . trim(mb_convert_case(mb_strtolower($member_data['last_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));
                        $briefanrede_mitglied .= " " . trim($member_data['last_name']);        //TODO-3847 Lore 10.02.2021
                    }
                    if (trim($briefanrede_mitglied) != ""){
                        //$briefanrede_mitglied = $this->view->translate('dear_sir_madam') . " ". trim($briefanrede_mitglied);
                        //TODO-3847 Lore 10.02.2021
                        $tr = new Zend_View_Helper_Translate();
                        if($member_data['gender'] == $tr->translate("female") ){
                            $briefanrede_mitglied = $this->view->translate('dear_madam') . " ". trim($briefanrede_mitglied);
                        } else {
                            $briefanrede_mitglied = $this->view->translate('dear_sir_madam') . " ". trim($briefanrede_mitglied);
                        }
                    }
                   	$tokens_multi[$member_id]['briefanrede_mitglied'] = (strlen(trim(rtrim($member_data['salutation_letter']))) > 0 
                   		? 
                   		html_entity_decode(trim($member_data['salutation_letter']), ENT_QUOTES, 'utf-8') 
                   		: 
                   		html_entity_decode($briefanrede_mitglied, ENT_QUOTES, 'utf-8')
                   	);
                   	$recipient_family = '';
                    if($member_data['type'] == 'family'){
                    	if( $family_child = MemberFamily::getMemberFamilyDetails($member_id) )
                    	{                    	
                    		$family_child = array_values($family_child);
                    		$family_child = $family_child[0];
                    		$recipient_family = ",";
                    		if(!empty($family_child['title'])){
                    			$recipient_family .= " " . trim($family_child['title']);
                    		}
                    		if(!empty($family_child['first_name']) || !empty($family_child['last_name'])){
                    			$recipient_family .= " " . trim($family_child['first_name']) . ' ' . trim($family_child['last_name']);
                    		}
                    	}
                    }
                   	
                    $recipient = array();
                    $recipient_company = trim(rtrim($member_data['member_company']));
                    $recipient_title = trim(rtrim($member_data['title']));
                    $recipient_name =  trim($member_data['first_name']) . ' ' . trim($member_data['last_name']);
                    $recipient_street = trim(rtrim($member_data['street1']));
                    $recipient_zip = trim(rtrim($member_data['zip']));
                    $recipient_city = trim(rtrim($member_data['city']));
                    
                    if ($recipient_company) {
                        $recipient[] = $recipient_company;
                    }
                    
                    if ($recipient_name) {
                        
                        if($recipient_title){
                            $recipient[] = $recipient_title.' '.$recipient_name . $recipient_family;
                        } else {  
                            $recipient[] = $recipient_name . $recipient_family;
                        }
                    }
                                        
                    if ($recipient_street) {
                        $recipient[] = $recipient_street;
                    }
                    
                    if ($recipient_zip || $recipient_city) {
                        $recipient_blocks = array();
                        
                        if ($recipient_zip) {
                            $recipient_blocks[] = $recipient_zip;
                        }
                        
                        if ($recipient_city) {
                            $recipient_blocks[] = $recipient_city;
                        }
                        
                        $recipient[] = implode(" ", $recipient_blocks);
                    }
                    
                    // benutzer_adresse - never changes
                    $tokens_multi[$member_id]['member_recipient_block'] = implode("<br />", $recipient);
                    $tokens_multi[$member_id]['mitglied_recipient_block'] = implode("<br />", $recipient); 
                    
                    $tokens_multi[$member_id]['address'] = "";


                    if($member_data['gender'] == "männlich"){
                       	$tokens_multi[$member_id]['anrede_mitglied'] = "Sehr geehrter Herr";
						$tokens_multi[$member_id]['member_HerrnFrau'] = "Herrn"; //ISPC-1236 Dragos 15.01.2021
                    } else if($member_data['gender'] ==  "weiblich"){
                        $tokens_multi[$member_id]['anrede_mitglied'] = "Sehr geehrte Frau";
						$tokens_multi[$member_id]['member_HerrnFrau'] = "Frau"; //ISPC-1236 Dragos 15.01.2021
                    } else{
                        $tokens_multi[$member_id]['anrede_mitglied'] = "";
						$tokens_multi[$member_id]['member_HerrnFrau'] = ""; //ISPC-1236 Dragos 15.01.2021
                    }
                    
                  
                    //ISPC-1236 Lore 12.04.2021
                    $tokens_multi[$member_id]['member_vereinsmitgliedsnummer'] = $member_data['member_number'];
                    $tokens_multi[$member_id]['member_beruf'] = $member_data['profession'];
                    $tokens_multi[$member_id]['member_Email'] = $member_data['email'];
                    $tokens_multi[$member_id]['member_Telefon'] = $member_data['phone'];
                    //.
                    
                    
                    if ($template_data) { 
                        $temp_files[$member_id] = $this->generate_file($clientid, $userid, $template_data[0], $tokens_multi[$member_id], 'docx', $batch_temp_folder, 'generate'); //TODO-3668 Ancuta 14.12.2020 - added $clientid and $userid param
                    }
                }
                
                if(count($temp_files) > '0')
				{
					//final cleanup (check if files are on disk)
					foreach($temp_files as $k_temp => $v_file)
					{
						if(!is_file($v_file))
						{
							//remove unexisting files
//							$unsetted_files[] = $v_file; //for debugs
							unset($temp_files[$k_temp]);
						}
						else{
							//ispc 1739 p.16
							//save individual file
							$file_showname = $template_data[0]['title'] ; 	
							if($this->user_print_jobs && $export_data['print_job'] == 1){
    							$this->save_member_file($v_file, $k_temp, $file_showname, $template_data[0]['id'] ,0 , 'docx',$clientid);//TODO-3668 Ancuta 14.12.2020 - added $clientid param
							} else{
    							$this->save_member_file($v_file, $k_temp, $file_showname, $template_data[0]['id'] ,0 , 'docx');
							}
							
						}
					}

					$remaining_temp_files = array_values(array_unique($temp_files));
					//print_r($remaining_temp_files);
					//die();
					
					if(count($remaining_temp_files) > '0')
					{
					    //TODO-3668 Ancuta 14.12.2020
					    if($this->user_print_jobs && $export_data['print_job'] == 1){
					        
					        $final_file_name = "";
					        $final_file_name = $this->generate_file($clientid,$userid,$template_data[0], null, 'pdf', $export_data['batch_temp_folder'], 'merge', $remaining_temp_files,$export_data['member_id']);
					        
					        return $final_file_name;
					        
					        
					    } else{
					       $final_file = $this->generate_file($clientid, $userid, $template_data[0], false, 'pdf', $batch_temp_folder, 'merge', $remaining_temp_files); //TODO-3668 Ancuta 14.12.2020 - added $clientid and $userid param
					    }
					    
					}
				}
				
				
				//TODO-3668 Ancuta 14.12.2020
				if($this->user_print_jobs && $export_data['print_job_id'] == 1){
				    
				} else{
				    exit();
				}
            }
        }
        
        //TODO-3668 Ancuta 14.12.2020 - added $clientid param
        public function save_member_file($file , $member_id,  $file_showname , $template_id = 0, $parent_id=0 , $file_type = 0,$clientid = 0)
        {
        	//die("rrrrr");
        	//$clientid = $this->clientid;
            $use_alternative = false;  
            if(isset($clientid) && $this->user_print_jobs ==1 ){
                $use_alternative = true;  
            }
            
        	$clientid = isset($clientid) && !empty($clientid) ?  $clientid : $this->clientid;
        	$path = false;
//         	$path = 'clientuploads';//TODO-3668
        	/*
        	 * //this path is used by ftp_connect
        	 * production should be this
        	 * $path = false;
        	 */
        	
        	//queue file into ftp_put
        	//ftp_put_queue returns the id from sql ... so we can join
        	
        	
        	//die($tempdir_for_ftp);      	
           	$ftp_path = $this->system_file_upload($clientid, $file , false,  $path);
        	
           	
        	//$cmd = "rm -r " . $file;
        	//@exec($cmd);

        	$file_realname =  pathinfo($file);
        	
        	
        	$query = new MemberFiles();
        	$query->clientid = $clientid;
        	$query->member_id = (int)$member_id;
        	$query->file_showname = $file_showname;//Pms_CommonData::normalizeString($file_showname);
        	$query->file_realname = $file_realname['basename'];
        	$query->file_type = $file_type;
        	$query->ftp_path = $ftp_path;
//         	$query->ftp_put_queue_id = (int)$ftp_put_queue;
        	$query->template_id = (int)$template_id;
        	$query->isdeleted = "0";
        	$query->parent_id = (int)$parent_id;
        	
        	$query->save();
        	if ($parent_id==0){
        		//this is the original file
        		$query->parent_id = $query->id;
        		$query->revision = "1";
        		$query->save();
        	}
        	return $query;
        	
        	//$query->getLast();      	
        }
        
        //ispc 1739 p.16
        public function fileuploadAction( $outside_call = true , $member_id = 0)
        {
        	if ($outside_call
        		&&
        		(	! $this->getRequest()->isPost()
        		 	||
        			(! isset($_REQUEST['doc_id'], $_REQUEST['member_id']) && ! isset($_REQUEST['parent_id'], $_REQUEST['member_id']))
        		)
        	) {
        		die();
        	}
        	$id = (!empty($_REQUEST['doc_id'])) ? (int)$_REQUEST['doc_id'] : 0 ;
        	$parent_id = (!empty($_REQUEST['parent_id'])) ? (int)$_REQUEST['parent_id'] : 0 ;
        	$clientid = $this->clientid;
        	
        	if ($member_id == 0){
        		$member_id = (!empty($_REQUEST['member_id'])) ? (int)$_REQUEST['member_id'] : 0 ;
        	}
        	$isdeleted = 0;
        	
        	if($outside_call){
        		$this->_helper->layout()->disableLayout();
        		$this->_helper->viewRenderer->setNoRender(true);
        	}
        	 
        	
        	  if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post'){ //catch file overload error...
        		$postMax = ini_get('post_max_size'); //grab the size limits...
        		$response = array('success' => false, "error" => "filesize exceeds limit! use a smaller file or contact admin");
        		
        	}
        	
        	
        	elseif( strpos( $_FILES['qqfile']['type'], 'word') === false 
        			&&
        			strpos( $_FILES['qqfile']['type'] ,"pdf") === false )
        	{
        		$response = array('success' => false, "error"=>"Invalid file format !");
        	} 
        
        	
        	elseif( $parent_id > 0
        			&&
        			$member_id > 0) 
        	{
        		/* upload a version step
        		&&
        		$fl = Doctrine::getTable('MemberFiles')->findByClientidAndMember_idAndParent_id($clientid, $member_id , $parent_id)		    		
        		this fl is just a verify, it can be removed
        		 */
        		$response = array('success' => false, "error" => "contact admin 1");
        		$ext = @end((explode(".", $_FILES["qqfile"]["name"]))); 
        		
//         		$filename = PUBLIC_PATH . "/uploads/" .Pms_CommonData::normalizeString($_FILES['qqfile']['name']);
        		if (!file_exists(PDF_PATH . "/" . $clientid )) {
        			mkdir(PDF_PATH . "/" . $clientid);
        		}
        		if (!file_exists(PDF_PATH . "/" . $clientid ."/members" )) {
        			mkdir(PDF_PATH . "/" . $clientid ."/members");
        		}
        		$filename = PDF_PATH . "/" . $clientid ."/members/" .Pms_CommonData::normalizeString($_FILES['qqfile']['name']);
        		
        		if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename))
        		{        			
        			/* 
        			$docx_correct =  false;
        			if($_FILES['qqfile']['type'] == "application/pdf"){
        				//@TODO validate pdf
        				$docx_correct =  true;
        				$file_type = 'pdf';
        			}else{
        				//validate docx
        				$file_type = 'docx';
	        			$docx = new ZipArchive();
	        			try {
	        				if(($docx->open($filename)) !== true){
	        					throw new Exception();
	        				}else{
	        					$docx_correct = true;
	        				}
	        			}
	        			catch (Exception $e) {
	        				$response = array('success' => false, "error"=>"Invalid docx file !"); 
	        				//unlink this file?
	        				@unlink($filename);		
	        			}
        			} */
        			$docx_correct =  true; 
					if($docx_correct){
					    $this->save_member_file($filename , $member_id,  $_FILES['qqfile']['name'] , 0, $parent_id , $ext);		
        				$response = array('success' => true);
					}
        		}
        	}
        	else{
        		//upload a new file, first version of its kind step
        		//$_FILES['qqfile']['name'] = Pms_CommonData::normalizeString($_FILES['qqfile']['name']);
        		$response = array('success' => false, "error" => "contact admin 2");
//         		$filename = PUBLIC_PATH . "/uploads/" .Pms_CommonData::normalizeString($_FILES['qqfile']['name']);    		
        		if (!file_exists(PDF_PATH . "/" . $clientid )) {
        			mkdir(PDF_PATH . "/" . $clientid);
        		}
        		if (!file_exists(PDF_PATH . "/" . $clientid ."/members" )) {
        			mkdir(PDF_PATH . "/" . $clientid ."/members");
        		}
        		$filename = PDF_PATH . "/" . $clientid ."/members/" .Pms_CommonData::normalizeString($_FILES['qqfile']['name']);    		
        		$ext = @end((explode(".", $_FILES["qqfile"]["name"]))); 
        		
        		if(move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename))
        		{
        			/* $docx_correct =  false;
        			if($_FILES['qqfile']['type'] == "application/pdf"){
        				//@TODO validate pdf
        				$docx_correct =  true;
        				$file_type = 'pdf';
        			}else{
        				//validate docx
        				$file_type = 'docx';
	        			$docx = new ZipArchive();
	        			try {
	        				if(($docx->open($filename)) !== true){
	        					throw new Exception();
	        				}else{
	        					$docx_correct = true;
	        				}
	        			}
	        			catch (Exception $e) {
	        				$response = array('success' => false, "error"=>"Invalid docx file !");
	        				//unlink this file?
	        				@unlink($filename);
	        			}
        			} */
        			$docx_correct =  true;
        			if($docx_correct){
        				if(!empty($_REQUEST['file_first_version_name'])){
        					$file_showname = urldecode($_REQUEST['file_first_version_name']) . "." . $ext;	
        				}elseif(!empty($_POST['file_first_version_name'])){
        					$file_showname = urldecode($_POST['file_first_version_name']) . "." . $ext;	
        				}else{
        					$file_showname = $_FILES['qqfile']['name'];       					
        				}
        				$this->save_member_file($filename , $member_id,  $file_showname , 0, 0, $ext );
    	    			$response = array('success' => true, 'file_showname'=>$file_showname);      
        			}
        		}
        	}

        	if($outside_call){
        		echo json_encode($response);
        		exit;    
        	}else{
        		return $response;
        	}
        }
        public function filedownloadAction() {
        	$this->_helper->layout->setLayout('layout');
        	$this->_helper->viewRenderer->setNoRender();
        	
        	$id = (int)$_GET['doc_id'];
        	$clientid = $this->clientid;
        	$member_id = ! empty($_GET['id']) ? (int)$_GET['id'] : 0;
        	$revision = (int)$_GET['rev'];
        	
        	$isdeleted = 0;
        	$file_password = $this->filepass;
        	
        	if($_GET['doc_id'] > 0)
			{
				$file_arr = Doctrine_Query::create()
				->select()
				->from('MemberFiles m')
				->where('id = ?', $id)
				->andWhere('clientid = ?', $clientid)
				->andWhere('revision = ?', $revision)
				->andWhere('isdeleted = ?', $isdeleted)
				->limit(1);
				
				if ($member_id != 0 ) {
					$file_arr->andWhere('member_id = ?', $member_id);
				}
				
				$file_arr = $file_arr->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
				
				
// 				if($fl = Doctrine::getTable('MemberFiles')->findByIdAndClientidAndMember_idAndRevisionAndIsdeleted($id, $clientid, $member_id , $revision, $isdeleted))
				if(is_array($file_arr) && $file_arr['ftp_path'] != '')
				{
					//$file_arr = $fl->toArray();
					
					//$file_arr = array_values($file_arr);
					
					//$file_arr =$file_arr[0];
// 					echo "<pre>";
// 					print_r($file_arr); 
// 					die();
					
					$ftp_path_info = pathinfo($file_arr['ftp_path']);
					if(empty($file_arr['ftp_path'])){
						die("ftp path error");
					}
					
					$local_zip = PUBLIC_PATH . "/uploads/" . $ftp_path_info['filename'] . '.zip';
					
// 					if($con_id = Pms_FtpFileupload::ftpconnect())
// 					{
// 						$download = Pms_FtpFileupload::filedownload($con_id, $local_zip , $file_arr['ftp_path']);
// 						Pms_FtpFileupload::ftpconclose($con_id);
// 					} else {
// 						die("ftp error");	
// 					}
			
// 					$cmd = "unzip -P " . $file_password ." ". $local_zip . ";";
// 					@exec($cmd);
					$pathinfo22 =  pathinfo(basename($file_arr['ftp_path']));
					$filename = $pathinfo22['filename'] . "/" . $file_arr['file_realname'];
					
					$old = $_REQUEST['old'] ? true : false; 
					if (($path = Pms_CommonData::ftp_download($file_arr['ftp_path'] , $file_password , $old , $file_arr['clientid'] , $filename, "MemberFiles", $file_arr['id'])) === false) {
						//failed to download ftp file	
					}
					
					$path_download = $path ."/" . $file_arr['file_realname'];

					if (!file_exists($path_download)){
						$path = FTP_DOWNLOAD_PATH . "/" . $ftp_path_info['filename'] . "/"; // change the path to fit your websites document structure
						$path_download = $path . $file_arr['file_realname'];
					}
					if (!file_exists($path_download)){
						$path = FTP_DOWNLOAD_PATH . "/" ; // change the path to fit your websites document structure
						$path_download = $path . $file_arr['file_realname'];
					}
					
// 					die($path_download);
					
// 					$path_download = PUBLIC_PATH . "/uploads/" . $ftp_path_info['filename'] . "/" . $file_arr['file_realname'];
					//echo $path_download;die();
					if(file_exists($path_download))
					{	
						$fsize = @filesize($path_download);
						$filename = $file_arr["file_showname"];
						$ext = explode(".", $file_arr["file_showname"]);
						$ext = @end($ext);

						if (strtolower($file_arr["file_type"]) != strtolower($ext) ){
							$filename .= "." . $file_arr["file_type"]; 
						}
						
						//$filename = $file_arr["file_showname"].'.'.$file_arr["file_type"];
						/*
						if (Pms_CommonData::normalizeString($file_arr["file_showname"]) == $file_arr["file_realname"]){
							$filename = $file_arr["file_showname"];
						}else{
							$filename = $file_arr["file_showname"]."_".$file_arr["file_realname"];
						}
						*/
						
						header('Pragma: public');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Content-Type: application/octet-stream');
						header('Content-Transfer-Encoding: Binary');
						header("Content-length: $fsize");
						header("Cache-Control: private",true);						
						
						if($_COOKIE['mobile_ver'] != 'yes' || ($_COOKIE['mobile_ver'] == 'yes' && stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false))
						{ //if on mobile version don't send content-disposition to play nice with iPad
							header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
						}
						
						ob_flush();	ob_clean();flush();
						readfile($path_download);
						unlink($path_download);
						exit;
					}
				}
				
			}
			exit;
        }
        public function fileremoveAction(){
        	if (	! $this->getRequest()->isGet()
        			||
        			! isset($_GET['doc_id'], $_GET['member_id'])
        		) 
        	{
        		die();
        	}
        			 
        	$id = (int)$_GET['doc_id'];
        	$clientid = $this->clientid;
        	$member_id = (int)$_GET['member_id'];
        	$isdeleted = 0;
        	$this->_helper->layout()->disableLayout();
        	$this->_helper->viewRenderer->setNoRender(true);
        	
        	$thrash = Doctrine_Query::create()
        	->update("MemberFiles")
        	->set('isdeleted', 1)
        	->where('id = ?' ,$id )
        	->andWhere('clientid = ? ' , $this->clientid)
        	->andWhere('member_id = ? ' , $member_id);
        	$thrash->execute();
        	
        	
        	$this->_redirect(APP_BASE . $this->getRequest()->getControllerName() . "/editmember?flg=suc&tabs=4&id=". $member_id);
        	 
        	
        	
        }
        
        
        /* PHPDOCX WORD AND PDF START */
        //		$batch_printing_mode (false, generate, merge)
        //		false (no batch)
        //		generate (generates only temp docx file)
        //		merge (does a merge of all files in a directory)
        
        //TODO-3668 Ancuta 14.12.2020 - added $clientid and $userid param
        private function generate_file($clientid = 0,$userid = 0, $template_data = false, $vars = false, $export_file_type = 'docx', $batch_temp_folder = false, $batch_printing_mode = false, $batch_temp_files = false,$export_members_id = false)
        {
        
            $clientid = isset($clientid) && !empty($clientid) ? $clientid : $this->clientid;
            $userid = isset($userid) && !empty($userid) ? $userid : $this->userid;
            //$clientid = $this->clientid;
            //$userid = $this->userid;
            
           	ob_end_clean();
            
          
            if($template_data && file_exists(MEMBER_LETTER_TEMPLATE_PATH . '/' . $template_data['file_path']))
            {
                $template_path = MEMBER_LETTER_TEMPLATE_PATH . '/' . $template_data['file_path'];
                $docx = new CreateDocxFromTemplate($template_path);
       
                if($vars)
                {
                    
                    $client_details_vars = BriefTemplates::get_client_details($clientid);
                    $client_details_vars = (!empty($client_details_vars) ? $client_details_vars : array());

                    $user_details_vars = BriefTemplates::get_user_details($userid);
                    $user_details_vars = (!empty($user_details_vars) ? $user_details_vars : array());
                    
                    $vars = array_merge($vars, $client_details_vars, $user_details_vars);
                    
                    //CUSTOM VARS
                    $vars['aktuelles_datum'] = date('d.m.Y', time());
                    
                    $html_tokens = array('member_recipient_block','mitglied_recipient_block');
        
                    foreach($html_tokens as $k_html => $token_html)
                    {
                   	
                        //unset the html variable from tokens $vars to avoid errors
                        if(strlen(trim(rtrim($vars[$token_html]))) > '0')
                        {
                            //set html options
                            $html_options = array('isFile' => false, 'parseDivs' => false, 'downloadImages' => false, "strictWordStyles" => false);
        
                            //cleanup token html entities
                            $html = html_entity_decode($vars[$token_html], ENT_COMPAT, 'UTF-8');
        
        
                            if($token_html == 'address' || $token_html == 'footer' || $token_html == "recipient" || $token_html == "member_recipient_block"  || $token_html == "mitglied_recipient_block" || $token_html == "comment")
                            {
//                                 $type = "inline";
                                $type = "block";
        
//                                 //get token fonts only for inline tokens
//                                 $docx_tmp = new CreateDocxFromTemplate($template_path);
//                                 $docx_tmp->replaceVariableByHTML($token_html, $type, $html, $html_options);
//                                	//$token_fonts = $docx_tmp->getTokenFont();
//                                 $token_fonts = array();
        
                                //convert inline_html_tokens to string_tokens
                                $new_tokens[] = $token_html;
        
                                $vars[$token_html . '_text'] = strip_tags(html_entity_decode($vars[$token_html], ENT_COMPAT, 'UTF-8'), "<br>");
                                $vars[$token_html . '_text'] = str_replace(array('<br/>', '<br />', '<br>'), '\n\r', $vars[$token_html . '_text']);
                                
//                                 $html = ($vars[$token_html]);
                               // $html = utf8_decode($vars[$token_html]);// UMLAUTS FOR  ITEMS TOKEN (19.03.2018)
                                if ($res = Pms_DocUtil::process_html_token($docx, $token_html, $html)) {
                                	$html = $res;
                                }
                            }
                            else
                            {
                                $type = "block";
                            }
        
                            //set each token font
//                             if($type == "inline" && count($token_fonts[$token_html]) > '0')
//                             {
//                                 $css_style = array();
//                                 if(strlen($token_fonts[$token_html]['font']['name']) > '0')
//                                 {
//                                     $css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
//                                 }
        
//                                 if(strlen($token_fonts[$token_html]['font']['size']) > '0')
//                                 {
//                                     $css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
//                                 }
        
//                                 if(strlen($token_fonts[$token_html]['font']['color']) > '0')
//                                 {
//                                     $css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
//                                 }
        
//                                 if($token_fonts[$token_html]['font']['isbold'] == '1')
//                                 {
//                                     $css_style[] = 'font-weight:bold';
//                                 }
        
//                                 if($token_fonts[$token_html]['font']['isitalic'] == '1')
//                                 {
//                                     $css_style[] = 'font-style:italic';
//                                 }
        
//                                 if($token_fonts[$token_html]['font']['isunderline'] == "1")
//                                 {
//                                     $css_style[] = 'text-decoration:underline';
//                                 }
        
//                                 //dummy css control
//                                 if(!empty($css_style))
//                                 {
//                                     $css_style[] = '';
//                                 }
        
//                                 $html = html_entity_decode('<p style="' . implode(';', $css_style) . '">' . strip_tags($vars[$token_html], '<br>') . '</p>', ENT_COMPAT, 'UTF-8');
//                             }
	
							//force change utf-8 in html entities, because on one server it did not return corectly utf-8
							// TODO-1455(22.03.2018)
							$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
								 
 
                            $docx->replaceVariableByHTML($token_html, $type, $html, $html_options);
                            unset($vars[$token_html]);
                        }
                        else
                        {
                            $vars[$token_html] = '';
                            $vars[$token_html . '_text'] = '';
                        }
                    }
        
                    //parse header
                    $docx->replaceVariableByText($vars, array('parseLineBreaks' => true, 'target' => 'header'));
        
                    //parse body
                    $options = array('parseLineBreaks' => true);
                    $docx->replaceVariableByText($vars, $options);
        
                    //parse footer
                    $docx->replaceVariableByText($vars, array('parseLineBreaks' => true, 'target' => 'footer'));
                }
        
                if(!is_dir(PDFDOCX_PATH))
                {
                    while(!is_dir(PDFDOCX_PATH))
                    {
                        mkdir(PDFDOCX_PATH);
                        if($i >= 50)
                        {
                            exit; //failsafe
                        }
                        $i++;
                    }
                }
                
                if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
                {
                    while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
                    {
                        mkdir(PDFDOCX_PATH . '/' . $clientid);
                        if($i >= 50)
                        {
                            exit; //failsafe
                        }
                        $i++;
                    }
                }
                if(isset($export_members_id) && !empty($export_members_id)){
                    $suffix = $export_members_id;
                } else{
                    if($vars['member_id']){
                        $suffix = $vars['member_id'];
                    } else{
                        $suffix = "";
                    }
                }
 
        
                $filename = PDFDOCX_PATH . '/' . $clientid . '/member_letter_' . $suffix;
                
                //rewrite $filename on batch job in another location
                //check and create temp folder used in batch
                if($batch_printing_mode && ($batch_printing_mode == 'merge' || $batch_printing_mode == 'merge_pdfs'))
                {
                    if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                    {
                        while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                        {
                            mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
                            if($i >= 50)
                            {
                                exit; //failsafe
                            }
                            $i++;
                        }
                    }
                   
                    $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/member_letter_' . $suffix;
                    $merged_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_member_letters' . $suffix . '.docx';
                    $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_member_letters_' . $suffix . '.' . $export_file_type;
                }
                else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs_multiple')
                {
                    if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                    {
                        while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                        {
                            mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
                            if($i >= 50)
                            {
                                exit; //failsafe
                            }
                            $i++;
                        }
                    }
        
                    $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/member_letter_' . $suffix;
                    $merged_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/merged_member_letters' . $suffix . '.docx';
                    $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/final_merged_member_letters_' . $suffix . '.' . $export_file_type;
                }
                else if($batch_printing_mode == 'generate_pdf')
                {
                    if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                    {
                        while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
                        {
                            mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
                            if($i >= 50)
                            {
                                exit; //failsafe
                            }
                            $i++;
                        }
                    }
        
                    $filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/member_letter_' . $suffix;
                    $other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/member_letter_o_' . $suffix . '.' . $export_file_type;
                }
                //		rewrite file extension
                if($_REQUEST['type'] == "pdf")
                {
                    $export_file_type = $_REQUEST['type'];
                }
                //batch printing methods
                //batch printing only docx(in a temp file) and then merge all in one file docx and then pdf
                if($batch_printing_mode && $batch_printing_mode == 'generate')
                {  
                    //make sure export file type is set to docx
                    if($export_file_type == 'docx')
                    {
                        $docx->createDocx($filename);
                        return $filename . '.' . $export_file_type;
                    }
                }
                else if($batch_printing_mode && $batch_printing_mode == 'generate_pdf')
                {
                    //create pdf but dont download it
                    $docx->createDocx($filename);
        
                    //$docx->enableCompatibilityMode();
                    $docx->transformDocument($filename . '.docx', $other_filename);
                            
                    return $other_filename;
                }
                else if($batch_printing_mode && $batch_printing_mode == 'merge')
                {
                    //merge all files existing in $batch_temp_files!
                    $merge = new MultiMerge();
                    $merge_options = array(
                        'mergeType' => '0',
                        'numbering' => 'continue',
                        'enforceSectionPageBreak' => true
                        
                    );
        
                    
                    $first_shit = $batch_temp_files[0];
                    unset($batch_temp_files[0]);
                    $merge_process = $merge->mergeDocx($first_shit, $batch_temp_files, $merged_filename, $merge_options);

                    array_map("unlink" , $batch_temp_files);
                    @unlink( $first_shit );
                                       
                    if(file_exists($merged_filename))
                    {
                    	
                    	$docx = new CreateDocxFromTemplate($merged_filename);
                    	//$docx->enableCompatibilityMode();
                    	$docx->transformDocument($merged_filename, $merged_other_filename);          
                                            
                        //$this->system_file_upload($clientid, $merged_other_filename);
                	    if(isset($export_members_id) && !empty($export_members_id )){
                            //$this->system_file_upload($clientid, $merged_other_filename , false , false , $foster_file = true);
                   	        //$this->system_file_upload_new($clientid, $merged_other_filename , false , false , $foster_file = true);
                    	    
                    	    return $merged_other_filename;
                    	    
                    	} else{
                        	
                            //stop unlinking files
                            //						unlink($merged_filename);
                            ob_end_clean();
                            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
                            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                            header("Cache-Control: no-store, no-cache, must-revalidate");
                            header("Cache-Control: post-check=0, pre-check=0", false);
                            header("Pragma: no-cache");
            
                            switch($export_file_type)
                            {
                                case 'pdf':
                                    header('Content-type: application/pdf');
                                    break;
                                case 'doc':
                                    header('Content-type: application/vnd.ms-word');
                                    break;
                                case 'rtf':
                                    header("Content-type: application/rtf");
                                    break;
                                case 'odt':
                                    header('Content-type: application/vnd.oasis.opendocument.text');
                                    break;
                                default:
                                    exit;
                                    break;
                            }
                            header('Content-Disposition: attachment; Filename="merged_member_letters' . $suffix . '.' . $export_file_type . '"');
                            readfile($merged_other_filename);
                            @unlink($merged_filename);
                            @unlink($merged_other_filename);
                            exit;
                    	}
                    	
                    	
                    }
                }
                else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs_multiple')
                {
                    $merge = new MultiMerge();
                    $merge_process = $merge->mergePdf($batch_temp_files, $merged_other_filename);
        
                    return $merged_other_filename;
                }
                else if($batch_printing_mode && $batch_printing_mode == 'merge_pdfs')
                {
                    //merge all files existing in $batch_temp_files!
                    $merge = new MultiMerge();
                    $merge_process = $merge->mergePdf($batch_temp_files, $merged_other_filename);
        
                    array_map('unlink', $batch_temp_files);
                    
                    if(file_exists($merged_other_filename))
                    {
                        $this->system_file_upload($clientid, $merged_other_filename);
                        //stop unlinking files
                        //						unlink($merged_filename);
                        ob_end_clean();
                        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
                        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                        header("Cache-Control: no-store, no-cache, must-revalidate");
                        header("Cache-Control: post-check=0, pre-check=0", false);
                        header("Pragma: no-cache");
        
                        switch($export_file_type)
                        {
                            case 'pdf':
                                header('Content-type: application/pdf');
                                break;
                            case 'doc':
                                header('Content-type: application/vnd.ms-word');
                                break;
                            case 'rtf':
                                header("Content-type: application/rtf");
                                break;
                            case 'odt':
                                header('Content-type: application/vnd.oasis.opendocument.text');
                                break;
                            default:
                                exit;
                                break;
                        }
                        header('Content-Disposition: attachment; Filename="merged_member_letters' . $suffix . '.' . $export_file_type . '"');
                        @readfile($merged_other_filename);
                        exit;
                    }
                    exit;
                }
                else if($export_file_type == 'docx')
                {
                    $docx->createDocxAndDownload($filename);
                    //					unlink($filename . '.docx');
                    exit;
                }
                else
                {
                    $docx->createDocx($filename);
                    $other_filename = PDFDOCX_PATH . '/' . $clientid . '/member_letters_final_' . $suffix . '.' . $export_file_type;
        
                    //$docx->enableCompatibilityMode();
                    $docx->transformDocument($filename . '.docx', $other_filename);
        
                    $this->system_file_upload($clientid, $other_filename, $template_data['title']);
                   
                    unlink($filename . '.docx');
        
                    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    header("Cache-Control: post-check=0, pre-check=0", false);
                    header("Pragma: no-cache");
        
                    switch($export_file_type)
                    {
                        case 'pdf':
                            header('Content-type: application/pdf');
                            break;
                        case 'doc':
                            header('Content-type: application/vnd.ms-word');
                            break;
                        case 'rtf':
                            header("Content-type: application/rtf");
                            break;
                        case 'odt':
                            header('Content-type: application/vnd.oasis.opendocument.text');
                            break;
                        default:
                            exit;
                            break;
                    }
                    header('Content-Disposition: attachment; Filename="member_letters_final_' . $suffix . '.' . $export_file_type . '"');
                    readfile($other_filename);
                    @unlink($other_filename);
                    exit;
                }
            }
            else
            {
                return false;
            }
        }
        

        public function system_file_upload($clientid , $source_path = false, $file_title = false , $test_path = false)
        {	
//         	echo "<pre>";

//         	$args = func_get_args();
// 			var_export($args);

            if($source_path)
            {
                //prepare unique upload folder
                //				$tmpstmp = $this->uniqfolder(PDF_PATH);
                $tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
        
                //get upload folder name
                $tmpstmp_filename = basename($tmpstmp);
        
                //get original file name
                $file_name_real = basename($source_path);
                $source_path_info = pathinfo($source_path);
        
        
                //construct upload folder, file destination
                $destination_path = PDF_PATH . "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
                $db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
                //do a copy (from place where the pdf is generated to upload folder
                copy($source_path, $destination_path);
//                 echo ($source_path ."<br>");
//                 die($destination_path);
//                 if($_REQUEST['zzz'])
//                 {
//                     print_r("Copied from:");
//                     print_r($source_path);
//                     print_r("\n\n");
        
//                     print_r("Copied to:");
//                     print_r($destination_path);
//                     print_r("\n\n");
        
//                     print_r("Copied");
//                     var_dump(copy($source_path, $destination_path));
//                     print_r("\n\n");
//                 }
        
                //prepare cmd for folder zip
//                 $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
        
//                 if($_REQUEST['zzz'])
//                 {
//                     print_r("Executed cmd:");
//                     var_dump($cmd);
//                     print_r("\n\n");
        
//                     print_r("Executed?:");
//                     var_dump(exec($cmd));
//                     print_r("\n\n");
//                 }
                //execute - zip the folder
//                 exec($cmd);
        
                $zipname = $tmpstmp . ".zip";
                /*
                 $filename = "uploads/" . $tmpstmp . ".zip";
                 */
                if($test_path == false){
                	$filename = "uploads/" . $tmpstmp . ".zip";
                }else{
                	$filename = $test_path . "/" . $tmpstmp . ".zip";
                }
                
                //connect
//                 $con_id = Pms_FtpFileupload::ftpconnect();
//                 if($_REQUEST['zzz'])
//                 {
//                     print_r("Connection ID:");
//                     var_dump($con_id);
//                     print_r("\n\n");
//                     exit;
//                 }
//                 if($con_id)
//                 {
//                     //do upload
//                     $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
//                     //close connection
//                     Pms_FtpFileupload::ftpconclose($con_id);
//                 }
                
//                 $ftp_put_queue = Pms_CommonData :: ftp_put_queue($destination_path , 'uploads');
                
                $client_data = Pms_CommonData::getClientDataFp($clientid);
                $file_password = $client_data[0]['fileupoadpass'];
                
                $ftp_put_queue = Pms_CommonData :: ftp_put_queue($destination_path ,  'uploads', $is_zipped = NULL, false,$clientid,$file_password );
                
                
            return $filename;
            }
        }
        
        //TODO-3668 Ancuta 15.12.2020
        private function system_file_upload_new($clientid, $source_path = false, $file_title = false , $test_path = false , $foster_file = false )
        {
            if($source_path)
            {
                
                if ($foster_file == true) {
                    $legacy_path = strtolower(__CLASS__);
                    $tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
                    $destination_path = PDF_PATH;
                    
                } else {
                    
                    if($test_path == 'clientuploads'){
                        $legacy_path = "clientuploads";
                        $tmpstmp = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
                        $destination_path = CLIENTUPLOADS_PATH;
                    }else{
                        $legacy_path = "uploads";
                        $tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
                        $destination_path = PDF_PATH;
                    }
                    
                    
                }
                
                //prepare unique upload folder
                //				$tmpstmp = $this->uniqfolder(PDF_PATH);
                
                //get upload folder name
                $tmpstmp_filename = basename($tmpstmp);
                
                //get original file name
                $file_name_real = basename($source_path);
                $source_path_info = pathinfo($source_path);
                
                
                //construct upload folder, file destination
                $destination_path .=  "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
                $db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
                
                
                //do a copy (from place where the pdf is generated to upload folder
                copy($source_path, $destination_path);
                // 	        if($_REQUEST['zzz'])
                    // 	        {
                    // 	            print_r("Copied from:");
                    // 	            print_r($source_path);
                    // 	            print_r("\n\n");
                    
                    // 	            print_r("Copied to:");
                    // 	            print_r($destination_path);
                    // 	            print_r("\n\n");
                    
                    // 	            print_r("Copied");
                    // 	            var_dump(copy($source_path, $destination_path));
                    // 	            print_r("\n\n");
                    // 	        }
                    
                    //prepare cmd for folder zip
                    // 	        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
                    
                    // 	        if($_REQUEST['zzz'])
                        // 	        {
                        // 	            print_r("Executed cmd:");
                        // 	            var_dump($cmd);
                        // 	            print_r("\n\n");
                        
                        // 	            print_r("Executed?:");
                        // 	            var_dump(exec($cmd));
                        // 	            print_r("\n\n");
                        // 	        }
                        //execute - zip the folder
                        // 	        exec($cmd);
                        
                        $zipname = $tmpstmp . ".zip";
                        /*
                         $filename = "uploads/" . $tmpstmp . ".zip";
                         */
                        // 	        if($test_path == false){
                        // 	        	$filename = "uploads/" . $tmpstmp . ".zip";
                        // 	        }else{
                        // 	        	$filename = $test_path . "/" . $tmpstmp . ".zip";
                        // 	        }
                        
                        //connect
                        // 	        $con_id = Pms_FtpFileupload::ftpconnect();
                        // 	        if($_REQUEST['zzz'])
                            // 	        {
                        // 	            print_r("Connection ID:");
                        // 	            var_dump($con_id);
                        // 	            print_r("\n\n");
                        // 	            exit;
                        // 	        }
                        // 	        if($con_id)
                            // 	        {
                            // 	            //do upload
                            // 	            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
                            // 	            //close connection
                            // 	            Pms_FtpFileupload::ftpconclose($con_id);
                            // 	        }
                            
                            
                            $client_data = Pms_CommonData::getClientDataFp($clientid);
                            $file_password = $client_data[0]['fileupoadpass'];
                            
                            $filename = Pms_CommonData :: ftp_put_queue($destination_path ,  $legacy_path, $is_zipped = NULL, $foster_file,$clientid,$file_password );
                            
                            return $db_filename_destination;
        }
	}
        
        /* PHPDOCX WORD AND PDF END */
        private function xlsBOF()
        {
            echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
            return;
        }
        
        private function xlsEOF()
        {
            echo pack("ss", 0x0A, 0x00);
            return;
        }
        
        private function xlsWriteNumber($Row, $Col, $Value)
        {
            echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
            echo pack("d", $Value);
            return;
        }
        
        private function xlsWriteLabel($Row, $Col, $Value)
        {
            $L = strlen($Value);
            echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
            echo $Value;
            return;
        }

        private function resetuploadvars()
        {
            //clear failed/other upload session vars
            $_SESSION['template_filename'] = '';
            unset($_SESSION['template_filename']);
        
            $_SESSION['template_filepath'] = '';
            unset($_SESSION['template_filepath']);
        
            $_SESSION['template_filetype'] = '';
            unset($_SESSION['template_filetype']);
        }
        
        //don't know why not save full variable as single array... instead endup with this approach to manualy manage/remember 1 million variable names
        private function retainValues($values, $prefix = '')
        {
            foreach($values as $key => $val)
            {
                if(!is_array($val))
                {
                    $this->view->{$prefix.$key} = $val;
//                     echo $prefix.$key ." => " . $val ."<br>"; 
                }
                else
                {
                	//retainValues changed to recursive by claudiu4.. so we can have infinite+1 variable names to remember in our head
                	self::retainValues($val, $prefix . $key );
                	
                    //retain 1 level array used in multiple hospizvbulk form
                	/*
                    foreach($val as $k_val => $v_val)
                    {                  	
                        if(!is_array($v_val))
                        {
                            $this->view->{$prefix . $key . $k_val} = $v_val;
                        }
                    }
                    */
                }
            }
        }
        
        private function clear_image_details()
        {
            $_SESSION['file'] = '';
            $_SESSION['filetype'] = '';
            $_SESSION['filetitle'] = '';
            $_SESSION['filename'] = '';
        
            unset($_SESSION['file']);
            unset($_SESSION['filetype']);
            unset($_SESSION['filetitle']);
            unset($_SESSION['filename']);
        }        
        
       

        private function array_sort($array, $on = NULL, $order = SORT_ASC)
        {
            $new_array = array();
            $sortable_array = array();
        
            if(count($array) > 0)
            {
                foreach($array as $k => $v)
                {
                    if(is_array($v))
                    {
                        foreach($v as $k2 => $v2)
                        {
                            if($k2 == $on)
                            {
                                if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on == "start_date" || $on == 'last_invoice_date'  || $on == 'last_donation_date' 
                                    || $on == 'membership_start_date'           //ISPC-2847 Lore 12.04.2021
                                    )
                                {
        
                                    if($on == 'birthdyears')
                                    {
                                        $v2 = substr($v2, 0, 10);
                                    }
                                    $sortable_array[$k] = strtotime($v2);
                                }
                                elseif($on == 'epid')
                                {
                                    $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                                }
                                elseif($on == 'percentage')
                                {
                                    $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                                }
                                else
                                {
                                    $sortable_array[$k] = ucfirst($v2);
                                }
                            }
                        }
                    }
                    else
                    {
                        if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on = "start_date" || $on == 'last_invoice_date'|| $on == 'last_donation_date' 
                            || $on == 'membership_start_date'           //ISPC-2847 Lore 12.04.2021
                               )
                        {
                            if($on == 'birthdyears')
                            {
                                $v = substr($v, 0, 10);
                            }
                            $sortable_array[$k] = strtotime($v);
                        }
                        elseif($on == 'epid' || $on == 'percentage')
                        {
                            $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                        }
                        elseif($on == 'percentage')
                        {
                            $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                        }
                        else
                        {
                            $sortable_array[$k] = ucfirst($v);
                        }
                    }
                }
        
                switch($order)
                {
                    case SORT_ASC:
                        //						asort($sortable_array);
                        $sortable_array = Pms_CommonData::a_sort($sortable_array);
                        break;
                    case SORT_DESC:
                        //						arsort($sortable_array);
                        $sortable_array = Pms_CommonData::ar_sort($sortable_array);
                        break;
                }
                foreach($sortable_array as $k => $v)
                {
                    $new_array[$k] = $array[$k];
                }
            }
        
            return $new_array;
        }
        

        

        

        public function mstatuslistAction(){
            
            $clientid = $this->clientid;
        
            $statuses = MemberStatuses::get_client_member_statuses($clientid);
            $this->view->statuses= $statuses;
             
        }
        
        
        
        public function addmstatusAction(){
        
            $clientid = $this->clientid;
            
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }

             
            if ($this->getRequest()->isPost())
            {
                $form = new Application_Form_MemberStatuses();
                 
                $_POST['clientid'] = $clientid ;
                if ($form->validate($_POST))
                {
                    $form->insert_data($_POST);
                     
                    $this->_redirect(APP_BASE . 'member/mstatuslist?flg=suc');
                    $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
                }
                else
                {
                    $form->assignErrorMessages();
                    $this->retainValues($_POST);
                }
            }
             
        }
        
        public function editmstatusAction(){
        
            $clientid = $this->clientid;
            
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
             
             
            $this->view->act = "member/editmstatus?id=" . $_GET['id'];
             
            $this->_helper->viewRenderer('addmstatus');
             
             
            if ($this->getRequest()->isPost())
            {
                $form = new Application_Form_MemberStatuses();
                 
                $_POST['clientid'] = $clientid ;
                $_POST['id'] = $_GET['id'];
                if ($form->validate($_POST))
                {
                    $form->update_data($_POST);
                     
                    $this->_redirect(APP_BASE . 'member/mstatuslist?flg=suc');
                    $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
                }
                else
                {
                    $form->assignErrorMessages();
                    $this->retainValues($_POST);
                }
            }
             
            if($_GET['id']){
                 
                $mstatus = MemberStatuses::get_client_member_statuses($clientid,$_GET['id']);
                if($mstatus){
                    $this->view->status = $mstatus[$_GET['id']]['status'];
                }
            }
   
             
        }
        
        
        public function deletemstatusAction ()
        {
            $clientid = $this->clientid;
        
            $has_edit_permissions = Links::checkLinkActionsPermission();
            if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
            {
                $this->_redirect(APP_BASE . "error/previlege");
                exit;
            }
             
            $this->_helper->viewRenderer->setNoRender();
             
            $fdoc = Doctrine::getTable('MemberStatuses')->findOneByIdAndClientid($_REQUEST['id'], $clientid);
            if($fdoc)
            {
                $fdoc->isdelete = 1;
                $fdoc->save();
                 
                $this->redirect(APP_BASE . 'member/mstatuslist?flg=del_suc');
                exit;
            }
            else
            {
                $this->redirect(APP_BASE . 'member/mstatuslist?flg=del_err');
                exit;
            }
             
        }
        
        
        public function gethighestmembernumberAction(){
            
            $this->_helper->layout->setLayout('layout_ajax');
            $logininfo = new Zend_Session_Namespace('Login_Info');
            
            $this->_helper->viewRenderer->setNoRender();
            
            $member = new Member();
            $highest_nr = $member->get_highest_member_number();
            
            echo json_encode($highest_nr);
            exit;
          
        }
	
        //gethighest_mandate_reference
        public function gethighestmandatereferenceAction()
        {
        
        	$member = new Member();
        	$highest_nr = $member->get_highest_mandate_reference_number();
        
       	
        	ob_end_clean();	ob_start();
        		
        	$this->_helper->json->sendJson(array("highest_nr"=>$highest_nr));
        	exit;
        	
        
        }
        
        //ispc 1739 p.15
        //get view list member_membership_end
		public function listmembershipendAction(){
			$clientid = $this->clientid;
			
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				if(!$_REQUEST['length'])
				{
					$_REQUEST['length'] = "10";
				}
				$limit = $_REQUEST['length'];
				$offset = $_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
				$order_column = "1";
				if(!empty($_REQUEST['order'][0]['column']))
				{
					$order_column = $_REQUEST['order'][0]['column'];
				}
				$order_dir = $_REQUEST['order'][0]['dir'];
				 
				$columns_array = array(
						"1" => "id",
						"2" => "description"
				);
				$order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
				// ########################################
				// #####  Query for count ###############
				$fdoc1 = Doctrine_Query::create();
				$fdoc1->select('count(*)');
				$fdoc1->from('MemberMembershipEnd');
				$fdoc1->where("clientid = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0  ");
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen($search_value) > 0)
				{
					$fdoc1->andWhere("( description like '%" . trim($search_value) . "%'     )");
				}
				$fdoc1->orderBy($order_by_str);
				$fdocexec = $fdoc1->execute();
				$fdocarray = $fdocexec->toArray();			
				$full_count  = $fdocarray[0]['count'];
				
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*');
				$fdoc1->Where("clientid = ".$clientid);
				$fdoc1->andWhere("isdelete = 0  ");
				
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen($search_value) > 0)
				{
					$fdoc1->andWhere("(description like '%" . trim($search_value) . "%'     )");
				}
				$fdoc1->limit($limit);
				$fdoc1->offset($offset); 
				$fdoclimitexec = $fdoc1->execute();
				$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
				$report_ids[] = '99999999999999';
				foreach ($fdoclimit as $key => $report)
				{
					$fdoclimit_arr[$report['id']] = $report;
					$report_ids[] = $report['id'];
				}
				$row_id = 0;
				$link = "";
				$resulted_data = array();
				foreach($fdoclimit_arr as $report_id =>$mdata)
				{
					$link = '%s';
					 
					$resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
				
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'member/listmembershipendedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
				 
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $full_count; // ??
				$response['data'] = $resulted_data;
				 			
				$this->_helper->json->sendJson($response);				
			}

		}
		//set edit list member_membership_end
		public function listmembershipendeditAction(){
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$this->_helper->viewRenderer('listmembershipendedit');
				
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MemberMembershipEnd();
			
				if($form->validate($_POST))
				{
					$_POST['id'] = (int)$_GET['id'];
					$form->update($_POST, $this->clientid);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			
			if ((int)$_GET['id'] > 0)
			{
				$fdoc = Doctrine::getTable('MemberMembershipEnd')->findbyIdAndClientid((int)$_GET['id'], $this->clientid );
				if ($fdoc)
				{
					$fdocarray = $fdoc->toArray();
					$fdocarray = array_values($fdocarray);
					$fdocarray = $fdocarray[0];
					$this->retainValues($fdocarray);
				}
			}
			
		}
		//set add to list member_membership_end
		public function listmembershipendaddAction(){

			$this->_helper->viewRenderer('listmembershipendedit');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
				
			
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MemberMembershipEnd();
			
				if($form->validate($_POST))
				{
					$form->insert($_POST, $this->clientid);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}	
		}
		//set delete from list member_membership_end
		public function listmembershipenddeleteAction ()
		{	
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if((int)$_GET['id'] > 0)
			{
				$thrash = Doctrine_Query::create()
				->update("MemberMembershipEnd")
				->set('isdelete', 1)
				->where('id = ?' ,(int)$_GET['id'] )
				->andWhere('clientid = ? ' , $this->clientid);
				$thrash->execute();	
			}
			$this->_redirect(APP_BASE . $this->getRequest()->getControllerName() . "/listmembershipend");
			exit;
		}
		
		//ispc 1739 p.12
		//get view list member_payment_method
		public function listmemberpaymentmethodAction(){
			$clientid = $this->clientid;
				
 
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "10";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
				$order_column = "1";
				if(!empty($_REQUEST['order'][0]['column']))
				{
					$order_column = $_REQUEST['order'][0]['column'];
				}
				$order_dir = $_REQUEST['order'][0]['dir'];
					
				$columns_array = array(
						"1" => "id",
						"2" => "description"
				);
				$order_by_str = addslashes($columns_array[$order_column].' '.$order_dir.' ');
				// ########################################
				// #####  Query for count ###############
				$fdoc1 = Doctrine_Query::create();
				$fdoc1->select('count(*)');
				$fdoc1->from('MemberPaymentMethod');
				$fdoc1->where("clientid = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0  ");
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen($search_value) > 0)
				{
					$fdoc1->andWhere("( description like '%" . trim($search_value) . "%'     )");
				}
				$fdoc1->orderBy($order_by_str);
				$fdocexec = $fdoc1->execute();
				$fdocarray = $fdocexec->toArray();
				$full_count  = $fdocarray[0]['count'];
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*');
				$fdoc1->Where("clientid = ".$clientid);
				$fdoc1->andWhere("isdelete = 0  ");
				
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen($search_value) > 0)
				{
					$fdoc1->andWhere("(description like '%" . trim($search_value) . "%'     )");
				}
				$fdoc1->limit($limit);
				$fdoc1->offset($offset);
				$fdoclimitexec = $fdoc1->execute();
				$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
				$report_ids[] = '99999999999999';
				foreach ($fdoclimit as $key => $report)
				{
					$fdoclimit_arr[$report['id']] = $report;
					$report_ids[] = $report['id'];
				}
				$row_id = 0;
				$link = "";
				$resulted_data = array();
				foreach($fdoclimit_arr as $report_id =>$mdata)
				{
					$link = '%s';
				
					$resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
				
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'member/listmemberpaymentmethodedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
					
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $full_count; // ??
				$response['data'] = $resulted_data;
				
				$this->_helper->json->sendJson($response);
			}
			
		}
		//set edit list	member_payment_method
		public function listmemberpaymentmethodeditAction(){
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer('listmemberpaymentmethodedit');
				
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MemberPaymentMethod();
			
				if($form->validate($_POST))
				{
					$_POST['id'] = (int)$_GET['id'];
					$form->update($_POST, $this->clientid);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			
			if ((int)$_GET['id'] > 0)
			{
				$fdoc = Doctrine::getTable('MemberPaymentMethod')->findbyIdAndClientid((int)$_GET['id'], $this->clientid );
				if ($fdoc)
				{
					$fdocarray = $fdoc->toArray();
					$fdocarray = array_values($fdocarray);
					$fdocarray = $fdocarray[0];
					$this->retainValues($fdocarray);
				}
			}
			
		}
		//set add to list member_payment_method
		public function listmemberpaymentmethodaddAction(){

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
				
			
			$this->_helper->viewRenderer('listmemberpaymentmethodedit');
			if($this->getRequest()->isPost())
			{
				$form = new Application_Form_MemberPaymentMethod();
			
				if($form->validate($_POST))
				{
					$form->insert($_POST, $this->clientid);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}	
		}
		//set delete from list member_payment_method
		public function listmemberpaymentmethoddeleteAction()
		{	

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
				
			if((int)$_GET['id'] > 0)
			{
				$thrash = Doctrine_Query::create()
				->update("MemberPaymentMethod")
				->set('isdelete', 1)
				->where('id = ?' ,(int)$_GET['id'] )
				->andWhere('clientid = ? ' , $this->clientid);
				$thrash->execute();	
			}
			$this->_redirect(APP_BASE . $this->getRequest()->getControllerName() . "/listmemberpaymentmethod");
			exit;
		}
	
		
		
		/**
		 * ISPC-2609 Ancuta 07.09.2020
		 */
		public function printjobdeleteAction(){
		    
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    
		    if ( !empty($_REQUEST['delete']) && !empty($_REQUEST['id']) && $_REQUEST['delete'] == "1" )
		    {
		        $pjb_obj = Doctrine::getTable('PrintJobsBulk')->find($_REQUEST['id']);
		        if($pjb_obj){
		            $pjb_obj->delete();
		        }
		        
		    }
		    
		}
		
		
		/**
		 * ISPC-2609 Ancuta 07.09.2020
		 */
		public function printjobclearAction(){
		    
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    if ( !empty($_REQUEST['user']) && !empty($_REQUEST['client'])  )
		    {
		        //find all - and delete all
		        
		        $fdoc1 = Doctrine_Query::create();
		        $fdoc1->select('*');
		        $fdoc1->from('PrintJobsBulk');
		        $fdoc1->where("clientid = ?", $_REQUEST['client']);
		        $fdoc1->andWhere("user = ?", $_REQUEST['user']);
// 		        $fdoc1->andWhere("print_controller = ?", $_REQUEST['print_controller']);
		        $fdoc1->andWhere("print_controller = ?", 'member');
		        $fdocarray = $fdoc1->fetchArray();
		        
		        if(!empty($fdocarray)){
		            foreach($fdocarray as $job_k=>$job_data){
		                
		                $pjb_obj = Doctrine::getTable('PrintJobsBulk')->find($job_data['id']);
		                if($pjb_obj){
		                    $pjb_obj->delete();
		                }
		            }
		        }
		        
		    }
		    
		}
		
		/**
		 * ISPC-2609 Ancuta 07.09.2020
		 *  + TODO-3668 Ancuta 14.12.2020
		 */
		public function printjobinfoAction(){
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    
		    $user = new User();
		    $user_details = array();
		    $user_details = $user->get_client_users($clientid,1,true);
		    
		    //populate the datatables
		    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost() && !empty($_REQUEST['print_controller'])) {
		        $this->_helper->layout()->disableLayout();
		        $this->_helper->viewRenderer->setNoRender(true);
		        if(!$_REQUEST['length']){
		            $_REQUEST['length'] = "25";
		        }
		        $limit = (int)$_REQUEST['length'];
		        $offset = (int)$_REQUEST['start'];
		        $search_value = addslashes($_REQUEST['search']['value']);
		        
		        $columns_array = array(
		            "0" => "user"
		        );
		        $columns_search_array = $columns_array;
		        
		        if(isset($_REQUEST['order'][0]['column']))
		        {
		            $order_column = $_REQUEST['order'][0]['column'];
		            $order_dir = $_REQUEST['order'][0]['dir'];
		        }
		        else
		        {
		            array_push($columns_array, "id");
		            $nrcol = array_search ('id', $columns_array);
		            $order_column = $nrcol;
		            $order_dir = "ASC";
		        }
		        
		        $order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
		        
		        // ########################################
		        // #####  Query for count ###############
		        $fdoc1 = Doctrine_Query::create();
		        $fdoc1->select('count(*)');
		        $fdoc1->from('PrintJobsBulk');
		        $fdoc1->where("clientid = ?", $clientid);
		        $fdoc1->andWhere("user = ?", $userid);
		        $fdoc1->andWhere("print_controller = ?", $_REQUEST['print_controller']);
		        //$fdoc1->andWhere('DATE(create_date) = ? ', date('Y-m-d'));
		        $fdocarray = $fdoc1->fetchArray();
		        $full_count  = $fdocarray[0]['count'];
		        
		        // ########################################
		        // #####  Query for details ###############
		        $fdoc1->select('*');
		        $fdoc1->orderBy('create_date DESC');
		        $fdoc1->limit($limit);
		        $fdoc1->offset($offset);
		        
		        $fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
		        
		        
		        
		        
		        $qs = Doctrine_Query::create();
		        $qs->select('*');
		        $qs->from('PrintJobsBulk');
		        $qs->where('status ="active" ');
		        $qs->orderBy('create_date ASC');
		        $act_result = $qs->fetchArray();
		        
		        $pnr = 0;
		        foreach($act_result as $pk=>$pactive){
		            $pnr++;
		            $qnr[$pactive['id']] = $pnr;
		        }
		        
		        $report_ids = array();
		        $fdoclimit_arr = array();
		        foreach ($fdoclimit as $key => $report)
		        {
		            $fdoclimit_arr[$report['id']] = $report;
		            $report_ids[] = $report['id'];
		        }
		        
		        $row_id = 0;
		        $link = "";
		        
		        $resulted_data = array();
		        
		        $row_id = 0 ;
		        foreach($fdoclimit_arr as $k=>$data){
		            if($data['status'] == 'active'  ) {
		                $resulted_data[$row_id]['queue_nr'] = $qnr[$data['id']];
		            } else{
		                $resulted_data[$row_id]['queue_nr'] =  '--';
		            }
		            
		            $resulted_data[$row_id]['print_user'] = $user_details[$data['user']];
		            $resulted_data[$row_id]['print_status'] = self::translate('ps_'.$data['status']);
		            $data['clientid_enc']= Pms_Uuid::encrypt($data['clientid']);
		            
		            if($data['status'] == 'completed' && $data['client_file_id'] != 0){
		                
		                $resulted_data[$row_id]['print_link'] = '<a href="'.APP_BASE.'misc/clientfile?doc_id='.$data['client_file_id'].'&cid='.$data['clientid_enc'].'">  <img border="0" src="'.RES_FILE_PATH.'/images/file_download.png" />  </a>';
		                
		            } else{
		                $resulted_data[$row_id]['print_link'] = '--';
		            }
		            $resulted_data[$row_id]['print_date'] = date('d.m.Y H:i',strtotime($data['create_date']));
		            
		            
		            $resulted_data[$row_id]['actions'] = '<a href="javascript:void(0);"  class="job_delete" rel="'.$data['id'].'" id="job_delete_'.$data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		            $row_id++;
		        }
		        
		        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		        $response['recordsTotal'] = $full_count;
		        $response['recordsFiltered'] = $filter_count; // ??
		        $response['data'] = $resulted_data;
		        
		        $this->_helper->json->sendJson($response);
		    }
		    
		}
		
		
		/**
		 * ISPC-2609 + TODO-3668 Ancuta 14.12.2020
		 */
		public function __StartPrintJobs(){
 		    $appInfo = Zend_Registry::get('appInfo');
 		    $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
		    
 		    $function_path = $app_path.'/cron/processprintjobs';
 		    popen('curl -s '.$function_path.' &', 'r');
		}
		
	}

?>