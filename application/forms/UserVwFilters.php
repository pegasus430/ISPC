<?php

require_once("Pms/Form.php");

class Application_Form_UserVwFilters extends Pms_Form {

	public function set_filter ($user,$client,$data)
	{
		
		foreach ( $data as $status => $value ) {
			$post_sh[$status] = $value; // get the new selected  filter
		}
 
		$reset_filter = $this->reset_filter($user,$client);
		
		if ($post_sh['status_color_g'] != 0 || $post_sh['status_color_y'] != 0 || $post_sh['status_color_r'] != 0 || $post_sh['status_color_b'] != 0 || $post_sh['status_inactive'] != 0 || $post_sh['status_color_blue'] != 0 || $post_sh['status_color_purple'] != 0 || $post_sh['status_color_grey'] != 0) {
    		$usercf = new UserVwFilters();
    		$usercf->user = $user;
    		$usercf->client = $client;
    		$usercf->status_color_g = $post_sh['status_color_g'];
    		$usercf->status_color_y = $post_sh['status_color_y'];
    		$usercf->status_color_r = $post_sh['status_color_r'];
    		$usercf->status_color_b = $post_sh['status_color_b'];
    		$usercf->status_inactive = $post_sh['status_inactive'];
    		$usercf->status_color_blue   = $post_sh['status_color_blue'];
    		$usercf->status_color_purple = $post_sh['status_color_purple'];
    		$usercf->status_color_grey   = $post_sh['status_color_grey'];
    		$usercf->save();
		}
		return true;
		
	}
 
	public function reset_filter ($user,$client)
	{
		$q = Doctrine_Query::create()
		->update('UserVwFilters ')
		->set('isdelete', "1")
		->where('user = "' . $user. '"')
		->andWhere('client = "' . $client . '"');
		$q->execute();

		
		
	}
	
	
	
}
?>