<?php

	Doctrine_Manager::getInstance()->bindComponent('ContactFormServiceEntry', 'MDAT');
	
	class ContactFormServiceEntry extends BaseContactFormsServiceEntry{
		
		public function getContactFormServiceEntry($contact_form_id , $ipid)
		{
			
			$services = Doctrine_Query::create()
			->select ('*')
			->from('ContactFormServiceEntry cs')
			->where('cs.contact_form_id = "' . $contact_form_id . '"')
			->andWhere('cs.ipid = "' . $ipid . '"');
			//->andWhere('cs.isdelete = "0"');
			$servicesarr = $services->fetchArray();
			
			
			if(sizeof($servicesarr) > 0)
			{
				foreach($servicesarr as $serv)
				{
					$newservarr[$serv['service_entry_id']] = $serv;
				}
				return $newservarr;
			}
		}
		public function getLastServiceEntry($ipid)
		{
			
			$last_services = Doctrine_Query::create()
			->select ('*')
			->from('ContactFormServiceEntry cs')
			->where('cs.ipid = "' . $ipid . '"')
			->andWhere('cs.isdelete = "0"')
			->orderBy('id');
			$last_services_arr = $last_services->fetchArray();
			
			foreach($last_services_arr as $k => $sval)
			{
				$lastserv[$sval['service_entry_id']] = $sval;
			}
			return $lastserv;
		}
	}