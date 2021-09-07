<?php

	Doctrine_Manager::getInstance()->bindComponent('DashboardEvents', 'SYSDAT');

	class DashboardEvents extends BaseDashboardEvents {

		public function get_dashboard_event($id)
		{
			$da = Doctrine_Query::create()
				->select("*")
				->from('DashboardEvents')
				->where('id="' . $id . '"');
			$da_array = $da->fetchArray();
			if(!empty($da_array))
			{
				return $da_array;
			}
		}

		public function create_dashboard_event($data,$completed = true )
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$da = new DashboardEvents();
			$da->client_id = $logininfo->clientid;
			$da->user_id = $logininfo->userid;
			$da->ipid = $data['ipid'];
			$da->tabname = $data['tabname'];
			$da->triggered_by = $data['triggered_by'];
			$da->title = $data['title'];
			$da->until_date = date("Y-m-d H:i:s", strtotime($data['due_date']));
			$da->create_date = date("Y-m-d H:i:s", time());
			if($completed){
				$da->iscompleted = '1';
				$da->complete_user = $logininfo->userid;
				$da->complete_date = date("Y-m-d H:i:s", time());
			} 
			$da->save();
		}
		
		
		public function complete_dashboard_event($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$da = Doctrine::getTable('DashboardEvents')->find($id);
			if($da)
			{
				$da->iscompleted = '1';
				$da->complete_user = $logininfo->userid;
				$da->complete_date = date("Y-m-d H:i:s", time());
				$da->save();
			}
		}

		public function uncomplete_dashboard_event($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$da = Doctrine::getTable('DashboardEvents')->find($id);
			if($da)
			{
				$da->iscompleted = '0';
				$da->complete_user = $logininfo->userid;
				$da->complete_date = date("Y-m-d H:i:s", time());
				$da->save();
			}
		}

		public function get_completed_dashboard_events($clientid, $skip_ids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$da = Doctrine_Query::create()
				->select("*")
				->from('DashboardEvents')
				->where('client_id="' . $clientid . '"')
				->andWhere('isdelete=0')
				->andWhere('triggered_by != "forced_system" ') // do not show the forced added actions for anlage  
				->andWhere('iscompleted=1');
			if($skip_ids)
			{
				$da->andWhereNotIn('id', $skip_ids);
			}

			$da->andWhere('(user_id = "' . $logininfo->userid . '" OR group_id = "' . $logininfo->groupid . '")');
			$daarray = $da->fetchArray();
			if(count($daarray) > 0)
			{
				return $daarray;
			}
		}

		public function delete_dashboard_event($id)
		{
			$todo = Doctrine::getTable('DashboardEvents')->find($id);
			$todo->isdelete = '1';
			$todo->save();
		}

	}

?>