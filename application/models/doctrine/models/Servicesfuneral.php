<?php
Doctrine_Manager::getInstance()->bindComponent('Servicesfuneral', 'SYSDAT');

class Servicesfuneral extends BaseServicesfuneral {

	public function get_service($id)
	{
		$drop = Doctrine_Query::create()
		->select('*')
		->from('Servicesfuneral')
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
		->from('Servicesfuneral')
		->whereIn("id", $array_ids);
		$droparray = $drop->fetchArray();

		return $droparray;
	}

	public function getServicesfuneral($ipid, $letter, $keyword, $arrayids)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		//ISPC-2612 Ancuta 27.06.2020
		$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Servicesfuneral',$clientid);
		

			if($keyword != false)
			{
				$keyword_sql = "AND services_funeral_name like '%" . ($keyword) . "%'";;
			}

			if($letter != false)
			{
				$keyword_sql = "AND services_funeral_name like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Servicesfuneral')
				->where("clientid='" . $clientid . "'AND isdelete =0  AND (services_funeral_name != '' or cp_fname != '' or cp_lname != '') ". $keyword_sql . $array_sql);
			//echo $drop->getSqlQuery();
				if($client_is_follower){
				    $drop->andWhere("connection_id is NOT null");
				    $drop->andWhere("master_id is NOT null");
				}
			$droparray = $drop->fetchArray();
			return $droparray;
	}
}

?>