<?php

	Doctrine_Manager::getInstance()->bindComponent('MdkSchne', 'MDAT');

	class MdkSchne extends BaseMdkSchne {

		function getMDKdetails($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('MdkSchne')
				->where("ipid LIKE '" . $ipid . "'")
				->orderBy('create_date ASC');
			$form_details = $drop->fetchArray();

			if($form_details)
			{
				return $form_details;
			}
			else
			{
				return false;
			}
		}

		function getLastMDKdetails($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('MdkSchne')
				->where("ipid LIKE '" . $ipid . "'")
				->orderBy('create_date DESC')
				->limit(1);
			$form_details = $drop->fetchArray();

			if($form_details)
			{
				return $form_details;
			}
			else
			{
				return false;
			}
		}

	}

?>