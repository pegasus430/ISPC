<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMemo', 'IDAT');

	class PatientMemo extends BasePatientMemo {

		public function getpatientMemo($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMemo')
				->where("ipid='" . $ipid . "'");
			$loc = $drop->fetchArray();

			if($loc)
			{
				return $loc;
			}
		}

		public function get_multiple_patient_memo($ipids)
		{
			if(count($ipids) == 0)
			{
				$ipids[] = '99999999999';
			}

			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMemo')
				->whereIn("ipid", $ipids)
				->andWhere('memo NOT LIKE "Dies ist ein Memo Feld, klicken Sie%" '); // remove the patients that have the default memo saved.
			$memo_res = $drop->fetchArray();

			if($memo_res)
			{
				foreach($memo_res as $k_memo => $v_memo)
				{
					$memo_arr[$v_memo['ipid']] = $v_memo['memo'];
				}

				return $memo_arr;
			}
		}

	}

?>