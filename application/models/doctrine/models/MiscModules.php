<?php

	Doctrine_Manager::getInstance()->bindComponent('MiscModules', 'SYSDAT');

	class MiscModules extends BaseMiscModules {

		public function getMiscModules($clientid)
		{
			$misc = Doctrine_Query::create()
				->select('*')
				->from('MiscModules')
				->orderby('id');

			$miscarr = $misc->fetchArray();
			if($miscarr)
			{

				return $miscarr;
			}
			else
			{
				return false;
			}
		}

	}

?>