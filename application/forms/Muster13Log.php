<?php

	require_once("Pms/Form.php");

	class Application_Form_Muster13Log extends Pms_Form {

		public function insert_muster13_log($ipid = false, $client = false, $data = false)
		{
			if($ipid && $client && !empty($data) && !empty($data['operation']) && !empty($data['muster13id']))
			{
				if(strlen($data['date']) > '0')
				{
					$date = date('Y-m-d H:i:s', strtotime($data['date']));
				}
				else
				{
					$date = "0000-00-00 00:00:00";
				}
				
				$ins = new Muster13Log();
				
				$ins->ipid = $ipid;
				$ins->client = $client;
				$ins->user = $data['user'];
				$ins->muster13id = $data['muster13id'];
				$ins->date = $date;
				$ins->operation = $data['operation'];

				if($data['operation'] == "duplicated")
				{
					$ins->source = $data['source'];
				}
				
				if($data['comment'] != null)
				{
					$ins->comment = $data['comment'];
				}

				$ins->save();

				$inserted = $ins->id;

				return $inserted;
			}
		}

	}

?>