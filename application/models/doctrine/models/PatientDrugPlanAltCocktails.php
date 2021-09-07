<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanAltCocktails', 'MDAT');

	class PatientDrugPlanAltCocktails extends BasePatientDrugPlanAltCocktails {

		public function get_drug_cocktails_alt($ipid = '', $cocktails_array = array(), $full_details = true)
		{//Changes for ISPC-1848 F
		    if (empty($cocktails_array) || ! is_array($cocktails_array)) {
		        return;
		    }

			$drugsc = Doctrine_Query::create()
// 				->select("*")
				->from('PatientDrugPlanAltCocktails')
				->whereIn('drugplan_cocktailid', $cocktails_array)
				->andWhere('ipid = ?' , $ipid)
				->andWhere('declined != 1')
				->andWhere('inactive = 0')
				->andWhere('approved = 0')
				->andWhere('isdelete = 0')
				->orderBy('id ASC');
			
			if($full_details){
			    $drugsc->select("*");
			} else {
			    $drugsc->select("id, drugplan_cocktailid, change_source");
			}
			
 			$drugcocktails = $drugsc->fetchArray();

			
			$drugscocktailsfinal = array();
			if(! empty($drugcocktails))
			{
			    
				foreach($drugcocktails as $cocktail)
				{
				    if($full_details){
				        
				        if ($cocktail['change_source'] == 'offline'){
				            $drugscocktailsfinal['offline'][$cocktail['drugplan_cocktailid']][] = $cocktail;
				        } else { 
    					   $drugscocktailsfinal['online'][$cocktail['drugplan_cocktailid']] = $cocktail;
				        }
				    } 
				    else
				    {
    					$drugscocktailsfinal[] = $cocktail['id'];
				        
				    }
				}
			}
			return $drugscocktailsfinal;
		}

		public function get_drug_cocktail_details_alt($ipid='', $cocktail = 0, $alt_id = 0)
		{
			//$cocktails_array[] = "9999999999";

			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlanAltCocktails')
				->where('ipid = ?', $ipid);
				if($cocktail){
				    $drugsc->andWhere('drugplan_cocktailid = ? ', $cocktail);
				}
				if($alt_id){
				    $drugsc->andWhere('id = ?',$alt_id);
				}
				$drugsc->andWhere('declined != 1')
				->andWhere('isdelete = 0')
				->andWhere('inactive = 0')
				->andWhere('approved = 0')
				->orderBy('id ASC');
			$drugcocktails = $drugsc->fetchArray();

			if(count($drugcocktails) > 0)
			{
				foreach($drugcocktails as $cocktail)
				{
					$drugscocktailsfinal[$cocktail['drugplan_cocktailid']] = $cocktail;
				}
			}

			return $drugscocktailsfinal;
		}
		
		public function get_declined_drug_cocktails_alt($ipid ='', $cocktails_array = array(), $full_details = true)
		{
		    if (empty($cocktails_array) || ! is_array($cocktails_array)) {
		        return;
		    }

			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlanAltCocktails')
				->whereIn('drugplan_cocktailid', $cocktails_array)
				->andWhere('ipid = ?', $ipid)
				->andWhere('declined = 1')
				->andWhere('inactive = 0')
				->orderBy('id ASC');
			$drugcocktails = $drugsc->fetchArray();

			if(count($drugcocktails) > 0)
			{
   				foreach($drugcocktails as $cocktail)
   				{
    			    if($full_details)
    			    {
    					$drugscocktailsfinal[$cocktail['drugplan_cocktailid']] = $cocktail;
    	   		    } 
    			    else
    			    {
       					$drugscocktailsfinal[] = $cocktail['drugplan_cocktailid'];
    			    }
			    }
			}
			return $drugscocktailsfinal;
		}
		
		public function get_declined_drug_cocktails_alt_offline($ipid ='', $cocktails_array = array(), $full_details = true)
		{
		    if (empty($cocktails_array) || ! is_array($cocktails_array)) {
		        return;
		    }
		
		    $drugsc = Doctrine_Query::create()
// 		    ->select("*")
		    ->from('PatientDrugPlanAltCocktails')
		    ->whereIn('drugplan_cocktailid', $cocktails_array)
		    ->andWhere('ipid = ?', $ipid)
		    ->andWhere('declined = 1')
		    ->andWhere('inactive = 0')
		    ->andWhere("change_source = 'offline'")
		    ->orderBy('id ASC');
		    
		    if($full_details){
		        $drugsc->select("*");
		    } else {
		        $drugsc->select("id, drugplan_cocktailid, change_source");
		    }		    
		    $drugcocktails = $drugsc->fetchArray();
		
		    if(count($drugcocktails) > 0)
		    {
		        foreach($drugcocktails as $cocktail)
		        {
		            if($full_details)
		            {
		                $drugscocktailsfinal[$cocktail['drugplan_cocktailid']] = $cocktail;
		            }
		            else
		            {
		                $drugscocktailsfinal[] = $cocktail['drugplan_cocktailid'];
		            }
		        }
		    }
		    
		    return $drugscocktailsfinal;
		}
		
		

		public function countDrugsPerCocktail($cocktailids)
		{
			if(count($cocktailids) == 0)
			{
				$cocktailids[] = '999999999';
			}

			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlanAlt')
				->whereIn("cocktailid", $cocktailids)
				->andWhere('isdelete = 0');
			$drugCocktails = $drugsc->fetchArray();
			foreach($drugCocktails as $drug)
			{
				$drugC[$drug['cocktailid']][] = $drug['id'];
			}

			return $drugC;
		}

		public function getCocktails($ipid)
		{
			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlanAltCocktails')
				->where("ipid = '" . $ipid . "'")
				->orderBy("id ASC");
			$drugCocktails = $drugsc->fetchArray();

			if($drugCocktails)
			{
				return $drugCocktails;
			}
		}

		public function clone_record($ipid, $target_ipid, $target_client)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$cocktails = $this->getCocktails($ipid);

			if($cocktails)
			{
				foreach($cocktails as $cocktail)
				{
					$patient_drug_c = new PatientDrugPlanAltCocktails();
					$patient_drug_c->userid = $logininfo->userid;
					$patient_drug_c->clientid = $target_client;
					$patient_drug_c->ipid = $target_ipid;
					$patient_drug_c->description = $cocktail['description'];
					$patient_drug_c->bolus = $cocktail['bolus'];
					$patient_drug_c->max_bolus = $cocktail['max_bolus'];
					$patient_drug_c->flussrate = $cocktail['flussrate'];
					$patient_drug_c->flussrate_type = $cocktail['flussrate_type'];     //ISPC-2684 Lore 08.10.2020
					$patient_drug_c->sperrzeit = $cocktail['sperrzeit'];
					$patient_drug_c->isdelete = $cocktail['isdelete'];
					$patient_drug_c->save();

					$return_data[$cocktail['id']] = $patient_drug_c->id;
				}

				return $return_data;
			}
			else
			{
				return false;
			}
		}

	}

?>