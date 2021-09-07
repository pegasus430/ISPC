<?php
//require_once("Pms/Form.php");
// Maria:: Migration ISPC to CISPC 08.08.2020	
class Application_Form_DemstepcareControl extends Pms_Form 
{

// 	protected $_patientMasterData;// is set in Pms_Form contruct
	
	public function validate($post = array())
	{		
		return true;	
	}

	
	public function insert($post = array()) 
	{
		$ipid = $this->_patientMasterData['ipid'];
		$month = $post['selected_month'];
	
		
// 		dd($post);
		
		//delete all old from this month	
// 		$this->_delete_old($ipid , $month);
		$this->_delete_all($ipid);
		
		$collection_data = array();
		
			
// 			    dd($post['form']);
		foreach($post['form'] as $shortcut =>  $shortcut_data) 
		{
		    foreach($shortcut_data as $day_val=>$val)
		    {
		        $month = date("n", strtotime($day_val));
		        $yearQuarter = ceil($month / 3);
		        $quart_id = '0' . $yearQuarter . '/' . date("Y", strtotime($day_val)) ;
		        
		        
				$collection_row =  array(
					'ipid' => $this->_patientMasterData['ipid'],
					'shortcut' => $shortcut,
					'value' => $val,
					'quarter' => $quart_id,
					'quarterly_date' => date("Y-m-d", strtotime($day_val))
				);
				
				$collection_data[] = $collection_row;
		    }
		}
		
		if( ! empty($collection_data)) {
			//insert this new form
			$collection = new Doctrine_Collection('DemstepcareControl');
			$collection->fromArray($collection_data);
			$collection->save();
		}
				
		
		
	}
	
	public function reset($post = array()) 
	{
		$ipid = $this->_patientMasterData['ipid'];
		$month = $post['selected_month'];
		$this->_delete_all($ipid);
	}
	
	
	private function _delete_all( $ipid = '')
	{
	    if(empty($ipid )){
	        return;
	    }

		$update = Doctrine_Query::create()
			->update("DemstepcareControl")
			
			->set('isdelete', '1')
			->set('change_date', 'NOW()')
			->set('change_user', '?' , $this->logininfo->userid)
			
			->where("ipid = ? ", $ipid)
			->andWhere("isdelete = 0")
			
			->execute();
		
	}
}

?>