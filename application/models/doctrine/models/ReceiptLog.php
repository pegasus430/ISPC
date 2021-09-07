<?php

	Doctrine_Manager::getInstance()->bindComponent('ReceiptLog', 'SYSDAT');

	class ReceiptLog extends BaseReceiptLog {

		public function get_patient_receipt_log($ipid = false, $receipt = false)
		{
			if($ipid && $receipt)
			{
				$receipts = Doctrine_Query::create()
					->select("*")
					->from('ReceiptLog')
					->where("`receipt`='" . $receipt . "'")
					->andWhere('`ipid` LIKE "' . $ipid . '"')
					->andWhere('`isdelete` = "0"')
					->orderBy('`date` ASC');
				$receiptsarray = $receipts->fetchArray();
				if($receiptsarray)
				{
					return $receiptsarray;
				}
				else
				{
					return false;
				}
			}
		}

	}

?>