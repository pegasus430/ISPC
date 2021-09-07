<?php

	require_once("Pms/Form.php");

	class Application_Form_Copy extends Pms_Form {

		
		
		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if($post['source_client'] == 0)
			{
				$this->error_message['sourceclient'] = $Tr->translate('selectfromclient');
				$error = 1;
			}
			if($post['target_client'] == 0)
			{
				$this->error_message['targetclient'] = $Tr->translate('selecttoclient');
				$error = 1;
			}
			if(!is_array($post['copytable']))
			{
				$this->error_message['copytable'] = $Tr->translate('selecttabletocopy');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		
		
		
		public function copy_data($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
	    	// Maria:: Migration ISPC to CISPC 08.08.2020
            // ISPC-2302 pct.1 @Lore 18.10.2019
            $special_tables = array(
                "Careservices",
                "ClientSymptomsGroups",
                "Member",
                "Specialists",
                "SgbvAction",
                "Locations",
                "Voluntaryworkers",
                "HealthInsurance",
                "Bedarfsmedication",
                "ClientOrderMaterials_Arzneimittel",
                "ClientOrderMaterials_Hilfsmittel",
                "ClientOrderMaterials_Pflegehilfsmittel",
                "ClientOrderMaterials_Verbandsstoffe"
            );
			     
			  			
		    foreach($post['copytable'] as  $table_name =>$value)
		    {
                if($value == "1")
                {
                    
                    if( ! in_array($table_name, $special_tables)){
                        //ISPC-2302 pct.1  @Lore 18.10.2019
                        $this->copy_tabels($table_name, $post['source_client'],$post['target_client'],$userid);
                        
                    } else {
                        
                        switch ($table_name)
                        {
                            case "ClientSymptomsGroups":
                                $this->copy_clientsymptomsgroups($post['source_client'],$post['target_client']);
                                break;                            
                            
                            case "Member":
                                $this->copy_Members($post['source_client'],$post['target_client']);
                                break;
                                
                            case "Specialists":
                                $this->copy_specialists($post['source_client'],$post['target_client']);
                                break;
                                
                                
                            case "SgbvAction":
                                $this->copy_sgbv_actions($post['source_client'],$post['target_client']);
                                break;
                             
                                
                            case "Locations":
                                $this->copy_locations($post['source_client'],$post['target_client']);
                                break;
                             
                                
                            case "Voluntaryworkers":
                                $this->copy_voluntary_workers($post['source_client'],$post['target_client']);
                                break;

                                
                            case "HealthInsurance":
                                $this->copy_healthinsurance($post['source_client'],$post['target_client']);
                                break;
                                
                                
                            case "Bedarfsmedication":
                                $this->copy_bedarfsmedikation($post['source_client'],$post['target_client']);
                                break;
                                
                                
                            case "Careservices":
                                $this->copy_Careservices($post['source_client'],$post['target_client'],$userid);
                                break;

                            case "ClientOrderMaterials_Arzneimittel":
                                $this->copy_order_arzneimittel($post['source_client'],$post['target_client'],$userid);
                                break;
                                
                            case "ClientOrderMaterials_Hilfsmittel":
                                $this->copy_order_hilfsmittel($post['source_client'],$post['target_client'],$userid);
                                break;
                                
                            case "ClientOrderMaterials_Pflegehilfsmittel":
                                $this->copy_order_pflegehilfsmittel($post['source_client'],$post['target_client'],$userid);
                                break;
                                
                            case "ClientOrderMaterials_Verbandsstoffe":
                                $this->copy_order_verbandsstoffe($post['source_client'],$post['target_client'],$userid);
                                break;
                                
                                
                            default:
                                
                                break;
                                
                        }

                        
                    }
       
                }    
		    }
		    

		    
		}
		
		public function update_data($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid; 
// 			dd($post);
		    foreach($post['copytable'] as  $db =>$value)
		    {
                if($value == "1")
                {
                    switch ($db)
                    {
                        //Apotheken
                        case "Pharmacy":
                            $this->update_Pharmacy($post['source_client'],$post['target_client'],$userid);
                            break;

                        //Aufenthaltsorte                            
                        case "Locations":
                            $this->update_Locations($post['source_client'],$post['target_client']);
                            break;
                            
                        //Hausarzte                            
                        case "FamilyDoctor":
                            $this->update_FamilyDoctor($post['source_client'],$post['target_client']);
                            break;
                            
                        //Pflegedienstes    
                        case "Pflegedienstes":
                            $this->update_Pflegedienstes($post['source_client'],$post['target_client'],$userid);
                            break;
                            
                        //Homecare                            
                        case "Homecare":
                            $this->update_Homecare($post['source_client'],$post['target_client'],$userid);
                            break;
                            
                            
                        //Sanitatshauser    
                        case "Supplies":
                            $this->update_Supplies($post['source_client'],$post['target_client'],$userid);
                            break;                            
                            
                            
                        //sonst. Versorger                            
                        case "Suppliers":
                            $this->update_Suppliers($post['source_client'],$post['target_client'],$userid);
                            break;                            
                            

                        // Hospizvereine   
                        case "Hospiceassociation":
                            $this->update_Hospiceassociation($post['source_client'],$post['target_client'],$userid);
                            break;                            
                            
                        //Eigene Krankenkassen
                        case "healthinsurance":
                            $this->update_HealthInsurance($post['source_client'],$post['target_client']);
                            break;

                        // Facharzt
                        case "Specialists":
                            $this->update_Specialists($post['source_client'],$post['target_client']);
                            break;                            
                        
                        default:
                            
                        break;                        
                        
                    }
                }    
		    }
		}
		
		
		
		public function copy_specialists($source,$target)
		{
		    // Get sourse specialists types  data
		    $types = new SpecialistsTypes();
		    $source_specialists_types = $types->get_specialists_types($source);
		    
		    if(!empty($source_specialists_types))
		    {
		        // DELETE -  existing ones?
		        		        
		        // insert specialists types  for target client
		        foreach($source_specialists_types as $spt_key=>$source_spt_value)
		        {
		            $insert_sp_types = new SpecialistsTypes();
		            $insert_sp_types->clientid = $target;
		            $insert_sp_types->name = $source_spt_value['name'];
		            $insert_sp_types->save();
		            $types_connection[$source_spt_value['id']] = $insert_sp_types->id; 
		        }
		    }
		    
		    // get source  - specialists data
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Specialists')
		    ->where("isdelete = 0")
		    ->andWhere('indrop=0')
		    ->andWhere('valid_till="0000-00-00"')
		    ->andWhere('(first_name!="" or last_name!="" or practice!="")')
		    ->andWhere('clientid= "'.$source.'" ');
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        
		        // DELETE -  existing ones?
		        foreach($source_array as $sp_key => $source_sp_value)
		        {
		            $insert_sp = new Specialists();
		            $insert_sp->clientid = $target;
		            $insert_sp->practice = $source_sp_value['practice'];
		            $insert_sp->title = $source_sp_value['title'];
		            $insert_sp->salutation = $source_sp_value['salutation'];
		            
		            if($types_connection[$source_sp_value['medical_speciality']])
		            {
    		            $insert_sp->medical_speciality = $types_connection[$source_sp_value['medical_speciality']];
		            } 
		            else
		            {
    		            $insert_sp->medical_speciality = "0";
		            }
		            
		            $insert_sp->first_name = $source_sp_value['first_name'];
		            $insert_sp->last_name = $source_sp_value['last_name'];
		            $insert_sp->street1 = $source_sp_value['street1'];
		            $insert_sp->zip = $source_sp_value['zip'];
		            $insert_sp->indrop = $source_sp_value['indrop'];
		            $insert_sp->city = $source_sp_value['city'];
		            $insert_sp->phone_practice = $source_sp_value['phone_practice'];
		            $insert_sp->phone_cell = $source_sp_value['phone_cell'];
		            $insert_sp->phone_private = $source_sp_value['phone_private'];
		            $insert_sp->fax = $source_sp_value['fax'];
		            $insert_sp->email = $source_sp_value['email'];
		            $insert_sp->doctornumber = $source_sp_value['doctornumber'];
		            $insert_sp->comments = $source_sp_value['comments'];
		            $insert_sp->indrop = "0";
		            $insert_sp->save();
		        }
		    }
		}
		
		
		
		
		public function update_specialists($source,$target)
		{
		    
		    // get source  - specialists data
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Specialists')
		    ->where("isdelete = 0")
		    ->andWhere('indrop=0')
		    ->andWhere('valid_till="0000-00-00"')
		    ->andWhere('(first_name!="" or last_name!="")')
		    ->andWhere('clientid= ?', $source );
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
    		    // get source  - specialists data
    		    $target_q = Doctrine_Query::create()
    		    ->select('*, concat(practice,first_name,last_name) as ident')
    		    ->from('Specialists')
    		    ->where("isdelete = 0")
    		    ->andWhere('indrop=0')
    		    ->andWhere('valid_till="0000-00-00"')
    		    ->andWhere('(first_name!="" or last_name!="")')
    		    ->andWhere('clientid= ?', $target );
    		    $target_array = $target_q->fetchArray();
    		    
    		    $existing_items = array();
    		    if(!empty($target_array))
    		    {
    		        foreach($target_array as $k=>$td){
    		            $existing_items[] = $td['ident'];
    		        }
    		    }
    		    
		        // DELETE -  existing ones?
    		    $ident ="";
		        foreach($source_array as $sp_key => $source_sp_value)
		        {
		            $ident = $source_sp_value['practice'].$source_sp_value['first_name'].$source_sp_value['last_name'];
		            
		            if( ! in_array($ident,$existing_items)){
		            
    		            $insert_sp = new Specialists();
    		            $insert_sp->clientid = $target;
    		            $insert_sp->practice = $source_sp_value['practice'];
    		            $insert_sp->title = $source_sp_value['title'];
    		            $insert_sp->salutation = $source_sp_value['salutation'];
    		            
//     		            if($types_connection[$source_sp_value['medical_speciality']])
//     		            {
//         		            $insert_sp->medical_speciality = $types_connection[$source_sp_value['medical_speciality']];
//     		            } 
//     		            else
//     		            {
        		            $insert_sp->medical_speciality = "0";
//     		            }
    		            
    		            $insert_sp->first_name = $source_sp_value['first_name'];
    		            $insert_sp->last_name = $source_sp_value['last_name'];
    		            $insert_sp->street1 = $source_sp_value['street1'];
    		            $insert_sp->zip = $source_sp_value['zip'];
    		            $insert_sp->indrop = $source_sp_value['indrop'];
    		            $insert_sp->city = $source_sp_value['city'];
    		            $insert_sp->phone_practice = $source_sp_value['phone_practice'];
    		            $insert_sp->phone_cell = $source_sp_value['phone_cell'];
    		            $insert_sp->phone_private = $source_sp_value['phone_private'];
    		            $insert_sp->fax = $source_sp_value['fax'];
    		            $insert_sp->email = $source_sp_value['email'];
    		            $insert_sp->doctornumber = $source_sp_value['doctornumber'];
    		            $insert_sp->comments = $source_sp_value['comments'];
    		            $insert_sp->indrop = "0";
    		            $insert_sp->save();
		          }
		        }
		    }
		}
		
		
		
		
		
		public function copy_sgbv_actions($source,$target)
		{
		    // Get sourse specialists types  data
		    $groups = new SocialCodeGroups();
		    $source_groups = $groups->getCientSocialCodeGroups($source);
		    
		    // DELETE -  existing ones?
		    if(!empty($source_groups))
		    {
		        // insert groups from source to target
		        foreach($source_groups as $spt_key=>$source_gr_value)
		        {
		            
		            $insert_groups = new SocialCodeGroups();
		            $insert_groups->clientid = $target;
		            $insert_groups->groupname = $source_gr_value['groupname'];
		            $insert_groups->groupshortcut = $source_gr_value['groupshortcut'];
		            $insert_groups->group_order = $source_gr_value['group_order'];
		            $insert_groups->save();
		            
		            $groups_connection[$source_gr_value['id']] = $insert_groups->id;
		        }
		    }
		    
		    // get source  - Actions
		    $source_actions = SocialCodeActions::getCientSocialCodeActions($source);
		    
		    if(!empty($source_actions))
		    {
		        // DELETE -  existing ones?
		        foreach($source_actions as $act_key => $source_actions_value)
		        {
		            $insert_actions = new SocialCodeActions();
		            $insert_actions->clientid = $target;
		            $insert_actions->internal_nr = $source_actions_value['internal_nr'];
		            $insert_actions->action_name = $source_actions_value['action_name'];
		            $insert_actions->action_invoice_name = $source_actions_value['action_invoice_name'];
		            $insert_actions->description = $source_actions_value['description'];
		            $insert_actions->pos_nr = $source_actions_value['pos_nr'];
		            $insert_actions->max_per_day = $source_actions_value['max_per_day'];
		            $insert_actions->default_duration = $source_actions_value['default_duration'];
		            $insert_actions->price = $source_actions_value['price'];
		            if($groups_connection[$source_actions_value['groupid']])
		            {
    		            $insert_actions->groupid = $groups_connection[$source_actions_value['groupid']];
		            } 
		            else
		            {
    		            $insert_actions->groupid = "0";
		            }

		            $insert_actions->custom = $source_actions_value['custom'];
		            $insert_actions->parent = $source_actions_value['parent'];
		            $insert_actions->issapv = $source_actions_value['issapv'];
		            $insert_actions->form_condition = $source_actions_value['form_condition'];
		            $insert_actions->extra = $source_actions_value['extra'];
		            $insert_actions->available = $source_actions_value['available'];
		            $insert_actions->parent_list = $source_actions_value['parent_list'];
		            $insert_actions->night_bonus = $source_actions_value['night_bonus'];
		            $insert_actions->nh_sunday_bonus = $source_actions_value['nh_sunday_bonus'];
		            $insert_actions->multi_resistance_bonus = $source_actions_value['multi_resistance_bonus'];
		            $insert_actions->save();
		        }
		    }
		}
		
		
		public function  copy_receipt_medication($source,$target)
		{
		    
		    // get source receipt medication list
		    $source_medr_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('MedicationReceipt')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("extra = 0")
		    ->andWhere('name!=""');
		    $source_medr_array= $source_medr_q->fetchArray();
		    
		    if(!empty($source_medr_array))
		    {
		        foreach($source_medr_array as $key => $med_receipt_values)
		        {     
		            //Insert to target client 
		            $insert_receipt_medication = new MedicationReceipt();
		            $insert_receipt_medication->clientid = $target;
		            $insert_receipt_medication->name = $med_receipt_values['name'];
		            $insert_receipt_medication->pzn = $med_receipt_values['pzn'];
		            $insert_receipt_medication->description = $med_receipt_values['description'];
		            $insert_receipt_medication->package_size = $med_receipt_values['package_size'];
		            $insert_receipt_medication->amount_unit = $med_receipt_values['amount_unit'];
		            $insert_receipt_medication->price = $med_receipt_values['price'];
		            $insert_receipt_medication->extra = $med_receipt_values['extra'];
		            $insert_receipt_medication->manufacturer = $med_receipt_values['manufacturer'];
		            $insert_receipt_medication->package_amount = $med_receipt_values['package_amount'];
		            $insert_receipt_medication->isdelete = $med_receipt_values['isdelete'];
		            $insert_receipt_medication->save();
		        }
		    }
		}
		
		public function  copy_medication($source,$target)
		{
		    // get source medication list
		    $source_medr_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Medication')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("extra = 0")
		    ->andWhere('name!=""');
		    $source_medr_array= $source_medr_q->fetchArray();
		    
		    if(!empty($source_medr_array))
		    {
		        foreach($source_medr_array as $key => $med_values)
		        {     
		            //Insert to target client 
		            $med = new Medication();
	                $med->clientid = $target;
		            $med->name = $med_values['name'];
		            $med->pzn = $med_values['pzn'];
		            $med->description = $med_values['description'];
		            $med->package_size = $med_values['package_size'];
		            $med->amount_unit = $med_values['amount_unit'];
		            $med->price = $med_values['price'];
		            $med->extra = $med_values['extra'];
		            $med->manufacturer = $med_values['manufacturer'];
		            $med->package_amount = $med_values['package_amount'];
		            $med->comment = $med_values['comment'];
		            $med->save();
		            
		        }
		    }
		}

		
		public function  copy_pharmacy($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Pharmacy')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {     
		            $source_array[$key]["id"] = NULL;
		            $source_array[$key]["clientid"] = $target;
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		        }
		        
		        if(count($source_array) > 0)
		        {
		        	//insert many records with one query!!
		        	$collection = new Doctrine_Collection('Pharmacy');
		        	$collection->fromArray($source_array);
		        	$collection->save();
		        }
		    }
		}
		
		public function  update_Pharmacy($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Pharmacy')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		    
		    
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(pharmacy,first_name,last_name) as ident')
		    ->from('Pharmacy')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		    
		    
		    $existing_items = array();
		    
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		        
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['pharmacy'].$values['first_name'].$values['last_name'];

		            if(!in_array($ident,$existing_items)){
		                
		                $update_array[$key] = $values;
    		            $update_array[$key]["id"] = NULL;
    		            $update_array[$key]["clientid"] = $target;
    		            $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
    		            $update_array[$key]["create_user"] = $userid;
    		            $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
    		            $update_array[$key]["change_user"] = "0";
		            }
		        }

		        if(count($update_array) > 0)
		        {
		        	//insert many records with one query!!
		        	$collection = new Doctrine_Collection('Pharmacy');
		        	$collection->fromArray($update_array);
		        	$collection->save();
		        }
		    }
		}
		public function  copy_family_doctor($source,$target)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('FamilyDoctor')
		    ->where("isdelete = 0")
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {     
		            //Insert to target client 
		            $insert_values = new FamilyDoctor();
		            $insert_values->clientid = $target;
		            $insert_values->practice = $values['practice'];
		            $insert_values->first_name = $values['first_name'];
		            $insert_values->last_name = $values['last_name'];
		            $insert_values->title = $values['title'];
		            $insert_values->salutation = $values['salutation'];
		            $insert_values->title_letter = $values['title_letter'];
		            $insert_values->salutation_letter = $values['salutation_letter'];
		            $insert_values->street1 = $values['street1'];
		            $insert_values->street2 = $values['street2'];
		            $insert_values->zip = $values['zip'];
		            $insert_values->city = $values['city'];
		            $insert_values->doctornumber = $values['doctornumber'];
		            $insert_values->doctor_bsnr = $values['doctor_bsnr'];
		            $insert_values->fax = $values['fax'];
		            $insert_values->phone_private = $values['phone_private'];
		            $insert_values->phone_practice = $values['phone_practice'];
		            $insert_values->phone_cell = $values['phone_cell'];
		            $insert_values->email=$values['email'];
		            $insert_values->kv_no = $values['kv_no'];
		            $insert_values->indrop = $values['indrop'];
		            $insert_values->medical_speciality = $values['medical_speciality'];
		            $insert_values->comments = $values['comments'];
		            $insert_values->valid_from = $values['valid_from'];
		            $insert_values->valid_till = $values['valid_till'];
		            $insert_values->save();
		        }
		    }
		}
		
		
		public function  update_FamilyDoctor($source,$target)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('FamilyDoctor')
		    ->where("isdelete = 0")
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		    
		    // get target list
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(practice,first_name,last_name) as ident')
		    ->from('FamilyDoctor')
		    ->where("isdelete = 0")
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array= $target_q->fetchArray();
		    
		  
		    
		    $existing_items = array();
		    
		    if(!empty($source_array))
		    {
		        
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		        
		        
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['practice'].$values['first_name'].$values['last_name'];
		        
		            if(!in_array($ident,$existing_items)){
		        
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		        
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('FamilyDoctor');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}
		


		public function copy_locations($source,$target)
		{
		    // get all     locations of client
		    $source_q = Doctrine_Query::create()
		    ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
		    ->from('Locations')
		    ->where("isdelete = 0")
		    ->andWhere('client_id= "'.$source.'" ');
		    $source_array= $source_q->fetchArray();
		    
		    foreach($source_array as $k=>$l_data){
		        $locations_ids[] = $l_data['id'];
		    }
		     
	        if($locations_ids){
	            $locations_ids[] = "9999999999";
	        }
		    // get all stations
		    $loc2stations = LocationsStations::getAllLocationsStationsByLocations($source,$locations_ids);
		    
		    if(!empty($source_array)){
		        
		        foreach($source_array as $k=>$values){
		            
		            //Insert to target client
		            $insert_values = new Locations();
		            $insert_values->client_id = $target;
		            $insert_values->location = Pms_CommonData::aesEncrypt($values['location']);
		            $insert_values->location_type = $values['location_type'];
		            $insert_values->location_sub_type = $values['location_sub_type'];
		            $insert_values->street = $values['street'];
		            $insert_values->zip = $values['zip'];
		            $insert_values->city = $values['city'];
		            $insert_values->phone1 = $values['phone1'];
		            $insert_values->phone2 = $values['phone2'];
		            $insert_values->fax = $values['fax'];
		            $insert_values->email = $values['email'];
		            $insert_values->comment = $values['comment'];
		            $insert_values->isdelete = $values['isdelete'];
		            $insert_values->save();
		            
		            $new_location_id = $insert_values->id; 
		            if(!empty($loc2stations[$values['id']])){
		                
		                foreach($loc2stations[$values['id']] as $sk=>$sv){
		                    
		                    $insert_s_values = new LocationsStations();
		                    $insert_s_values->client_id = $target;
		                    $insert_s_values->station =  Pms_CommonData::aesEncrypt($sv['station']);
		                    $insert_s_values->location_id = $new_location_id;
		                    $insert_s_values->phone1 = $sv['phone1'];
		                    $insert_s_values->phone2 = $sv['phone2'];
		                    $insert_s_values->isdelete = $sv['isdelete'];
		                    $insert_s_values->save();		                    
		                }
		            }
		        }
		    }
		}
		
		
		public function update_Locations($source,$target)
		{
		    // get all     locations of client
		    $source_q = Doctrine_Query::create()
		    ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
		    ->from('Locations')
		    ->where("isdelete = 0")
		    ->andWhere('client_id= ?', $source);
		    $source_array= $source_q->fetchArray();
		    
		    
		    // get target all     locations of client
		    $target_q = Doctrine_Query::create()
		    ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
		    ->from('Locations')
		    ->where("isdelete = 0")
		    ->andWhere('client_id= ?', $target);
		    $target_array= $target_q->fetchArray();
		    
		    $existing_locations = array();
		    
		    foreach($target_array as $k=>$tdata){
		        $existing_locations[] = $tdata['location'];
		    }
		    
		    foreach($source_array as $k=>$l_data){
		        $locations_ids[] = $l_data['id'];
		    }
		     
	        if($locations_ids){
	            $locations_ids[] = "9999999999";
	        }
		    // get all stations
		    $loc2stations = LocationsStations::getAllLocationsStationsByLocations($source,$locations_ids);
		    
		    if(!empty($source_array)){
		        
		        foreach($source_array as $k=>$values){
		            
		           if( ! in_array($values['location'],$existing_locations)) {
		               
    		            //Insert to target client
    		            $insert_values = new Locations();
    		            $insert_values->client_id = $target;
    		            $insert_values->location = Pms_CommonData::aesEncrypt($values['location']);
    		            $insert_values->location_type = $values['location_type'];
    		            $insert_values->location_sub_type = $values['location_sub_type'];
    		            $insert_values->street = $values['street'];
    		            $insert_values->zip = $values['zip'];
    		            $insert_values->city = $values['city'];
    		            $insert_values->phone1 = $values['phone1'];
    		            $insert_values->phone2 = $values['phone2'];
    		            $insert_values->fax = $values['fax'];
    		            $insert_values->email = $values['email'];
    		            $insert_values->comment = $values['comment'];
    		            $insert_values->isdelete = $values['isdelete'];
    		            $insert_values->save();
    		            
    		            $new_location_id = $insert_values->id; 
    		            if(!empty($loc2stations[$values['id']])){
    		                
    		                foreach($loc2stations[$values['id']] as $sk=>$sv){
    		                    
    		                    $insert_s_values = new LocationsStations();
    		                    $insert_s_values->client_id = $target;
    		                    $insert_s_values->station =  Pms_CommonData::aesEncrypt($sv['station']);
    		                    $insert_s_values->location_id = $new_location_id;
    		                    $insert_s_values->phone1 = $sv['phone1'];
    		                    $insert_s_values->phone2 = $sv['phone2'];
    		                    $insert_s_values->isdelete = $sv['isdelete'];
    		                    $insert_s_values->save();		                    
    		                }
    		            }
    		            
    		            
    		        }
		        }
		    }
		}
		
		public function copy_voluntary_workers($source,$target)
		{
		    $hq = Doctrine_Query::create()
		    ->select('*')
		    ->from('Hospiceassociation')
		    ->where("clientid='" . $source . "'")
		    ->andWhere("indrop = 0 ")
		    ->andWhere("isdelete = 0 ");
		    $hospice_array = $hq->fetchArray();
		    
		    
		    if(!empty($hospice_array))
		    {
		        // insert specialists types  for target client
		        foreach($hospice_array as $h_key=>$h_value)
		        {
		            $insert_sp_types = new Hospiceassociation();
		            $insert_sp_types->clientid = $target;
		            $insert_sp_types->hospice_association = $h_value['hospice_association'];
		            $insert_sp_types->first_name = $h_value['first_name'];
		            $insert_sp_types->last_name = $h_value['last_name'];
		            $insert_sp_types->title = $h_value['title'];
		            $insert_sp_types->salutation = $h_value['salutation'];
		            $insert_sp_types->title_letter = $h_value['title_letter'];
		            $insert_sp_types->salutation_letter = $h_value['salutation_letter'];
		            $insert_sp_types->street1 = $h_value['street1'];
		            $insert_sp_types->street2 = $h_value['street2'];
		            $insert_sp_types->zip = $h_value['zip'];
		            $insert_sp_types->city = $h_value['city'];
		            $insert_sp_types->phone_practice = $h_value['phone_practice'];
		            $insert_sp_types->phone_emergency = $h_value['phone_emergency'];
		            $insert_sp_types->fax = $h_value['fax'];
		            $insert_sp_types->phone_private = $h_value['phone_private'];
		            $insert_sp_types->email = $h_value['email'];
		            $insert_sp_types->comments = $h_value['comments'];
		            $insert_sp_types->indrop = $h_value['indrop'];
		            $insert_sp_types->isdelete = $h_value['isdelete'];
		            $insert_sp_types->save();
		            
		            $h_connection[$h_value['id']] = $insert_sp_types->id;
		            
		        }
		    }
		    
		    $sss_q = Doctrine_Query::create()
		    ->select('* ')
		    ->from('VoluntaryWorkersSecondaryStatuses')
		    ->where('isdelete = "0"')
		    ->andWhere('clientid = ?', $source);
		    $sss_arr = $sss_q->fetchArray();
		    
		    if(!empty($sss_arr)){
		        foreach($sss_arr as $k=>$ss){
		            
		            
		            $insert_sps_types = new VoluntaryWorkersSecondaryStatuses();
		            $insert_sps_types->clientid = $target;
		            $insert_sps_types->description = $ss['description'];
		            $insert_sps_types->isdelete = $ss['isdelete'];
		            $insert_sps_types->save();
		            
		            $ss_connection[$ss['id']] = $insert_sps_types->id;
		        }
		    }
		    
		    
		    
		    
		    // get source  - Voluntaryworkers data
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Voluntaryworkers')
		    ->where("isdelete = 0")
		    ->andWhere('indrop=0')
		    ->andWhere('clientid= "'.$source.'" ');
		    $source_array = $source_q->fetchArray();
		    
		    foreach($source_array as $vk=>$vd ){
		        $vw_ids[] = $vd['id'];
		    }
		    if(empty($vw_ids)){
		        $vw_ids[]="99999999";
		    }
		    
		    $source_data = array();
		    
		    //voluntaryworkers_statuses
		    $source_data['status'] = VoluntaryworkersStatuses::get_voluntaryworker_statuses($vw_ids,$source);
		    
            //voluntaryworkers_color_statuses
		    $source_data['color_status'] = VwColorStatuses ::get_color_statuses($vw_ids,$source);
            

            // get team events
		    $event_types = TeamEventTypes::get_team_event_types ($source,true );
		    foreach($event_types as $ket =>$vet){
		        
		        $ins_tev = new TeamEventTypes();
		        $ins_tev->client= $target;
		        $ins_tev->name = $vet['name'];
		        $ins_tev->voluntary = $vet['voluntary'];
		        $ins_tev->isdelete = $vet['isdelete'];
		        $ins_tev->save();
		        
		        $event_types_select[$vet['id']] = $ins_tev->id;
		    }

            // get client GRUND 
		    $htypes = HospizVisitsTypes::get_client_hospiz_visits_types($source);
		    foreach($htypes as $k_hvt =>$hvt){
		        
		        $ins_hvt = new HospizVisitsTypes();
		        $ins_hvt->clientid= $target;
		        $ins_hvt->grund= $hvt['grund'];
		        $ins_hvt->old_id= $hvt['old_id'];
		        $ins_hvt->isdelete= $hvt['isdelete'];
		        $ins_hvt->billable= $hvt['billable'];
		        $ins_hvt->save();

		        $hvt_select[$hvt['id']] = $ins_hvt->id;
		    }
	    	

		    //voluntaryworkers_activities
		    $vw_a_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('VoluntaryworkersActivities')
		    ->whereIn("vw_id", $vw_ids)
		    ->andWhere("isdelete = 0")
		    ->orderBy('date DESC');
		    $vw_a_array= $vw_a_q->fetchArray();

		    foreach($vw_a_array as $k=>$wa){
		        $source_data['activity'][$wa['vw_id']][] = $wa;
		    }

		    
            //voluntaryworkers_work_data
		    $w_data_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('VwWorkdata');
		    $w_data_q->andWhereIN("vw_id",$vw_ids);
		    $w_data_q->orderBy('work_date ASC');
		    $w_data_array = $w_data_q->fetchArray();
		    
		    foreach($w_data_array as $k=>$wd){
		        $source_data['work_bulk'][$wd['vw_id']][] = $wd;
		    }
		    
		    //voluntaryworkers_availability
		    $v_av_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('VwAvailability')
		    ->whereIn("vw_id",$vw_ids)
		    ->andWhere("isdelete = 0");
		    $v_av_array = $v_av_q->fetchArray();
		    
		    foreach($v_av_array as $k=>$w_al){
		        $source_data['availability'][$w_al['vw_id']][] = $w_al;
		    }
		    
		    
		    $ident_fields = array("id","img_path","create_date","create_user","change_date","change_user");
		    
		    if(!empty($source_array))
		    {

		        foreach($source_array as $sp_key => $source_value)
		        {
		            $insert_vw = new Voluntaryworkers();
		            $insert_vw->clientid = $target;

		            foreach($source_value as $field=>$value){
		                if(!in_array($field,$ident_fields)){
		                    
    		                if($field == "clientid"){
    		                  $insert_vw->clientid = $target;
    		                } 
    		                elseif($field == "hospice_association"){
    		                    if($h_connection[$value])
    		                    {
    		                        $insert_vw->hospice_association = $h_connection[$value];
    		                    }
    		                    else
    		                    {
    		                        $insert_vw->hospice_association = "0";
    		                    }
    		                } 
                            else
                            {
    		                  $insert_vw->$field = $value;
                            }
		                }
		            }
		            $insert_vw->save();
		            
		            $inserted_id = $insert_vw->id;
		            
		            /* ################## STATUS ######################################## */
		            if(!empty($source_data['status'][$source_value['id']]) && $inserted_id)
		            {
		                $vw_statuses_data_array = array();

		                print_r($source_data['status'][$source_value['id']]); 
		                foreach($source_data['status'][$source_value['id']] as $k_status => $v_status_arr)
		                {
		                    $vw_statuses_data_array[] = array(
		                        'vw_id' => $inserted_id,
		                        'clientid' => $target,
		                        'status' => !empty($ss_connection[$v_status_arr]) ? $ss_connection[$v_status_arr] : $v_status_arr,
		                    );
		                }
		            
		                $collection = new Doctrine_Collection('VoluntaryworkersStatuses');
		                $collection->fromArray($vw_statuses_data_array);
		                $collection->save();
		            }
		            // other statuses 
		            
		            
		            /* ################## AVAILABILITY #################################### */
		            if(!empty($source_data['availability'][$source_value['id']]) && $inserted_id){

		                $working_data_array = array();
		                
		                foreach($source_data['availability'][$source_value['id']] as $act => $act_ar){
		                    
		                    	
		                        $working_data_array[] = array(
		                            'vw_id' => $inserted_id,
		                            'clientid' => $target,
		                            'week_day' => $act_ar['week_day'],
		                            'start_time' =>  $act_ar['start_time'],
		                            'end_time' =>  $act_ar['end_time']
		                        );
		                        
		                }
		                $collection_w = new Doctrine_Collection('VwAvailability');
		                $collection_w->fromArray($working_data_array);
		                $collection_w->save();
		            }
		            	
		            /* ################## ACTIVITIES #################################### */
		            if(!empty($source_data['activity'][$source_value['id']]) && $inserted_id)
		            {
		                $vw_activities_data_array = array();
		                
		                foreach($source_data['activity'][$source_value['id']] as $k => $a_values)
		                {
		                        $vw_activities_data_array[] = array(
		                            'vw_id' => $inserted_id,
		                            'clientid' => $target,
		                            'activity' => $a_values['activity'],
		                            'comment' => $a_values['comment'],
		                            'date' => $a_values['date'],
		                            'team_event' => $a_values['team_event'],
		                            'team_event_id' => $a_values['team_event_id'],
		                            'team_event_type' => $event_types_select[$a_values['team_event_type']],
		                            'duration' => $a_values['duration'],
		                            'driving_time' => $a_values['driving_time']
		                        );
		                }
		                
		                $collection = new Doctrine_Collection('VoluntaryworkersActivities');
		                $collection->fromArray($vw_activities_data_array);
		                $collection->save();
		            }

		            /* ################## COLOR STATUSES #################################### */
		            if(!empty($source_data['color_status'][$source_value['id']]) && $inserted_id){
		                
		                $c_status_data_array = array();
		                
		                foreach($source_data['color_status'][$source_value['id']] as $row_id => $cstatus_data){
                        	    
                    	    if(strlen($cstatus_data['end_date']) >"1"){
                    	        
                    	        $cstatus_data['end_date'] = date("Y-m-d H:i:s",strtotime($cstatus_data['end_date']));

                    	    } else {
                    	        
                    	        $cstatus_data['end_date'] = "0000-00-00 00:00:00";
                    	    }
                    	    
                    	    if(strlen($cstatus_data['start_date']) >"1"){

                    	        $cstatus_data['start_date'] = date("Y-m-d H:i:s",strtotime($cstatus_data['start_date']));
                    	    
                    	    } else {

                    	        $cstatus_data['start_date'] = "0000-00-00 00:00:00";
                    	    
                    	    }
                    	    
		                    $c_status_data_array[] = array(
		                        'clientid' => $target,
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

		            /* ################## VW WORK DATA ########################################### */
		            if(!empty($source_data['work_bulk'][$source_value['id']]) && $inserted_id){
		                
		                $work_bulk_array = array();
		                
		                foreach($source_data['work_bulk'][$source_value['id']] as $bw_row_id => $bw_row_data){
		                    $work_bulk_array[] = array(
		                        
		                        'type' => $bw_row_data['type'],
		                        'vw_id' => $inserted_id,
		                        'work_date' => $bw_row_data['work_date'],
		                        'comments' => $bw_row_data['comments'],
		                        'besuchsdauer' => $bw_row_data['besuchsdauer'],
		                        'fahrtkilometer' => $bw_row_data['fahrtkilometer'],
		                        'fahrtzeit' => $bw_row_data['fahrtzeit'],
		                        'grund' => $hvt_select[$bw_row_data['grund']],
		                        'amount' => $bw_row_data['amount'],
		                        'nightshift' => $bw_row_data['nightshift']
		                    );
		                }
		                $collection_bw = new Doctrine_Collection('VwWorkdata');
		                $collection_bw->fromArray($work_bulk_array);
		                $collection_bw->save();

		            }
		        }
		    }
		}
		
		

		public function  copy_healthinsurance($source,$target)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('HealthInsurance')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("extra = 0")
		    ->andWhere("onlyclients = 1");
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		         // get subdivisions 
                foreach($source_array as $k=>$hv){
                    $hi_ids[] = $hv['id'];
                }
 
                if(empty($hi_ids)){
                    $hi_ids[] = "99999999";
                }
                
                $h2s_q = Doctrine_Query::create()
                ->select('*')
                ->from('HealthInsurance2Subdivisions')
                ->whereIn("company_id",$hi_ids)
                ->andWhere("isdelete = 0")
                ->andWhere("onlyclients = 1 ");;
                $h2s_array = $h2s_q->fetchArray();
		        
                if(!empty($h2s_array)){
                    foreach($h2s_array as $k=>$s){
                        $sub[$s['company_id']][]= $s;
                    }
                }
		        
		        $ident_fields = array("id","create_user","change_date","change_user");
		        
		        foreach($source_array as $key => $data)
		        {
		            //Insert to target client
		            $insert_values = new HealthInsurance();
		            foreach($data as $field=>$value){
		                
                        if(!in_array($field,$ident_fields))
                        {
                            if($field == "clientid"){
        		              $insert_values->clientid = $target;
                            }
                            else 
                            {
        		              $insert_values->$field = $value;
                            }
                        }        
		            }
		            $insert_values->save();
		            
		            $inserted_id = $insert_values->id;
		            
		            if(!empty($sub[$data['id']]) && $inserted_id){
		                
		                foreach($sub[$data['id']] as $k=>$sdata){
		                    
		                    $insert_s_values = new HealthInsurance2Subdivisions();
		                    foreach($sdata as $field=>$value){
		                    
		                        if(!in_array($field,$ident_fields))
		                        {
		                            if($field == "clientid"){
		                                $insert_s_values->clientid = $target;
		                            }
		                            elseif($field == "company_id"){
		                                $insert_s_values->company_id = $inserted_id;
		                            }
		                            else
		                            {
		                                $insert_s_values->$field = $value;
		                            }
		                        }
		                    }		                    
		                    $insert_s_values->save();
		                }
		            }
		        }
		    }
		}
		

		public function  update_HealthInsurance($source,$target)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('HealthInsurance')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("extra = 0")
		    ->andWhere("onlyclients = 1");
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        
		        // get target list
		        $target_array = array();
		        $target_q = Doctrine_Query::create()
		        ->select('*')
		        ->from('HealthInsurance')
		        ->where('isdelete = 0 ')
		        ->andWhere('clientid=?', $target)
		        ->andWhere("extra = 0")
		        ->andWhere("onlyclients = 1");
		        $target_array= $target_q->fetchArray();
		        
		        $existing_items = array();
                foreach($target_array as $tk=>$hvt){
                    $existing_items[] = $hvt['name'];
                }
 
		        
		        
		         // get subdivisions 
                foreach($source_array as $k=>$hv){
                    $hi_ids[] = $hv['id'];
                }
 
                if(empty($hi_ids)){
                    $hi_ids[] = "99999999";
                }
                
                $h2s_q = Doctrine_Query::create()
                ->select('*')
                ->from('HealthInsurance2Subdivisions')
                ->whereIn("company_id",$hi_ids)
                ->andWhere("isdelete = 0")
                ->andWhere("onlyclients = 1 ");;
                $h2s_array = $h2s_q->fetchArray();
		        
                if(!empty($h2s_array)){
                    foreach($h2s_array as $k=>$s){
                        $sub[$s['company_id']][]= $s;
                    }
                }
		        
		        $ident_fields = array("id","create_user","change_date","change_user");
		        
		        foreach($source_array as $key => $data)
		        {
		            
		            if(!in_array($data['name'],$existing_items)){
		                
    		            //Insert to target client
    		            $insert_values = new HealthInsurance();
    		            foreach($data as $field=>$value){
    		                
                            if(!in_array($field,$ident_fields))
                            {
                                if($field == "clientid"){
            		              $insert_values->clientid = $target;
                                }
                                else 
                                {
            		              $insert_values->$field = $value;
                                }
                            }        
    		            }
    		            $insert_values->save();
    		            
    		            $inserted_id = $insert_values->id;
    		            
    		            if(!empty($sub[$data['id']]) && $inserted_id){
    		                
    		                foreach($sub[$data['id']] as $k=>$sdata){
    		                    
    		                    $insert_s_values = new HealthInsurance2Subdivisions();
    		                    foreach($sdata as $field=>$value){
    		                    
    		                        if(!in_array($field,$ident_fields))
    		                        {
    		                            if($field == "clientid"){
    		                                $insert_s_values->clientid = $target;
    		                            }
    		                            elseif($field == "company_id"){
    		                                $insert_s_values->company_id = $inserted_id;
    		                            }
    		                            else
    		                            {
    		                                $insert_s_values->$field = $value;
    		                            }
    		                        }
    		                    }		                    
    		                    $insert_s_values->save();
    		                }
    		            }
		           }
		        }
		    }
		}
		

		public function  copy_bedarfsmedikation($source,$target)
		{
		    // get source list
		    $source_array = array();
		    $ident_fields = array("id","create_user","change_date","change_user");
		    
		    
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('BedarfsmedicationMaster')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source);
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		         // get subdivisions 
                foreach($source_array as $k=>$hv){
                    $b_ids[] = $hv['id'];
                }
 
                if(empty($b_ids)){
                    $b_ids[] = "99999999";
                }
                
                $m2b_q = Doctrine_Query::create()
                ->select('*')
                ->from('Bedarfsmedication')
                ->whereIn("bid",$b_ids);
                $m2b_array = $m2b_q->fetchArray();

                
                $med_ids[] = '999999999999';
                foreach($m2b_array as $k=>$mb){
                    $med_ids[] = $mb['medication_id'];
                    
                    $m2b[$mb['bid']][] = $mb;
                }
                
                // get all medications used in bedarf 
                $med = new Medication();
                $medarr = $med->getMedicationById($med_ids);
                
                if(!empty($medarr))
                {
                    foreach($medarr as $key => $mdata)
                    {
                        // create new medication
                        $insert_values = new Medication();
                        foreach($mdata as $field=>$value){
                    
                            if(!in_array($field,$ident_fields))
                            {
                                if($field == "clientid"){
                                    $insert_values->clientid = $target;
                                }
                                else
                                {
                                    $insert_values->$field = $value;
                                }
                            }
                        }
                        $insert_values->save();
                        $inserted_id = $insert_values->id;
                        $new_medication_id[$mdata['id']] = $inserted_id; 
                    }
                }
                
                
		        foreach($source_array as $key => $data)
		        {
		            //Insert to target client
		            $insert_values_bm = new BedarfsmedicationMaster();
		            foreach($data as $field=>$value){
		                
                        if(!in_array($field,$ident_fields))
                        {
                            if($field == "clientid"){
        		              $insert_values_bm->clientid = $target;
                            }
                            else 
                            {
        		              $insert_values_bm->$field = $value;
                            }
                        }        
		            }
		            $insert_values_bm->save();
		            $inserted_id_bm = $insert_values_bm->id;
		            
		            if(!empty($m2b[$data['id']]) && $inserted_id_bm){
		                
		                foreach($m2b[$data['id']] as $k=>$sdata){
		                    
		                    $insert_s_values = new Bedarfsmedication();
		                    
		                    foreach($sdata as $bfield=>$bvalue){
		                    
		                        if(!in_array($bfield,$ident_fields))
		                        {
		                            if($bfield == "bid"){
		                                $insert_s_values->bid = $inserted_id_bm;
		                            }
		                            elseif($bfield == "medication_id"){
		                                $insert_s_values->medication_id = $new_medication_id[$bvalue];
		                            }
		                            else
		                            {
		                                $insert_s_values->$bfield = $bvalue;
		                            }
		                        }
		                    }		                    
		                    $insert_s_values->save();
		                }
		            }
		        }
		    }
		}

		
		
		public function  copy_Pflegedienstes($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Pflegedienstes')
			->where('isdelete = 0 ')
			->andWhere('clientid=?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();
		
			
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["logo"] = "";
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}

				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Pflegedienstes');
					$collection->fromArray($source_array);
					$collection->save();
				}
				
				
			}
		}
		
		
		public function  update_Pflegedienstes($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Pflegedienstes')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		
		
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(nursing,first_name,last_name) as ident')
		    ->from('Pflegedienstes')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		
		
		    $existing_items = array();
		
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['nursing'].$values['first_name'].$values['last_name'];
		
		            if(!in_array($ident,$existing_items)){
		
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["logo"] = "";
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('Pflegedienstes');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}
		
		 
		
		public function  copy_services_funeral($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Servicesfuneral')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source);
			$source_array= $source_q->fetchArray();

			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Servicesfuneral');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		
		
		public function  copy_PatientReferredBy($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('PatientReferredBy')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source);
			$source_array= $source_q->fetchArray();

			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('PatientReferredBy');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		public function  copy_Homecare($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Homecare')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();

			
			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["logo"] = "";
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Homecare');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		

		public function  update_Homecare($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Homecare')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		
		
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(homecare,first_name,last_name) as ident')
		    ->from('Homecare')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		
		
		    $existing_items = array();
		
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['homecare'].$values['first_name'].$values['last_name'];
		
		            if(!in_array($ident,$existing_items)){
		
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["logo"] = "";
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('Homecare');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}		
		
		public function  copy_Churches($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Churches')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();

			
			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Churches');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		public function  copy_Hospiceassociation($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Hospiceassociation')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();

			
			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Hospiceassociation');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		
		public function  update_Hospiceassociation($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Hospiceassociation')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		
		
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(hospice_association,first_name,last_name) as ident')
		    ->from('Hospiceassociation')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		
		
		    $existing_items = array();
		
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['hospice_association'].$values['first_name'].$values['last_name'];
		
		            if(!in_array($ident,$existing_items)){
		
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('Hospiceassociation');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}		
		
		
		
		
		public function  copy_FamilyDegree($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('FamilyDegree')
			->where('isdelete = 0 ')
			->andWhere('clientid= ?', $source);
			$source_array= $source_q->fetchArray();

			
			if(!empty($source_array))
			{
				foreach($source_array as  $key=> $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('FamilyDegree');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}

		
		
		public function  copy_Careservices($source,$target,$userid)
		{
		    // Get sourse specialists types  data
		    $groups = new CareservicesGroups();
		    $source_groups = $groups->get_client_groups($source);
		    
		    if(!empty($source_groups))
		    {
		        // DELETE -  existing ones?
		        		        
		        // insert specialists types  for target client
		        foreach($source_groups as $spt_key=>$source_spt_value)
		        {
		            $insert_sp_types = new CareservicesGroups();
		            $insert_sp_types->client = $target;
		            $insert_sp_types->groupname = $source_spt_value['groupname'];
		            $insert_sp_types->save();
		            $types_connection[$source_spt_value['id']] = $insert_sp_types->id; 
		        }
		    }
		    
		    // get source  - specialists data
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('CareservicesItems')
		    ->where("isdelete = 0")
		    ->andWhere('clientid= ?', $source);//ISPC-2652, elena, 08.10.2020
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        
		        // DELETE -  existing ones?
		        foreach($source_array as $sp_key => $source_sp_value)
		        {
		            $insert_sp = new CareservicesItems();
		            $insert_sp->client = $target;
		            $insert_sp->item = $source_sp_value['item'];
		            if($types_connection[$source_sp_value['group_id']])
		            {
    		            $insert_sp->group_id = $types_connection[$source_sp_value['group_id']];
		            } 
		            else
		            {
    		            $insert_sp->group_id = "0";
		            }
 
		            $insert_sp->save();
		        }
		    }
		}
		


		
		// 10.01.2018
// 			$columns = $this->getTable()->getColumns();
// 			$columns_names = array_keys($columns);

		// change to a more 
		public function  copy_Physiotherapists($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Physiotherapists')
			->where('isdelete = 0 ')
			->andWhere('clientid=?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["logo"] = "";
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Physiotherapists');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		
		public function  copy_Supplies($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Supplies')
			->where('isdelete = 0 ')
			->andWhere('clientid=?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["logo"] = "";
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Supplies');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		
		public function  update_Supplies($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Supplies')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		
		
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(supplier,first_name,last_name) as ident')
		    ->from('Supplies')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		
		
		    $existing_items = array();
		
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['supplier'].$values['first_name'].$values['last_name'];
		
		            if(!in_array($ident,$existing_items)){
		
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["logo"] = "";
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('Supplies');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}		
		
		
		public function  copy_Suppliers($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Suppliers')
			->where('isdelete = 0 ')
			->andWhere('clientid=?', $source)
			->andWhere("indrop = 0");
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Suppliers');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		

		public function  update_Suppliers($source,$target,$userid)
		{
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Suppliers')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $source)
		    ->andWhere("indrop = 0");
		    $source_array= $source_q->fetchArray();
		
		
		    // get target array
		    $target_array = array();
		    $target_q = Doctrine_Query::create()
		    ->select('*, concat(supplier,first_name,last_name) as ident')
		    ->from('Suppliers')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid=?', $target)
		    ->andWhere("indrop = 0");
		    $target_array = $target_q->fetchArray();
		
		
		    $existing_items = array();
		
		    if(!empty($source_array))
		    {
		        if(!empty($target_array))
		        {
		            foreach($target_array as $k=>$td){
		                $existing_items[] = $td['ident'];
		            }
		        }
		
		        $update_array = array();
		        foreach($source_array as $key => $values)
		        {
		            $ident = $values['supplier'].$values['first_name'].$values['last_name'];
		
		            if(!in_array($ident,$existing_items)){
		
		                $update_array[$key] = $values;
		                $update_array[$key]["id"] = NULL;
		                $update_array[$key]["logo"] = "";
		                $update_array[$key]["clientid"] = $target;
		                $update_array[$key]["create_date"] = date('Y-m-d H:i:s');
		                $update_array[$key]["create_user"] = $userid;
		                $update_array[$key]["change_date"] = "0000-00-00 00:00:00";
		                $update_array[$key]["change_user"] = "0";
		            }
		        }
		
		        if(count($update_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('Suppliers');
		            $collection->fromArray($update_array);
		            $collection->save();
		        }
		    }
		}		
		
		public function  copy_PatientFileTags($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('PatientFileTags')
			->where('isdelete = 0 ')
			->andWhere('client =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["client"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('PatientFileTags');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		public function  copy_DischargeMethod($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('DischargeMethod')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('DischargeMethod');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		public function  copy_DischargeLocation($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('DischargeLocation')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('DischargeLocation');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		public function  copy_Remedies($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Remedies')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Remedies');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		public function  copy_HospitalReasons($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('HospitalReasons')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('HospitalReasons');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
			
		public function  copy_MedicationTreatmentCare($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('MedicationTreatmentCare')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('MedicationTreatmentCare');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		public function  copy_Aid($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('Aid')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('Aid');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		/**
		 * 08.07.2019 	    // Maria:: Migration ISPC to CISPC 08.08.2020
		 * TODO-2397
		 * @param unknown $source
		 * @param unknown $target
		 * @param unknown $userid
		 */
		public function  copy_MedicationIndications($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('MedicationIndications')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('MedicationIndications');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		
		/**
		 * 08.07.2019
		 * TODO-2397
		 * @param unknown $source
		 * @param unknown $target
		 * @param unknown $userid
		 */
		public function  copy_MedicationDosageform($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('MedicationDosageform')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('MedicationDosageform');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
		/**
		 * 08.07.2019
		 * TODO-2397
		 * @param unknown $source
		 * @param unknown $target
		 * @param unknown $userid
		 */
		public function  copy_MedicationUnit($source,$target,$userid)
		{
			// get source list
			$source_array = array();
			$source_q = Doctrine_Query::create()
			->select('*')
			->from('MedicationUnit')
			->where('isdelete = 0 ')
			->andWhere('clientid =?', $source) ;
			$source_array= $source_q->fetchArray();
		
				
			if(!empty($source_array))
			{
				foreach($source_array as $key => $values)
				{
					$source_array[$key]["id"] = NULL;
					$source_array[$key]["clientid"] = $target;
					$source_array[$key]["create_date"] = date('Y-m-d H:i:s');
					$source_array[$key]["create_user"] = $userid;
						
					$source_array[$key]["change_date"] = "0000-00-00 00:00:00";
					$source_array[$key]["change_user"] = "0";
				}
		
				if(count($source_array) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('MedicationUnit');
					$collection->fromArray($source_array);
					$collection->save();
				}
			}
		}
	
		/*
		 * ISPC-2302 @Lore 21.10.2019 
		 */
		public function copy_Members($source,$target)
		{
		    // Get sourse Memberships  data
		    $memberships = new Memberships();
		    $source_get_memberships = $memberships->get_memberships($source);
		    
		    if(!empty($source_get_memberships))
		    {	        
		        // insert memberships  for target client
		        foreach($source_get_memberships as $spt_key=>$source_mss_value)
		        {	            
		            $insert_memberships = new Memberships();
		            $insert_memberships->clientid = $target;
		            $insert_memberships->membership = $source_mss_value['membership'].' (II)';
		            $insert_memberships->shortcut = substr($source_mss_value['membership'],0,2);
		            
		            $insert_memberships->save();
		            $types_connection['Memberships'][$source_mss_value['id']] = $insert_memberships->id;
		        }
   
		    }
		    
		    
		    // get source  - member data
		    $source_member = new Member();
		    $source_array_mb= $source_member->get_client_members($source);
		    
		    if(!empty($source_array_mb))
		    {
		        foreach($source_array_mb as $sp_key => $source_mb_value)
		        {
		            $insert_mb = new Member();
		            $insert_mb->clientid = $target;
		            $insert_mb->type = $source_mb_value['type'];
		            $insert_mb->member_company= $source_mb_value['member_company'];
		            $insert_mb->title = $source_mb_value['title'];
		            $insert_mb->salutation_letter = $source_mb_value['salutation_letter'];
		            $insert_mb->auto_member_number= $source_mb_value['auto_member_number'];
		            $insert_mb->member_number = $source_mb_value['member_number'];
		            $insert_mb->salutation = $source_mb_value['salutation'];
		            $insert_mb->first_name = $source_mb_value['first_name'];
		            $insert_mb->last_name = $source_mb_value['last_name'];
		            $insert_mb->birthd = $source_mb_value['birthd'];
		            $insert_mb->gender = $source_mb_value['gender'];
		            $insert_mb->street1 = $source_mb_value['street'];
		            $insert_mb->zip = $source_mb_value['zip'];
		            $insert_mb->city = $source_mb_value['city'];
		            $insert_mb->fax = $source_mb_value['fax'];
		            $insert_mb->phone = $source_mb_value['phone'];
		            $insert_mb->mobile = $source_mb_value['mobile'];
		            $insert_mb->email = $source_mb_value['email'];
		            $insert_mb->bank_name = $source_mb_value['bank_name'];
		            $insert_mb->iban = $source_mb_value['iban'];
		            $insert_mb->bic = $source_mb_value['bic'];
		            $insert_mb->account_holder = $source_mb_value['account_holder'];
		            $insert_mb->mandate_reference = $source_mb_value['mandate_reference'];
		            $insert_mb->mandate_reference_date = $source_mb_value['mandate_reference_date'];
		            $insert_mb->remarks = $source_mb_value['remarks'];
		            $insert_mb->inactive = $source_mb_value['inactive'];
		            $insert_mb->inactive_from = $source_mb_value['inactive_from'];
		            $insert_mb->status = $source_mb_value['status'];
		            $insert_mb->profession = $source_mb_value['profession'];
		            $insert_mb->street2 = $source_mb_value['street2'];
		            $insert_mb->country = $source_mb_value['country'];
		            $insert_mb->website = $source_mb_value['website'];
		            $insert_mb->memos = $source_mb_value['memos'];
		            $insert_mb->comments = $source_mb_value['comments'];
		            $insert_mb->payment_method_id = $source_mb_value['payment_method_id'];
		            $insert_mb->merged_parent = $source_mb_value['merged_parent'];
		            $insert_mb->merged_slave = $source_mb_value['merged_slave'];
		            
		            $insert_mb->save();
		            $types_connection['Member'][$source_mb_value['id']] = $insert_mb->id;
		            
		        }
		        
		        // get source  - member2memberships data
		        $source_q = Doctrine_Query::create()
		        ->select('*')
		        ->from('Member2Memberships')
		        ->where("isdelete = 0")
		        ->andWhere('clientid= "'.$source.'" ');
		        $source_array_mms= $source_q->fetchArray();
		        
		        if(!empty($source_array_mms))
		        {
		            foreach($source_array_mms as $sp_key => $source_mms_value)
		            {//dd($source_mms_value,$types_connection);
		                $insert_mms = new Member2Memberships();
		                $insert_mms->clientid = $target;
		                
		                if($types_connection['Member'][$source_mms_value['member']])
		                {
		                    $insert_mms->member = $types_connection['Member'][$source_mms_value['member']];
		                }
		                else
		                {
		                    $insert_mms->member = "0";
		                }
		                if($types_connection['Memberships'][$source_mms_value['membership']])
		                {
		                    $insert_mms->membership = $types_connection['Memberships'][$source_mms_value['membership']];
		                }
		                else
		                {
		                    $insert_mms->membership = "0";
		                }
		                
		                $insert_mms->membership_price = $source_mms_value['membership_price'];
		                $insert_mms->start_date = $source_mms_value['start_date'];
		                $insert_mms->end_date = $source_mms_value['end_date'];
		                $insert_mms->isdelete = $source_mms_value['isdelete'];
		                $insert_mms->end_reasonid = $source_mms_value['end_reasonid'];
		                $insert_mms->save();
		                
		            }
		        }
		        
		        // get source  - MemberReferalTab data
		        $source_q = Doctrine_Query::create()
		        ->select('*')
		        ->from('MemberReferalTab')
		        ->where("isdelete = 0")
		        ->andWhere('clientid= "'.$source.'" ');
		        $source_array_mrt= $source_q->fetchArray();
		        
		        if(!empty($source_array_mrt))
		        {
		            foreach($source_array_mrt as $sp_key => $source_mrt_value)
		            {
		                $insert_mrt = new MemberReferalTab();
		                $insert_mrt->clientid = $target;
		                if($types_connection['Member'][$source_mrt_value['memberid']])
		                {
		                    $insert_mrt->memberid = $types_connection['Member'][$source_mrt_value['memberid']];
		                }
		                else
		                {
		                    $insert_mrt->memberid = "0";
		                }
		                $insert_mrt->referal_tab = $source_mrt_value['referal_tab'];
		                $insert_mrt->save();
		                
		            }
		        }
		        
		        // get source  - MembersSepaSettings data
		        $source_q = Doctrine_Query::create()
		        ->select('*')
		        ->from('MembersSepaSettings')
		        ->where("isdelete = 0")
		        ->andWhere('clientid= "'.$source.'" ');
		        $source_array_mss= $source_q->fetchArray();
		        
		        if(!empty($source_array_mss))
		        {
		            foreach($source_array_mss as $sp_key => $source_mss_value)
		            {
		                $insert_mss = new MembersSepaSettings();
		                $insert_mss->clientid = $target;
		                
		                if($types_connection['Member'][$source_mss_value['memberid']])
		                {
		                    $insert_mss->memberid = $types_connection['Member'][$source_mss_value['memberid']];
		                }
		                else
		                {
		                    $insert_mss->memberid = "0";
		                }
		                if($types_connection['Memberships'][$source_mss_value['member2membershipsid']])
		                {
		                    $insert_mss->member2membershipsid = $types_connection['Memberships'][$source_mss_value['member2membershipsid']];
		                }
		                else
		                {
		                    $insert_mss->member2membershipsid = "0";
		                }
		                
		                $insert_mss->howoften = $source_mss_value['howoften'];
		                $insert_mss->when_day = $source_mss_value['when_day'];
		                $insert_mss->when_month = $source_mss_value['when_month'];
		                $insert_mss->amount = $source_mss_value['amount'];
		                
		                $insert_mss->save();
		                
		            }
		        }
		        
	        
		        // get source  - MemberFamily data
		        $source_q = Doctrine_Query::create()
		        ->select('*')
		        ->from('MemberFamily')
		        ->where("isdelete = 0")
		        ->andWhere('clientid= "'.$source.'" ');
		        $source_array_mf= $source_q->fetchArray();
		        
		        if(!empty($source_array_mf))
		        {
		            foreach($source_array_mf as $sp_key => $source_mf_value)
		            {
		                $insert_mf = new MemberFamily();
		                $insert_mf->clientid = $target;
		                $insert_mf->type = $source_mf_value['type'];
		                
		                if($types_connection['Member'][$source_mf_value['member_id']])
		                {
		                    $insert_mf->member_id = $types_connection['Member'][$source_mf_value['member_id']];
		                }
		                else {
		                    $insert_mf->member_id = 0;
		                }
		                $insert_mf->auto_member_number  = $source_mf_value['auto_member_number'];
		                $insert_mf->member_number  = $source_mf_value['member_number'];
		                $insert_mf->member_company  = $source_mf_value['member_company'];
		                $insert_mf->title   = $source_mf_value['title'];
		                $insert_mf->salutation_letter   = $source_mf_value['salutation_letter'];
		                $insert_mf->salutation  = $source_mf_value['salutation'];
		                $insert_mf->first_name  = $source_mf_value['first_name'];
		                $insert_mf->last_name  = $source_mf_value['last_name'];
		                $insert_mf->gender  = $source_mf_value['gender'];
		                $insert_mf->birthd   = $source_mf_value['birthd'];
		                $insert_mf->phone   = $source_mf_value['phone'];
		                $insert_mf->private_phone  = $source_mf_value['private_phone'];
		                $insert_mf->mobile  = $source_mf_value['mobile'];
		                $insert_mf->email  = $source_mf_value['email'];
		                $insert_mf->website  = $source_mf_value['website'];
		                $insert_mf->fax   = $source_mf_value['fax'];
		                $insert_mf->street1   = $source_mf_value['street1'];
		                $insert_mf->street2  = $source_mf_value['street2'];
		                $insert_mf->zip  = $source_mf_value['zip'];
		                $insert_mf->city  = $source_mf_value['city'];
		                $insert_mf->country  = $source_mf_value['country'];
		                $insert_mf->profession   = $source_mf_value['profession'];
		                $insert_mf->inactive   = $source_mf_value['inactive'];
		                $insert_mf->inactive_from  = $source_mf_value['inactive_from'];
		                $insert_mf->status  = $source_mf_value['status'];
		                $insert_mf->shortname  = $source_mf_value['shortname'];
		                $insert_mf->bank_name  = $source_mf_value['bank_name'];
		                $insert_mf->bank_account_number   = $source_mf_value['bank_account_number'];
		                $insert_mf->bank_number   = $source_mf_value['bank_number'];
		                $insert_mf->iban  = $source_mf_value['iban'];
		                $insert_mf->bic  = $source_mf_value['bic'];
		                $insert_mf->account_holder  = $source_mf_value['account_holder'];
		                $insert_mf->mandate_reference  = $source_mf_value['mandate_reference'];
		                $insert_mf->mandate_reference_date   = $source_mf_value['mandate_reference_date'];
		                $insert_mf->payment_method_id   = $source_mf_value['payment_method_id'];
		                $insert_mf->remarks  = $source_mf_value['remarks'];
		                $insert_mf->memos  = $source_mf_value['memos'];
		                $insert_mf->comments  = $source_mf_value['comments'];
		                $insert_mf->img_path  = "";
		                
		                $insert_mf->save();
		                
		            }
		        }
		        	        
		    }
		        
		}
	
		
		
		public function  copy_order_arzneimittel($source,$target,$userid)
		{
		    // get source list
		    $category = 'drugs';
		    
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('ClientOrderMaterials')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid =?', $source) 
		    ->andWhere('category =?', $category);
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {
		            $source_array[$key]["id"] = NULL;
		            $source_array[$key]["clientid"] = $target;
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		        }
		        
		        if(count($source_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('ClientOrderMaterials');
		            $collection->fromArray($source_array);
		            $collection->save();
		        }
		    }
		}
		
		public function  copy_order_hilfsmittel($source,$target,$userid)
		{
		    $category = 'auxiliaries';
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('ClientOrderMaterials')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid =?', $source)
		    ->andWhere('category =?', $category);
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {
		            $source_array[$key]["id"] = NULL;
		            $source_array[$key]["clientid"] = $target;
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		        }
		        
		        if(count($source_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('ClientOrderMaterials');
		            $collection->fromArray($source_array);
		            $collection->save();
		        }
		    }
		}
		
		/*
		 *  ISPC-2302 @Lore 21.10.2019 
		 */
		public function  copy_order_pflegehilfsmittel($source,$target,$userid)
		{
		    $category = 'nursingauxiliaries';
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('ClientOrderMaterials')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid =?', $source)
		    ->andWhere('category =?', $category);
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {
		            $source_array[$key]["id"] = NULL;
		            $source_array[$key]["clientid"] = $target;
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		        }
		        
		        if(count($source_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('ClientOrderMaterials');
		            $collection->fromArray($source_array);
		            $collection->save();
		        }
		    }
		}
		
		public function  copy_order_verbandsstoffe($source,$target,$userid)
		{
		    $category = 'dressings';
		    // get source list
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('ClientOrderMaterials')
		    ->where('isdelete = 0 ')
		    ->andWhere('clientid =?', $source)
		    ->andWhere('category =?', $category);
		    
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {
		            $source_array[$key]["id"] = NULL;
		            $source_array[$key]["clientid"] = $target;
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		        }
		        
		        if(count($source_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('ClientOrderMaterials');
		            $collection->fromArray($source_array);
		            $collection->save();
		        }
		    }
		}

		/*
		 * ISPC-2302 @Lore 29.10.2019
		 */
		public function copy_clientsymptomsgroups($source,$target)
		{
		    $types = new ClientSymptomsGroups();
		    $source_symp_types = $types->get_client_symptoms_groups($source);
		    
		    if(!empty($source_symp_types))
		    {
		        // insert types  for target client
		        foreach($source_symp_types as $spt_key=>$source_spt_value)
		        {
		            $insert_sp_types = new ClientSymptomsGroups();
		            $insert_sp_types->clientid = $target;
		            $insert_sp_types->groupname = $source_spt_value['groupname'];
		            $insert_sp_types->save();
		            $types_connection[$source_spt_value['id']] = $insert_sp_types->id;
		        }
		    }
		    
		    // get source  - symptoms data
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('ClientSymptoms')
		    ->where("isdelete = 0")
		    ->andWhere('clientid= "'.$source.'" ');
		    $source_array= $source_q->fetchArray();
		    
		    if(!empty($source_array))
		    {
		        
		        foreach($source_array as $sp_key => $source_sp_value)
		        {
		            $insert_sp = new ClientSymptoms();
		            $insert_sp->clientid = $target;
		            $insert_sp->description = $source_sp_value['description'];
		            
		            if($types_connection[$source_sp_value['group_id']])
		            {
		                $insert_sp->group_id = $types_connection[$source_sp_value['group_id']];
		            }
		            else
		            {
		                $insert_sp->group_id = "0";
		            }
		            
		            $insert_sp->save();
		        }
		    }
		}
		
		/*
		 * @auth Lore
		 * ISPC-2302 pct.1  18.10.2019
		 */
		public function  copy_tabels($table_name, $source, $target, $userid)
		{
		    if( empty($table_name) ){
		        return;
		    }
		    
		    if( empty($source) || empty($target)  ){
		        return;
		    }
		    
		    
		    // get source list
		    $cols_plus_indrop = array( "FamilyDoctor", "Pharmacy", "Pflegedienstes" ,"Homecare" , "Churches", "Hospiceassociation", "Physiotherapists" ,"Supplies", "Suppliers" );
		    
		    $cols_plus_logo = array( "Pflegedienstes" , "Homecare", "Physiotherapists", "Supplies", "Suppliers"  );
		    
		    $client_vs_clientid = array( "ClientShifts", "PatientFileTags" );
		    
		    $source_array = array();
		    $source_q = Doctrine_Query::create()
		    ->select('*')
		    ->from($table_name)
		    ->where('isdelete = 0 ');
		    
		    if(in_array($table_name, $cols_plus_indrop)){
		        $source_q->andWhere("indrop = 0");
		    }
		    
		    if(in_array($table_name, $client_vs_clientid)){
		        $source_q->andWhere('client =?', $source) ;
		    }
		    else {
		       $source_q->andWhere('clientid =?', $source) ;
		    }
		    
		    $source_array= $source_q->fetchArray();
		    
		    
		    if(!empty($source_array))
		    {
		        foreach($source_array as $key => $values)
		        {
		            $source_array[$key]["id"] = NULL;
		            
		            if(in_array($table_name, $client_vs_clientid)){
		                $source_array[$key]["client"] = $target;
		            } else {
		                $source_array[$key]["clientid"] = $target;
		            }
		            
		            $source_array[$key]["create_date"] = date('Y-m-d H:i:s');
		            $source_array[$key]["create_user"] = $userid;
		            
		            $source_array[$key]["change_date"] = "0000-00-00 00:00:00";
		            $source_array[$key]["change_user"] = "0";
		            
		            if(in_array($table_name, $cols_plus_logo)){
		                $source_array[$key]["logo"] = "";
		            }
		            
		        }
		        
		        if(count($source_array) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection($table_name);
		            $collection->fromArray($source_array);
		            $collection->save();
		        }
		    }
		}
		
		
		
	}
?>