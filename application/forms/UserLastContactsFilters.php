<?php

require_once("Pms/Form.php");

class Application_Form_UserLastContactsFilters extends Pms_Form {

	
    //ISPC-2440 Lore 11.03.2020
	public function set_filter ($user,$client,$data)
	{
	    
	    $m_user_filters = new UserLastContactsFilters();
		$user_filter_q = $m_user_filters->get_user_filter();
		
		foreach($user_filter_q as $k=>$uk){
			$current_shortcut_data[] = $uk['shortcut'];
		}
		
		
		$post_sh[$data['shortcut']] = $data['value'] ; // get the new selected  filter
		
		foreach($current_shortcut_data as $k=>$sh){
			if(!in_array($sh,array_keys($post_sh))){
				$post_sh[$sh] = "1"; // insert current shortcuts that are not available in the curent patient
			}
		}
		
		foreach($post_sh as $shortcut => $value ) {
			if ($value == "1") {
				$shortcut_data [] = array (
						'user' => $user,
						'client' => $client,
						'shortcut' => $shortcut,
						'isdelete' => "0" 
				);
			}
		}
		//dd($post_sh,$shortcut_data);
		
		$reset_filter = $this->reset_filter($user,$client);
		
		if(!empty($shortcut_data)){
			$collection = new Doctrine_Collection('UserLastContactsFilters');
			$collection->fromArray($shortcut_data);
			$collection->save();
		}
		
		return true;
		
	}

	
	public function reset_filter ($user,$client)
	{
		$q = Doctrine_Query::create()
		->update('UserLastContactsFilters ')
		->set('isdelete', "1")
		->where('user = "' . $user. '"')
		->andWhere('client = "' . $client . '"');
		$q->execute();

		
		
	}
	
	public function reset_filter_to_all ($user,$client,$sh_time)
	{
	    $filter_time = array('12h','24h');
	    
	    $q = Doctrine_Query::create()
	    ->update('UserLastContactsFilters ')
	    ->set('isdelete', "1")
	    ->where('user = "' . $user. '"')
	    ->andWhere('client = "' . $client . '"');
	    
	    if($sh_time == 'all_time'){
	        $q->andWhereIn('shortcut', $filter_time );
	    }else {
	        $q->andWhereNotIn('shortcut', $filter_time );
	    }
	    $q->execute();
	}
	

	
}
?>