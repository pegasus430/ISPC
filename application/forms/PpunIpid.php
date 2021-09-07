<?php

	require_once("Pms/Form.php");

	class Application_Form_PpunIpid extends Pms_Form {

		public function insert_ppun($post_data)
		{
			if($post_data)
			{
				$course = new PpunIpid();
				$course->ipid = $post_data['ipid'];
				$course->ppun = $post_data['ppun'];
				$course->clientid = $post_data['clientid'];
				$course->save();

				return $course->toArray();
			}
		}

		
		public function update_or_insert_ppun($post_data)
		{
			if($post_data)
			{
			    $PpunIpid_obj = new PpunIpid();
			    $ppun_number_array = $PpunIpid_obj->check_patient_ppun_db($post_data['ipid'],$post_data['clientid']);
			    
			    if(!empty($ppun_number_array)){
    			    $cust = Doctrine::getTable('PpunIpid')->find($ppun_number_array['id']);
    			    $cust->ppun = $post_data['ppun'];
    			    $cust->save();
			    } else{
			        if(strlen($post_data['ppun']) > 0 ){
    			        $insert =  self::insert_ppun($post_data);
			        }
			    }
			}
		}
	}

?>