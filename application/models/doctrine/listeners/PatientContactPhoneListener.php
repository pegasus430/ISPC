<?php

/**
 * PatientContactPhoneListener
 *
 *
 * @package    ISPC
 * @subpackage Application (2017-08-14)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 * ISPC-2550   Loredana+Ancuta 17.02.2020 - Added fax // Maria:: Migration ISPC to CISPC 08.08.2020
 */
class PatientContactPhoneListener extends Doctrine_Record_Listener 
{
	
	protected $_options = array();
	
	protected $_session_ipid = null;
	
	public function __construct(array $options)
	{
		$this->_options = $options;

		$last_ipid_session = new Zend_Session_Namespace('last_ipid');
		$this->_session_ipid = $last_ipid_session->ipid ;
	}	
	
	
	/**
	 * before insert we remove the last PatientLocation record from the contactphone
	 * 
	 * (non-PHPdoc)
	 * @see Doctrine_Record_Listener::preInsert()
	 */
	public function preInsert(Doctrine_Event $event)
	{
		$Invoker = $event->getInvoker();
		$ComponentName = $Invoker->getTable()->getComponentName();
	
		//TODO: ISPC-2121
// 		if ($ComponentName == 'LocationsStations') {
// 		    return; 
// 		}
// 		
		
		if( empty($Invoker->ipid)) {
			return;
		}
		
		if ( $ComponentName == "PatientLocation" ) {
			
		    
		    
			//get last location
			$last_location = PatientLocation::getIpidLastLocationDetails($Invoker->ipid);
			
			if ($last_location && $last_location['is_contact'] == 1) {
				
				//if last location is contact make this also as a contact   
				$Invoker->is_contact = 1;  
				
				//remove all last of type Locations from contact phone
				if ($last_location['ComponentName'] == "Locations") {
				
					$pcp_obj = new PatientContactphone();
					$phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndFromLocations($Invoker->ipid, "Locations", 'yes');
					if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
						$phone_obj->delete();
					}							
				}
				elseif ($last_location['ComponentName'] == "ContactPersonMaster") {
					// check if  is_contact  == 0  - daca nu este si pesoana ca contact, stergem,
					
					$contact_table_id = $last_location['id'];
					$cpm_obj = new ContactPersonMaster();
					$cpm_data = $cpm_obj ->getTable()->findByIpidAndId($Invoker->ipid, $contact_table_id);
					$cpm_data_array= $cpm_data->toArray();

					if($cpm_data_array['0']['is_contact'] == 0 ){
						$pcp_obj = new PatientContactphone();
						$phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndTableIdAndFromLocations($Invoker->ipid, "ContactPersonMaster", $contact_table_id, 'yes');
						if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
							$phone_obj->delete();
						}
					}
					// same for adding new record - do not  add if we have data
				}
				elseif ($last_location['ComponentName'] == "PatientMaster") {
				
				    $pm_obj = new PatientMaster();
				    $pm_data = $pm_obj->getTable()->findByIpidAndIsdelete($Invoker->ipid, 0);
				    $pm_data_array = $pm_data->toArray();
				    
				    if($pm_data_array['0']['is_contact'] == 0 ){
				       
    					$pcp_obj = new PatientContactphone();
    					$phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndFromLocations($Invoker->ipid, "PatientMaster", 'yes');
    					if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
    						$phone_obj->delete();
    					}	
				    }
				}
			}
		}
	}
	
	public function postInsert(Doctrine_Event $event)
	{
		
		$Invoker = $event->getInvoker();
		$ComponentName = $Invoker->getTable()->getComponentName();
		
		//TODO: ISPC-2121
// 		if ($ComponentName == 'LocationsStations') {
// 		    return; //ISPC-2121
// 		}
		
		if( (empty($this->_options['is_contact']) || ! isset($Invoker->{$this->_options['is_contact']}) || (int)$Invoker->{$this->_options['is_contact']} != 1) 
			&& ( $ComponentName != "PatientLocation" )) 
		{
			//this was NOT checked as 'ist die Kontakt-Telefonnummer'(or you don't have 0/1 as values for checkbox, or you are missing the is_contact)
			return;
		}
		
		if (empty($Invoker->ipid)
		    && ! empty($this->_session_ipid)
		    && property_exists($Invoker, 'ipid'))
		{
		    $Invoker->ipid = $this->_session_ipid;
		}
				
		if( empty($Invoker->ipid)) {
			//property_exists will not cut it for my needs
			return;
		}		
		
		
		$Table_id = $Invoker->id;

		$contact_phone = (! empty($this->_options['phone']) && isset($Invoker->{$this->_options['phone']})) ? $Invoker->{$this->_options['phone']} : null;
		$contact_mobile = (! empty($this->_options['mobile']) &&  isset($Invoker->{$this->_options['mobile']})) ? $Invoker->{$this->_options['mobile']} : null;
		$contact_fax = (! empty($this->_options['fax']) &&  isset($Invoker->{$this->_options['fax']})) ? $Invoker->{$this->_options['fax']} : null;
		$contact_last_name	= (!empty($this->_options['last_name']) &&  isset($Invoker->{$this->_options['last_name']})) ? $Invoker->{$this->_options['last_name']} : null;
		$contact_first_name	= (!empty($this->_options['first_name']) &&  isset($Invoker->{$this->_options['first_name']})) ? $Invoker->{$this->_options['first_name']} : null;
		$contact_other_name	= (!empty($this->_options['other_name']) &&  isset($Invoker->{$this->_options['other_name']})) ? $Invoker->{$this->_options['other_name']} : null;
		
		$contact_extra = ! isset($this->_options['extra']) || empty($this->_options['extra']) ? null : [
		    'comment' =>  isset($Invoker->{$this->_options['extra']['comment']}) ? $Invoker->{$this->_options['extra']['comment']} : null,
		    'street' => isset($Invoker->{$this->_options['extra']['street']}) ? $Invoker->{$this->_options['extra']['street']} : null,
		    'city' => isset($Invoker->{$this->_options['extra']['city']}) ? $Invoker->{$this->_options['extra']['city']} : null,
		    'zip' => isset($Invoker->{$this->_options['extra']['zip']}) ? $Invoker->{$this->_options['extra']['zip']} : null,
		];

		
		$contact_from_locations = 'no';
		
		switch($ComponentName) {
				
			case "PatientMaster":{
				$decrypted = Pms_CommonData::aesDecryptMultiple(array(
						'contact_phone' => $contact_phone,
						'contact_mobile' => $contact_mobile,
						'contact_last_name' => $contact_last_name,
						'contact_first_name' => $contact_first_name,
						'contact_other_name' => $contact_other_name,
				));
				$contact_phone		= !empty($decrypted ['contact_phone']) ? $decrypted ['contact_phone'] : null;
				$contact_mobile		= !empty($decrypted ['contact_mobile']) ? $decrypted ['contact_mobile'] : null;
				$contact_last_name	= !empty($decrypted ['contact_last_name']) ? $decrypted ['contact_last_name'] : null;
				$contact_first_name	= !empty($decrypted ['contact_first_name']) ? $decrypted ['contact_first_name'] : null;
				$contact_other_name	= !empty($decrypted ['contact_other_name']) ? $decrypted ['contact_other_name'] : $contact_other_name;
				
			}
			break;
			case "PatientLocation":{

				$last_location = PatientLocation::getIpidLastLocationDetails($Invoker->ipid);				

				if ($last_location && $last_location['is_contact'] == 1) {
					
					$ComponentName		= $last_location['ComponentName'];
					$Table_id			= $last_location['id'];
						
					$contact_phone		= $last_location['phone'];
					$contact_mobile		= $last_location['mobile'];
					$contact_fax		= $last_location['fax'];             //ISPC-2550 Lore 17.02.2020
					$contact_last_name	= $last_location['last_name'];
					$contact_first_name	= $last_location['first_name'];
					$contact_other_name	= $last_location['other_name'];
					
					$contact_from_locations = 'yes';
					
					//now update the invoker's is_contact
					$Invoker->is_contact =  1; // we add this as contactphone only if the previous location was also checked as contact
// 					$Invoker->save();
					
				} else {
					return; // exit the insert listener, the previous patientLocation was not checked as contactphone
				}
				
			}
			break;
			case "ContactPersonMaster":{
				$decrypted = Pms_CommonData::aesDecryptMultiple(array(
						'contact_phone' => $contact_phone,
						'contact_mobile' => $contact_mobile,
						'contact_last_name' => $contact_last_name,
						'contact_first_name' => $contact_first_name,
						'contact_other_name' => $contact_other_name,
						'contact_extra_comment' => isset($contact_extra['comment']) ? $contact_extra['comment'] : null,
						'contact_extra_street' => isset($contact_extra['street']) ? $contact_extra['street'] : null,
						'contact_extra_city' => isset($contact_extra['city']) ? $contact_extra['city'] : null,
						'contact_extra_zip' => isset($contact_extra['zip']) ? $contact_extra['zip'] : null,
				));
				$contact_phone		= !empty($decrypted ['contact_phone']) ? $decrypted ['contact_phone'] : null;
				$contact_mobile		= !empty($decrypted ['contact_mobile']) ? $decrypted ['contact_mobile'] : null;
				$contact_last_name	= !empty($decrypted ['contact_last_name']) ? $decrypted ['contact_last_name'] : null;
				$contact_first_name	= !empty($decrypted ['contact_first_name']) ? $decrypted ['contact_first_name'] : null;
				$contact_other_name	= !empty($decrypted ['contact_other_name']) ? $decrypted ['contact_other_name'] : $contact_other_name;
				$contact_extra = empty($contact_extra) ? null : serialize([
				    'comment' =>  ! empty($decrypted ['contact_extra_comment']) ? $decrypted ['contact_extra_comment'] : null,
				    'street' =>  ! empty($decrypted ['contact_extra_street']) ? $decrypted ['contact_extra_street'] : null,
				    'city' =>  ! empty($decrypted ['contact_extra_city']) ? $decrypted ['contact_extra_city'] : null,
				    'zip' =>  ! empty($decrypted ['contact_extra_zip']) ? $decrypted ['contact_extra_zip'] : null,
				]); 
				
				if ( (int)$contact_other_name > 0) {
					$familydegree = new FamilyDegree();
					$degreearray = $familydegree->get_relation($contact_other_name);
					$contact_other_name = $degreearray[0]['family_degree'];
				} else {
					$contact_other_name = null;
				}
				
			}
		}

		
		//update the patient_contactphone
		$pcp_obj = new PatientContactphone();
		$phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndTableIdAndFromLocations($Invoker->ipid, $ComponentName, $Table_id, $contact_from_locations);
		if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
		    
		    foreach ($phone_obj->getIterator()  as $one_phone) {
		        $one_phone->phone         = $contact_phone;
		        $one_phone->mobile        = $contact_mobile;
		        $one_phone->fax           = $contact_fax;         //ISPC-2550 Lore 17.02.2020
		        $one_phone->last_name     = $contact_last_name;
		        $one_phone->first_name    = $contact_first_name;
		        $one_phone->other_name    = $contact_other_name ;
		        $one_phone->extra         = $contact_extra ;
		        $one_phone->isdelete      = $Invoker->isdelete == 1 ? 1 : !($Invoker->{$this->_options['is_contact']});
		    }
		    $phone_obj->save();
		} else {
		
    		$pcp_obj->set_new_record(array(
    				'ipid'			=> $Invoker->ipid,
    				'parent_table'	=> $ComponentName,
    				'table_id'		=> $Table_id,
    				'from_locations'=> $contact_from_locations,
    				'phone'			=> $contact_phone,
    				'mobile'		=> $contact_mobile,
    		        'fax'		    => $contact_fax,                 //ISPC-2550 Lore 17.02.2020
    				'last_name'		=> $contact_last_name,
    				'first_name'	=> $contact_first_name,
    				'other_name'	=> $contact_other_name,
    				'extra'         => $contact_extra,
    				'isdelete'		=> $Invoker->isdelete == 1 ? 1 : !($Invoker->{$this->_options['is_contact']}),
    				//empty(isdelete) add this just in case you want to insert as deleted maybe
    		));
		}
	}
		
	
	public function postUpdate(Doctrine_Event $event)
	{
		
		$Invoker = $event->getInvoker();
		
		$ComponentName = $Invoker->getTable()->getComponentName();

		if (empty($this->_options['is_contact']) 
		    || ! isset($Invoker->{$this->_options['is_contact']}) 
		    || is_null($Invoker->{$this->_options['is_contact']})) 
		{
		    return;
		}
		
		if (empty($Invoker->ipid) 
		    && ! empty($this->_session_ipid)
	        && property_exists($Invoker, 'ipid'))
		{
		    $Invoker->ipid = $this->_session_ipid;		    
		}
		
		if (empty($Invoker->ipid) 
		    && $ComponentName != "Locations") 
		{
			return;
		}
		
		if (isset($Invoker->is_contact_Location) 
		    && $ComponentName != "PatientLocation") 
		{
			return;
		}
		
		$Table_id = $Invoker->id;
		
		$contact_phone = (! empty($this->_options['phone']) && isset($Invoker->{$this->_options['phone']})) ? $Invoker->{$this->_options['phone']} : null;
		$contact_mobile = (! empty($this->_options['mobile']) &&  isset($Invoker->{$this->_options['mobile']})) ? $Invoker->{$this->_options['mobile']} : null;
		$contact_fax = (! empty($this->_options['fax']) &&  isset($Invoker->{$this->_options['fax']})) ? $Invoker->{$this->_options['fax']} : null;   //ISPC-2550 Lore 17.02.2020
		$contact_last_name	= (!empty($this->_options['last_name']) &&  isset($Invoker->{$this->_options['last_name']}))  ? $Invoker->{$this->_options['last_name']} : null;
		$contact_first_name	= (!empty($this->_options['first_name']) &&  isset($Invoker->{$this->_options['first_name']}))  ? $Invoker->{$this->_options['first_name']} : null;
		$contact_other_name	= (!empty($this->_options['other_name']) &&  isset($Invoker->{$this->_options['other_name']}))  ? $Invoker->{$this->_options['other_name']} : null;
		
		$contact_from_locations = 'no';
		
		$contact_extra = ! isset($this->_options['extra']) || empty($this->_options['extra']) ? null : [
		    'comment' =>  isset($Invoker->{$this->_options['extra']['comment']}) ? $Invoker->{$this->_options['extra']['comment']} : null,
		    'street' => isset($Invoker->{$this->_options['extra']['street']}) ? $Invoker->{$this->_options['extra']['street']} : null,
		    'city' => isset($Invoker->{$this->_options['extra']['city']}) ? $Invoker->{$this->_options['extra']['city']} : null,
		    'zip' => isset($Invoker->{$this->_options['extra']['zip']}) ? $Invoker->{$this->_options['extra']['zip']} : null,
	    ];
		
		
		$initial_ComponentName = $ComponentName;
		switch($ComponentName) {
			
			case "ContactPersonMaster":{
			
				if ( (int)$contact_other_name > 0) {
					$familydegree = new FamilyDegree();
					$degreearray = $familydegree->get_relation($contact_other_name);
					$contact_other_name = $degreearray[0]['family_degree'];
				} else {
					$contact_other_name = null;
				}
				
				//do not double-encrypt, for now use decrypt
				$decrypted = Pms_CommonData::aesDecryptMultiple(array(
						'contact_phone' => $contact_phone,
						'contact_mobile' => $contact_mobile,
						'contact_last_name' => $contact_last_name,
						'contact_first_name' => $contact_first_name,
    				    //'contact_other_name' => $contact_other_name,// TODO-3794 Ancuta 05.04.2021
    				    'contact_extra_comment' => isset($contact_extra['comment']) ? $contact_extra['comment'] : null,
    				    'contact_extra_street' => isset($contact_extra['street']) ? $contact_extra['street'] : null,
    				    'contact_extra_city' => isset($contact_extra['city']) ? $contact_extra['city'] : null,
    				    'contact_extra_zip' => isset($contact_extra['zip']) ? $contact_extra['zip'] : null,
				));
				$contact_phone		= !empty($decrypted ['contact_phone']) ? $decrypted ['contact_phone'] : null;
				$contact_mobile		= !empty($decrypted ['contact_mobile']) ? $decrypted ['contact_mobile'] : null;
				$contact_last_name	= !empty($decrypted ['contact_last_name']) ? $decrypted ['contact_last_name'] : null;
				$contact_first_name	= !empty($decrypted ['contact_first_name']) ? $decrypted ['contact_first_name'] : null;
				$contact_other_name	= !empty($decrypted ['contact_other_name']) ? $decrypted ['contact_other_name'] : $contact_other_name;
				$contact_extra = empty($contact_extra) ? null : serialize([
				    'comment' =>  ! empty($decrypted ['contact_extra_comment']) ? $decrypted ['contact_extra_comment'] : null,
				    'street' =>  ! empty($decrypted ['contact_extra_street']) ? $decrypted ['contact_extra_street'] : null,
				    'city' =>  ! empty($decrypted ['contact_extra_city']) ? $decrypted ['contact_extra_city'] : null,
				    'zip' =>  ! empty($decrypted ['contact_extra_zip']) ? $decrypted ['contact_extra_zip'] : null,
				]);
			}
			break;
			
			case "PatientLocation": {
			    			    
				$last_location = PatientLocation::getIpidLastLocationDetails($Invoker->ipid);
				
				$contact_from_locations =  'yes';
				
				//should we first delete all other locations saved as contactphone?
				if (empty($last_location) 
				    || ($last_location['PatientLocation_id'] == $Invoker->id)  ) 
				{
				    
    				$pcp_obj = new PatientContactphone();
    				$phone_obj = $pcp_obj->getTable()->findByIpidAndFromLocations($Invoker->ipid, $contact_from_locations);
    				if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
    				    $phone_obj->delete();
    				}
				}
				
				if ($last_location) {
				    
    				//if ID of updated patientLocation != ID of last location, you have updated an older location of this patient, exit listener
    				if ($last_location['PatientLocation_id'] != $Invoker->id) { 	
    				    return;			    
    				}
					
					$ComponentName		= $last_location['ComponentName'];
					$Table_id			= $last_location['id'];
							
					$contact_phone		= $last_location['phone'];
					$contact_mobile		= $last_location['mobile'];
					$contact_fax		= $last_location['fax'];                 //ISPC-2550 Lore 17.02.2020
					$contact_last_name	= $last_location['last_name'];
					$contact_first_name	= $last_location['first_name'];
					$contact_other_name	= $last_location['other_name'];
					
				}
				
			}
			break;
			
			
			case "Locations":
			case "PatientMaster":{
				
				//do not double-encrypt, for now use decrypt
				$decrypted = Pms_CommonData::aesDecryptMultiple(array(
						'contact_phone' => $contact_phone,
						'contact_mobile' => $contact_mobile,
						'contact_last_name' => $contact_last_name,
						'contact_first_name' => $contact_first_name,
						'contact_other_name' => $contact_other_name,
				));
				$contact_phone		= !empty($decrypted ['contact_phone']) ? $decrypted ['contact_phone'] : null;
				$contact_mobile		= !empty($decrypted ['contact_mobile']) ? $decrypted ['contact_mobile'] : null;
				$contact_last_name	= !empty($decrypted ['contact_last_name']) ? $decrypted ['contact_last_name'] : null;
				$contact_first_name	= !empty($decrypted ['contact_first_name']) ? $decrypted ['contact_first_name'] : null;
				$contact_other_name	= !empty($decrypted ['contact_other_name']) ? $decrypted ['contact_other_name'] : null;
				
			}
			break;
		}
		
		$pcp_obj = new PatientContactphone(); //table where we keep the patient's call-contacts
		$phone_obj = null;
		
		switch($initial_ComponentName) {
		    
		    case "Locations":
		        // when you update the client locations list, you affect all patients
		        $phone_obj = $pcp_obj->getTable()->findByParentTableAndTableId($ComponentName, $Table_id); 
	        break;
	        
		    case 'PatientLocation':
		        
		        //if you update the PatientLocations, this should NOT affect others as contactphone
		        $phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndTableIdAndFromLocations($Invoker->ipid, $ComponentName, $Table_id, 'yes');
		        
	        break;
	        
		    default :
		         
	            //deleted contactphones will affect PatientLocations as contactphone also
		        $phone_obj = $pcp_obj->getTable()->findByIpidAndParentTableAndTableId($Invoker->ipid, $ComponentName, $Table_id);
		        
	        break;
		        
		}
		
	    
		if( $Invoker->isdelete == 1 ) {
			//deleted
			if( $phone_obj instanceof Doctrine_Collection && ($phone_obj->count() > 0) ){
				$phone_obj->delete();
			}
				
		} else {

		    $updated_consignee =  false;
		    
			if ($phone_obj instanceof Doctrine_Collection 
			    && $phone_obj->count() > 0 ) 
			{
			    
				//update
				foreach ($phone_obj as &$row ) {
					
					$row->phone			= $contact_phone;
					$row->mobile		= $contact_mobile;
					$row->fax		    = $contact_fax;     //ISPC-2550 Lore 17.02.2020
					$row->first_name	= $contact_first_name;
					$row->last_name		= $contact_last_name;
					$row->other_name	= $contact_other_name;
					$row->extra         = $contact_extra;
					
					if ($row['from_locations'] == $contact_from_locations ) {

					    $row->isdelete     = (!empty($this->_options['is_contact']) && isset($Invoker->{$this->_options['is_contact']})) ? !($Invoker->{$this->_options['is_contact']}) : $phone_obj->isdelete;					
					    
					    $updated_consignee   = true;					    
					}
					
				}
				
				$phone_obj->save();

			}

			
			if (( ! $phone_obj instanceof Doctrine_Collection || $phone_obj->count() == 0 || ! $updated_consignee) 
			    && ( ! empty($this->_options['is_contact']) && $Invoker->{$this->_options['is_contact']})) 
			{
			    //insert
				$pcp_obj = new PatientContactphone();
				$pcp_obj->set_new_record(array(
						'ipid'			=> $Invoker->ipid,
						'parent_table'	=> $ComponentName,
						'table_id'		=> $Table_id,
				        'from_locations'=> $contact_from_locations,
						'phone'			=> $contact_phone,
						'mobile'		=> $contact_mobile,
						'fax'		    => $contact_fax,           //ISPC-2550 Lore 17.02.2020
						'first_name'	=> $contact_first_name,
						'last_name'		=> $contact_last_name,
						'other_name'	=> $contact_other_name,
						'extra'         => $contact_extra,
						'isdelete'		=> (!empty($this->_options['is_contact'])  && isset($Invoker->{$this->_options['is_contact']})) ? !($Invoker->{$this->_options['is_contact']}) : 0,
				));

				
			}
			
		}
				
	}

	

	
	public function postDelete(Doctrine_Event $event)
	{
	    
	    if (! empty($this->_options['is_contact'])
	        && isset($event->getInvoker()->{$this->_options['is_contact']})
	        && ! is_null($event->getInvoker()->{$this->_options['is_contact']}))
	    {
	        $event->getInvoker()->{$this->_options['is_contact']} = 0;
	    }
	    
		//hard postDelete in one of the tables
		self::postUpdate($event);
	}

	

}
?>