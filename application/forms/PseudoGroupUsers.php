<?php

	require_once("Pms/Form.php");

	class Application_Form_PseudoGroupUsers extends Pms_Form {

	
		public function InsertData($post,$u) {
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
			//print_r($post['user_id']); exit;
			
			if ($post['clientid'] > 0) {
				$post['clientid'] = $post['clientid'];
			} else {
				$post['clientid'] = $logininfo->clientid;
			}
			foreach ($post['user_id'] as $k_post => $v_post)
			{
				$save_data[] = array('user_id' => $k_post, 'clientid' => $post['clientid'],'pseudo_id'=>$u);
			}
		
			$collection = new Doctrine_Collection('PseudoGroupUsers');
			$collection->fromArray($save_data);
			$collection->save();
			
			return $user;
		}
		
		public function UpdateData($post) {
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			
			if($_GET['id'] > 0 )
			{
				if ($post['clientid'] > 0) {
					$post['clientid'] = $post['clientid'];
				} else {
					$post['clientid'] = $logininfo->clientid;
				} 
				
				$Q = Doctrine_Query::create()
				->update('PseudoGroupUsers')
				->set('isdelete', '1')
				->set('change_date', "'" . date('Y-m-d H:i:s') . "'")
				->set('change_user', "'" . $userid . "'")
				->where("pseudo_id= ?", $_GET['id']);
				$result = $Q->execute();
				

				foreach ($post['user_id'] as $k_post => $v_post)
				{
					$save_data[] = array('user_id' => $k_post, 'clientid' => $post['clientid'],'pseudo_id'=>$_GET['id']);
				}
				
				$collection = new Doctrine_Collection('PseudoGroupUsers');
				$collection->fromArray($save_data);
				$collection->save();
					
					
				return $user;
				}
				
			
				
				
			}
			
		
	}
		
		
?>