<?php

	require_once("Pms/Form.php");

	class Application_Form_PatientCareservices extends Pms_Form {
		
	    

	    public function clear_data($ipid, $date)
	    {
	        if(!empty($date))
	        {
	            $Q = Doctrine_Query::create()
	            ->update('PatientCareservices')
	            ->set('isdelete', '1')
	            ->where("DATE(date) = '" . $date . "'")
	            ->andWhere('ipid LIKE "' . $ipid . '"');
	            $Q->execute();
	            return true;
	        }
	        else
	        {
	            return false;
	        }
	    }
	     
	    
	    
	    
		public function insert($post)
		{
			//print_r($post); exit;
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			
			$val = new Pms_Validation();
				
            $shifts = array('morning','noon','night');
			
			if(strlen($post['date']) > 0 && $val->isdate($post['date']) ){
			    $date = date("Y-m-d 00:00:00", strtotime($post['date']));
			    $clear_date = date("Y-m-d", strtotime($post['date']));
			}
			
			foreach($post['items'] as $item_id => $item_data)
			{
            	 foreach($shifts as $k=>$shift){
            	     
           	           $shif_data[$item_id]['shift'] = $shift;
           	           
            	       if($item_data[$shift]['full'] == "1"){
            	           $shif_data[$item_id]['full'] = 1;
            	           $shif_data[$item_id]['full_amount']= $item_data[$shift]['full_amount'];
            	       } 
            	       else
            	       {
            	           $shif_data[$item_id]['full'] = 0;
            	           $shif_data[$item_id]['full_amount']=0;
            	           $shif_data[$item_id]['full_amount']= 0;
            	       }
            	       
            	       if($item_data[$shift]['partial'] == "1"){
            	           $shif_data[$item_id]['partial'] = 1;
            	           $shif_data[$item_id]['partial_amount']= $item_data[$shift]['partial_amount'];
            	           
            	       } 
            	       else
            	       {
            	           $shif_data[$item_id]['partial'] = 0;
            	           $shif_data[$item_id]['partial_amount']= 0;
            	       }
            	       
            	       $records[] = array(
            	           'ipid' => $ipid,
            	           'date' => $date,
            	           'shift' =>  $shif_data[$item_id]['shift'],
            	           'item' => $item_id,
            	           'full' => $shif_data[$item_id]['full'] ,
            	       	   'full_amount' => $shif_data[$item_id]['full_amount'],
            	           'partial' => $shif_data[$item_id]['partial'] ,
            	       	   'partial_amount' => $shif_data[$item_id]['partial_amount']
            	       );
            	 }
			}
			
			// clear data
			$this->clear_data($ipid,$clear_date);
			
			$collection = new Doctrine_Collection('PatientCareservices');
			$collection->fromArray($records);
			$collection->save();
		}

	}

?>