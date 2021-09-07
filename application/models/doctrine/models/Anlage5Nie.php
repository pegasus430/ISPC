<?php

Doctrine_Manager::getInstance()->bindComponent('Anlage5Nie', 'MDAT');

class Anlage5Nie extends BaseAnlage5Nie {
	
	function get_anlage5_details($ipid)
	{
		$drop = Doctrine_Query::create()
		->select('*')
		->from('Anlage5Nie')
		->where("ipid='" . $ipid . "'");

		$droparray = $drop->fetchArray();
			
		return $droparray;
	}
}