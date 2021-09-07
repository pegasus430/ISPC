<?php
require_once("Pms/Form.php");
class Application_Form_PatientSteps extends Pms_Form
{
	public function validate ( $post )
	{

	}

	public function insert_data ( $post, $ipid, $clientid )
	{
		//get all client steps
		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);
		$steps = new OrgSteps();
		$client_steps = $steps->get_paths_steps($client_paths, false);


		foreach ($client_steps as $k_step => $v_step)
		{
			$client_manual_steps[] = $v_step['id'];
		}

		foreach ($client_manual_steps as $k_step => $v_step_id)
		{
			if (array_key_exists($v_step_id, $post['step']))
			{
				$step_value = '1';
				$step_color = 'green';

				$records[] = array(
						'ipid' => $ipid,
						'step' => $v_step_id,
						'value' => $step_value,
						'status' => $step_color,
						'step_identification' => $post['step_identification'][$v_step_id]
				);
			}
				
			if(array_key_exists($v_step_id, $post['status']) && $post['status'][$v_step_id] == 'red')
			{
				//catch all manual steps with no value to save.
				$todos_steps[] = $v_step_id;
				$steps_identifications[$v_step_id] = $post['step_identification'][$v_step_id];
			}
		}

		if (count($todos_steps) != '0')
		{
			$this->send_todos($ipid, $clientid, $todos_steps, $steps_identifications);
		}
		$clear_chart_entryes = $this->clear_chart_entryes($ipid);

		if ($clear_chart_entryes)
		{
			//insert many with one query!!
			$collection = new Doctrine_Collection('PatientSteps');
			$collection->fromArray($records);
			$collection->save();
		}
		return $todos_steps;
	}

	public function send_todos ( $ipid, $clientid, $steps_ids , $steps_identifications)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$hidemagic = Zend_Registry::get('hidemagic');

		$paths = new OrgPaths();
		$clients_paths = $paths->get_clients_paths(array($clientid));

		$steps = new OrgSteps();
		$get_paths_steps = $steps->get_paths_steps($clients_paths);

		$org_step_permissions = new OrgStepsPermissions();
		$steps2group_permissions = $org_step_permissions->clients_steps2groups(array($clientid));
		$c_steps2group_permissions = $steps2group_permissions[$clientid];

		$master_group_perms[] = '9999999';
		foreach ($c_steps2group_permissions as $k_step_perms => $v_step_perms)
		{
			$master_group_perms = array_merge($master_group_perms, $v_step_perms);
		}
		$master_group_perms = array_unique($master_group_perms);

		$user_groups = new Usergroup();
		$c_user_groups = $user_groups->getUserGroups($master_group_perms);
		foreach ($c_user_groups as $k_user_gr => $v_user_gr)
		{
			$client_master_gr2user_gr[$v_user_gr['groupmaster']][] = $v_user_gr['id'];
		}


		$sqlh = "*,e.epid,";
		$sqlh .= "AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,";
		$sqlh .=	"AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name";

		$q = Doctrine_Query::create()
		->select($sqlh)
		->from('PatientMaster p')
		->where('p.ipid =  "' . $ipid . '"')
		->andWhere('p.isdelete = "0"')
		->andWhere('p.isstandby= "0"')
		->andWhere('p.isstandbydelete = "0"')
		->leftJoin("p.EpidIpidMapping e")
		->andWhere('e.clientid = ' . $clientid);
		$q_res = $q->fetchArray();

		$ipids_arr_final[] = '999999999999999';
		foreach ($q_res as $k_p_res => $v_p_res)
		{
			$patient_details[$v_p_res['ipid']] = $v_p_res;
			$ipids_arr_final[] = $v_p_res['ipid'];
		}

		//check if discharged dead
		$distod = Doctrine_Query::create()
		->select("*")
		->from('DischargeMethod')
		->where("isdelete = 0")
		->andWhere("abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='DTH' or abbr='Dth' or abbr='dth' or abbr='DIS' or abbr='dis' or abbr='Dis' or abbr='transkh' or abbr='Transkh' or abbr='TRANSKH' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN' or abbr='TODENT' or abbr='Todent' or abbr='todent'")
		->andWhereIn('clientid', $clientid);

		$todarray = $distod->fetchArray();

		$tod_ids[] = "9999999999999";
		foreach($todarray as $todmethod)
		{
			$tod_ids[] = $todmethod['id'];
		}

		$dispat = Doctrine_Query::create()
		->select("*")
		->from("PatientDischarge")
		->where('ipid = "'.$ipid.'"')
		->andWhere('isdelete = 0')
		->andWhereIn("discharge_method", $tod_ids);
		$discharged_res = $dispat->fetchArray();

		$is_discharged_dead = false;
		if(count($discharged_res)>'0')
		{
			$is_discharged_dead = true;
		}

		foreach ($c_steps2group_permissions as $k_shortcut_id => $v_master_groups)
		{
			if (in_array($k_shortcut_id, $steps_ids))
			{
				foreach ($v_master_groups as $k_mgr => $v_mgr)
				{
					foreach ($client_master_gr2user_gr[$v_mgr] as $k_ugr => $v_ugr)
					{

						//exclude dead patients which are not having E1 shortcut
						$exclude_patient_todo = false;
						if($is_discharged_dead && $get_paths_steps[$k_shortcut_id]['shortcut'] != 'E1' )
						{
							$exclude_patient_todo = true;
						}

						if(!$exclude_patient_todo)
						{
							$text = $patient_details[$ipid]['last_name'].', '.$patient_details[$ipid]['first_name']. ' -  ' . $get_paths_steps[$k_shortcut_id]['todo_text'] . '';
							$uk = $ipid . 'system_step_' . $k_shortcut_id . '_' . $get_paths_steps[$k_shortcut_id]['shortcut'] . '' . $v_ugr;

							$records_todo[$uk] = array(
									"client_id" => $clientid,
									"user_id" => '0',
									"group_id" => $v_ugr,
									"ipid" => $ipid,
									"todo" => $text,
									"triggered_by" => 'system_step_' . $k_shortcut_id . '_' . $get_paths_steps[$k_shortcut_id]['shortcut'] . '',
									"patient_step_identification" => $steps_identifications[$k_shortcut_id],
									"create_date" => date('Y-m-d H:i:s', time()),
									"until_date" => date('Y-m-d H:i:s', time())
							);
							
							$step_validation[$uk] = $steps_identifications[$k_shortcut_id];
						}
					}
				}
			}
		}

		if (!empty($records_todo))
		{

			foreach ($records_todo as $key => $details)
			{
				$sql_parts[] = '(ipid LIKE "' . $details['ipid'] . '" AND triggered_by = "' . $details['triggered_by'] . '"  AND group_id = "' . $details['group_id'] . '"  AND iscompleted = 0  )';
			}

			$sql_check_todos = implode(" OR ", $sql_parts);


			$sapv = Doctrine_Query::create()
			->select("*")
			->from('ToDos')
			->where('isdelete = 0')
			->andWhere($sql_check_todos);
			$sapv_res = $sapv->fetchArray();

			foreach ($sapv_res as $k => $v)
			{

				// if  patient_step_identification != 0 we compare  the itentification from new todos with the identification from existing todos
				if( $v['patient_step_identification'] != "0" ){
					if($v['patient_step_identification'] == $step_validation[$v['ipid'] . $v['triggered_by'] . $v['group_id']] ){
						$results[$v['ipid'] . $v['triggered_by'] . $v['group_id']] = $v;
					}
				} else { // if patient_step_identification == 0 - this means that we already have a todo for this step and we don't insert a new one
					$results[$v['ipid'] . $v['triggered_by'] . $v['group_id']] = $v;
				}

			}

			foreach ($results as $result_key => $value)
			{
				if (array_key_exists($result_key, $records_todo))
				{
					unset($records_todo[$result_key]);
					$unset_todos[] = $records_todo[$result_key];
				}
			}

			if (count($records_todo) > 0)
			{
				$collection = new Doctrine_Collection('ToDos');
				$collection->fromArray($records_todo);
				$collection->save();
			}
		}
	}

	public function clear_chart_entryes ( $ipid )
	{
		$query = Doctrine_Query::create()
		->update('PatientSteps')
		->set("isdelete","1")
		->where("ipid LIKE '" . $ipid . "'");
		$query->execute();

		return true;
	}

}
?>