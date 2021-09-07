<?php

	Doctrine_Manager::getInstance()->bindComponent('FinalDocumentation', 'MDAT');

	class FinalDocumentation extends BaseFinalDocumentation {

		function getFinalDocumentationDetails($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('FinalDocumentation')
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

		function getLastFinalDocumentation($ipid)
		{

			$drop = Doctrine_Query::create()
				->select("*")
				->from('FinalDocumentation')
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