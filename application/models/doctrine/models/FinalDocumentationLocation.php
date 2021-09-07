<?php

	Doctrine_Manager::getInstance()->bindComponent('FinalDocumentationLocation', 'MDAT');

	class FinalDocumentationLocation extends BaseFinalDocumentationLocation {

		function getFormFinalDocumentationLocation($ipid, $form_id)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('FinalDocumentationLocation')
				->where("ipid LIKE '" . $ipid . "'")
				->andWhere("form_id ='" . $form_id . "'")
				->andWhere("isdelete = 0 ")
				->orderBy('id ASC');
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