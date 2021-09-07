<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanCocktails', 'MDAT');

	class PatientDrugPlanCocktails extends BasePatientDrugPlanCocktails {

		public function getDrugCocktails($cocktails_array = array())
		{//Changes for ISPC-1848 F
			
			if (empty($cocktails_array) || !is_array($cocktails_array)) {
				return $cocktails_array;
			}
// 			$cocktails_array[] = "9999999999";
//		$cIds = implode(",", $cocktails_array);

			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlanCocktails')
				->whereIn("id", $cocktails_array)
				->orderBy("id ASC");
			$drugCocktails = $drugsc->fetchArray();

			if(count($drugCocktails) > 0)
			{
				foreach($drugCocktails as $cocktail)
				{
					$drugsCocktailsFinal[$cocktail['id']] = $cocktail;
				}
			}

			return $drugsCocktailsFinal;
		}

		public function countDrugsPerCocktail($cocktailids)
		{
			if(count($cocktailids) == 0)
			{
				$cocktailids[] = '999999999';
			}

			$drugsc = Doctrine_Query::create()
				->select("*")
				->from('PatientDrugPlan')
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
				->from('PatientDrugPlanCocktails')
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
					$patient_drug_c = new PatientDrugPlanCocktails();
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