<?php

	Doctrine_Manager::getInstance()->bindComponent('EthicalForm', 'MDAT');

	class EthicalForm extends BaseEthicalForm {
		
		public function get_ethicaldetails($ipid){
				
			$drop = Doctrine_Query::create()
			->select('*')
			->from('EthicalForm')
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