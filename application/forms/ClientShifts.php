<?php

	require_once("Pms/Form.php");

	class Application_Form_ClientShifts extends Pms_Form {

		public function validate($post)
		{
			
		}

		public function insert_data($post, $clientid)
		{

			$today = date('Y-m-d ', time());
			$start_period = date('Y-m-d H:i:s', strtotime($today . $post['start'] . ":00"));
			$end_period = date('Y-m-d H:i:s', strtotime($today . $post['end'] . ":00"));

			$cust = new ClientShifts();
			$cust->client = $clientid;
			$cust->name = $post['name'];
			$cust->start = $start_period;
			$cust->end = $end_period;
			$cust->color = $post['color'];
			$cust->shortcut = $post['shortcut'];
			$cust->show_time = $post['show_time'];
			$cust->isholiday = $post['isholiday'];
			$cust->istours = $post['istours'];
			if (empty($post['active_till'])) {
				$cust->active_till = null;
			} else {
				$cust->active_till = date("Y-m-d", strtotime($post['active_till']));
			}
					
			
			
			$cust->save();

			if($cust->id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function update_data($sid, $post, $clientid)
		{
			$today = date('Y-m-d ', time());
			$start_period = date('Y-m-d H:i:s', strtotime($today . $post['start'] . ":00"));
			$end_period = date('Y-m-d H:i:s', strtotime($today . $post['end'] . ":00"));

			$cust = Doctrine::getTable('ClientShifts')->find($_REQUEST['sid']);
			$cust->client = $clientid;
			$cust->name = $post['name'];
			$cust->start = $start_period;
			$cust->end = $end_period;
			$cust->color = $post['color'];
			$cust->shortcut = $post['shortcut'];
			$cust->show_time = $post['show_time'];
			$cust->isholiday = $post['isholiday'];
			$cust->istours = $post['istours'];
			if (empty($post['active_till'])) {
				$cust->active_till = null;
			} else {
				$cust->active_till = date("Y-m-d", strtotime($post['active_till']));
			}
			
			$cust->save();

			return true;
		}

	}

?>