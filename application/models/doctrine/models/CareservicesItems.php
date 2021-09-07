<?php

	Doctrine_Manager::getInstance()->bindComponent('CareservicesItems', 'SYSDAT');

	class CareservicesItems extends BaseCareservicesItems {

		public function get_groups_items($gr_id)
		{
		    if(is_array($gr_id))
    		{
    		  $array_ids = $gr_id;
    		}
			else
			{
    		  $array_ids = array($gr_id);
			}
			if(empty($array_ids)){
			    $array_ids[] = "XXX";
			}
			
			$drop = Doctrine_Query::create()
				->select('*')
				->from('CareservicesItems indexBy id')
				->where("isdelete = 0")
				->andWhereIn("group_id",$array_ids);
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}
		
	}

?>