<?php

Doctrine_Manager::getInstance()->bindComponent('HealthInsurance', 'SYSDAT');

class HealthInsurance extends BaseHealthInsurance{

    /**
     * @claudiu 23.11.2017
     * @return array for input[name=insurance_options]
     */
    public static function getDefaultInsuranceOptions(){
        $Tr = new Zend_View_Helper_Translate();
        return array(
            '' =>  $Tr->translate('Select option'),
            'privatepatient' =>  $Tr->translate('privatepatient'),
            'direct_billing' =>  $Tr->translate('direct_billing'),
            'bg_patient' =>  $Tr->translate('bg_patient'),
        );
    }
    
    public static function getDefaultValid(){
        $Tr = new Zend_View_Helper_Translate();
        return array(
            '1' =>  $Tr->translate('yesconfirm'),
            '2' =>  $Tr->translate('noconfirm'),
        );
    }
    
    
    
	function getCompanyinfofromId($id)
	{
		$drop = Doctrine_Query::create()
			   ->select("*")
			   ->from('HealthInsurance')
			   ->where("id='" . $id . "'");
		$droparray = $drop->fetchArray();

		return $droparray;
	}

	function get_multiple_healthinsurances ( $ids )
	{
		if (count($ids) == '0')
		{
			$ids[] = '99999999999';
		}

		$drop = Doctrine_Query::create()
			->select("*")
			->from('HealthInsurance')
			->whereIn("id", $ids);
		$droparray = $drop->fetchArray();
		
		if ($droparray)
		{
			foreach ($droparray as $k_res => $v_res)
			{
				$res_hi_master[$v_res['id']] = $v_res;
			}

			return $res_hi_master;
		}
	}

	public function getHealthInsuraces($ipid = false, $letter = false, $keyword = false, $arrayids = false)
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if ($ipid != false)
		{
			$patienthealth = new PatientHealthInsurance();
			$patient_health_insurance = $patienthealth->getPatientHealthInsurance($ipid);

			if (count($patient_health_insurance) > 0)
			{
				foreach ($patient_health_insurance as $keyph => $valueph)
				{
					$pharry[$keyph] = $valueph['companyid'];
				}
				$ids = implode(",", $pharry);

				$ipid_sql .= " AND id IN (" . $ids . ")";
			}
			else
			{
				$ipid_sql .= " AND id IN (0)";
			}
		}
		else
		{
			$ipid_sql = " AND extra=0 AND isdelete =0 AND valid_till = '0000-00-00'";
		}

		if ($keyword != false)
		{
			$keyword_sql = " AND name like '%" . ($keyword) . "%'";
		}

		if ($letter != false)
		{
			$keyword_sql = " AND name like '" . ($letter) . "%'";
		}

		if ($arrayids != false)
		{
			$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
			$ipid_sql = '';
		}

		$drop = Doctrine_Query::create()
			   ->select('*')
			   ->from('HealthInsurance')
			   ->where("name != '' and extra='0' and isdelete='0' and onlyclients='1' and clientid='".$clientid."'" . $ipid_sql . $keyword_sql . $array_sql); //only per client health insurance
		$droparray = $drop->fetchArray();

		return $droparray;
	}

	public function clone_record($id, $target_client)
	{
		$health_insu = $this->getCompanyinfofromId($id);

		//clone health insurance if is client specific only, else return the id to be added in patient health insurance
		if($health_insu[0]['clientid'] != '0')
		{
			$hinsu = new HealthInsurance();
			$hinsu->clientid = $target_client;
			$hinsu->name = $health_insu[0]['name'];
			$hinsu->name2 = $health_insu[0]['name2'];
			$hinsu->street1 = $health_insu[0]['street1'];
			$hinsu->street2 = $health_insu[0]['street2'];
			$hinsu->zip = $health_insu[0]['zip'];
			$hinsu->city = $health_insu[0]['city'];
			$hinsu->phone = $health_insu[0]['phone'];
			$hinsu->phone2 = $health_insu[0]['phone2'];
			$hinsu->phonefax = $health_insu[0]['phonefax'];
			$hinsu->post_office_box = $health_insu[0]['post_office_box'];
			$hinsu->post_office_box_location = $health_insu[0]['post_office_box_location'];
			$hinsu->email = $health_insu[0]['email'];
			$hinsu->zip = $health_insu[0]['zip'];
			$hinsu->zip_mailbox = $health_insu[0]['zip_mailbox'];
			$hinsu->kvnumber = $health_insu[0]['kvnumber'];
			$hinsu->iknumber = $health_insu[0]['iknumber'];
			$hinsu->debtor_number = $health_insu[0]['debtor_number'];
			$hinsu->comments = $health_insu[0]['comments'];
			$hinsu->valid_from = $health_insu[0]['valid_from'];
			$hinsu->valid_till = $health_insu[0]['valid_till'];
			$hinsu->extra = '1';
			$hinsu->onlyclients = '1';
			$hinsu->save();

			if($hinsu)
			{
				return $hinsu->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $id;
		}
	}
	
	
	/**
	 * @claudiu
	 * @param string $fieldName
	 * @param unknown $value
	 * @param array $data
	 * @param unknown $hydrationMode
	 * @return Doctrine_Record
	 */
	public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
	
	        if ($fieldName != $this->getTable()->getIdentifier()) {
	            $entity = $this->getTable()->create(array( $fieldName => $value));
	        } else {
	            $entity = $this->getTable()->create();
	        }
	    }
	
// 	    $this->_encryptData($data);
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
	
	
	
	
    	
    /**
     * @author Ancuta 21.11.2019
     * Copy of fn PpunIpid->generate_patient_ppun()
     * ISPC2-452
     * @param string $client
     * @return unknown|multitype:string boolean |boolean
     */
	public function generate_hi_debitor_number(  $client = false )
	{
        if (empty($client)) {
            return false;
        }
        
        $is_private = true;
        $post_data = array();
        
        // get hi_debitor client range::returns hi_debitor_start value
        $settings_hi_debNr_data = self::get_hi_debitor_start_client_settings($client);
        
        // get last generated debitor number of client
        $existing_hi_debNr_data = self::get_hi_debitor_last_generated($client);
        
        
        // compare settings value with existing highest value
        if ($existing_hi_debNr_data === false) {
            $base_nr = $settings_hi_debNr_data;
        } else 
            if ($existing_hi_debNr_data >= $settings_hi_debNr_data) {
                $base_nr = $existing_hi_debNr_data;
                $base_nr ++;
            } else {
                $base_nr = $settings_hi_debNr_data;
                $base_nr ++;
            }
        
        $post_data['hi_debitor_number'] = $base_nr;
        $post_data['clientid'] = $client;
        
        return $post_data;
    }
	/**
	 * @author Ancuta 
	 * ISPC-2452
	 * @param string $client
	 * @return boolean
	 */
	public function get_hi_debitor_start_client_settings($client = false)
	{
	    //get client ppun range settings!
	    if($client)
	    {
	        $client_data = Client::getClientDataByid($client);
	
	        return $client_data[0]['hi_debitor_start'];
	    }
	    else
	    {
	        return false;
	    }
	}
	/**
	 * @author Ancuta
	 * ISPC-2452
	 * @param string $client
	 * @return boolean
	 * TODO-3029 Ancuta 27.03.2020 - add - isdelete = 0  condition 
	 */
	public function get_hi_debitor_last_generated($client = false)
	{
 
	    //get last generated ppun of client
	    if($client)
	    {
	        $doc = Doctrine_Query::create()
	        ->select('*, cast(debtor_number as decimal) as dec_debtor_number')
	        ->from('HealthInsurance')
	        ->where('clientid = ?',$client)
	        ->andWhere("extra = ?", 0)
    		->andWhere("onlyclients = ?",1)
    		->andWhere('debtor_number REGEXP "^[0-9]+$"')
    		->andWhere("isdelete = ?",0)
	        ->orderBy('dec_debtor_number DESC')
	        ->limit(1);
	        $doc_res = $doc->fetchArray();
	
	        if($doc_res)
	        {
	            return $doc_res[0]['debtor_number'];
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
	/**
	 * @author Ancuta
	 * @param unknown $debitor
	 * @param unknown $client
	 * TODO-2865 Ancuta 31.01.2020 - Edited, added condition isdelete 0
	 */
	
	 public  function check_hi_debitor_exists($client,$debitor,$company_id = false){

	     if(empty($client)){
	         return;
	     }
	     
	     if(!empty($debitor)){
	         
    	     $doc = Doctrine_Query::create()
    	     ->select('*')
    	     ->from('HealthInsurance')
    	     ->where('clientid = ?',$client)
    	     ->andWhere('isdelete = 0')
    	     ->andWhere('debtor_number=?',$debitor);
    	     if(!empty($company_id)){
    	       $doc->andWhere('id != ?',$company_id);
    	     }
    	     $doc->orderBy('debtor_number DESC')
    	     ->limit('1');
    	     $doc_res = $doc->fetchArray();
    	     
    	     if($doc_res){
    	         return true;
    	     } else {
    	         return false;
    	     }
	     }   
	     else
	     {
  	         return false;
	     } 
	 }
}
?>