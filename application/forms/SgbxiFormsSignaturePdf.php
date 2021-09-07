<?php
//require_once("Pms/Form.php");

class Application_Form_SgbxiFormsSignaturePdf extends Pms_Form 
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
		
		
		foreach( $post['data'] as $groupid => $group_actions) {
			
			foreach($group_actions as $actionid =>  $action_row) {
				
				foreach($action_row['data'] as $day =>  $day_val) {
					
					$collection_row =  array(
							'ipid' => $this->_patientMasterData['ipid'],
								
							'groupid' => $action_row['groupid'],
							'actionid' => $action_row['actionid'],
							'startdate' => date("Y-m-d", strtotime($action_row['startdate'])),
							'interval_action' => $action_row['interval'],
							'interval_options' => $action_row['interval_options'],
							'selected_hour' => $action_row['selected_hour'],
								
							'form_day' => date("Y-m-d", strtotime($day)),
							'form_value' => $day_val,
					
					);
					
					$collection_data[] = $collection_row;
				}
				
			}
			
			
		}

		if( ! empty($collection_data)) {
			//insert this new form
			$collection = new Doctrine_Collection('SgbxiFormsSignaturePdf');
			$collection->fromArray($collection_data);
			$collection->save();
		}
				
		
		
	}
	
	private function _delete_old( $ipid = '' , $month = '')
	{

		$update = Doctrine_Query::create()
			->update("SgbxiFormsSignaturePdf")
			
			->set('isdelete', '1')
			->set('change_date', 'NOW()')
			->set('change_user', '?' , $this->logininfo->userid)
			
			->where("ipid = ? ", $ipid)
			->andWhere("isdelete = 0")
			->andWhere("DATE_FORMAT(form_day, \"%Y-%m\") = ?", $month)
			
			->execute();
		
	}

}

?>