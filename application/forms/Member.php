<?php
require_once ("Pms/Form.php");
class Application_Form_Member extends Pms_Form 
{

	public function validate($post) 
	{
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		
		$did = (!empty($_GET ['id'])) ? (int)$_GET ['id'] : 0;
		
		$Tr = new Zend_View_Helper_Translate ();
		
		$error = 0;
		$val = new Pms_Validation ();
		
		if ($post ['type'] == 'company') {
			if (! $val->isstring ( $post ['member_company'] )) {
				$this->error_message ['member_company'] = $Tr->translate ( 'enter_member_company' );
				$error = 5;
			}
		} else {
			
			if (! $val->isstring ( $post ['first_name'] )) {
				$this->error_message ['first_name'] = $Tr->translate ( 'enterfirstname' );
				$error = 5;
			}
			
			if (! $val->isstring ( $post ['last_name']))
    			{
    			    $this->error_message['last_name'] = $Tr->translate('enterlastname');
    			    $error = 5;
    			}
    			
    			
/*
    			if(!$val->isstring($post['birthd']))
    			{
    			    $this->error_message['birthd'] = $Tr->translate('birthdate_error');
    			    $error = 11;
    			}
  */  			
    			if(date('Y', strtotime($post['birthd'])) < '1900')
    			{
    			    $this->error_message['birthd'] = $Tr->translate('birthdate_error_before_1900');
    			    $error = 12;
    			}
    			
    			if($val->isstring($post['birthd']))
    			{
    			    list($BirthDay, $BirthMonth, $BirthYear) = explode(".", $post['birthd']);
    			    $bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
    			    $curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y"));
    			
    			
    			    if($bdt_time > $curr_time)
    			    {
    			        $this->error_message['birthd'] = $Tr->translate('birthdateg_error');
    			        $error = 14;
    			    }
    			}
    			//ispc 1739 
    			if($post['type'] == 'family'){
    				if(!$val->isstring($post['first_name_child']))
    				{
    					$this->error_message['child_first_name'] = $Tr->translate('enterfirstname');
    					$error = 5;
    				}
    				if(!$val->isstring($post['last_name_child']))
    				{
    					$this->error_message['child_last_name'] = $Tr->translate('enterlastname');
    					$error = 5;
    				}
    				
    				if(date('Y', strtotime($post['birthd_child'])) < '1900')
    				{
    					$this->error_message['child_birthd'] = $Tr->translate('birthdate_error_before_1900');
    					$error = 12;
    				}
    				 
    				if($val->isstring($post['birthd_child']))
    				{
    					list($BirthDay, $BirthMonth, $BirthYear) = explode(".", $post['birthd_child']);
    					$bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
    					$curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y"));
    					 
    					 
    					if($bdt_time > $curr_time)
    					{
    						$this->error_message['child_birthd'] = $Tr->translate('birthdateg_error');
    						$error = 14;
    					}
    				}
    			}

			}
			//var_dump($this->error_message); die();
			if( $post['inactive'] == "1" && strlen($post['inactive_from'])){
			    $post_inactive_array = explode(".",$post['inactive_from']);
			    $day = $post_inactive_array[0];
			    $month = $post_inactive_array[1];
			    $year = $post_inactive_array[2];
				
    			if(checkdate($month,$day,$year) === false)
    			{
    			    $this->error_message['inactive_from'] = $Tr->translate('inactive_from_error_invalid');
    			    $error = 16;
    			}
			}
			
			if( $post['inactive'] == "1"){
			    // check if membersips are opened
			    $opened = 0 ;
			    foreach($post['membership'] as $mid=>$mdata){
			        if(empty($mdata['end'])){
			            $opened ++;
			        }
			    }
			    

			    if($opened >  0)
			    {
			        $this->error_message['inactive_membership_end'] = $Tr->translate('inactive_membership_end_must_be_filled');
			        $error = 17;
			    }
			    
			}			
			
			
			
			if((strlen($post['bank_name']) > 0 ||  strlen($post['iban']) > 0 ||  strlen($post['account_holder']) > 0 )){
				$mandate_reference_Regex = '/^([A-Za-z0-9]|[\-]){1,35}?$/i';
				
				// validate MndtRltdInf >> MndtId
				if(strlen($post['mandate_reference']) > 0 ){
					
					if (! preg_match( $mandate_reference_Regex, trim($post['mandate_reference']))) {				
						$this->error_message['mandate_reference'] = $Tr->translate("mandate_reference") . " A-Z, a-z, 0-9, -, max 35 (ex: MandatTest-8)";
						$error = 13;
					}else{
						//verify if duplicate
						$member_nb = Doctrine_Query::create()
							->select('id')
							->from('Member')
							->where("clientid = ? ", $clientid)
							->andWhere('isdelete = 0')
							->andWhere("id != ? ", $did )
							->andWhere('mandate_reference = ? ', $post['mandate_reference']);
						
						$memberarray = $member_nb->fetchArray();
						
						if(count($memberarray)>0)
						{
							$this->error_message['mandate_reference'] = $Tr->translate("mandaterefexists");
							$error = 13;
						}
					}
				}
				
				
				//iban bic validate
				if(strlen(trim($post['iban'])) > 0)
				{
					$iban_Validator = new Pms_SepaIbanValidator(array(
							'allow_non_sepa'=>false ,
							'iban'=>$post['iban']));
				
					$iban_is_valid = false;
					if ( !$iban_is_valid =  $iban_Validator->isValid() ){
						$this->error_message['iban'] = $Tr->translate('IBAN validation failed');
						$error = 13;
					}
				
					if ( $iban_is_valid && strlen(trim($post['bic'])) > 0 && ! $iban_Validator->bic_isValid($post['bic']) ){
						$this->error_message['bic'] = $Tr->translate('BIC validation failed');
						$error = 13;
					}
				}
				
				
			}
			
			if( !empty($post['membership']) && $post['sepa_is_active_input'] == "1"){
				if($post['sepa_howoften'] == "monthly" && ((int)$post['sepa_when_monthly'] == 0 || (int)$post['sepa_when_monthly']>30)  ){
					$this->error_message['sepa_when_monthly'] = $Tr->translate("error_sepa_when_monthly");
					$error = 1;
				}
				elseif($post['sepa_howoften'] == "quarterly" && ((int)$post['sepa_when_quarterly'] == 0 || (int)$post['sepa_when_quarterly']>90)  ){
					$this->error_message['sepa_when_quarterly'] = $Tr->translate("error_sepa_when_quarterly");
					$error = 1;
				}
				elseif($post['sepa_howoften'] == "annually" && ((int)$post['sepa_when_annually'] == 0 || (int)$post['sepa_when_annually']>365)  ){
					$this->error_message['sepa_when_annually'] = $Tr->translate("error_sepa_when_annually");
					$error = 1;
				}
				
				if($post['sepa_howoften'] == "monthly" && empty($post['sepa_month'])){
					$this->error_message['sepa_month'] = $Tr->translate("error_sepa_month");
					$error = 1;
				}
				elseif($post['sepa_howoften'] == "quarterly" && empty($post['sepa_quarter'])){
					$this->error_message['sepa_quarter'] = $Tr->translate("error_sepa_quarter");
					$error = 1;
				}
			}

			if($error == 0)
			{
				return true;
			} else {
				//$this->error_message['message'] = $Tr->translate("General Error, please verify all tabs");
			}

			return false;
		}

		public function InsertData($post , $verify_duplicate = true )
		{
    		$logininfo = new Zend_Session_Namespace('Login_Info');
   		    $clientid = $logininfo->clientid;
   		    $Tr = new Zend_View_Helper_Translate();
			$fdoc = new Member();
			$fdoc->clientid = $clientid;
			$fdoc->type = $post['type'];
			$fdoc->member_company= $post['member_company'];
			
			$fdoc->title = $post['title'];
			
			//ISPC-1739
			if (trim($post['salutation_letter']) == ''){
/* 				$post['salutation_letter'] = trim($post['salutation']) 
					. " " . trim($post['title']) 
					. " " . trim(ucfirst(strtolower($post['first_name']))) 
					. " " . trim(ucfirst(strtolower($post['last_name']))); */
			    //TODO-3847 Lore 10.02.2021
			    $post['salutation_letter'] = trim($post['salutation']);
			    $post['salutation_letter'] .= (trim($post['title']) != "") ? " ".trim($post['title']) : "";
			    //$post['salutation_letter'] .= " " . trim($post['first_name']) . " " . trim($post['last_name']);     
			    $post['salutation_letter'] .= " " . trim($post['last_name']);    //TODO-3272 Lore 15.02.2021
			    
				
				if (trim($post['salutation_letter']) != ""){
				    //$post['salutation_letter'] = $Tr->translate('dear_sir_madam') . " ". $post['salutation_letter'];
				    //TODO-3847 Lore 10.02.2021
				    if($post['gender'] == '2'){
				        $post['salutation_letter'] = $Tr->translate('dear_madam') . " ". trim($post['salutation_letter']);
				    } else {
				        $post['salutation_letter'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter']);
				    }
				}
			}
			$fdoc->salutation_letter = trim($post['salutation_letter']);

			// ISPC - 1518
			$fdoc->auto_member_number= $post['auto_member_number'];
			$fdoc->member_number = $post['member_number'];
			$fdoc->salutation = $post['salutation'];
			
			$fdoc->first_name = $post['first_name'];
			$fdoc->last_name = $post['last_name'];
			if($post['birthd'])
			{
				$post['birthd'] = date('Y-m-d',strtotime($post['birthd']));
			}else{
				$post['birthd'] ="0000-00-00";
			}
			$fdoc->birthd = $post['birthd'];
			
			
			
			$fdoc->gender = $post['gender'];
			$fdoc->street1 = $post['street'];
			$fdoc->zip = $post['zip'];
			$fdoc->city = $post['city'];
			$fdoc->fax = $post['fax'];
			$fdoc->phone = $post['phone'];
			$fdoc->mobile = $post['mobile'];
			$fdoc->email = $post['email'];
			$fdoc->bank_name = $post['bank_name'];
			$fdoc->iban = $post['iban'];
			$fdoc->bic = $post['bic'];
			$fdoc->account_holder = $post['account_holder'];
			$fdoc->mandate_reference = $post['mandate_reference'];
			
			if ( ! empty($post['mandate_reference_date']) && trim($post['mandate_reference_date']) != '') {
				$post['mandate_reference_date']  = date("Y-m-d", strtotime($post['mandate_reference_date']));
			} else {
				$post['mandate_reference_date'] = NULL;
			}
			
			$fdoc->mandate_reference_date = $post['mandate_reference_date'];
			// ISPC - 1518
			$fdoc->remarks = $post['remarks'];
			
			$fdoc->inactive = $post['inactive'];
			
		
			if($post['inactive'] == "1" && strlen($post['inactive_from']) > 0){
			    $fdoc->inactive_from = date('Y-m-d',strtotime($post['inactive_from']));
			} else {
			    $fdoc->inactive_from = "0000-00-00";
			}
				
			
			$fdoc->status = $post['status'];
			$fdoc->profession = $post['profession'];
			$fdoc->vw_id = $post['vw_id'];
			
			// ISPC-1739
			$fdoc->street2 = $post['street2'];
			$fdoc->country = $post['country'];
			$fdoc->website = $post['website'];
			$fdoc->memos = $post['memos'];
			$fdoc->comments = $post['comments'];
			$fdoc->payment_method_id = $post['payment_method_id'];
			
			//ispc-1739 verify if member allready exists
			/* if($verify_duplicate){
				$verify = $fdoc->verify_member_name_exists($post , $clientid);
				if (is_array($verify) && isset($verify[0]['id'])){
					$this->error_message['member_exists'] = $Tr->translate('member_allready_exists');
					return false;
				}
			} */
			if(!empty($post['merged_parent'])) $fdoc->merged_parent = $post['merged_parent'];
			if(!empty($post['merged_slave'])) $fdoc->merged_slave = $post['merged_slave'];
			
			$fdoc->save();

			if($fdoc)
			{
			
				$inserted_id = $fdoc->id;

				//ispc 1881
				$mrt = new MemberReferalTab();
				$mrt->set_referal_tab($clientid, $inserted_id, $post['member_referal_tab'] ); 
				
				$icons = new MemberIcons();
				$icons->set_member_icon($inserted_id, $post['custom_icons'], $clientid);
				
				
				/* ################## DONATIONS #################################### */
				if(!empty($post['donation']) && $inserted_id)
				{
				     
				    foreach($post['donation'] as $k => $a_values)
				    {
				         
				         
				        if(!empty($a_values['amount']) )
				        {
				            if(strlen($a_values['donation_date']) > 0)
				            {
				                $a_values['donation_date'] = date('Y-m-d', strtotime($a_values['donation_date']));
				            }
				            else
				            {
				                $a_values['donation_date'] = date('Y-m-d');
				            }
				             
				            $donations_data_array[] = array(
				                'clientid' => $clientid,
				                'member' => $inserted_id,
				                'donation_date' => $a_values['donation_date'],
				                'amount' => $a_values['amount']
				            );
				        }
				    }
				     
				    $collection = new Doctrine_Collection('MemberDonations');
				    $collection->fromArray($donations_data_array);
				    $collection->save();
				}
				
				
			}
			
			if(!empty($_SESSION['filename']))
			{
				$this->move_uploaded_icon($inserted_id);
			}
			
			// ISPC-1739 p.7
			if($post['type'] == 'family'){
				$post['member_id'] = $inserted_id;
				$post['title'] = $post['title_child'];
				$post['salutation'] = $post['salutation_child'];
				$post['first_name'] = $post['first_name_child'];
				$post['last_name'] = $post['last_name_child'];
				$post['birthd'] = $post['birthd_child'];
				$post['gender'] = $post['gender_child'];
				if (trim($post['salutation_letter_child']) == ''){
					$post['salutation_letter_child'] = trim($post['salutation']);
					$post['salutation_letter_child'] .= (trim($post['title']) != "") ? " ".trim($post['title']) : "";       //TODO-3847 Lore 10.02.2021
					//$post['salutation_letter_child'] .= " " . trim($post['first_name'])	. " " . trim($post['last_name']);   //TODO-3847 Lore 10.02.2021
					$post['salutation_letter_child'] .= " " . trim($post['last_name']);   //TODO-3272 Lore 15.02.2021
					//. " " . trim(ucfirst(strtolower($post['first_name'])));
					//. " " . trim(ucfirst(strtolower($post['last_name'])));
					
					if (trim($post['salutation_letter_child']) != ""){
						//$post['salutation_letter_child'] = $Tr->translate('dear_sir_madam') . " " .$post['salutation_letter_child'];
					    //TODO-3847 Lore 10.02.2021
					    if($post['gender'] == '2'){
					        $post['salutation_letter_child'] = $Tr->translate('dear_madam') . " ". trim($post['salutation_letter_child']);
					    } else {
					        $post['salutation_letter_child'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter_child']);
					    }
					}
				
				}
				$post['salutation_letter'] = trim($post['salutation_letter_child']);
				$post['clientid'] = $clientid;
				$this->InsertUpdate_FamilyMember_Data($post ,'insert');
			}
			
			
			
			return $inserted_id;
		}
	    
		public function UpdateData($post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $Tr = new Zend_View_Helper_Translate();
			if(!empty($_SESSION['filename']))
			{
				$this->move_uploaded_icon($post['did']);
			}

			//$fdoc = Doctrine::getTable('Member')->find($post['did']);
			$fdoc = Doctrine::getTable('Member')->findByIdAndClientid($post['did'], $clientid);
			$fdoc = $fdoc{0};
			
			$fdoc->clientid = $clientid;
			$fdoc->type = $post['type'];
			
			
			$fdoc->member_company= $post['member_company'];
			$fdoc->title = $post['title'];
			
			//ISPC - 1518 
			$fdoc->auto_member_number= $post['auto_member_number'];
			$fdoc->member_number= $post['member_number'];
			$fdoc->salutation= $post['salutation'];

			// ISPC-1739
			if (trim($post['salutation_letter']) == ''){
				$post['salutation_letter'] = trim($post['salutation']);
				if(trim($post['title']) != ''){
					$post['salutation_letter'] .= " " . trim($post['title']);
				}
				if(trim($post['first_name']) != ''){
					//$post['salutation_letter'] .= " " . trim(mb_convert_case(mb_strtolower($post['first_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));
				    //$post['salutation_letter'] .= " " . trim($post['first_name']);       //TODO-3847 Lore 10.02.2021 //TODO-3272 Lore 15.02.2021
				}
				if(trim($post['last_name']) != ''){
					//$post['salutation_letter'] .= " " . trim(mb_convert_case(mb_strtolower($post['last_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));
				    $post['salutation_letter'] .= " " . trim($post['last_name']);       //TODO-3847 Lore 10.02.2021
				}
				if (trim($post['salutation_letter']) != ""){
					//$post['salutation_letter'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter']);
				    //TODO-3847 Lore 10.02.2021
				    if($post['gender'] == '2'){
				        $post['salutation_letter'] = $Tr->translate('dear_madam') . " ". trim($post['salutation_letter']);
				    } else {
				        $post['salutation_letter'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter']);
				    }
				}
			}
			$fdoc->salutation_letter = trim($post['salutation_letter']);
			$fdoc->first_name = $post['first_name'];
			$fdoc->last_name = $post['last_name'];
		
			if($post['birthd'])
			{
				$fdoc->birthd = date('Y-m-d',strtotime($post['birthd']));
			}else{
				$fdoc->birthd ="0000-00-00";
			}
			
			
			$fdoc->gender = $post['gender'];
			$fdoc->street1 = $post['street'];
			$fdoc->zip = $post['zip'];
			$fdoc->city = $post['city'];
			
			//ISPC - 1518
			$fdoc->fax = $post['fax'];
			
			$fdoc->phone = $post['phone'];
			$fdoc->mobile = $post['mobile'];
			$fdoc->email = $post['email'];
			$fdoc->bank_name = $post['bank_name'];
			$fdoc->iban = $post['iban'];
			$fdoc->bic = $post['bic'];
			$fdoc->account_holder = $post['account_holder'];
			$fdoc->mandate_reference = $post['mandate_reference'];
			
			
			if ( ! empty($post['mandate_reference_date']) && trim($post['mandate_reference_date']) != '') {
				$post['mandate_reference_date']  = date("Y-m-d", strtotime($post['mandate_reference_date']));
			} else {
				$post['mandate_reference_date'] = NULL;
			}
			
			$fdoc->mandate_reference_date = $post['mandate_reference_date'];
			// ISPC - 1518 
			$fdoc->remarks = $post['remarks'];
			
			$fdoc->inactive = $post['inactive'];

			if($post['inactive'] == "1" && strlen($post['inactive_from']) > 0){
			    $fdoc->inactive_from = date('Y-m-d',strtotime($post['inactive_from']));
			} else {
			    $fdoc->inactive_from = "0000-00-00";
			}
			
			$fdoc->status = $post['status'];
			$fdoc->profession = $post['profession'];
			$fdoc->vw_id = $post['vw_id'];
			
			// ISPC-1739
			$fdoc->street2 = $post['street2'];
			$fdoc->country = $post['country'];
			$fdoc->website = $post['website'];
			$fdoc->memos = $post['memos'];
			$fdoc->comments = $post['comments'];
			$fdoc->payment_method_id = $post['payment_method_id'];
			$fdoc->save();			

			//save icons
			
			$icons = new MemberIcons();
			$icons->set_member_icon($post['did'], $post['custom_icons'], $clientid);

// 			die(print_r($fdoc));
			//ispc 1881 save referal_tab = members/donors 
			$mrt = new MemberReferalTab();
			$mrt->set_referal_tab($clientid , $post['did'], $post['member_referal_tab']);
			
			
			if($fdoc)
			{ 
			    /* ################## DONATIONS #################################### */
				//ispc 1739
				if((empty($post['donation']) || $post['delete_donation_ids'] != '0') && $post['did']){
					$donation_ids = array();
					if( $post['delete_donation_ids'] != '0'){
						$donation_ids = explode(',', $post['delete_donation_ids']);
						array_walk($donation_ids, create_function('&$val', '$val = trim($val);'));
					}
					$this->reset_member_donations($post['did'],$clientid, $donation_ids);
				}
			    if(!empty($post['donation']) && $post['did'])
			    {
			    	$donations_data_array = array();
			        foreach($post['donation'] as $k => $a_values)
			        {
			            if(!empty($a_values['amount']) )
			            {
			            	if(Zend_Date::isDate($a_values['donation_date'] , 'dd.mm.YYYY'))
			                {
			                    $date = date('Y-m-d', strtotime($a_values['donation_date']));
			                    
			                }
			                else
			                {
			                    $date = date('Y-m-d');
			                }
			                $donations_data_array[$k] = array(
			                    'clientid' => $clientid,
			                    'member' => addslashes($post['did']),
			                    'donation_date' => addslashes($date),
			                    'amount' => addslashes($a_values['amount'])
			                );
			                if(!isset($a_values['custom'])){
			                	$donations_data_array[$k]['id'] = (int)$k;
			                }
			            }
			        }
			        foreach($donations_data_array as $d){
			        	if(isset($d['id'])){
			        		$update = Doctrine_Query::create()
			        		->update("MemberDonations")
			        		->set('donation_date', "'".$d['donation_date']."'")
			        		->set('amount', "'".$d['amount']."'")
			        		->where('id = ?' , (int)$d['id'] )
			        		->andWhere('clientid = ? ' , $d['clientid']);
// 			        		Pms_DoctrineUtil::get_raw_sql($update);
			        		$update->execute();
			        	}else{
			        		//new donation insert
			        		$fdoc = new MemberDonations();
			        		$fdoc->clientid = $d['clientid'];
			        		$fdoc->member = $d['member'];
			        		$fdoc->donation_date = $d['donation_date'];
			        		$fdoc->amount = $d['amount'];
			        		$fdoc->save();
			        	}
			        }

			       /*
			       $collection = new Doctrine_Collection('MemberDonations');
			       $collection->fromArray($donations_data_array);
			       $collection->save();
			       */
			    }

			}
			

			// ISPC-1739 p.7
			if($post['type'] == 'family'){
				$post['member_id'] = $post['did'];
				$post['title'] = $post['title_child'];
				$post['salutation'] = $post['salutation_child'];	
				$post['first_name'] = $post['first_name_child'];
				$post['last_name'] = $post['last_name_child'];
				$post['birthd'] = $post['birthd_child'];
				$post['gender'] = $post['gender_child'];
				if (trim($post['salutation_letter_child']) == ''){
					$post['salutation_letter_child'] = trim($post['salutation']);
					if(trim($post['title']) != ''){
						$post['salutation_letter_child'] .= " " . trim($post['title']);
					}
					if(trim($post['first_name']) != ''){
						//$post['salutation_letter_child'] .= " " . trim(mb_convert_case(mb_strtolower($post['first_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));						
					    //$post['salutation_letter_child'] .= " " . trim($post['first_name']);      //TODO-3847 Lore 10.02.2021    //TODO-3272 Lore 15.02.2021
					}
					if(trim($post['last_name']) != ''){
						//$post['salutation_letter_child'] .= " " . trim(mb_convert_case(mb_strtolower($post['last_name'], 'UTF-8'), MB_CASE_TITLE, "UTF-8"));						
					    $post['salutation_letter_child'] .= " " . trim($post['last_name']);      //TODO-3847 Lore 10.02.2021
					}
					if (trim($post['salutation_letter_child']) != ""){
						//$post['salutation_letter_child'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter_child']);
					    //TODO-3847 Lore 10.02.2021
					    if($post['gender'] == '2'){
					        $post['salutation_letter_child'] = $Tr->translate('dear_madam') . " ". trim($post['salutation_letter_child']);
					    } else {
					        $post['salutation_letter_child'] = $Tr->translate('dear_sir_madam') . " ". trim($post['salutation_letter_child']);
					    }
					}
				}
				$post['salutation_letter'] = trim($post['salutation_letter_child']);
				$post['clientid'] = $clientid;
				$this->InsertUpdate_FamilyMember_Data($post ,'update');
			}
		}
		
	   

		private function move_uploaded_icon($inserted_icon_id)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
 	        $clientid = $logininfo->clientid;
			
			if(!empty($_SESSION['filename']))
			{

				$filename_arr = explode(".", $_SESSION['filename']);

				$allowed_ext = array("jpg", "png", "gif", "jpeg");

				if(in_array($filename_arr[1], $allowed_ext))
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
					$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
					$icon_new_path = 'icons_system/' . $clientid . '/members/' . $inserted_icon_id . '.' . $filename_ext;

					copy($icon_upload_path, $icon_new_path);
					unlink($icon_upload_path);

					$update = Doctrine::getTable('Member')->find($inserted_icon_id);
					$update->img_path = $clientid . '/members/' . $inserted_icon_id . '.' . $filename_ext;
					$update->save();
				}
			}
		}


		public function reset_member_donations($member, $client = false , $donation_ids = array(), $merged_slave = 0)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $q = Doctrine_Query::create()
		    ->update('MemberDonations')
		    ->set('isdelete', '1')
		    ->set('change_date', '"'.date("Y-m-d H:i:s").'"')
		    ->set('change_user', '"'.$logininfo->userid.'"');
		    
		    if($merged_slave != 0){
		    	$q>set('merged_slave', $merged_slave);	
		    }
		    
		    if($client){
		    				$q->where(' clientid = "' . $client . '" AND member = "' . $member . '"');
		    } else{
		    				$q->where('member = "' . $member . '"');
		    }
		    if (sizeof($donation_ids)>0){
		    	$q->andWhereIn('id', $donation_ids);
		    }
		    $q->execute();
		}
		
		
		/*
		 * left enhr
		 * merged_parent = the id of the right member
		 * 
		 */
		public function merge_member($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$merged = false;
			
			$Tr = new Zend_View_Helper_Translate ();
			
			$this->error_message['merged'] = $Tr->translate('member_abort');
			if((int)$post['member_left']>0 && (int)$post['member_right']>0 && (int)$post['member_right']!=(int)$post['member_left'])
			{	
				$members = Member::getMembersDetails(array_unique(array((int)$post['member_left'] , (int)$post['member_right'])));
				if (count($members) == 2){
					
					foreach($post as $k=>$v){
						$new_member[$k] = $members[$v] [$k] ; 
					}	
					//mark this newly created member as one that is resulted as a merge of other 2
					$new_member['merged_parent'] = "1";
					

					//merge donation
					$donation_history = MemberDonations::get_donations_history($clientid, array((int)$post['member_left'], (int)$post['member_right'] ));
					$donations = array();
					foreach($donation_history as $r){
						$donations[] = array('donation_date' =>  $r['donation_date'],
											'amount' => $r['amount']); 
					}
					if(count($donations)>0){
						$new_member['donation'] = $donations;
					}
					 
					
					//merge famyly children, if any 
					if ( $new_member['type'] == 'family' ){
						$family = MemberFamily::getMemberFamilyDetails(array((int)$post['member_left'] , (int)$post['member_right']));
						$family_child = array();
						foreach($family as $one){
							$family_child[$one['member_id']] = $one;
							continue;
						}

						$new_member['title_child'] = $family_child[ $post['family_child']['title'] ] ['title'];
						$new_member['salutation_child'] = $family_child[ $post['family_child']['salutation'] ] ['salutation'];
						$new_member['first_name_child'] = $family_child[ $post['family_child']['first_name'] ] ['first_name'];
						$new_member['last_name_child'] = $family_child[ $post['family_child']['last_name'] ] ['last_name'];
						$new_member['birthd_child'] = $family_child[ $post['family_child']['birthd'] ] ['birthd'];
						$new_member['gender_child'] = $family_child[ $post['family_child']['gender'] ] ['gender'];
						$new_member['salutation_letter_child'] = $family_child[ $post['family_child']['salutation_letter'] ] ['salutation_letter'];
						
					}
					
					//save this new merged_member
					$new_id = $this->InsertData($new_member, false);
					
					//merge membership
					//$membership_history_array = Member2Memberships::get_memberships_history($clientid,(int)$post['membership_history']);
					
					$membership_history = Member2Memberships::get_memberships_history($clientid, array_unique(array( (int)$post['membership_history'], (int)$post['member_left'] , (int)$post['member_right']) ));
					
					foreach($membership_history as $arr){
						if( $arr['member'] == (int)$post['membership_history']){
							$first[] = $arr;
						}else{
							$last[] = $arr;
							
						}
					}
					$membership_history = array_merge($first, $last);
					
					
					
					//echo "<hr>[Member2Memberships::get_memberships_history] ".(int)$post['membership_history'];
					//print_r($membership_history);
					
					$membership = array();
					foreach($membership_history as $k){
						if($k['member'] == (int)$post['membership_history']){
							$membership[$k['id']] = array(
									            'clientid' => $clientid,
									            'member' => $new_id,
									            'membership' => $k['membership'],
									            'membership_price' => $k['membership_price'],
									            'start_date' => $k['start_date'],
									            'end_date' => $k['end_date'],
									            'isdelete' => $k['isdelete'],
									            'end_reasonid' => $k['end_reasonid']
											);
						
							$fdoc = new Member2Memberships();
							$fdoc->clientid = $clientid;
							$fdoc->member = $new_id;
							$fdoc->membership = $k['membership'];
							$fdoc->membership_price = $k['membership_price'];
							$fdoc->start_date = $k['start_date'];
							$fdoc->end_date = $k['end_date'];
							$fdoc->isdelete = $k['isdelete'];
							$fdoc->end_reasonid = $k['end_reasonid'];
							$fdoc->save();
							$new_membership_id = $fdoc->id;
						}
						elseif(empty($new_membership_id)){
							$new_membership_id = $k['id'];
						}
						
						$fl = Doctrine::getTable('MembersInvoices')->findByMemberAndClientAndIsdeleteAndMembership_data($k['member'] ,$clientid, 0, $k['id']);
						$file_arr = $fl->toArray();
						
						//echo "<hr>[MembersInvoices]";
						//print_r($file_arr);
						
						//echo "<hr>[MembersInvoiceItems::getInvoicesItems]";
						
						
						$inv_items = array();
						$inv_ids_arr = array_column($file_arr, 'id');
						//print_r($inv_ids_arr);
						if ( is_array($inv_ids_arr > 0 ) && count($inv_ids_arr) > 0){ 
							//echo "<hr>0<hr>";
							$inv_items = MembersInvoiceItems::getInvoicesItems(array_column($file_arr, 'id'));
						}
						//print_r($inv_items);
						
						foreach($file_arr  as $inv){
							
							//echo $inv['id']. "<br>";
							
							$finv = new MembersInvoices();

				            $finv->member = $new_id;
				            $finv->invoice_start = $inv['invoice_start'];
				            $finv->invoice_end = $inv['invoice_end'];
				            $finv->membership_start = $inv['membership_start'];
				            $finv->membership_end = $inv['membership_end'];
				            $finv->membership_data = $new_membership_id;
				            $finv->invoiced_month = $inv['invoiced_month'];
				            $finv->client = $inv['client'];
				            $finv->prefix = $inv['prefix'];
				            $finv->invoice_number = $inv['invoice_number'];
				            $finv->invoice_total = $inv['invoice_total'];
				            $finv->paid_date = $inv['paid_date'];
				            $finv->status = $inv['status'];
				            $finv->client_name = $inv['client_name']; 
				            $finv->recipient = $inv['recipient']; 
				            $finv->comment = $inv['comment']; 
				            $finv->isdelete = $inv['isdelete'];
				            $finv->isarchived = $inv['isarchived'];
				            $finv->record_id = $inv['record_id'];
				            $finv->storno = $inv['storno'];
				            $finv->completed_date = $inv['completed_date'];
				            $finv->save();
				            $new_ivoice_id = $finv->id;
				            
				            
				            foreach($inv_items[$inv['id']] as $one_item){
				            	//print_r($one_item);	
				            	$f_inv = new MembersInvoiceItems();
				            	$f_inv ->invoice = $new_ivoice_id;
				            	$f_inv ->client = $one_item['client'];
				            	$f_inv ->shortcut = $one_item['shortcut'];
				            	$f_inv ->description = $one_item['description'];
				            	$f_inv ->qty = $one_item['qty'];
				            	$f_inv ->price = $one_item['price'];
				            	$f_inv ->total = $one_item['total'];
				            	$f_inv ->custom = $one_item['custom'];
				            	$f_inv ->isdelete = $one_item['isdelete'];
				            	$f_inv ->save();	
				            	$new_item_id = $f_inv->id;
				            }
				            
				            //echo "<hr>[MembersInvoicePayments::getInvoicePayments]";
				            $inv_payments = MembersInvoicePayments::getInvoicePayments($inv['id']);
				            //print_r($inv_payments);
							foreach($inv_payments as $one_payment){
								$f_pay = new MembersInvoicePayments();
								$f_pay->invoice = $new_ivoice_id;
								$f_pay->amount = $one_payment['amount'];
								$f_pay->comment = $one_payment['comment'];
								$f_pay->paid_date = $one_payment['paid_date'];
								$f_pay->isdelete = $one_payment['isdelete'];
								//$f_pay->create_date = $one_payment['create_date'];
								//$f_pay->create_user = $one_payment['create_user'];
								$f_pay ->save();
								
							}
							

						}
						
						
					}	
					
				
						
					//die();
					
					
					//set member_left and member_right is_deleted =1 and member_slave of the $new_id
					$update = Doctrine_Query::create()->update("Member");
					$update->set('merged_slave', "'" . $new_id."'");
					$update->set('isdelete', "'1'");
					$update->whereIn('id', array((int)$post['member_left'] , (int)$post['member_right']));
					$update->execute();
					
					//set error message= successsss
					$this->error_message['merged'] =  $Tr->translate('member_finished');
						
						
					
					
				}
			}
			return true;
		}
	
		//untagle this mess
		public function merge_member_undo($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$Tr = new Zend_View_Helper_Translate ();
			
			$usr = Doctrine_Query::create ();
			$usr->select ( 'GROUP_CONCAT(id)' );
			$usr->from ( 'Member' );
			$usr->where ( 'merged_slave = ?' ,  (int)$post['unmerge_id']);
			$results = $usr->execute (array (), Doctrine_Core::HYDRATE_SINGLE_SCALAR );
			
			if( $results == '' ){
				$this->error_message['unmerge'] = $Tr->translate('something went wrong, cannot unmerge, inform admin about this problem');
				return false;
			}
			
			$update = Doctrine_Query::create()->update("Member");
			$update->set('merged_slave', "'0'");
			$update->set('isdelete', "'0'");
			$update->where('id IN('.$results.')');
			$update->execute();
			
			$update = Doctrine_Query::create()->update("Member");
			$update->set('merged_parent', "'0'");
			$update->set('isdelete', "'1'");
			$update->where('id = ?',(int)$post['unmerge_id']);
			$update->execute();
			
			$this->error_message['unmerge'] = $Tr->translate('unmerge_finished_ok');
			return true;
		}
		
		public function InsertUpdate_FamilyMember_Data($post , $action='update'){
			
			if ($action == 'insert')
			{//new family_child insert
				$fdoc = new MemberFamily();
			}
			else 
			{//check first if allreay has one to update
				if($fdoc = Doctrine::getTable('MemberFamily')->findByMember_idAndClientid($post['member_id'], $post['clientid'])){
					$fdoc = $fdoc{0};
				}
				else{
					$fdoc = new MemberFamily();
				}
			}

			if (isset($post['clientid'])) $fdoc->clientid = $post['clientid'];
			if (isset($post['type'])) $fdoc->type = $post['type'];
			if (isset($post['member_company'])) $fdoc->member_company= $post['member_company'];
		
			if (isset($post['title'])) $fdoc->title = $post['title'];
			if (isset($post['salutation_letter'])) $fdoc->salutation_letter = $post['salutation_letter'];
				
			// ISPC - 1518
			if (isset($post['auto_member_number'])) $fdoc->auto_member_number= $post['auto_member_number'];
			if (isset($post['member_number'])) $fdoc->member_number = $post['member_number'];
			if (isset($post['salutation'])) $fdoc->salutation = $post['salutation'];
		
			if (isset($post['first_name'])) $fdoc->first_name = $post['first_name'];
			if (isset($post['last_name'])) $fdoc->last_name = $post['last_name'];
			if($post['birthd'])
			{
				$fdoc->birthd = date('Y-m-d',strtotime($post['birthd']));
			}else{
				$fdoc->birthd ="0000-00-00";
			}
		
			if (isset($post['gender'])) $fdoc->gender = $post['gender'];
			if (isset($post['street'])) $fdoc->street1 = $post['street'];
			if (isset($post['zip'])) $fdoc->zip = $post['zip'];
			if (isset($post['city'])) $fdoc->city = $post['city'];
			if (isset($post['fax'])) $fdoc->fax = $post['fax'];
			if (isset($post['phone'])) $fdoc->phone = $post['phone'];
			if (isset($post['mobile'])) $fdoc->mobile = $post['mobile'];
			if (isset($post['email'])) $fdoc->email = $post['email'];
			if (isset($post['bank_name'])) $fdoc->bank_name = $post['bank_name'];
			if (isset($post['iban'])) $fdoc->iban = $post['iban'];
			if (isset($post['bic'])) $fdoc->bic = $post['bic'];
			if (isset($post['account_holder'])) $fdoc->account_holder = $post['account_holder'];
			if (isset($post['mandate_reference'])) $fdoc->mandate_reference = $post['mandate_reference'];
			if (isset($post['mandate_reference_date'])) $fdoc->mandate_reference_date = $post['mandate_reference_date'];
			if (isset($post['remarks'])) $fdoc->remarks = $post['remarks'];
			if (isset($post['inactive'])) $fdoc->inactive = $post['inactive'];
			
			if($post['inactive'] == "1" && strlen($post['inactive_from']) > 0){
				$fdoc->inactive_from = date('Y-m-d',strtotime($post['inactive_from']));
			} else {
				$fdoc->inactive_from = "0000-00-00";
			}
			if (isset($post['status'])) $fdoc->status = $post['status'];
			if (isset($post['profession'])) $fdoc->profession = $post['profession'];
			if (isset($post['vw_id'])) $fdoc->vw_id = $post['vw_id'];
			if (isset($post['street2'])) $fdoc->street2 = $post['street2'];
			if (isset($post['country'])) $fdoc->country = $post['country'];
			if (isset($post['website'])) $fdoc->website = $post['website'];
			if (isset($post['memos'])) $fdoc->memos = $post['memos'];
			if (isset($post['comments'])) $fdoc->comments = $post['comments'];
			if (isset($post['member_id'])) $fdoc->member_id = $post['member_id'];
			if (isset($post['payment_method_id'])) $fdoc->payment_method_id = $post['payment_method_id'];
			
			
			if(!empty($post['merged_parent'])) $fdoc->merged_parent = $post['merged_parent'];
			if(!empty($post['merged_slave'])) $fdoc->merged_slave = $post['merged_slave'];
			
			
			$fdoc->save();
			return true;	
		}
		
	}

?>