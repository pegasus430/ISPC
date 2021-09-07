<?php

	Doctrine_Manager::getInstance()->bindComponent('ContactFormsSymp', 'MDAT');

	class ContactFormsSymp extends BaseContactFormsSymp {

		public function getContactFormsSymp($contact_form_id, $ipid)
		{
			$symps = Doctrine_Query::create()
				->select('*')
				->from('ContactFormsSymp bvs')
				->where('bvs.contact_form_id ="' . (int)$contact_form_id . '" AND bvs.ipid ="' . $ipid . '"');
			$symarr = $symps->fetchArray();

			if(sizeof($symarr) > 0)
			{
				foreach($symarr as $symp)
				{
					$newsymarr[$symp['symp_id']] = $symp;
				}
				return $newsymarr;
			}
		}

	}

?>