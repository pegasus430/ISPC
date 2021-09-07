<?php

	Doctrine_Manager::getInstance()->bindComponent('ExtraFormsClient', 'IDAT');

	class ExtraFormsClient extends BaseExtraFormsClient {

		public function getExtraFormsClient($clientid)
		{

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraFormsClient')
				->where('clientid =' . $clientid . '');
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

		public function getExtraFormsClientQuammasep($clientid)
		{

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraFormsClient')
				->where('clientid =' . $clientid . '');
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();

				if(count($fcarr) > 0)
				{
					$comma = "";
					foreach($fcarr as $key => $val)
					{
						$forms.= $comma . $val['formid'];
						$comma = "";
					}
					return $forms;
				}
			}
		}

		public function getExtraForms($clientid, $formids)
		{

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraFormsClient')
				->where('clientid =' . $clientid . '')
				->andWhere("formid in (" . $formids . ")");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

	}

?>