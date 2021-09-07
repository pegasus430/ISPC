<?php

	require_once("Pms/Form.php");

	class Application_Form_ReceiptLog extends Pms_Form {

		public function insert_receipt_log($ipid = false, $client = false, $data = false)
		{
			if($ipid && $client && !empty($data) && !empty($data['operation']) && !empty($data['receipt']))
			{
				if(strlen($data['date']) > '0')
				{
					$date = date('Y-m-d H:i:s', strtotime($data['date']));
				}
				else
				{
					$date = "0000-00-00 00:00:00";
				}

				$ins = new ReceiptLog();
				$ins->ipid = $ipid;
				$ins->client = $client;
				$ins->user = $data['user'];
				$ins->receipt = $data['receipt'];
				$ins->date = $date;
				$ins->operation = $data['operation'];
				if(!empty($data['assign_type']))
				{
					$ins->assign_type = $data['assign_type'];
				}

				if($data['operation'] == "assign" || $data['operation'] == "unassign")
				{
					$ins->involved_users = serialize($data['involved_users']);
				}
				else if($data['operation'] == "sc")
				{
					$ins->old_status = $data['old_status'];
					$ins->new_status = $data['new_status'];
				}
				else if($data['operation'] == "duplicated")
				{
					$ins->source = $data['source'];
				}
				
				$ins->isdelete = "0";
				$ins->save();

				$inserted = $ins->id;

				return $inserted;
			}
		}

	}

?>