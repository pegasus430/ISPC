<?php

	Doctrine_Manager::getInstance()->bindComponent('OverviewCookie', 'SYSDAT');

	class OverviewCookie extends BaseOverviewCookie {

		public static function getCookieData($userid = 0, $page_name = '')
		{
			$livearr = Doctrine_Query::create()
			->select("*")
			->from('OverviewCookie')
			->where("userid = ?", $userid)
			->andWhere("page_name = ?", $page_name)
			->fetchArray();
			
			if($livearr)
			{
				return $livearr;
			}
		}

		public function getRadioChoice()
		{
			$Tr = new Zend_View_Helper_Translate();
			$closeallboxes = $Tr->translate('closeallboxes');
			$opencontentboxes = $Tr->translate('opencontentboxes');
			$letmechoose = $Tr->translate('letmechoose');

			$relisionarray = array('1' => $closeallboxes, '2' => $opencontentboxes, '3' => $letmechoose);

			return $relisionarray;
		}

		public function getAdmissionRadioChoice()
		{
			$Tr = new Zend_View_Helper_Translate();
			$closeallboxes = $Tr->translate('closeallboxes');
			$letmechoose = $Tr->translate('letmechoose');

			$relisionarray = array('1' => $closeallboxes, '3' => $letmechoose);

			return $relisionarray;
		}

		public function getdivNames()
		{

			$divarr = array('1' => 'grow1', '2' => 'grow2', '3' => 'grow3', '4' => 'grow4', '5' => 'grow5', '6' => 'grow6', '7' => 'grow7', '8' => 'grow8', '9' => 'grow9', '10' => 'grow10',
				'11' => 'grow11', '12' => 'grow12', '13' => 'grow13', '14' => 'grow14', '15' => 'grow15', '16' => 'grow16', '17' => 'grow17', '18' => 'grow18', '19' => 'grow19', '20' => 'grow20',
				'21' => 'grow21', '22' => 'grow22', '23' => 'grow23', '24' => 'grow24', '25' => 'grow25', '26' => 'grow26', '27' => 'grow27', '28' => 'grow28', '29' => 'grow29', '30' => 'grow30',
				'31' => 'grow31', '32' => 'grow32', '33' => 'grow33', '34' => 'grow34', '35' => 'grow35', '36' => 'grow36', '37' => 'grow37', '38' => 'grow38', '39' => 'grow39', '40' => 'grow40',
				'41' => 'grow41', '42' => 'grow42',
			    /*
			     * if they are not listed here, you cannot save them
			     */
			    '43' => 'grow43',
			    '44' => 'grow44',
			    '45' => 'grow45',
			    '46' => 'grow46',
			    '47' => 'grow47',
			    '48' => 'grow48',
			    '49' => 'grow49',
			    '50' => 'grow50',
			    '51' => 'grow51',
			    '52' => 'grow52',
			    '53' => 'grow53',
			    '54' => 'grow54',
			    '55' => 'grow55',
				'57' => 'grow35', //ISPC-1757
				'58' => 'grow36',  //ISPC-1757
				'56' => 'grow56',
			    '60' => 'grow60',  //ISPC-2400
				'70' => 'grow70',  //ISPC-2411
				'80' => 'grow80',  //ISPC-2396 Carmen 08.10.2019
				'100' => 'grow100',  //ISPC-2508 Carmen 20.01.2020//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
				'117' => 'grow117',  //ISPC-2774 Carmen 16.12.2020
				'124' => 'grow124',  //ISPC-2381 Carmen 14.01.2021
			    
			);

			return $divarr;
		}

		public function getstammdivNames()
		{

			$divarr = array('16' => 'grow16', '17' => 'grow17', '18' => 'grow18', '19' => 'grow19', '20' => 'grow20', '21' => 'grow21', '22' => 'grow22', '26' => 'grow26', '35' => 'grow35',
				'36' => 'grow36', '37' => 'grow37', '38' => 'grow38', '39' => 'grow39', '40' => 'grow40'
			);

			return $divarr;
		}

	}

?>