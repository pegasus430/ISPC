<?php
Doctrine_Manager::getInstance()->bindComponent('Services', 'SYSDAT');

class Services extends BaseServices {

	public function get_service($id)
	{
		$drop = Doctrine_Query::create()
		->select('*')
		->from('Services')
		->where("id='" . $id . "'");
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

	public function get_services($ids)
	{
		if(is_array($ids))
		{
			$array_ids = $ids;
		}
		else
		{
			$array_ids = array($ids);
		}

		$drop = Doctrine_Query::create()
		->select('*')
		->from('Services')
		->whereIn("id", $array_ids);
		$droparray = $drop->fetchArray();

		return $droparray;
	}

}

?>