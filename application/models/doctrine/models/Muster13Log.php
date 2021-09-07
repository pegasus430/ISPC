<?php

	Doctrine_Manager::getInstance()->bindComponent('Muster13Log', 'MDAT');

	class Muster13Log extends BaseMuster13Log {

		public function get_patient_muster13_log($ipid = false, $ms13id = false)
		{
			
			if($ipid && $ms13id)
			{
				$ms13s = Doctrine_Query::create()
					->select("*")
					->from('Muster13Log')
					->where("`muster13id`='" . $ms13id . "'")
					->andWhere('`ipid` LIKE "' . $ipid . '"')
					->andWhere('`isdelete` = "0"')
					->orderBy('`date` ASC');
				$ms13array = $ms13s->fetchArray();
				
				if(!empty($ms13array))
				{
					return $ms13array;
				}
				else
				{
					return false;
				}
			}
		}

	}

?>