<?php

	Doctrine_Manager::getInstance()->bindComponent('Member', 'SYSDAT');
	

	class Member extends BaseMember {


		/**
		 * private $referal_tab;
		 * 
		 * One Member has One MemberReferalTab.
		 * @OneToOne(targetEntity="MemberReferalTab", inversedBy="MemberReferalTab")
		 * @JoinColumn(name="id", referencedColumnName="referal_tab")
		 * ALTER TABLE MemberReferalTab ADD FOREIGN KEY (memberid) REFERENCES Member(id);
		 */

		
		public function get_client_members($cid, $isdropdwn = 0, $only_active=false , $isdelete = 0 , $ids = false)
		{
			//$translate = new Zend_View_Helper_Translate();
			$usr = Doctrine_Query::create()
				->select('*')
				->from('Member INDEXBY id');
				$usr->where("clientid='" . $cid . "'");
				
			if($isdelete == 0 ){
				$usr->andWhere("isdelete= ?", $isdelete);
			}
			
			if($only_active){
				$usr->andWhere("inactive=0");
			}
			
			if ($ids!==false){
				if (!is_array($ids)){
					$ids = (empty($ids)) ? array("0") : array($ids);
				}
				$usr->andWhereIn('id', $ids);
			}
			
			$usr->orderBy('last_name ASC');
			$memberarr = $usr->fetchArray();

			if($isdropdwn == 1)
			{
				foreach($memberarr as $member)
				{
					$membersS[$member['id']] = $member['last_name'] . ", " . $member['first_name'];
				}
				return $membersS;
			}
			else
			{
				return $memberarr;
			}
		}

		
		//this function should be called for a single memberid, not an array, so it should returs single member array, NOT member collection of arrays
		public function getMemberDetails($memberid = 0, $active = false , $isdelete = false)
		{
			$member_array = array();
			
			$usr = Doctrine_Query::create()
				->select('*, mrt.referal_tab')
				->from('Member m')
				->where("id = ? ", $memberid )
				
				->leftJoin("m.MemberReferalTab mrt")
				//->andWhere('m.id = mrt.memberid')
				
				->orderBy('last_name ASC');
			
			if($active)
			{
				$usr->andWhere('inactive=0');
			}
			if(!$isdelete){
				$usr->andWhere('isdelete=0');
			}
			
			$memberarr = $usr->fetchArray();
			
			
			foreach ($memberarr as $k=>$member){
			    $member_array[$member['id']] = $member;
			}

						
			if($member_array)
			{
				return $member_array;
			}
		}

		// Maria:: Migration ISPC to CISPC 08.08.2020
		public function getMultipleMemberDetails($memberids,$order_by = 'last_name',$sort = "ASC",$upcoming_birthdays = false, $tab = false, $has_member_family = false) // ISPC-2527 Andrei 29.05.2020 added member_family parameter
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		    
			$sort = strtoupper($sort) == 'ASC' ? $sort : 'DESC';
			 
			$order_by_str = $order_by.' '.$sort;
			
			if(empty($memberids)){
    			$memberids[] = '99999999999';
			}
			
			/*if ($upcoming_birthdays) {
			    $sql_active = '';
			}*/
		    /*else*/if($tab == "0"){ // active tab
		        $sql_active = 'AND inactive = 0  '; 
		    } elseif($tab == "1"){ // inactive tab
		        $sql_active = 'AND inactive = 1  '; 
		    } else {
		        $sql_active = ''; 
		    }
		    
		    if ($upcoming_birthdays) {
		        $sql_active = 'AND inactive = 0  '; // Forced removal of inactive members TODO-1524 @ancuta 24.04.2018
		    }
		    //ISPC-1900 order by upcoming birthdays
		    $birthday_sort_sql = '';
		    if($upcoming_birthdays) {
		    	
		    	$birthday_sort_sql .= ", DATE_FORMAT(birthd, '%m%d') as birthd_daymonth, ";
		    	$birthday_sort_sql .= " DATE_FORMAT(birthd, '%Y') as birthd_year, ";
		    	 
		    	$order_by_str = "birthd_daymonth  ASC, birthd_year ASC," . $order_by_str;
		    }
		    
		    // ISPC-2527 Andrei 29.05.2020
		    $member_family_sql = '';
		    if ($has_member_family)
		    {
		        $member_family_sql .= ", mf.*";
                //ISPC-2803,Elena,22.02.2021
		        if($upcoming_birthdays){
                    $member_family_sql.= ", DATE_FORMAT(mf.birthd, '%m%d') as birthd_daymonth, ";
                    $member_family_sql .= " DATE_FORMAT(mf.birthd, '%Y') as birthd_year, ";
                    $member_family_sql .= " floor( DATEDIFF(CURRENT_DATE, STR_TO_DATE(mf.birthd, '%Y-%m-%d'))/365) as age , ";
                    $member_family_sql .= " DATEDIFF(mf.birthd + INTERVAL YEAR(now()) - YEAR(mf.birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(mf.birthd, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd ";

                }
		    }
		    
			//TODO: check what is the age for those born on 28 and 29 February AND 31 December in leap years.. cause you divide by 365 .. 
			//TIMESTAMPDIFF(YEAR, DATE(birthd), CURDATE()) AS age
            //ISPC-2803,Elena,22.02.2021
            //i don't understand why days_to_birthd don't properly calculated and days_to_birthd2 do it
			$usr = Doctrine_Query::create()
				->select("m.*,
				    floor( DATEDIFF(CURRENT_DATE, STR_TO_DATE(birthd, '%Y-%m-%d'))/365) as age, 
				    DATE_FORMAT(birthd, '%m%d') as birthd_daymonth,
				    DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd , 
				      
				    DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd2 "
					. $birthday_sort_sql
				    . $member_family_sql // ISPC-2527 Andrei 29.05.2020
				)
				->from('Member m') // ISPC-2527 Andrei 29.05.2020 added m
				->where('isdelete = 0')
				->andWhere('clientid = '.$clientid.'  '.$sql_active);

            if($upcoming_birthdays && $has_member_family) { //ISPC-2803,Elena,22.02.2021//ISPC-2803,Elena,03.03.2021
                //do nothing because we probably have to calculate it for partners and partner's data match
                //$usr->andWhere(" DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) < 60");
            }else if($upcoming_birthdays){
 				$usr->andWhere(" DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) < 60");
			}else{
				$usr->andWhereIn("id", $memberids);
			}
				
			if ($has_member_family) // ISPC-2527 Andrei 29.05.2020
			{
			    $usr->leftJoin("m.MemberFamily mf");
			}
				
				$usr->orderBy($order_by_str);

			$memberarr = $usr->fetchArray();

			//dd($memberarr);
			//print_r($memberarr);
			if($memberarr)
			{
			    //get statuses
			    $mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			    
			    
			    // get donations
			    $donations_history = MemberDonations::get_donations_history($clientid,$memberids);
			    
	 
			    foreach($donations_history as $donation_id =>$donation_data){
			        $donation_details[$donation_data['member']][] = $donation_data;
			    }
		 
			    
			    // get Invoices
			    $storno_invoices_q = Doctrine_Query::create()
			    ->select("*")
			    ->from('MembersInvoices')
			    ->where('client = ?', $clientid)
			    ->andwhereIn('member', $memberids)
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
			    ->andwhereIn('member', $memberids)
			    ->andwhereNotIn('id', $storno_ids)
			    ->andWhere('isdelete = 0')
			    ->andWhere('storno = 0')
			    ->orderBy('completed_date_sort ASC');
			    $invoices_array = $invoices->fetchArray();
			     
			    foreach($invoices_array as $k => $invoice){
			        $invoices_data[$invoice['member']]['invoice_date'][] = $invoice['completed_date_sort'];
			    }
			    
			    // get memberships
			    $client_memberships = Memberships::get_memberships($clientid);
			    foreach($client_memberships as $k=>$v){
			        $membership_data[$v['id']] = $v;
			    }
			    $current_membership_array = Member2Memberships:: get_memberships_history($clientid,$memberids,true);
			   
			    			    
			    //ISPC-2152
			    $ReasonOfMembershipEnd_list =  array();
			    if (! empty($current_membership_array)) {
			        $ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid);
			    }
			     
			    $end_membership = array();
			    foreach($current_membership_array as $k=>$mb){
			        $actual_membership[$mb['member']] = $mb['membership'];
			         
			        $end_membership[$mb['member']]['start_date'] = date('d.m.Y',strtotime($mb['start_date']));
			         
			        if($mb['end_date'] != "0000-00-00 00:00:00"){
			            $end_membership[$mb['member']]['end_date'] = date('d.m.Y',strtotime($mb['end_date']));
			        }
			         
			        if ($mb['end_reasonid'] > 0) {
			            $end_membership[$mb['member']]['end_reasonid'] = $mb['end_reasonid'];
			            $end_membership[$mb['member']]['end_reasonid_description'] = $ReasonOfMembershipEnd_list[$mb['end_reasonid']]['description'];
			        }
			        /*
			        $membership_period[0]['start'] = $mb['start_date'];
			        $membership_period[0]['end'] = $mb['end_date'];
			        
			        $current_price_list = $p_list->get_client_list_patients_periods($membership_period);
			         
			        if($mb['membership_price'] !="0.00")
			        {
			            $membership_price[$mb['member']]['price'] = $mb['membership_price'];
			        }
			        else
			        {
			            if($current_price_list)
			            {
			                $price_mbs = $price_memberships->get_prices($current_price_list[0]['id'], $clientid);
			            }
			            $membership_price[$mb['member']]['price'] = $price_mbs[$mb['membership']]['price'];
			        }
			        */
			    }
			    
			    
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
			    $docarray = $fdoc->getClientsVoluntaryworkers($vw_clientid);
			    
			    foreach($docarray as $k=>$vw){
			        $vw_details[$vw['id']] = $vw;
			        $vw_name_details[$vw['id']] = $vw['last_name'].', '.$vw['first_name'];
			        $vw_ids[] = $vw['id'];
			    }
			  

			    //payment_method_id => payment_method_description
			    $MemberPaymentMethod_list = MemberPaymentMethod::get_list($clientid  );
			    	
			    
			    $age="";
                //ISPC-2803,Elena,22.02.2021
			    $familyMembers = [];
			    //print_r($memberarr);
				foreach($memberarr as $member)
				{
				    if(strlen($member['title']) > 0 ){
    					$memberarray[$member['id']]['title'] = $member['title']." ";
				    } else {
    					$memberarray[$member['id']]['title'] = $member['title'];
				    }
					$memberarray[$member['id']]['last_name'] = $member['last_name'];
					$memberarray[$member['id']]['first_name'] = $member['first_name'];
                    //ISPC-2803,Elena,22.02.2021
                    $memberarray[$member['id']]['days_to_birthd'] = $member['days_to_birthd2'];

					if($upcoming_birthdays){
                        //ISPC-2803,Elena,22.02.2021
                        $memberarray[$member['id']]['birthd_daymonth'] = $member['birthd_daymonth'];
    					
					    if($member['birthd'] != "0000-00-00"){

					    	$bday = new DateTime($member['birthd']);
					    	$today = new DateTime(date("Y-m-d")); // for testing purposes
					    	 
					    	$diff = $today->diff($bday);
					    	$member['age'] =  $diff->y;
					    	
					    	
					    	//added +1 because it's upcoming, not allready
					        if((($member['age']+1) % 10) == 0) {
					            $age = " <b>(".$member['age']." &raquo; ".($member['age']+1).")</b>";
					            
				            //added to check if today it's the day
					        } elseif ((($member['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')){
					            $age = " <b>(".$member['age'].")</b>";
					        } else{
					            $age = " (".$member['age'] . " &raquo; ".($member['age']+1) . ")";
					        }
					        
        					$memberarray[$member['id']]['birthd'] = date('d.m.Y',strtotime($member['birthd'])).$age;
    					}else{
        					$memberarray[$member['id']]['birthd'] = "";
    					}
    					
					} else {
					    if($member['birthd'] != "0000-00-00"){
        					$memberarray[$member['id']]['birthd'] = date('d.m.Y',strtotime($member['birthd']));
    					}else{
        					$memberarray[$member['id']]['birthd'] = "";
    					}
					}
					
					
					$memberarray[$member['id']]['salutation_letter'] = $member['salutation_letter'];
					
					$tr = new Zend_View_Helper_Translate();
					if($member['gender'] == "1"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("male");
					} else if($member['gender'] == "2"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("female");
					} else if($member['gender'] == "0"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("divers"); //ISPC-2442 @Lore   30.09.2019
					} else{
					    $memberarray[$member['id']]['gender'] = $tr->translate("gender_not_documented");
					}

					//ISPC-2795 Dragos 15.01.2021
					$memberarray[$member['id']]['member_type'] = $tr->translate($member['type'].'_type');
					
					$memberarray[$member['id']]['street1'] = $member['street1'];
					$memberarray[$member['id']]['zip'] = $member['zip'];
					$memberarray[$member['id']]['city'] = $member['city'];
					$memberarray[$member['id']]['phone'] = $member['phone'];
					$memberarray[$member['id']]['mobile'] = $member['mobile'];
					$memberarray[$member['id']]['email'] = $member['email'];
					$memberarray[$member['id']]['membership_type'] = $membership_data [$actual_membership[$member['id']] ]['membership'];
					if(!empty($invoices_data[$member['id']]['invoice_date'])){
    					$memberarray[$member['id']]['last_invoice_date'] = date('d.m.Y',strtotime(end($invoices_data[$member['id']]['invoice_date'])));
					} else{
    					$memberarray[$member['id']]['last_invoice_date'] = "";
					}
					$memberarray[$member['id']]['member_company'] = $member['member_company'];
					
					$memberarray[$member['id']]['voluntary_referance'] = $vw_name_details[$member['vw_id']];
					
					$memberarray[$member['id']]['status'] = $mstatuses[$member['status']]['status'];
					$memberarray[$member['id']]['profession'] = $member['profession'];
					if(!empty($donation_details[$member['id']])){
    				    $last_donation[$member['id']] = end($donation_details[$member['id']]);
                        $memberarray[$member['id']]['last_donation'] = $last_donation[$member['id']]['donation_date'].' ('.$last_donation[$member['id']]['amount'].' &euro;)';;
					} else{
                        $memberarray[$member['id']]['last_donation'] = "-";
					}
					$memberarray[$member['id']]['salutation'] = $member['salutation'];
					
					
					$memberarray[$member['id']]['bank_name'] = $member['bank_name'];
					$memberarray[$member['id']]['iban'] = $member['iban'];
					$memberarray[$member['id']]['payment_method_description'] = $MemberPaymentMethod_list[$member['payment_method_id']]['description'];
					
					$memberarray[$member['id']]['membership_end_reasonid'] = $end_membership[$member['id']]['end_reasonid_description'];
					$memberarray[$member['id']]['membership_start_date'] = $end_membership[$member['id']]['start_date'];
					
					// ISPC-2527 Andrei 02.06.2020
					if ($has_member_family) {
                        //ISPC-2803,Elena,22.02.2021
					    if($upcoming_birthdays){
                            $memberFamily = $member['MemberFamily'];
                            $familyMembers[] = $memberFamily;
                        }

					    if(strlen($member['MemberFamily']['title']) > 0 ){
					        $memberarray[$member['id']]['mf_title'] = $member['MemberFamily']['title']." ";
					    } else {
					        $memberarray[$member['id']]['mf_title'] = $member['MemberFamily']['title'];
					    }
					    $memberarray[$member['id']]['mf_salutation'] = $member['MemberFamily']['salutation'];
					    $memberarray[$member['id']]['mf_salutationletter'] = $member['MemberFamily']['salutation_letter'];
					    $memberarray[$member['id']]['mf_firstname'] = $member['MemberFamily']['first_name'];
					    $memberarray[$member['id']]['mf_lastname'] = $member['MemberFamily']['last_name'];
					    if($member['MemberFamily']['birthd'] != "0000-00-00"){
					        $memberarray[$member['id']]['mf_birthd'] = date('d.m.Y',strtotime($member['MemberFamily']['birthd']));
					    }else{
					        $memberarray[$member['id']]['mf_birthd'] = "";
					    }
					    //ISPC-2527 Lore 25.06.2020
					    if($member['MemberFamily']['gender'] == "1"){
					        $memberarray[$member['id']]['mf_gender'] = $tr->translate("male");
					    } else if($member['MemberFamily']['gender'] == "2"){
					        $memberarray[$member['id']]['mf_gender'] = $tr->translate("female");
					    } else if($member['MemberFamily']['gender'] == "0"){
					        $memberarray[$member['id']]['mf_gender'] = $tr->translate("divers"); //ISPC-2442 @Lore   30.09.2019
					    } else{
					        $memberarray[$member['id']]['mf_gender'] = $tr->translate("gender_not_documented");
					    }
					}
					
				}
                //ISPC-2803,Elena,22.02.2021
                if($upcoming_birthdays && $has_member_family){

                    foreach ($familyMembers as $fmember) {
                        //print_r($fmember);
                        if(empty($fmember)){
                            continue;
                        }

                        if($fmember['birthd'] != "0000-00-00" ){

                            $bday = new DateTime($fmember['birthd']);
                            $today = new DateTime(date("Y-m-d")); // for testing purposes
                            $fmember['birthd_daymonth'] = date('md', strtotime($fmember['birthd']));

                            $diff = $today->diff($bday);
                            $fmember['age'] =  $diff->y;


                            //added +1 because it's upcoming, not allready
                            if((($fmember['age']+1) % 10) == 0) {
                                $age = " <b>(".$fmember['age']." &raquo; ".($member['age']+1).")</b>";

                                //added to check if today it's the day
                            } elseif ((($fmember['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')){
                                $age = " <b>(".$fmember['age'].")</b>";
                            } else{
                                $age = " (".$fmember['age'] . " &raquo; ".($fmember['age']+1) . ")";
                            }

                            $fmember['birthd'] = date('d.m.Y',strtotime($fmember['birthd'])).$age;
                        }else{
                            $fmember['birthd'] = "";
                        }


                        $member_special_id = $fmember['id'] . '_' . $fmember['member_id'];

                        $memberarray[$member_special_id] = $fmember;
                        //$memberarray[$member['id']]['birthd_daymonth'] = '';

                    }
                    //print_r($memberarray);
                    $today = date('md', time());

                    $memberarray = array_filter($memberarray,function($mentry){
                        //print_r($mentry['days_to_birthd']);

                        return (!empty($mentry) && intval($mentry['days_to_birthd'])  < 100);
                    });
                    //echo 'nach filter';
                    //print_r($memberarray);

                    usort($memberarray, function($a, $b) {
                        return $a['birthd_daymonth'] > $b['birthd_daymonth'];
                    });


                }



				return $memberarray;
			}
		}
		

		//ISPC-2401 1)
		public function getMultipleMemberDetails_allyear($memberids,$order_by = 'last_name',$sort = "ASC",$allyear_birthdays = false, $tab = false, $has_member_family = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $sort = strtoupper($sort) == 'ASC' ? $sort : 'DESC';
		    
		    $order_by_str = $order_by.' '.$sort;
		    
		    if(empty($memberids)){
		        $memberids[] = '99999999999';
            } elseif ($tab == "0") { // active tab
		        $sql_active = 'AND inactive = 0  ';
		    } elseif($tab == "1"){ // inactive tab
		        $sql_active = 'AND inactive = 1  ';
		    } else {
		        $sql_active = '';
		    }
		    
		    if ($allyear_birthdays) {
		        $sql_active = 'AND inactive = 0  '; // Forced removal of inactive members TODO-1524 @ancuta 24.04.2018
		    }

		    $birthday_sort_sql = '';
		    if($allyear_birthdays) {
		        
		        $birthday_sort_sql .= ", DATE_FORMAT(birthd, '%m%d') as birthd_daymonth, ";
		        $birthday_sort_sql .= " DATE_FORMAT(birthd, '%Y') as birthd_year, ";
		        
		        $order_by_str = "birthd_daymonth  ASC, birthd_year ASC," . $order_by_str;
		    }
            // ISPC-2527 Andrei 29.05.2020
            $member_family_sql = '';
            if ($has_member_family) {
                $member_family_sql .= ", mf.*";
		    
                $member_family_sql .= ", DATE_FORMAT(mf.birthd, '%m%d') as birthd_daymonth, ";
                $member_family_sql .= " DATE_FORMAT(mf.birthd, '%Y') as birthd_year, ";
                $member_family_sql .= " floor( DATEDIFF(CURRENT_DATE, STR_TO_DATE(mf.birthd, '%Y-%m-%d'))/365) as age , ";
                $member_family_sql .= " DATEDIFF(mf.birthd + INTERVAL YEAR(now()) - YEAR(mf.birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(mf.birthd, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd ";



            }

		    //TODO: check what is the age for those born on 28 and 29 February AND 31 December in leap years.. cause you divide by 365 ..
		    //TIMESTAMPDIFF(YEAR, DATE(birthd), CURDATE()) AS age
		    $usr = Doctrine_Query::create()
		    ->select("*,
				    floor( DATEDIFF(CURRENT_DATE, STR_TO_DATE(birthd, '%Y-%m-%d'))/365) as age,
				    DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd"
		        . $birthday_sort_sql
                    . $member_family_sql
		        )
                ->from('Member m')
		        ->where('isdelete = 0')
		        ->andWhere('clientid = '.$clientid.'  '.$sql_active);
		        
		        if($allyear_birthdays){
                //$usr->andWhere(" DATEDIFF(birthd + INTERVAL YEAR(now()) - YEAR(birthd) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthd, '%m%d'), 1, 0) YEAR, now()) < 365");
		        } else{
		            $usr->andWhereIn("id", $memberids);
		        }
            //ISPC-2803,Elena,22.02.2021
            if ($has_member_family) // ISPC-2527 Andrei 29.05.2020
            {
                $usr->leftJoin("m.MemberFamily mf");
            }


		        
		        $usr->orderBy($order_by_str);
		        
		        $memberarr = $usr->fetchArray();
		        
		        
		        
		        if($memberarr)
		        {
		            //get statuses
		            $mstatuses= MemberStatuses::get_client_member_statuses($clientid);
		            
		            
		            // get donations
		            $donations_history = MemberDonations::get_donations_history($clientid,$memberids);
		            
		            
		            foreach($donations_history as $donation_id =>$donation_data){
		                $donation_details[$donation_data['member']][] = $donation_data;
		            }
		            
		            
		            // get Invoices
		            $storno_invoices_q = Doctrine_Query::create()
		            ->select("*")
		            ->from('MembersInvoices')
		            ->where('client = ?', $clientid)
		            ->andwhereIn('member', $memberids)
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
		            ->andwhereIn('member', $memberids)
		            ->andwhereNotIn('id', $storno_ids)
		            ->andWhere('isdelete = 0')
		            ->andWhere('storno = 0')
		            ->orderBy('completed_date_sort ASC');
		            $invoices_array = $invoices->fetchArray();
		            
		            foreach($invoices_array as $k => $invoice){
		                $invoices_data[$invoice['member']]['invoice_date'][] = $invoice['completed_date_sort'];
		            }
		            
		            // get memberships
		            $client_memberships = Memberships::get_memberships($clientid);
		            foreach($client_memberships as $k=>$v){
		                $membership_data[$v['id']] = $v;
		            }
		            $current_membership_array = Member2Memberships:: get_memberships_history($clientid,$memberids,true);
		            
		            
		            //ISPC-2152
		            $ReasonOfMembershipEnd_list =  array();
		            if (! empty($current_membership_array)) {
		                $ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid);
		            }
		            
		            $end_membership = array();
		            foreach($current_membership_array as $k=>$mb){
		                $actual_membership[$mb['member']] = $mb['membership'];
		                
		                $end_membership[$mb['member']]['start_date'] = date('d.m.Y',strtotime($mb['start_date']));
		                
		                if($mb['end_date'] != "0000-00-00 00:00:00"){
		                    $end_membership[$mb['member']]['end_date'] = date('d.m.Y',strtotime($mb['end_date']));
		                }
		                
		                if ($mb['end_reasonid'] > 0) {
		                    $end_membership[$mb['member']]['end_reasonid'] = $mb['end_reasonid'];
		                    $end_membership[$mb['member']]['end_reasonid_description'] = $ReasonOfMembershipEnd_list[$mb['end_reasonid']]['description'];
		                }

		            }
		            
		            
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
		            $docarray = $fdoc->getClientsVoluntaryworkers($vw_clientid);
		            
		            foreach($docarray as $k=>$vw){
		                $vw_details[$vw['id']] = $vw;
		                $vw_name_details[$vw['id']] = $vw['last_name'].', '.$vw['first_name'];
		                $vw_ids[] = $vw['id'];
		            }
		            
		            
		            //payment_method_id => payment_method_description
		            $MemberPaymentMethod_list = MemberPaymentMethod::get_list($clientid  );
		            
		            
		            $age="";
                    //ISPC-2803,Elena,22.02.2021
                    $familyMembers = [];
		            foreach($memberarr as $member)
		            {
                        //ISPC-2803,Elena,22.02.2021
		                if ($has_member_family && !empty($member['MemberFamily'])) {
                            //if ($upcoming_birthdays) {
                                $memberFamily = $member['MemberFamily'];
                            if($allyear_birthdays) {

                                if ($memberFamily['birthd'] != "0000-00-00") {

                                    $bday = new DateTime($memberFamily['birthd']);
                                    $today = new DateTime(date("Y-m-d")); // for testing purposes

                                    $diff = $today->diff($bday);
                                    $memberFamily['age'] = $diff->y;
                                    $memberFamily['birthd_month'] = date('md', strtotime($memberFamily['birthd']));


                                    //added +1 because it's upcoming, not allready
                                    if ((($memberFamily['age'] + 1) % 10) == 0) {
                                        $age = " <b>(" . $memberFamily['age'] . " &raquo; " . ($memberFamily['age'] + 1) . ")</b>";

                                        //added to check if today it's the day
                                    } elseif ((($memberFamily['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')) {
                                        $age = " <b>(" . $memberFamily['age'] . ")</b>";
                                    } else {
                                        $age = " (" . $memberFamily['age'] . " &raquo; " . ($memberFamily['age'] + 1) . ")";
                                    }

                                    $memberFamily['birthd'] = date('d.m.Y', strtotime($memberFamily['birthd'])) . $age;
                                }
                            }
                                $familyMembers[] = $memberFamily;
                           // }
                        }

		                if(strlen($member['title']) > 0 ){
		                    $memberarray[$member['id']]['title'] = $member['title']." ";
		                } else {
		                    $memberarray[$member['id']]['title'] = $member['title'];
		                }
		                $memberarray[$member['id']]['last_name'] = $member['last_name'];
		                $memberarray[$member['id']]['first_name'] = $member['first_name'];
		                if($allyear_birthdays){
		                    
		                    if($member['birthd'] != "0000-00-00"){
                                //ISPC-2803,Elena,22.02.2021
                                $memberarray[$member['id']]['birthd_month'] = date('md', strtotime($member['birthd']));
		                        
		                        $bday = new DateTime($member['birthd']);
		                        $today = new DateTime(date("Y-m-d")); // for testing purposes
		                        
		                        $diff = $today->diff($bday);
		                        $member['age'] =  $diff->y;
		                        
		                        
		                        //added +1 because it's upcoming, not allready
		                        if((($member['age']+1) % 10) == 0) {
		                            $age = " <b>(".$member['age']." &raquo; ".($member['age']+1).")</b>";
		                            
		                            //added to check if today it's the day
		                        } elseif ((($member['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')){
		                            $age = " <b>(".$member['age'].")</b>";
		                        } else{
		                            $age = " (".$member['age'] . " &raquo; ".($member['age']+1) . ")";
		                        }
		                        
		                        $memberarray[$member['id']]['birthd'] = date('d.m.Y',strtotime($member['birthd'])).$age;
		                    }else{
		                        $memberarray[$member['id']]['birthd'] = "";
		                    }
		                    
		                } else {
		                    if($member['birthd'] != "0000-00-00"){
                                //ISPC-2803,Elena,22.02.2021
                                $memberarray[$member['id']]['birthd_month'] = date('md', strtotime($member['birthd']));
		                        $memberarray[$member['id']]['birthd'] = date('d.m.Y',strtotime($member['birthd']));
		                    }else{
		                        $memberarray[$member['id']]['birthd'] = "";
		                    }
		                }
		                
		                
		                $memberarray[$member['id']]['salutation_letter'] = $member['salutation_letter'];
		                
		                $tr = new Zend_View_Helper_Translate();
		                
		                if($member['gender'] == "1"){
		                    $memberarray[$member['id']]['gender'] = $tr->translate("male");
		                } else if($member['gender'] == "2"){
		                    $memberarray[$member['id']]['gender'] = $tr->translate("female");
		                } else if($member['gender'] == "0"){
		                    $memberarray[$member['id']]['gender'] = $tr->translate("divers"); //ISPC-2442 @Lore   30.09.2019
		                } else{
		                    $memberarray[$member['id']]['gender'] = $tr->translate("gender_not_documented");
		                }
		                
		                $memberarray[$member['id']]['street1'] = $member['street1'];
		                $memberarray[$member['id']]['zip'] = $member['zip'];
		                $memberarray[$member['id']]['city'] = $member['city'];
		                $memberarray[$member['id']]['phone'] = $member['phone'];
		                $memberarray[$member['id']]['mobile'] = $member['mobile'];
		                $memberarray[$member['id']]['email'] = $member['email'];
		                $memberarray[$member['id']]['membership_type'] = $membership_data [$actual_membership[$member['id']] ]['membership'];
		                if(!empty($invoices_data[$member['id']]['invoice_date'])){
		                    $memberarray[$member['id']]['last_invoice_date'] = date('d.m.Y',strtotime(end($invoices_data[$member['id']]['invoice_date'])));
		                } else{
		                    $memberarray[$member['id']]['last_invoice_date'] = "";
		                }
		                $memberarray[$member['id']]['member_company'] = $member['member_company'];
		                
		                $memberarray[$member['id']]['voluntary_referance'] = $vw_name_details[$member['vw_id']];
		                
		                $memberarray[$member['id']]['status'] = $mstatuses[$member['status']]['status'];
		                $memberarray[$member['id']]['profession'] = $member['profession'];
		                if(!empty($donation_details[$member['id']])){
		                    $last_donation[$member['id']] = end($donation_details[$member['id']]);
		                    $memberarray[$member['id']]['last_donation'] = $last_donation[$member['id']]['donation_date'].' ('.$last_donation[$member['id']]['amount'].' &euro;)';;
		                } else{
		                    $memberarray[$member['id']]['last_donation'] = "-";
		                }
		                $memberarray[$member['id']]['salutation'] = $member['salutation'];
		                
		                
		                $memberarray[$member['id']]['bank_name'] = $member['bank_name'];
		                $memberarray[$member['id']]['iban'] = $member['iban'];
		                $memberarray[$member['id']]['payment_method_description'] = $MemberPaymentMethod_list[$member['payment_method_id']]['description'];
		                
		                $memberarray[$member['id']]['membership_end_reasonid'] = $end_membership[$member['id']]['end_reasonid_description'];
		                $memberarray[$member['id']]['membership_start_date'] = $end_membership[$member['id']]['start_date'];
		            }

                    //ISPC-2803,Elena,22.02.2021

                    foreach($familyMembers as $fmember){
                        $member_special_id = $fmember['id'] . '_' . $fmember['member_id'];
                        $memberarray[$member_special_id] = [];
                        $memberarray[$member_special_id] = $fmember;
                    }

                    usort($memberarray, function($a, $b) {
                        return $a['birthd_month']> $b['birthd_month'];
                    });
		            return $memberarray;
		        }
		}
		
		//TODO-3668 Ancuta - added client
		public function get_all_members_details($memberids,$order_by = 'last_name',$sort = "ASC", $clientid = 0 )
		{
		    if(! isset($clientid) ||  $clientid == "0"){
    		    $logininfo = new Zend_Session_Namespace('Login_Info');
    			$clientid = $logininfo->clientid;
		    }
		    
			$p_list = new PriceList();
			$price_memberships = new PriceMemberships();
			
			if(empty($memberids)){
    			$memberids[] = '99999999999';
			}
			
			$usr = Doctrine_Query::create()
				->select('*')
				->from('Member')
				->whereIn("id", $memberids)
				->andWhere('isdelete=0')
				->orderBy($order_by.' '.$sort);
			$memberarr = $usr->fetchArray();

			if($memberarr)
			{
			    // get Invoices

			    $storno_invoices_q = Doctrine_Query::create()
			    ->select("*")
			    ->from('MembersInvoices')
			    ->where('client = "' . $clientid . '"  ')
			    ->andwhereIn('member', $memberids)
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
			    ->andwhereIn('member', $memberids)
			    ->andwhereNotIn('id', $storno_ids)
			    ->andWhere('isdelete = 0')
			    ->andWhere('storno = 0')
			    ->orderBy('completed_date_sort ASC');
			    $invoices_array = $invoices->fetchArray();
			    
			    foreach($invoices_array as $k => $invoice){
			        $invoices_data[$invoice['member']]['invoice_date'][] = $invoice['completed_date_sort'];
			    }			    
			    
			    //get statuses
			    $mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			    
			    
			    // get memberships
			    $client_memberships = Memberships::get_memberships($clientid);
			    foreach($client_memberships as $k=>$v){
			        $membership_data[$v['id']] = $v;
			    }
			    $current_membership_array = Member2Memberships:: get_memberships_history($clientid,$memberids,true);

			    //ISPC-2152
			    $ReasonOfMembershipEnd_list =  array();
			    if (! empty($current_membership_array)) {
			        $ReasonOfMembershipEnd_list = MemberMembershipEnd::get_list($clientid);
			    }
			    
			    $end_membership = array();
			    foreach($current_membership_array as $k=>$mb){
			        $actual_membership[$mb['member']] = $mb['membership'];
			        
			        $end_membership[$mb['member']]['start_date'] = date('d.m.Y',strtotime($mb['start_date']));
			        
			        if($mb['end_date'] != "0000-00-00 00:00:00"){
    			        $end_membership[$mb['member']]['end_date'] = date('d.m.Y',strtotime($mb['end_date']));
			        }
			        
			        if ($mb['end_reasonid'] > 0) {
			            $end_membership[$mb['member']]['end_reasonid'] = $mb['end_reasonid'];
			            $end_membership[$mb['member']]['end_reasonid_description'] = $ReasonOfMembershipEnd_list[$mb['end_reasonid']]['description'];
			        }
			        
			        $membership_period[0]['start'] = $mb['start_date'];
			        $membership_period[0]['end'] = $mb['end_date'];
			        $current_price_list = $p_list->get_client_list_patients_periods($membership_period);
			        
			        if($mb['membership_price'] !="0.00")
			        {
    			        $membership_price[$mb['member']]['price'] = $mb['membership_price'];
			        } 
			        else
			        {
        			    if($current_price_list)
        			    {
        			        $price_mbs = $price_memberships->get_prices($current_price_list[0]['id'], $clientid);
        			    }
    			        $membership_price[$mb['member']]['price'] = $price_mbs[$mb['membership']]['price'];
			        }
			    }

				
				foreach($memberarr as $member)
				{
					$memberarray[$member['id']]['type'] = $member['type'];
					$memberarray[$member['id']]['title'] = $member['title'];
					$memberarray[$member['id']]['last_name'] = $member['last_name'];
					$memberarray[$member['id']]['first_name'] = $member['first_name'];
					if($member['birthd'] != "0000-00-00"){
    					$memberarray[$member['id']]['birthd'] = date('d.m.Y',strtotime($member['birthd']));
					}else{
    					$memberarray[$member['id']]['birthd'] = "";
					}
					$memberarray[$member['id']]['salutation_letter'] = $member['salutation_letter'];
					$memberarray[$member['id']]['salutation'] = $member['salutation'];
					
					$tr = new Zend_View_Helper_Translate();
					
					if($member['gender'] == "1"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("male");
					} else if($member['gender'] == "2"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("female");
					} else if($member['gender'] == "0"){
					    $memberarray[$member['id']]['gender'] = $tr->translate("divers"); //ISPC-2442 @Lore   30.09.2019
					} else{
					    $memberarray[$member['id']]['gender'] = $tr->translate("gender_not_documented");
					}
					
					$memberarray[$member['id']]['street1'] = $member['street1'];
					$memberarray[$member['id']]['zip'] = $member['zip'];
					$memberarray[$member['id']]['city'] = $member['city'];
					$memberarray[$member['id']]['phone'] = $member['phone'];
					$memberarray[$member['id']]['mobile'] = $member['mobile'];
					$memberarray[$member['id']]['email'] = $member['email'];
					$memberarray[$member['id']]['membership_type'] = $membership_data [ $actual_membership[$member['id']] ]['membership'];
					$memberarray[$member['id']]['membership_start_date'] = $end_membership[ $member['id'] ]['start_date'];
					$memberarray[$member['id']]['membership_end_date'] = $end_membership[ $member['id'] ]['end_date'];
					$memberarray[$member['id']]['membership_price'] = $membership_price[ $member['id'] ]['price'];
					
					$memberarray[$member['id']]['membership_end_reasonid'] = $end_membership[ $member['id'] ]['end_date'];
					$memberarray[$member['id']]['membership_end_reasonid_description'] = $end_membership[ $member['id'] ]['end_reasonid_description'];
					
					if(!empty($invoices_data[$member['id']]['invoice_date'])){
    					$memberarray[$member['id']]['last_invoice_date'] = date('d.m.Y',strtotime(end($invoices_data[$member['id']]['invoice_date'])));
					} else{
    					$memberarray[$member['id']]['last_invoice_date'] = "";
					}
					
					$memberarray[$member['id']]['status'] = $mstatuses[$member['status']]['status'];
					$memberarray[$member['id']]['profession'] = $member['profession'];
					$memberarray[$member['id']]['mandate_reference'] = $member['mandate_reference'];
					
					$memberarray[$member['id']]['bank_name'] = $member['bank_name'];       //ISPC-1236 Lore 05.02.2020
					
					$memberarray[$member['id']]['member_company'] = $member['member_company'];       //TODO-3520 Lore 20.10.2020
					
					//ISPC-1236 Lore 12.04.2021
					$memberarray[$member['id']]['member_number'] = $member['member_number'];
					
				}
				return $memberarray;
			}
		}
 
	 

		/**
		 * 
		 * @param string|array with id of $members 
		 * @return void|Ambigous <multitype:, Doctrine_Collection>
		 */
		public function getMembersDetails($members)
		{
			if (empty($members)) {
				return;
			}
			
			$members_arr = array();
			if(is_array($members))
			{
				$members_arr = $members;
			}
			else
			{
				$members_arr[] = $members;
			}
// 			$members_arr[] = '9999999999999';
			$members_arr = array_unique($members_arr);

			$usr = Doctrine_Query::create()
				->select('* , mrt.referal_tab as member_referal_tab')
				->from('Member m')
				->whereIn("id", $members_arr)
				->andWhere('isdelete=0')
				
				->leftJoin("m.MemberReferalTab mrt")
				
				->orderBy('last_name ASC');
			$memberarr = $usr->fetchArray();

			if($memberarr)
			{
				foreach($memberarr as $k_usr => $v_usr)
				{
					$members_details[$v_usr['id']] = $v_usr;
				}

				return $members_details;
			}
		}

 
 

		public function livesearch_members($str, $clientid, $sadmin = false)
		{
			if(strlen(trim(rtrim($str))) > '0')
			{
				$usr = Doctrine_Query::create();
				$usr->select('*');
				$usr->from('Member');

				if($sadmin)
				{
					$usr->where('(clientid = "' . $clientid . '" OR clientid=0) OR membertype = "SA"');
				}
				else
				{
					$usr->where("clientid='" . $clientid . "'");
				}

				$usr->andWhere("(trim(lower(last_name)) like trim(lower('%" . $str . "%'))) or (trim(lower(first_name)) like trim(lower('%" . $str . "%')))");
				$usr->andWhere('isdelete="0"');
				$memberarr = $usr->fetchArray();

				if($memberarr)
				{
					return $memberarr;
				}
				else
				{
					return false;
				}
			}
		}
 
		public function get_clients_members($clientids)
		{
			if($clientids)
			{
				$clientids[] = '0';
				$usr = Doctrine_Query::create()
					->select('*')
					->from('Member')
					->whereIn("clientid", $clientids)
					->andWhere('isdelete = "0"')
					->orderBy('last_name ASC');
				$memberarr = $usr->fetchArray();

				if($memberarr)
				{
					foreach($memberarr as $member)
					{
						$memberarray[$member['id']] = $member;
					}
					return $memberarray;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		
		public function get_all_members_shortname()
		{
		 
			$usr = Doctrine_Query::create()
				->select('*')
				->from('Member')
				->where('isdelete = "0"')
				->orderBy('last_name ASC');
			$memberarr = $usr->fetchArray();

			if($memberarr)
			{
				foreach($memberarr as $member)
				{
					
					//extract initials
					if(empty($member['shortname']))
					{
						$memberarray[$member['id']]['initials'] = mb_substr(trim($member['first_name']), 0, 1, "UTF-8") . "" . mb_substr(trim($member['last_name']), 0, 1, "UTF-8");
					}
					else
					{
						$memberarray[$member['id']]['initials'] = $member['shortname'];
					}
					
					$memberarray[$member['id']]['details'] = $member;
				}
				return $memberarray;
			}
			else
			{
				return false;
			}
		 
		}
		
		
    // #################		    
	// Mandate_referance 	
    // ##################		    
		public function get_higher_mandate_reference($clientid)
		{
			$member_number = Doctrine_Query::create()
				->select('*')
				->from('Member')
				->where("clientid='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->orderBy('mandate_reference DESC')
				->limit('1');
			
			$member_number_data = $member_number->fetchArray();
			
			if($member_number_data)
			{
				return $member_number_data[0];
			}
			else
			{
				return false;
			}
		}
		
		public function get_next_mandate_reference($clientid)
		{
			$client = new Client();
			$client_data = $client->getClientDataByid($clientid);
			
			$member_last_number = $this->get_higher_mandate_reference($clientid);
			
			if($member_last_number)
			{ 
				if($member_last_number['mandate_reference'] >= $client_data[0]['mandate_reference'])
				{	
					$m_number = $member_last_number['mandate_reference'];
					$m_number++;
					
				}
				else
				{  
					$m_number = $client_data[0]['mandate_reference'];
					if($client_data[0]['mandate_reference'] == '0')
					{
						$m_number++;
						
					}
					
				}
			}
			else 
			{
				if(strlen($client_data[0]['mandate_reference']) > 0)
				{
					$m_number = $client_data[0]['mandate_reference'];
					if($client_data[0]['mandate_reference'] == '0')
					{
						$m_number++;
					}
				}
				else
				{
					$m_number ='1';
				}
								
			}
			$membernumber = $m_number;
			return $membernumber;
		}

		
		// #################
		// Member number 
		// #################
		public function get_highest_member_number( $get_client_defined_start =  true)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $invoice_number = Doctrine_Query::create()
		    ->select("id, member_number, cast(member_number as decimal) as dec_member_number")
		    ->from('Member')
		    ->where("clientid='" . $clientid . "'")
		    ->andWhere('member_number REGEXP "^[0-9]+$"')
		    ->andWhere('isdelete = 0')
		    ->orderBy('dec_member_number DESC')
		    ->limit('1');

		    $number_data = $invoice_number->fetchArray();
		    
		    if($number_data){
		        $member_last_number_dec = $number_data[0]['dec_member_number'];
		        $member_last_number = $number_data[0]['member_number'];
		         
		    }
		    
		    if($member_last_number_dec)
		    {
	            $m_number = $member_last_number_dec;
	            $m_number++;

		    }
		    else
		    {
	            $m_number ='1';
		    }
		    
		    $max_strlen =  strlen($member_last_number);
		    
		    //get the client-defined start numbering
		    if ( $get_client_defined_start ) 
		    {
			    $man_obj = new MemberAutoNumber();
			    $start_number = $man_obj->get_start_number($clientid);
			    
			    if ( ! empty($start_number['dec_start_number'])) {

				    if ($start_number['dec_start_number'] > $m_number) {
				    	$m_number = $start_number['dec_start_number'];
				    }
				    
				    if (strlen($start_number['start_number']) > $max_strlen) {
				    	$max_strlen = strlen($start_number['start_number']);
				    }
				    
			    }
			    
		    }

		    
		    
		    if(strlen($m_number) < $max_strlen )
		    {
		        $result = str_pad($m_number, $max_strlen, "0", STR_PAD_LEFT);
		    } 
		    else
		    {
		        $result = $m_number;
		    }
		    
            return $result;
		    
		}
		
		//ispc 1739 p.3
		public function verify_member_name_exists($post, $clientid ){
			/*
			 * company -> first_name last_name member_company
			 * family or person ->  first_name last_name birthd
			 * 
			 * */
			$usr = Doctrine_Query::create()
			->select('*')
			->from('Member')
			->Where('isdelete=0')
			->andWhere('clientid = ? ' , (int)$clientid )//TODO-3089 Ancuta 08.04.2020
			->limit(1);
			
			if(! empty($post['first_name']) ){
				$usr->andWhere('CONVERT(trim(lower(first_name)) USING utf8) = ?', trim(mb_strtolower($post['first_name'], 'UTF-8')));
			}
			if(! empty($post['last_name']) ){
				$usr->andWhere('CONVERT(trim(lower(last_name)) USING utf8) = ?', trim(mb_strtolower($post['last_name'], 'UTF-8')) );
			}
			if(! empty($post['member_company']) ){
				$usr->andWhere('CONVERT(trim(lower(member_company)) USING utf8) = ?', trim(mb_strtolower($post['member_company'], 'UTF-8')) );
			}
			if(! empty($post['birthd']) ){
				$usr->andWhere('birthd = ?', $post['birthd']);
			}
			
			$memberarr = $usr->fetchArray();	
			return $memberarr;
			
		}
		
		//ispc 1739 p.1
		public function get_member_edit_history($clientid = 0, $member_id = 0 , $params =  array() )
		{
			
			
			
			/*
			 * !!! using the allready defined... 
			 * need to change bellow the so it will query dbf, if you will call this function static from another place
			 * 
			 * $genders = Pms_CommonData::getGender();
			 * $mstatuses= MemberStatuses::get_client_member_statuses($clientid);
			 * $memberships = Memberships::get_memberships($clientid);
			 * 
			*/
			$type = $params['type'];
			$genders =  $params['genders'];
			$mstatuses = $params['mstatuses'];
			$memberships = $params['memberships'];
			$reasonofmembershipend = $params['reasonofmembershipend'];
			$payment_method = $params['payment_method'];
			
			//reorder
			foreach ($memberships as $one){
				$memberships[$one['id']] = $one['membership'];
			}
			
			$TR = new Zend_View_Helper_Translate();
			
			$inactive[0]= "Nein";
			$inactive[1]= "Ja";
			
			$types['person'] = $TR->translate('person_type');
			$types['company'] = $TR->translate('company_type');
			$types['family'] = $TR->translate('family_type');
			
			$results = MemberHistory::get_member_history_difference($clientid, $member_id, $type);
			$history = array();
		
			//get all voluntary id's fname+lname
			$vw_ids = array();
			$voluntary_name = array();
			foreach($results as $r){
				foreach($r as $k=>$v){
					if($k == 't1_vw_id'){
						$v= explode(" ||| ", $v);
						if(isset($v[1])){
							$vw_ids[$v[0]] = $v[0];
							$vw_ids[$v[1]] = $v[1];
						}
					}
				}
			}
			if (sizeof($vw_ids) > 0){
				$vw_details = Voluntaryworkers::getClientsVoluntaryworkers($clientid, $vw_ids);
				foreach($vw_details as $k){
					$voluntary_name[$k['id']] = $k['last_name'].', '.$k['first_name'];
				}
			}
			
			$users = array();
			$i=0;
			foreach($results as $r){
				if ($r['t2_action'] == 'insert') continue;
				$i++;

				$history[$i]['date'] = $r['t1_dt_datetime'];
				$history[$i]['action'] = $r['t2_action'];
				$history[$i]['result'] = array();
				$history[$i]['change_user'] = $r['t2_change_user'];
				$users[] = $history[$i]['change_user'];
				$history[$i]['tab_block'] = $r['tab_block'];
				
				foreach($r as $k=>$v){
					if (in_array($k, array('t1_id', 't2_dt_datetime', 't2_action', 't1_create_user'))) continue;
					
					$column_changed = str_replace(array("t1_","t2_"), "", $k) ;		
					if ($v != '' && strpos($v,"|||")!==false ) {
						$history[$i]['result'][$column_changed]['column_name'] = $column_changed;				
						
						$v= explode("|||", $v);
						$v[0] = trim($v[0]);
						$v[1] = trim($v[1]);
						
						if(in_array($column_changed, array('birthd', 'inactive_from' ,'start_date','end_date','donation_date','mandate_reference_date'))){
							if ('0000-00-00' == $v[0] || '0000-00-00 00:00:00' == $v[0]) {
								$v[0] = '';
							}else{
								$v[0] = date('d.m.Y',strtotime($v[0]));
							}
							if ('0000-00-00' == $v[1] || '0000-00-00 00:00:00' == $v[1]) {
								$v[1] = '';
							}else{
								$v[1] = date('d.m.Y',strtotime($v[1]));
							}							
						}
						elseif(in_array($column_changed, array('inactive','auto_member_number' ,'isdelete'))){
							$v[0] = $inactive[$v[0]];
							$v[1] = $inactive[$v[1]];
						}
						elseif($column_changed == 'gender'){
							$v[0] = $genders[$v[0]];
							$v[1] = $genders[$v[1]];
						}
						elseif($column_changed == 'type'){
							$v[0] = $types[$v[0]];
							$v[1] = $types[$v[1]];
						}
						elseif($column_changed == 'status'){
							$v[0] = $mstatuses[$v[0]]['status'];
							$v[1] = $mstatuses[$v[1]]['status'];
						}
						elseif($column_changed == 'vw_id'){
							$v[0] = $voluntary_name[$v[0]];
							$v[1] = $voluntary_name[$v[1]];
						}
						elseif ($column_changed=='membership'){
							$v[0] = $memberships[$v[0]];
							$v[1] = $memberships[$v[1]];
						}
						elseif ($column_changed=='end_reasonid'){
							$v[0] = $reasonofmembershipend[$v[0]];
							$v[1] = $reasonofmembershipend[$v[1]];
						}
						elseif ($column_changed=='merged_parent'){
							if($merged = self::getMemberDetails($v[1], false, true))
							{
								$merged = array_values($merged);
								$merged = $merged[0];
								$v[1] = $merged['last_name'].", ". $merged['first_name'] ;
							}
							$v[0] = "";
						}
						elseif ($column_changed=='payment_method_id'){
							$v[0] = $payment_method[$v[0]];
							$v[1] = $payment_method[$v[1]];
						}
						
						
						$history[$i]['result'][$column_changed]['old'] = htmlentities(nl2br($v[0]));
						$history[$i]['result'][$column_changed]['new'] = htmlentities(nl2br($v[1]));
					}
				}	
			}
			//$users[] = $this->view->create_user;
			//who is thi in the view? i can;t findit
			$users = User::getMultipleUserDetails(array_unique($users),false,$clientid);
			$user_names = array();
			foreach ($users as $k=>$v){
				$user_names[$k] = $v['last_name'] .", ". $v['first_name'];
			}

			
			$tr = array();
			$tr["id"] = $TR->translate("id");
			$tr["create_user"] = $TR->translate("create_user");
			$tr["dt_datetime"] = $TR->translate("dt_datetime");
			$tr["action"] = $TR->translate("action");
			$tr["change_user"] = $TR->translate("change_user");
			$tr["type"] = $TR->translate("type");
			$tr["auto_member_number"] = $TR->translate("auto_member_number");
			$tr["member_number"] = $TR->translate("member_number");
			$tr["member_company"] = $TR->translate("member_company");
			$tr["title"] = $TR->translate("title");
			$tr["salutation_letter"] = $TR->translate("salutation_letter");
			$tr["salutation"] = $TR->translate("salutation");
			$tr["first_name"] = $TR->translate("firstname");
			$tr["last_name"] = $TR->translate("lastname");
			$tr["gender"] = $TR->translate("gender");
			$tr["birthd"] = $TR->translate("birthd");
			$tr["phone"] = $TR->translate("phone");
			$tr["private_phone"] = $TR->translate("private_phone");
			$tr["mobile"] = $TR->translate("mobile");
			$tr["email"] = $TR->translate("email");
			$tr["website"] = $TR->translate("website");
			$tr["fax"] = $TR->translate("fax");
			$tr["street1"] = $TR->translate("street");
			$tr["street2"] = $TR->translate("street2");
			$tr["zip"] = $TR->translate("postcode");
			$tr["city"] = $TR->translate("city");
			$tr["country"] = $TR->translate("country");
			$tr["profession"] = $TR->translate("member_profession");
			$tr["clientid"] = $TR->translate("cccccccc");
			$tr["isdelete"] = $TR->translate("isdelete");
			$tr["inactive"] = $TR->translate("inactive");
			$tr["inactive_from"] = $TR->translate("inactive")." ".$TR->translate("entrydate"); 
			$tr["status"] = $TR->translate("mtstatus");
			$tr["shortname"] = $TR->translate("cccccccc");
			$tr["bank_name"] = $TR->translate("Bank Name");
			$tr["bank_account_number"] = $TR->translate("Bank account number");
			$tr["bank_number"] = $TR->translate("cccccccc");
			$tr["iban"] = $TR->translate("IBAN");
			$tr["bic"] = $TR->translate("BIC");
			$tr["account_holder"] = $TR->translate("account_holder");
			$tr["mandate_reference"] = $TR->translate("mandate_reference");
			$tr["remarks"] = $TR->translate("remarks");
			$tr["memos"] = $TR->translate("memos");
			$tr["comments"] = $TR->translate("comments");
			$tr["img_path"] = $TR->translate("img");
			$tr["vw_id"] = $TR->translate("voluntary_referance");
			//member2memberships
			$tr["membership"] = $TR->translate("membership types");
			$tr["membership_price"] = $TR->translate("th_membership_price");
			$tr["start_date"] = $TR->translate("start_date");
			$tr["end_date"] = $TR->translate("end_date");
			$tr["end_reasonid"] = $TR->translate("end_reasonid");
			//MemberDonations
			$tr["amount"] = $TR->translate("amount");
			$tr["donation_date"] = $TR->translate("date_of_action");
			$tr["insert"] = $TR->translate("add add membership");
			$tr["merged_parent"] = $TR->translate("merged_with");

			$tr["payment_method_id"] = $TR->translate("payment_method");
				
			$tr["mandate_reference_date"] = $TR->translate("mandate_reference_date_history");
			
			$history['history'] = $history;
			$history['translate'] = $tr;
			$history['user_names'] = $user_names;
			
			
			
			//die();
			return $history;
			
		}
		
		//ispc 1739 p.27
		public function set_active_inactiv_members($clientid = 0){
			//get all members that must be active
			$result = Member2Memberships::get_memberships_history(false, false, true);
			$active_ids = array();
			foreach($result as $one){
				$active_ids[ $one['member'] ] = $one['member'];				
			}
			$active_ids = array_values($active_ids);

			$q = Doctrine_Query::create()
			->update('Member')
			->set('inactive', '"0"')
			->set('inactive_from', '"0000-00-00"')
			->set('change_date', ' NOW() ')
			->set('change_user', '"-1"')
			->whereIn('id', $active_ids)
			->andWhere('inactive != 0');
			$q->execute();
			/*
			$q = Doctrine_Query::create()
			->select('id')
			->from('Member')
			->WhereIn('id', $active_ids)
			->andWhere('inactive != ?',0);
			$result = $q->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
			if(!is_array($result)) $result = array($result);
			
			if( count($result) > 0 ){
				$q = Doctrine_Query::create();
				foreach($result as $id){
					$q->update('Member')
					->set('inactive', '"0"')
					->set('inactive_from', '"0000-00-00"')
					->set('change_date', ' NOW() ')
					->set('change_user', '"-1"')
					->where('id = ?', $id)
					->execute();
				}
			}
			*/
			
			
			
			/*
			 * @TODO
			 * if it has membershi_1 and membership_2 and membership_3
			 * and now we fall in between this one of this
			 * we are then inactive=0
			 * we set the inactive_date as the MAX(end_date), with could be from either 1 or 2 or 3
			 * need to get the MAX(end_date) that is in the past from today or max(end_date) if none is in the past
			 * 
			 * SELECT id,
		IFNULL( 
			(SELECT MAX(end_date) FROM `member2memberships` where  member IN (6946) AND end_date<CURDATE() LIMIT 1 ) , 
			(SELECT MAX(end_date) FROM `member2memberships` where  member IN (6946) LIMIT 1)) as max_date
	FROM member2memberships 
	WHERE member=6946 and isdelete=0 group BY member
			 */
			$q = Doctrine_Query::create()
			->select('member , MAX(end_date) as end_date')
			->from('Member2Memberships')
			->Where("isdelete='0'")
			->andWhereNotIn('member', $active_ids)
			->groupBy('member');
			$result = $q->fetchArray();
			$inactive_ids = array();
			$inactive = array();
			foreach($result as $one){
				$inactive_ids[ $one['member'] ] = $one;
				$inactive[] = $one['member'];
			}
			$inactive = array_values($inactive);
			

			$q = Doctrine_Query::create()
			->select('id')
			->from('Member')
			->WhereIn('id', $inactive)
			->andWhere('inactive = ?',0);
			$result = $q->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
			if(!is_array($result)) $result = array($result);
			
			if( count($result) > 0 ){
				$q = Doctrine_Query::create();
				foreach($result as $id){
					$q->update('Member')
					->set('inactive', '"1"')
					->set('inactive_from', '"'.$inactive_ids[$id]['end_date'].'"')
					->set('change_date', ' NOW() ')
					->set('change_user', '"-1"')
					->where('id = ?', $id)
					->execute();
				}
			}
			
			return true;
		}
	
		
		/**
		 *
		 * example:
		 * Member::getMembersNiceName(array(1,2,3) , 1)
		 * Member::getMembersNiceName(array(1,2,3) , 1, "*")
		 * Member::getMembersNiceName(array(1,2,3) , 1, "create_date, change_date")
		 * Member::getMembersNiceName(array(1,2,3) , 1, array("create_date, change_date"))
		 * 
		 * 
		 * edit Jul 24, 2017 @claudiu
		 * you now have the 3rd param $extra_columns so you can pass * or column_name
		 *
		 * @param array $member_id_arr
		 * @param unknown $clientid
		 * @param string $extra_columns
		 * @return Ambigous <multitype:, Doctrine_Collection>|boolean
		 */
		public static function getMembersNiceName( $member_id_arr = array(), $clientid = 0, $extra_columns = null)
		{
			
			if( empty($member_id_arr) || ! is_array($member_id_arr)) {
				return false;
			}
			
			$member_id_arr = array_values(array_unique($member_id_arr));
			
			$extra_columns_sql = '';
			if( ! empty($extra_columns) ) {
				if (is_array($extra_columns)) {
					$extra_columns = implode(", ", $extra_columns) ;
				}
				$extra_columns_sql = $extra_columns . ", ";
			}
				
			$usrarray = Doctrine_Query::create()
			->select( $extra_columns_sql . 'id, type, title, member_company, title, first_name, last_name, email, isdelete, inactive, inactive_from ' )
			->from('Member')
			->whereIn('id', $member_id_arr)
			->andWhere('clientid = ? ' , (int)$clientid )
			->fetchArray();
				
			self::beautifyName($usrarray);
			
			$member_names_array = array();
			
			foreach ( $usrarray as $row ) {
				$member_names_array [ $row['id'] ] = $row ;
			}
			
			return $member_names_array;
		}
		

		public static function beautifyName( &$usrarray )
		{
			//mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
			foreach ( $usrarray as &$k ) 
			{			
				if ( ! is_array($k) || isset($k['nice_name'])) {
					continue; // varaible allready exists, use another name for the variable
				}
				
				if ( $k['type'] == "company")
				{
					if ( trim($k['last_name']) != "" || trim($k['first_name'])) {
						$person = trim($k['title'] . " " . $k['last_name']);
						$person .= trim( $k['first_name']) != '' ? (", " . $k['first_name']) : "";
						$person_test =  trim($k['last_name'] . $k['first_name']);
					}
			
					if (trim($k['member_company']) != '') {
						
						$k['nice_name']  = trim($k['member_company']);
						
						if (trim($person_test) != '' && $person_test != trim($k['member_company']) ) {
							
							$k['nice_name']  = trim($k['member_company']) . " (".$person.")";
						}
					} else {
						
						$k['nice_name']  = $person;
					}
			
				} else {
					$k['nice_name']  = trim($k['title'] . " " . $k['last_name']);
					$k['nice_name']  .= trim( $k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
				}
			}
		}
		
		public function get_highest_mandate_reference_number() 
		{
			//this fn is get NEXT highest_mandate_reference_number
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			$member_last_number_dec = false;

			$number_data = $this->getTable()->createQuery()
			->select("id, mandate_reference, cast(mandate_reference as decimal) as dec_member_number")
// 			->from('Member')
			->where("clientid= ?" , $clientid )
			->andWhere('mandate_reference REGEXP ? ' , $regex="([a-z]*)(_|-)*[0-9]+(\-)*[0-9]+$")
			->andWhere('isdelete = 0')	
			->orderBy('IF(mandate_reference REGEXP \'^[a-z]\', mandate_reference, dec_member_number) DESC')
// 			->orderBy('mandate_reference DESC')
			->limit('1')
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			if($number_data){
								
				$prefix = "";
				preg_match('/^([a-z0-9])*(_|-)*/i' , $number_data['mandate_reference'], $matches, PREG_OFFSET_CAPTURE);
				if ( !empty($matches[0][0])) {
					$prefix = $matches[0][0];
				}
								
				preg_match('/[0-9]+/i' , $number_data['mandate_reference'], $matches, PREG_OFFSET_CAPTURE , strlen($prefix));
				if ( !empty($matches[0][0])) {
// 					print_r($matches);
					
					$number = (float)($matches[0][0]);
					$number++;
					
					$number = number_format($number , 0, "","");
				
					
					if(strlen($number) < strlen($matches[0][0]) ) {
						$number = str_pad($number, strlen($matches[0][0]), "0", STR_PAD_LEFT);
					}
					
					
				}
				
				$result = $prefix . $number ;
// 				$result = $prefix . $number . "-001";
				
				 
			} else {
				//first born
				$result =  "1-001";
			}

			return $result;

		}
	
		public static function get_ids_by_clientid( $clientid = 0 , $isdelete = 0)
		{
			$result = array();
			
			$usrarray = Doctrine_Query::create()
			->select('id')
			->from('Member')
			->Where('clientid = ? ' , (int)$clientid )
			->andWhere('isdelete = ? ' , $isdelete )
			->fetchArray();
			
			if (! empty($usrarray)) {
				$result =  array_column($usrarray, 'id');
			}
			
			return $result;
				
		}
	
		/**
		 * 
		 * @param string $search_string / fn value_patternation is applied to string - ei=ay=ey=ai=..
		 * @param number $clientid
		 * @return array(ids) of the members that match the seach_string
		 * 
		 * @TODO ! function does not search in family members, the 3rd param is intended for that
		 * 
		 */
		public static function search_memberids( $search_string = "", $clientid = 0, $search_familymembers = false) 
		{
			$result = array();
			
// 			$search_string = trim($search_string);
// 			Pms_CommonData::value_patternation($search_string);
			
			
// 			$regexp = mb_strtolower($search_string, 'UTF-8');
// 			$regexp = mb_convert_case(mb_strtolower($search_string, 'UTF-8'), MB_CASE_TITLE, "UTF-8");
			//@claudiu 12.2017, changed Pms_CommonData::value_patternation
			$regexp = $search_string;
			
			$query_params =  array("clientid"	=> $clientid);
			
			$member_ids = Doctrine_Query::create()
			->select('id')
			->from('Member')
			->where('clientid = :clientid' );
			
// 			->andWhere('LOWER(CONVERT(CONCAT_WS(
// 			->andWhere('(CONVERT(CONCAT_WS(
// 					" ",
// 					member_company,
// 		    		title,
// 					salutation_letter,
// 					salutation,
// 					last_name,
// 					first_name,
// 					birthd,
// 					phone,
// 					private_phone,
// 					mobile,
// 					email,
// 					website,
// 					fax,
// 					street1,
// 					street2,
// 					zip,
// 					city,
// 					country,
// 					profession,
// 					remarks,
// 					memos,
// 					comments) USING utf8 )) COLLATE UTF8_GENERAL_CI
// 					REGEXP :reg_exp')
// 			->fetchArray( array(
// 					"clientid"	=> $clientid,
// 					"reg_exp"	=> $regexp
// 			));
			
			

			if ( ! empty($search_string)) {
			    
			    $regexp = trim($search_string);
			    Pms_CommonData::value_patternation($regexp, false, true);
			
			    $filter_search_value_arr = array();
			    $regexp_arr = array();
			    
			    foreach ($regexp as $word) {
			
			        $filter_search_value_arr [] = 'CONVERT(CAST(member_company as BINARY) USING utf8) LIKE CONVERT(CAST(:member_company as BINARY) USING utf8)';
			        $regexp_arr['member_company'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(title as BINARY) USING utf8) LIKE CONVERT(CAST(:title as BINARY) USING utf8)';
			        $regexp_arr['title'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(salutation_letter as BINARY) USING utf8) LIKE CONVERT(CAST(:salutation_letter as BINARY) USING utf8)';
			        $regexp_arr['salutation_letter'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(salutation as BINARY) USING utf8) LIKE CONVERT(CAST(:salutation as BINARY) USING utf8)';
			        $regexp_arr['salutation'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(first_name as BINARY) USING utf8) LIKE CONVERT(CAST(:first_name as BINARY) USING utf8)';
			        $regexp_arr['first_name'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(last_name as BINARY) USING utf8) LIKE CONVERT(CAST(:last_name as BINARY) USING utf8)';
			        $regexp_arr['last_name'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(birthd as BINARY) USING utf8) LIKE CONVERT(CAST(:birthd as BINARY) USING utf8)';
			        $regexp_arr['birthd'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(phone as BINARY) USING utf8) LIKE CONVERT(CAST(:phone as BINARY) USING utf8)';
			        $regexp_arr['phone'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(private_phone as BINARY) USING utf8) LIKE CONVERT(CAST(:private_phone as BINARY) USING utf8)';
			        $regexp_arr['private_phone'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(mobile as BINARY) USING utf8) LIKE CONVERT(CAST(:mobile as BINARY) USING utf8)';
			        $regexp_arr['mobile'] = '%'. $word .'%';
			        $filter_search_value_arr ['email'] = 'CONVERT(CAST(email as BINARY) USING utf8) LIKE CONVERT(CAST(:email as BINARY) USING utf8)';
			        $regexp_arr['email'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(website as BINARY) USING utf8) LIKE CONVERT(CAST(:website as BINARY) USING utf8)';
			        $regexp_arr['website'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(fax as BINARY) USING utf8) LIKE CONVERT(CAST(:fax as BINARY) USING utf8)';
			        $regexp_arr['fax'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(street1 as BINARY) USING utf8) LIKE CONVERT(CAST(:street1 as BINARY) USING utf8)';
			        $regexp_arr['street1'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(street2 as BINARY) USING utf8) LIKE CONVERT(CAST(:street2 as BINARY) USING utf8)';
			        $regexp_arr['street2'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(zip as BINARY) USING utf8) LIKE CONVERT(CAST(:zip as BINARY) USING utf8)';
			        $regexp_arr['zip'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(city as BINARY) USING utf8) LIKE CONVERT(CAST(:city as BINARY) USING utf8)';
			        $regexp_arr['city'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(country as BINARY) USING utf8) LIKE CONVERT(CAST(:country as BINARY) USING utf8)';
			        $regexp_arr['country'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(profession as BINARY) USING utf8) LIKE CONVERT(CAST(:profession as BINARY) USING utf8)';
			        $regexp_arr['profession'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(remarks as BINARY) USING utf8) LIKE CONVERT(CAST(:remarks as BINARY) USING utf8)';
			        $regexp_arr['remarks'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(memos as BINARY) USING utf8) LIKE CONVERT(CAST(:memos as BINARY) USING utf8)';
			        $regexp_arr['memos'] = '%'. $word .'%';
			        $filter_search_value_arr [] = 'CONVERT(CAST(comments as BINARY) USING utf8) LIKE CONVERT(CAST(:comments as BINARY) USING utf8)';
			        $regexp_arr['comments'] = '%'. $word .'%';
			    }
			
			
			    $regexp = trim($search_string);
			    Pms_CommonData::value_patternation($regexp);
			    $filter_search_value_arr[] = 'CONVERT( CONCAT_WS(" ",
		    								member_company,
		    								title,
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
											comments ) USING utf8 ) REGEXP :reg_exp';
			    $regexp_arr['reg_exp'] = $regexp;
			
			    /*
			     * original query before ispc-1739
			     $filters['member'] .= ' AND (CONCAT(last_name,first_name,title,street1,zip,city,phone,email) LIKE "%' . addslashes($_REQUEST['f_keyword']) . '%")';
			     */
			    $member_ids->andWhere( '(' . implode( ' OR ', $filter_search_value_arr) .')' , $regexp_arr);
			    
			    
			    $query_params = array_merge($query_params, $regexp_arr);
			
			   
			
			}
				
			$member_ids = $member_ids->fetchArray( $query_params );
			
			if (! empty($member_ids)) {
				$result =  array_column($member_ids, 'id');
			}
			
			return $result;
				
		}
		
		/**
		 * this fn will search only as not empty string 
		 * @param unknown $columns_search
		 * @param number $clientid
		 * @return void|multitype:
		 */
		public function search_memberids_by_columns( $columns_search = array(), $clientid = 0 )
		{
			
			if (empty($columns_search) || ! is_array($columns_search)) {
				return;
			}
			$result = array();

			$query = $this->getTable()->createQuery()
			->select('id')
			->where('clientid = :clientid' );
			
			$allowed_columns = $this->getTable()->getColumns();
			
			
			$values = array();
			
			foreach ($columns_search as $k => $v) {
				if (isset($allowed_columns[$k])) {
					
					$search_string = trim($v);
					Pms_CommonData::value_patternation($search_string);
					//$regexp = mb_strtolower($search_string, 'UTF-8'); 
					//@claudiu 12.2017, changed Pms_CommonData::value_patternation
					$regexp = $search_string;
					
					if (!empty($regexp)) {
						$query->andWhere("{$k} REGEXP :{$k}");
						$values[$k] = $regexp;
					}
				}
			}

			
			$member_ids = $query->fetchArray( array_merge(array("clientid"	=> $clientid), $values));
								
						
			if (! empty($member_ids)) {
				$result =  array_column($member_ids, 'id');
			}
								
			return $result;
		
		}
		
		
	}

?>
