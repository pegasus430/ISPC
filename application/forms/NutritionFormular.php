<?php
require_once("Pms/Form.php");

class Application_Form_NutritionFormular extends Pms_Form
{
	
	public function insert_data ( $ipid, $post )
	{
		//invalidate all older formulars
		$this->set_isdelete($ipid);
		
		//remove empty rows
		foreach($post as $k=>$v) {
			if (empty($v['name']) && empty($v['ammount']) && (empty($v['application']) || $v['application']== "" ) && empty($v['freetext']) ) {
				unset($post[$k]);
			}
		}
		
		//and save this new one
		$insert_data = new NutritionFormular();
		$insert_data->ipid = $ipid;		
		$insert_data->formular_values = json_encode($post);
		$insert_data->isdelete = 0;
		$insert_data->save();
		$insert_id = $insert_data->id;

		
		//add verlauf entry - pdf link + formular link?

		
		return $insert_id;
	}

	private function set_isdelete ( $ipid = null )
	{
		$row = Doctrine_Query::create()
		->update('NutritionFormular')
		->set('isdelete', '1')
		->where('ipid = ?', $ipid)
		->andWhere('isdelete = 0')
		->execute();
		
	}
	


}
?>