<?php

	Doctrine_Manager::getInstance()->bindComponent('FallprotocolForm', 'MDAT');

	class FallprotocolForm extends BaseFallprotocolForm {
		
		public function get_fallprotocoldetails($ipid){
				
			$drop = Doctrine_Query::create()
			->select('*')
			->from('FallprotocolForm')
			->where("ipid='" . $ipid . "'");
			$droparray = $drop->fetchArray();
			
			if(count($droparray)>0)
			{
				return $droparray[0];
			}else 
			{
				return false;
			}
			
		}
		
		
	}
?>	