<?php
//require_once("Pms/Form.php");

class Application_Form_RlpControl extends Pms_Form 
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
		
		//delete all old from this month	
		$this->_delete_old($ipid , $month);
		
		$collection_data = array();
		
			
		foreach($post['form'] as $shortcut =>  $shortcut_row) {
			
			foreach($shortcut_row  as $day =>  $day_val) {
				
				$collection_row =  array(
					'ipid' => $this->_patientMasterData['ipid'],
					'shortcut' => $shortcut,
					'value' => $day_val,
					'form_date' => date("Y-m-d", strtotime($day))
				);
				
				$collection_data[] = $collection_row;
			}
			
		}
		if( ! empty($collection_data)) {
			//insert this new form
			$collection = new Doctrine_Collection('RlpControl');
			$collection->fromArray($collection_data);
			$collection->save();
		}
				
		
		
	}
	
	public function reset($post = array()) 
	{
		$ipid = $this->_patientMasterData['ipid'];
		$month = $post['selected_month'];
		
		//delete all old from this month	
		$this->_delete_old($ipid , $month);
		
	}
	
	private function _delete_old( $ipid = '' , $month = '')
	{

		$update = Doctrine_Query::create()
			->update("RlpControl")
			
			->set('isdelete', '1')
			->set('change_date', 'NOW()')
			->set('change_user', '?' , $this->logininfo->userid)
			
			->where("ipid = ? ", $ipid)
			->andWhere("isdelete = 0")
			->andWhere("DATE_FORMAT(form_date, \"%Y-%m\") = ?", $month)
			
			->execute();
		
	}

}

?>