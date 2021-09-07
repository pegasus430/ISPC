<?php

require_once("Pms/Form.php");

class Application_Form_UserCourseFilters extends Pms_Form {

	
	
	public function set_filter ($user,$client,$data)
	{

		// get user Filter - ISPC-1272 = 150401
		$m_user_filters = new UserCourseFilters();
		$user_filter_q = $m_user_filters->get_user_filter();
		foreach($user_filter_q as $k=>$uk){
			$current_shortcut_data[] = $uk['shortcut'];
		}
		
		foreach ( $data as $shortcut => $value ) {
			$post_sh[$shortcut] = $value; // get the new selected  filter
		}
		
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
		
		$reset_filter = $this->reset_filter($user,$client);
		
		if(!empty($shortcut_data)){
			$collection = new Doctrine_Collection('UserCourseFilters');
			$collection->fromArray($shortcut_data);
			$collection->save();
		}
		
		return true;
		
	}
	
	
	public function InsertFilter ($user,$client,$post)
	{
		$usercf = new UserCourseFilters();
		$usercf->user = $user;
		$usercf->client = $client;
		$usercf->shortcut = $post['shortcut'];
		$usercf->save();

		return true;
	}

	public function RemoveFilter ($user,$client,$post)
	{
		$q = Doctrine_Query::create()
		->update('UserCourseFilters ')
		->set('isdelete', "1")
		->where('user = "' . $user. '"')
		->andWhere('client = "' . $client . '"')
		->andWhere('shortcut = "' . $post['shortcut'] . '"');
		$q->execute();

	}
	
	public function reset_filter ($user,$client)
	{
		$q = Doctrine_Query::create()
		->update('UserCourseFilters ')
		->set('isdelete', "1")
		->where('user = "' . $user. '"')
		->andWhere('client = "' . $client . '"');
		$q->execute();

		
		
	}
	
	
	
}
?>