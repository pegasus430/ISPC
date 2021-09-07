<?php
	Doctrine_Manager::getInstance ()->bindComponent ( 'ContactFormsSympDetails', 'MDAT' );
	
	class ContactFormsSympDetails extends BaseContactFormsSympDetails {
	
		public function get_contact_form_symptomatology_details($contact_form_id, $labels = false){
			
			$symp_zapv_details = new SymptomatologyZapvDetails();
			$zapv_details_items = $symp_zapv_details->getSymptpomatologyZapvItems();
			
			$symps = Doctrine_Query::create()
			->select('det.*,sym.*')
			->from('ContactFormsSympDetails det')
			->where('contact_form_id =? ', $contact_form_id);
			$symps->leftJoin('det.ContactFormsSymp sym');
			$symps->andWhere('det.entry_id = sym.id');
			$symarr = $symps->fetchArray();
			
			foreach( $symarr as $k =>$v_symp){
				if($labels){
					$symp_details[$v_symp['ContactFormsSymp']['symp_id']][] = $zapv_details_items[$v_symp['detail_id']];
				} 
				else
				{
					$symp_details[$v_symp['ContactFormsSymp']['symp_id']][] = $v_symp['detail_id'];
				}
			}
			
			return $symp_details;
		}
		
		
		
		public function get_last_contact_form_symptomatology_details($ipid, $labels = false){
			
		    if(empty($ipid)){
		        return;
		    }
		    
			$symp_zapv_details = new SymptomatologyZapvDetails();
			$zapv_details_items = $symp_zapv_details->getSymptpomatologyZapvItems();
			
			
			// get last contact form
			$cf_arr = Doctrine_Query::create()
			->select("id")
			->from("ContactForms")
			->where('ipid = ?' , $ipid)
			->andWhere('isdelete = 0')
			->orderBy("start_date DESC")
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			if(!empty($cf_arr))
			{
			    $last_cf_id = $cf_arr['id'];
			}
			
			
			$symps = Doctrine_Query::create()
			->select('det.*,sym.*')
			->from('ContactFormsSympDetails det')
			->where('contact_form_id =?', $last_cf_id);
			$symps->leftJoin('det.ContactFormsSymp sym');
			$symps->andWhere('det.entry_id = sym.id');
			$symarr = $symps->fetchArray();
			
			foreach( $symarr as $k =>$v_symp){
				if($labels){
					$symp_details[$v_symp['ContactFormsSymp']['symp_id']][] = $zapv_details_items[$v_symp['detail_id']];
				} 
				else
				{
					$symp_details[$v_symp['ContactFormsSymp']['symp_id']][] = $v_symp['detail_id'];
				}
			}
			
			return $symp_details;
		}
		
		
		
	}
?>