<?php

	Doctrine_Manager::getInstance()->bindComponent('Bedarfsmedication', 'MDAT');

	class Bedarfsmedication extends BaseBedarfsmedication {

		public function getbedarfsmedication($bid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('Bedarfsmedication')
				->where("bid='" . $bid . "'");


			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

	}

?>