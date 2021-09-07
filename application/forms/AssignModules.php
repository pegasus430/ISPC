<?php

require_once("Pms/Form.php");

class Application_Form_AssignModules extends Pms_Form
{
	public function InsertData($post)
	{
		foreach($post['hdnmoduleid'] as $moduleid)
		{
			$q = Doctrine_Query::create()
			->select('*')
			->from('ClientModules')
			->where('clientid= ?', $_GET['id'])
			->andWhere('moduleid= ?', $moduleid);
				
			$clientpre = $q->execute();
				
			if(count($clientpre->toArray())<1)
			{
				$clientmodules = new ClientModules();
				$clientmodules->clientid = $_GET['id'];
				$clientmodules->moduleid = $moduleid;
				$clientmodules->canaccess = $post['canaccess'][$moduleid];
				$clientmodules->save();
			}else{
					
				$q = Doctrine_Query::create()
				->update('ClientModules')
				->set('canaccess',"'".$post['canaccess'][$moduleid]."'")
				->where('clientid= ?', $_GET['id'])
				->andWhere('moduleid= ?', $moduleid);
				$q->execute();
			}
			 
		}

	}
}

?>