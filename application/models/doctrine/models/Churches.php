<?php
Doctrine_Manager::getInstance()->bindComponent('Churches', 'SYSDAT');

class Churches extends BaseChurches {

	public function getChurch($id)
	{
		$drop = Doctrine_Query::create()
		->select('*')
		->from('Churches')
		->where("id='" . $id . "'");
		$droparray = $drop->fetchArray();

		return $droparray;
	}

	public function getChurches($ipid = false, $letter = false, $keyword = false, $arrayids = false)
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($ipid != false)
		{
				
			$chp = new PatientChurches();
			$charray = $chp->getPatientChurches($ipid);
				
			if(count($charray) > 0)
			{
				foreach($charray as $keych => $valuech)
				{
					$chids[$keych] = $valuech['id'];
				}
				$ids = implode(",", $chids);

				$ipid_sql .= " AND id IN (" . $ids . ")";
			}
			else
			{
				$ipid_sql .= " AND id IN (0)";
			}
		}
		else
		{
			$ipid_sql = " AND indrop=0 AND isdelete =0 AND valid_till = '0000-00-00'";
		}

		if($keyword != false)
		{
			$keyword_sql = " AND name like '%" . ($keyword) . "%'";
		}

		if($letter != false)
		{
			$keyword_sql = " AND name like '" . ($letter) . "%'";
		}

		if($arrayids != false)
		{
			$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
			$ipid_sql = '';
		}

		$drop = Doctrine_Query::create()
		->select('*')
		->from('Churches')
		->where("clientid='" . $clientid . "' AND (name != '' or contact_firstname != '' or contact_lastname != '') " . $ipid_sql . $keyword_sql . $array_sql);
		$droparray = $drop->fetchArray();

		return $droparray;
	}

	public function clone_record($id, $target_client)
	{
		$church = $this->getChurch($id);

		if($church)
		{
			$chp = new Churches();
			$chp->clientid = $target_client;
			$chp->id_master = $id;
			$chp->name = $church[0]['name'];
			$chp->contact_firstname = $church[0]['contact_firstname'];
			$chp->contact_lastname = $church[0]['contact_lastname'];
			$chp->street = $church[0]['street'];
			$chp->zip = $church[0]['zip'];
			$chp->city = $church[0]['city'];
			$chp->phone = $church[0]['phone'];
			$chp->phone_cell = $church[0]['phone_cell'];
			$chp->email = $church[0]['email'];
			$chp->valid_from = $church[0]['valid_from'];
			$chp->valid_till = $church[0]['valid_till'];
			$chp->isdelete = $church[0]['isdelete'];
			$chp->indrop = '1';
			$chp->save();//ISPC-2614 Ancuta 19.07.2020 corrected error changed from fdoc to chp

			if($chp)
			{
				return $chp->id;
			}
			else
			{
				return false;
			}
		}
	}
}


?>