<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientFb3categories', 'SYSDAT');

	class ClientFb3categories extends BaseClientFb3categories {

		public function getClientFb3categories($clientid)
		{
			$clcat = Doctrine_Query::create()
				->select('*')
				->from('ClientFb3categories')
				->where('clientid = ' . $clientid);
			$categ = $clcat->fetchArray();

			return $categ;
		}

		public function defaultClientFb3categories()
		{
			$category_defaultarray = array(
				'1' => array('cid' => '1', 'title' => 'Einsatzort / -art'),
				'2' => array('cid' => '2','title' => 'Absprache mit/ Beratung von FachkollegInnen'),
				'3' => array('cid' => '3','title' => 'palliativärztlich-pflegerische Maßnahmen'),
				'4' => array('cid' => '4','title' => 'Psychosoziale Beratung und Begleitung'),
				'5' => array('cid' => '5','title' => 'Sozialrechtliche Beratung'),
				'6' => array('cid' => '6','title' => 'Ethisch-rechtliche Beratung'),
				'7' => array('cid' => '7','title' => 'Unterstützung in der Trauer'),
				'8' => array('cid' => '8','title' => 'Palliativmedizinische Leistungen'),
				'9' => array('cid' => '9','title' => 'Zeiten und Strecken')
			);

			return $category_defaultarray;
		}

		public function getClientFb3categoriesBycat($clientid, $ctid)
		{
			$clcat = Doctrine_Query::create()
				->select('*')
				->from('ClientFb3categories')
				->where('clientid = "' . $clientid . '"')
				->andwhere('categoryid = "' . $ctid . '"');
			$categ = $clcat->fetchArray();

			return $categ;
		}

	}

?>