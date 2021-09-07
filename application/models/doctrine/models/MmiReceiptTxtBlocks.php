<?php

	Doctrine_Manager::getInstance()->bindComponent('MmiReceiptTxtBlocks', 'SYSDAT');

	class MmiReceiptTxtBlocks extends BaseMmiReceiptTxtBlocks {

		public function get_receipt_txt($mtid, $clientid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('MmiReceiptTxtBlocks')
				->where("id='" . $mtid . "'")
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("isdeleted = '0'");
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

		public function search_receipt_mmitxt($client, $search = '')
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('MmiReceiptTxtBlocks')
				->where("text LIKE '%" . $search . "%'")
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("isdeleted = '0'");
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

	}

?>