<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockClientSymptoms', 'MDAT');

	class FormBlockClientSymptoms extends BaseFormBlockClientSymptoms {

		public function getPatientFormBlockClientSymptoms($ipid, $contact_form_id, $allow_deleted = false)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockClientSymptoms')
				->where("ipid='" . $ipid . "'")
				->andWhere('contact_form_id ="' . $contact_form_id . '"');
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}
			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				foreach($groupsarray as $key => $action_details)
				{
					$patient_actions[$action_details['id']] = $action_details;
				}

				return $patient_actions;
			}
		}

		/**
		 * 
		 * @param unknown $ipids
		 * @param unknown $contact_form_ids
		 * @param boolean $only_checked
		 * @param boolean $allow_deleted
		 * @param boolean $all_data
		 * @return unknown
		 * 
		 * 
		 * On 20.01.2020 - Added new param- all_data by Ancuta for ISPC-1236 
		 */
		public function get_patients_form_block_ClientSymptoms($ipids, $contact_form_ids, $only_checked = false, $allow_deleted = false,$all_data = false)
		{

			if(!is_array($ipids))
			{
				$ipids_array = array($ipids);
			}
			else
			{
				$ipids_array = $ipids;
			}

			if(!is_array($contact_form_ids))
			{
				$contact_form_ids_array = array($contact_form_ids);
			}
			else
			{
				$contact_form_ids_array = $contact_form_ids;
			}

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockClientSymptoms')
				->whereIn("ipid", $ipids_array)
				->andWhereIn("contact_form_id", $contact_form_ids_array);
			if($only_checked === true)
			{
				$groups_sql->andWhere('action_value = 1');
			}

			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				foreach($groupsarray as $key => $action_details)
				{
					
					if($all_data){
					    
    					$patient_actions[$action_details['ipid']][$action_details['contact_form_id']][] = $action_details;
					} else{
    					$patient_actions[$action_details['ipid']][$action_details['contact_form_id']][] = $action_details['symptom_id'];
					    
					}
				}
				return $patient_actions;
			}
		}

		//Deprecated - Ancuta 01.10.2018
		public function get_last_patients_client_symptoms_old($ipid,$all_details = true,$allow_deleted = false)
		{
            // get last contact form
		    $cf_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('ContactForms')
		    ->where("ipid='" . $ipid . "'")
	        ->andWhere('isdelete = 0')
		    ->orderBy("start_date DESC")
		    ->limit('1');
		    $cf_arr = $cf_sql->fetchArray();
		    
		    if(!empty($cf_arr))
		    {
		        $last_cf_id = $cf_arr[0]['id']; 
			
    			$groups_sql = Doctrine_Query::create()
    				->select('*')
    				->from('FormBlockClientSymptoms')
    				->where("ipid='" . $ipid . "'");
    			if($last_cf_id ){
        			$groups_sql->andWhere('contact_form_id ="' . $last_cf_id  . '"');
    			}
    			if(!$allow_deleted)
    			{
    				$groups_sql->andWhere('isdelete = 0');
    			}
    
    			$groupsarray = $groups_sql->fetchArray();
    
    			if($groupsarray)
    			{
    			    $user_syms = array();
    				foreach($groupsarray as $key => $action_details)
    				{
    				    if($all_details){
        					$patient_actions[$action_details['id']] = $action_details;
    				    } else{
    				        if(!in_array())
        					$patient_actions[$action_details['id']]['symptom_id'] = $action_details['symptom_id'];
        					$user_syms[] = $action_details['symptom_id'];
    				    }
    				}
    				return $patient_actions;
    			}
    			
    		}
		}

	
		/**
		 *
		 * @param unknown $ipid
		 * @param string $all_details
		 * @param string $allow_deleted
		 * @return Ambigous <unknown, multitype:, Doctrine_Collection>
		 * Changed on 01.10.2018
		 * Added forms type arrat to the query - so only forms that have permission to client system block are allowed here
		 */
		
		public function get_last_patients_client_symptoms($ipid,$all_details = true,$allow_deleted = false)
		{
		
		    // get only the forms that have access to symptom
		    // Added on 01.10.2018
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $FormBlocks2Type = new FormBlocks2Type();
		    $forms2blocks = $FormBlocks2Type->get_form_types_blocks_special($clientid, 0, array("clientsymptoms"));
		
		    $form_type_ids = array();
		    if(!empty($forms2blocks)){
		        foreach($forms2blocks as $k=>$f2b){
		            $form_type_ids[] = $f2b['form_type'];
		        }
		    }
		
		    // get last contact form
		    $cf_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('ContactForms')
		    ->where("ipid= ? ", $ipid);
		    if(!empty($form_type_ids )){
		        $cf_sql->andWhereIn('form_type', $form_type_ids );
		    }
		    $cf_sql->andWhere('isdelete = 0')
		    ->orderBy("start_date DESC")
		    ->limit('1');
		    $cf_arr = $cf_sql->fetchArray();

		    
		
		    if(!empty($cf_arr))
		    {
		        $last_cf_id = $cf_arr[0]['id'];
		        $groups_sql = Doctrine_Query::create()
		    				->select('*')
		    				->from('FormBlockClientSymptoms')
		    				->where("ipid=?", $ipid );
		         
		        if($last_cf_id ){
		            $groups_sql->andWhere('contact_form_id = ? ',$last_cf_id );
		        }
		         
		        if(!$allow_deleted)
		        {
		            $groups_sql->andWhere('isdelete = 0');
		        }
		
		        $groupsarray = $groups_sql->fetchArray();
		        
		        if($groupsarray)
		        {
		            $user_syms = array();
		            foreach($groupsarray as $key => $action_details)
		            {
		                if($all_details){
		                    $patient_actions[$action_details['id']] = $action_details;
		                } else{
		                    if(!in_array())
		                        $patient_actions[$action_details['id']]['symptom_id'] = $action_details['symptom_id'];
		                    $user_syms[] = $action_details['symptom_id'];
		                }
		            }
		            return $patient_actions;
		        }
		         
		    }
		}
		
		//ISPC-2516 Carmen 09.07.2020
		public static function get_patient_symptpomatology_period($clientid, $ipid, $period)
		{
			if(empty($ipid))
			{
				return;
			}
			
			$sql_period_params = array();
			
			if($period)
			{
				$sql_period_1 = ' (DATE(cf.date) != "1970-01-01" AND cf.date BETWEEN ? AND ? ) ';
			
				$sql_period_params = array( $period['start'], $period['end'] );
			}
			else
			{
				$sql_period_1 = ' DATE(cf.date) != "1970-01-01"  ';
			}
			
			if($period)
			{
				$sql_period_s = ' (DATE(cs.symptom_date) != "1970-01-01" AND DATE(cs.symptom_date) != "0000-00-00" AND cs.symptom_date BETWEEN ? AND ? ) ';
					
				$sql_period_params = array( $period['start'], $period['end'] );
			}
			else
			{
				$sql_period_s = ' DATE(cs.symptom_date) != "1970-01-01" AND DATE(cs.symptom_date) != "0000-00-00" ';
			}
			
			$sym = array();
			//$sym_c = array();
			$sym_s = array();
			$sym_tot = array();
			
			$cust = Doctrine_Query::create()
			->select('cs.*, cf.*')
			->from('FormBlockClientSymptoms cs')
			->leftJoin('cs.ContactForms cf')
			->where('cs.ipid = ?',$ipid )
			->andwhere('cs.isdelete = 0 ')                   //ISPC-2517 Lore 20.07.2020
			->andWhere('cs.contact_form_id = cf.id');
			//->orderBy('cf.date asc');
			if ( ! empty($sql_period_1)) {                   //ISPC-2517 Lore 20.07.2020
			    $cust->andWhere( $sql_period_1 , $sql_period_params);
			}
			$sym = $cust->fetchArray();
			
			foreach($sym as $ks => &$vs)
			{
				$vs['symptom_date'] = $vs['ContactForms']['date'];
				unset($vs['ContactForms']);
			}
			
			$cust_s = Doctrine_Query::create()
			->select('cs.*')
			->from('FormBlockClientSymptoms cs')
			->where('cs.ipid = ?',$ipid )
			->andwhere('cs.isdelete = 0 ');                  //ISPC-2517 Lore 20.07.2020
			if ( ! empty($sql_period_s)) {
				$cust_s->andWhere( $sql_period_s , $sql_period_params);
			}
			$sym_s = $cust_s->fetchArray();
			
			$sym_tot = array_merge($sym, $sym_s);
			
			array_multisort( array_column( $sym_tot, strtotime('symptom_date')), SORT_ASC, SORT_NUMERIC, $sym_tot );

			$color_mapping = array(
		        "0"=>"2bae2f",
		        
		        "1"=>"e9d149",
		        "2"=>"e9d149",
		        "3"=>"e9d149",
		        "4"=>"e9d149",
		        
		        "5"=>"ffa500",
		        "6"=>"ffa500",
		        "7"=>"ffa500",
		        
		        "8"=>"dc4646",
		        "9"=>"dc4646",
		        "10"=>"dc4646",
		    );
		  
		    if($sym_tot)
		    {
		        $client_sym_details_arr = ClientSymptoms::get_client_symptoms($clientid);

		        // add symptom names and colors
		        foreach($sym_tot as $key => &$symp)
		        {
		        	$sym_tot[$key]['entry_date'] = $symp['symptom_date'];
		            $sym_tot[$key]['symptom_name'] = $client_sym_details_arr[$symp['symptom_id']]['description'];
		            $sym_tot[$key]['symptom_value_color'] = $color_mapping[$symp['severity']];
		        }

		        return $sym_tot;
		    }
			
			
		}
		
	}

?>